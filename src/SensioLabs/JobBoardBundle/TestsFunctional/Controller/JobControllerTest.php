<?php

namespace SensioLabs\JobBoardBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use SensioLabs\JobBoardBundle\DataFixtures\ORM\SingleJob;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\HttpFoundation\Response;

class JobControllerTest extends WebTestCase
{
    public function testUpdateAction()
    {
        $fixtures = $this->loadFixtures([SingleJob::class])->getReferenceRepository();

        $client = static::createClient();
        $crawler = $client->request('GET', '/FR/full-time/foobar-job/update');
        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $buttonCrawlerNode = $crawler->selectButton('Update');
        self::assertCount(1, $buttonCrawlerNode);

        /** @var Job $reference */
        $reference = $fixtures->getReference('job');

        $form = $buttonCrawlerNode->form();
        self::assertArraySubset([
            'job[title]' => $reference->getTitle(),
            'job[country]' => $reference->getCountry(),
            'job[city]' => $reference->getCity(),
            'job[contractType]' => $reference->getContractType(),
            'job[description]' => $reference->getDescription(),
            'job[howToApply]' => $reference->getHowToApply(),
            'job[company]' => $reference->getCompany(),
        ], $form->getValues());

        $form->setValues([
            'job[title]' => 'New Title',
            'job[country]' => 'GB',
            'job[city]' => 'New City',
            'job[contractType]' => Job::CONTRACT_ALTERNANCE_TIME,
            'job[description]' => 'New Description',
            'job[howToApply]' => 'New HTA',
            'job[company]' => 'New Company',
        ]);

        $client->submit($form);
        self::assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();
        self::assertContains('Preview', $crawler->filter('#breadcrumb .active')->text());
        self::assertContains('New Title', $crawler->filter('h2 .title')->text());
    }
}
