# Search engine practice

How the theme applies Google's Search Engine Optimization Starter Guide and its
list of structured data that Google Search supports, and how it behaves beside
Rank Math. The theme makes the site legible. It does not try to manipulate
ranking, and the guide itself says to write for readers first.

## Starter Guide, page by page

| Guide topic | What the theme does | Where |
|---|---|---|
| Unique, accurate page titles | Core title tag with a ` | ` separator; section archives use the section name; the front page carries the name and its focus ("Objections to Islam, answered"). Glossary entries, articles and sections each have their own. | `inc/seo.php` |
| Description meta tag | One per page: the excerpt on articles and glossary entries (one line, under 160 characters), the section description on archives, a written promise on the front page. None on search results. | `inc/seo.php` |
| URLs with words | Slugs are words (`/who-is-allah/`, `/glossary/hijab/`, `/topic/history/`). Set Settings, Permalinks to Post name so an article has one address; old dated addresses redirect by slug. | WordPress |
| One version of a URL | Canonical link on every indexable view; `redirect_canonical` for the rest. | `inc/seo.php` |
| Navigation and breadcrumbs | Eight sections in the header and footer, breadcrumb trail with BreadcrumbList markup, a "Read next" card and a Keep reading list of four related articles on every article, the first mention of each glossary term linked in the text, related terms and articles on glossary entries, and a text link for every article (no drop-downs or image maps). | `parts/`, `inc/seo.php`, `inc/reading.php`, `inc/glossary.php` |
| Site map for users and for search engines | An HTML Site map page and the core XML sitemap (articles, pages, glossary, sections; users, categories and tags left out; unlisted articles excluded). | `inc/setup.php`, `inc/seo.php` |
| Useful 404 page | Real HTTP 404, the site's own design, a way home, the sections and the latest articles. | `templates/404.html` |
| Quality content | Each article is a numbered argument with sources in Notes; each glossary entry defines the term, says what it means, and cites. | `content/`, `inc/glossary-data/` |
| Anchor text | Links in running text are the name of what they point to; the glossary linker links the term itself; the lead card's button carries the article title. | `inc/glossary-links.php`, `inc/features.php` |
| Images: alt text, file names, one directory, common formats | Every image has alt text and a descriptive file name (the article slug), all in `assets/images/`, as AVIF. Credit shown beneath each. | `inc/images.php`, `inc/inline-images.php` |
| Heading tags | One h1 per page (the site name in the header is not a heading); the article title is the h1, entries are h2, notes h3. | `templates/`, `blocks/` |
| robots.txt | Search results disallowed; nothing sensitive is hidden with it. Search results and paged archives also carry noindex. | `inc/seo.php` |
| nofollow | Core adds `rel="nofollow ugc"` to comment links; links the owner configures under Contextual Links go only to approved domains. | core, `inc/contextual-links.php` |
| Mobile | Responsive layout, one URL for every device, no separate mobile site. | `assets/css/` |
| Promotion | RSS feed with autodiscovery, follow links, shareable articles. | core, `inc/features.php` |
| Free webmaster tools | Fields for the Search Console and Bing verification codes (Appearance, Best of Islam, Search engines); submit `/wp-sitemap.xml`. | `inc/settings.php`, `inc/seo.php` |

## Structured data Google Search supports

Google's list, as of June 2026, and where each feature applies to this site.

| Feature | Used | Notes |
|---|---|---|
| Article | Yes | Every article: headline, description, dates, author, publisher, section and images. |
| Breadcrumb | Yes | Every page with a trail. |
| Image metadata | Yes | Licence, creator, credit and acquire-licence page on the featured image and on the two images in the text. |
| Organization | Yes | Front page: name, logo, `sameAs` links. WebSite beside it, for the site name. |
| Profile page | Yes | Author archives: ProfilePage with the author as a Person. |
| Carousel, Course, Dataset, Event, Job posting, Local business, Math solver, Movie, Product, Recipe, Review snippet, Software app, Vacation rental, Employer rating | No | The site has none of these things. |
| Discussion forum, Q&A, Education Q&A | No | The site has no user-generated discussion, and its questions are the site's own answers rather than a quiz. |
| Video | No | There is no video. Add VideoObject if videos are embedded later. |
| Speakable, Subscription and paywalled content | No | Speakable is for news publishers; nothing is paywalled. |

The theme also prints ItemList for each listicle and FAQPage for the front-page
questions. They describe their pages accurately. Google restricts FAQ rich
results to a narrow class of sites, so none is promised.

Validate with the Rich Results Test after any change.

## Rank Math

With Rank Math active, the theme does not repeat what the plugin does, and
supplies the plugin with what only the theme knows (`inc/rank-math.php`):

| Concern | Without Rank Math | With Rank Math |
|---|---|---|
| Titles | Theme | Rank Math |
| Description | Theme | Rank Math; the theme's text fills in where Rank Math has none |
| Canonical link | Theme | Rank Math |
| Robots directives | Theme (`wp_robots`) | Rank Math, told to noindex search results and paged archives |
| XML sitemap | Core, filtered | Rank Math, told to leave out unlisted articles |
| Article, Organization, WebSite, ProfilePage | Theme graph | Rank Math graph |
| Image licence and credit | In the theme's Article | Added to Rank Math's primary image |
| Images in the text | ImageObjects in the theme's graph | Added to Rank Math's graph |
| Breadcrumb markup | Theme | Rank Math when its breadcrumbs are on, otherwise the theme |
| Verification codes | Theme settings | Rank Math's Webmaster Tools |
| ItemList, FAQPage | Theme | Theme (Rank Math does not produce them) |

Rank Math's Image SEO module may rewrite alt text and titles; turn it off for
this site, since every image already carries its own.
