---
name: sulu-discover
description: Use before relying on memory for a Sulu API, template structure, webspace, route, service or admin view - look it up in the project instead. The project's files, consoles and installed source are authoritative; recalled knowledge may not match the installed Sulu version.
version: 1.0.0
updated: 2026-08-31
sulu-versions: ">=3.0"
---

# Discover, don't guess

The project itself is authoritative - its config files, consoles and installed source
answer most questions faster and more reliably than memory, which may lag behind the
installed Sulu version. Ask the project.

## What is installed

`composer.json` gives the Sulu version (`sulu/sulu: ~3.0`) and the optional bundles
(form, redirect, headless, automation). `config/bundles.php` shows what is actually
enabled. Assume nothing that neither file confirms.

## The project's own structure

The existing files beat any example - copy their conventions, not remembered ones:

| Question | Look in |
|---|---|
| Which templates exist, which properties/blocks they use | `config/templates/{pages,snippets,articles}/` |
| Webspaces, locales, navigation contexts, default templates | `config/webspaces/*.xml` |
| Admin lists and forms | `config/lists/`, `config/forms/` |
| Twig side of every template | `templates/` |
| Image formats | `config/image-formats.xml` |
| Sulu bundle configuration | `config/packages/sulu_*.yaml` |

## What exists at runtime

Sulu has **two kernels**: `bin/adminconsole` and `bin/websiteconsole` run the same
commands against different containers - admin-tagged services and `/admin` routes only
exist in the first. `bin/console` alone is not enough evidence that something is missing.

```bash
bin/adminconsole sulu:admin:info                  # sulu version + environment info
bin/adminconsole sulu:admin:debug-view            # every registered admin view
bin/adminconsole sulu:admin:debug-view <name>     # one view in detail
bin/adminconsole debug:router                     # admin + api routes
bin/websiteconsole debug:router                   # website routes
bin/adminconsole debug:container --tag=sulu.admin # registered Admin classes
bin/adminconsole debug:config sulu_admin          # resources, templates, field types
bin/adminconsole debug:autowiring <name>          # what a type-hint resolves to
```

Integration points are tagged services - `debug:container --tag=<tag>` for
`sulu.context`, `sulu_content.property_resolver`, `sulu_content.resource_loader`,
`sulu_content.smart_content_provider`, `sulu_route.route_defaults_provider`,
`sulu.teaser.provider`, `sulu.link.provider`, `sulu_preview.object_provider`.

## After changing things

In the dev environment most edits are picked up automatically - Symfony rebuilds the
affected caches on the next request, so a cache clear is not required after every
change. But template XML, webspace XML, lists and forms are all cached, and when an
edit seems to do nothing, clearing is always the safe fallback before hunting for
another cause:

```bash
bin/adminconsole cache:clear && bin/websiteconsole cache:clear
```

## When the console cannot answer

Read the installed source, not memory: `vendor/sulu/sulu/` contains the packages
(`packages/content`, `packages/page`, …) including reference implementations - the
article package and `packages/content/tests/Application/ExampleTestBundle` show how a
content-rich entity is wired end to end.
