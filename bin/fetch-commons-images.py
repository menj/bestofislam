#!/usr/bin/env python3
"""Swaps the second in-text image of some articles for a Wikimedia Commons file.

The files named in bin/commons-swaps.json were chosen for their subjects. For
each one this script asks Commons for the file's own licence metadata, refuses
anything that is not public domain or CC0, downloads a 960px copy, stores it as
AVIF in assets/images/inline/, and rewrites that article's entry in
inc/images-data.json with the artist, licence and source page.

Run it from a machine that Wikimedia is not rate-limiting, then run
bin/convert-images.py to confirm nothing but AVIF remains:

    python3 bin/fetch-commons-images.py

Needs Pillow 11.3 or later with AVIF support.
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
UA = {'User-Agent': 'BestOfIslamTheme/1.58 (https://bestofislam.org; contact the site owner) python-urllib'}
API = 'https://commons.wikimedia.org/w/api.php?'


def get(url):
    for attempt in range(6):
        try:
            return urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=60).read()
        except urllib.error.HTTPError as e:
            wait = int(e.headers.get('Retry-After', '0') or 0) if e.code == 429 else 0
            time.sleep(max(wait, 15 * (attempt + 1)))
        except Exception:
            time.sleep(10)
    return None


swaps = json.load(open(os.path.join(ROOT, 'bin', 'commons-swaps.json'), encoding='utf-8'))
data_path = os.path.join(ROOT, 'inc', 'images-data.json')
data = json.load(open(data_path, encoding='utf-8'))
failed = []

for slug, (title, alt) in swaps.items():
    query = urllib.parse.urlencode({
        'action': 'query', 'format': 'json', 'titles': title, 'prop': 'imageinfo',
        'iiprop': 'url|extmetadata', 'iiurlwidth': '1400',
        'iiextmetadatafilter': 'LicenseShortName|Artist',
    })
    raw = get(API + query)
    if raw is None:
        failed.append(slug)
        continue
    page = next(iter(json.loads(raw)['query']['pages'].values()))
    info = page['imageinfo'][0]
    meta = info['extmetadata']
    licence = meta['LicenseShortName']['value']
    if not re.match(r'^(public domain|pd|cc0|cc 0)', licence, re.I):
        print('SKIPPED, licence is', licence, '-', title)
        failed.append(slug)
        continue
    artist = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', html.unescape(meta.get('Artist', {}).get('value', 'Unknown')))).strip()[:110] or 'Unknown'
    blob = get(info.get('thumburl') or info['url'])
    if blob is None:
        failed.append(slug)
        continue
    image = Image.open(io.BytesIO(blob)).convert('RGB')
    if image.width > 960:
        image = image.resize((960, round(image.height * 960 / image.width)))
    name = slug + '-2.avif'
    image.save(os.path.join(ROOT, 'assets', 'images', 'inline', name), 'AVIF', quality=55, speed=4)
    data[slug]['inline'][1] = {
        'file': name,
        'alt': alt,
        'artist': artist,
        'licence': 'CC0' if licence.lower().startswith('cc') else 'Public domain',
        'source': 'https://commons.wikimedia.org/wiki/' + page['title'].replace(' ', '_'),
    }
    json.dump(data, open(data_path, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    print('ok', slug)
    time.sleep(3)

print('failed:', failed or 'none')
sys.exit(1 if failed else 0)
