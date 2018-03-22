<?php

namespace AppBundle\Repository\ReferentOrganizationalChart;

use AppBundle\Entity\Referent;
use AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ReferentPersonLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferentPersonLink::class);
    }

    public function findOrCreateByOrgaItemAndReferent(PersonOrganizationalChartItem $organizationalChartItem, Referent $referent): ?ReferentPersonLink
    {
        return $this->createQueryBuilder('referent_person_link')
            ->where('referent_person_link.referent = :referent')
            ->andWhere('referent_person_link.personOrganizationalChartItem = :personOrganizationalChartItem')
            ->setParameters([
                'referent' => $referent,
                'personOrganizationalChartItem' => $organizationalChartItem,
            ])
            ->getQuery()
            ->getOneOrNullResult() ?? new ReferentPersonLink($organizationalChartItem, $referent)
        ;
    }
}
