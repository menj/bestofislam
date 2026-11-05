# Best of Islam

A child theme of Twenty Twenty-Five (1.5) for bestofislam.org, a publication of
numbered arguments: each article states the objections to Islam in their own
terms and answers them from the Quran, the hadith and the historical record.

Version 1.100.17. Licensed GNU GPL v3 or later; the full text is in `LICENSE`. Every feature lives in the theme, with no plugins required. See `docs/SSOT.md`
for the decisions behind the design, `CHANGELOG.md` for the history,
`docs/UPGRADING.md` for updates and `docs/CREDITS.md` for credits.

## What it provides

### Content

- **Listicle blocks.** A `Listicle` container and a `Listicle Entry` child block, both rendered on the server, so rank numbers and tallies always reflect the current state. Numbering counts down or up, and renumbers itself when entries are added, removed or reordered.
- **Seeded content.** 152 published articles across eight sections, each with one featured image and two in the text, from Wikimedia Commons, Pexels and Pixabay, and the About, FAQ, Contact, Sources and standards, Privacy and Sitemap pages. Seeded content belongs to the site's first administrator.
- **Sections.** A hierarchical `listicle-topic` taxonomy at `/topic/<section>/`, with its own template.
- **Categories and tags.** Each seeded article also carries one WordPress category and 3 to 6 tags, listed in `inc/data/seed.json` (`categories`, `tags`, and `category` and `tags` on each article). Every category mirrors a section; the section archive is canonical, so category archives are noindexed, point their canonical link at the section and stay out of the sitemap. A tag archive with fewer than 2 articles is noindexed too. `bin/check-content.py --summary` prints the counts.
- **Reading order.** Seven parts that take a new reader from what Islam teaches to the Muslim world today, with a "Read next" card at the foot of every article and a numbered list of the parts in the footer.
- **Questions answered.** 45 questions with short answers, 14 of them on the front page, each linked to its article: in an accordion on the front page, in a panel on every article, and as FAQ markup.
- **Voting.** Optional, rate-limited visitor voting stored in a dedicated table, with a moderation log. Off by default.
- **Contact form.** A native form with a nonce, a honeypot and a rate limit, sent through `wp_mail`.

### Reading

- **Front page.** Nine full-width bands that tell one story (`docs/SSOT.md`, D51): the hero, with the Birmingham Quran leaves as its background and the opening entries of the first article; live counts; then six numbered steps, the questions, the editor's picks, the newest article, the latest articles, a three-step start and the sections; and the mission.
- **Articles.** Reading time, the updated and reviewed dates, an "In short" box of key points with the questions the piece answers, a contents list for articles of three or more sections (a jump list on lists), a next step before the third section, a FAQ style for Details blocks, ruled tables, share buttons, an author card, related pieces, and "Read next".
- **Search.** Relevance-ordered results with links straight to matching entries, filters for kind and section, and readable addresses such as `/search/Paul/`. The base word is set on the Search engines tab or in Settings, Permalinks.
- **Reading progress.** A bar on single posts and pages fills as you scroll. It is drawn in the palette, updates once per animation frame from a passive listener, drops its animation under `prefers-reduced-motion` and sits below the admin bar. It is `aria-hidden`: it repeats what the scrollbar shows, and a live progressbar would be read out on every scroll.
- **Lead and list.** The first page of Reflections, of each section and of each archive opens with the newest piece large, its title on a dark band at the foot of the image under a gold rule, and its credit below. The next five stand in a list beside it, each a 16:9 thumbnail and a title clamped at three lines; the grid below leaves those six out, so pagination does not move. `inc/lead-list.php` draws it as the `bestofislam/lead-list` block from the main query (`boi_lead_list( $posts, $args )`, with `heading`, `heading_id`, `lead_id` and `max`; the block takes `heading` and `leadId`). It uses the front page's image helpers (`boi_lead_image()`, `boi_list_thumbnail()` in `inc/features.php`) and the image-credit block's line (`boi_image_credit_markup()` in `inc/images.php`). Below five pieces, or when the lead has neither a featured image nor a bundled one, it draws nothing and the grid shows alone. Unlisted posts never appear. `assets/css/lead-list.css` loads on the first page of those views (`boi_lead_list_assets()` on `wp_enqueue_scripts`, never from the block, since an enqueue made during rendering would print the theme styles above the core block styles); columns are `minmax(0,3fr) minmax(0,2fr)`, stacked under 782px, and the title sits on `rgb(0 0 0 / .85)` so white type stays above 4.5:1. The front page is unchanged.
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
- **Browser icons.** Until a Site Icon is set, `inc/favicon.php` prints `favicon.svg`, `favicon-32.png`, `apple-touch-icon.png`, `site.webmanifest` and `theme-color` #0D2B4F on `wp_head`, `login_head` and `admin_head`, and answers `/favicon.ico` with `assets/images/favicon.ico` (`Cache-Control: public, max-age=86400`, an ETag and 304; `template_redirect` at priority 0 covers HEAD). The manifest is static, with relative icon paths, so it works in a subfolder. The theme `.htaccess` refuses only dot files, PHP, Markdown and the development folders, so every icon file is served. On a site in a subfolder, such as `example.com/blog/`, browsers still ask for `example.com/favicon.ico`, which WordPress never sees: put a copy of `assets/images/favicon.ico` at the domain root there. The head tags cover the pages either way.
- **Icons.** 45 single-colour icons from the Minimalist Social & Platform Icons Pack, inlined in the text colour, for share buttons and follow links. The follow row sits in the footer and goes anywhere else through the `[boi_social_icons]` shortcode or the Follow icons pattern; its colours are CSS variables.
- **Responsive.** Tested on a working install at twelve widths from 320 to 1920px: nothing scrolls sideways or overshoots the screen, no text is under 12px, and every tap target on a phone is at least 24px.

## Settings

Under Appearance, Theme Options, in eight tabs. The screen is a set of cards under a tab list: the tabs switch without a reload (Content and Security load their own page, so their queries run only there) and answer the arrow keys, Home and End; booleans are switches, the image size and the default schema are picked from cards, and the voting rate limit and the alert interval are sliders that print their value. Each tab saves only its own fields, and each opens with a short note on how to use it. Below 782px the rows stack and the tab list scrolls sideways. The styles and script (`assets/css/admin-settings.css`, `assets/js/admin-settings.js`) load on that screen only. Without JavaScript the tabs are links and still work.

- **Display:** rank badges, the entry image size, the article contents and the mid-article next step (`article_contents`, `article_mid_next`), the hero background, editor's picks, follow channels, the reading progress bar and the contact form recipient.
- **Schema:** the default list markup.
- **Voting:** voting, whether it is limited to logged-in users, the rate limit, and the 20 latest votes.
- **Content:** re-running population, how many images have reached the media library, and any seeded articles that differ from this version, with a choice to update them.
- **Search engines:** meta descriptions, verification codes, and readable search addresses with their base word.
- **Access:** the custom login address, the security headers, and the list of unlisted items.
- **Security:** XML-RPC, application passwords and REST writes from outside the browser switched off, the gambling spam gate, 410 Gone for removed spam, the spam scan and clean-up tools, buttons that revoke every application password and end every session, and the attack reports and security log. Add `define( 'BOI_SECURITY_LOCK', false );` to `wp-config.php` to lift the three locks in an emergency.
- **Errors:** the branded maintenance, database and PHP error pages, email alerts and the minutes between them. The tab shows each file in `wp-content` as installed, outdated, absent, left behind by another theme or another file left alone, and **Install error pages** writes them at once. A copy another theme left behind, marked "safe to delete", is replaced; on a single site the theme's own files are removed when another theme is activated. `maintenance.php` and `db-error.php` answer 503 with `Retry-After`; `php-error.php` answers 500. A file without the marker `Best of Islam error drop-in` is never touched.
- **Server errors:** static 403, 500, 502, 503 and 504 pages for the errors the web server answers before WordPress starts. The theme writes a copy of each into `wp-content/bestofislam-errors/` with the site's own name, language and home address, so the pages work when WordPress sits in a subfolder, and, on a single site, removes them when another theme is activated (on a network they stay; see Multisite). `errors/` holds the copies for a site at the domain root, built by `php bin/build-error-pages.php` from the drop-in template. The Errors tab gives the `.htaccess` lines for the site; at the domain root they read:

  ```
  ErrorDocument 403 /wp-content/bestofislam-errors/403.html
  ErrorDocument 500 /wp-content/bestofislam-errors/500.html
  ErrorDocument 502 /wp-content/bestofislam-errors/502.html
  ErrorDocument 503 /wp-content/bestofislam-errors/503.html
  ErrorDocument 504 /wp-content/bestofislam-errors/504.html
  ```


Under Appearance, Contextual Links, in three tabs:

- **Domains:** the linked sites (always approved) and any other domain links may lead to.
- **Phrases:** site-wide `phrase | /path/` or `phrase | https://…` lines, which win over the shared map in `inc/data/network-links.json`. Each article also has its own list in the Contextual links box.
- **Rules:** switches and caps for links to this site (four a page) and to the other sites (three a page), a new-tab choice for links to other sites, and the pages that never receive links.

## Requirements

- WordPress 6.7 or later (tested on 7.1.1)
- PHP 7.4 or later (tested on 8.3)
- Twenty Twenty-Five 1.5 installed as the parent theme

## Installation

1. Install Twenty Twenty-Five and keep it in the themes directory.
2. Upload `bestofislam-<version>.zip` under Appearance, Themes, Add New, and activate it.
   The zip is built by the Package theme workflow (`.github/workflows/package.yml`), after the image scripts have run. The Images workflow (`.github/workflows/images.yml`) runs `bin/fetch-commons-images.py` and `bin/convert-images.py` on every pull request and commits any change, so images are always AVIF and the Commons swaps are made without anyone running them. To run them before each local commit, run `sh bin/install-hooks.sh` once. The Content checks workflow (`.github/workflows/checks.yml`) runs `bin/check-content.py` on every pull request: it refuses a Glossary slug with a collision suffix (`-2`), a dangling link between entries or to an article, an article whose footnotes do not match its Notes list, and JSON that does not parse.
   The working notes for coding agents, `.claude/CLAUDE.md` and `.claude/FARUQI.md`, stay out of the zip and out of `git archive`. On a production server the theme deletes `.claude` and `.git` with the other development files (see Security); a local or development environment, or `BOI_KEEP_DEV_FILES`, keeps them.
3. On activation, or on the first page load after an update, the theme creates its votes table and populates the site: the sections, the front page, the Reflections index, the standing pages, the menus and the 152 articles. Images are copied into the media library three at a time as pages load; until then the bundled copies are shown.
4. Configure under Appearance, Theme Options.

### Moving from the plugins the theme replaces

Unlist Posts & Pages, Pretty Search Permalinks, WPS Hide Login and Reading Progress Bar are rebuilt inside the theme. While any of them is active, the theme's version of that feature stands aside. Deactivate the plugin, and the theme takes over, importing the plugin's saved settings. The custom login address still needs switching on in the Access tab.

## Authoring a listicle

Insert the **Listicle starter** pattern, or add a `Listicle` block and fill its entries. Each entry takes a title, an optional image, an optional objection stated in the objector's terms, an optional outbound link, and free block content for the answer. Assign the article to a section.

## File layout

```
bestofislam/
├── style.css            Theme header (Version)
├── functions.php        BOI_VERSION, the file list, the upload guard, asset loading
├── theme.json           Palette, typography, spacing and listicle tokens
├── screenshot.png       1200 by 900 theme screenshot
├── readme.txt           For site owners (served: the Theme Options footer links it)
├── CHANGELOG.md         History (served: the Theme Options footer links it)
├── README.md            This file (refused by .htaccess)
├── .htaccess            Refuses dot entries, theme PHP, the dev folders and Markdown
├── .gitattributes       export-ignore list for the release zip
├── inc/                 One file per concern, loaded in this order by functions.php:
│                        util, taxonomy, blocks, schema, voting, settings,
│                        setup, appearance, reflections, search, sections,
│                        seo, icons, features, lead-list, contact, front, inline-images,
│                        images, questions, reading, anatomy, structured-data,
│                        schema-graph, rank-math, unlist, search-permalinks,
│                        reading-progress, login, login-screen, favicon,
│                        glossary, glossary-links, renamed-slugs, error-pages,
│                        error-page-static, lock, housekeeping,
│                        security-headers, security. Loaded by those:
│                        maintenance (first, the upload guard),
│                        contextual-links, glossary-data, security-detector
├── inc/data/            network-links.json (the shared phrase map),
│                        seed.json (topics, categories, tags, 152 articles,
│                        8 pages), questions.json (45), image-credits.json,
│                        schema-entities.json. Read, never executed
├── inc/glossary-data/   a.json to z.json: the 797 Glossary entries
├── inc/*.json           images-data.json, glossary-images.json, related-articles.json
├── content/             The 152 seeded articles as block markup, one JSON file each
├── blocks/              entry, listicle, theme-toggle: block.json and editor scripts
├── templates/           404, archive, front-page, home, page, page-glossary,
│                        search, single, single-listicle, tag, taxonomy-listicle-topic
├── parts/               header, footer
├── patterns/            Listicle starter, Brand lockup, Follow icons, Questions readers ask (FAQ)
├── styles/              Best of Islam, Best of Islam Night
├── assets/css/          One file per concern (front, listicle, glossary, security ...)
├── assets/js/           One file per concern (theme-toggle, voting, questions ...)
├── assets/fonts/        Platypi, Vollkorn, Ysabeau Office, EB Garamond; scripts/ with its README
├── assets/icons/        45 SVG icons and their licence
├── assets/images/       mark.svg, mark-solid.svg, logo-512.png; featured/ and inline/ (AVIF)
├── errors/              Static 403, 500, 502, 503, 504 pages for a site at the domain root
├── bin/                 build-error-pages.php, build-related.py, check-content.py,
│                        convert-images.py, fetch-commons-images.py,
│                        fetch-stock-images.py, make-original-graphics.py,
│                        install-hooks.sh (own .htaccess; export-ignored)
├── tests/               Command-line self-tests (own .htaccess; export-ignored)
├── docs/                SSOT, UPGRADING, CREDITS, SEO, POLEMICS, CONTENT-REVIEW,
│                        SHAMELA-REVIEW (own .htaccess)
├── .github/workflows/   checks.yml, images.yml, package.yml
└── .claude/             Notes for contributors (export-ignored, refused, removed on a live site)
```

No build step is required. Editor scripts use `wp.element.createElement` directly, without JSX.

## Boot sequence

1. `functions.php` defines `BOI_VERSION`, `BOI_DIR` and `BOI_URI`, then hands the full file list (`$boi_need`) to `inc/maintenance.php`.
2. If any file is missing, nothing else loads. Visitors get a 503 page with `Retry-After: 120` and noindex; the admin stays up with a notice naming the missing files. The exception is `inc/maintenance.php` itself: while it is missing, `functions.php` checks the other files and, if one is absent, returns without the 503 page or the notice. The files load inside `try`, so a half-written file (a `ParseError`) gives the same page. The log gets one line per problem every ten minutes. The login screen, AJAX, cron and the command line go on without the page.
3. The `inc/` files load in the order shown above. Each adds its own hooks.
4. On `init` (priority 99) the first request of a new version runs the dev-file clean-up and the `.htaccess` sync, each under the lock in `inc/lock.php`.

## Hooks and filters

The theme's own filters:

| Filter | Effect |
|---|---|
| `boi_author_same_as` | Profile addresses in the author's `sameAs` |
| `boi_cl_guards`, `boi_cl_post_types` | Longer names never linked, so a shorter phrase inside them is not taken ("Ismail al Faruqi"); the post types that get contextual links |
| `boi_contextual_links_enabled`, `boi_contextual_links_map` | Switch contextual links off for one post; change the phrase map |
| `boi_deferred_style_handles` | Styles loaded after first paint |
| `boi_dev_paths` | The paths the live-server clean-up removes |
| `boi_front_title_focus` | The front page title's focus phrase (default "Objections to Islam, answered") |
| `boi_gl_always`, `boi_gl_max`, `boi_gl_stoplist`, `boi_glossary_read_more_max` | Glossary: entries linked at first mention whatever the budget, the link budget, words never linked, and the Read more count on an entry (6) |
| `boi_image_sitemap` | False turns `/sitemap-images.xml` off. Rank Math, or a site hidden from search engines, turns it off anyway |
| `boi_renamed_paths` | Old addresses and the articles they redirect to |
| `boi_seed_renames` | Seeded article slugs that changed, old to current |
| `boi_glossary_retired_slugs` | Glossary entry slugs that changed or merged, old to current |
| `boi_schema_nodes` | The nodes of the page graph. `inc/anatomy.php` uses it to add an `FAQPage` node for an article with FAQ-style Details blocks and to add `.boi-answer` to `speakable` |
| `boi_sec_rest_allow` | REST route prefixes that may write with non-cookie authentication, for example `/jetpack/` |
| `boi_security_headers` | The front-end security headers |
| `boi_title_max_length` | The title tag limit |
| `boi_unlist_enabled`, `unlist_posts_enabled` | Switch the unlist feature off |

`grep -n "add_action\|add_filter" functions.php inc/*.php` lists every hook the theme uses. The ones a host meets most:

| Hook | Callback | Purpose |
|---|---|---|
| `init` (99) | `boi_remove_dev_files_front`, `boi_sec_sync_htaccess_once` | Clean-up and `.htaccess` sync on the first request of a version |
| `admin_init` | `boi_remove_dev_files`, `boi_sec_sync_htaccess` | The same from the admin, the sync at most hourly |
| `switch_theme` | `boi_sec_remove_htaccess`, `boi_error_remove` | Takes the `.htaccess` sections and the theme's drop-ins out |
| `send_headers` | `boi_send_security_headers` | Security headers |

## Constants

Set these in `wp-config.php`. Each is optional.

| Constant | Effect |
|---|---|
| `BOI_SECURITY_LOCK` | `false` lifts the three Security locks (XML-RPC, application passwords, REST writes) without changing the options |
| `BOI_HIDE_LOGIN` | `false` switches the private login address off |
| `BOI_KEEP_DEV_FILES` | `true` keeps the development files on a live server. Wins over `BOI_REMOVE_GIT_DIR` |
| `BOI_REMOVE_GIT_DIR` | `true` forces the clean-up, `.git` included, even in a development environment; `false` keeps `.git` on production while the rest goes |
| `BOI_BLOCK_DIRECT_FILES` | `false` leaves the "Best of Islam: block direct file access" section out of `.htaccess` |
| `BOI_SPAM_THRESHOLD` | Spam score at which a post is held (default 3) |
| `WP_ENVIRONMENT_TYPE` | `local` or `development` keeps the development files |

Fixed in code: `BOI_VERSION`, `BOI_DIR`, `BOI_URI`, `BOI_OPTION_KEY` (`boi_settings`), `BOI_GLOSSARY_VERSION` (14), `BOI_TERMS_VERSION` (2), `BOI_UNLISTED_OPTION` (`boi_unlisted`), `BOI_CL_OPTION` (`boi_cl`), `BOI_ERR_MARKER`, `BOI_STATIC_ERRORS` (`bestofislam-errors`), `BOI_SEC_FILES_MARKER`, and the `BOI_SEC_*` option names.

## Security

- Upload guard: see the boot sequence. A theme caught half uploaded answers 503, never a fatal error.
- REST users: visitors who are not logged in get no `/wp/v2/users` routes (404), and `?author=N` gives them a 404 (`boi_sec_hide_user_routes()`, `boi_sec_block_author_query()`).
- XML-RPC is off while "Turn off XML-RPC" is on (the default), and a correct password sent through it is refused even when it is back on.
- The theme folder's `.htaccess` refuses every dot entry but `.well-known`, every theme PHP file, the `tests`, `bin`, `docs` and `dist` folders, and every Markdown file but `CHANGELOG.md`. It works with mod_alias, with mod_rewrite (`RewriteOptions Inherit`), with neither (`FilesMatch`), and on Apache 2.4 with an `<If>` for dot folders. `tests/`, `bin/` and `docs/` carry their own deny file.
- The live-server clean-up removes the paths in `boi_dev_paths()` (`.git`, `.github`, `.githooks`, `.claude`, `.vscode`, `.idea`, `.gitattributes`, `.gitignore`, `.editorconfig`, `.DS_Store`, `dist`, `tests`, `bin`, `docs`) once per version, on activation, on the first admin page and on the first front-end request, under a lock it renews while it works.

### Root .htaccess sections

On Apache the theme keeps two sections in the site's `.htaccess`, through `insert_with_markers()`, and removes both on a theme switch. The Security tab says when the file cannot be written.

```
# BEGIN Block xmlrpc.php
<Files xmlrpc.php>
<IfModule mod_authz_core.c>
Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
Order Deny,Allow
Deny from all
</IfModule>
</Files>
# END Block xmlrpc.php
```

The xmlrpc section follows the "Turn off XML-RPC" switch. Other themes that write the same block use the same marker, so it is never written twice.

The direct-file section, for a site at the domain root with WordPress in the same folder:

```
# BEGIN Best of Islam: block direct file access
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule (^|/)\.(git|claude) - [F]
RewriteRule ^wp-includes/(?!(js/tinymce/wp-tinymce|ms-files)\.php$).+\.php(/|$) - [F]
RewriteRule ^wp-admin/includes/.+\.php(/|$) - [F]
RewriteRule ^(wp-config\.php|wp-settings\.php|wp-config-sample\.php|wp-admin/install\.php|readme\.html|license\.txt)(/|$) - [F]
RewriteRule ^wp-content/themes/.+\.php(/|$) - [F]
RewriteRule ^wp-content/themes/bestofislam/(tests|bin|docs|dist|\.vscode|\.idea|\.editorconfig|\.DS_Store)(/|$) - [F]
</IfModule>
# END Best of Islam: block direct file access
```

The rules carry the site's own folder prefixes, and add a `wp-config.php` rule for the folder above WordPress when WordPress sits in a subfolder. `BOI_BLOCK_DIRECT_FILES` false leaves the section out.

### Nginx

Nginx ignores `.htaccess`, so neither section is written and the theme folder's own file does nothing. The PHP side still works: the locks, the upload guard, the clean-up and the security headers. Put the equivalent rules in the server block, ahead of the `location ~ \.php$` block, since Nginx uses the first regular expression that matches:

```
location ~ /\.(?!well-known/) { deny all; }
location = /xmlrpc.php { deny all; }
location ~ ^/wp-includes/(?!(js/tinymce/wp-tinymce|ms-files)\.php$).+\.php { deny all; }
location ~ ^/wp-admin/includes/.+\.php { deny all; }
location ~ (^|/)wp-config\.php$ { deny all; }
location ~ ^/(wp-settings|wp-config-sample)\.php { deny all; }
location ~ ^/(wp-admin/install\.php|readme\.html|license\.txt) { deny all; }
location ~ ^/wp-content/themes/.+\.php { deny all; }
location ~ ^/wp-content/themes/bestofislam[^/]*/(tests|bin|docs|dist)/ { deny all; }
location ~* ^/wp-content/themes/bestofislam[^/]*/(?!changelog\.md$).+\.md$ { deny all; }
error_page 403 /wp-content/bestofislam-errors/403.html;
error_page 500 /wp-content/bestofislam-errors/500.html;
error_page 502 /wp-content/bestofislam-errors/502.html;
error_page 503 /wp-content/bestofislam-errors/503.html;
error_page 504 /wp-content/bestofislam-errors/504.html;
```

The `bestofislam[^/]*` in the theme lines also covers the `bestofislam-new` and `bestofislam-old` folders of an FTP update, whose Markdown files and development folders Nginx would otherwise serve while they sit beside the live folder. Put these lines in place before the first FTP update.

Nginx serves `error_page` for PHP's own 500 and 503 answers only with `fastcgi_intercept_errors on;` in the FastCGI location; without it those answers reach visitors as PHP sent them. Jetpack needs XML-RPC: leave out the `xmlrpc.php` line and also switch "Turn off XML-RPC" off on the Security tab, since the theme refuses XML-RPC in PHP too. Where the client logs in to XML-RPC, with a password or any other credential, switch the REST lock off as well, since `boi_sec_refuse_remote_password()` refuses every XML-RPC login while either switch is on. These rules do not follow the Security switches or `BOI_SECURITY_LOCK`: to let XML-RPC through, take the `xmlrpc.php` line out by hand as well. The `wp-config.php` rule matches at any depth, so it also covers a `wp-config.php` in the folder above a WordPress that lives in a subfolder. Prefix the other paths when WordPress lives in a subfolder.

## Error pages

The Errors and Server errors entries under Settings above describe the drop-ins (`maintenance.php` and `db-error.php` at 503 with `Retry-After: 300`, `php-error.php` at 500), the static pages and the per-site copies in `wp-content/bestofislam-errors/`. Build the copies for a root site with `php bin/build-error-pages.php`. Every page carries the 32px icon as a `data:` link (`boi_error_page_icon()`), so the tab icon needs no path.

## Multisite

Settings, seeded content, votes and the Security records are per site. On a network:

- The drop-ins and the static copies are shared by every site. The admin-page repair (`boi_error_maybe_sync()`) and the Install error pages button run on the main site only. Activating the theme or saving Theme Options on any site runs `boi_error_sync()`, which has no network guard, so a subsite can rewrite the shared files with its own name and address, or remove them if its settings turn the error pages off. A theme switch never removes them, since another site may still use them.
- The private login address is unavailable.
- The lock in `inc/lock.php` lives in the main site's options table, so every site that runs the theme shares one lock, as they share the theme files and `.htaccess`.
- A theme switch on any site removes the two root `.htaccess` sections until another site's next sync writes them back.

## Build, test and release

```
set -e                                               # stop at the first failure
for t in tests/*.php; do php "$t" || exit 1; done   # every self-test; WordPress is stubbed
python3 bin/check-content.py                         # Glossary, links, footnotes, JSON
php bin/build-error-pages.php                        # rebuild errors/*.html
python3 bin/build-related.py                         # rebuild inc/related-articles.json
python3 bin/convert-images.py                        # every image to AVIF
sh bin/install-hooks.sh                              # run the image scripts before each commit
git archive --format=zip --prefix=bestofislam/ -o bestofislam-1.100.17.zip HEAD   # the committed files: commit the release first
```

Every PHP script in `tests/` and `bin/` exits unless PHP runs it from the command line. CI runs `checks.yml` (content checks and PHP syntax), `images.yml` (Commons swaps and AVIF conversion, committed back) and `package.yml` on GitHub. The release zip is the one `package.yml` builds: it copies the checkout with `rsync`, leaving out `.git`, `.github`, `.githooks`, `/.claude`, `/.gitattributes`, `/.gitignore`, `/.editorconfig`, `bin`, `tests` and `dist`, then zips `dist/bestofislam`. The `git archive` line above gives the same files for a clean checkout, since `.gitattributes` export-ignores the same paths. `.gitattributes` keeps `.github`, `.githooks`, `.gitattributes`, `.gitignore`, `.editorconfig`, `bin`, `tests` and `.claude` out of the zip.

Deploy through the dashboard (Upload Theme, "Replace active with uploaded") or by FTP: show hidden files, upload to a temporary folder, then rename the old folder away and the new one into place, one straight after the other. The upload guard lives in `functions.php`, so it cannot cover a missing or half-written `functions.php`, or the moment between the two renames. Load one admin page afterwards.
