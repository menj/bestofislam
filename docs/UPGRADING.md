# Upgrading

## Before any upgrade

1. Back up the database, since the theme owns the `{prefix}_boi_votes` table.
2. Back up the theme directory if it has been modified in place.
3. Confirm Twenty Twenty-Five remains installed at 1.5 or later.

## Procedure

1. Deactivate nothing; child theme upgrades do not require switching themes.
2. Upload the new package under Appearance, Themes, Add New, and replace the
   existing installation.
3. Visit any admin screen once. `boi_maybe_install()` compares `boi_db_version`
   with `BOI_VERSION` and runs `dbDelta()` when they differ.
4. Visit Settings, Permalinks and save once if the topic archive returns a 404.

## Data notes

- Vote records survive upgrades. The unique key prevents duplicate insertion.
- Tallies are cached for one hour; clear transients if counts appear stale after
  a direct database edit.
- Settings live in the single `boi_settings` option and are merged against
  defaults on read, so new options appear without a migration.

## Rollback

Reinstall the previous package. The votes table is additive across 1.x, so no
schema reversal is required. Should a future release alter the schema, the
change will be documented here before release.

## Version history

See `CHANGELOG.md`.
