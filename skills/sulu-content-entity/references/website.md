# Website, preview & optional integrations (Sulu 3.0)

Derived from `ExampleTestBundle/Resources/config/services.yaml` in sulu/sulu
(`packages/content/tests/Application/`). Each integration is one service with one
tag; the skeleton autowires/autoconfigures `App\` classes, but the tags with their
alias/key attributes must be declared explicitly in `config/services.yaml`.

## Website rendering (entity has own URLs)

Requires `RoutableTrait` on the dimension content and a `route`-type `url` property
in the entity's templates. A **RouteDefaultsProvider** tells the router how to
render a matched route - use the generic content one as pattern
(`ExampleTestBundle/Route/ExampleRouteDefaultsProvider.php`) and tag it:

```yaml
App\Sulu\EventRouteDefaultsProvider:
    tags:
        - { name: sulu_route.route_defaults_provider, resource_key: events }
```

It resolves the entity via repository + `sulu_content.content_aggregator` (stage
`live`) and returns the template's `<view>`/`<controller>` as route defaults - the
same `ContentController` flow pages use.

## Admin preview

No own class needed - register the generic provider with the entity class:

```yaml
app.event_preview_object_provider:
    class: Sulu\Content\Infrastructure\Sulu\Preview\ContentObjectProvider
    arguments:
        - '@sulu_admin.metadata_provider_registry'
        - '@doctrine.orm.entity_manager'
        - '@sulu_content.content_aggregator'
        - '@sulu_content.content_data_mapper'
        - App\Entity\Event
    tags:
        - { name: sulu.context, context: admin }
        - { name: sulu_preview.object_provider, provider-key: events }
```

## Optional integrations (one tagged service each)

| Integration | Tag | Reference implementation |
| --- | --- | --- |
| Teaser selection in pages | `sulu.teaser.provider, alias: events` | `ExampleTestBundle/Teaser/ExampleTeaserProvider.php` |
| Link plugin in text editor | `sulu.link.provider, alias: events` | `ExampleTestBundle/Link/ExampleLinkProvider.php` |
| Smart content data provider | `sulu_content.smart_content_provider, type: events` | `ExampleTestBundle/SmartContent/ExampleSmartContentProvider.php` |
| `event_selection` content type | `sulu_content.property_resolver, alias: event_selection` | `ExampleTestBundle/PropertyResolver/ExampleSelectionPropertyResolver.php` |
| Resource loading for resolvers | `sulu_content.resource_loader, alias: event` | `ExampleTestBundle/ResourceLoader/ExampleResourceLoader.php` |
| Reference tracking (media usage etc.) | `sulu_reference.refresher` | `ExampleTestBundle/Reference/ExampleReferenceRefresher.php` |

Add them only when asked for - none are required for the admin/CRUD/publish flow.

## Twig side

Website templates for the entity live wherever the template XML's `<view>` points
(e.g. `templates/events/default.html.twig`) and work exactly like page templates:
`content.*`, `extension.seo`, `extension.excerpt` (see the sulu-template skill).
