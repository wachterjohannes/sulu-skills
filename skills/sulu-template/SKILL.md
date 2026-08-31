---
name: sulu-template
description: Create or modify page, snippet or article templates in a Sulu 3.0 project — the XML structure definition, the matching Twig template, registration in the webspace and cache handling. Use whenever a task involves adding a new page type, changing template properties, or wiring a template into a webspace.
version: 1.0.0
updated: 2026-08-31
sulu-versions: ">=3.0"
---

# Sulu 3.0 templates

A template consists of two files that must stay in sync:

1. **XML structure definition** — `config/templates/<type>/<key>.xml`
   with `<type>` one of `pages`, `snippets`, `articles`. The file name (without
   `.xml`) MUST equal the `<key>` element inside the file.
2. **Twig template** — `templates/<view>.html.twig`, where `<view>` is the `<view>`
   value from the XML (e.g. `<view>pages/event</view>` → `templates/pages/event.html.twig`).
   Snippets have no view/controller — they render inside the page that references them.

## Workflow

1. **Start from an existing template.** Read `config/templates/` in the project and
   copy the closest existing XML as base; same for the Twig side. Only fall back to
   `templates/page.xml` and `templates/page.html.twig` from this skill when the
   project has nothing suitable.
2. **Write the XML.** Rules that must hold:
   - `<key>` = filename.
   - Page and article templates need `<view>`, `<controller>` and `<cacheLifetime>`.
     The 3.0 default controller is
     `Sulu\Content\UserInterface\Controller\Website\ContentController::indexAction`.
   - Page templates need a `title` property tagged `sulu.rlp.part` and a `url`
     property of type `route` tagged `sulu.rlp`. Article templates use
     type `page_tree_route` for `url` instead. Snippet templates need a `title`
     property tagged `sulu.node.name` and no `url`.
   - Every property carries `<meta><title lang="en">…</title></meta>`; add all
     locales the project's webspaces define.
   - See `references/template-xml.md` for the full annotated structure, common
     property types and block syntax.
3. **Write the Twig template.** Extend the project's `base.html.twig`; property
   values are available as `content.<name>`, SEO/excerpt data as `extension.seo` /
   `extension.excerpt`. See `references/twig.md` for the 3.0 variables and functions
   (several were renamed from 2.x).
4. **Register where needed.** Templates in `config/templates/pages/` are picked up
   automatically and appear in the admin's template dropdown. Touch
   `config/webspaces/*.xml` only when the template should become a
   `<default-template>` (types `page`, `home`) or be blocked via
   `<excluded-templates>`.
5. **Clear caches and verify.**
   ```bash
   bin/adminconsole cache:clear
   bin/websiteconsole cache:clear
   ```
   Verify by listing the admin's known templates or loading a page with the new
   template. If the template does not appear in the admin dropdown, the key/filename
   mismatch or a schema violation in the XML is the most likely cause — validate
   against the XSD referenced in the file header.

## Pitfalls

- **2.x knowledge does not transfer blindly.** In 3.0 the website controller FQCN,
  the `url` property type (`route` instead of `resource_locator`) and several Twig
  function names changed. When in doubt, trust the project's existing files and
  `references/`, not memory of Sulu 2.x.
- Changing a property **name** in a template that already has content orphans the
  stored values — that is a content migration (see the `sulu-content-migration`
  skill), not a template edit.
- The `homepage` template type is separate: it is assigned via the webspace's
  `<default-template type="home">`, not selectable per page.
- Missing `mandatory="true"` on `title`/`url` of a page template breaks saving in
  subtle ways; keep both mandatory.
