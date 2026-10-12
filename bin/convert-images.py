#!/usr/bin/env python3
"""Converts the theme's article images to AVIF and deletes the originals.

Run before building a release:  python3 bin/convert-images.py
Needs Pillow 11.3 or later with AVIF support. Files already in AVIF are left
alone. After converting it rewrites the file names in inc/images.php and
inc/images-data.json, then checks that every referenced file exists and that
no JPEG, PNG or WebP remains.
"""
import json
import os
import re
import sys

from PIL import Image

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DIRS = [os.path.join(ROOT, 'assets', 'images', d) for d in ('featured', 'inline')]
QUALITY = 55

converted = 0
for d in DIRS:
    for name in sorted(os.listdir(d)):
        base, ext = os.path.splitext(name)
        if ext.lower() not in ('.jpg', '.jpeg', '.png', '.webp'):
            continue
        src = os.path.join(d, name)
        Image.open(src).convert('RGB').save(os.path.join(d, base + '.avif'), 'AVIF', quality=QUALITY, speed=4)
        os.remove(src)
        converted += 1

php = os.path.join(ROOT, 'inc', 'images.php')
text = open(php, encoding='utf-8').read()
text = re.sub(r"('file'\s+=> '[^']+)\.(?:jpe?g|png|webp)'", r"\1.avif'", text)
open(php, 'w', encoding='utf-8').write(text)

data_path = os.path.join(ROOT, 'inc', 'images-data.json')
data = json.load(open(data_path, encoding='utf-8'))
for entry in data.values():
    for image in ([entry['featured']] if entry['featured'] else []) + entry['inline']:
        image['file'] = os.path.splitext(image['file'])[0] + '.avif'
json.dump(data, open(data_path, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)

problems = []
for slug, f in re.findall(r"'file'\s+=> '([^']+)'", text) and [(None, x) for x in re.findall(r"'file'\s+=> '([^']+)'", text)]:
    if not os.path.exists(os.path.join(ROOT, 'assets', 'images', 'featured', f)):
        problems.append(f)
for slug, entry in data.items():
    if entry['featured'] and not os.path.exists(os.path.join(ROOT, 'assets', 'images', 'featured', entry['featured']['file'])):
        problems.append(entry['featured']['file'])
    for image in entry['inline']:
        if not os.path.exists(os.path.join(ROOT, 'assets', 'images', 'inline', image['file'])):
            problems.append(image['file'])
for d in DIRS:
    for name in os.listdir(d):
        if not name.endswith('.avif'):
            problems.append('not avif: ' + name)

print('converted', converted)
if problems:
    print('PROBLEMS', *problems, sep='\n  ')
    sys.exit(1)
print('ok')
