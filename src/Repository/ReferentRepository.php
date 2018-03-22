<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Referent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ReferentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referent::class);
    }

    /**
     * @return Referent[]
     */
    public function findByStatus(string $status = Referent::ENABLED): array
    {
        $qb = $this->createQueryBuilder('lc');

        $qb
            ->where('lc.status = :status')
            ->setParameter('status', $status)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findOneByEmailAndSelectPersonOrgaChart(string $email): Referent
    {
        return $this->createQueryBuilder('referent')
            ->leftJoin('referent.referentPersonLinks', 'referent_person_links')
            ->addSelect('referent_person_links')
            ->where('referent.emailAddress = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
