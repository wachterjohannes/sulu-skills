---
name: sulu-content-entity
description: Equip a custom Doctrine entity with Sulu 3.0's content system (SuluContentBundle) - localized, versioned, template-based content with draft/publish workflow, SEO/excerpt tabs, preview and website rendering. Use when a custom entity should behave like pages/articles (own templates, publish workflow, content tabs) rather than a plain admin CRUD entity.
version: 1.0.0
updated: 2026-08-31
sulu-versions: ">=3.0"
---

# Content-rich custom entity (Sulu 3.0)

Sulu 3.0's content system (`Sulu\Content\…`, the former SuluContentBundle) splits a
content-rich entity in two:

- the **main entity** (identity only) implementing `ContentRichEntityInterface`,
- a **dimension content entity** holding the actual data once per dimension
  (locale × stage draft/live × version), composed from traits: template data, SEO,
  excerpt, workflow, routing, author, shadow.

Articles in 3.0 are built exactly this way - `packages/article` in sulu/sulu and the
`ExampleTestBundle` in `packages/content/tests/Application/` are the reference
implementations this skill is derived from.

Prerequisite: the plain-entity mechanics (Admin class, list XML, routes, resource
registration) from the [sulu-custom-entity](../sulu-custom-entity/SKILL.md) skill -
only the content-specific deltas are described here.

## Workflow

Use `Event`/`events`/template type `event` as placeholders.

1. **Entities** - `Event` (uses `ContentRichEntityTrait`, implements
   `createDimensionContent()`) and `EventDimensionContent` (the trait stack).
   Start from `templates/Event.php` and `templates/EventDimensionContent.php`.
   Only id/relation/denormalized fields need Doctrine mapping - the content
   package's `MetadataLoader` maps all trait fields automatically. See
   `references/entity.md`.
2. **Template type + templates** - register a template directory for the new type
   in `config/packages/sulu_admin.yaml` (`sulu_admin.templates.event`), then create
   template XMLs there exactly as in the sulu-template skill (with `url` property
   of type `route` if the entity has website pages). The dimension content's
   `getTemplateType()` must return this type.
3. **Admin class** - like a plain entity, but tab views come from
   `ContentViewBuilderFactoryInterface` (`sulu_content.content_view_builder_factory`),
   which generates the content/SEO/excerpt tabs from the template metadata; add
   `PermissionTypes::LIVE` to the security context. List and tab views need
   locales (`addLocales`). See `references/admin-api.md`.
4. **REST controller + routes** - the controller works through
   `ContentManagerInterface` (`resolve`/`persist`/`copy`/`applyTransition`) instead
   of mapping fields itself, and handles the workflow actions
   (`publish`, `unpublish`, `copy_locale`, `remove_draft`, `restore`) via the
   `?action=` query parameter. Every route gets
   `options: { api_dimension_listener: true }`. See `references/admin-api.md`.
5. **Website & preview (if the entity has its own URLs)** - a
   `RouteDefaultsProvider` renders published content through the template's
   controller/view, and the generic `ContentObjectProvider` service enables admin
   preview. Optional integrations (teaser, link, smart content, selection property
   resolver + resource loader) are each one tagged service. See
   `references/website.md`.
6. **Migration, translations, cache** - as with plain entities:
   `doctrine:migrations:diff` + `migrate` (expect two tables, e.g. `events` and
   `event_dimension_contents`), admin translation keys, cache:clear for admin and
   website, grant the new permission to the role.

## Pitfalls

- **Don't hand-map trait fields** in the dimension content's Doctrine mapping -
  the MetadataLoader does it; duplicating columns breaks the schema diff.
- The dimension content table gets **multiple rows per entity** (per locale,
  stage, version). Never query it directly for "the" content - always resolve
  through `ContentManager`/`ContentAggregator` with dimension attributes.
- Workflow actions that cross dimensions (`copy_locale`, `restore`, `unpublish`,
  `remove_draft`, publishing) need the repository to load **all** dimension
  contents, not just the current dimension's - loading too narrowly causes silent
  data loss on publish.
- The template's `url` property only produces routes if the dimension content uses
  `RoutableTrait` and the entity has a `RouteDefaultsProvider` - one without the
  other yields templates that save but pages that 404.
- Resource key, template type, `getResourceKey()`, the preview provider-key, the
  route-defaults `resource_key` and the smart-content/teaser aliases must all
  agree - grep for the resource key across the project when something doesn't show
  up.
