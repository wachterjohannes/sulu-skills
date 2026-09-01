---
name: sulu-content-migration
description: Write content migrations for structural changes in a Sulu 3.0 project - template property renames, block restructuring, bulk edits of stored content across the dimension content tables. Use when a template change orphans stored values or existing content must be transformed in bulk.
version: 1.0.0
updated: 2026-09-01
sulu-versions: ">=3.0"
---

# Content migrations (Sulu 3.0)

In 3.0 content lives in the database. Every content-rich entity stores its data in a
dimension content table (`pa_page_dimension_contents`, `ar_article_dimension_contents`,
`sn_snippet_dimension_contents`, custom entities in their own `<x>_dimension_contents`).
The template payload is the JSON column `templateData`, next to `seoData` and
`excerptData`; `templateKey`, `locale`, `stage` (draft/live) and `version` identify the
dimension row. A structural template change does not touch that stored JSON, so the
content must be migrated - what `phpcr:migrations:migrate` covered in 2.x is now a
plain DoctrineMigrationsBundle migration.

## Workflow

1. **Decide whether a migration is needed.** Adding a new optional property needs
   none (render with `|default` in Twig). Renaming a property, renaming a block
   type, moving a property into or out of blocks, or changing a stored value
   format orphans data and needs one.
2. **Generate an empty migration**: `bin/console doctrine:migrations:generate`;
   `doctrine:migrations:diff` only detects schema changes, never data.
3. **Transform the rows.** Select the affected rows scoped by `templateKey`,
   decode `templateData`, transform in PHP, write back. Copy the patterns from
   `references/migration-patterns.md` (property rename, block restructuring, an
   SQL-only variant for trivial renames).
4. **Cover every dimension.** Filter by `templateKey` only - never by locale,
   stage or version: draft and live rows and all version rows (0 is the current
   one) must be transformed, or published pages diverge from their drafts and
   restoring an old version brings the old structure back.
5. **Ship XML and migration together.** The template XML change and the migration
   belong in the same commit/deploy; between the two states the Twig template
   reads `null` for the affected properties.
6. **Run and verify:**
   ```bash
   bin/console doctrine:migrations:migrate
   bin/adminconsole cache:clear && bin/websiteconsole cache:clear
   ```
   Open a migrated page in website and admin. Reindex search when searchable or
   listed data changed: `bin/adminconsole cmsig:seal:reindex`.

## Pitfalls

- **Renaming a property in the XML alone loses nothing but shows nothing** - the
  old values stay in `templateData` under the old key and simply stop rendering.
  That silent state is the reason to treat every rename as a migration.
- `seoData` and `excerptData` are separate JSON columns - moving a value between
  the content tab and the SEO/excerpt tab moves it between columns, not keys.
- Content entities often denormalize fields into real columns (e.g. `title`, see
  the [sulu-content-entity](../sulu-content-entity/SKILL.md) skill) - update the
  column together with the JSON or lists show stale values.
- Changing stored `url` values is route territory, not a JSON edit: routes live
  in the route table and old URLs need redirects (SuluRedirectBundle).
- For large tables batch the PHP loop (`WHERE id > ? ORDER BY id LIMIT 500`);
  loading every row at once exhausts memory.
- `doctrine:schema:update` stays off-limits; migrations are the only mechanism.
