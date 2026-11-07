#!/usr/bin/env python3
"""Checks the Glossary data and the article content before they ship.

Fails when: a JSON file does not parse; two Glossary entries share a slug; a
slug ends in -2 to -9 (the suffix WordPress adds on a collision, which must
never reach a URL: give the entry its own name instead); an entry's "terms"
or "articles" point at nothing; a seeded article has no content file; an
article's footnote numbers are not 1..N matching its Notes list; an
article's entry or paragraph blocks are unbalanced; a seeded article
lacks a category, sits in a category that has subcategories, or in a
category outside its section, or has fewer than 3 or more than 6 tags; a tag slug holds
digits; two tags are near-duplicates; a shared tag has no description; a
Glossary entry has no valid kind. --kinds prints the count for each kind.

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

# Glossary data. Every entry names one kind from boi_glossary_types()
# (inc/glossary-data.php), written in its record, never guessed at runtime.
KINDS = ('term', 'worship', 'belief', 'law', 'scripture', 'ethics', 'person', 'prophet', 'place', 'event', 'group', 'book', 'topic')
kind_use = {}
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
        kind = t.get('type')
        if kind not in KINDS:
            errors.append('glossary: "%s" has kind "%s"; use one of %s' % (slug, kind, ', '.join(KINDS)))
        kind_use[kind] = kind_use.get(kind, 0) + 1

# Seeded articles: the seed list lives in inc/data/seed.json.
seed_path = os.path.join(ROOT, 'inc', 'data', 'seed.json')
try:
    seeds = [x['slug'] for x in json.load(open(seed_path, encoding='utf-8')).get('listicles', [])]
except (OSError, json.JSONDecodeError, KeyError, TypeError) as e:
    errors.append('inc/data/seed.json: cannot read the seed list (%s)' % e)
    seeds = []
# The term map in seed.json must carry the version the code migrates to.
try:
    _seed_tv = json.load(open(seed_path, encoding='utf-8')).get('terms_version')
    _m = re.search(r"const BOI_TERMS_VERSION = (\d+);", open(os.path.join(ROOT, 'inc', 'setup.php'), encoding='utf-8').read())
    if not _m or _seed_tv != int(_m.group(1)):
        errors.append('inc/data/seed.json: terms_version %r does not match BOI_TERMS_VERSION in inc/setup.php' % (_seed_tv,))
except (OSError, json.JSONDecodeError) as e:
    errors.append('terms_version check failed (%s)' % e)
for slug in seeds:
    if not os.path.exists(os.path.join(ROOT, 'content', slug + '.json')):
        errors.append('seed: "%s" has no content/%s.json' % (slug, slug))


def collision_suffix(slug, known):
    """True for the -2, -3, ... -10, -11 suffixes WordPress adds on a collision.

    A single digit is always refused. A longer run of digits is refused only
    when the slug without it is itself a known slug, so an abd-10 beside abd
    is refused. Since 1.94.0 the digit check below refuses every digit anyway.
    """
    m = re.search(r'-(\d+)$', slug)
    if not m:
        return False
    digits = m.group(1)
    if len(digits) == 1:
        return digits in '23456789'
    return slug[: m.start()] in known


# Title tags read "<page> | Best of Islam" and stay under 60 characters, so the
# page part of each seeded article and Glossary entry may run to 43. A longer
# title needs a short "seo_title" (see boi_title_parts() in inc/seo.php).
TITLE_BUDGET = 59 - len(' | Best of Islam')
try:
    seed_items = json.load(open(seed_path, encoding='utf-8')).get('listicles', [])
except (OSError, json.JSONDecodeError):
    seed_items = []
for item in seed_items:
    shown = item.get('seo_title') or item.get('title', '')
    if len(shown) > TITLE_BUDGET:
        errors.append('seed: "%s" title tag would run %d characters before " | Best of Islam" (limit %d); add a shorter seo_title' % (item.get('slug'), len(shown), TITLE_BUDGET))
for path in sorted(glob.glob(os.path.join(ROOT, 'inc', 'glossary-data', '*.json'))):
    try:
        data = json.load(open(path, encoding='utf-8'))
    except json.JSONDecodeError:
        continue
    for t in data:
        shown = t.get('seo_title') or t.get('title', '')
        if len(shown) > TITLE_BUDGET:
            errors.append('glossary: "%s" title tag would run %d characters (limit %d); add a shorter seo_title' % (t.get('slug'), len(shown), TITLE_BUDGET))

for slug in terms:
    if collision_suffix(slug, terms):
        errors.append('glossary: slug "%s" carries a collision suffix; give the entry its own name' % slug)

for slug in seeds:
    if collision_suffix(slug, set(seeds)):
        errors.append('seed: slug "%s" carries a collision suffix' % slug)

# Addresses carry no digits: no verse number, count or year (SSOT D49).
for slug in seeds:
    if re.search(r'\d', slug):
        errors.append('seed: slug "%s" carries a digit; name the subject and keep the number in the title' % slug)
for slug in terms:
    if re.search(r'\d', slug):
        errors.append('glossary: slug "%s" carries a digit; name the subject and keep the number in the title' % slug)

# Every renamed article redirects to a seeded one, and no old slug is seeded
# again (inc/renamed-slugs.php).
try:
    _src = open(os.path.join(ROOT, 'inc', 'renamed-slugs.php'), encoding='utf-8').read()
    _body = re.search(r'function boi_post_slug_renames\(\) \{.*?\n\}', _src, re.S).group(0)
    for _old, _new in re.findall(r"'([a-z0-9-]+)'\s*=>\s*'([a-z0-9-]+)'", _body):
        if _new not in seeds:
            errors.append('inc/renamed-slugs.php: "%s" redirects to "%s", which is not a seeded article' % (_old, _new))
        if _old in seeds:
            errors.append('inc/renamed-slugs.php: "%s" is renamed but still seeded' % _old)
except (OSError, AttributeError) as e:
    errors.append('inc/renamed-slugs.php: cannot read the rename map (%s)' % e)

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
        lex = t.get('lexicon')
        if lex:
            if not lex.get('root') or not lex.get('notes'):
                errors.append('glossary: "%s" lexicon needs a root and at least one note' % t.get('slug'))
            for n in lex.get('notes', []):
                for k in ('src', 'ar', 'gloss', 'url'):
                    if not n.get(k):
                        errors.append('glossary: "%s" lexicon note lacks "%s"' % (t.get('slug'), k))
                if not str(n.get('url', '')).startswith('https://arabiclexicon.hawramani.com/'):
                    errors.append('glossary: "%s" lexicon url must be on arabiclexicon.hawramani.com' % t.get('slug'))
                if len(n.get('ar', '')) > 450:
                    errors.append('glossary: "%s" lexicon excerpt runs %d characters (limit 450); quote a shorter passage' % (t.get('slug'), len(n['ar'])))

# A new entry must not reuse another entry's title or alias: the link map keeps
# the first match, so mentions would link to the older entry. The baseline file
# bin/glossary-name-baseline.json lists, for each name that was already shared
# when this check was added, the slugs that shared it. Those pairs are allowed;
# any other entry that uses the name is refused. Names are compared trimmed and
# lowercased, as the link map does.
_names = {}
for path in sorted(glob.glob(os.path.join(ROOT, 'inc', 'glossary-data', '*.json'))):
    try:
        _data = json.load(open(path, encoding='utf-8'))
    except json.JSONDecodeError:
        continue
    for t in _data:
        for n in {t.get('title', '').strip().lower()} | {a.strip().lower() for a in t.get('aliases', [])}:
            _names.setdefault(n, []).append(t.get('slug'))
try:
    _baseline = json.load(open(os.path.join(ROOT, 'bin', 'glossary-name-baseline.json'), encoding='utf-8'))
except (OSError, json.JSONDecodeError):
    _baseline = {}
for n, slugs in sorted(_names.items()):
    if len(slugs) > 1:
        extra = [x for x in slugs if x not in set(_baseline.get(n, []))]
        if extra:
            errors.append('glossary: the name "%s" is used by %s; give %s its own title or alias' % (n, ', '.join(slugs), ', '.join(extra)))

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

# Article images: AVIF or WebP only, and every credited file must exist.
for folder in ('featured', 'inline'):
    for name in sorted(os.listdir(os.path.join(ROOT, 'assets', 'images', folder))):
        if not name.lower().endswith(('.avif', '.webp')):
            errors.append('images: assets/images/%s/%s is not AVIF or WebP' % (folder, name))
try:
    credits = json.load(open(os.path.join(ROOT, 'inc', 'images-data.json'), encoding='utf-8'))
except (OSError, json.JSONDecodeError) as e:
    credits = {}
    errors.append('inc/images-data.json: cannot read (%s)' % e)
for slug, entry in credits.items():
    shots = ([('featured', entry['featured'])] if entry.get('featured') else []) + [('inline', i) for i in entry.get('inline') or [] if i]
    for folder, shot in shots:
        if not os.path.exists(os.path.join(ROOT, 'assets', 'images', folder, shot['file'])):
            errors.append('images: %s lists %s/%s, which does not exist' % (slug, folder, shot['file']))


# Categories and tags. Every seeded article names one category from
# `categories` and 3 to 6 tags from `tags` in inc/data/seed.json. A tag slug
# carries no digits (a verse number belongs in the text, never in an
# address), two tags may not differ only by case or a plural "s", and a tag
# used by two or more articles has a description.
def tag_slug(name):
    return re.sub(r'-+', '-', re.sub(r'[^a-z0-9]+', '-', name.lower())).strip('-')


try:
    seed_all = json.load(open(seed_path, encoding='utf-8'))
except (OSError, json.JSONDecodeError):
    seed_all = {}
categories = seed_all.get('categories', {}) or {}
vocabulary = seed_all.get('tags', {}) or {}
topics = seed_all.get('topics', {}) or {}
# A top-level category mirrors a section ("topic"); a subcategory names a
# top-level "parent" instead. Articles are filed in a leaf (a category with
# no subcategories), except the few in DIRECT, which fit none of their
# parent's subcategories. The top-level category above an article's own must
# mirror the article's section, so the canonical section archive holds it.
# Every leaf holds at least 5 articles, except those in SMALL.
DIRECT = {
    'faith-and-reason': ('universe-demands-creator', 'morality-without-god'),
    'history': ('quran-preservation-reddit', 'quranic-stories-and-history', 'quran-mysteries-in-the-stories'),
}
SMALL = {
    # The Library of Alexandria article went back to History (its section) in
    # 1.90.0, which leaves 4 here; the owner accepted 4 for this section.
    'arts-and-culture': 4,
}
children = {}
for slug, cat in categories.items():
    if cat.get('parent'):
        children.setdefault(cat['parent'], []).append(slug)


def top_of(slug):
    seen_slugs = set()
    while slug in categories and categories[slug].get('parent') and slug not in seen_slugs:
        seen_slugs.add(slug)
        slug = categories[slug]['parent']
    return slug


for slug, cat in categories.items():
    if re.search(r'\d', slug):
        errors.append('categories: slug "%s" contains a digit' % slug)
    if not (cat.get('description') or '').strip():
        errors.append('categories: "%s" has no description' % slug)
    if cat.get('parent'):
        parent = categories.get(cat['parent'])
        if parent is None or parent.get('parent'):
            errors.append('categories: "%s" has parent "%s", which is not a top-level category' % (slug, cat['parent']))
        if cat.get('topic'):
            errors.append('categories: subcategory "%s" names a section; only its parent mirrors one' % slug)
    elif cat.get('topic') not in topics:
        errors.append('categories: "%s" mirrors no section ("%s")' % (slug, cat.get('topic')))
tag_use = {}
cat_use = {}
for item in seed_items:
    slug = item.get('slug')
    cat = item.get('category')
    if not cat or cat not in categories:
        errors.append('seed: "%s" has no category from the seed list' % slug)
    else:
        cat_use[cat] = cat_use.get(cat, 0) + 1
        if cat in children and slug not in DIRECT.get(cat, ()):
            errors.append('seed: "%s" is filed in "%s", which has subcategories; file it in one' % (slug, cat))
        top = categories.get(top_of(cat), {})
        if top.get('topic') != item.get('topic'):
            errors.append('seed: "%s" is in section "%s" but category "%s" (under section "%s")' % (slug, item.get('topic'), cat, top.get('topic')))
    if item.get('category_v1') and item['category_v1'] not in categories:
        errors.append('seed: "%s" has an unknown category_v1 "%s"' % (slug, item['category_v1']))
    tags = item.get('tags') or []
    if not 3 <= len(tags) <= 6:
        errors.append('seed: "%s" has %d tags (3 to 6 needed)' % (slug, len(tags)))
    if len(set(tags)) != len(tags):
        errors.append('seed: "%s" lists a tag twice' % slug)
    for name in tags:
        if name not in vocabulary:
            errors.append('seed: "%s" uses tag "%s", which is not in the tag list' % (slug, name))
        tag_use[name] = tag_use.get(name, 0) + 1
seen = {}
for name, description in vocabulary.items():
    tslug = tag_slug(name)
    if re.search(r'\d', tslug):
        errors.append('tags: "%s" has digits in its slug (%s); name scripture in words' % (name, tslug))
    key = tslug[:-1] if tslug.endswith('s') else tslug
    if key in seen:
        errors.append('tags: "%s" and "%s" are near-duplicates' % (seen[key], name))
    seen[key] = name
    if name not in tag_use:
        errors.append('tags: "%s" is used by no article' % name)
    elif tag_use[name] >= 2 and not (description or '').strip():
        errors.append('tags: "%s" is used by %d articles and has no description' % (name, tag_use[name]))
for cat in categories:
    held = cat_use.get(cat, 0) + sum(cat_use.get(c, 0) for c in children.get(cat, []))
    least = SMALL.get(cat, 5)
    if held < least:
        errors.append('categories: "%s" holds %d articles (at least %d)' % (cat, held, least))

if '--summary' in sys.argv or '--kinds' in sys.argv:
    print('Glossary kind -> entries (%d in all)' % sum(kind_use.values()))
    for kind in KINDS:
        print('  %-22s %d' % (kind, kind_use.get(kind, 0)))

if '--summary' in sys.argv:
    print('Category -> articles')
    for cat in categories:
        if categories[cat].get('parent'):
            continue
        sub = children.get(cat, [])
        print('  %-26s %d' % (cat, cat_use.get(cat, 0) + sum(cat_use.get(c, 0) for c in sub)))
        for c in sub:
            print('    %-24s %d' % (c, cat_use.get(c, 0)))
        if sub and cat_use.get(cat, 0):
            print('    %-24s %d' % ('(directly)', cat_use.get(cat, 0)))
    print('Tags: %d (%d used once)' % (len(tag_use), sum(1 for v in tag_use.values() if v == 1)))
    for name, n in sorted(tag_use.items(), key=lambda x: (-x[1], x[0]))[:30]:
        print('  %-28s %d' % (name, n))


if errors:
    print('\n'.join(errors))
    print('\n%d problem(s).' % len(errors))
    sys.exit(1)

print('OK: %d glossary entries, %d seeded articles.' % (len(terms), len(seeds)))
