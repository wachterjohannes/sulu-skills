# Sulu Skills — Brainstorm

Claude (Agent) Skills for **Sulu 3.0** project development. Sources: the `[Example]` PRs
in [sulu/sulu-demo](https://github.com/sulu/sulu-demo/pulls) (each demonstrates one
common customization), the Sulu bundle ecosystem, and the Sulu docs.

**Scope decision:** The skills target Sulu 3.0. The demo example PRs were written
against Sulu 2.x, so they serve as the *catalog of tasks worth covering* — the concrete
guidance in each skill must be written and verified against 3.0 (new content
architecture via SuluContentBundle, changed template/config conventions). Where a 2.x
pattern no longer applies in 3.0, the skill documents the 3.0 way, not the PR's diff.

The core of the repo are **implementation skills** (developer + maintenance). MCP-based
content-ops skills (content audit etc.) are kept as ideas in the backlog at the bottom.

---

## Implementation skills (core)

### sulu-template
Create or modify page/snippet/article templates: the XML structure definition, the Twig
template, registration in the webspace, cache invalidation. Knows the property/content
types, blocks vs. properties, `sulu_content_load`, and 3.0 template conventions.
The highest-frequency task in every Sulu project and pure boilerplate knowledge.

### sulu-block
Add block types to templates, including block settings
(task from [#86](https://github.com/sulu/sulu-demo/pull/86)), shared block definitions
via XInclude, matching Twig partials.

### sulu-custom-entity
Integrate a custom Doctrine entity into the admin: entity + repository, Admin class,
list XML, form XML, routes, controller/REST API, permissions — in 3.0 ideally
content-aware via SuluContentBundle where versioned/localized content is needed
(tasks from [#73](https://github.com/sulu/sulu-demo/pull/73),
[#88 global settings entity](https://github.com/sulu/sulu-demo/pull/88),
[#106 webspace-specific settings](https://github.com/sulu/sulu-demo/pull/106)).
Probably the single most valuable skill: many steps, all conventional, easy to get wrong.

### sulu-extend-entity
Extend built-in entities: extra tab on contacts
([#89](https://github.com/sulu/sulu-demo/pull/89)), modify category list/form
([#92](https://github.com/sulu/sulu-demo/pull/92)), extend the User entity with
attributes and lifecycle events ([#118](https://github.com/sulu/sulu-demo/pull/118)),
add properties to the Excerpt & Taxonomies tab
([#76](https://github.com/sulu/sulu-demo/pull/76)).

### sulu-content-type
Create, register and use a custom content type and admin field type, including the
admin JS build ([#79](https://github.com/sulu/sulu-demo/pull/79),
[#66](https://github.com/sulu/sulu-demo/pull/66)).

### sulu-list
List-view customizations: custom field transformers
([#67](https://github.com/sulu/sulu-demo/pull/67)), toolbar actions on list and form
views ([#68](https://github.com/sulu/sulu-demo/pull/68),
[#69](https://github.com/sulu/sulu-demo/pull/69)), item actions
([#72](https://github.com/sulu/sulu-demo/pull/72)), list overlay adapters for
selections ([#119](https://github.com/sulu/sulu-demo/pull/119)).

### sulu-admin-ui
Admin customizations that require the admin JS build: custom views
([#65](https://github.com/sulu/sulu-demo/pull/65)), styling overrides
([#62](https://github.com/sulu/sulu-demo/pull/62)), CKEditor configuration
([#77](https://github.com/sulu/sulu-demo/pull/77)), custom icon fonts
([#111](https://github.com/sulu/sulu-demo/pull/111)), changing the admin URL
([#60](https://github.com/sulu/sulu-demo/pull/60)). The skill should encode the
`assets/admin` build workflow for 3.0 — the part people struggle with most.

### sulu-website
Website-side features: navigation contexts, smart content data providers, search,
sitemap providers, dynamic robots.txt ([#116](https://github.com/sulu/sulu-demo/pull/116)),
error pages, displaying admin login state on the website
([#59](https://github.com/sulu/sulu-demo/pull/59)).

### sulu-webspace
Webspace/portal configuration: localizations, segments, URL schemes, multi-domain
setups, redirects (SuluRedirectBundle), security contexts.

### sulu-headless
Set up and extend headless delivery (SuluHeadlessBundle / API Platform,
[#57](https://github.com/sulu/sulu-demo/pull/57)): serializers, custom endpoints,
frontend consumption patterns.

### sulu-form
SuluFormBundle: dynamic forms, custom form fields, handlers/notifications.

### sulu-testing
Test setup and patterns for Sulu projects: functional tests with SuluTestBundle,
PestPHP integration ([#93](https://github.com/sulu/sulu-demo/pull/93)), E2E with
Cypress ([#100](https://github.com/sulu/sulu-demo/pull/100)), API tests
([#101](https://github.com/sulu/sulu-demo/pull/101)).

### sulu-devops
Project setup and runtime: 3.0 skeleton bootstrap, Docker/FrankenPHP setups
([#102](https://github.com/sulu/sulu-demo/pull/102),
[#115](https://github.com/sulu/sulu-demo/pull/115)), deployment checklists
(image formats, http cache, storage configuration).

## Maintenance skills

### sulu-upgrade
Upgrade a project to Sulu 3.0 (and between 3.x versions): run sulu-rector, apply
UPGRADE.md entries, migrate template XML changes, migrate content off PHPCR onto the
3.0 content architecture, verify the admin build. Encodes the known pitfalls of the
2.x → 3.0 jump — highest-pain task in the ecosystem right now.

### sulu-content-migration
Write content migrations for structural changes: template property renames, block
restructuring, bulk content changes — the 3.0 equivalent of what
SuluPHPCRMigrationBundle covered in 2.x.

### sulu-doctor
Diagnose a Sulu project: common misconfigurations (missing cache invalidation, wrong
webspace keys, template not registered, permission issues), read logs, propose fixes.

---

## Structure & prioritization

Repo layout: raw skills — one directory per skill with `SKILL.md` plus `references/`
(distilled, 3.0-verified guidance; the demo PRs serve as the task catalog, not as copy
sources) and `templates/` (file skeletons). No Claude Code plugin packaging for now;
distribution later possibly as a Symfony AI Mate extension (like sulu-mate-extension),
so a Sulu project pulls the skills in via Composer and `mate discover`. Keeping the
skills raw and tool-agnostic keeps that path open.

Suggested order by value ÷ effort:

1. **sulu-template** — most frequent task, pure convention.
2. **sulu-custom-entity** — most steps, biggest payoff.
3. **sulu-upgrade** — 2.x → 3.0 is the acute pain point; also drives 3.0 adoption.
4. **sulu-admin-ui** — hardest to get right without guidance (JS build).
5. **sulu-block / sulu-list / sulu-extend-entity** — round out the everyday tasks.

Open questions:

- Which parts of the 2.x demo PR catalog have no 3.0 equivalent yet? Needs a pass
  over the 3.0 docs/skeleton per skill before writing.
- One big `sulu` skill with routing vs. many small skills? Many small ones trigger
  more precisely and stay maintainable.

---

## Backlog: content-ops skill ideas (MCP-based, not part of the core)

Kept as ideas — these target editors/marketers in Cowork via the Sulu MCP tools
(SuluMcpBundle / sulu-mate-extension) rather than developers:

- **sulu-content-audit** — crawl the page tree and report missing SEO
  titles/descriptions, empty excerpts, missing alt texts, broken internal links,
  unpublished drafts, untranslated pages per locale.
- **sulu-landing-page** — build a landing page from a brief with existing blocks,
  fill excerpt/SEO, generate a preview link.
- **sulu-translate** — localize a page/article tree into another locale as drafts.
- **sulu-content-import** — migrate content from external sources (CMS export, CSV,
  Markdown, URLs) into pages/articles.
- **sulu-media-librarian** — media library housekeeping: alt texts, naming,
  categories, unused media.
- **sulu-editorial-calendar** — article drafting in house style, scheduling via
  SuluAutomationBundle, taxonomy consistency.
