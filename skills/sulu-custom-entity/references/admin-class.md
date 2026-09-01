# Admin class reference (Sulu 3.0)

Pattern verified against `TagAdmin` in sulu/sulu 3.0. App-level Admin classes in `src/Admin/` are picked up automatically: the AdminBundle autoconfigures every `Admin` subclass with the `sulu.admin` tag, and the skeleton autowires constructors.

```php
<?php

namespace App\Admin;

use App\Entity\Event;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class EventAdmin extends Admin
{
    public const SECURITY_CONTEXT = 'app.events';

    public const LIST_VIEW = 'app.events.list';
    public const ADD_FORM_VIEW = 'app.events.add_form';
    public const EDIT_FORM_VIEW = 'app.events.edit_form';

    public function __construct(
        private ViewBuilderFactoryInterface $viewBuilderFactory,
        private SecurityCheckerInterface $securityChecker,
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $item = new NavigationItem('app.events');
            $item->setPosition(30);
            $item->setIcon('su-calendar');
            $item->setView(static::LIST_VIEW);

            // top-level; alternatively nest under an existing item:
            // $navigationItemCollection->get(Admin::SETTINGS_NAVIGATION_ITEM)->addChild($item);
            $navigationItemCollection->add($item);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $formToolbarActions = [];
        $listToolbarActions = [];

        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }
        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
        }
        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }
        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }

        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/events')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setListKey('events')          // config/lists/events.xml
                    ->setTitle('app.events')
                    ->addListAdapters(['table'])
                    ->setAddView(static::ADD_FORM_VIEW)
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '/events/add')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setBackView(static::LIST_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder('app.events.add_form.details', '/details')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('event_details')   // config/forms/event_details.xml
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::ADD_FORM_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/events/:id')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setBackView(static::LIST_VIEW)
                    ->setTitleProperty('name')
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder('app.events.edit_form.details', '/details')
                    ->setResourceKey(Event::RESOURCE_KEY)
                    ->setFormKey('event_details')
                    ->setTabTitle('sulu_admin.details')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );
        }
    }

    public function getSecurityContexts()
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'App' => [
                    self::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }
}
```

Notes:

- For a **localized** entity, add `->addLocales($locales)` / `->setDefaultLocale($locales[0])` to the list and tab view builders and use `/events/:locale` style paths (get locales from `Sulu\Component\Localization\Manager\LocalizationManagerInterface`).
- Translation keys (`app.events`) go into `translations/admin.en.json`.
- The security context group label (`'App'` above) is the section heading shown in the permission matrix of user roles.
