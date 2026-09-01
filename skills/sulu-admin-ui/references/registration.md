# Admin JS registration reference (Sulu 3.0)

Verified against `AdminBundle/Resources/js` in sulu/sulu 3.0. Everything below goes into
`assets/admin/app.js` and requires a rebuild (`npm run build`).

## Custom view

JS side: register a React component under a view type key:

```js
// assets/admin/app.js
import {viewRegistry} from 'sulu-admin-bundle/containers';
import Dashboard from './views/Dashboard';

viewRegistry.add('app.dashboard', Dashboard);
```

PHP side: an Admin class creates a view whose third argument is that key:

```php
$viewCollection->add(
    $this->viewBuilderFactory->createViewBuilder('app.dashboard', '/dashboard', 'app.dashboard')
);
```

`createViewBuilder(string $name, string $path, string $type)`: `$name` is the route/view
name, `$path` the admin URL, `$type` the JS registry key. Add a `NavigationItem`
pointing at the view name to make it reachable (see the sulu-custom-entity skill's
admin-class reference). The component receives router props; extend existing behavior by
composing components exported from `sulu-admin-bundle/containers` and
`sulu-admin-bundle/components`.

## Styling overrides

Plain css imported from `app.js` lands in the extracted admin css:

```js
import './app.css';
```

`.css` files are global (css-loader), `.scss` files are treated as css modules with
postcss. Overriding admin styles means global css targeting the admin's class names;
keep it minimal, the hashed module class names are not a stable API.

## CKEditor (text_editor field type)

```js
import {ckeditorConfigRegistry, ckeditorPluginRegistry} from 'sulu-admin-bundle/containers';
import Superscript from '@ckeditor/ckeditor5-basic-styles/src/superscript';

ckeditorPluginRegistry.add(Superscript);
ckeditorConfigRegistry.add((config) => ({
    toolbar: [...config.toolbar, 'superscript'],
}));
```

- `ckeditorPluginRegistry.add(PluginClass)` appends a CKEditor 5 plugin class to every
  editor instance.
- `ckeditorConfigRegistry.add((config) => partialConfig)` receives the current config
  and returns an object that is merged over it (all registered callbacks are reduced in
  order).
- Install the plugin package into `assets/admin` (`npm install
  @ckeditor/ckeditor5-basic-styles`); update-build keeps such extra dependencies via its
  package.json merge.
- Per-property editor options (link targets etc.) are template XML params of the
  `text_editor` property type, no JS needed: see the
  [property types reference](https://docs.sulu.io/3.x/reference/property-types/index.html).

## Icons

The `Icon` component accepts `su-*` names (Sulu's bundled icon font) and Font Awesome
names (`fa-*`, `fas ...`, `fab ...`); anything else logs a warning and renders nothing.
Font Awesome (free) is bundled completely, so custom icons are usually just `fa-*` names
in `NavigationItem::setIcon()` etc. Additional `su-*` glyphs require shipping your own
`@font-face` css (imported via `app.js`) that defines the missing `su-<name>` classes.
