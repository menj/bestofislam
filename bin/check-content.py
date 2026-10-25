#!/usr/bin/env python3
"""Checks the Glossary data and the article content before they ship.

Fails when: a JSON file does not parse; two Glossary entries share a slug; a
slug ends in -2 to -9 (the suffix WordPress adds on a collision, which must
never reach a URL: give the entry its own name instead); an entry's "terms"
or "articles" point at nothing; a seeded article has no content file; an
article's footnote numbers are not 1..N matching its Notes list; an
article's entry or paragraph blocks are unbalanced.

Run it by hand:  python3 bin/check-content.py
The workflow in .github/workflows/checks.yml runs it on every pull request.
"""
import glob
import json
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
errors = []

# Glossary data.
terms = {}
for path in sorted(glob.glob(os.path.join(ROOT, 'inc', 'glossary-data', '*.json'))):
    try:
        data = json.load(open(path, encoding='utf-8'))
    except json.JSONDecodeError as e:
        errors.append('%s: invalid JSON (%s)' % (os.path.relpath(path, ROOT), e))
        continue
    for t in data:
        slug = t.get('slug', '')
        if slug in terms:
            errors.append('glossary: duplicate slug "%s" (%s and %s)' % (slug, terms[slug], os.path.basename(path)))
        terms[slug] = os.path.basename(path)
        if re.search(r'-[2-9]$', slug):
            errors.append('glossary: slug "%s" carries a collision suffix; give the entry its own name' % slug)

# Seeded articles.
setup = open(os.path.join(ROOT, 'inc', 'setup.php'), encoding='utf-8').read()
start = setup.find('function boi_seed_listicles')
end = setup.find('function boi_seed_pages', start)
seeds = re.findall(r"'slug'\s+=> '([^']+)'", setup[start:end])
for slug in seeds:
    if re.search(r'-[2-9]$', slug):
        errors.append('seed: slug "%s" carries a collision suffix' % slug)
    if not os.path.exists(os.path.join(ROOT, 'content', slug + '.json')):
        errors.append('seed: "%s" has no content/%s.json' % (slug, slug))

for path in sorted(glob.glob(os.path.join(ROOT, 'inc', 'glossary-data', '*.json'))):
    try:
        data = json.load(open(path, encoding='utf-8'))
    except json.JSONDecodeError:
        continue
    for t in data:
        for r in t.get('terms', []):
            if r not in terms:
                errors.append('glossary: "%s" links to unknown term "%s"' % (t.get('slug'), r))
        for r in t.get('articles', []):
            if r not in seeds:
                errors.append('glossary: "%s" links to unknown article "%s"' % (t.get('slug'), r))

# Article content.
for path in sorted(glob.glob(os.path.join(ROOT, 'content', '*.json'))):
    rel = os.path.relpath(path, ROOT)
    try:
        c = json.load(open(path, encoding='utf-8'))['content']
    except (json.JSONDecodeError, KeyError, TypeError) as e:
        errors.append('%s: invalid article JSON (%s)' % (rel, e))
        continue
    if c.count('<!-- wp:bestofislam/entry') != c.count('<!-- /wp:bestofislam/entry -->'):
        errors.append('%s: unbalanced entry blocks' % rel)
    if c.count('<!-- wp:paragraph') != c.count('<!-- /wp:paragraph -->'):
        errors.append('%s: unbalanced paragraph blocks' % rel)
    sups = sorted({int(n) for n in re.findall(r'<sup>\[(\d+)\]</sup>', c)})
    notes = None
    for m in re.finditer(r'<h[2-4][^>]*>Notes</h[2-4]>', c):
        notes = m.start()
    if sups:
        if notes is None:
            errors.append('%s: footnotes without a Notes list' % rel)
        else:
            items = len(re.findall(r'<li>', c[notes:]))
            if sups != list(range(1, items + 1)):
                errors.append('%s: footnotes run to %d but the Notes list has %d items' % (rel, sups[-1], items))

if errors:
    print('\n'.join(errors))
    print('\n%d problem(s).' % len(errors))
    sys.exit(1)

print('OK: %d glossary entries, %d seeded articles.' % (len(terms), len(seeds)))
