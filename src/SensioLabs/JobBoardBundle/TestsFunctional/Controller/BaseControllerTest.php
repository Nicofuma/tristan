<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\Controller;

use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\FifteenJobsData;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\TestsFunctional\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BaseControllerTest extends WebTestCase
{
    public function testPostActionFailure()
    {
        $crawler = $this->client->request('GET', '/post');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $buttonCrawlerNode = $crawler->selectButton('Preview');
        $form = $buttonCrawlerNode->form();
        $crawler = $this->client->submit($form);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        self::assertEquals(6, $crawler->filter('.errors li')->count());
    }

    public function testPostActionSucess()
    {
        $this->loadFixtures([]);

        $crawler = $this->client->request('GET', '/post');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $buttonCrawlerNode = $crawler->selectButton('Preview');
        $form = $buttonCrawlerNode->form([
            'job[title]' => 'Test Job',
            'job[country]' => 'FR',
            'job[city]' => 'Bar',
            'job[contractType]' => Job::CONTRACT_FULL_TIME,
            'job[description]' => 'This is a description',
            'job[howToApply]' => 'How to apply?',
            'job[company]' => 'FooBar',
        ]);
        $this->client->submit($form);
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->followRedirect();

        self::assertContains('Preview', $crawler->filter('#breadcrumb .active')->text());
    }

    public function testIndexAction()
    {
        $this->loadFixtures([FifteenJobsData::class]);

        $crawler = $this->client->request('GET', '/');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        self::assertCount(10, $crawler->filter('#job-container .box'));
        self::assertContains('#0 - FooBar Job', $crawler->filter('#job-container .box .title')->eq(0)->text());
    }

    public function testIndexActionPage2()
    {
        $this->loadFixtures([FifteenJobsData::class]);

        $crawler = $this->client->request('GET', '/', ['page' => 2], [], ['X-Requested-With' => 'XMLHttpRequest']);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        self::assertCount(5, $crawler->filter('#job-container .box'));
        self::assertContains('#10 - FooBar Job', $crawler->filter('#job-container .box .title')->eq(0)->text());
    }

    public function testIndexActionEmptyPage()
    {
        $this->loadFixtures([FifteenJobsData::class]);

        $this->client->request('GET', '/', ['page' => 3], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        self::assertEmpty($this->client->getResponse()->getContent());
    }
}
