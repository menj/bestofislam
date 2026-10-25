#!/usr/bin/env python3
"""Builds inc/related-articles.json: for each article, the articles most
worth reading after it.

Score: each glossary term both articles use counts for more the rarer it is
across the site (so "Quran" counts for little and "Kharijites" for a lot),
plus three for the same section and one for the same part of the reading
order. The four best are kept.
Run it after adding or changing articles:  python3 bin/build-related.py
"""
import glob
import html
import json
import math
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# Topic of each article, from the seed list.
setup = open(os.path.join(ROOT, 'inc', 'setup.php'), encoding='utf-8').read()
topic = {m[0]: m[1] for m in re.findall(r"'slug'\s+=> '([^']+)',.*?'topic'\s+=> '([^']+)'", setup, re.S)}

# Part of the reading order.
reading = open(os.path.join(ROOT, 'inc', 'reading.php'), encoding='utf-8').read()
part = {}
for i, block in enumerate(re.findall(r"'slugs'\s+=> array\((.*?)\)", reading, re.S)):
    for slug in re.findall(r"'([^']+)'", block):
        part[slug] = i

# Glossary terms and the common English words never linked.
stop = set(re.findall(r"'([^']+)'", re.search(r"\$words = array\((.*?)\);", open(os.path.join(ROOT, 'inc', 'glossary-links.php'), encoding='utf-8').read(), re.S).group(1)))
terms = []
for f in glob.glob(os.path.join(ROOT, 'inc', 'glossary-data', '*.json')):
    for entry in json.load(open(f, encoding='utf-8')):
        names = [n.replace('’', "'") for n in [entry['title']] + entry.get('aliases', []) if len(n) >= 4 and n.lower() not in stop]
        if names:
            terms.append((entry['slug'], names))

text = {}
for f in glob.glob(os.path.join(ROOT, 'content', '*.json')):
    body = json.load(open(f, encoding='utf-8'))['content']
    raw = re.sub(r'<!--.*?-->', '', body, flags=re.S)
    paragraphs = re.findall(r'<p>(.*?)</p>', raw, re.S)
    text[os.path.basename(f)[:-5]] = html.unescape(' '.join(re.sub(r'<[^>]+>', '', p) for p in paragraphs)).replace('’', "'")

used = {}
for slug, body in text.items():
    used[slug] = {t for t, names in terms if any(re.search(r'(?<![\w])' + re.escape(n) + r'(?![\w])', body, re.I) for n in names)}

df = {}
for terms_used in used.values():
    for t in terms_used:
        df[t] = df.get(t, 0) + 1

weight = {t: math.log(len(text) / n) for t, n in df.items()}

# Hand-picked first choices where the score alone reads oddly.
PINS = {
    'islamophobia-5-facts': ['taqiyya-5-myths', 'war-and-killing-in-islam', 'islam-religion-of-peace', 'crusades-5-facts'],
    'taqiyya-5-myths': ['kharijites-and-takfir', 'war-and-killing-in-islam', 'islamophobia-5-facts', 'islam-religion-of-peace'],
    'quran-4-34': ['hijab-and-modesty', 'child-marriage-in-islam', 'questions-about-aisha', 'quranic-ethics'],
    'hijab-and-modesty': ['quran-4-34', 'questions-about-aisha', 'women-in-the-prophets-life', 'islamophobia-5-facts'],
    'women-in-the-prophets-life': ['questions-about-aisha', 'companions-who-started-with-nothing', 'quran-4-34', 'hijab-and-modesty'],
    'kharijites-and-takfir': ['taqiyya-5-myths', 'war-and-killing-in-islam', 'islam-religion-of-peace', 'apostasy-in-islam'],
    'war-and-killing-in-islam': ['islam-religion-of-peace', 'kharijites-and-takfir', 'crusades-5-facts', 'jizya'],
    'mosques-adapt-to-place': ['islamic-art-and-the-one-god', 'islam-in-china', 'dome-inscriptions', 'before-your-first-friday-prayer'],
    'islamic-art-and-the-one-god': ['mosques-adapt-to-place', 'dome-inscriptions', 'who-is-allah', 'islam-in-china'],
    'sharia-and-hudud': ['apostasy-in-islam', 'why-no-interest', 'war-and-killing-in-islam', 'jizya'],
    'why-no-interest': ['sharia-and-hudud', 'quranic-ethics', 'jizya', 'instruments-islamic-world'],
    'six-articles-of-faith': ['who-is-allah', 'five-pillars-explained', 'fate-and-free-will', 'end-of-time'],
}

related = {}
for a in sorted(text):
    scored = []
    for b in text:
        if a == b:
            continue
        score = sum(weight[t] for t in used[a] & used[b]) + (3 if topic.get(a) == topic.get(b) else 0) + (1 if part.get(a) == part.get(b) else 0)
        scored.append((-score, b))
    picks = [x for x in PINS.get(a, []) if x in text and x != a]
    for _, b in sorted(scored):
        if len(picks) >= 4:
            break
        if b not in picks:
            picks.append(b)
    related[a] = picks[:4]

json.dump(related, open(os.path.join(ROOT, 'inc', 'related-articles.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print(len(related), 'articles')
