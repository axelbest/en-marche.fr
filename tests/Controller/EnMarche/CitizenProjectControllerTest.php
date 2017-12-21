<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadCitizenProjectCommentData;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\Message\CitizenProjectNewFollowerMessage;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 * @group citizenProject
 */
class CitizenProjectControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testAnonymousUserCanSeeAnApprovedCitizenProject(): void
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertFalse($this->seeCommentSection());
        $this->assertFalse($this->seeReportLink());
    }

    public function testAnonymousUserCannotSeeAPendingCitizenProject(): void
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-marseille');
        $this->assertClientIsRedirectedTo('http://'.$this->hosts['app'].'/espace-adherent/connexion', $this->client);
    }

    public function testAdherentCannotSeeUnapprovedCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-marseille');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testAdherentCanSeeCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->isSuccessful($this->client->getResponse());
        $this->assertTrue($this->seeReportLink());
        $this->assertFalse($this->seeCommentSection());
    }

    public function testAdministratorCanSeeUnapprovedCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-marseille');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeCommentSection());
        $this->assertFalse($this->seeReportLink());
    }

    public function testAdministratorCanSeeACitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeReportLink());

        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8/discussions');
        $this->assertTrue($this->seeCommentSection());
        $this->assertSeeComments([
            ['Carl Mirabeau', 'Jean-Paul à Maurice : tout va bien ! Je répète ! Tout va bien !'],
            ['Lucie Olivera', 'Maurice à Jean-Paul : tout va bien aussi !'],
        ]);
    }

    public function testFollowerCanSeeACitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeReportLink());

        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8/discussions');
        $this->assertTrue($this->seeCommentSection());
        $this->assertSeeComments([
            ['Carl Mirabeau', 'Jean-Paul à Maurice : tout va bien ! Je répète ! Tout va bien !'],
            ['Lucie Olivera', 'Maurice à Jean-Paul : tout va bien aussi !'],
        ]);
    }

    /**
     * @depends testAdministratorCanSeeACitizenProject
     * @depends testFollowerCanSeeACitizenProject
     */
    public function testFollowerCanAddCommentToCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8/discussions');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->submit(
            $this->client->getCrawler()->selectButton('Publier')->form([
                'citizen_project_comment_command[content]' => 'Commentaire Test',
            ])
        );

        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeCommentSection());
        $this->assertSeeComments([
            ['Mirabeau', 'Commentaire Test'],
            ['Carl Mirabeau', 'Jean-Paul à Maurice : tout va bien ! Je répète ! Tout va bien !'],
            ['Lucie Olivera', 'Maurice à Jean-Paul : tout va bien aussi !'],
        ]);
    }

    public function testAjaxSearchCommittee()
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=pa', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=pa', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame(\GuzzleHttp\json_encode([[
            'uuid' => LoadAdherentData::COMMITTEE_1_UUID,
            'name' => 'En Marche Paris 8',
        ]]), $this->client->getResponse()->getContent());

        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testCommitteeSupportCitizenProject()
    {
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com', 'Champion20');

        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID);

        $this->assertFalse($citizenProject->isApproved());
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));
        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $committee = $this->getCommitteeRepository()->findOneByUuid(LoadAdherentData::COMMITTEE_4_UUID);
        $this->assertCount(0, $citizenProject->getCommitteeSupports());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));
        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertClientIsRedirectedTo(sprintf('/projets-citoyens/%s', $citizenProject->getSlug()), $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $flash = $crawler->filter('#notice-flashes');
        $this->assertSame(1, count($flash));
        $this->assertSame(sprintf('Votre comité soutient maintenant le projet citoyen %s', $citizenProject->getName()), trim($flash->text()));

        $this->manager->clear();
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(1, $citizenProject->getApprovedCommitteeSupports());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));

        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertClientIsRedirectedTo(sprintf('/projets-citoyens/%s', $citizenProject->getSlug()), $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $flash = $crawler->filter('#notice-flashes');
        $this->assertSame(1, count($flash));
        $this->assertSame(sprintf('Votre comité %s soutient déjà le projet citoyen %s',
            $committee->getName(),
            $citizenProject->getName()
        ), trim($flash->text()));

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $committeeOnSupport = $crawler->filter('#support-committee')->filter('li');
        $this->assertSame(1, $committeeOnSupport->count());
        $this->assertSame(trim($committeeOnSupport->first()->text()), $committee->getName());

        $citizenProject->removeCommitteeSupport($committee);
        $this->manager->flush();
        $this->manager->clear();

        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(0, $citizenProject->getCommitteeSupports()->toArray());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->submit($crawler->selectButton('Notre comité souhaite aussi soutenir ce projet')->form());
        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('#notice-flashes');
        $this->assertCount(1, $flash);
        $this->assertSame(sprintf('Votre comité soutient maintenant le projet citoyen %s', $citizenProject->getName()), trim($flash->text()));

        $this->manager->clear();
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(1, $citizenProject->getApprovedCommitteeSupports());

        $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->submit($crawler->selectButton('Notre comité souhaite aussi soutenir ce projet')->form());
        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('#notice-flashes');
        $this->assertCount(1, $flash);
        $this->assertSame(sprintf('Votre comité %s soutient déjà le projet citoyen %s',
            $committee->getName(),
            $citizenProject->getName()
        ), trim($flash->text()));
    }

    public function testCitizenProjectContactActors()
    {
        // Authenticate as the administrator (host)
        $crawler = $this->authenticateAsAdherent($this->client, 'lolodie.dutemps@hotnix.tld', 'politique2017');
        $crawler = $this->client->click($crawler->selectLink('En Marche - Projet citoyen')->link());
        $crawler = $this->client->click($crawler->selectLink('Tous >')->link());

        $token = $crawler->filter('#members-contact-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $actorsListUrl = $this->client->getRequest()->getPathInfo();
        $contactUrl = $actorsListUrl.'/contact';

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Try to post with an empty message
        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'message' => ' ',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('.form__errors > .form__error')->text());

        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'message' => 'Bonsoir à tous.',
        ]);

        $this->assertClientIsRedirectedTo($actorsListUrl, $this->client);
        $crawler = $this->client->followRedirect();
        $this->seeMessageSuccesfullyCreatedFlash($crawler, 'Félicitations, votre message a bien été envoyé aux acteurs sélectionnés.');

        // Try to illegally contact an adherent, adds an adherent not linked with this citizen project
        $uuids[] = LoadAdherentData::ADHERENT_1_UUID;

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        // The protection filter should be remove the illegal adherent
        $this->assertCount(1, json_decode($crawler->filter('input[name="contacts"]')->attr('value'), true));

        // Force the contact form with the foreign uuid
        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => json_encode($uuids),
            'message' => 'Bonsoir à tous.',
        ]);

        $this->assertClientIsRedirectedTo($actorsListUrl, $this->client);
        $crawler = $this->client->followRedirect();
        $this->seeMessageSuccesfullyCreatedFlash($crawler, 'Félicitations, votre message a bien été envoyé aux acteurs sélectionnés.');
    }

    public function testAnonymousUserIsNotAllowedToFollowCitizenProject()
    {
        $committeeUrl = sprintf('/projets-citoyens/%s', 'le-projet-citoyen-a-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertTrue($this->seeRegisterLink($crawler));
    }

    public function testAuthenticatedCitizenProjectAdministratorCanUnfollowCommittee()
    {
        // Login as administrator
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', 'le-projet-citoyen-a-paris-8'));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $unfollowButton = $crawler->filter('.citizen-project-unfollow');

        // Button should be disabled for adminitrator
        $this->assertNotNull($unfollowButton->attr('disabled'));
        $this->assertSame('En tant que porteur de projet citoyen, vous ne pouvez pas cesser de le suivre.', trim($crawler->filter('.citizen-project-follow--anonymous__link')->text()));
    }

    public function testAuthenticatedAdherentCanFollowCitizenProject()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        // Browse to the citizen project details page
        $citizenProjectUrl = sprintf('/projets-citoyens/%s', 'le-projet-citoyen-a-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $citizenProjectUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 acteurs', $crawler->filter('#followers > h3')->text());
        $this->assertTrue($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));

        // Emulate POST request to follow the committee.
        $token = $crawler->selectButton('Rejoindre ce projet citoyen')->attr('data-csrf-token');
        $this->client->request(Request::METHOD_POST, $citizenProjectUrl.'/rejoindre', ['token' => $token]);

        // Email sent to the host
        $this->assertCountMails(1, CitizenProjectNewFollowerMessage::class, 'jacques.picard@en-marche.fr');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Refresh the committee details page
        $crawler = $this->client->request(Request::METHOD_GET, $citizenProjectUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('3 acteurs', $crawler->filter('#followers > h3')->text());
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertTrue($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));

        // Emulate POST request to unfollow the committee.
        $token = $crawler->selectButton('Quitter ce projet citoyen')->attr('data-csrf-token');
        $this->client->request(Request::METHOD_POST, $citizenProjectUrl.'/quitter', ['token' => $token]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Refresh the committee details page
        $crawler = $this->client->request(Request::METHOD_GET, $citizenProjectUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 acteurs', $crawler->filter('#followers > h3')->text());
        $this->assertTrue($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));
    }

    private function assertSeeComments(array $comments)
    {
        foreach ($comments as $position => $comment) {
            list($author, $text) = $comment;
            $this->assertSeeComment($position, $author, $text);
        }
    }

    private function assertSeeComment(int $position, string $author, string $text)
    {
        $crawler = $this->client->getCrawler();
        $this->assertContains($author, $crawler->filter('.citizen-project-comment')->eq($position)->text());
        $this->assertContains($text, $crawler->filter('.citizen-project-comment p')->eq($position)->text());
    }

    private function seeCommentSection(): bool
    {
        return 1 === count($this->client->getCrawler()->filter('.citizen-project-comments'));
    }

    private function seeMessageSuccesfullyCreatedFlash(Crawler $crawler, ?string $message = null)
    {
        $flash = $crawler->filter('#notice-flashes');

        if ($message) {
            $this->assertSame($message, trim($flash->text()));
        }

        return 1 === count($flash);
    }

    private function seeFollowLink(Crawler $crawler): bool
    {
        return 1 === count($crawler->filter('.citizen-project-follow'));
    }

    private function seeUnfollowLink(Crawler $crawler): bool
    {
        return 1 === count($crawler->filter('.citizen-project-unfollow'));
    }

    private function seeRegisterLink(Crawler $crawler, $nb = 1): bool
    {
        $this->assertCount($nb, $crawler->filter('.citizen-project-follow--disabled'));

        return 1 === count($crawler->filter('#citizen-project-register-link'));
    }

    private function seeReportLink(): bool
    {
        try {
            $this->client->getCrawler()->selectLink('Signaler un abus')->link();
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
            LoadCitizenProjectCommentData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}