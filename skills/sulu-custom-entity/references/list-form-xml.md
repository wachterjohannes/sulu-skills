# List & form XML reference (Sulu 3.0)

Verified against TagBundle in sulu/sulu 3.0. Both directories are pre-registered in
the skeleton (`config/packages/sulu_admin.yaml`).

## List XML - `config/lists/events.xml`

```xml
<?xml version="1.0" ?>
<list xmlns="http://schemas.sulu.io/list-builder/list">
    <key>events</key>

    <properties>
        <property name="id" visibility="no" translation="sulu_admin.id">
            <field-name>id</field-name>
            <entity-name>App\Entity\Event</entity-name>
        </property>

        <property name="name" visibility="always" searchability="yes" translation="app.name">
            <field-name>name</field-name>
            <entity-name>App\Entity\Event</entity-name>
        </property>

        <property name="startDate" visibility="yes" translation="app.start_date">
            <field-name>startDate</field-name>
            <entity-name>App\Entity\Event</entity-name>
            <transformer type="datetime"/>
            <filter type="datetime"/>
        </property>
    </properties>
</list>
```

- `<key>` = resource key = what `setListKey()` and
  `FieldDescriptorFactory::getFieldDescriptors()` reference.
- `visibility`: `always` (not hideable), `yes` (default on), `no` (default off).
- `searchability="yes"` feeds the list search box.
- `<transformer>` formats values client-side (`datetime`, `date`, `number`,
  `bytes`, `thumbnails`, …); `<filter>` adds the column filter UI.
- Joins to related entities use named `<joins>` blocks referenced from fields
  (see TagBundle's `tags.xml` creator/changer joins for the pattern).

### Filter types

Simple filters take no params: `text`, `number`, `date`, `datetime`, `boolean`.
Two take configuration (both verified against MediaBundle's `media.xml`):

`select` - fixed option list; param name = stored value, param value =
translation key:

```xml
<property name="type" visibility="yes" translation="app.type">
    <field-name>type</field-name>
    <entity-name>App\Entity\Event</entity-name>

    <filter type="select">
        <params>
            <param name="options" type="collection">
                <param name="concert" value="app.type_concert"/>
                <param name="workshop" value="app.type_workshop"/>
            </param>
        </params>
    </filter>
</property>
```

`selection` - filter by related Sulu resources via a selection overlay; needs a
join to the related entity:

```xml
<property name="tagId" visibility="never" translation="sulu_tag.tags">
    <field-name>id</field-name>
    <entity-name>Sulu\Bundle\TagBundle\Entity\Tag</entity-name>

    <joins>
        <join>
            <entity-name>Sulu\Bundle\TagBundle\Entity\Tag</entity-name>
            <field-name>App\Entity\Event.tags</field-name>
        </join>
    </joins>

    <filter type="selection">
        <params>
            <param name="displayProperty" value="name"/>
            <param name="resourceKey" value="tags"/>
        </params>
    </filter>
</property>
```

Filter-only relation properties get `visibility="never"` - as a column they would
duplicate rows for entities with multiple relations (see the comment in core's
`media.xml`).

## Form XML - `config/forms/event_details.xml`

Same schema and property types as page templates (`form-1.0.xsd` instead of
`template-1.0.xsd`, no view/controller/cacheLifetime):

```xml
<?xml version="1.0" ?>
<form xmlns="http://schemas.sulu.io/template/template"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://schemas.sulu.io/template/template http://schemas.sulu.io/template/form-1.0.xsd">

    <key>event_details</key>

    <properties>
        <property name="name" type="text_line" mandatory="true">
            <meta>
                <title>sulu_admin.name</title>
            </meta>
            <params>
                <param name="headline" value="true"/>
            </params>
        </property>

        <property name="startDate" type="datetime">
            <meta>
                <title>app.start_date</title>
            </meta>
        </property>
    </properties>
</form>
```

- `<title>` here takes a translation key directly (no `lang` attribute
  needed - admin translations resolve it).
- Property names must match what the REST API serializes/accepts.
- All template property types work (`media_selection`, `single_select`, blocks,
  …) - see the sulu-template skill's reference for the type list.
