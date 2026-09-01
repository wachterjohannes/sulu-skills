<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * @implements ContentRichEntityInterface<EventDimensionContent>
 */
#[ORM\Entity]
#[ORM\Table(name: 'events')]
class Event implements ContentRichEntityInterface
{
    /** @phpstan-use ContentRichEntityTrait<EventDimensionContent> */
    use ContentRichEntityTrait;

    public const RESOURCE_KEY = 'events';
    public const TEMPLATE_TYPE = 'event';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    /**
     * Redeclared from ContentRichEntityTrait to attach the mapping
     * attribute - MUST stay untyped, a typed redeclaration conflicts with the trait.
     *
     * @var Collection<int, EventDimensionContent>&iterable<int, EventDimensionContent>
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventDimensionContent::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $dimensionContents;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function createDimensionContent(): DimensionContentInterface
    {
        return new EventDimensionContent($this);
    }
}
