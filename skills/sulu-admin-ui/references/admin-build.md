# Admin build reference (Sulu 3.0)

Verified against the sulu/skeleton `3.0` branch and `UpdateBuildCommand` in
sulu/sulu. Authoritative docs:
[build cookbook](https://docs.sulu.io/3.x/cookbook/build-admin-frontend.html).

## Layout

| Path | Role |
| --- | --- |
| `assets/admin/index.js` | entry; imports all sulu bundles + `app.js`, calls `startAdmin()`; owned by update-build |
| `assets/admin/app.js` | project code: registry additions, css imports, extra bundle imports |
| `assets/admin/package.json` | npm scripts `build`/`watch`; sulu packages as `file:` links into `vendor/sulu/sulu` |
| `assets/admin/webpack.config.js` | thin wrapper delegating to `vendor/sulu/sulu/webpack.config.js` |
| `public/build/admin/` | build output (hashed js/css/fonts + `manifest.json`) |

The sulu npm packages point into the vendor dir, some at
`src/Sulu/Bundle/<X>Bundle/Resources/js`, the 3.0 packages at
`packages/<x>/assets/js` (page, snippet, route, search, custom-url). The
`file:` links mean `composer install` must run before `npm install`.

## Building

```bash
cd assets/admin
npm install
npm run build    # webpack --mode production
npm run watch    # webpack --mode development --watch
```

Engines (package.json + cookbook): Node 20-25, npm 8-11; bun/pnpm work too
(`bun install && bun run build`). css goes through postcss (postcss-import,
nested, simple-vars, calc, hexrgba, autoprefixer); `.scss` files are css
modules; webpack emits to `public/build/admin` and writes `manifest.json`.

## sulu:admin:update-build

`bin/adminconsole sulu:admin:update-build` compares these `assets/admin`
files against the sulu/skeleton tag matching the installed sulu/sulu version:
`app.js`, `index.js`, `package.json`, `webpack.config.js`,
`babel.config.json`, `.browserslistrc`, `.npmrc`, `postcss.config.js`.

- All files unchanged and a tagged sulu version installed: it downloads the
  skeleton zip and copies its prebuilt `public/build/admin` over the local
  one. Requires `ext-zip`. No Node needed.
- `app.js` differs (project code) or a non-tagged version is installed: it
  offers to update the other files (with a JSON merge for `package.json`, so
  extra project npm dependencies survive), cleans up old `node_modules`
  folders, then runs `npm install` and `npm run build` itself.

`bin/adminconsole sulu:admin:validate-build` checks that the deployed build
was compiled for the installed sulu/sulu version (the build embeds
`SULU_ADMIN_BUILD_VERSION` from composer.lock).

## Troubleshooting

From the cookbook's common-errors section, in order:

1. Check Node/npm versions (`node -v`, `npm -v`) against the ranges above.
2. Delete every `node_modules` folder and `package-lock.json` (also the ones
   inside `vendor/sulu/sulu/...` js folders), then `npm cache clean --force`
   and reinstall.
3. Re-run `composer install` first when `file:` dependencies fail to resolve.

`link-sulu-bundles.sh`/`unlink-sulu-bundles.sh` in `assets/admin` symlink a
sulu/sulu development checkout into the build; only needed when working on
sulu itself.
