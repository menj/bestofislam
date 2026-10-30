---
title: "The Yahweh Question: editorial and SEO report"
series: "The Yahweh Question"
site: "https://bestofislam.org"
prepared: "2026-10-29"
---

# The Yahweh Question: editorial and SEO report

This report goes with the five articles in this folder. It covers the site audit, overlap with existing articles, the evidence and its limits, the keyword map, the links, publication order, the fact-check notes and a final checklist.

## 1. Website audit

**How the audit was done.** The live site was read on 29 October 2026: the front page, the WordPress REST API listings of posts and sections, and individual articles. It was then checked against the theme repository that seeds the site (`content/*.json`, `inc/setup.php`, `docs/SSOT.md`). The live site carries 117 articles, the same 117 slugs as the repository.

**Editorial identity.** The site answers objections to Islam in numbered listicles. Each entry states the objection in its strongest form and answers it from the Quran, the hadith, classical scholarship and the historical record. The documented house rules that matter here are:

- No contractions in any English (SSOT D34). Prose is also checked for contrastive negation, coordinated triads and anaphoric runs.
- Islamic English conventions (SSOT D20): Arabic terms such as *tawhid* are glossed on first use, and the Prophet's name is followed by ﷺ at first mention.
- British spelling.
- Citations are numbered in the text and listed under "Notes". Quran translations are identified, mostly Saheeh International.
- Article titles are in sentence case with the count in the title, for example "7 reasons why Allah of the Quran is the same God as the God of the Bible".
- Images are from free sources, credited, and never depict a prophet (SSOT D30).
- The site does not describe itself as apologetics (SSOT D31).
- Meta descriptions run to at most 130 characters (README).

**Length.** The site's own range is 1,600 to 2,500 words of body text. All five articles fall inside it, and inside the ranges this brief set.

**Sections and tags.** The live site has eight sections (taxonomy `listicle-topic`): Arts & Culture, Belief & Practices, Comparative Religion, Faith & Reason, History, Islamophobia, Science and The Muslim World. **Comparative Religion** (24 articles) fits all five and is set in each front matter. The site uses no tags: the tags endpoint returns an empty list. The `tags` field is therefore left empty rather than inventing any.

**Title case.** The brief's titles are in title case. They are kept exactly as given in each `title` field. Each article's H1 uses the site's sentence case: for example, "Is Yahweh Satan? 7 troubling biblical parallels that raise serious questions". The seeded articles (version 1.78.0) use sentence case, which matches every existing title on the site.

## 2. Overlap with existing articles and tensions

| Existing article | Overlap | How the series handles it |
|---|---|---|
| [same-god](https://bestofislam.org/same-god/), "7 reasons why Allah of the Quran is the same God as the God of the Bible" | Strongest overlap with article 5. Its last entry, "The claim that Allah is Satan inverts the text", is the mirror image of article 1's question. | Article 5 does not repeat its arguments: Arab Christians' use of "Allah", Vatican II, Volf, the general list of shared attributes. It targets a different intent: the name Yahweh, the Satan question, Iblis and the reference/description distinction. Every article links to same-god. Article 1's conclusion (identity not established) agrees with same-god. Article 5 shows why a Muslim cannot accept the identity claim. |
| [who-is-allah](https://bestofislam.org/who-is-allah/) | The meaning of the word Allah. | Article 5 cross-references it and does not discuss the etymology. |
| [islamic-dilemma](https://bestofislam.org/islamic-dilemma/), [where-is-the-injil](https://bestofislam.org/where-is-the-injil/) | *Tahrif* and the earlier scriptures. | Article 5, point 5, summarises the two views of *tahrif* in two sentences and links to islamic-dilemma. |
| [verses-read-differently](https://bestofislam.org/verses-read-differently/), [jesus-not-god](https://bestofislam.org/jesus-not-god/) | Psalm 82:6 and John 10:34. | Article 3 links to verses-read-differently rather than repeat the John 10 discussion. |
| [six-articles-of-faith](https://bestofislam.org/six-articles-of-faith/), [magic-jinn-and-satan-hadiths](https://bestofislam.org/magic-jinn-and-satan-hadiths/), [first-revelation-demonic-claim](https://bestofislam.org/first-revelation-demonic-claim/) | Iblis as one of the jinn; the Satan hadith reports. | Article 5 uses Iblis to argue against the identity claim and links to the hadith article. |
| [gods-attributes-and-anthropomorphism](https://bestofislam.org/gods-attributes-and-anthropomorphism/) | Quran 50:38 (no weariness) as a reply to the seventh-day rest. | Article 5 cites 50:38 in one sentence. Consider adding a link there. |
| [pauline-letters-authorship](https://bestofislam.org/pauline-letters-authorship/) | Marcion's canon. | Article 4 links to it. |
| [fate-and-free-will](https://bestofislam.org/fate-and-free-will/) | God's decree and evil. | Article 5, point 3, links to it. |

**Tension to note for the editor.** Article 1's title asks a question that, put without care, could look as if the site endorses calling the God of the Bible Satan. The site teaches, in same-god, that Muslims and Christians address the same God. The series keeps the site consistent in four ways:

- It states the hypothesis as contested in every article.
- It concludes in article 1 that identity is not established and that several key texts state the opposite.
- Article 5 shows why identity is ruled out on Islamic grounds: Quran 20:14 and 28:30, where Allah speaks to Moses; Quran 18:50 and 37:158, which make Iblis a creature and reject any lineage between God and the jinn.
- It notes, in articles 1 and 5, that the inference would apply equally to the Quran (4:142, 14:4, 21:35), so the argument proves too much.

Keep this framing if the titles are edited.

**Keyword cannibalisation.** "Yahweh vs Allah" and "is Yahweh Allah" are close to same-god's intent ("same God as the God of the Bible"). They are treated as distinct because they name Yahweh and attract readers asking about the name and about the Satan claim. If search data later show the two pages competing, merge article 5's points 1 and 7 into same-god and point the series link there. The other four articles do not overlap any existing page.

## 3. Seven-point outlines

These are the outlines the drafts were written from. Each draft follows its outline in its seven numbered H2 sections.

1. **Is Yahweh Satan?**
   1. 2 Samuel 24:1 and 1 Chronicles 21:1: the Chronicler's substitution.
   2. 1 Kings 22 and Ezekiel 14:9: the deception is commissioned and then disclosed.
   3. Genesis 22, Job 1–2 and James 1:13: testing and temptation; Jubilees and Mastema.
   4. Deuteronomy 20 and 1 Samuel 15: moral critique, and Jewish, Christian and scholarly responses.
   5. John 12:31, 2 Corinthians 4:4 and Luke 4:5–8: in Luke 4:8 Jesus answers Satan with Deuteronomy 6:13. John 8:44 in context, with 8:54.
   6. *satan* as a common noun: Numbers 22, 1 Kings 11, Job, Zechariah, the Septuagint's *diabolos*, Second Temple development.
   7. The three hypotheses weighed.
2. **Who was Yahweh?** Soleb and Amarah West; Kuntillet Ajrud; Khirbet el-Qom; Arad; the Mesha Stele; figurines, the Taanach stand and Motza; what archaeology can and cannot show.
3. **Was Yahweh one of many gods?** El and Yahweh (Exodus 6:3, with Rashi); Ugarit; Deuteronomy 32:8–9 in the Masoretic Text, 4QDeut<sup>j</sup> and the Septuagint; Psalm 82 and its three readings; Baal imagery and rivalry; monolatry and monotheism; redaction, set against the views of Smith and Römer, of Kaufmann, and of faith traditions.
4. **Did early Christians believe Yahweh was evil?** Marcion; the varieties of Demiurge (Plato, Ptolemy, Saturninus); Yaldabaoth in the Apocryphon of John; the Eden serpent (Testimony of Truth; the Quran's 7:20 for contrast); John 8, 14 and 17; Justin, Irenaeus, Polycarp, Tertullian and the canon; who equated the creator with Satan (no second-century source does so explicitly; Samael; the medieval Bogomils).
5. **Is Yahweh the same as Allah?** The burning bush (Exodus 3:15 and Quran 20:14) and Saadia Gaon; *tawhid* and 37:158; mercy, justice and the Quran's own "testing" and "misleading" verses; Iblis; *tahrif* and troubling portrayals; Jesus worships "my Lord and your Lord"; the difference between reference and description.

## 4. Keyword and search-intent map

The keywords are those set in the brief. **No search volumes or difficulty scores were available, and none are given.** They need checking in a keyword tool before publication.

| Article | Primary keyword | Intent served | Secondary keywords |
|---|---|---|---|
| 1 | is Yahweh Satan | A reader who has met the claim online and wants the evidence weighed | Yahweh and Satan; is Yahweh the devil; Satan in the Old Testament; Yahweh and evil in the Bible |
| 2 | origin of Yahweh | Historical and archaeological curiosity | Yahweh archaeology; Yahweh archaeological evidence; Shasu of Yhw; Kuntillet Ajrud Yahweh; Yahweh and Asherah |
| 3 | ancient Israelite polytheism | Biblical-studies question about early monotheism | Yahweh and El; El Elyon and Yahweh; divine council in the Bible; Deuteronomy 32 sons of God; origins of biblical monotheism |
| 4 | Gnosticism Yahweh | Early Christian history and the Demiurge | Yahweh Demiurge; Yaldabaoth Yahweh; Marcion Old Testament God; Gnostic creator god |
| 5 | Yahweh vs Allah | Comparative theology from an Islamic standpoint | is Yahweh Allah; Allah and Yahweh; God of the Bible and Quran; Islam and the God of Abraham |

Each primary keyword appears in the title, the meta description and the opening section. It is not repeated beyond natural use. Meta descriptions run from 120 to 129 characters, within the site limit of 130.

## 5. Links

**Internal links to existing articles, checked live on 29 October 2026 (all returned HTTP 200):**

- same-god (5 links)
- fate-and-free-will
- islamic-dilemma
- jesus-not-god
- magic-jinn-and-satan-hadiths
- pauline-letters-authorship
- verses-read-differently
- who-is-allah

**Series links (return 404 until published):** is-yahweh-satan, who-was-yahweh-archaeological-origins, was-yahweh-one-of-many-gods, early-christians-yahweh-evil-gnosticism-marcion and is-yahweh-the-same-as-allah. They assume the slugs in the brief and the site's permalink pattern (`/slug/`). Article 1 is the hub and links to all four others. Each of the others links back to article 1 and to the parts named in the brief.

**External references.** Every work cited is listed in the Notes of the article that uses it, with author, title, publisher or journal, and year. Only one external URL is given: Titus Kennedy's Soleb article on eScholarship, which was confirmed to exist. No DOIs are given except where confirmed. Kennedy's DOI is 10.5070/D66146256, reported by a search result and not added to the article.

## 6. Research and evidence limitations

- **Primary texts.** Every Bible quotation was taken from the World English Bible, a public-domain translation, through bible-api.com, or from the King James Version where marked. Every Quran quotation was taken from Saheeh International through the quran.com API. The World English Bible renders the Tetragrammaton as "Yahweh", which suits the series. Quotations that would have brought contractions into the text (the WEB has "can't", "don't") were taken from the KJV instead, to respect SSOT D34.
- **Epigraphy and archaeology.** The inscriptions and finds were checked against published scholarship and reference summaries; the excavation reports themselves were not read. Readings that are disputed are presented as disputed: *yhw* at Soleb; "his asherah"; the Khirbet el-Qom text. Dates of objects are kept separate from dates of discovery.
- **Nag Hammadi texts.** The usual online text of the Apocryphon of John and the Testimony of Truth could not be reached, so those texts are paraphrased, not quoted. The one short phrase given, the gloss "fool" for Saklas, is standard. Note 11 of article 4 says so.
- **Wikimedia Commons** could not be queried (rate-limited). Only the Aleppo Codex files were confirmed to exist. The other four featured-image suggestions name an object and must be sourced and licence-checked before use. None depicts a prophet.
- **Search data.** None was available; see section 4.

## 7. Fact-check notes: details to confirm against the sources before publication

Each item below was written from reference works or from memory of standard scholarship. It is believed accurate, but it was not checked against the primary edition.

**Article 1**
- Babylonian Talmud, Sanhedrin 89a (the spirit of Naboth).
- Mishnah Yadayim 4:4 (Sennacherib mixing the nations).
- Maimonides, Laws of Kings 6:1.
- Jubilees 17:15–18 and 48:2–3.
- 1QS 3.13–4.26.
- The Septuagint's *diabolos* in Job 1, Zechariah 3 and 1 Chronicles 21.
- Japhet's commentary, cited for the date of Chronicles.

**Article 2**
- Kuntillet Ajrud inscription numbers 3.1 and 3.9 in the 2012 report.
- The two standing stones at Arad: the excavators and later writers differ on one or two.
- The Arad niche reconstruction at the Israel Museum.
- Herzog's argument about the date the shrine went out of use.
- Mesha Stele line numbers 4–9 and 14–18 (check against Aḥituv).
- Kisilevitz 2015, *Tel Aviv* 42, for Motza.
- Lapp 1969, *BASOR* 195, for Taanach.
- Astour 1979, the Festschrift Edel details.

**Article 3**
- The Ugarit discovery and destruction dates.
- Ginsberg 1938, the *Atti* publication details.
- Rashi on Exodus 6:3.
- The New JPS rendering of Deuteronomy 6:4.
- The longer text of Deuteronomy 32:43 at Qumran (4QDeut<sup>q</sup>).

**Article 4**
- The Tertullian quotation in the Holmes translation (Against Marcion 1.6).
- Page references in NHC II,1 (10–13, 11, 22), NHC IX,3 (45–48) and NHC II,4 (89–90).
- Irenaeus 1.24.2 on Saturninus.
- Irenaeus 1.29 as the parallel to the Apocryphon.
- Irenaeus 3.3.4 for Polycarp's words.
- Justin, *First Apology* 26.
- Ascension of Isaiah 1:8–11 and 2:1 for Sammael.
- Euthymius Zigabenus, *Panoplia* 27, on the Bogomils.

**Article 5**
- Saadia's rendering of the divine name (Steiner 2010).
- Sahih al-Bukhari 3194.
- Sahih Muslim 771.
- Al-Tabari on 4:142 and 3:54 (requital).
- Ibn Hazm's al-Fisal on Genesis 6:6 and 32:24–30.

## 8. Suggested publication order

1. Article 2 (archaeology) and article 3 (divine council), which carry the evidence the hub refers to.
2. Article 4 (Marcion and Gnosticism).
3. Article 5 (Islamic perspective), together with or just before article 1, so the hub never stands alone without the Islamic answer.
4. Article 1, the hub, last, when every series link resolves.

The series was seeded into the theme in version 1.78.0. Each article has:

- a content JSON in `content/`
- an entry in `inc/setup.php` with its section (Comparative Religion)
- a place in the reading order (`inc/reading.php`)
- a featured image and two in-text images from Wikimedia Commons, each public domain or CC0, credited in `inc/images-data.json`
- its Markdown footnotes converted to the site's Notes list

Seeding creates all five posts in one population pass, so the series links resolve together and the order above applies only if the posts are released by hand.

## 9. Publication-readiness checklist

- [x] Five full articles, each with seven numbered H2 points, one H1, an introduction, a conclusion and Notes.
- [x] Body word counts (Notes excluded): 1 = 2,487; 2 = 2,307; 3 = 2,114; 4 = 2,082; 5 = 2,327. All are within the brief's ranges; the total is 11,317 (target 9,600–12,100).
- [x] Bible and Quran translations identified in each article and in each quotation's note.
- [x] Satan as a common noun distinguished from Satan as a developed figure (article 1, point 6; article 4).
- [x] No article claims that an artefact identifies Yahweh with Satan (article 2, point 7, says so explicitly).
- [x] Gnostic groups, Marcion, Satan and the Demiurge kept distinct (article 4, points 1, 2 and 7).
- [x] John 8:44 treated in its historical and literary context, and explicitly not as a statement about Jews collectively (article 1, point 5; article 4, point 5).
- [x] Detailed archaeology kept to article 2, the divine council to article 3, Marcion and the Gnostics to article 4, Islamic theology to article 5; the other articles cross-refer.
- [x] No contractions in the authors' prose; British spelling; ﷺ at first mention of the Prophet (article 5).
- [x] Internal links to existing articles checked live.
- [ ] Confirm the fact-check items in section 7.
- [x] Images sourced from Wikimedia Commons, public domain or CC0 only.
- [x] Sentence case adopted for the seeded titles.
- [ ] Verify keyword volumes.
- [ ] Publish in the order in section 8 so that the series links resolve.
