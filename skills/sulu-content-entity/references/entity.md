# Entity reference — content-rich entity (Sulu 3.0)

Derived from `ExampleTestBundle` in `sulu/sulu` `packages/content/tests/Application/`
and the article package (`packages/article/src/Domain/Model/`).

## Main entity

```php
<?php

namespace App\Entity;

use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * @implements ContentRichEntityInterface<EventDimensionContent>
 */
class Event implements ContentRichEntityInterface
{
    /** @phpstan-use ContentRichEntityTrait<EventDimensionContent> */
    use ContentRichEntityTrait;

    public const RESOURCE_KEY = 'events';
    public const TEMPLATE_TYPE = 'event';

    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function createDimensionContent(): DimensionContentInterface
    {
        return new EventDimensionContent($this);
    }
}
```

(Articles use a UUID instead of an auto-increment id — `Uuid::v7()->toRfc4122()` in
the constructor; either works.)

## Dimension content entity

Compose from the trait/interface pairs the entity actually needs. Full stack (what
articles use):

```php
<?php

namespace App\Entity;

use Sulu\Content\Domain\Model\AuditableInterface;
use Sulu\Content\Domain\Model\AuditableTrait;
use Sulu\Content\Domain\Model\AuthorInterface;
use Sulu\Content\Domain\Model\AuthorTrait;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\DimensionContentTrait;
use Sulu\Content\Domain\Model\ExcerptInterface;
use Sulu\Content\Domain\Model\ExcerptTrait;
use Sulu\Content\Domain\Model\RoutableInterface;
use Sulu\Content\Domain\Model\RoutableTrait;
use Sulu\Content\Domain\Model\SeoInterface;
use Sulu\Content\Domain\Model\SeoTrait;
use Sulu\Content\Domain\Model\ShadowInterface;
use Sulu\Content\Domain\Model\ShadowTrait;
use Sulu\Content\Domain\Model\TaxonomyInterface;
use Sulu\Content\Domain\Model\TaxonomyTrait;
use Sulu\Content\Domain\Model\TemplateInterface;
use Sulu\Content\Domain\Model\TemplateTrait;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowTrait;

/**
 * @implements DimensionContentInterface<Event>
 */
class EventDimensionContent implements
    DimensionContentInterface,
    TemplateInterface,
    SeoInterface,
    ExcerptInterface,
    TaxonomyInterface,
    RoutableInterface,
    WorkflowInterface,
    AuthorInterface,
    ShadowInterface,
    AuditableInterface
{
    use DimensionContentTrait;
    use TemplateTrait {
        setTemplateData as parentSetTemplateData;
    }
    use SeoTrait;
    use ExcerptTrait;
    use TaxonomyTrait;
    use RoutableTrait;
    use WorkflowTrait;
    use AuthorTrait;
    use ShadowTrait;
    use AuditableTrait;

    protected ?int $id = null;
    protected Event $event;
    protected ?string $title = null;   // denormalized for lists/queries

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->created = new \DateTimeImmutable();
        $this->changed = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResource(): ContentRichEntityInterface
    {
        return $this->event;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /** @param array<string, mixed> $templateData */
    public function setTemplateData(array $templateData): void
    {
        if (\array_key_exists('title', $templateData)) {
            $this->title = \is_string($templateData['title']) ? $templateData['title'] : null;
        }

        $this->parentSetTemplateData($templateData);
    }

    public static function getTemplateType(): string
    {
        return Event::TEMPLATE_TYPE;
    }

    public static function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }
}
```

Trim the stack for simpler entities: minimum is `DimensionContentTrait` +
`TemplateTrait`; drop `RoutableTrait` if the entity has no own URLs, `ShadowTrait`
if no shadow locales, etc. Interfaces and traits come in pairs — implement the
interface for every trait used (the content system feature-detects via
`instanceof`).

## Doctrine mapping

**Map only your own fields** — id, the relation between the two entities, and
denormalized fields like `title`. Everything the traits add (locale, stage,
version, templateKey, templateData, seo, excerpt, workflowPlace, …) is mapped
automatically by `Sulu\Content\Infrastructure\Doctrine\MetadataLoader` via the
`loadClassMetadata` event.

With attribute mapping in an app (the core packages map via XML, which doesn't
have this constraint):

```php
// Event
#[ORM\Entity]
#[ORM\Table(name: 'events')]
// ...
// Redeclared from ContentRichEntityTrait to attach the attribute. MUST stay
// untyped — the trait declares it untyped, and PHP fatals on a typed redeclare.
#[ORM\OneToMany(mappedBy: 'event', targetEntity: EventDimensionContent::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
protected $dimensionContents;

// EventDimensionContent
#[ORM\Entity]
#[ORM\Table(name: 'event_dimension_contents')]
// ...
#[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'dimensionContents')]
#[ORM\JoinColumn(name: 'eventId', nullable: false, onDelete: 'CASCADE')]
protected Event $event;

#[ORM\Column(type: 'string', length: 191, nullable: true)]
protected ?string $title = null;
```

The `dimensionContents` ↔ entity relation must cascade persist and delete via the
database (`onDelete: CASCADE`), matching the ExampleTestBundle's XML mapping.

## Repository

Content-aware queries go through
`sulu_content.dimension_content_query_enhancer` — build a repository like
`ExampleTestBundle/Repository/ExampleRepository.php` (selector constants decide
whether/which dimension contents get joined) or start from the article package's
`ArticleRepository` for the full-featured version.
