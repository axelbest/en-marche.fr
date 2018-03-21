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
     * @ORM\OneToMany(targetEntity="ReferentPersonLink", mappedBy="personItem", cascade={"persist"})
     */
    private $referentPersonLinks;

    public function __construct()
    {
        $this->referentPersonLinks = new ArrayCollection();
    }

    /**
     * @return Collection|ReferentPersonLink[]
     */
    public function getReferentPersonLinks(): Collection
    {
        return $this->referentPersonLinks;
    }

    public function setReferentPersonLinks(Collection $referentPersonLinks): self
    {
        $this->referentPersonLinks = $referentPersonLinks;

        return $this;
    }
}
