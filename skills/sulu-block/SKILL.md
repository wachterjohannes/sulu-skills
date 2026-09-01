---
name: sulu-block
description: Add or change block types in Sulu 3.0 templates - local block types, shared global blocks, block settings (hide, schedules, custom fields) and the matching Twig partials. Use when a task adds repeatable content sections to a template or shares one block definition across several templates.
version: 1.0.0
updated: 2026-09-01
sulu-versions: ">=3.0"
---

# Blocks (Sulu 3.0)

A block is a repeatable, typed content section inside a template. Three building blocks matter: the `<block>` element in the template XML (local types), global block files in `config/templates/blocks/` (shared types), and the per-type Twig partials. Block settings (hide, scheduling) come for free. Template basics are covered by the [sulu-template](../sulu-template/SKILL.md) skill; only the block-specific parts are described here.

## Workflow

1. **Local block type.** Inside the template's `<properties>`, add `<block name="blocks" default-type="...">` with one `<type name="...">` per variant; each type carries its own `<properties>`, and a type may nest another `<block>`. Full syntax, attributes and params in `references/blocks.md`.
2. **Shared (global) block.** For a type used by several templates, create `config/templates/blocks/<key>.xml` (a `<template>` file with `<key>`, `<meta>` and `<properties>`, no view/controller) and reference it as `<type ref="<key>"/>` in any template's block. The stored and rendered `block.type` equals the global block's key. This replaces the 2.x XInclude approach for sharing blocks.
3. **Twig.** Render one partial per type; hidden and out-of-schedule blocks are already removed server-side:
   ```twig
   {% for block in content.blocks %}
       {% include 'includes/blocks/' ~ block.type ~ '.html.twig' with {
           content: block,
           view: view.blocks[loop.index0],
       } %}
   {% endfor %}
   ```
4. **Settings.** Every block automatically gets the settings icon in the admin (form key `content_block_settings`: hide block plus fixed/weekly schedules). Extend the form with a same-key XML in `config/forms/`, or point one block at its own form via `<param name="settings_form_key" value="..."/>`. React to custom settings on the website with a tagged block visitor. Details in `references/blocks.md`.
5. **Clear caches and verify**: `bin/adminconsole cache:clear && bin/websiteconsole cache:clear`, then check the admin form (types dropdown, settings overlay) and the website rendering.

## Pitfalls

- **Renaming a block type orphans stored entries.** Existing content keeps the old `type` string; the resolver logs an error and falls back to `default-type` (in debug mode it throws). Renames need a content migration (see the [sulu-content-migration](../sulu-content-migration/SKILL.md) skill).
- Keep global block keys and local type names unique across the project - mixing is supported, but a local type shadowing a global key is confusing (docs recommendation).
- A block with `minOccurs="1"` and `maxOccurs="1"` resolves to a single block object instead of a list - a `{% for %}` over it breaks. Use distinct markup for that case.
- Hidden blocks vanish from the website but stay visible in the admin and in the preview of the draft; a "missing" block on production is usually a hidden or scheduled one, not lost content.
- Sharing block XML between templates via `xi:include` is 2.x lore; there is no XInclude handling in the 3.0 template loader. Use global blocks.
