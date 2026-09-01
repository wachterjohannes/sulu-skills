# REST API reference (Sulu 3.0)

Pattern verified against `TagController` in sulu/sulu 3.0. The admin's list view talks a specific protocol: `GET …?flat=true` with pagination/sorting/filter query parameters, answered by a `PaginatedRepresentation` built from the list metadata.

## Controller

```php
<?php

namespace App\Controller\Admin;

use App\Admin\EventAdmin;
use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventController extends AbstractRestController implements SecuredControllerInterface
{
    public function __construct(
        ViewHandlerInterface $viewHandler,
        private FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private RestHelperInterface $restHelper,
        private EventRepository $eventRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct($viewHandler);
    }

    public function cgetAction(Request $request): Response
    {
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Event::RESOURCE_KEY);
        $listBuilder = $this->listBuilderFactory->create(Event::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $list = new PaginatedRepresentation(
            $listBuilder->execute(),
            Event::RESOURCE_KEY,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );

        return $this->handleView($this->view($list, 200));
    }

    public function getAction(int $id): Response
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            throw new NotFoundHttpException();
        }

        return $this->handleView($this->view($event));
    }

    public function postAction(Request $request): Response
    {
        $event = new Event();
        $this->mapDataToEntity($request->toArray(), $event);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $this->handleView($this->view($event, 201));
    }

    public function putAction(Request $request, int $id): Response
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            throw new NotFoundHttpException();
        }
        $this->mapDataToEntity($request->toArray(), $event);
        $this->entityManager->flush();

        return $this->handleView($this->view($event));
    }

    public function deleteAction(int $id): Response
    {
        $event = $this->eventRepository->find($id);
        if ($event) {
            $this->entityManager->remove($event);
            $this->entityManager->flush();
        }

        return $this->handleView($this->view(null, 204));
    }

    public function getSecurityContext(): string
    {
        return EventAdmin::SECURITY_CONTEXT;
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->get('locale');
    }

    /** @param array<string, mixed> $data */
    private function mapDataToEntity(array $data, Event $event): void
    {
        // map the form XML property names to entity setters
        $event->setName($data['name'] ?? '');
    }
}
```

The response of `getAction` must serialize to the property names the form XML uses (JMS serializer is installed; plain getters matching the form property names work).

Wiring: the skeleton autowires and autoconfigures `App\` services. The needed autowiring aliases exist in Sulu core (`FieldDescriptorFactoryInterface`, `DoctrineListBuilderFactoryInterface`, `RestHelperInterface`); FOSRestBundle provides `ViewHandlerInterface`. If autowiring fails for one of them, wire the service ids explicitly: `sulu_core.list_builder.field_descriptor_factory`, `sulu_core.doctrine_list_builder_factory`, `sulu_core.doctrine_rest_helper`, `fos_rest.view_handler`.

## Routes

`config/routes/app_admin_api.yaml` (new file, picked up automatically):

```yaml
app.get_events:
    path: /admin/api/events.{_format}
    controller: App\Controller\Admin\EventController::cgetAction
    methods: GET
    format: json
    requirements: { _format: json|csv }

app.get_event:
    path: /admin/api/events/{id}.{_format}
    controller: App\Controller\Admin\EventController::getAction
    methods: GET
    format: json
    requirements: { _format: json }

app.post_event:
    path: /admin/api/events.{_format}
    controller: App\Controller\Admin\EventController::postAction
    methods: POST
    format: json
    requirements: { _format: json }

app.put_event:
    path: /admin/api/events/{id}.{_format}
    controller: App\Controller\Admin\EventController::putAction
    methods: PUT
    format: json
    requirements: { _format: json }

app.delete_event:
    path: /admin/api/events/{id}.{_format}
    controller: App\Controller\Admin\EventController::deleteAction
    methods: DELETE
    format: json
    requirements: { _format: json }
```

## Resource registration

`config/packages/sulu_admin.yaml`:

```yaml
sulu_admin:
    # ...existing config...
    resources:
        events:
            routes:
                list: app.get_events
                detail: app.get_event
```

The admin frontend derives every API call for the resource key from these two route names - `detail` doubles for POST/PUT/DELETE.
