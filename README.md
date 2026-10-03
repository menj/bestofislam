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
- **Seeded content.** 36 published articles with 196 entries across six sections, each illustrated with a verified public-domain or CC0 image from Wikimedia Commons, and the About, FAQ, Contact, Sources and standards, Privacy and Site map pages. Seeded content belongs to the site's first administrator.
- **Sections.** A hierarchical `listicle-topic` taxonomy at `/topic/<section>/`, with its own template.
- **Reading order.** Seven parts that take a new reader from what Islam teaches to the Muslim world today, with a "Read next" card at the foot of every article and a numbered list of the parts in the footer.
- **Questions answered.** 33 questions with short answers, each linked to its article: in an accordion on the front page, in a panel on every article, and as FAQ markup.
- **Voting.** Optional, rate-limited visitor voting stored in a dedicated table, with a moderation log. Off by default.
- **Contact form.** A native form with a nonce, a honeypot and a rate limit, sent through `wp_mail`.

### Reading

- **Front page.** Nine full-width bands: the hero, with the Birmingham Quran leaves as its background and the opening entries of the first article; live counts; the newest article; a three-step start; the sections; the questions; the latest articles; editor's picks; and the mission.
- **Articles.** Reading time, a jump list for lists of three or more, share buttons, an author card, related pieces, questions answered, and "Read next".
- **Search.** Relevance-ordered results with links straight to matching entries, filters for kind and section, and readable addresses such as `/search/Paul/`.
- **Images.** Every image is credited beneath it and on the Sources and standards page. Where the media library copy of a seeded image is missing, the copy bundled with the theme is shown.

### Search engines

- **Following Google's SEO Starter Guide.** Unique titles; meta descriptions of up to 130 characters, including the front and author pages; canonical links on every indexable view; breadcrumbs; a clean heading outline; search results kept out of the index; and an XML sitemap limited to useful pages.
- **Structured data on Google's supported list.** Article on every article, with its image's licence metadata drawn from the Commons credits; Organization and WebSite on the front page, with a 512px logo; BreadcrumbList throughout; ItemList on every list; FAQ markup for the questions. All names are plain text.
- **Steps aside for SEO plugins.** Descriptions and canonical links yield to Yoast SEO, Rank Math, All in One SEO and SEOPress.

### Access

- **Unlisted posts and pages.** A box in the editor sidebar hides an item from every listing, search, feed and sitemap while it stays reachable by its own link, marked noindex.
- **Custom login address.** Serves the login page from a private address and answers `wp-login.php` and `wp-admin` with the 404 page for anyone not logged in. Off by default. Add `define( 'BOI_HIDE_LOGIN', false );` to `wp-config.php` to switch it off in an emergency.
- **Login page.** A permanent design: the manuscript background across the whole screen, the bars and wordmark, the line "Editorial access only.", and the form in a navy card with a gold top edge.

### Design

- **Identity.** The mark is three descending bars, gold, azure and white, describing the countdown format. The wordmark is set lowercase in Vollkorn. The palette is navy, azure and gold over white.
- **Typography.** Platypi for headings and numerals, Ysabeau Office for text, Vollkorn for the wordmark, all bundled as variable fonts. Running text is justified with hyphenation at 1.5 line spacing; short text is left-aligned. Arabic, Hebrew, Greek, Syriac and Akkadian faces are scoped by `unicode-range`; see `assets/fonts/scripts/README.md`.
- **Light and dark.** A toggle in the header switches the whole site; colour tokens live in `theme.json`.
- **Header.** One row above 1300px; two rows below, with the section links scrolling sideways between 600 and 781px; a menu button below 600px. The search field and toggle share one height and style.
- **Footer.** The mark's bars run full width, then the wordmark and the site's promise, then four columns (the reading order, sections, a start column and follow links), then a bar of standing pages.
- **Icons.** 45 single-colour icons from the Minimalist Social & Platform Icons Pack, inlined in the text colour, for share buttons and follow links.
- **Responsive.** Tested on a working install at twelve widths from 320 to 1920px: nothing scrolls sideways or overshoots the screen, no text is under 12px, and every tap target on a phone is at least 24px.

## Settings

Under Appearance, Listicles, in six tabs:

- **Display:** the hero background, editor's picks and follow channels.
- **Schema:** the default list markup.
- **Voting:** voting, and whether it is limited to logged-in users.
- **Content:** re-running population, how many images have reached the media library, and any seeded articles that differ from this version, with a choice to update them.
- **Search engines:** meta descriptions, and readable search addresses with their base word.
- **Access:** the custom login address, and the list of unlisted items.

## Requirements

- WordPress 6.7 or later (tested on 7.1.1)
- PHP 7.4 or later (tested on 8.3)
- Twenty Twenty-Five 1.5 installed as the parent theme

## Installation

1. Install Twenty Twenty-Five and keep it in the themes directory.
2. Upload `bestofislam-1.56.0.zip` under Appearance, Themes, Add New, and activate it.
3. On activation, or on the first page load after an update, the theme creates its votes table and populates the site: the sections, the front page, the Reflections index, the standing pages, the menus and the 36 articles. Images are copied into the media library three at a time as pages load; until then the bundled copies are shown.
4. Configure under Appearance, Listicles.

### Moving from the plugins the theme replaces

Unlist Posts & Pages, Pretty Search Permalinks and WPS Hide Login are rebuilt inside the theme. While any of them is active, the theme's version of that feature stands aside. Deactivate the plugin, and the theme takes over, importing the plugin's saved settings. The custom login address still needs switching on in the Access tab.

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
│                        appearance, blocks, contact, features, front, icons,
│                        images, login, login-screen, questions, reading,
│                        reflections, schema, search, search-permalinks,
│                        sections, seo, settings, setup, structured-data,
│                        taxonomy, unlist, voting
├── blocks/              Block definitions and editor scripts
├── templates/           404, archive, front-page, home, page, search,
│                        single, single-listicle, tag, taxonomy-listicle-topic
├── parts/               header, footer
├── patterns/            Listicle starter
├── styles/              Style variations
├── content/             The 36 seeded articles as block markup
├── assets/css/          admin-settings, editor-listicle, editor-typography,
│                        footer, front, listicle, login, navigation, print,
│                        scripts, search, theme-toggle, typography, voting
├── assets/js/           admin-settings, questions, theme-toggle, voting
├── assets/fonts/        Platypi, Vollkorn, Ysabeau Office; script fonts guide
├── assets/icons/        45 SVG icons and their licence
├── assets/images/       mark.svg, mark-solid.svg, logo-512.png, and
│                        featured/ with 36 Commons images
└── docs/                SSOT, UPGRADING, CREDITS
```

No build step is required. Editor scripts use `wp.element.createElement` directly, without JSX.
