---
name: sulu-upgrade
description: Upgrade a Sulu project - a 3.x minor/patch update, or the major migration from Sulu 2.6 to 3.0 including the PHPCR-to-database content migration. Use whenever a task asks to update Sulu versions, migrate content storage, or fix a project broken by an upgrade.
version: 1.0.0
updated: 2026-08-31
sulu-versions: ">=2.6"
---

# Upgrading Sulu

The authoritative source is `vendor/sulu/sulu/UPGRADE-3.x.md` **after** updating (or the
[3.0 branch copy](https://raw.githubusercontent.com/sulu/sulu/refs/heads/3.0/UPGRADE-3.x.md) before). Read the
sections between the project's current version and the target - this skill gives the
procedure and the traps, not a substitute for that file.

## 3.x minor/patch upgrade

1. `composer update sulu/sulu --with-dependencies`
2. Read every `UPGRADE-3.x.md` section newer than the previous version; apply what
   matches the project (many entries are schema changes, some rename params in form XML).
3. `bin/console doctrine:migrations:migrate` - 3.0 uses DoctrineMigrationsBundle and
   ships migrations with the package.
4. `bin/adminconsole sulu:admin:update-build`, then clear both caches
   (`bin/adminconsole cache:clear && bin/websiteconsole cache:clear`).
5. Verify: admin loads, a page saves and publishes, website renders.

## 2.6 → 3.0 major upgrade

The order matters - steps 1–4 happen **while still on 2.x**:

1. **Latest 2.6 first.** Update sulu/sulu and every sulu bundle to their latest 2.6
   releases. `sulu/sulu-rector` helps here (`SuluLevelSetList::UP_TO_SULU_26`) -
   note it has **no 3.0 set**; the 3.0 renames are manual (see
   `references/2x-to-30.md`). Then run all pending PHPCR migrations:
   `bin/adminconsole phpcr:migrations:migrate`.
2. **Validate keys.** Webspace, template and navigation-context keys must match
   `[a-z0-9_-]+` with max 31 characters - 3.0 enforces this strictly. Fix violations
   now, via PHPCR migration or database.
3. **ArticleBundle pre-step** (only if `sulu/article-bundle` is installed): update it
   to latest 2.6, ensure its `Version202407111600` PHPCR migration ran, then
   `composer remove sulu/article-bundle --no-scripts` (and `elasticsearch/elasticsearch`
   if unused). Flex may delete `config/templates/articles/` - restore it from git.
4. **Cleanup the content-repository:** `bin/adminconsole sulu:document:phpcr-cleanup`.
5. **Switch the dependency:** `composer require sulu/sulu:"3.0.*" --no-scripts`.
   Re-register bundles in `config/bundles.php` (new FQCNs like
   `Sulu\Content\Infrastructure\Symfony\HttpKernel\SuluContentBundle`) and update
   `config/routes/sulu_admin.yaml` - full diffs in `references/2x-to-30.md`.
6. **Apply the code/config/template renames** from `references/2x-to-30.md`:
   controller FQCN, `url` route property, Twig function renames, template path
   config, article types → template groups.
7. **Database schema:** `bin/console doctrine:migrations:migrate` creates the new
   content storage tables.
8. **Content migration PHPCR → database:**
   ```bash
   composer require sulu/phpcr-migration-bundle
   # register SuluPhpcrMigrationBundle, configure the DSN
   # (dbal://default?workspace=... or jackrabbit://...):
   bin/adminconsole sulu:phpcr-migration:migrate
   ```
   Installation and configuration details:
   [SuluPHPCRMigrationBundle README](https://github.com/sulu/SuluPHPCRMigrationBundle).
   The command is **re-runnable** - already migrated content is overwritten, not
   duplicated. Fix errors from customized code and run again.
9. **Finish:** `bin/adminconsole sulu:admin:update-build`, clear both caches, log in,
   grant the new permissions (articles, snippets, template groups) to the roles.
10. **Cleanup:** remove Jackrabbit and the PHPCR/migration packages once verified.

## Pitfalls

- Don't skip the 2.x pre-steps and "fix forward" on 3.0 - the content migration
  reads the PHPCR repository, and skipped PHPCR migrations or dirty keys fail it.
- `--no-scripts` on the composer steps is deliberate: recipes and builds run against
  half-migrated config otherwise.
- After the upgrade, the admin build (`assets/admin`) references moved JS package
  paths; `sulu:admin:update-build` handles this - a manually maintained
  `package.json` needs the paths from `references/2x-to-30.md`.
- Grep the project for 2.x namespaces (`Sulu\Bundle\ArticleBundle`,
  `Sulu\Bundle\RouteBundle`, `DefaultController`) after step 6 - the "Moved classes
  for 3.0" section of `UPGRADE-3.x.md` is the lookup table for anything left.
