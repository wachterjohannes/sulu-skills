---
name: sulu-extend-entity
description: Extend Sulu's built-in entities and admin screens in a Sulu 3.0 project - extra columns on contact/account/user/category/media/tag, added or changed fields in core lists and forms, an extra tab on a core edit view, extra excerpt properties, reacting to user lifecycle events. Use when a task modifies something Sulu ships rather than adding a new entity.
version: 1.0.0
updated: 2026-09-01
sulu-versions: ">=3.0"
---

# Extending built-in entities (Sulu 3.0)

Three independent mechanisms, combinable:

1. **Swap the entity class** via the persistence `objects` config: core entities are
   Doctrine mapped-superclasses; the configured `model` becomes the concrete entity, so
   a project subclass can add columns.
2. **Merge list/form XML by key**: files in `config/lists/` and `config/forms/` with the
   same `<key>` as a core file merge into it; new properties are added, a redefined
   property (same name) replaces the core definition.
3. **Add views** from a project Admin class: an extra tab hangs below a core edit view
   via `setParent()`.

## Workflow

### Extra columns on a core entity

1. `App\Entity\Contact extends SuluContact` with `#[ORM\Entity]` and the **table of the
   extended entity** (`#[ORM\Table(name: 'co_contacts')]`); new columns nullable or with
   defaults. Then point the object config at it:
   ```yaml
   sulu_contact:
       objects:
           contact:
               model: App\Entity\Contact
   ```
2. `bin/console doctrine:migrations:diff` + `migrate`, clear caches. All config keys
   (contact, account, user, role, category, media, tag, ...) and the code pattern:
   `references/entity-and-objects.md`.

### Fields in core lists and forms

Drop `config/lists/contacts.xml` or `config/forms/contact_details.xml` containing only
`<key>` plus the extra/changed properties; they merge with the core file. Rendering is
not persistence: a new form field only saves if the entity has the column AND the API
accepts the value (see the tab pattern for the reliable way). Details:
`references/admin-extension.md`.

### Extra tab on a core edit view

Own Admin class with `getPriority()` below the core Admin, guard with
`$viewCollection->has(...)`, add a form view with
`->setParent(ContactAdmin::CONTACT_EDIT_FORM_VIEW)`, own form key, own small GET/PUT
controller and `sulu_admin.resources` entry. Full 3.0 code:
`references/admin-extension.md`.

### Extra excerpt properties

`config/forms/content_excerpt.xml` with `<key>content_excerpt</key>` merges into the
excerpt tab of every content entity; values persist without further code because the
whole excerpt payload lands in the `excerptData` JSON column.

### React to user changes

Sulu dispatches domain events (`UserCreatedEvent`, `UserModifiedEvent`, ...) through the
regular Symfony event dispatcher; register a normal listener. See
`references/entity-and-objects.md`.

## Pitfalls

- **New columns need defaults or nullable** - core code instantiates and hydrates the
  entity without knowing them; a non-nullable column without default breaks user/contact
  creation.
- The `objects` model can only be swapped once per object; a bundle that also overrides
  it conflicts with the project override.
- Overriding a list property assumes the project directory loads after the core one (it
  does in the skeleton setup); verify the rendered list once.
- Merged form fields on core forms (contact details etc.) render but silently do not
  persist unless the endpoint handles them; the excerpt tab is the exception (schemaless
  JSON storage). Prefer the own-tab-own-endpoint pattern for anything that must save.
- Domain events fire only through Sulu's managers/controllers; raw Doctrine writes
  bypass them - use a Doctrine entity listener for those.
- Implement the tab controller's `getSecurityContext()` with the core context (e.g.
  `ContactAdmin::CONTACT_SECURITY_CONTEXT`) or the tab bypasses the contact permissions.
