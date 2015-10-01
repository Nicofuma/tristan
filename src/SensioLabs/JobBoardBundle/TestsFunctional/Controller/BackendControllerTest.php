<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\FifteenJobsData;
use SensioLabs\JobBoardBundle\TestsFunctional\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BackendControllerTest extends WebTestCase
{
    public function testAccessDenied()
    {
        $this->loadFixtures([]);

        // Anonymous
        $this->client->request('GET', '/backend');
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        self::assertContains('https://connect.sensiolabs.com/oauth/authorize', $this->client->getResponse()->headers->get('Location'));

        // Not admin
        $this->signin('user-1');
        $this->client->request('GET', '/backend');
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testListAction()
    {
        $fixtures = $this->loadFixtures([FifteenJobsData::class])->getReferenceRepository();

        /** @var Job $job0 */
        $job0 = $fixtures->getReference('job-0');
        /** @var Job $job14 */
        $job14 = $fixtures->getReference('job-14');

        $this->signin('user-1', ['ROLE_ADMIN']);
        $crawler = $this->client->request('GET', '/backend');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Default sort
        self::assertCount(15, $crawler->filter('#backend-job-container table tbody tr'));
        self::assertNotContains('▲', $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th')->eq(0)->text());
        self::assertNotContains('▲', $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th')->eq(1)->text());
        self::assertContains('▲', $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th')->eq(2)->text());
        $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th')->each(function ($node, $i) {
            self::assertNotContains('▼', $node->text());
        });
        self::assertSame($job0->getTitle(), trim($crawler->filter('#backend-job-container table tbody tr')->eq(0)->filter('td')->eq(1)->text()));

        $link = $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th a')->eq(2)->link();

        // Manually change the direction because KnpPaginator uses directly $_GET (which will not be altered by Symfony itself)
        $_GET['direction'] = 'desc';
        $crawler = $this->client->click($link);

        // Reversed createdAt sort
        self::assertCount(15, $crawler->filter('#backend-job-container table tbody tr'));
        self::assertNotContains('▼', $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th')->eq(0)->text());
        self::assertNotContains('▼', $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th')->eq(1)->text());
        self::assertContains('▼', $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th')->eq(2)->text());
        $crawler->filter('#backend-job-container table thead tr')->eq(0)->filter('th')->each(function ($node, $i) {
            self::assertNotContains('▲', $node->text());
        });
        self::assertSame($job14->getTitle(), trim($crawler->filter('#backend-job-container table tbody tr')->eq(0)->filter('td')->eq(1)->text()));

        $editLink = $crawler->filter('#backend-job-container table tbody tr')->eq(0)->filter('td')->eq(6)->filter('a')->link();
        self::assertSame('/backend/15/edit', $editLink->getNode()->getAttribute('href'));

        $deleteButton = $crawler->filter('#backend-job-container table tbody tr')->eq(0)->selectButton('Delete');
        self::assertCount(1, $deleteButton);
        $form = $deleteButton->form();
        $this->client->submit($form);
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->followRedirect();
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertCount(0, $crawler->filter('.backend-flashes .error'));
        self::assertCount(1, $crawler->filter('.backend-flashes .success'));
        self::assertCount(14, $crawler->filter('#backend-job-container table tbody tr'));
    }
}
