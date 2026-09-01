# Extending core entities: classes, objects config, user events (Sulu 3.0)

Verified against sulu/sulu 3.0 and the
[extend-entities cookbook](https://docs.sulu.io/3.x/cookbook/extend-entities.html).

## How it works

Core entities (Contact, User, Category, ...) are mapped as Doctrine
**mapped-superclasses**. Sulu's persistence `MetadataSubscriber` flips the configured
`model` class to a concrete entity at runtime and attaches the configured repository.
Swapping the model therefore requires no change to core mappings.

## Extending class

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sulu\Bundle\ContactBundle\Entity\Contact as SuluContact;

#[ORM\Entity]
#[ORM\Table(name: 'co_contacts')]   // MUST be the table of the extended entity
class Contact extends SuluContact
{
    #[ORM\Column(type: 'string', length: 63, nullable: true)]
    private ?string $externalCrmId = null;   // nullable or defaulted!

    public function getExternalCrmId(): ?string
    {
        return $this->externalCrmId;
    }

    public function setExternalCrmId(?string $externalCrmId): void
    {
        $this->externalCrmId = $externalCrmId;
    }
}
```

## Objects config keys

Each bundle exposes `objects.<name>.model` (and `repository`); the repository service
`sulu.repository.<name>` and the `%sulu.model.<name>.class%` parameter follow the
override.

| Config root | Objects |
| --- | --- |
| `sulu_contact` | `contact`, `account` |
| `sulu_security` | `user`, `role`, `role_setting`, `access_control` |
| `sulu_category` | `category`, `category_translation`, `keyword` |
| `sulu_media` | see bundle Configuration (collection, media types) |
| `sulu_tag` | `tag` |

```yaml
# config/packages/sulu_contact.yaml
sulu_contact:
    objects:
        contact:
            model: App\Entity\Contact
            # repository: App\Repository\ContactRepository   # optional
```

After the config change: `doctrine:migrations:diff`, `migrate`, clear both caches.
Type-hints against the Sulu class keep working (subclass); code that needs the new
getters casts or type-hints the app class.

## User lifecycle events

`UserManager` collects domain events which the ActivityBundle dispatches through the
standard `event_dispatcher`: `UserCreatedEvent`, `UserModifiedEvent`,
`UserRemovedEvent`, `UserEnabledEvent`, `UserLockedEvent`, `UserPasswordResettedEvent`
(plus Role events), all in `Sulu\Bundle\SecurityBundle\Domain\Event`.

```php
<?php

namespace App\EventListener;

use Sulu\Bundle\SecurityBundle\Domain\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class UserCreatedListener
{
    public function __invoke(UserCreatedEvent $event): void
    {
        $user = $event->getResourceUser();
        // provision defaults, notify, sync to CRM, ...
    }
}
```

These events fire on actions going through Sulu's managers/controllers. For guarantees
on every persistence path (fixtures, custom commands) use a plain Doctrine entity
listener on the extended entity instead.
