# Twig reference for Sulu 3.0 templates

Verified against the [sulu/skeleton `3.0` branch](https://github.com/sulu/skeleton/tree/3.0).

## Variables available in a page/article template

| Variable | Content |
| --- | --- |
| `content.<property>` | resolved property values from the template XML |
| `extension.seo` | SEO tab data (title, description, …) |
| `extension.excerpt` | Excerpt & Taxonomies tab data |
| `localizations` | available localizations for the current document |
| `shadowBaseLocale` | shadow locale, if any |

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

## Navigation and paths — 3.0 names

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
