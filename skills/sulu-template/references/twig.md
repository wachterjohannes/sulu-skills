# Twig reference for Sulu 3.0 templates

Verified against the [sulu/skeleton `3.0` branch](https://github.com/sulu/skeleton/tree/3.0).
Full function/filter list: [Twig extensions reference](https://docs.sulu.io/3.x/reference/twig-extensions/index.html).

## Variables available in a page/article template

| Variable | Content |
| --- | --- |
| `content.<property>` | resolved property values from the template XML |
| `view.<property>` | render metadata per property (e.g. selected ids, params) |
| `extension.seo` | SEO tab data (title, description, …) |
| `extension.excerpt` | Excerpt & Taxonomies tab data |
| `localizations` | available localizations for the current document |
| `shadowBaseLocale` | shadow locale, if any |

## How XML properties map to Twig variables

Every `<property name="x">` in the template XML appears in Twig as `content.x` -
the names must match exactly, which is why the XML and the Twig template have to
stay in sync: renaming a property in one file silently yields `null` in the other.

The value is not the raw stored data. Before rendering, the content system runs
each property through the resolver for its XML `type` (tagged
`sulu_content.property_resolver`), which turns stored references into usable
values - a `media_selection` stores ids but arrives in Twig as media objects, a
`single_page_selection` as the resolved page, `text_line` simply as its string.
That is also why the property **type** matters beyond admin UI: it decides what
`content.x` contains. The resolver additionally fills `view.x` with render
metadata (ids, params) for the same property name.

## SEO head block

```twig
{% block meta %}
    {{ include('@SuluWebsite/Extension/seo.html.twig', {
        seo: extension.seo|default([]),
        content: content|default([]),
        localizations: localizations|default([]),
        shadowBaseLocale: shadowBaseLocale|default(),
    }) }}
{% endblock %}
```

## Navigation and paths - 3.0 names

Several functions were renamed from 2.x; the page-tree navigation functions now carry
a `sulu_page_` prefix:

```twig
{# root path of the current portal #}
<a href="{{ sulu_content_root_path() }}">Homepage</a>

{# navigation context "main", depth 1; renamed from 2.x sulu_navigation_root_tree #}
{% for item in sulu_page_navigation_root_tree('main', 1, {
    title: 'title',
    url: 'url',
}) %}
    <a href="{{ sulu_content_path(item.url) }}" title="{{ item.title }}">{{ item.title }}</a>
{% endfor %}
```

`sulu_content_path(url)` resolves a stored route to an absolute path for the current
portal/localization.

## Search endpoint

```twig
<form action="{{ path('sulu_search.website_search') }}" method="GET">
    <input name="q" type="text"/>
</form>
```

## Minimal page template

```twig
{% extends 'base.html.twig' %}

{% block content %}
    <h1>{{ content.title }}</h1>

    {{ content.article|raw }}
{% endblock %}
```

## Block rendering pattern

```twig
{% for block in content.blocks %}
    {{ include('includes/blocks/' ~ block.type ~ '.html.twig', { block: block }) }}
{% endfor %}
```
