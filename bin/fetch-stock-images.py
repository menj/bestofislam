#!/usr/bin/env python3
"""Fetches the photographs named in bin/stock-images.json and wires them into
inc/images-data.json.

For each article the plan names one featured image and up to three in-text
images, each from Pexels, Pixabay or Wikimedia Commons, with the alt text and
the caption to print. The script downloads the file, crops it to 3:2, resizes it
(1400px wide for the featured image, 960px for the others), stores it as AVIF
and writes the credit entry (artist, licence, source, alt, caption).

Pexels and Pixabay need a key. Pass them in the environment and never commit
them:

    PEXELS_KEY=... PIXABAY_KEY=... python3 bin/fetch-stock-images.py

Commons needs no key; only public domain, CC0, CC BY and CC BY-SA files are
accepted. A file already in place is not downloaded again, so a rerun without
keys only rewrites the credit entries. Needs Pillow 11.3 or later with AVIF.
"""
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

from PIL import Image

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
UA = {'User-Agent': 'BestOfIslamTheme/1.78 (https://bestofislam.org; image script) python-urllib'}
PLAN = os.path.join(ROOT, 'bin', 'stock-images.json')
DATA = os.path.join(ROOT, 'inc', 'images-data.json')
QUALITY = 55


def get(url, headers=None):
    h = dict(UA)
    h.update(headers or {})
    for attempt in range(5):
        try:
            return urllib.request.urlopen(urllib.request.Request(url, headers=h), timeout=60).read()
        except urllib.error.HTTPError as e:
            if e.code == 429:
                time.sleep(15 * (attempt + 1))
                continue
            return None
        except Exception:
            time.sleep(5)
    return None


def pexels(pid):
    key = os.environ.get('PEXELS_KEY')
    if not key:
        return None
    raw = get('https://api.pexels.com/v1/photos/%s' % pid, {'Authorization': key})
    if not raw:
        return None
    p = json.loads(raw)
    return {'url': p['src'].get('large2x') or p['src']['large'], 'artist': p['photographer'],
            'licence': 'Pexels licence', 'source': p['url']}


def pixabay(pid):
    key = os.environ.get('PIXABAY_KEY')
    if not key:
        return None
    raw = get('https://pixabay.com/api/?' + urllib.parse.urlencode({'key': key, 'id': pid}))
    hits = json.loads(raw).get('hits', []) if raw else []
    if not hits:
        return None
    p = hits[0]
    return {'url': p.get('largeImageURL') or p['webformatURL'], 'artist': p['user'],
            'licence': 'Pixabay licence', 'source': p['pageURL']}


def commons(title):
    raw = get('https://commons.wikimedia.org/w/api.php?' + urllib.parse.urlencode({
        'action': 'query', 'format': 'json', 'titles': title, 'prop': 'imageinfo',
        'iiprop': 'url|extmetadata', 'iiurlwidth': '1600',
        'iiextmetadatafilter': 'LicenseShortName|Artist'}))
    if not raw:
        return None
    page = next(iter(json.loads(raw)['query']['pages'].values()))
    if 'imageinfo' not in page:
        return None
    info = page['imageinfo'][0]
    meta = info['extmetadata']
    licence = meta.get('LicenseShortName', {}).get('value', '')
    if not re.match(r'^(public domain|pd|cc0|cc 0|cc by(-sa)? [0-9])', licence, re.I):
        print('SKIPPED, licence is', licence or 'unknown', '-', title)
        return None
    artist = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', html.unescape(meta.get('Artist', {}).get('value', 'Unknown')))).strip()[:110] or 'Unknown'
    short = 'Public domain' if re.match(r'^(public domain|pd)', licence, re.I) else ('CC0' if re.match(r'^cc0|^cc 0', licence, re.I) else licence)
    return {'url': info.get('thumburl') or info['url'], 'artist': artist, 'licence': short,
            'source': 'https://commons.wikimedia.org/wiki/' + page['title'].replace(' ', '_')}


PROVIDERS = {'pexels': pexels, 'pixabay': pixabay, 'commons': commons}


def store(spec, folder, name, width, current=None):
    """Download one image as 3:2 AVIF. Returns its credit fields, or None.

    The file is downloaded when it is missing or when the credit now on record
    names a different source, so a picture is never left under another
    photographer's credit."""
    path = os.path.join(ROOT, 'assets', 'images', folder, name)
    found = PROVIDERS[spec['src']](spec['id'])
    if found is None:
        return None
    same = bool(current) and current.get('source') == found['source'] and os.path.exists(path) and not os.environ.get('FORCE')
    if not same:
        blob = get(found['url'])
        if blob is None:
            return None
        image = Image.open(io.BytesIO(blob)).convert('RGB')
        w, h = image.size
        target = 3 / 2
        if w / h > target:
            nw = round(h * target)
            image = image.crop(((w - nw) // 2, 0, (w - nw) // 2 + nw, h))
        else:
            nh = round(w / target)
            top = max(0, round((h - nh) * 0.35))
            image = image.crop((0, top, w, top + nh))
        image = image.resize((width, round(width / target)), Image.LANCZOS)
        image.save(path, 'AVIF', quality=QUALITY, speed=4)
    return {'file': name, 'alt': spec['alt'], 'caption': spec['caption'], 'artist': found['artist'],
            'licence': found['licence'], 'source': found['source']}


def main():
    plan = json.load(open(PLAN, encoding='utf-8'))
    data = json.load(open(DATA, encoding='utf-8'))
    failed = []
    for slug, spec in plan.items():
        entry = data.setdefault(slug, {'featured': None, 'inline': []})
        if spec.get('featured'):
            got = store(spec['featured'], 'featured', slug + '.avif', 1400, entry.get('featured'))
            if got:
                entry['featured'] = got
            else:
                failed.append(slug + ' featured')
        inline = list(entry.get('inline') or [])
        for i, item in enumerate(spec.get('inline', [])):
            if not item:
                continue
            got = store(item, 'inline', '%s-%d.avif' % (slug, i + 1), 960, inline[i] if i < len(inline) else None)
            if not got:
                failed.append('%s inline %d' % (slug, i + 1))
                continue
            while len(inline) <= i:
                inline.append(None)
            inline[i] = got
        entry['inline'] = inline
        json.dump(data, open(DATA, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
        print('ok', slug)
    print('failed:', failed or 'none')
    return 1 if failed else 0


if __name__ == '__main__':
    sys.exit(main())
