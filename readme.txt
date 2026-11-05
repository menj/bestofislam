=== Best of Islam ===
Contributors: menj
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.100.14
Template: twentytwentyfive
License: GNU General Public License v3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The child theme of Twenty Twenty-Five that runs bestofislam.org: numbered objections to Islam, each answered from the Quran, the hadith and the historical record.

== Description ==

Best of Islam is a block child theme for one site, bestofislam.org. Each article is a numbered list. An entry states an objection to Islam in the objector's own terms, then answers it.

Everything the site needs ships in the theme. There is no companion plugin. The theme builds the site on activation: 8 sections, the standing pages, the menus and 152 articles, each with a featured image and 2 more in its text. A Glossary of 797 Arabic and Islamic terms sits at /glossary/.

Twenty Twenty-Five 1.5 or later must stay installed. Best of Islam takes its templates and base styles from it.

== Features ==

* Listicle and Listicle Entry blocks, rendered on the server, so rank numbers and vote tallies always match the page. Lists count down or up and renumber themselves when you add, move or remove an entry.
* A Theme toggle block for the light and dark appearance.
* A section taxonomy (listicle-topic) at /topic/<section>/, with categories and tags on every seeded article.
* A reading order in 7 parts, with a "Read next" card at the foot of every article.
* 45 questions with short answers, linked to their articles, on the front page and in a panel on each article.
* A Glossary of 797 entries with Arabic script, a search box and an A to Z index, linked from the first mention of a term in an article.
* Contextual links from phrases in articles to other articles here and to inner pages of other sites, set under Appearance, Contextual Links.
* Optional visitor voting, rate limited and stored in its own table. It is off by default.
* A contact form with a nonce, a honeypot and a rate limit, sent with wp_mail().
* Search with readable addresses such as /search/Paul/, filters for kind and section, and links straight to the matching entry.
* Titles, meta descriptions, canonical links, breadcrumbs and one JSON-LD graph per page: Article, ItemList, FAQPage, BreadcrumbList, ProfilePage, Organization, WebSite and a DefinedTermSet for the Glossary. With Rank Math, Yoast SEO, All in One SEO or SEOPress active, the theme hands over what the plugin does and adds only what it cannot know, such as each image's licence and credit.
* On each article: the updated and reviewed dates under the title, an "In short" box of key points with the questions the piece answers, a list of its sections, a next step in the middle, a FAQ style for questions and ruled tables.
* A reading progress bar on single posts and pages, in the palette, with its edge, height, colour and post types set on the Display tab.
* Unlisted posts and pages, a private login address, security headers, a spam gate for gambling spam and a Security tab with a spam scan, clean-up tools and attack reports.
* Branded maintenance, database and PHP error pages, with email alerts.
* Bundled fonts: EB Garamond, Platypi, Ysabeau Office, Vollkorn, Noto Naskh Arabic and KFGQPC HAFS Uthmanic Script for Quranic verses. The script faces are scoped by unicode-range, so a page downloads them only when it has text in that script. 4 more script faces activate when the site owner drops in the licensed files (assets/fonts/scripts/README.md).

== Installation ==

1. Install Twenty Twenty-Five 1.5 or later and leave it in the themes folder.
2. Upload bestofislam-<version>.zip under Appearance, Themes, Add New, and activate it.
3. On activation the theme creates its votes table and fills the site. Images are copied into the media library 3 at a time as pages load. Until a copy arrives, the copy bundled with the theme is shown.
4. Open Appearance, Theme Options and work through the tabs.

To install by FTP, turn on hidden files in your FTP program, upload the bestofislam folder to wp-content/themes/, then activate it under Appearance, Themes.

= Updating from the dashboard =

Upload the new zip under Appearance, Themes, Add New, and choose "Replace active with uploaded". Then open any admin page. The theme brings its tables, terms, error pages and unedited seeded articles up to date by itself. The Content tab lists any seeded article it could not update because it may have been edited.

= Updating by FTP =

1. Turn on hidden files in your FTP program. The theme carries files whose names begin with a dot, such as .htaccess, which keep its private files closed to visitors. In FileZilla: Server, Force showing hidden files.
2. Upload the new bestofislam folder next to the old one under a temporary name, such as wp-content/themes/bestofislam-new. Send its .htaccess files first: the one at the top of the folder, then those in bin, docs and tests. On Apache they close the theme's private files to visitors while the rest arrives. Wait until every file has arrived.
3. Rename the old folder to bestofislam-old and, straight after, rename bestofislam-new to bestofislam. Between the two renames the site has no bestofislam folder and its pages may fail for that moment, so do them one after the other and keep the admin closed until both are done.
4. Open any admin page, check the site, then delete bestofislam-old.

If you upload straight over the live folder and the copy stops halfway, leaving one of the theme's files missing or broken, visitors see a short "Updating, back in a minute" page with a 503 status instead of an error, and the admin names the files still missing. Upload them and the site returns. That page comes from functions.php, so it cannot help while functions.php itself is missing or half written, or in the moment between the two renames above. It checks the PHP files and the data files the theme lists in functions.php: a missing stylesheet, font or image, or a file cut short at a point where it still reads as valid, slips past it. The temporary-folder method avoids those cases: an upload that stops there leaves the live theme untouched.

Never upload a fresh copy of WordPress itself over the site without keeping wp-config.php. That file holds the database details; without it WordPress opens its installer to anyone who visits.

docs/UPGRADING.md has the notes for each version. On a live server the theme removes its docs folder after the update, so read the copy in the zip.

== Usage ==

Appearance, Theme Options holds the settings in 8 tabs. Each tab opens with a note on how to use it, and each saves only its own fields.

* Display: rank badges, the entry image size, two article switches (the contents list and the next step in the middle of an article), the front-page hero image, editor's picks, follow links, the reading progress bar and the contact form recipient.
* Schema: the default list markup, ItemList or none. One listicle can override it in its block sidebar.
* Voting: switch voting on, limit it to logged-in users and set the votes per visitor in 10 minutes. The 20 latest votes are listed.
* Content: run population again, see how many images have reached the media library, and choose which seeded articles that differ from this release to replace.
* Search engines: meta descriptions, Google and Bing verification codes, and readable search addresses.
* Access: the private login address, the security headers and the list of unlisted items.
* Security: switches for XML-RPC, application passwords, REST writes from outside the browser, the spam gate and 410 Gone, then the spam tools and reports.
* Errors: the branded error pages, email alerts and the minutes between 2 alerts. The tab shows whether each page is installed, and gives the lines to paste into .htaccess for the errors the web server answers itself.

Appearance, Contextual Links holds 3 more tabs:

* Domains: the other sites links may lead to.
* Phrases: your own phrase and address pairs, which win over the shared list. Each article also has its own list in the editor.
* Rules: switches and caps for links to this site and to other sites, whether outside links open a new tab, and the pages that never receive links.

To write a list, insert the Listicle starter pattern, or add a Listicle block and fill its entries. Each entry takes a title, an optional image, an optional objection, an optional outbound link and the answer as ordinary blocks. Then assign the article to a section.

Colours and type sizes live in the Site Editor, under Styles. The theme ships 2 style variations, Best of Islam and Best of Islam Night.

== Frequently Asked Questions ==

= Do I need any plugins? =

Every feature is in the theme, so the site runs with none. Rank Math and the other SEO plugins are optional, and the theme steps aside for them where they overlap.

= I installed Unlist Posts & Pages, Pretty Search Permalinks or WPS Hide Login. What happens? =

While one of them is active, the theme leaves that job to the plugin. Deactivate the plugin and the theme takes over, carrying its saved settings across. The private login address still has to be switched on in the Access tab.

= I am locked out after changing the login address. =

Add define( 'BOI_HIDE_LOGIN', false ); to wp-config.php and log in at wp-login.php. Then fix the address in the Access tab.

= A tool I use stopped publishing after an update. =

The Security tab turns off XML-RPC, application passwords and REST writes from outside the browser by default, because spam tools use them. Switch off the one your tool needs. Every login sent through XML-RPC, with a password or any other credential, is refused while either the XML-RPC switch or the REST switch is on (the REST switch guards that route too), so a tool that logs in to XML-RPC needs both off. A tool that writes through the REST API with an application password needs both the REST switch and the application-password switch off; one that uses a logged-in browser session needs neither. In an emergency, define( 'BOI_SECURITY_LOCK', false ); in wp-config.php lifts all 3. That works in PHP at once; on Apache the xmlrpc.php block in .htaccess may stay until the theme next checks it from an admin page, so to let XML-RPC through at once, also delete the lines between # BEGIN Block xmlrpc.php and # END Block xmlrpc.php in the site's .htaccess.

= Why is there no colour picker in Theme Options? =

Colours are theme.json tokens. Change them in the Site Editor under Styles, or pick a style variation, and the whole site follows, the Theme Options screen included.

= How do I add key points or a review date to an article? =

Open the article and find the Key points and review box below the text. Type one key point a line; they show in the "In short" box at the head of the article. Pick the day you last checked the article against its sources; it shows under the title as Reviewed. Leave a field empty to show nothing.

= I chose "Your latest posts" under Settings, Reading. Will an update undo it? =

From 1.98.0 the front page follows Settings, Reading. With "Your latest posts" the site shows the posts index, and later population runs leave that choice alone.

= What are the error pages? =

WordPress shows a plain screen when it is updating, when PHP fails or when the database is down. The theme replaces those screens with its own, and can email you when one appears. It also ships pages for the errors your web server answers before WordPress starts (403, 500, 502, 503 and 504). The Errors tab shows their state and the lines for .htaccess.

= Why did the theme delete .git, tests and docs from its folder? =

They are development files, and a web server can hand them to anyone who asks. On a live server the theme removes them on the first visit after an update. Until then Apache refuses them through the theme's .htaccess; Nginx does so only if the host has added the rules in README.md. A site where you work on the theme keeps them: set WP_ENVIRONMENT_TYPE to local or development in wp-config.php, or add define( 'BOI_KEEP_DEV_FILES', true );.

= What do the security headers do? =

They ask browsers not to show the site inside another site's frame, not to guess file types, and to send other sites only your address, never the page a reader came from. They are on by default under the Access tab. HSTS, which tells browsers to use HTTPS for a year, is off until you switch it on, because it cannot be undone from the site.

= Can I translate the theme? =

Yes. The text domain is bestofislam. Put bestofislam-<locale>.mo in the theme's languages folder or in wp-content/languages/themes.

== Privacy ==

The theme sends nothing to another server. It loads no fonts, scripts, styles or images from a third party, and it has no analytics, tracking or advertising.

What it does keep:

* Votes, when voting is switched on: the post, the entry, the time and a salted SHA-256 hash of the visitor's IP address and browser string (or of the user ID for a logged-in user). The address itself is never stored. The visitor's browser also keeps a list of the entries it voted for, in localStorage.
* The contact form emails the message to the site's recipient and stores nothing. A hash of the sender's IP address is kept for 1 hour to enforce the rate limit.
* The appearance toggle saves the reader's choice of light or dark in localStorage. It never leaves the browser.
* For security, the theme records failed logins by account name with the IP addresses they came from, for 24 hours; each administrator's last login time and IP address; daily counts of refused requests; and a capped log of held spam and admin actions with IP addresses. Only administrators see them, on the Security tab, and the log can be cleared there.
* Error alerts, when switched on, email the error, the path of the page that failed (never its query string) and the time to the site administrator.

The seeded Privacy page says the same in plain words. If you switch on voting, check that it still matches your site.

== Copyright ==

Best of Islam, Copyright 2026 Mohd Elfie Nieshaem Juferi. The theme's code is licensed under the GNU General Public License v3 or later. The full text is in the LICENSE file.

Bundled resources keep their owners' licences. docs/CREDITS.md lists each one with its source.

* EB Garamond, Platypi, Ysabeau Office, Vollkorn and Noto Naskh Arabic: SIL Open Font License 1.1, with the licence text beside each font.
* KFGQPC HAFS Uthmanic Script: King Fahd Glorious Quran Printing Complex, under its own licence (assets/fonts/scripts/kfgqpc-hafs-uthmanic-script-LICENSE.txt).
* Minimalist Social & Platform Icons Pack 2.8: GNU General Public License (assets/icons/LICENSE.txt).
* Photographs: public domain or CC0 from Wikimedia Commons, the Pexels licence and the Pixabay Content Licence, credited under each image and on the Sources and standards page.
* Code adapted from Unlist Posts & Pages, Pretty Search Permalinks, WPS Hide Login and Reading Progress Bar, all GPLv2 or later.

== Changelog ==

= 1.100.14 =
* The FTP update steps send the theme's .htaccess files first, so its private files stay closed while the rest uploads.

= 1.100.13 =
* An article with key points opens with an "In short" box, which now also holds the questions the piece answers. Key points and a review date are typed in the new Key points and review box.
* The line under the title shows when an article was updated and when it was last reviewed.
* An article with three or more sections lists them under the box. A list with a jump list keeps that.
* A next step before the third section points to the notes, the next piece in the reading order and the Contact page.
* A FAQ style for Details blocks, with a Questions readers ask pattern. Its questions are marked up for search engines only while they are on the page.
* Tables in articles are ruled, carry their captions and scroll on their own on a phone.
* The theme is now licensed under the GNU GPL, version 3 or later. The full text is in the LICENSE file.

= 1.100.12 =
* The documentation is up to date with the theme: how to update by FTP, every settings screen, the security switches, the error pages and the files the theme removes from a live server.
* The Content tab gives the right number of seeded articles, 152.

= 1.100.11 =
* The web server refuses the theme's Markdown notes, such as README.md. The changelog the settings page links to stays open.

= 1.100.10 =
* The live-server cleanup keeps its lock while it works, so a long cleanup is never run twice at once.

= 1.100.9 =
* Visitors can no longer list the site's user accounts.
* A theme caught half uploaded shows the short "updating" page instead of an error, even when a file arrived only in part.

= 1.100.8 =
* On a live server the theme deletes its development files again, the .git repository included, and the web server refuses direct requests for core and theme PHP files, .git, .claude and the theme's tests, bin and docs folders. See UPGRADING.md to keep them where you work on the theme.
* Old dated addresses such as /2019/04/12/what-is-the-qibla/ redirect to the article.
* A post with no featured image no longer leaves an unnamed link on the front page and in archives: the placeholder carries the post title for screen readers.
* The All reflections link is large enough to tap on a phone.

= 1.100.7 =
* The front page tells one story. The questions come straight after the hero and the counts, then the editor's picks, the newest and latest articles, the first-visit path and the sections, with each band numbered as a step.

= 1.100.6 =
* The web server error pages work when WordPress sits in a subfolder. The theme writes a copy of each into wp-content/bestofislam-errors with the site's home address, and the .htaccess lines on the Errors tab point there.
* The theme no longer deletes development files from a folder that is a git checkout.

= 1.100.5 =
* An edited or half-restored copy of the error pages is written back on the next admin visit.

= 1.100.4 =
* The theme removes its error pages from wp-content when another theme is activated, and replaces error pages another theme left behind.
* A 504 page for the web server, with its .htaccess line.

= 1.100.3 =
* A new article on Ka'b ibn al-Ashraf, Abu Rafi' and the poets Asma bint Marwan and Abu Afak.
* The article on the first revelation answers the claim that the angel gave no name and no "fear not", and the article on Islam and peace answers the claim that peaceful Muslims are not following real Islam.

= 1.100.2 =
* The Islamic dilemma article no longer calls other websites related to this one. Its links stay.
* The structured data names the author as Mohd Elfie Nieshaem Juferi, at https://menj.bio. Nothing visible changes.

= 1.100.1 =
* Install error pages now says so when it could not write a file, and names the file, in place of the success notice.

= 1.100.0 =
* The Errors tab has an Install error pages button and tells a current error page from an outdated one.
* Static 403, 500, 502 and 503 pages in the errors folder for the web server, with the .htaccess lines on the Errors tab.
* The error pages tell search engines not to index them, and the PHP error page no longer asks browsers to retry.
* The 404 page tells search engines not to index it.

= 1.99.0 =
* A reading progress bar on single posts and pages. Switch it on or off, pick the top or bottom edge, a height from 2 to 12 pixels, a palette colour or your own, and the post types, on the Display tab.
* Unlisting works on every public post type, from Quick Edit and Bulk Edit as well as the editor sidebar. Post lists gain an Unlisted column and an Unlisted view.
* The search base can be set in Settings, Permalinks too, as the plugin allowed. Rewrite rules are rebuilt once after each update.
* The [boi_social_icons] shortcode and the Follow icons pattern put the follow icons anywhere. The icons take their colours from CSS variables and show a focus ring.

= 1.98.1 =
* Structured data: listicle lists and the front page Reflections strip are linked in ways schema.org accepts. The Schema Markup Validator rejected both.

= 1.98.0 =
* Theme Options rebuilt as cards under a tab list. The tabs switch without a reload and follow the arrow keys, Home and End. Booleans are switches, the image size and default schema are picked from cards, and the 2 number settings are sliders that show their value. The screen stacks below 782px.
* Every tab opens with a note on how to use it.
* "Settings saved" now shows after a save, and so does the error for a login address that cannot work. Neither appeared before.
* The front page follows Settings, Reading, and population on an update no longer turns "Your latest posts" back into a static front page.
* The theme declares title-tag and automatic-feed-links and loads its translations.
* Links in comment text are underlined, as links in article text are.
* Licence files for Platypi, Ysabeau Office and Vollkorn, and docs/CREDITS.md now carries the theme's copyright and every bundled resource.
* This readme.txt.

= 1.97.0 =
* Mobile-first and WCAG 2.2 AA fixes: the Glossary search box, tap targets of 44px in the footer, and focus kept clear of the sticky Glossary bar.

= 1.96.1 =
* With Rank Math on, the page graph carries one WebSite node.

Earlier releases are in CHANGELOG.md.

== Upgrade Notice ==

= 1.100.14 =
Documentation only. Replace the theme files; nothing else to do.

= 1.100.13 =
Two new switches on the Display tab, both on: Contents and Next step. Nothing else to do.

= 1.98.0 =
A new Theme Options screen with the same settings and the same saved values. If you set Settings, Reading to "Your latest posts", the front page now shows your posts.
