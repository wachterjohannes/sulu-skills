---
name: sulu-admin-ui
description: Customize the Sulu 3.0 admin UI where the JavaScript build is involved - the assets/admin build workflow, custom views, styling overrides, CKEditor plugins and config, icons, and changing the /admin URL. Use when a task touches assets/admin, sulu:admin:update-build, admin JS registries, or admin styling.
version: 1.0.0
updated: 2026-09-01
sulu-versions: ">=3.0"
---

# Admin UI customization (Sulu 3.0)

The admin is a React app built from `assets/admin` into `public/build/admin`.
Two states exist: while `assets/admin` matches the sulu/skeleton files,
`bin/adminconsole sulu:admin:update-build` downloads the official prebuilt
build (no Node needed); as soon as project code lands in `assets/admin`, the
project owns the build and must compile it itself.

Check first whether the task needs the JS build at all: lists, forms, tabs,
navigation and most views are configured in XML and the PHP Admin class (see
the [sulu-custom-entity](../sulu-custom-entity/SKILL.md) skill). The build is
only needed for new JavaScript: custom view components, CKEditor plugins,
styling overrides, icons.

## Workflow

1. **Put project code in `app.js`**, never in `index.js` - `index.js` is owned
   by `sulu:admin:update-build` and gets overwritten. `app.js` is imported from
   `index.js` before `startAdmin()` runs, so registry additions land before the
   app boots.
2. **Register through the registries.** Import from the npm package names
   (`sulu-admin-bundle/containers` etc.), not vendor paths:
   ```js
   import {viewRegistry} from 'sulu-admin-bundle/containers';
   viewRegistry.add('app.dashboard', Dashboard);
   ```
   The PHP side references the same key:
   `$this->viewBuilderFactory->createViewBuilder('app.dashboard', '/dashboard', 'app.dashboard')`.
   All registration APIs (views, styling, CKEditor, icons): `references/registration.md`.
3. **Build.** In `assets/admin`: `npm install`, then `npm run build`
   (`npm run watch` during development). Node 20-25 and npm 8-11 per the
   [build cookbook](https://docs.sulu.io/3.x/cookbook/build-admin-frontend.html).
   Details, update-build behavior and troubleshooting: `references/admin-build.md`.
4. **After every `composer update` of sulu/sulu** run
   `bin/adminconsole sulu:admin:update-build` - it re-syncs the `assets/admin`
   skeleton files (merging your `package.json`) and downloads or rebuilds.
   `bin/adminconsole sulu:admin:validate-build` reports version mismatches.

## Changing the admin URL

No JS involved. The `/admin` prefix lives in the project, not the bundle:
change every `prefix: /admin...` in `config/routes/sulu_admin.yaml` and the
`^/admin` patterns in `config/packages/security.yaml` (firewall `pattern`,
`access_control` rows) to the new prefix, then clear caches.

## Pitfalls

- **Never edit `index.js`** and do not add imports there; the next
  update-build run reverts it. `app.js` survives (update-build treats it as
  project-owned and switches to a manual build instead).
- A customized `assets/admin` makes the prebuilt download unavailable
  forever after: every Sulu update then requires a local (or CI) build.
  Keep customizations out of the build when XML/PHP can do the job.
- The sulu-* npm packages are `file:` links into `vendor/sulu/sulu` - npm
  install only works after `composer install`, and a stale build after a
  Sulu update fails with a version-mismatch warning in the admin.
- Icon names must start with `su-` (Sulu icon font) or be Font Awesome
  classes (`fa-*`, `fas`, `fab`); any other name renders nothing and only
  logs a console warning.
- Build errors are usually stale state: remove all `node_modules` folders and
  `package-lock.json` files, `npm cache clean --force`, reinstall (see the
  common-errors section of the build cookbook).
