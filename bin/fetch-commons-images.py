#!/usr/bin/env python3
"""Swaps the second in-text image of some articles for a Wikimedia Commons file.

The files named in bin/commons-swaps.json were chosen for their subjects. For
each one this script asks Commons for the file's own licence metadata, refuses
anything that is not public domain or CC0, downloads a 960px copy, stores it as
AVIF in assets/images/inline/, and rewrites that article's entry in
inc/images-data.json with the artist, licence and source page.

It is safe to run again: swaps already made are skipped. The workflow in
.github/workflows/images.yml runs it, then bin/convert-images.py, on every pull
request, and commits whatever changed. To run it by hand:

    python3 bin/fetch-commons-images.py
    python3 bin/convert-images.py

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

def lookup(title):
    """Return (page, info, meta) for a Commons file title, or None. A title that
    starts with "search:" is a Commons search; the first public domain or CC0
    image whose title contains the last search word is used."""
    if title.startswith('search:'):
        term = title[7:].strip()
        raw = get(API + urllib.parse.urlencode({
            'action': 'query', 'format': 'json', 'list': 'search', 'srsearch': term,
            'srnamespace': '6', 'srlimit': '10'}))
        if raw is None:
            return None
        hits = [h['title'] for h in json.loads(raw).get('query', {}).get('search', [])]
        key = term.split()[-1].lower()
        candidates = [h for h in hits if key in h.lower() and re.search(r'\.(jpe?g|png|tiff?)$', h, re.I)]
    else:
        candidates = [title]
    for candidate in candidates:
        raw = get(API + urllib.parse.urlencode({
            'action': 'query', 'format': 'json', 'titles': candidate, 'prop': 'imageinfo',
            'iiprop': 'url|extmetadata', 'iiurlwidth': '1400',
            'iiextmetadatafilter': 'LicenseShortName|Artist'}))
        if raw is None:
            continue
        page = next(iter(json.loads(raw)['query']['pages'].values()))
        if 'imageinfo' not in page:
            print('MISSING', candidate)
            continue
        meta = page['imageinfo'][0]['extmetadata']
        licence = meta.get('LicenseShortName', {}).get('value', '')
        if not re.match(r'^(public domain|pd|cc0|cc 0)', licence, re.I):
            print('SKIPPED, licence is', licence or 'unknown', '-', candidate)
            continue
        return page, page['imageinfo'][0], meta
    return None


for key, (title, alt) in swaps.items():
    # "slug" swaps the second in-text image, "slug#1" the first.
    slug, _, slot_no = key.partition('#')
    slot = 0 if slot_no == '1' else 1
    if data[slug]['inline'][slot]['source'].startswith('https://commons.wikimedia.org/'):
        continue  # already swapped on an earlier run
    found = lookup(title)
    if found is None:
        failed.append(key)
        continue
    page, info, meta = found
    licence = meta['LicenseShortName']['value']
    artist = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', html.unescape(meta.get('Artist', {}).get('value', 'Unknown')))).strip()[:110] or 'Unknown'
    blob = get(info.get('thumburl') or info['url'])
    if blob is None:
        failed.append(key)
        continue
    image = Image.open(io.BytesIO(blob)).convert('RGB')
    if image.width > 960:
        image = image.resize((960, round(image.height * 960 / image.width)))
    name = '%s-%d.avif' % (slug, slot + 1)
    image.save(os.path.join(ROOT, 'assets', 'images', 'inline', name), 'AVIF', quality=55, speed=4)
    data[slug]['inline'][slot] = {
        'file': name,
        'alt': alt,
        'artist': artist,
        'licence': 'CC0' if licence.lower().startswith('cc') else 'Public domain',
        'source': 'https://commons.wikimedia.org/wiki/' + page['title'].replace(' ', '_'),
    }
    json.dump(data, open(data_path, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    print('ok', key)
    time.sleep(3)

print('failed:', failed or 'none')
# A swap that finds no suitable file leaves the existing image in place, so a
# miss is reported but does not fail the run.
sys.exit(0)
