<?php

namespace SensioLabs\JobBoardBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\HttpFoundation\Response;

class BaseControllerTest extends WebTestCase
{
    public function testPostActionFailure()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/post');
        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $buttonCrawlerNode = $crawler->selectButton('Preview');
        $form = $buttonCrawlerNode->form();
        $crawler = $client->submit($form);
        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        self::assertEquals(6, $crawler->filter('.errors li')->count());
    }

    public function testPostActionSucess()
    {
        $this->loadFixtures([]);

        $client = static::createClient();

        $crawler = $client->request('GET', '/post');
        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

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
        $client->submit($form);
        self::assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        self::assertContains('Preview', $crawler->filter('#breadcrumb .active')->text());
    }
}
