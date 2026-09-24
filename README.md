# Best of Islam

A listicle child theme of Twenty Twenty-Five (1.5) for bestofislam.org.

## What it provides

- **Listicle blocks.** A `Listicle` container and a `Listicle Entry` child block. Both render server-side, so rank numbers and vote tallies always reflect current state rather than saved markup.
- **Automatic numbering.** Countdown or ascending, set on the container. Adding, removing, or reordering entries renumbers the list without editorial intervention.
- **Schema output.** `ItemList` JSON-LD generated from the parsed block tree. Because the graph is derived from the same blocks the visitor sees, it cannot drift from the visible content. Review and Rating markup is deliberately absent.
- **Topic taxonomy.** A hierarchical `listicle-topic` taxonomy at `/topic/<term>/`, with its own block template.
- **Voting.** Rate-limited visitor voting through a REST endpoint, stored in a dedicated table, with a moderation log and a logged-in-only switch. Off by default.
- **Appearance toggle.** A block placeable in the header, switching the page between light and dark. The dark palette mirrors the Night style variation.
- **Article features.** Reading time, share links, an author card, related pieces from the same section, and a featured lead on the front page.
- **Search engine practices.** Unique titles, meta descriptions, breadcrumbs with BreadcrumbList markup, noindex on search results, robots.txt and sitemap handling, a useful 404, and a clean heading outline, following the Google Search Engine Optimization Starter Guide.
- **Search.** Relevance-ordered results with entry-level deep links, plus filters for kind and section.
- **Questions answered.** 26 questions with short answers, each linked to its article, in the front-page band, with an anchor on each, and in a panel on every article linking to its answers.
- **Jump list.** An anchored outline of entry titles at the head of any list of three or more.
- **Multiscript typography.** Arabic, Hebrew, Greek, Syriac, and Akkadian faces scoped by `unicode-range`. Font files are supplied by the site owner; see `assets/fonts/scripts/README.md`.
- **Configurable colour.** All listicle colour, spacing, and sizing tokens are declared in `theme.json` under `settings.custom.listicle` and consumed as CSS custom properties. Two bundled style variations, Best of Islam and Best of Islam Night, recolour the list without editing stylesheets.

## Design

The identity is editorial and secular in its chrome, following the constraint that the site must not read as overtly Islamic at first glance. Religious typography and treatments belong to the content, never to navigation or furniture.

- **Typography.** Platypi for headings and rank numerals, Ysabeau Office for body and interface text, Vollkorn for the wordmark. All bundled as variable woff2.
- **Palette.** Navy, azure and gold over white, carried across from the Bismika Allahuma property. A Night variation moves the ground to deep navy and lifts the accent to sky blue.
- **Layout.** Entries sit in rounded cards on a pale blue fill, with the rank numeral in the left margin as a display figure. Corners follow a four-step radius scale declared in `theme.json`.

## Requirements

- WordPress 6.7 or later
- PHP 7.4 or later
- Twenty Twenty-Five 1.5 installed as the parent theme

## Installation

1. Install and keep Twenty Twenty-Five active in the themes directory.
2. Upload `bestofislam-1.45.0.zip` under Appearance, Themes, Add New.
3. Activate. On first activation the theme creates its votes table, flushes rewrite rules, and populates the site: the section taxonomy, a front page, a Reflections index, the About, FAQ, Contact, Sources and Privacy pages, the primary menu, and twenty-nine published articles.
4. Configure under Appearance, Listicles. The Content tab re-runs population if anything is missing.

## Authoring a listicle

Insert the **Listicle starter** pattern, or add a `Listicle` block and populate its entries. Each entry carries a title, image, an optional objection stated in the objector's terms, an optional outbound link, and free block content for the response. Assign the post to a listicle topic for archive placement.

## File layout

```
bestofislam/
├── style.css               Theme header only
├── functions.php           Bootstrap and conditional asset loading
├── theme.json              Custom listicle tokens, template registration
├── inc/                    taxonomy, blocks, schema, voting, settings, setup, appearance, reflections, search, sections, seo, features, contact, front, images, questions, reading, icons
├── blocks/                 Block definitions and editor scripts
├── templates/              Block templates: front-page, home, single, single-listicle, page, archive, tag, taxonomy, search, 404
├── styles/                 Style variation
├── patterns/               Starter pattern
├── assets/css/             Stylesheets
├── assets/fonts/           Bundled variable fonts
├── assets/images/          Logo mark, and featured/ with 23 Commons images
├── assets/js/              Scripts
└── docs/                   SSOT, upgrading notes
```

No build step is required. Editor scripts use `wp.element.createElement` directly rather than JSX.
