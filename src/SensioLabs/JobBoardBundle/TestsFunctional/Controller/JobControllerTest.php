<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\SingleJobData;
use SensioLabs\JobBoardBundle\TestsFunctional\WebTestCase;
use Swift_Message;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\HttpFoundation\Response;

class JobControllerTest extends WebTestCase
{
    public function testUpdateAction()
    {
        $fixtures = $this->loadFixtures([SingleJobData::class])->getReferenceRepository();

        $this->signin();
        $crawler = $this->client->request('GET', '/FR/full-time/foobar-job/update');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->enableProfiler();

        $buttonCrawlerNode = $crawler->selectButton('Update');
        self::assertCount(1, $buttonCrawlerNode);

        /** @var Job $reference */
        $reference = $fixtures->getReference('job');

        $form = $buttonCrawlerNode->form();
        self::assertArraySubset([
            'job[title]' => $reference->getTitle(),
            'job[company][country]' => $reference->getCompany()->getCountry(),
            'job[company][city]' => $reference->getCompany()->getCity(),
            'job[contractType]' => $reference->getContractType(),
            'job[description]' => $reference->getDescription(),
            'job[howToApply]' => $reference->getHowToApply(),
            'job[company][name]' => $reference->getCompany()->getName(),
        ], $form->getValues());

        $form->setValues([
            'job[title]' => 'New Title',
            'job[company][country]' => 'GB',
            'job[company][city]' => 'New City',
            'job[contractType]' => Job::CONTRACT_ALTERNANCE_TIME,
            'job[description]' => 'New Description',
            'job[howToApply]' => 'New HTA',
            'job[company][name]' => 'New Company',
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
        self::assertEquals('A validated announcement has been updated', $message->getSubject());
        self::assertEquals($this->getContainer()->getParameter('mailer_app_sender'), key($message->getFrom()));
        self::assertEquals($this->getContainer()->getParameter('admin_email_address'), key($message->getTo()));
        self::assertStringMatchesFormat(
'%A
<table>
    <tr>
        <td rowspan="2">BEFORE</td>
        <td>Title: FooBar Job</td>
    </tr>
    <tr>
        <td>Description:<br/>This is the description of an amazing job!</td>
    </tr>

    <tr>
        <td rowspan="2">AFTER</td>
        <td>Title: New Title</td>
    </tr>
    <tr>
        <td>Description:<br/><br/>New Description</td>
    </tr>
</table>
%A
',
            $message->getBody()
        );

        $crawler = $this->client->followRedirect();
        self::assertContains('Preview', $crawler->filter('#breadcrumb .active')->text());
        self::assertContains('New Title', $crawler->filter('h2 .title')->text());
    }

    public function testUpdateActionNotAuthenticated()
    {
        $this->loadFixtures([SingleJobData::class])->getReferenceRepository();

        $this->client->request('GET', '/FR/full-time/foobar-job/update');
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        self::assertContains('https://connect.sensiolabs.com/oauth/authorize', $this->client->getResponse()->headers->get('Location'));
    }

    public function testUpdateActionWrongUser()
    {
        $this->loadFixtures([SingleJobData::class])->getReferenceRepository();

        $this->signup(['username' => 'wrong-user']);
        $this->signin('wrong-user');
        $this->client->request('GET', '/FR/full-time/foobar-job/update');
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testShowAction()
    {
        $fixtures = $this->loadFixtures([SingleJobData::class])->getReferenceRepository();

        $crawler = $this->client->request('GET', '/FR/full-time/foobar-job');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        /** @var Job $reference */
        $reference = $fixtures->getReference('job');
        self::assertSame($reference->getTitle(), trim($crawler->filter('.box h2 a.title')->text()));
        self::assertSame('/FR/full-time/foobar-job', trim($crawler->filter('.box h2 a.title')->attr('href')));
        self::assertSame($reference->getDescription(), trim($crawler->filter('#job-description')->text()));
        self::assertSame('/?country='.$reference->getCompany()->getCountry(), trim($crawler->filter('.box .details .filter a')->eq(0)->attr('href')));
        self::assertSame('/?country='.$reference->getCompany()->getCountry().'&contractType='.$reference->getContractType(), trim($crawler->filter('.box .details .filter a')->eq(1)->attr('href')));
    }

    public function testJobViewCount()
    {
        $fixtures = $this->loadFixtures([SingleJobData::class])->getReferenceRepository();

        /** @var Job $job */
        $job = $fixtures->getReference('job');

        self::assertSame(0, $job->getViewCountHomepage());
        self::assertSame(0, $job->getViewCountDetails());
        self::assertSame(0, $job->getViewCountAPI());
        self::assertSame(0, $job->getTotalViewCount());

        $this->client->request('GET', '/');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->getObjectManager()->refresh($job);

        self::assertSame(1, $job->getViewCountHomepage());
        self::assertSame(0, $job->getViewCountDetails());
        self::assertSame(0, $job->getViewCountAPI());
        self::assertSame(1, $job->getTotalViewCount());

        $this->client->request('GET', '/FR/full-time/foobar-job');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->getObjectManager()->refresh($job);

        self::assertSame(1, $job->getViewCountHomepage());
        self::assertSame(1, $job->getViewCountDetails());
        self::assertSame(0, $job->getViewCountAPI());
        self::assertSame(2, $job->getTotalViewCount());
    }
}
