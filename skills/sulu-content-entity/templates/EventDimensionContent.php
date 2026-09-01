<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
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
 * Full trait stack (what articles use) — trim what the entity doesn't need:
 * minimum is DimensionContentTrait + TemplateTrait; drop RoutableTrait without
 * own URLs, ShadowTrait without shadow locales, etc. Keep interface and trait
 * in pairs — the content system feature-detects via instanceof.
 *
 * @implements DimensionContentInterface<Event>
 */
#[ORM\Entity]
#[ORM\Table(name: 'event_dimension_contents')]
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

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'dimensionContents')]
    #[ORM\JoinColumn(name: 'eventId', nullable: false, onDelete: 'CASCADE')]
    protected Event $event;

    /**
     * Denormalized from templateData for lists/queries.
     */
    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    protected ?string $title = null;

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
