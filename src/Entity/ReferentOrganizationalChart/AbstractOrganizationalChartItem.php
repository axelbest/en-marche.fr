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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection|AbstractOrganizationalChartItem[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getParent(): AbstractOrganizationalChartItem
    {
        return $this->parent;
    }

    public function setParent(AbstractOrganizationalChartItem $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
