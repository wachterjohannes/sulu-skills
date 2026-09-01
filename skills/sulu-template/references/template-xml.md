# Template XML reference (Sulu 3.0)

Verified against the [sulu/skeleton `3.0` branch](https://github.com/sulu/skeleton/tree/3.0).

## Page template - annotated

```xml
<?xml version="1.0" ?>
<template xmlns="http://schemas.sulu.io/template/template"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://schemas.sulu.io/template/template http://schemas.sulu.io/template/template-1.0.xsd">

    <!-- MUST match the filename (event.xml -> event) -->
    <key>event</key>

    <!-- templates/pages/event.html.twig -->
    <view>pages/event</view>

    <!-- 3.0 namespace - NOT the 2.x Sulu\Bundle\WebsiteBundle controller -->
    <controller>Sulu\Content\UserInterface\Controller\Website\ContentController::indexAction</controller>

    <!-- http cache lifetime in seconds -->
    <cacheLifetime>604800</cacheLifetime>

    <!-- name shown in the admin template dropdown, per locale -->
    <meta>
        <title lang="en">Event</title>
        <title lang="de">Veranstaltung</title>
    </meta>

    <properties>
        <property name="title" type="text_line" mandatory="true">
            <meta>
                <title lang="en">Title</title>
                <title lang="de">Titel</title>
            </meta>
            <params>
                <param name="headline" value="true"/>
            </params>
            <!-- title feeds the generated URL -->
            <tag name="sulu.rlp.part"/>
        </property>

        <!-- 3.0: type "route" (2.x used "resource_locator") -->
        <property name="url" type="route" mandatory="true">
            <meta>
                <title lang="en">Resourcelocator</title>
                <title lang="de">Adresse</title>
            </meta>
            <tag name="sulu.rlp"/>
        </property>

        <property name="article" type="text_editor">
            <meta>
                <title lang="en">Article</title>
                <title lang="de">Artikel</title>
            </meta>
        </property>
    </properties>
</template>
```

## Snippet template differences

- No `<view>`, `<controller>`, `<cacheLifetime>`, no `url` property.
- `title` is tagged `<tag name="sulu.node.name"/>` instead of `sulu.rlp.part`.

## Article template differences

- Same head as pages (`view`, `controller`, `cacheLifetime`).
- `url` property uses `type="page_tree_route"` (article URLs hang below a page),
  without the `sulu.rlp` tag.

## Common property types

Full list with parameters: [property types reference](https://docs.sulu.io/3.x/reference/property-types/index.html).
Always check availability in the concrete project (bundles can add types):

| Type | Purpose |
| --- | --- |
| `text_line`, `text_area`, `text_editor` | single line / plain multiline / CKEditor rich text |
| `route` | page URL (tag `sulu.rlp`) |
| `page_tree_route` | article URL below a page tree |
| `media_selection`, `single_media_selection` | media library references |
| `single_page_selection`, `page_selection` | internal links |
| `snippet_selection` | embed snippets |
| `smart_content` | auto-filled content lists (data providers) |
| `category_selection`, `tag_selection` | taxonomies |
| `select`, `single_select`, `checkbox`, `color`, `date`, `time`, `url`, `email`, `phone`, `number` | scalar inputs |
| `block` | repeatable, typed content sections (below) |

## Blocks

Blocks use a dedicated `<block>` element (not `<property type="block">`), with the
default type as attribute - this is the syntax the skeleton's `default.xml` uses:

```xml
<block name="blocks" default-type="text" minOccurs="0">
    <meta>
        <title lang="en">Content</title>
    </meta>
    <types>
        <type name="text">
            <meta>
                <title lang="en">Text</title>
            </meta>
            <properties>
                <property name="text" type="text_editor">
                    <meta>
                        <title lang="en">Text</title>
                    </meta>
                </property>
            </properties>
        </type>
        <type name="image">
            <meta>
                <title lang="en">Image</title>
            </meta>
            <properties>
                <property name="image" type="single_media_selection">
                    <meta>
                        <title lang="en">Image</title>
                    </meta>
                    <params>
                        <param name="types" value="image"/>
                    </params>
                </property>
            </properties>
        </type>
    </types>
</block>
```

Twig side: iterate `content.blocks`, dispatch on `block.type`, typically by including
one partial per block type.

## Registration

- Dropping the XML into `config/templates/pages/` is enough; no explicit
  registration needed in the skeleton setup.
- Webspace (`config/webspaces/*.xml`) controls defaults and exclusions:

```xml
<default-templates>
    <default-template type="page">default</default-template>
    <default-template type="home">homepage</default-template>
</default-templates>

<excluded-templates>
    <excluded-template>other</excluded-template>
</excluded-templates>
```
