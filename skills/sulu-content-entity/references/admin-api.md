# Admin & API reference - content-rich entity (Sulu 3.0)

Derived from `ExampleTestBundle` (`packages/content/tests/Application/` in sulu/sulu).

## Configuration

`config/packages/sulu_admin.yaml` - resource routes, plus a **template type** registration that gives the entity its own template directory:

```yaml
sulu_admin:
    resources:
        events:
            routes:
                list: app.get_events
                detail: app.get_event
    templates:
        event:                       # = Event::TEMPLATE_TYPE
            default_type: default
            directories:
                app: '%kernel.project_dir%/config/templates/events'
```

Template XMLs in `config/templates/events/` follow the sulu-template skill (`default.xml` with title/url/properties; `<view>`/`<controller>` if the entity renders website pages).

## Admin class

Same skeleton as a plain entity (see sulu-custom-entity skill), with these deltas:

```php
public function __construct(
    private ViewBuilderFactoryInterface $viewBuilderFactory,
    private ContentViewBuilderFactoryInterface $contentViewBuilderFactory, // sulu_content.content_view_builder_factory
    private SecurityCheckerInterface $securityChecker,
    private LocalizationManagerInterface $localizationManager,
) {
}
```

- List and tab views are **localized**: paths like `/events/:locale` and `/events/:locale/:id`, with `->addLocales($locales)` and `->setDefaultLocale($locales[0])` (`$locales = $this->localizationManager->getLocales()`).
- Instead of building form views yourself, generate the content tabs:

```php
$viewBuilders = $this->contentViewBuilderFactory->createViews(
    Event::class,             // the ContentRichEntity class
    static::EDIT_TABS_VIEW,
    static::ADD_TABS_VIEW,
    static::SECURITY_CONTEXT
);
foreach ($viewBuilders as $viewBuilder) {
    $viewCollection->add($viewBuilder);
}
```

This derives the content tab (template dropdown + properties), SEO and excerpt tabs, and the publish/draft toolbar from the traits the dimension content implements.

- Security context includes the extra permission for publishing: `PermissionTypes::LIVE` alongside VIEW/ADD/EDIT/DELETE.

## Controller

Work through `ContentManagerInterface` (`sulu_content.content_manager`); dimension attributes come from the request (`locale`, `stage`). Core operations, following `ExampleTestBundle/Controller/ExampleController.php`:

```php
// read
$event = $this->eventRepository->findOneBy(['id' => $id], [/* select content for $dimensionAttributes */]);
$dimensionContent = $this->contentManager->resolve($event, $dimensionAttributes);

// create/update
$dimensionContent = $this->contentManager->persist($event, $data, $dimensionAttributes);
$this->entityManager->flush();

// publish (data flows draft -> live)
$this->contentManager->applyTransition($event, $dimensionAttributes, WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH);
$this->entityManager->flush();

// serialize for the admin
$normalizedContent = $this->contentManager->normalize($dimensionContent);
```

The `postTriggerAction` (POST on `/events/{id}?action=…`) dispatches the workflow operations the admin UI sends: `copy_locale` and `restore` via `$contentManager->copy(...)` between dimension attribute sets, `unpublish` and `remove_draft` via `applyTransition`. **For publish/unpublish/copy_locale/ restore/remove_draft, load the entity with ALL dimension contents**, not just the current dimension - copy the branching from the ExampleController verbatim.

`cgetAction` is a normal DoctrineListBuilder listing over the **main entity class**, plus `$listBuilder->setParameter('locale', $request->query->get('locale'))` and select fields for `locale`/`ghostLocale` - the list XML joins the dimension content for title/workflow columns (copy `ExampleTestBundle/Resources/config/lists/examples.xml` as starting point).

## Routes

As in the plain-entity skill, plus:

- a trigger route: `POST /admin/api/events/{id}` → `postTriggerAction`,
- optionally a versions route: `GET /admin/api/events/{id}/versions` → DoctrineListBuilder over an `events_versions` list,
- **every route** carries:

```yaml
    options:
        api_dimension_listener: true
```

which activates the listener that turns `locale`/`stage` query parameters into dimension attributes.
