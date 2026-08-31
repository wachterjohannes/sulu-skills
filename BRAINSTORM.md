# Sulu Skills — Brainstorm

Ideas for Claude (Agent) Skills around Sulu CMS. Sources: the `[Example]` PRs in
[sulu/sulu-demo](https://github.com/sulu/sulu-demo/pulls) (each demonstrates one common
customization), the Sulu bundle ecosystem, and the Sulu MCP tools (SuluMcpBundle /
sulu-mate-extension) for content operations.

Skills fall into three groups with different audiences:

1. **Developer skills** — used inside a Sulu project with Claude Code; encode the
   know-how of the demo example PRs so Claude can apply them to any project.
2. **Content-ops skills** — used in Claude Cowork against a running Sulu instance via
   the MCP tools (pages, articles, snippets, blocks, media, categories, tags).
3. **Maintenance skills** — upgrades, migrations, quality.

---

## 1. Developer skills (mapped from demo PRs)

### sulu-template
Create or modify page/snippet/article templates: the XML structure definition, the Twig
template, registration in the webspace, cache invalidation. Knows the property/content
types, `sulu_content_load`, blocks vs. properties, `sulu-headless` serialization.
This is the highest-frequency task in every Sulu project and pure boilerplate knowledge.

### sulu-block
Add block types to templates, including block settings
(→ [#86 Add additional field to block settings](https://github.com/sulu/sulu-demo/pull/86)),
shared block definitions via XInclude, matching Twig partials.

### sulu-custom-entity
Integrate a custom Doctrine entity into the admin: entity + repository, Admin class,
list XML, form XML, routes, controller/REST API, permissions
(→ [#73](https://github.com/sulu/sulu-demo/pull/73),
[#88 global settings entity](https://github.com/sulu/sulu-demo/pull/88),
[#106 webspace-specific settings](https://github.com/sulu/sulu-demo/pull/106)).
Probably the single most valuable skill: many steps, all conventional, easy to get wrong.

### sulu-extend-entity
Extend built-in entities: extra tab on contacts
(→ [#89](https://github.com/sulu/sulu-demo/pull/89)), modify category list/form
(→ [#92](https://github.com/sulu/sulu-demo/pull/92)), extend the User entity with
attributes and lifecycle events (→ [#118](https://github.com/sulu/sulu-demo/pull/118)),
add properties to the Excerpt & Taxonomies tab
(→ [#76](https://github.com/sulu/sulu-demo/pull/76)).

### sulu-content-type
Create, register and use a custom content type and admin field type, including the
JS build (→ [#79](https://github.com/sulu/sulu-demo/pull/79),
[#66](https://github.com/sulu/sulu-demo/pull/66)).

### sulu-list
List-view customizations: custom field transformers
(→ [#67](https://github.com/sulu/sulu-demo/pull/67)), toolbar actions on list and form
views (→ [#68](https://github.com/sulu/sulu-demo/pull/68),
[#69](https://github.com/sulu/sulu-demo/pull/69)), item actions
(→ [#72](https://github.com/sulu/sulu-demo/pull/72)), list overlay adapters for
selections (→ [#119](https://github.com/sulu/sulu-demo/pull/119)).

### sulu-admin-ui
Admin customizations that require the admin JS build: custom views
(→ [#65](https://github.com/sulu/sulu-demo/pull/65)), styling overrides
(→ [#62](https://github.com/sulu/sulu-demo/pull/62)), CKEditor configuration
(→ [#77](https://github.com/sulu/sulu-demo/pull/77)), custom icon fonts
(→ [#111](https://github.com/sulu/sulu-demo/pull/111)), changing the admin URL
(→ [#60](https://github.com/sulu/sulu-demo/pull/60)). The skill should encode the
`assets/admin` build workflow, which is the part people struggle with most.

### sulu-website
Website-side features: navigation contexts, smart content data providers, search
(SuluSearchBundle), sitemap providers, dynamic robots.txt
(→ [#116](https://github.com/sulu/sulu-demo/pull/116)), error pages, displaying admin
login state on the website (→ [#59](https://github.com/sulu/sulu-demo/pull/59)).

### sulu-webspace
Webspace/portal configuration: localizations, segments, URL schemes, multi-domain
setups, redirects (SuluRedirectBundle), security contexts.

### sulu-headless
Set up and extend SuluHeadlessBundle (or API Platform,
→ [#57](https://github.com/sulu/sulu-demo/pull/57)): serializers, custom endpoints,
frontend consumption patterns.

### sulu-form
SuluFormBundle: dynamic forms, custom form fields, handlers/notifications.

### sulu-testing
Test setup and patterns for Sulu projects: functional tests with SuluTestBundle,
PestPHP integration (→ [#93](https://github.com/sulu/sulu-demo/pull/93)), E2E with
Cypress (→ [#100](https://github.com/sulu/sulu-demo/pull/100)), API tests
(→ [#101](https://github.com/sulu/sulu-demo/pull/101)).

### sulu-devops
Project setup and runtime: skeleton bootstrap, Docker/FrankenPHP setups
(→ [#102](https://github.com/sulu/sulu-demo/pull/102),
[#115](https://github.com/sulu/sulu-demo/pull/115)), deployment checklists
(image formats, varnish/http cache, PHPCR storage choices).

---

## 2. Content-ops skills (MCP-based)

These assume the Sulu MCP tools are connected and target editors/marketers in Cowork
rather than developers.

### sulu-landing-page
Build a landing page from a brief: pick the right template, compose blocks, fill
excerpt/SEO, link media, generate a preview link. Encodes house rules (which blocks
exist, tone, image conventions) per project.

### sulu-content-audit
Crawl the page tree via MCP and report: missing SEO titles/descriptions, empty
excerpts, missing alt texts on media, broken internal links, unpublished drafts,
untranslated pages per locale.

### sulu-translate
Localize a page/article tree into another locale: copy structure, translate content
block by block, keep internal links locale-correct, leave as draft for review.

### sulu-content-import
Migrate content from external sources (old CMS export, CSV, Markdown, URLs) into
pages/articles with correct block mapping, categories, tags and media.

### sulu-media-librarian
Media library housekeeping: fill titles/descriptions/alt texts (from image content),
consistent naming, category/tag assignment, find unused media.

### sulu-editorial-calendar
Work with articles: draft posts in house style from notes, schedule/publish flows
(SuluAutomationBundle), maintain category/tag taxonomy consistently.

---

## 3. Maintenance skills

### sulu-upgrade
Upgrade a project between Sulu versions: run sulu-rector, apply UPGRADE.md entries,
migrate template XML changes, verify the admin build. Encodes the known upgrade
pitfalls per version jump (2.4 → 2.5 → 2.6 → 3.0).

### sulu-phpcr-migration
PHPCR content migrations with SuluPHPCRMigrationBundle: write migrations for template
property renames, block restructuring, bulk content changes — the piece developers
avoid because the API is unfamiliar.

### sulu-doctor
Diagnose a Sulu project: common misconfigurations (missing cache invalidation, wrong
webspace keys, template not registered, permission issues), read logs, propose fixes.

---

## Structure & prioritization

Repo layout: one directory per skill with `SKILL.md` plus `references/` (distilled
diffs from the corresponding demo PRs as reference material) and `templates/`
(file skeletons). Packaged as a Claude Code plugin so a project can install all Sulu
skills at once; content-ops skills separately, since their audience and tool
requirements (MCP connection) differ.

Suggested order by value ÷ effort:

1. **sulu-template** — most frequent task, pure convention.
2. **sulu-custom-entity** — most steps, best demo-PR coverage (#73, #88, #106).
3. **sulu-upgrade** — highest pain, strong differentiator.
4. **sulu-content-audit** — first MCP skill, read-only, safe to ship early.
5. **sulu-admin-ui** — hardest to get right without guidance (JS build).

Open questions:

- Target Sulu 2.6, 3.0, or both? (Skills need version-specific guidance.)
- Should skills fetch the demo PR diffs live, or vendor distilled copies?
  (Vendored copies are stable and reviewable; the PRs are drafts and may change.)
- One big `sulu` skill with routing vs. many small skills? Many small ones trigger
  more precisely and stay maintainable.
