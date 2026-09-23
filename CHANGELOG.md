# Changelog

All notable changes to the Best of Islam child theme are recorded here. The
format follows Keep a Changelog, and the project adheres to semantic
versioning.

## [1.29.1] - 2026-09-20

### Added

- Comparative Religion: 5 answers to the claim that Haman is a Quranic mistake, answering the standard orientalist objection that Haman was borrowed anachronistically from the Book of Esther. Draws on the Egyptian *ḥmn-ḥ* title (overseer of the stone-quarry workers), attested in a 19th Dynasty inscription, as the strongest point of contact with the Quran's account. The theme now ships 16 articles.

## [1.29.0] - 2026-09-18

### Fixed

- Every module added since the first responsive pass now has tablet and phone rules: the featured lead, mission band, Reflections strip, section cards, related pieces, share links, author card, contact form, breadcrumbs and the footer grid. A new step at 1024px moves the header tools cluster onto its own row so the navigation, search and toggle never crowd the wordmark on a landscape tablet.
- The overlay menu WordPress opens on phones now renders as a plain stacked list with full-width tap targets instead of a column of pills.
- Contact form inputs are 16px on phones, which stops iOS zooming the page on focus.
- The breadcrumb's current-page label truncates on phones instead of wrapping to three lines.
- A global guard keeps images, SVG and video within their container, and the document clips horizontal overflow, so nothing can push the page sideways.

## [1.28.0] - 2026-09-18

### Added

- `screenshot.png` at 1200 by 900, rendered from the theme's own palette and bundled fonts, so the theme has a tile in Appearance, Themes.

## [1.27.0] - 2026-09-18

### Fixed

- The logo mark rendered at zero size. Its sizing rule was cut from the stylesheet when the section-card styles were rewritten in 1.21. Restored.

### Added

- Secondary pages, seeded with real content and linked from the footer: About, Frequently asked questions, Contact, Sources and standards, Privacy.
- A contact form with no plugin: nonce, honeypot, three messages an hour per address, delivered through `wp_mail()` to the address on the Display tab or the site administrator. Nothing is stored on the site.
- Comparative Religion brought to 8 articles with five new pieces: why Allah of the Quran is the God of the Bible; why Muslims believe Jesus is not God; manuscript transmission compared; six shared prophets and where the accounts diverge; seven verses read differently. The theme now ships 15 articles.
- The About, FAQ and Contact pages are added to the Site Editor navigation post on migration.

## [1.26.0] - 2026-09-18

### Added

- Six further articles, seeded and published on update, so every top-level section holds at least one piece:
  - Belief & Practices: The 5 pillars, explained without the jargon; Who is Allah? 5 misunderstandings about the name itself.
  - Faith & Reason: 5 reasons why the universe demands a creator; Why morality is impossible without God: 4 fatal flaws in secular humanism.
  - Science: 6 instruments invented in the Islamic world that science still uses.
  - History: 7 libraries of the Islamic world, and what happened to each.
- The theme now ships 10 articles, 52 entries, across 5 of the 6 sections.

## [1.25.0] - 2026-09-18

### Fixed

- The image placeholder never rendered on the front page, and the featured lead had no media, because the fallback skipped every singular view and a static front page is singular. It now skips only the post being read, in its own hero slot.
- The update routine ran only on `admin_init`, so an owner who loaded the front end after updating saw the old seed until they opened the dashboard. It now runs on `init` as well.

### Added

- Section cards on the front page carry the section description, the piece count, and the latest title in that section, so a section with no pieces yet still explains itself.
- Mission band beneath the lead: what the site is for, in a navy panel with a link to the About page.
- Reflections strip: the three most recent essays, rendered only when there are any.
- Follow module: RSS by default, plus YouTube, X, Facebook and Telegram when a link is set on the Display tab.

## [1.24.0] - 2026-09-18

### Fixed

- The page was 645px wide. The child inherited the parent's reading column for everything, header and footer included, which stacked the navigation under the wordmark and confined every grid to a narrow strip. The child now sets a 46rem reading column and a 76rem wide band, and the header, footer, front page, grids and archives all use the wide band.
- Seed fixes never reached an existing install, because population ran only on first activation. Every theme update now re-runs population, which is idempotent, and then migrates earlier records: terms move to the parent the current seed declares, seeded posts take the current title and excerpt, and their section assignment is corrected. Hello world and Sample Page are trashed on the same pass.

### Added

- Featured lead on the front page: the most recent piece as a wide two-column card, with the grid below it starting from the second post.
- Reading time in the byline of every post.
- Share links beneath every post: X, Facebook, WhatsApp, Telegram, email. Plain links, no scripts, no tracking.
- Author card beneath every post, with avatar, name and biography.
- More in this section: up to three related pieces from the same section beneath every post.
- Search field in the header, hidden on tablets and phones where the search page serves instead.
- A three-column footer: site and tagline, sections, site links, with a rule and a notice beneath.

## [1.23.0] - 2026-09-18

### Fixed

- Appearance toggle. The current-state glyph was drawn in the page base colour, which on the navy header band is white, and the block covering it is also white, so the current state vanished and only the inactive icon showed. That made the control read as a target rather than a state. The toggle now carries its own foreground and background pair, set to white and navy inside the header and footer bands, and the filled cell's glyph is drawn in the opposite colour so it is always visible.
- Inactive cells are dimmed rather than full strength, so the filled cell is unambiguously the current one.
- Control grew slightly, to 4.5rem by 2.125rem, for balance against the pill navigation beside it.

## [1.22.0] - 2026-09-18

### Changed

- Every title is sentence case. Seeded post titles, entry titles, the 404 heading and the pattern titles now capitalise only the first word and proper nouns.
- Entry titles are H2 rather than H3, so a listicle's outline is the article H1 followed by one H2 per entry, as the guide's heading advice describes.
- The entry link's default label is "Read the full treatment" rather than "Read more", since anchor text should describe the destination.

### Added, following the Search Engine Optimization Starter Guide

- Unique page titles: the front page carries the site name alone, section archives carry the section name, and a spaced pipe separates title parts.
- A description meta tag on every page, drawn from the excerpt on posts and the term description on archives, capped at 160 characters. The theme steps back automatically when Yoast, Rank Math, AIOSEO or SEOPress is active, so no page carries two.
- Breadcrumbs on every view except the front page, with BreadcrumbList structured data. The trail runs Home, parent section, section, title.
- A custom 404 template that returns the 404 status, offers search, lists every section, and shows recent pieces, so a broken link lands on something useful.
- Search results and paginated archives are marked noindex, and the search endpoint is disallowed in robots.txt, since the guide is explicit that result pages should not be crawled.
- The section taxonomy is included in the core XML sitemap, and the users provider is removed from it.
- A Search engines tab in settings with the description toggle and a summary of what the theme handles.

## [1.21.0] - 2026-09-18

### Fixed

- Navigation. Block themes read menus from the `wp_navigation` post type, and the seed was creating a classic menu the navigation block never reads, so an install showed the WordPress default links. The header and footer parts now carry their links directly, present on activation, and the seed creates a `wp_navigation` post named Primary for the Site Editor.
- Front page rendered its hero twice: once from the template and once from the Home page content, which also carried a second grid. The template no longer renders page content, and the Home page is seeded empty.
- Three seeded articles were filed under `comparative-religion`, a term the seed never created, so they rendered without a section. Comparative Religion is now a top-level section.
- The Sections row used the core categories block, which hides empty terms and prints the count outside the link. A `bestofislam/sections` block shows every top-level section from day one, with counts that include child terms.
- Hello world and Sample Page are trashed on population.

### Added

- Card fill and border on every query-grid item, so grids read as a structure rather than a column of text.
- A branded placeholder in the featured-image slot for posts without one, so a grid never has holes.

### Changed

- Arts & Culture is nested under History, keeping seven sections of content within six navigation items, as the content plan recommended.

## [1.20.0] - 2026-09-18

### Fixed

- The palette lived only in the Best of Islam style variation, so a default install inherited the parent's yellow, purple and near-white scheme. `accent-5` resolved to `#FBFAF3`, which is why standfirsts and section labels rendered white on white. The palette, the base styles and the type assignments are now declared in the child's own `theme.json` and apply without selecting anything.
- The theme shipped no header or footer, so the navy band, the wordmark and the appearance toggle existed only in the previews and never in an install. `parts/header.html` and `parts/footer.html` now ship with the brand lockup, the navigation, the toggle, and the band colours.
- Query grids had no styling at all, so the front page, archives and index rendered as unstyled lists. Cards, terms, titles, excerpts and featured images are now styled, with images cropped to a consistent 16:10 so a post without one keeps its place in the grid.

### Changed

- The layout stylesheet loads on every view, since the header and footer parts depend on it.

## [1.19.0] - 2026-09-18

### Added

- Navigation items render as rounded pill buttons, with a tinted fill and a hairline border appearing on hover and on keyboard focus.
- The item for the section the reader is in stays filled, so the navigation shows position rather than only offering links.
- Submenu panels take the card radius, and the items inside them take the small radius.
- `assets/css/navigation.css`, loaded on every view and in the editor.

### Changed

- Hover and border colours are derived from the surrounding text with `color-mix` rather than named, so one rule set covers the navy band, a white ground, and the dark appearance.

## [1.18.0] - 2026-09-18

### Changed

- Wordmark set lowercase in Vollkorn at weight 800 with -0.035em tracking, replacing the Platypi setting. The lowercase heavy serif reads as a mark rather than as another heading in the header band.
- The wordmark scales with the viewport between 1.4rem and 1.8rem, dropping to 1.25rem on phones.

### Added

- Vollkorn bundled as a variable woff2, declared in `theme.json` and in the primary style variation, so the wordmark holds whichever variation is active.

## [1.17.0] - 2026-09-18

### Added

- `front-page.html`: hero, a six-post latest grid, and a section list, with the front page's own content rendered beneath so the page stays editable.
- `single.html`: section term, title, standfirst, byline and date, featured image, content, tags, previous and next links, and comments.
- `page.html`: title, featured image, content, with no query furniture.
- `archive.html`: title, term description, and a reflowing card grid with pagination. Covers date, author and category archives.
- `tag.html`: a Tag eyebrow above the term name, then results as stacked cards rather than a grid, since tag archives are usually short.
- Styles for the front-page section list and the previous and next links.

### Changed

- The layout stylesheet now loads on the front page, archives, tag archives and singular views, since those templates use its classes.

## [1.16.0] - 2026-09-18

### Fixed

- Entries had a single breakpoint at 600px, so between 601px and roughly 820px the rank column held its full width while the text column was squeezed. A tablet step narrows the numeral column first, then the phone step drops it.
- On phones the numeral now sits above the title at label size, so it stays visible while the reader is inside a long entry.
- Card grids on the Reflections index and the section archives used a fixed three-column count, which held three columns on a tablet. They now take a minimum column width and reflow.
- Wide content inside an entry, tables and preformatted blocks, scrolls inside its own box, so the page body never scrolls sideways.
- Long unbroken words in entry and jump-list titles now break instead of overflowing.

### Changed

- The vote control takes a 2.75rem minimum height on phones, meeting the touch target minimum.
- The appearance toggle grows slightly and drops its text label on phones.
- Search chips and result cards tighten their padding at each step.

## [1.15.0] - 2026-09-18

### Added

- Search template, replacing the parent default. Results render as cards carrying the section term, title and excerpt.
- Entry-level matching. When a listicle contains entries matching the query, up to three are listed beneath the result and link straight to that entry's anchor, so the reader lands on the answer instead of the top of the article.
- Relevance ordering. An exact title match outranks a partial title match, which outranks an excerpt match, which outranks a body match, with date breaking ties. WordPress orders search results by date alone.
- Filters for kind (everything, lists, reflections) and for section, rendered as chips that preserve the current query. Both are read from the URL, so a filtered search is linkable and bookmarkable.
- `assets/css/search.css`, loaded only on the search results page.

### Changed

- Search is restricted to posts and pages, so attachments no longer surface.

## [1.14.0] - 2026-09-18

### Fixed

- Platypi and Ysabeau Office were declared only inside the Best of Islam style variation, so on any other variation the fonts never loaded and every `--wp--preset--font-family--*` reference fell back to the parent stack. Both families are now declared in the child `theme.json` and are available whichever variation is active.
- The child re-declared `templateParts` with only header and footer, which replaced the parent's list and dropped its other five parts from the registry. The key is removed, so the parent's declarations stand.

### Verified against Twenty Twenty-Five 1.5

- Every template part slug referenced by the child exists in the parent.
- Every colour and spacing preset referenced resolves against the parent palette and spacing scale.
- Every bundled font file named in `theme.json` is present.
- `templates/home.html` overrides the parent template of the same name; `single-listicle.html` and `taxonomy-listicle-topic.html` are additions.

## [1.13.0] - 2026-09-18

### Added

- Reflections and listicles are now separate streams. A post carrying the listicle block is flagged as a listicle in post meta on save, and the Reflections index excludes them, leaving essays and shorter pieces.
- `boi_backfill_listicle_flags()` classifies posts that predate the flag, and runs at the end of population.

### Changed

- The Reflections standfirst names what the index holds and points readers to the section archives for the lists.
- Section archives are untouched by the filter, so a reader browsing a section still sees everything filed there.

## [1.12.0] - 2026-09-18

### Changed

- The posts index is called Reflections, in the page title and in the primary menu.

## [1.11.0] - 2026-09-18

### Added

- Posts index. An Articles page is created on activation and assigned as the WordPress posts page, giving the site a single URL listing every listicle with pagination.
- `templates/home.html`, the block template for that index: a three-column grid carrying the featured image, section term, title and excerpt.
- Articles sits in the primary menu between Home and the sections.

### Fixed

- The page seeder now casts page content to a string, so a page defined with empty content is still created. The Articles page needs none of its own, since the template supplies the query.

## [1.10.0] - 2026-09-18

### Added

- Logo mark: three rounded strokes of descending length inside a rounded frame, drawn rather than typeset. The form is the countdown the site publishes, and it holds at 18px.
- Brand lockup pattern, pairing the mark with the site title, for insertion into the header template part.
- Two SVG files in `assets/images/`: `mark.svg`, which inherits its colour from the surrounding text so it reverses on the navy band, and `mark-solid.svg` for the site icon and share cards.
- `.boi-brand` styles.

### Changed

- Site identity is no longer the site title set in Platypi with tight letter-spacing, which was a placeholder.

## [1.9.0] - 2026-09-18

### Changed

- Palette rebuilt on navy, azure and gold over white, carrying the colour identity of the Bismika Allahuma property. Replaces the paper and terracotta scheme, which was too close to the other sites in the group.
- Corners rounded throughout. A `radius` token scale (6px, 10px, 16px, pill) is declared in `theme.json` and consumed by cards, media, quotes, the jump list, buttons and the appearance toggle.
- Entries sit in filled cards on a pale blue ground, replacing the rule-separated rows.
- The objection block takes gold as its accent and sits on a white fill inside the card.
- The Night variation and the toggle's dark token set both moved to the deep navy values, so the reader-facing switch and the editor-facing variation still agree.

## [1.8.0] - 2026-09-18

### Added

- Four finished articles ship with the theme and publish on activation: the religion of peace piece for The Muslim World, and the poisoning, al-Zutt, and Islamic Dilemma pieces for Comparative Religion.
- `content/` directory holding each article as block markup, loaded by `boi_load_article()`. Prose lives in files an editor can revise, and out of PHP.

### Changed

- Seed listicles publish rather than draft, since they carry finished content.
- Each seed declares its own slug, so the published URL is fixed by the theme instead of derived from the title.
- Skeleton seed definitions and the inline block builder removed, replaced by the file loader.

## [1.7.0] - 2026-09-18

### Added

- Faith & Reason section, holding argument and rebuttal content. Separating it from Belief & Practices keeps explanatory material for readers inside the faith apart from argumentative material addressed to readers outside it, since the two serve different search intents.
- Term descriptions on every seed section, rendered in the archive header by the taxonomy template.

### Changed

- Section scheme settled at six top-level terms: Belief & Practices, Faith & Reason, Science, History, Arts & Culture, The Muslim World, with Islamophobia beneath the last.
- The three argumentative seed listicles reassigned from Belief & Practices to Faith & Reason.

## [1.6.0] - 2026-09-18

### Changed

- Seed topic terms now follow the established Best of Islam section scheme: Belief & Practices, Arts & Culture, History, Science, and The Muslim World, with Islamophobia nested beneath the last.
- Seed listicles reassigned to the new sections.
- The primary menu carries top-level sections only. Child terms are reachable from their parent archive rather than crowding the navigation.

### Added

- Parent and child support in the seed routine. Topic definitions now declare an optional parent slug, and parents are created before their children.

## [1.5.0] - 2026-09-18

### Changed

- Seed topic terms broadened from article-level subjects to publication sections: Theology, Scripture, Science, History, Ethics, Comparative Religion. Narrow terms produced single-post archives too thin to rank and unusable as navigation. The taxonomy remains hierarchical, so narrower terms may be nested beneath these where a section grows large enough to warrant it.
- Seed listicles reassigned accordingly, and the primary menu now carries the six sections.

## [1.4.0] - 2026-09-18

### Added

- Appearance toggle block. A two-cell hairline frame with a solid block that moves between the cells, matching the square-cornered identity rather than the conventional sliding pill.
- Dark token set applied through `data-theme` on the document element, mirroring the Best of Islam Night variation. Overrides sit on the preset tokens, so every block follows, not only the listicle.
- Pre-paint script in the document head that applies the stored preference before the body renders, so the page never flashes light on its way to dark.
- System preference is followed until the reader states a choice, after which the stored choice wins. Transitions respect `prefers-reduced-motion`.

## [1.3.0] - 2026-09-18

### Added

- Content auto-population on first activation: four topic terms, a front page set as the static home, an about page, the primary navigation menu built from the topics, and four draft listicles constructed from the block set with entry titles and objections in place.
- Content tab under Appearance, Listicles, with a manual re-run. Population is idempotent, so a second run adds only what is missing.

## [1.2.0] - 2026-09-18

### Added

- Objection field on the entry block, rendered as a set-off blockquote above the response, with optional attribution. The format states the argument being answered before answering it.
- Automatic jump list at the head of any listicle of three or more entries, built from the entry titles, with a fragment identifier on each entry. Anchored outlines compete for the list-format result.
- Multiscript typography layer covering Quranic Arabic, general Arabic, Hebrew, Greek, Syriac, and Akkadian, each face scoped by `unicode-range` so nothing downloads on a page without those glyphs. Helper classes govern direction and scale.
- Jump list toggle on the container block.

### Changed

- Voting now defaults to off. Argumentative content attracts coordinated voting, and a public tally reads as a verdict on the argument rather than a measure of interest. The machinery remains available under Appearance, Listicles.

### Removed

- Star rating on entries, and the `ItemListReview` schema option with it. Review and Rating markup attached to a theological proposition invites a structured-data penalty, since a proposition is not a reviewable entity. Schema output is now `ItemList` or none.

## [1.1.0] - 2026-09-18

### Added

- Distinct visual identity for bestofislam.org, deliberately independent of the bestofislam.com property.
- Two bundled style variations: Best of Islam (paper) and Best of Islam Night.
- Platypi and Ysabeau Office bundled as woff2 variable fonts, declared in the primary variation.
- `card.rule` design token for the hairline separator between entries.

### Changed

- Listicle layout reworked from enclosed cards to rule-separated editorial rows.
- Rank numeral rendered as a display figure in the accent colour, replacing the filled circular badge.
- Rating indicator reduced to a hairline bar.
- Editor styles aligned with the front-end layout.

## [1.0.0] - 2026-09-18

### Added

- Initial release as a child theme of Twenty Twenty-Five 1.5.
- `bestofislam/listicle` container block with countdown or ascending numbering and per-list schema selection.
- `bestofislam/entry` block with title, image, rating, outbound link, and free block content.
- Server-side rendering for both blocks, so rank and tally reflect current state.
- `ItemList` JSON-LD derived from the parsed block tree, with optional per-entry `Review` and `Rating`.
- Hierarchical `listicle-topic` taxonomy served at `/topic/<term>/`.
- Block templates for the topic archive and for single listicles.
- Visitor voting: REST endpoint, dedicated votes table, per-visitor hash, ten-minute rate limiting, transient-cached tallies.
- Tabbed settings screen under Appearance, Listicles, covering Display, Schema, and Voting, with a recent-vote moderation log.
- Listicle design tokens in `theme.json` under `settings.custom.listicle`, plus a bundled style variation.
- Listicle starter block pattern.
