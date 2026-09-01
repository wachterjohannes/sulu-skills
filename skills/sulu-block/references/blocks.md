# Block reference (Sulu 3.0)

Verified against sulu/sulu 3.0 (template XSD, content package resolvers, admin form
metadata) and the 3.x docs
([block property type](https://docs.sulu.io/3.x/reference/property-types/block.html),
[templates book page](https://docs.sulu.io/3.x/book/templates.html)).

## Local block - full syntax

```xml
<block name="blocks" default-type="editor" minOccurs="0">
    <meta>
        <title lang="en">Content</title>
    </meta>
    <params>
        <param name="add_button_text">
            <meta>
                <title>app.add_block</title>
            </meta>
        </param>
        <param name="collapsable" value="true"/>
        <param name="movable" value="true"/>
    </params>
    <types>
        <type name="editor">
            <meta>
                <title lang="en">Editor</title>
            </meta>
            <properties>
                <property name="article" type="text_editor">
                    <meta>
                        <title lang="en">Article</title>
                    </meta>
                </property>
            </properties>
        </type>
        <type ref="text_block"/>
    </types>
</block>
```

- Attributes (from `properties-1.0.xsd`): `name` (required), `default-type`,
  `mandatory`, `multilingual`, `minOccurs`, `maxOccurs`, `colspan`, `visibleCondition`,
  `disabledCondition`.
- Params: `add_button_text`, `paste_button_text`, `collapsable` (default true),
  `movable` (default true).
- A `<type>` has either `name` (local) or `ref` (global block key), never both; one of
  the two is required.
- Nested blocks: a type's `<properties>` may contain another `<block>`, to any depth.

## Global blocks

Definition file `config/templates/blocks/text_block.xml` (the directory is
pre-registered as template type `block`; filename = key):

```xml
<?xml version="1.0" ?>
<template xmlns="http://schemas.sulu.io/template/template"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://schemas.sulu.io/template/template http://schemas.sulu.io/template/template-1.0.xsd">

    <key>text_block</key>

    <meta>
        <title lang="en">Text Block</title>
    </meta>

    <properties>
        <property name="title" type="text_line" mandatory="true">
            <meta>
                <title lang="en">Title</title>
            </meta>
        </property>

        <property name="description" type="text_editor">
            <meta>
                <title lang="en">Description</title>
            </meta>
        </property>
    </properties>
</template>
```

No `<view>`, `<controller>` or `url` property. A global block's properties may
themselves contain a `<block>`, including further `<type ref="..."/>` entries. Usage in
any template: `<type ref="text_block"/>`. Stored entries carry `"type": "text_block"`,
so the Twig partial dispatch works unchanged.

## Stored JSON shape

Inside the dimension content's `templateData`:

```json
{
    "blocks": [
        {"type": "editor", "article": "<p>...</p>"},
        {
            "type": "text_block",
            "title": "...",
            "description": "<p>...</p>",
            "settings": {"hidden": true}
        }
    ]
}
```

`settings` appears once the editor touched the block's settings overlay.

## Twig

```twig
{% for block in content.blocks %}
    {% include 'includes/blocks/' ~ block.type ~ '.html.twig' with {
        content: block,
        view: view.blocks[loop.index0],
    } %}
{% endfor %}
```

One partial per type, e.g. `templates/includes/blocks/editor.html.twig`:

```twig
{{ content.article|raw }}
```

A nested block renders by repeating the loop inside the partial over
`content.<nested block name>`. Resolved settings that survive filtering are available as
`block.settings`.

## Block settings

Every `block` field automatically receives `settings_form_key: content_block_settings`
(`BlockSettingsFormMetadataVisitor` in the AdminBundle). The shipped form
(`packages/content/config/forms/content_block_settings.xml`) contains:

- `hidden` - hide the block,
- `schedules` - show only in fixed date ranges or weekly time windows (page package and
  audience targeting merge segment/target-group settings in the same way).

On the website, `HiddenBlockVisitor` and `ScheduleBlockVisitor` (content package) drop
matching blocks during property resolving; the admin and the draft preview still show
them.

**Add custom settings fields** by shipping a form with the same key in `config/forms/`
- forms with an identical `<key>` are merged (`XmlFormMetadataLoader`):

```xml
<?xml version="1.0" ?>
<form xmlns="http://schemas.sulu.io/template/template"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://schemas.sulu.io/template/template http://schemas.sulu.io/template/form-1.0.xsd">

    <key>content_block_settings</key>

    <properties>
        <property name="full_width" type="checkbox">
            <params>
                <param name="label">
                    <meta>
                        <title>app.full_width</title>
                    </meta>
                </param>
                <param name="type" value="toggler"/>
            </params>

            <tag name="sulu.block_setting_icon" icon="su-expand"/>
        </property>
    </properties>
</form>
```

The `sulu.block_setting_icon` tag shows an icon on the collapsed block when the setting
is active. A single block can use a completely separate form via
`<param name="settings_form_key" value="my_block_settings"/>`.

**React to settings on the website** either in the partial (`block.settings.full_width`)
or, to filter blocks out entirely, with a visitor:

```php
use Sulu\Content\Application\PropertyResolver\BlockVisitor\BlockVisitorInterface;

class FullWidthBlockVisitor implements BlockVisitorInterface
{
    public function visit(array $block): ?array
    {
        // return null to remove the block, or a (modified) $block to keep it
        return $block;
    }
}
```

```yaml
services:
    App\Content\FullWidthBlockVisitor:
        tags: ['sulu_content.block_visitor']
```
