<?php

namespace AppBundle\Entity\ReferentOrganizationalChart;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *    "person_orga_item" = "AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem",
 *    "group_orga_item" = "AppBundle\Entity\ReferentOrganizationalChart\GroupOrganizationalChartItem"
 * })
 */
abstract class AbstractOrganizationalChartItem
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $label;

    /**
     * @var Collection|AbstractOrganizationalChartItem[]
     *
     * @ORM\OneToMany(targetEntity="AbstractOrganizationalChartItem", mappedBy="parent", cascade={"persist"})
     */
    private $children;

    /**
     * @var AbstractOrganizationalChartItem
     *
     * @ORM\ManyToOne(targetEntity="AbstractOrganizationalChartItem", inversedBy="children", cascade={"persist"})
     */
    private $parent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return Collection|AbstractOrganizationalChartItem[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function addChild(AbstractOrganizationalChartItem $child): void
    {
        $child->setParent($this);
        $this->children[] = $child;
    }

    public function removeChild(AbstractOrganizationalChartItem $child): void
    {
        $child->setParent(null);
        $this->children[] = $child;
    }

    public function getParent(): AbstractOrganizationalChartItem
    {
        return $this->parent;
    }

    public function setParent(?AbstractOrganizationalChartItem $parent): void
    {
        $this->parent = $parent;
    }

    public function getLevel(): int
    {
        if ($this->getParent()) {
            return $this->getParent()->getLevel() + 1;
        }

        return 1;
    }
}
