<?php

namespace AppBundle\Repository\ReferentOrganizationalChart;

use AppBundle\Entity\ReferentOrganizationalChart\AbstractOrganizationalChartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AbstractOrganizationalChartItemRepository extends ServiceEntityRepository
{
     public function __construct(RegistryInterface $registry)
     {
         parent::__construct($registry, AbstractOrganizationalChartItem::class);
     }

    /**
     * @return AbstractOrganizationalChartItem[]
     */
    public function findAllRootItems(): array
    {
        return $this->createQueryBuilder('abstract_organizational_chart_item')
            ->where('abstract_organizational_chart_item.parent IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }
}
