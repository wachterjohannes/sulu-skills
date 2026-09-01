# Extending core lists, forms and views (Sulu 3.0)

Verified against sulu/sulu 3.0 (`XmlFormMetadataLoader`,
`FieldDescriptorFactory`, `ContactAdmin`); the tab pattern re-derives
sulu-demo PR #89 for 3.0.

## Same-key merge for lists and forms

Both metadata loaders collect every XML file from the configured directories
(the skeleton registers `config/lists/` and `config/forms/`; the core bundles
register theirs) and combine files by `<key>`:

- **Forms** (`XmlFormMetadataLoader`): same-key forms are merged; properties
  from the project file are appended.
- **Lists** (`FieldDescriptorFactory`): all properties of all same-key files
  are collected into one descriptor set, keyed by property name; a property
  redefined in a later-loaded file (the project file) replaces the core one.

So adding a column to the contact list is one file:

```xml
<?xml version="1.0" ?>
<list xmlns="http://schemas.sulu.io/list-builder/list">
    <key>contacts</key>

    <properties>
        <property name="externalCrmId" visibility="yes" translation="app.external_crm_id">
            <field-name>externalCrmId</field-name>
            <entity-name>%sulu.model.contact.class%</entity-name>
        </property>
    </properties>
</list>
```

Use `%sulu.model.<name>.class%` as entity name so the descriptor follows the
`objects` override. Core keys worth knowing: lists `contacts`, `accounts`,
`categories`, `media`; forms `contact_details`, `account_details`,
`category_details`, `content_excerpt`, `content_seo`.

**Persistence caveat:** a merged form field renders, but the core endpoint
only saves fields it knows. The excerpt tab (`content_excerpt`) is schemaless
(`excerptData` JSON, mapped by `ExcerptDataMapper`), so merged excerpt fields
persist without code. For everything else use the tab pattern below.

## Extra tab on a core edit view

Four pieces: form XML, Admin view, controller, resource registration.
Prerequisite: entity extended as in `entity-and-objects.md`.

`config/forms/additional_contact_data.xml`: a normal form (see the
sulu-custom-entity skill) with `<key>additional_contact_data</key>`.

`src/Admin/AdditionalContactDataAdmin.php`:

```php
<?php

namespace App\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\ContactBundle\Admin\ContactAdmin;

class AdditionalContactDataAdmin extends Admin
{
    public function __construct(
        private ViewBuilderFactoryInterface $viewBuilderFactory,
    ) {
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if (!$viewCollection->has(ContactAdmin::CONTACT_EDIT_FORM_VIEW)) {
            return;   // ContactBundle views may be permission-gated
        }

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder('app.contact_additional_data', '/additional-data')
                ->setResourceKey('additional_contact_data')
                ->setFormKey('additional_contact_data')
                ->setTabTitle('app.additional_data')
                ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                ->setTabOrder(4096)
                ->setParent(ContactAdmin::CONTACT_EDIT_FORM_VIEW)
        );
    }

    public static function getPriority(): int
    {
        return ContactAdmin::getPriority() - 1;   // run after ContactAdmin
    }
}
```

Controller: a small GET/PUT pair on `/admin/api/additional-contact-data/{id}`
that loads the (extended) contact by id, maps exactly the tab's fields, and
implements `SecuredControllerInterface::getSecurityContext()` returning
`ContactAdmin::CONTACT_SECURITY_CONTEXT`. Route definitions are plain Symfony
routes in 3.0 (no `type: rest`); follow the controller/route pattern from the
sulu-custom-entity skill's `references/rest-api.md`, only `getAction` and
`putAction` are needed.

Resource registration (`config/packages/sulu_admin.yaml`):

```yaml
sulu_admin:
    resources:
        additional_contact_data:
            routes:
                detail: app.get_additional-contact-data
```

The parent resource tab view passes `:id` along, so the tab loads and saves
against the contact id. Add the translation keys, clear caches, and check the
tab appears (permission for the contact context required).

## View keys of the core edit views

`bin/adminconsole sulu:admin:debug-view` lists all; frequently extended:
`sulu_contact.contact_edit_form`, `sulu_contact.account_edit_form`, the
category and user edit forms (verify the exact keys via debug-view). Prefer
the class constants (`ContactAdmin::CONTACT_EDIT_FORM_VIEW`) where the bundle
exposes them.
