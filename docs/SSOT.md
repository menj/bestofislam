# Single Source of Truth

Authoritative record of architectural decisions for the Best of Islam child
theme. Amend this file whenever a decision here is superseded.

## Scope

bestofislam.org, a listicle publication. Distinct from bestofislam.com, which
runs a separate theme.

## Decisions

### D1. Everything ships in the child theme

No companion plugin. Directed by the site owner. The consequence is that the
taxonomy, blocks, and votes table are bound to this theme; switching themes
would orphan taxonomy terms and leave block markup unrendered. Mitigation: each
concern lives in a self-contained file under `inc/` that could be moved into a
plugin without modification.

### D2. Blocks rather than a custom post type

Entries live in `post_content` as nested blocks. This keeps content portable,
visible in the editor canvas, and free of meta-box repeaters. Any ordinary post
may become a listicle.

### D3. Server-side rendering for both blocks

`save()` emits inner block content only. Rank numbers, vote tallies, and the
configured image size are resolved at render time, so reordering entries or
changing settings requires no post re-save.

### D4. Schema derived from parsed blocks

`inc/schema.php` walks `parse_blocks()` output rather than reading duplicated
meta. Structured data therefore cannot diverge from visible content.

### D5. Colour configuration through theme.json

Listicle tokens are declared under `settings.custom.listicle` and consumed as
`--wp--custom--listicle--*` custom properties. Recolouring happens through the
Site Editor and style variations. The settings screen deliberately carries no
colour picker.

### D6. Votes in a dedicated table

`{prefix}_boi_votes`, with a unique key on `(post_id, entry_id, voter_hash)`.
Post meta was rejected because a popular list would bloat the meta table and
slow every query touching the post. Tallies are cached in transients for one
hour and invalidated on write.

### D7. Public voting, rate limited

Default is public voting, one vote per entry per visitor hash, capped at ten
votes per ten minutes. A logged-in-only switch is available if coordinated
voting appears. The visitor hash combines address and user agent with
`wp_salt()`; for authenticated users it is derived from the user identifier.

### D8. No build step

Editor scripts are written against `wp.element.createElement` and loaded
directly through `block.json`. No npm toolchain, no compiled bundle.

### D9. Distinct identity from bestofislam.com

The .org property carries its own visual identity. It inherits one constraint
from the .com theme: the chrome must not read as overtly Islamic at first
glance, so religious typography appears only where the content calls for it.
Typography is Platypi for display and Ysabeau Office for text, both bundled
from the parent theme's font set as variable woff2. The wordmark is a third
face, Vollkorn at 800, set lowercase and tightened, so the site name reads as a
mark rather than as a heading that happens to sit in the header. It is
typeset rather than drawn, which remains the honest limit of the current
identity.

The palette is navy, azure and gold over white, taken from the Bismika
Allahuma property. The earlier paper and terracotta scheme was dropped because
it read as a house habit across the group rather than as an identity for this
site. Only the colour crosses over: the gradients, bevels, mosque silhouette
and masthead calligraphy of that design stay behind, the last two because they
would breach the chrome constraint above.

Gold fails contrast against white at text sizes, so it is confined to rules,
borders and fills.

Corners are rounded on a four-step token scale in `settings.custom.radius`
(6px, 10px, 16px, pill). Entries are filled cards rather than rule-separated
rows.

### D10. Content model serves apologetics, not product review

The site publishes argumentative listicles. Three consequences follow. Star
ratings are removed, since a rating communicates nothing about a proposition
and `Review` markup on one risks a structured-data penalty. Voting defaults to
off, since a public tally on a contested argument invites organised voting and
reads as a verdict. Each entry carries an optional objection field, rendered
before the response, so the claim-then-answer rhythm is visible on the page.

### D11. Script fonts are range-scoped and unbundled

Six faces covering Arabic, Hebrew, Greek, Syriac, and Akkadian are declared in
`assets/css/scripts.css` under `unicode-range`, so a page without those glyphs
downloads nothing. The files themselves are not shipped; licence terms put that
on the site owner. `assets/fonts/scripts/README.md` names each expected file.

### D12. Population is idempotent and reversible by hand

`inc/setup.php` runs once on activation, guarded by the `boi_content_populated`
option, and may be re-run from the Content tab. Every creation step checks for
an existing object first, so a second run adds only what is missing. Listicles
are created as drafts rather than published, so nothing reaches the public site
before the author has written the responses.

### D13. Reader-facing dark mode overrides tokens, not stylesheets

The appearance toggle sets `data-theme` on the document element, and
`assets/css/theme-toggle.css` redefines the `--wp--preset--color--*` tokens
under that attribute. Overriding the presets rather than the listicle tokens
means every block on the page follows the switch. The dark values mirror the
Night style variation, so the reader-facing toggle and the editor-facing
variation produce the same palette. A pre-paint script in the head prevents the
light flash; it is inline by necessity, since an external file would load too
late.

### D14. Topics are sections, not subjects

Topic terms name a standing section of the publication rather than the subject
of one article. A term should be able to hold dozens of listicles over time.
Where a section grows large enough to justify subdivision, the taxonomy is
hierarchical and narrower terms nest beneath the section rather than sitting
beside it. The seed set reproduces the section scheme the Best of Islam
property has used since its earlier incarnation: Belief & Practices, Arts &
Culture, History, Science, The Muslim World, with Islamophobia as a child of
the last, with one addition. Faith & Reason was added because argument and
rebuttal content serves a different reader and a different search intent from
explanatory content, and routing both into Belief & Practices blurred the
signal for each.

Three rules govern the taxonomy from here. The top level is capped at six
terms, since beyond that the navigation wraps and each archive thins. A post
takes exactly one section, because multiple assignment creates duplicate
archive paths for the same content. Child terms are created on evidence rather
than in advance, once a section holds roughly a dozen articles and a recurring
sub-theme is visible in the analytics. Creating children early produces empty
archives, which was the weakness of the earlier scheme.

### D15. Articles ship as files, not as PHP arrays

Finished prose lives in `/content/<slug>.html` as block markup and is read at
seed time. Embedding articles in PHP string literals makes both the code and
the prose harder to revise, and it puts editorial work behind a deployment. The
loader resolves the slug through `sanitize_file_name()` and returns an empty
string when the file is absent, in which case that post is skipped rather than
created empty.

Seeded articles publish rather than draft, because they carry finished content.
Each declares its own slug so the URL is fixed by the theme.

### D16. The mark describes the format, not the subject

The logo is three rounded strokes of descending length inside a rounded frame:
the countdown list, drawn. It was chosen over two alternatives, an eightfold
geometric knot and a pointed arch, because both of those would sit equally well
on any site about Islam, and because both breach the chrome constraint in D9 by
signalling the subject before the content does. The descent mark signals the
format instead.

`mark.svg` takes `currentColor` for its frame and third stroke, so it reverses
on the navy band and holds on white without a second file. `mark-solid.svg`
carries fixed colours for the site icon and share cards, where inheritance is
unavailable.

### D17. The front page and the posts index are separate

The front page is a static page, so it can become a curated landing page
without disturbing anything. The posts index lives on its own page, titled Reflections, assigned
through `page_for_posts` and rendered by `templates/home.html`. Keeping the two
apart means the site always has one URL listing every article, whatever the
front page later becomes.

### D18. Content type is derived, not declared

The site publishes two kinds of post. Listicles take the ranked format and are
reached through the section archives. Reflections are essays and shorter pieces
and are reached through the posts index.

The distinction is derived from the content: `save_post` checks for the
listicle block and writes `_boi_is_listicle` accordingly, and a `pre_get_posts`
filter excludes flagged posts from the home query. No custom post type, no
editorial checkbox, and no way for the classification to disagree with what the
post actually contains. A separate post type was rejected because it would split
the taxonomy, the feed and the archive logic for a distinction the content
already makes.

Section archives are deliberately unfiltered: a reader browsing History wants
both kinds.

### D19. Search results point at entries, not only at posts

The useful unit on this site is the entry. A reader searching for a specific
objection wants the entry that answers it, and a result that drops him at the
top of a 1,200-word article makes him find it himself.

`boi_matching_entries()` parses the post's blocks, tests each entry's title and
rendered body against the term, and returns up to three with their anchors. The
anchors come from `boi_entry_anchor()`, the same function the entry renderer
and the jump list use, so the three cannot disagree.

Relevance ordering is applied through `posts_orderby` rather than by
post-processing, so it survives pagination. Filters are read from the URL
rather than held in session, so a filtered search is linkable.

### D20. Breakpoints follow the content, and the numeral degrades rather than disappears

The header steps at 1300px, 781px and 600px. Six section links, the search
field and the toggle need about 1,200px beside the wordmark, so up to 1300px
the header takes two rows. Between 600 and 781px the links keep one row that
scrolls sideways, fading at the edge, because WordPress folds them into its
menu button only below 600px. Below 600px the menu button, search and toggle
share a row. Elsewhere, layout steps at 1024px, 781px and 600px; the lower two
match the parent's breakpoints so the child never disagrees with it.

The rank numeral is the element that decides the scale. At full width it is a
display figure in the margin. On a tablet the column narrows and the figure
shrinks. On a phone the column is dropped and the figure becomes a label above
the title, which keeps the reader's position visible inside a long entry. It is
never removed, because the numeral is the format.

Grids take a minimum column width, not a column count, so they reflow instead
of holding a fixed count into a narrow screen. Grid items align to the start
of their row, so opening one question never stretches its neighbour.

### D21. The identity ships in the base, not in a variation

Everything that defines how the site looks by default belongs in the child's
own `theme.json`: the palette, the base styles, the type assignments. A style
variation is an option a user selects, and shipping the identity there meant a
fresh activation inherited the parent's defaults and looked broken. The two
variations remain, and now differ from the base only where they are meant to.

The same reasoning applies to the header and footer. A block theme that relies
on the user assembling its chrome in the Site Editor has not shipped that
chrome. The child supplies both parts, overriding the parent's, so the band,
the wordmark and the toggle are present on activation.

### D22. Navigation ships in the parts, and the seed writes wp_navigation

Block themes resolve the navigation block against the `wp_navigation` post
type. Classic menus are invisible to it. The header and footer parts carry
their links as inner blocks, so the menu exists on activation with nothing to
select; the seed also creates a `wp_navigation` post so an editor who opens
the Site Editor finds the same menu there. Links are written as custom URLs
rather than object references, because the parts are files and cannot know
post or term identifiers in advance.

### D23. Nothing on the front page comes from page content

The front page is entirely template. Rendering page content beneath a
templated hero invites duplication the moment someone edits the page, which
is exactly what happened. The Home page exists only so WordPress has a static
front, and it is seeded empty.

### D24. The starter guide is implemented, not appended

The Google Search Engine Optimization Starter Guide governs the theme's
search-facing behaviour. Its practices are implemented in `inc/seo.php` and
the templates rather than left to a plugin: unique titles, a description per
page, breadcrumbs with structured data, search results kept out of the index,
robots directives, sitemap inclusion, a useful 404, one H1 per page with
entries as H2, and descriptive anchor text. The description tag yields to an
installed SEO plugin, because two descriptions on a page is worse than none.

Titles are sentence case throughout. A listicle title capitalises its first
word and proper nouns only.

### D25. Updates migrate; they do not wait for a reinstall

`boi_maybe_install()` runs on every version change. It creates the votes
table, then re-runs population if the site was populated before. Population
is idempotent, so it creates only what is missing, and `boi_migrate_seed()`
then corrects records an earlier seed left behind: term parents and
descriptions, and the title, excerpt and section of each seeded post. A seed
fix therefore reaches an existing site on the next update with no action from
the owner.

### D26. Two widths, and the chrome uses the wide one

The reading column is 46rem, wide enough for prose without lines running
long. The wide band is 76rem, and the header, footer, front-page grids,
archives and the featured lead all align to it. The parent's 645px column was
never meant to carry a site's chrome, and inheriting it was what made the
site feel confined.

### D27. The front page carries modules that do not depend on post count

Four articles cannot fill a front page, and the front page should not look
empty while the archive grows. Alongside the lead and the latest grid it
carries a mission band, section cards that describe each section and show
its latest piece, a Reflections strip that appears once essays exist, and a
Follow module. Each earns its place with no posts behind it, so the page
reads as a publication on day one rather than as a grid waiting to fill.

### D28. The secondary pages ship with content, and the contact form needs no plugin

About, FAQ, Contact, Sources and standards, and Privacy are seeded with real
prose rather than placeholders, because a site with an empty About page is
not launched. The contact form is native: nonce, honeypot, a per-address rate
limit, and `wp_mail()`. It stores nothing, which is what the Privacy page
promises, and it means one fewer plugin to keep updated.

### D29. The front page borrows the house rhythm from Abrahamic

The front page is a run of full-bleed bands, each with one job and a centred
head of label, title and subtitle, alternating white, tint and dark. The
pattern is taken from the Abrahamic theme, where fifteen such bands give a
long page room without monotony. Features borrowed from Kolofon, editor's
picks, an HTML site map and a print stylesheet, sit alongside. Every module
renders from live data and hides itself when it has nothing to show, so no
band can advertise content that does not exist.

### D30. Images come from Commons, are verified, and never depict a prophet

Every bundled image is taken from Wikimedia Commons and accepted only when
its own licence metadata reads public domain or CC0; a file is never assumed
to be free. A second rule sits above the licence: no image depicts a prophet.
Articles about Jesus, Abraham, Moses or Muhammad are illustrated by
manuscripts, places and objects. Credit is shown beneath every image and
collected on the Sources page, although neither licence requires it, because
a site built on citing its sources should cite its pictures too.

### D31. The site does not name itself as apologetics

Public copy never labels the site, its editor or its sections as
apologetics. The work speaks for itself: objections stated and answered.
Articles may still describe a critic's argument as polemic, since that
describes the opponent and says nothing about the site.

### D32. Questions are answered once, anonymously, and linked both ways

The site answers the questions its critics raise without naming anyone who
raised them. A question is phrased as it is asked and answered in a few
sentences, and every answer links to the article that argues it in full.
The list in `inc/questions.php` is the only copy: the front-page band and
each article's panel read from it. The answers live on the front page, with
an anchor on every question; a separate page would only repeat them. An answer
corrected there is corrected everywhere, including the Markdown export used
for replies elsewhere.

FAQPage markup is emitted on the front page because the answers are there. Google limits FAQ rich results to a narrow class of sites,
so the markup should not be expected to produce them.

### D33. The site has a reading order

Twenty-four articles arranged by section alone read as islands. The reading
order in `inc/reading.php` gives them a sequence in seven parts, and four
things follow it: the Read next card, the Start here path, the hero, and the
seeded publish dates. Sections remain the way to browse; the order is the way
to read. A new article is placed in the order when it is added.

### D34. No contractions in any English

The owner's rule excludes contractions from all English the theme contains,
with every word spelt out in full: articles, answers and seeded pages, and
equally the interface text, settings descriptions, code comments and
documentation. It overrides the anti-AI voice skill, which otherwise permits
them. A new article is checked
for contractions, contrastive negation, coordinated triads and anaphoric
runs before it is seeded.

### D35. No newsletter

The site offers no mailing list. Readers follow by RSS or by the channels
set on the Display tab, shown in the Follow links at the foot of the front
page. The privacy position stays simple as a result: the site collects no
email addresses except those a reader sends through the contact form.

### D36. Justified running text at 1.5

Running text is justified with hyphenation, at a line spacing of 1.5.
Headings, navigation, labels and centred elements keep their alignment, and
headings keep their tighter display leading. The rules live in a stylesheet
loaded last, so a later component stylesheet cannot quietly undo them.

### D37. The footer carries the mark

The footer is the one place every page shares, so it states the identity:
the mark's descending bars, the wordmark at display size, the site's promise,
and the reading order numbered in the countdown style. A footer of plain
link columns could belong to any site; this one could belong only to this
one.

### D38. Layout is tested on real WordPress output

Responsive checks run against a working WordPress install in a real browser,
never against mockups, which drifted from the theme twice. The standard at
every width from 320 to 1920px: no sideways scrolling, nothing past the screen
edge even where clipping hides it, no text under 12px, and phone tap targets
of at least 24px.

### D39. Structured data follows Google's supported list

The site emits the structured data features Google Search supports that fit
it: Article, Breadcrumb, Image metadata and Organization, plus WebSite. Each
page's entities form one graph joined by @id. Image metadata is built from the
same Commons credits shown beneath each image, so the licence a reader sees
and the licence Google reads are one record. Markup Google no longer rewards,
FAQ and list carousels, stays only because it describes its page accurately.

### D40. Three plugins, rebuilt inside the theme

Unlist Posts & Pages, Pretty Search Permalinks and WPS Hide Login are
rebuilt natively, in keeping with D1, and credited in docs/CREDITS.md. Each
was rebuilt to suit the theme's own structure, never copied in whole:

- Unlisting filters at pre_get_posts, since the plugin's posts_where filter
  is skipped by get_posts(), which builds most of the theme's modules.
- Search redirects keep every query argument, since the theme's search
  filters travel as arguments and the plugin dropped them.
- The login address intercepts at wp_loaded, since a theme loads after the
  plugins_loaded hook the plugin used.

Each feature stands aside while its original plugin is active and imports
that plugin's saved settings. The login address is off by default, refuses
any address that would not work, and yields to BOI_HIDE_LOGIN false in
wp-config.php. Because it belongs to the theme, activating another theme
restores wp-login.php; the failure mode is an open door, never a locked one.

### D41. The login page belongs to the site

The login page is the first thing an editor sees and the only part of the
admin a visitor might reach. The whole page carries the site's identity: the
manuscript background covers the screen, and the bars, the wordmark and the
form sit in one centred column, so arriving there feels like arriving at the
site. It keeps to WordPress's own markup and hooks, adding a
panel and a heading and restyling the rest, so core updates to the login
page keep working.

### D42. The hero carries an image, chosen for legibility

The front-page hero has a background image under a navy overlay, darkest
behind the headline. The default is the Birmingham Quran leaves, chosen after
testing six images for legibility behind the headline. It shows Arabic
script, a deliberate exception to D9's neutral chrome that the owner
accepted; the Hubble Deep Field is the neutral alternative, one setting away
on the Display tab. The same image carries the login page.

### D43. Icons come from one pack, inlined in the text colour

Share and follow icons come from the Minimalist Social & Platform Icons Pack
(GPL), bundled as SVG files and inlined filled with currentColor, so each
takes the colour of the text around it in either appearance and costs no
extra request. Icons whose licence would require a visible credit are left
out; icons the pack lacks are drawn to its grid.

### D44. Seeded images never depend on the media library

Every seeded article's image ships with the theme. The copy into the media
library runs in the background and can lag or fail on hosts that restrict
uploads, so wherever an attached image is missing, the bundled copy stands in:
in cards, on the article, in its credit, in the hero and in the structured
data. The empty-image placeholder is a plain element, never a link, since the
image block wraps its output in a link of its own.

### D45. The theme compares content as WordPress stores it

WordPress changes content on saving: it removes one level of backslashes and
rewrites the formatting of block settings. So every save slashes its data
first, and every fingerprint is taken from the saved copy after passing
through WordPress's own block parser and serialiser. Where the theme cannot
tell whether the owner edited an article, it leaves the article alone and
asks; an update never overwrites what it cannot prove it wrote.

## Asset conventions

CSS in `assets/css/`, JavaScript in `assets/js/`, fonts in `assets/fonts/`,
icons in `assets/icons/`, images in `assets/images/`; one file per concern.

- Loaded on every page: `theme-toggle`, `navigation`, `front`, `footer`,
  `print`, `scripts`, `listicle`, and `typography`, which loads last so its
  rules on justification and line spacing hold everywhere. The header,
  footer and article cards appear site-wide, so their rules do too.
- Loaded where needed: `search` on search results, and `voting` on articles
  with a list when voting is on. Of the scripts, `theme-toggle` runs on every
  page, `questions` on the front page, and `voting` where its stylesheet
  loads.
- The login page loads `login` alone, with its own font declarations, since
  it does not receive the theme's global styles.
- The editor loads `editor-listicle` and `editor-typography`; the settings
  screen loads `admin-settings`.

## Naming

Prefix `boi_` for functions, `boi-` for CSS classes and handles, `bestofislam/`
for block names, `bestofislam` for the text domain. Package archives follow
`[theme name]-[version].zip`.
