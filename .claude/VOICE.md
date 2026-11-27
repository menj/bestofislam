# The voice of the articles

The owner asked on 2026-11-23 for every article, glossary entry and site page to
follow three of his writing skills: his voice profile (the strategic voice), his
anti-AI writing style (the sentence-level rules) and his academic-research method
(citation verification). This file is the working summary for this site. Where it
and `.claude/FARUQI.md` touch the same point, they agree; FARUQI.md still comes first.

## 1. The spine

The work answers claims and refuses to grant the opponent's frame. A fact the
evidence supports is stated as a fact, and the argument built on it is then
answered; the critic's argument is never conceded (FARUQI.md).

Confrontation with Islamophobes is a must (owner's rule, 2026-11-27). Name the
public critic who made the claim, quote him exactly with a checkable source, and
take the claim apart directly and without deference: call an error an error, a
misquotation a misquotation, a fabrication a fabrication, and show it from the
evidence. The tone may be hard, blunt and scornful of the argument. Its force
comes from the evidence: every charge against a named critic's work must be
documented. What is out of bounds is the person's private life (family, marriage,
health, appearance), slurs and abuse, and private individuals, who are never named.

## 2. Voice and structure

- **Editorial *we*.** The analytical voice is *we*, never *I*, and never the
  impersonal "the site". Cross-references read "We set out the evidence in
  [article]", not "The site's article on [article] takes it further".
- **Open with the allegation.** Paragraph 1 states the claim being answered,
  cleanly and fairly. For an explanatory article with no opponent, open with the
  question and what is at stake.
- **Purpose line in paragraph 2 or 3**, once: "This article aims to provide
  context to [...], insha'Allah." The method is stated there and then enacted,
  never re-explained.
- **Scare-quotes on contested terms.** A term from the opponent's vocabulary that
  we dispute ("contradiction", "borrowed", "former Muslim", "error") goes in
  quotation marks at its first appearance, and either the article refutes it or
  the marks stay. Neutral terms and quotations are left alone.
- **Islamic framing.** A verse, hadith or classical principle frames the opening,
  the points of judgement and the close.
- **Evidence stack, then verdict.** Sources accumulate through the body; the
  verdict comes at the end, under "The bottom line", and is no stronger than the
  stack beneath it.
- **Paragraphs of one to three sentences.** Vary sentence length.

## 3. Sentence rules (hard)

- No contractions. The possessive 's, quotations and names (Sa'd) are exempt.
- No em dashes.
- No contrastive negation in any shape: "not X but Y", "not only... but also",
  "this is not X; it is Y", "X rather than Y" used as a reframe, "while X might
  seem..., Y is actually". Say what the thing is.
- Banned words (in the writer's own prose; quotations keep their wording): delve,
  realm, tapestry, leverage, underscore, crucial, pivotal, highlight (verb),
  emphasise, boast, foster, showcase, enhance, robust, seamless, unprecedented,
  testament (in the sense "a testament to"), journey (as a metaphor), landscape
  (abstract), navigate (metaphorical), vibrant, vivid, valuable, genuinely,
  depicted, and the rest of the anti-AI list. Also "serves as", "stands as",
  "marks a" in place of "is".
- Banned phrases and transitions: "in order to", "it is worth noting", "it is
  important to note", "in other words", "that said", "furthermore", "moreover",
  "additionally", "in conclusion", "in summary", "overall", "straightforward",
  any announcement of what the article will do beyond the one purpose line.
- No padding triads or anaphoric runs. A set of three stays when each item is a
  distinct citation, name or category; quotations and the names of God are never
  trimmed.
- Name who said it. No "scholars say", "many sources", "it is widely held".

## 4. Conventions kept from the site

- "(peace be upon him)" at the first mention of Muhammad, and the other
  conventions of SSOT D20 (Islamic English).
- British spelling. His/Him capitalised for God, never for Jesus.
- The other sites are never called "sister sites" or a "network".

## 5. Citations

Every citation is checked against its source, never from memory: does the source
exist with the details given, and does it say what the article says it says?
Hostile witnesses (Christian and secular scholars quoted against Christian claims)
get the surrounding-context check without exception. A citation that cannot be
found is treated as suspect until confirmed, and is corrected, replaced or cut.
The ledger of checks is `docs/CITATIONS.md`.

## 6. Excerpt and abstract

The owner set this on 2026-11-23: the Excerpt and the Abstract are different texts with different readers.

- **Excerpt** (the post excerpt, `excerpt` in `inc/data/seed.json`): for a general audience, at most 150 characters, ending in a mild call to action such as "Read on." It shows under the title, in cards and in search results.
- **Abstract** (`inc/data/abstracts.json`, the box at the top of the article and the schema `abstract`): the overall summary of the article, academic in register. It states the claim answered, the method and the points examined, and the verdict, in 55 to 110 words, with no call to action.

Never copy one into the other. `tests/abstract-test.php` checks both limits.

## 7. Bible references link to thebible.exposed

The owner set this on 2026-11-26, and restated it more strictly the same day, as a permanent, mandatory, project-wide rule until the owner amends or revokes it:

- **Primary reference.** Every Bible verse, quotation, passage and theological discussion links to thebible.exposed: a verse as `https://thebible.exposed/bible/{book}/{chapter}/#v{verse}` (book slug in lower case with hyphens, such as `1-kings`), a chapter as `.../bible/{book}/{chapter}/`, and a topic to the matching page under `/commentary/` or `/glossary/`. It comes before any other Bible website; academic and scholarly sources stay where the argument needs them.
- **The domain is never visible.** It appears only as a link destination (`href`). It never appears as visible text in any publishable content: articles, pages, glossary entries, footnotes, endnotes, citations, bibliographies, captions, annotations, appendices, excerpts, abstracts, and the theme's own readme and changelog. Wording such as "linked on", "according to", "available at" or "as read at" the site is forbidden. A note reads simply "1 Timothy 5:8, ESV", with the reference linked.
- **Anchors** are the reference itself or the words it supports, never "click here" and never the domain.
- **No disclosure.** Do not state the site's ownership, management, affiliation or connection to us or our projects, and make no claim about its independence. Where explicit attribution or disclosure would be required, use another appropriate source or flag it privately for the owner.
- **Verify.** Fetch every destination; the verse anchor must exist on the page. Never fabricate a link. A link that cannot be verified is not added.
- **Gaps.** When the site lacks the material, record it in `.claude/BIBLE-GAPS.md` (topic, proposed title, URL slug, content requirements, the articles waiting) and tell the owner privately in the session. When the page appears, update the references and close the gap.
- **Scope.** Published articles, drafts, scheduled articles and their notes, glossary entries and site pages, retroactively and for all future content.
- **Compliance check, mandatory before finalising.** `tests/bible-links-test.php` fails if the domain appears anywhere outside a link destination in publishable content, or if a link to the site has a malformed address. Run it before every commit that touches content.
