---
name: sulu-custom-entity
description: Integrate a plain custom Doctrine entity into the Sulu 3.0 admin - entity, list view, form, REST API, navigation and permissions. Use when a task asks to manage a project-specific entity (e.g. events, locations, FAQs) in the Sulu admin. For entities that need localized/versioned page-like content (templates, SEO, publish workflow), use the sulu-content-entity skill instead.
version: 1.0.0
updated: 2026-08-31
sulu-versions: ">=3.0"
---

# Custom entity in the Sulu 3.0 admin

Seven pieces, all conventional. Use one consistent resource key throughout (plural,
snake_case - e.g. `events`); mismatches between the pieces are the main failure mode.

## Workflow

Use `Event`/`events` below as placeholder for the actual entity.

1. **Entity + repository** - plain Doctrine entity with attributes in `src/Entity/`
   (the skeleton maps `App\Entity` with attribute mapping), repository in
   `src/Repository/`. Add a `RESOURCE_KEY` constant to the entity:
   ```php
   public const RESOURCE_KEY = 'events';
   ```
2. **List XML** - `config/lists/events.xml` (this directory is pre-registered in
   `config/packages/sulu_admin.yaml`). `<key>` = resource key. Start from
   `templates/list.xml` in this skill; details and filter types in
   `references/list-form-xml.md`.
3. **Form XML** - `config/forms/event_details.xml` (also pre-registered). Uses the
   same schema and property types as page templates. Start from
   `templates/form.xml`; see `references/list-form-xml.md`.
4. **REST controller + routes** - controller in `src/Controller/Admin/` providing
   `cgetAction`/`getAction`/`postAction`/`putAction`/`deleteAction`, plus route
   definitions under `/admin/api/events`. The list action MUST support the
   `?flat=true` listing protocol via the DoctrineListBuilder. See
   `references/rest-api.md` - this is the fiddliest part.
5. **Resource registration** - map the resource key to the routes in
   `config/packages/sulu_admin.yaml`:
   ```yaml
   sulu_admin:
       resources:
           events:
               routes:
                   list: app.get_events
                   detail: app.get_event
   ```
6. **Admin class** - `src/Admin/EventAdmin.php` extending
   `Sulu\Bundle\AdminBundle\Admin\Admin`. Subclasses are autoconfigured (tagged
   `sulu.admin`) - no service config needed in the skeleton. Defines navigation,
   list/form views and security contexts. See `references/admin-class.md`.
7. **Migration + translations** - `bin/console doctrine:migrations:diff` and
   `... migrate`; add navigation/label keys to `translations/admin.en.json` (and
   other admin locales).

Then clear caches (`bin/adminconsole cache:clear`) and verify: navigation item
appears (after granting the new permission in the user's role!), list loads, an
entity can be created, edited and deleted.

## Pitfalls

- **The new security context starts unassigned.** Until the role gets permissions
  for it (Settings → User Roles), the navigation item is invisible - this looks
  like a broken Admin class but isn't.
- The list endpoint must return a `PaginatedRepresentation` keyed by the resource
  key, and the route names in `sulu_admin.resources` must exist - otherwise the
  list view stays empty or 404s without a clear error.
- `AbstractRestController` is deprecated since 2.6 but still what all core bundles
  use in 3.0. Using it is fine; alternatively build a plain controller and inject
  the same services (autowiring aliases exist for `FieldDescriptorFactoryInterface`,
  `DoctrineListBuilderFactoryInterface` and `RestHelperInterface`).
- Implement `SecuredControllerInterface` on the controller so Sulu enforces the
  security context on the API, not only in the UI.
- Form `<key>` (`event_details`) and list `<key>` (`events`) are different keys
  referenced from different view builders (`setFormKey` vs `setListKey`) - don't
  unify them.
