# Upgrading

## Before any upgrade

1. Back up the database. The theme owns the `{prefix}_boi_votes` table and
   keeps its settings in the `boi_settings` option.
2. Back up the theme directory if it has been modified in place.
3. Confirm Twenty Twenty-Five remains installed at 1.5 or later.
4. If the custom login address is on, note it down before upgrading.

## Procedure

1. Upload the new package under Appearance, Themes, Add New, and replace the
   existing installation. There is no need to switch themes. By FTP instead:
   turn on hidden files in the FTP program, so the `.htaccess` files go up;
   upload the folder into `wp-content/themes/` as `bestofislam-new`, its
   `.htaccess` files first (the theme folder's, then `docs/`, and `bin/` and
   `tests/` when the copy has them), so on Apache their rules apply while the rest arrives; on
   Nginx, which ignores them, add the server rules in `README.md` before the
   first FTP update; then rename `bestofislam` to
   `bestofislam-old` and, straight after, `bestofislam-new` to `bestofislam`,
   with the admin closed in between. Delete `bestofislam-old` once the site
   checks out. Never upload WordPress itself without keeping `wp-config.php`.
2. Load any page of the site, in the admin or on the front end. The theme
   compares its stored version with the new one and migrates on that first
   load: it updates the votes table, adds any missing seeded content,
   refreshes seeded pages and articles that have not been edited since the
   theme wrote them, repairs seeded content without an author, and repairs
   articles whose entry titles were lost to the saving fault fixed in 1.52.0.
3. Open the Content tab under Appearance, Theme Options. If it lists seeded
   articles that differ from this version, the update could not tell whether
   you edited them and has left them alone. Tick the ones you have not edited
   and choose Update the ticked articles; anything ticked is replaced with this
   version's text.
4. Featured images are copied into the media library three at a time as pages
   load. The Content tab reports how many have arrived. Until an image
   arrives, the copy bundled with the theme is shown, so no article goes
   without its picture.

## After an upgrade

- **Customised template parts.** If the header or footer was ever edited in
  the Site Editor, WordPress keeps showing the saved copy and ignores the
  theme's new one. Reset it under Appearance, Editor, Patterns, Template
  parts, then choose the part and Reset.
- **Addresses returning a 404.** An update does not rebuild WordPress's
  rewrite rules. If a section or search address returns a 404 after an
  update, save Settings, Permalinks once. Changing the search base word on
  the Search engines tab rebuilds them automatically on the next page load.

## Moving from the plugins the theme replaces

The theme rebuilds Unlist Posts & Pages, Pretty Search Permalinks and WPS Hide
Login. While any of these plugins is active, the theme's version of that
feature stands aside, so nothing runs twice.

1. Deactivate the plugin.
2. Open Appearance, Theme Options. The theme imports the plugin's saved settings:
   the list of unlisted items, the search base word, or the login address.
3. For the login address, confirm the address on the Access tab and switch it
   on. It is off until switched on, even when an address was imported.
4. Delete the plugin once the theme's version is working.

## Upgrading to 1.100.12

Documentation only, plus the article count on the Content tab. Replace the theme files; nothing else to do.

## Upgrading to 1.109.0

Replace the theme files. The new article reaches existing sites through `boi_migrate_seed()` on this version; its images are in `assets/images/`.

## Upgrading to 1.108.0

Replace the theme files. The new article reaches existing sites through `boi_migrate_seed()` on this version; its images are in `assets/images/`.

## Upgrading to 1.107.0

Replace the theme files. The Glossary sync re-runs (`BOI_GLOSSARY_VERSION` 17) and rebuilds the pages that gained notes; entries the owner has edited are left alone.

## Upgrading to 1.106.1

Replace the theme files. Nothing else changes: `bin/check-content.py` gains a check, with the names already shared listed, with their entries, in `bin/glossary-name-baseline.json`.

## Upgrading to 1.106.0

Replace the theme files. The Glossary sync re-runs (`BOI_GLOSSARY_VERSION` 16) and creates the new entry, Ruh; entries the owner has edited are left alone.

## Upgrading to 1.105.0

Replace the theme files. The new Glossary notes reach existing sites through the Glossary sync, which re-runs because `BOI_GLOSSARY_VERSION` is now 15; entries the owner has edited are left alone.

## Upgrading to 1.104.0

Replace the theme files. The Images tab appears in Theme Options; the setting `pexels_key` is added to `boi_settings` only when you save a key.

## Upgrading to 1.103.0

Replace the theme files. Glossary entries gain an optional "In the classical dictionaries" section from `inc/glossary-data/*.json` (field `lexicon`), styled in `assets/css/glossary.css`; it reaches existing sites through the Glossary sync (`BOI_GLOSSARY_VERSION` 15).

## Upgrading to 1.102.0

Replace the theme files. The new articles reach existing sites through `boi_migrate_seed()` on this version; their images are in `assets/images/`, and the credits are in `inc/images-data.json`.

## Upgrading to 1.100.21

Replace the theme files. Nothing else changes: image files are added in `assets/images/inline/`, and the credits are in `inc/images-data.json`.

## Upgrading to 1.100.20

Replace the theme files. The new articles reach existing sites through `boi_migrate_seed()` on this version; their images are added in `assets/images/`.

## Upgrading to 1.100.19

Replace the theme files. Nothing else changes: thirteen image files are added in `assets/images/inline/`, and the credits are in `inc/images-data.json`.

## Upgrading to 1.100.18

Replace the theme files. Nothing else changes: eleven articles gain an image file each in `assets/images/inline/`, and the credits are in `inc/images-data.json`.

## Upgrading to 1.100.17

Replace the theme files. Nothing else changes. On a site in a subfolder, put a copy of `assets/images/favicon.ico` in the domain root, since browsers ask for `/favicon.ico` there.

## Upgrading to 1.100.16

Replace the theme files. The new files are `inc/lead-list.php`, `assets/css/lead-list.css` and `tests/lead-list-test.php`. If you edited the Reflections, section or archive template in the Site Editor, your copy wins and shows no lead and list; reset it, or add the Lead and list block above the query. Nothing else to do.

## Upgrading to 1.100.15

Replace the theme files. The new files are in `assets/images/`: `favicon.ico`, `icon-192.png`, `icon-512.png`, `icon-maskable-512.png` and `site.webmanifest`. The next admin page rewrites the error pages with the icon. If a `favicon.ico` file sits in the site root, the web server sends it and the theme never answers; delete it to use the theme's.

## Upgrading to 1.100.14

Documentation only: the FTP steps send the `.htaccess` files first. Replace the theme files; nothing else to do.

## Upgrading to 1.100.13

Replace the theme files. The new files are `LICENSE`, `inc/anatomy.php`, `assets/css/anatomy.css`, `patterns/faq.php` and `tests/anatomy-test.php`. Two switches join the Display tab, both on: Contents and Next step. Key points and a review date are typed per article, in the Key points and review box; until then the head of an article looks as before, with the questions panel.

## Upgrading to 1.100.9, 1.100.10 or 1.100.11

Replace the theme files and load one page. Visitors who are not logged in lose the `/wp/v2/users` routes and `?author=N` (both answer 404). Nothing to set.

## Upgrading to 1.100.8

Replace the theme files. On the first admin page load the theme deletes its development files on a production server: `.git`, `.claude`, `tests`, `bin`, `docs` and the editor files (D52). To keep them on a server where you work on the theme, set `WP_ENVIRONMENT_TYPE` to `local` or `development`, or define `BOI_KEEP_DEV_FILES` as true, in `wp-config.php` before you upload. To keep only `.git`, define `BOI_REMOVE_GIT_DIR` as false.

On Apache the same page load adds a "Best of Islam: block direct file access" section to `.htaccess`. If anything that used to load now answers 403, define `BOI_BLOCK_DIRECT_FILES` as false and tell the theme's maintainer which address it was.

## Upgrading from 1.78.0 to 1.78.1

Replace the theme files. On Apache, with "Turn off XML-RPC" on (the default), the theme adds a "Block xmlrpc.php" section to .htaccess on the next admin page load, so the manual step 7 below is no longer needed. Switching it off removes the section. If the Security tab says .htaccess could not be written, ask the host to deny xmlrpc.php at the server.

## Upgrading from 1.77.1 to 1.78.0

Replace the theme files. The Security tab (Appearance, Theme Options, Security) is on by default: XML-RPC, application passwords and REST writes from outside the browser stop at once. XML-RPC was already off in this theme, so only the last two are new.

After the October 2026 attack on the sister site, the owner checklist:

1. Change the password of every administrator to a long random one used nowhere else, then Log Out Everywhere Else (Users, Profile), or press "Log out all sessions" on the Security tab.
2. Create a new administrator whose name is not the site name, log in as it, and delete the old account, attributing its content to the new one.
3. Press "Revoke all application passwords".
4. Check Plugins for anything not installed by you (auto-posting, AI writers, remote management) and remove it. Check Users for unknown accounts.
5. Run the spam scan, select the results, move them to the trash, then delete the empty spam categories.
6. Purge any page cache, then resubmit the sitemap in Google Search Console.
7. Optional, at the server: deny xmlrpc.php in .htaccess as well, so the requests never reach PHP. Jetpack needs XML-RPC; if Jetpack is used, switch "Turn off XML-RPC" off instead (password logins through XML-RPC stay refused).

If a lock breaks a tool you rely on, add this line to `wp-config.php`. It turns the three locks off without changing the options:

```php
define( 'BOI_SECURITY_LOCK', false );
```

The spam gate holds anything published through XML-RPC or a REST application password as Pending, whatever it says. Seeded articles, pages and Glossary entries are exempt from the gate and the scan.

## If you are locked out

The custom login address can be switched off without logging in. Add this line
to `wp-config.php`, above the comment that marks the end of the
settings you may edit:

```php
define( 'BOI_HIDE_LOGIN', false );
```

The standard `wp-login.php` works again at once. Log in, correct the address
on the Access tab, then remove the line. Activating another theme also
restores `wp-login.php`, since the feature belongs to this theme.

## Data notes

- Vote records survive upgrades. The unique key prevents duplicate records.
- Vote tallies are cached for one hour; clear transients if counts look stale
  after a direct database edit.
- Settings live in the single `boi_settings` option and are merged with the
  defaults on read, so new settings appear without a migration.
- The list of unlisted items lives in the `boi_unlisted` option.

## Rollback

Reinstall the previous package. The votes table is additive across 1.x, so no
schema reversal is needed. Settings added in a later version are ignored by an
earlier one and return when the later version is reinstalled. Should a future
release alter the database schema, the change will be documented here first.

## Roadmap

These are plans, not promises. Each item rests on something on record: the code, the server logs of November 2026, a documented limit, or a feature another of the owner's themes already has. Items leave this list when they ship and go into `CHANGELOG.md`. GitHub had no open issues or pull requests when the list was last checked (2026-11-05), and the code carries no TODO markers.

### Next

- Export, import and reset for Theme Options. The abrahamic theme has all three on its Tools tab; here a move to a new site means setting every tab again by hand.
- Error pages on a network. The admin-page repair runs on the main site only (`boi_error_maybe_sync()`), so the shared drop-ins stop updating once it leaves the theme, while activation and a settings save on any subsite run `boi_error_sync()` unguarded and can rewrite them with that subsite's address. The abrahamic theme hands the job to one site, the lowest ID that runs it.
- Keep the root `.htaccess` sections on a network when one site leaves the theme. `boi_sec_remove_htaccess()` runs on any site's `switch_theme`, and the shared file then lacks both sections until another site's next sync.

### Later

- A preview of the `bestofislam/lead-list` block in the Site Editor. It renders on the server only, so the editor shows a placeholder. Waits on an editor script for the theme's server-rendered blocks.
- A translation template, `languages/bestofislam.pot`. The FAQ in `readme.txt` tells owners where to put a `.mo` file, but the theme ships no template to make one from. The injil.help and compelling-evidence themes ship a `languages/` folder. Waits on a string freeze after the current round of article work.
- A full Content-Security-Policy. `inc/security-headers.php` sends `frame-ancestors` only, because WordPress and its plugins print inline scripts and styles an enforcing policy would block. Waits on WordPress core.

### Ideas

- A sticky contents variant for long articles, as the compelling-evidence theme has (`#ce-toc`). The contents here are inline only.
- Key points for the 152 seeded articles. The box is built, but no seeded article carries key points yet, so it shows only the questions panel.
- A crawl rule for the heaviest bots. In the November 2026 log QlyzeBot made 1,014 requests and Amazonbot 825, against 378 from Googlebot.
- Fixed publication dates for seeded articles, as the abrahamic seeder has (`post_date`, published or Scheduled), so new articles can go out on a plan.
- An image sitemap that stays on beside Rank Math. `boi_image_sitemap_enabled()` turns it off whenever Rank Math is active, and leaves the images to Rank Math's own sitemap; weigh whether the theme's licence data should reach search engines there too.

## Version history

See `CHANGELOG.md`.
