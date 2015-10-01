<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\FifteenJobsData;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\SingleJobData;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\SingleNotValidatedJobData;
use SensioLabs\JobBoardBundle\TestsFunctional\WebTestCase;
use Swift_Message;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        $this->signup(['username' => 'user-1']);
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

        $this->signup(['username' => 'user-admin', 'roles' => ['ROLE_ADMIN']]);
        $this->signin('user-admin');
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

    public function testEditActionValidateJob()
    {
        $fixtures = $this->loadFixtures([SingleNotValidatedJobData::class])->getReferenceRepository();

        $this->signup(['username' => 'user-admin', 'roles' => ['ROLE_ADMIN']]);
        $this->signin('user-admin');
        $crawler = $this->client->request('GET', '/backend/1/edit');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->enableProfiler();

        $buttonCrawlerNode = $crawler->selectButton('Update');
        self::assertCount(1, $buttonCrawlerNode);

        /** @var Job $reference */
        $reference = $fixtures->getReference('job');

        $form = $buttonCrawlerNode->form();
        self::assertArraySubset([
            'job_admin[title]' => $reference->getTitle(),
            'job_admin[country]' => $reference->getCountry(),
            'job_admin[city]' => $reference->getCity(),
            'job_admin[contractType]' => $reference->getContractType(),
            'job_admin[description]' => $reference->getDescription(),
            'job_admin[howToApply]' => $reference->getHowToApply(),
            'job_admin[company]' => $reference->getCompany(),
        ], $form->getValues());

        $form->setValues([
            'job_admin[isValidated]' => true,
            'job_admin[publishedAt]' => '10/05/2015',
            'job_admin[endedAt]' => '10/05/2016',
        ]);

        $this->client->submit($form);
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        self::assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        /** @var Swift_Message $message */
        $message = $collectedMessages[0];

        self::assertInstanceOf('Swift_Message', $message);
        self::assertEquals('Your announcement is now online', $message->getSubject());
        self::assertEquals($this->getContainer()->getParameter('mailer_app_sender'), key($message->getFrom()));
        self::assertEquals($reference->getUser()->getEmail(), key($message->getTo()));

        $baseUrl = $this->getContainer()->get('router')->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
        self::assertStringMatchesFormat(
            '<html>
<body>
Your announcement is now online and can been seen here:
<a href="'.$baseUrl.'FR/full-time/foobar-job">
    '.$baseUrl.'FR/full-time/foobar-job
</a>
</body>
</html>',
            $message->getBody()
        );

        $crawler = $this->client->followRedirect();
        self::assertCount(0, $crawler->filter('.backend-flashes .error'));
        self::assertCount(1, $crawler->filter('.backend-flashes .success'));
        self::assertCount(1, $crawler->filter('#backend-job-container table tbody tr'));
    }

    public function testEditActionReValidateJob()
    {
        $fixtures = $this->loadFixtures([SingleJobData::class])->getReferenceRepository();

        $this->signup(['username' => 'user-admin', 'roles' => ['ROLE_ADMIN']]);
        $this->signin('user-admin');
        $crawler = $this->client->request('GET', '/backend/1/edit');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->enableProfiler();

        $buttonCrawlerNode = $crawler->selectButton('Update');
        self::assertCount(1, $buttonCrawlerNode);

        /** @var Job $reference */
        $reference = $fixtures->getReference('job');

        $form = $buttonCrawlerNode->form();
        self::assertArraySubset([
            'job_admin[title]' => $reference->getTitle(),
            'job_admin[country]' => $reference->getCountry(),
            'job_admin[city]' => $reference->getCity(),
            'job_admin[contractType]' => $reference->getContractType(),
            'job_admin[description]' => $reference->getDescription(),
            'job_admin[howToApply]' => $reference->getHowToApply(),
            'job_admin[company]' => $reference->getCompany(),
        ], $form->getValues());

        $this->client->submit($form);
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        self::assertEquals(0, $mailCollector->getMessageCount());

        $crawler = $this->client->followRedirect();
        self::assertCount(0, $crawler->filter('.backend-flashes .error'));
        self::assertCount(1, $crawler->filter('.backend-flashes .success'));
        self::assertCount(1, $crawler->filter('#backend-job-container table tbody tr'));
    }
}
