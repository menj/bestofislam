# Upgrading

## Before any upgrade

1. Back up the database. The theme owns the `{prefix}_boi_votes` table and
   keeps its settings in the `boi_settings` option.
2. Back up the theme directory if it has been modified in place.
3. Confirm Twenty Twenty-Five remains installed at 1.5 or later.
4. If the custom login address is on, note it down before upgrading.

## Procedure

1. Upload the new package under Appearance, Themes, Add New, and replace the
   existing installation. There is no need to switch themes.
2. Load any page of the site, in the admin or on the front end. The theme
   compares its stored version with the new one and migrates on that first
   load: it updates the votes table, adds any missing seeded content,
   refreshes seeded pages and articles that have not been edited since the
   theme wrote them, repairs seeded content without an author, and repairs
   articles whose entry titles were lost to the saving fault fixed in 1.52.0.
3. Open the Content tab under Appearance, Listicles. If it lists seeded
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
2. Open Appearance, Listicles. The theme imports the plugin's saved settings:
   the list of unlisted items, the search base word, or the login address.
3. For the login address, confirm the address on the Access tab and switch it
   on. It is off until switched on, even when an address was imported.
4. Delete the plugin once the theme's version is working.

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

## Version history

See `CHANGELOG.md`.
