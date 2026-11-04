# Best of Islam

A child theme of Twenty Twenty-Five (1.5) for bestofislam.org, a publication of
numbered arguments: each article states the objections to Islam in their own
terms and answers them from the Quran, the hadith and the historical record.

Every feature lives in the theme, with no plugins required. See `docs/SSOT.md`
for the decisions behind the design, `CHANGELOG.md` for the history,
`docs/UPGRADING.md` for updates and `docs/CREDITS.md` for credits.

## What it provides

### Content

- **Listicle blocks.** A `Listicle` container and a `Listicle Entry` child block, both rendered on the server, so rank numbers and tallies always reflect the current state. Numbering counts down or up, and renumbers itself when entries are added, removed or reordered.
- **Seeded content.** 151 published articles across eight sections, each with one featured image and two in the text, from Wikimedia Commons, Pexels and Pixabay, and the About, FAQ, Contact, Sources and standards, Privacy and Sitemap pages. Seeded content belongs to the site's first administrator.
- **Sections.** A hierarchical `listicle-topic` taxonomy at `/topic/<section>/`, with its own template.
- **Categories and tags.** Each seeded article also carries one WordPress category and 3 to 6 tags, listed in `inc/data/seed.json` (`categories`, `tags`, and `category` and `tags` on each article). Every category mirrors a section; the section archive is canonical, so category archives are noindexed, point their canonical link at the section and stay out of the sitemap. A tag archive with fewer than 2 articles is noindexed too. `bin/check-content.py --summary` prints the counts.
- **Reading order.** Seven parts that take a new reader from what Islam teaches to the Muslim world today, with a "Read next" card at the foot of every article and a numbered list of the parts in the footer.
- **Questions answered.** 45 questions with short answers, 14 of them on the front page, each linked to its article: in an accordion on the front page, in a panel on every article, and as FAQ markup.
- **Voting.** Optional, rate-limited visitor voting stored in a dedicated table, with a moderation log. Off by default.
- **Contact form.** A native form with a nonce, a honeypot and a rate limit, sent through `wp_mail`.

### Reading

- **Front page.** Nine full-width bands: the hero, with the Birmingham Quran leaves as its background and the opening entries of the first article; live counts; the newest article; a three-step start; the sections; the questions; the latest articles; editor's picks; and the mission.
- **Articles.** Reading time, a jump list for lists of three or more, share buttons, an author card, related pieces, questions answered, and "Read next".
- **Search.** Relevance-ordered results with links straight to matching entries, filters for kind and section, and readable addresses such as `/search/Paul/`. The base word is set on the Search engines tab or in Settings, Permalinks.
- **Reading progress.** A bar on single posts and pages fills as you scroll. It is drawn in the palette, updates once per animation frame from a passive listener, drops its animation under `prefers-reduced-motion` and sits below the admin bar. It is `aria-hidden`: it repeats what the scrollbar shows, and a live progressbar would be read out on every scroll.
- **Images.** Every image is credited beneath it and on the Sources and standards page. Where the media library copy of a seeded image is missing, the copy bundled with the theme is shown.

### Search engines

- **Following Google's SEO Starter Guide.** Unique titles; meta descriptions of up to 130 characters, including the front and author pages; canonical links on every indexable view; breadcrumbs; a clean heading outline; search results kept out of the index; and an XML sitemap limited to useful pages.
- **Structured data for search and AI answers.** Article on every article, with its image's licence metadata drawn from the Commons credits; Organization and WebSite on the front page, with a 512px logo; BreadcrumbList throughout; ProfilePage on author archives; ItemList on every list; FAQ markup for the questions; a DefinedTermSet for the Glossary. Markup off Google's list stays, because AI answer engines read it. All names are plain text. An image sitemap at `/sitemap-images.xml` lists every article's images.
- **Steps aside for SEO plugins.** Descriptions and canonical links yield to Yoast SEO, Rank Math, All in One SEO and SEOPress. With Rank Math the theme also yields robots, sitemap and structured data, holds Rank Math's titles to the site's title rule, and adds its image credits and the rest of its page graph to Rank Math's graph through the plugin's own hooks, so a page never has two. See `docs/SEO.md`.

### Access

- **Unlisted posts and pages.** A box in the editor sidebar, a Quick Edit box and a Bulk Edit choice hide an item of any public post type from every listing, search, feed and sitemap while it stays reachable by its own link, marked noindex. Post lists have an Unlisted column and an Unlisted view.
- **Custom login address.** Serves the login page from a private address and answers `wp-login.php` and `wp-admin` with the 404 page for anyone not logged in. Off by default. Add `define( 'BOI_HIDE_LOGIN', false );` to `wp-config.php` to switch it off in an emergency.
- **Login page.** A permanent design: the manuscript background across the whole screen, the bars and wordmark, the line "Editorial access only.", and the form in a navy card with a gold top edge.

### Design

- **Identity.** The mark is three descending bars, gold, azure and white, describing the countdown format. The wordmark is set lowercase in Vollkorn. The palette is navy, azure and gold over white.
- **Typography.** Platypi for headings and numerals, EB Garamond for running text, Ysabeau Office for labels and interface text, Vollkorn for the wordmark, all bundled (Platypi, Ysabeau Office and Vollkorn as variable fonts, EB Garamond as five static WOFF2 files). Running text is justified with hyphenation at 1.5 line spacing; short text is left-aligned. Arabic, Hebrew, Greek, Syriac and Akkadian faces are scoped by `unicode-range`; Quranic verses alone set in KFGQPC HAFS Uthmanic Script, served as a woff2 conversion by the owner's decision (SSOT D11). See `assets/fonts/scripts/README.md`.
- **Light and dark.** A toggle in the header switches the whole site; colour tokens live in `theme.json`.
- **Header.** One row above 1300px; two rows below, with the section links scrolling sideways between 600 and 781px; a menu button below 600px. The search field and toggle share one height and style.
- **Footer.** The mark's bars run full width, then the wordmark and the site's promise, then four columns (the reading order, sections, a start column and follow links), then a bar of standing pages.
- **Icons.** 45 single-colour icons from the Minimalist Social & Platform Icons Pack, inlined in the text colour, for share buttons and follow links. The follow row sits in the footer and goes anywhere else through the `[boi_social_icons]` shortcode or the Follow icons pattern; its colours are CSS variables.
- **Responsive.** Tested on a working install at twelve widths from 320 to 1920px: nothing scrolls sideways or overshoots the screen, no text is under 12px, and every tap target on a phone is at least 24px.

## Settings

Under Appearance, Theme Options, in eight tabs. The screen is a set of cards under a tab list: the tabs switch without a reload (Content and Security load their own page, so their queries run only there) and answer the arrow keys, Home and End; booleans are switches, the image size and the default schema are picked from cards, and the voting rate limit and the alert interval are sliders that print their value. Each tab saves only its own fields, and each opens with a short note on how to use it. Below 782px the rows stack and the tab list scrolls sideways. The styles and script (`assets/css/admin-settings.css`, `assets/js/admin-settings.js`) load on that screen only. Without JavaScript the tabs are links and still work.

- **Display:** rank badges, the entry image size, the hero background, editor's picks, follow channels, the reading progress bar and the contact form recipient.
- **Schema:** the default list markup.
- **Voting:** voting, whether it is limited to logged-in users, the rate limit, and the 20 latest votes.
- **Content:** re-running population, how many images have reached the media library, and any seeded articles that differ from this version, with a choice to update them.
- **Search engines:** meta descriptions, verification codes, and readable search addresses with their base word.
- **Access:** the custom login address, the security headers, and the list of unlisted items.
- **Security:** XML-RPC, application passwords and REST writes from outside the browser switched off, the gambling spam gate, 410 Gone for removed spam, the spam scan and clean-up tools, buttons that revoke every application password and end every session, and the attack reports and security log. Add `define( 'BOI_SECURITY_LOCK', false );` to `wp-config.php` to lift the three locks in an emergency.
- **Errors:** the branded maintenance, database and PHP error pages, email alerts and the minutes between them.

Under Appearance, Contextual Links, in three tabs:

- **Domains:** the sister sites (always approved) and any other domain links may lead to.
- **Phrases:** site-wide `phrase | /path/` or `phrase | https://…` lines, which win over the shared map in `inc/data/network-links.json`. Each article also has its own list in the Contextual links box.
- **Rules:** switches and caps for links to this site (four a page) and to the sister sites (three a page), a new-tab choice for links to other sites, and the pages that never receive links.

## Requirements

- WordPress 6.7 or later (tested on 7.1.1)
- PHP 7.4 or later (tested on 8.3)
- Twenty Twenty-Five 1.5 installed as the parent theme

## Installation

1. Install Twenty Twenty-Five and keep it in the themes directory.
2. Upload `bestofislam-<version>.zip` under Appearance, Themes, Add New, and activate it.
   The zip is built by the Package theme workflow (`.github/workflows/package.yml`), after the image scripts have run. The Images workflow (`.github/workflows/images.yml`) runs `bin/fetch-commons-images.py` and `bin/convert-images.py` on every pull request and commits any change, so images are always AVIF and the Commons swaps are made without anyone running them. To run them before each local commit, run `sh bin/install-hooks.sh` once. The Content checks workflow (`.github/workflows/checks.yml`) runs `bin/check-content.py` on every pull request: it refuses a Glossary slug with a collision suffix (`-2`), a dangling link between entries or to an article, an article whose footnotes do not match its Notes list, and JSON that does not parse.
3. On activation, or on the first page load after an update, the theme creates its votes table and populates the site: the sections, the front page, the Reflections index, the standing pages, the menus and the 151 articles. Images are copied into the media library three at a time as pages load; until then the bundled copies are shown.
4. Configure under Appearance, Theme Options.

### Moving from the plugins the theme replaces

Unlist Posts & Pages, Pretty Search Permalinks, WPS Hide Login and Reading Progress Bar are rebuilt inside the theme. While any of them is active, the theme's version of that feature stands aside. Deactivate the plugin, and the theme takes over, importing the plugin's saved settings. The custom login address still needs switching on in the Access tab.

## Authoring a listicle

Insert the **Listicle starter** pattern, or add a `Listicle` block and fill its entries. Each entry takes a title, an optional image, an optional objection stated in the objector's terms, an optional outbound link, and free block content for the answer. Assign the article to a section.

## File layout

```
bestofislam/
├── style.css            Theme header
├── functions.php        Bootstrap and asset loading
├── theme.json           Palette, typography, spacing and listicle tokens
├── screenshot.png       1200 by 900 theme screenshot
├── inc/                 One file per concern:
│                        appearance, blocks, contact, contextual-links,
│                        features, front, glossary-links, icons,
│                        images, login, login-screen, questions, reading,
│                        reflections, schema, schema-graph, search,
│                        reading-progress, search-permalinks,
│                        sections, security, security-detector, seo,
│                        settings, setup, structured-data,
│                        taxonomy, unlist, voting
├── inc/data/            network-links.json, the shared map of phrases to
│                        inner pages, identical in all seven themes;
│                        seed.json (topics, categories, tags, articles, pages),
│                        questions.json and image-credits.json. Seed data
│                        is JSON, read and never executed
├── blocks/              Block definitions and editor scripts
├── templates/           404, archive, front-page, home, page, search,
│                        single, single-listicle, tag, taxonomy-listicle-topic
├── parts/               header, footer
├── patterns/            Listicle starter, Brand lockup, Follow icons
├── styles/              Style variations
├── content/             The 36 seeded articles as block markup
├── assets/css/          admin-settings, editor-listicle, editor-typography,
│                        footer, front, listicle, login, navigation, print,
│                        reading-progress, scripts, search, theme-toggle,
│                        typography, voting
├── assets/js/           admin-settings, questions, reading-progress,
│                        theme-toggle, unlist-admin, voting
├── assets/fonts/        Platypi, Vollkorn, Ysabeau Office, EB Garamond; script fonts guide
├── assets/icons/        45 SVG icons and their licence
├── assets/images/       mark.svg, mark-solid.svg, logo-512.png, and
│                        featured/ and inline/ with the article images, in AVIF (run bin/convert-images.py before a release)
└── docs/                SSOT, UPGRADING, CREDITS
```

No build step is required. Editor scripts use `wp.element.createElement` directly, without JSX.
