#!/usr/bin/env python3
"""Looks up and installs Wikimedia Commons images through the Commons API.

The Commons API (https://commons.wikimedia.org/wiki/Commons:Commons_API, run
by Magnus Manske on Toolforge) returns a file's licences, author, source,
size and original address in one call. When it does not answer, the tool
asks the official MediaWiki API instead. Thumbnails from upload.wikimedia.org
are often refused with HTTP 429, so the tool downloads the original and
resizes it here.

D30 and D48 in docs/SSOT.md set the rules: an image is used only when its
licence is public domain or CC0, it is credited, and it never depicts a
prophet. The tool enforces the licence. The picture itself is for a person to
look at before it is used.

    python3 bin/commons-api.py info "File:Birmingham Quran manuscript.jpg"
    python3 bin/commons-api.py fetch "File:Birmingham Quran manuscript.jpg" \\
        --slug uthman-copies --kind inline --n 3 \\
        --alt "Two leaves of the Birmingham Quran manuscript" \\
        --caption "The Birmingham leaves, radiocarbon dated to 568 to 645."
    python3 bin/commons-api.py fetch "File:..." --slug ... --kind featured --alt "..." --dry-run

`info` prints the file's details as JSON. `fetch` saves the image as AVIF in
assets/images/featured/ (1,400 pixels wide at most) or assets/images/inline/
(960), and records it in inc/images-data.json with its credit. An image
already in that place is kept unless --replace is given, since D48 keeps
bundled images as they are. --dry-run saves the picture to the current
directory and changes nothing in the theme.

Needs Pillow 11.3 or later with AVIF support for `fetch`.
"""
import argparse
import html
import io
import json
import os
import re
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
UA = {'User-Agent': 'BestOfIslamTheme (https://bestofislam.org; image import) python-urllib'}
COMMONS_API = 'https://magnustools.toolforge.org/commonsapi/?'
MEDIAWIKI_API = 'https://commons.wikimedia.org/w/api.php?'
WIDTH = {'featured': 1400, 'inline': 960}
QUALITY = 55  # the same as bin/convert-images.py

# Licence template names that mean public domain or CC0, as the Commons API
# and the MediaWiki API report them.
FREE = re.compile(r'^(pd\b|pd-|cc-pd|cc0|cc-zero|cc zero|public domain)', re.I)


def get(url, tries=5):
    """Fetch a URL, retrying on HTTP 429 and network errors (a minute's wait at most)."""
    for attempt in range(tries):
        try:
            return urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=60).read()
        except urllib.error.HTTPError as e:
            if e.code == 404:
                return None
            wait = int(e.headers.get('Retry-After', '0') or 0) if e.code == 429 else 0
            if attempt + 1 < tries:
                time.sleep(min(max(wait, 5 * (attempt + 1)), 60))
        except Exception:
            if attempt + 1 < tries:
                time.sleep(5 * (attempt + 1))
    return None


def plain(text):
    """Text from a Commons HTML fragment, leaving out what it hides."""
    text = re.sub(r'<(\w+)[^>]*display:\s*none[^>]*>.*?</\1>', '', html.unescape(text or ''), flags=re.S)
    return re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', text)).strip()


def title_of(name):
    """The file title in the form "File:Name with spaces.jpg"."""
    name = urllib.parse.unquote(name.strip()).replace('_', ' ')
    name = re.sub(r'^https?://commons\.wikimedia\.org/wiki/', '', name)
    return name if name.lower().startswith('file:') else 'File:' + name


def from_commons_api(title):
    raw = get(COMMONS_API + urllib.parse.urlencode({'image': title[5:]}))
    if not raw:
        return None
    root = ET.fromstring(raw)
    f = root.find('file')
    if f is None or f.find('urls/file') is None:
        return None
    english = root.find("description/language[@code='en']")
    return {
        'title': title,
        'page': 'https://commons.wikimedia.org/wiki/' + title.replace(' ', '_'),
        'url': f.findtext('urls/file').split('?')[0],
        'width': int(f.findtext('width') or 0),
        'height': int(f.findtext('height') or 0),
        'licences': [n.text for n in root.findall('licenses/license/name') if n.text],
        'artist': plain(f.findtext('author')) or 'Unknown',
        'description': plain(english.text if english is not None else root.findtext("description/language[@code='default']")),
        'via': 'Commons API',
    }


def from_mediawiki_api(title):
    raw = get(MEDIAWIKI_API + urllib.parse.urlencode({
        'action': 'query', 'format': 'json', 'titles': title, 'prop': 'imageinfo',
        'iiprop': 'url|size|extmetadata',
        'iiextmetadatafilter': 'LicenseShortName|Artist|ImageDescription'}))
    if not raw:
        return None
    page = next(iter(json.loads(raw)['query']['pages'].values()))
    if 'imageinfo' not in page:
        return None
    info = page['imageinfo'][0]
    meta = info.get('extmetadata', {})
    return {
        'title': page['title'],
        'page': 'https://commons.wikimedia.org/wiki/' + page['title'].replace(' ', '_'),
        'url': info['url'],
        'width': info.get('width', 0),
        'height': info.get('height', 0),
        'licences': [meta.get('LicenseShortName', {}).get('value', '')],
        'artist': plain(meta.get('Artist', {}).get('value')) or 'Unknown',
        'description': plain(meta.get('ImageDescription', {}).get('value')),
        'via': 'MediaWiki API',
    }


def lookup(name):
    """A file's details, with its licence judged against D30 and D48."""
    title = title_of(name)
    info = from_commons_api(title) or from_mediawiki_api(title)
    if info is None:
        return None
    free = [l for l in info['licences'] if FREE.match(l or '')]
    info['usable'] = bool(free)
    info['licence'] = ('CC0' if any(re.match(r'^cc(0|-zero| zero)', l, re.I) for l in free) else 'Public domain') if free else ''
    info['artist'] = info['artist'][:110]
    return info


def cmd_info(args):
    info = lookup(args.title)
    if info is None:
        sys.exit('Not found on Commons: ' + args.title)
    print(json.dumps(info, ensure_ascii=False, indent=1))


def cmd_fetch(args):
    info = lookup(args.title)
    if info is None:
        sys.exit('Not found on Commons: ' + args.title)
    if not info['usable']:
        sys.exit('Refused: the licences are %s. D30 and D48 accept public domain or CC0 only.' % (', '.join(info['licences']) or 'unknown'))

    data_path = os.path.join(ROOT, 'inc', 'images-data.json')
    data = json.load(open(data_path, encoding='utf-8'))
    entry = data.setdefault(args.slug, {'featured': None, 'inline': []})
    entry.setdefault('inline', [])

    if args.kind == 'featured':
        name, occupied = args.slug + '.avif', bool(entry.get('featured'))
    else:
        n = args.n or len(entry['inline']) + 1
        if n < 1 or (n > len(entry['inline']) + 1 and not args.dry_run):
            sys.exit('--n must be between 1 and %d for %s.' % (len(entry['inline']) + 1, args.slug))
        name, occupied = '%s-%d.avif' % (args.slug, n), n <= len(entry['inline'])
    if occupied and not args.replace and not args.dry_run:
        sys.exit('That place already holds an image; D48 keeps bundled images. Pass --replace to swap it.')

    blob = get(info['url'])
    if blob is None:
        sys.exit('Could not download ' + info['url'])
    from PIL import Image
    image = Image.open(io.BytesIO(blob)).convert('RGB')
    width = WIDTH[args.kind]
    if image.width > width:
        image = image.resize((width, round(image.height * width / image.width)), Image.LANCZOS)

    target = name if args.dry_run else os.path.join(ROOT, 'assets', 'images', args.kind, name)
    image.save(target, 'AVIF', quality=QUALITY, speed=4)

    record = {'file': name, 'alt': args.alt}
    if args.caption:
        record['caption'] = args.caption
    record.update({'artist': info['artist'], 'licence': info['licence'], 'source': info['page']})

    if args.dry_run:
        print(json.dumps({'saved': os.path.abspath(target), 'size': image.size, 'record': record}, ensure_ascii=False, indent=1))
        return

    if args.kind == 'featured':
        entry['featured'] = record
    elif n <= len(entry['inline']):
        entry['inline'][n - 1] = record
    else:
        entry['inline'].append(record)
    with open(data_path, 'w', encoding='utf-8') as out:
        json.dump(data, out, ensure_ascii=False, indent=1)
    print('Saved %s (%dx%d) and recorded it for %s.' % (os.path.relpath(target, ROOT), image.width, image.height, args.slug))
    if args.kind == 'inline':
        print('Place it in the article with <!-- wp:bestofislam/figure {"n":%d} /--> if it is new.' % n)


def main():
    parser = argparse.ArgumentParser(description='Look up and install Wikimedia Commons images.')
    sub = parser.add_subparsers(dest='command', required=True)

    p = sub.add_parser('info', help="print a file's licence, author and size as JSON")
    p.add_argument('title', help='"File:Name.jpg", "Name.jpg" or the file page URL')
    p.set_defaults(run=cmd_info)

    p = sub.add_parser('fetch', help='save a public-domain or CC0 file as AVIF and record it')
    p.add_argument('title')
    p.add_argument('--slug', required=True, help='article slug in inc/images-data.json')
    p.add_argument('--kind', choices=('featured', 'inline'), required=True)
    p.add_argument('--n', type=int, help='position of an inline image, from 1; default: the next free one')
    p.add_argument('--alt', required=True, help='alt text: what the picture shows')
    p.add_argument('--caption', help='caption shown under the picture')
    p.add_argument('--replace', action='store_true', help='swap an image already in that place')
    p.add_argument('--dry-run', action='store_true', help='save to the current folder; change nothing in the theme')
    p.set_defaults(run=cmd_fetch)

    args = parser.parse_args()
    args.run(args)


if __name__ == '__main__':
    main()
