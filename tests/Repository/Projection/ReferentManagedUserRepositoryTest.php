<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\DataFixtures\ORM\LoadReferentManagedUserData;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Entity\ReferentTag;
use AppBundle\Referent\ManagedUsersFilter;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Tests\AppBundle\MysqlWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 */
class ReferentManagedUserRepositoryTest extends MysqlWebTestCase
{
    use TestHelperTrait;

    /**
     * @var ReferentManagedUserRepository
     */
    private $referentManagedUserRepository;

    /**
     * @var ObjectRepository
     */
    private $referentTagRepository;

    public function testSearch()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(
            [
                $this->referentTagRepository->findOneBy(['name' => 'CH']),
                $this->referentTagRepository->findOneBy(['name' => '77']),
            ],
            '1.123456',
            '2.34567'
        );

        $results = $this->referentManagedUserRepository->search($referent)->getQuery()->getResult();

        $this->assertCount(3, $results);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSearchWithInvalidReferent()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent([], '1.123456', '2.34567');

        $this->referentManagedUserRepository->search($referent);
    }

    public function testCreateDispatcherIterator()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(
            [
                $this->referentTagRepository->findOneBy(['name' => '92']),
                $this->referentTagRepository->findOneBy(['name' => '77']),
            ],
            '1.123456',
            '2.34567'
        );

        $results = $this->referentManagedUserRepository->createDispatcherIterator($referent);

        $expectedEmails = ['francis.brioul@yahoo.com', 'gisele-berthoux@caramail.com'];

        $count = 0;
        foreach ($results as $key => $result) {
            $this->assertSame($expectedEmails[$key], $result[0]->getEmail());
            ++$count;
        }

        $this->assertSame(2, $count);
    }

    public function testCreateDispatcherIteratorWithOffset()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(
            [
                $this->referentTagRepository->findOneBy(['name' => '92']),
                $this->referentTagRepository->findOneBy(['name' => '77']),
            ],
            '1.123456',
            '2.34567'
        );

        $filter = $this->createMock(ManagedUsersFilter::class);
        $filter->expects($this->once())->method('getOffset')->willReturn(1);

        $results = $this->referentManagedUserRepository->createDispatcherIterator($referent, $filter);

        $expectedEmails = ['gisele-berthoux@caramail.com'];

        $count = 0;
        foreach ($results as $key => $result) {
            $this->assertSame($expectedEmails[$key], $result[0]->getEmail());
            ++$count;
        }

        $this->assertSame(1, $count);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadNewsletterSubscriptionData::class,
            LoadReferentManagedUserData::class,
        ]);

        $this->container = $this->getContainer();
        $this->referentManagedUserRepository = $this->getRepository(ReferentManagedUser::class);
        $this->referentTagRepository = $this->getRepository(ReferentTag::class);
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->referentManagedUserRepository = null;
        $this->referentTagRepository = null;
        $this->container = null;

        parent::tearDown();
    }
}
