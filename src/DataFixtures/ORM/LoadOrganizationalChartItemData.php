<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadOrganizationalChartItemData extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $referent = new PersonOrganizationalChartItem();
    }
}
