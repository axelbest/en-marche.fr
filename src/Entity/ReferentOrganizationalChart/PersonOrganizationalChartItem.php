<?php

namespace AppBundle\Entity\ReferentOrganizationalChart;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 */
class PersonOrganizationalChartItem extends AbstractOrganizationalChartItem
{
    /**
     * @var Collection|ReferentPersonLink[]
     *
     * @ORM\OneToMany(targetEntity="ReferentPersonLink", mappedBy="personOrganizationalChartItem", cascade={"persist"})
     */
    private $referentPersonLinks;

    public function __construct(string $label, AbstractOrganizationalChartItem $parent = null)
    {
        parent::__construct($label, $parent);

        $this->referentPersonLinks = new ArrayCollection();
    }

    /**
     * @return Collection|ReferentPersonLink[]
     */
    public function getReferentPersonLinks(): Collection
    {
        return $this->referentPersonLinks;
    }

    public function setReferentPersonLinks(Collection $referentPersonLinks): void
    {
        $this->referentPersonLinks = $referentPersonLinks;
    }

    public function addReferentPersonLink(ReferentPersonLink $referentPersonLink): void
    {
        $referentPersonLink->setPersonItem($this);
        $this->referentPersonLinks->add($referentPersonLink);
    }

    public function removeReferentPersonLink(ReferentPersonLink $referentPersonLink): void
    {
        $referentPersonLink->setPersonItem(null);
        $this->referentPersonLinks->remove($referentPersonLink);
    }
}
