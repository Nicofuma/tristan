<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\FifteenJobsData;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\FilterJobsData;
use SensioLabs\JobBoardBundle\TestsFunctional\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Intl;

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

    public function testIndexActionFilters()
    {
        $countryNames = array_keys(Intl::getRegionBundle()->getCountryNames());

        $this->loadFixtures([FilterJobsData::class]);

        $crawler = $this->client->request('GET', '/');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        self::assertCount(2 + FilterJobsData::NB_COUNTRIES, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), FilterJobsData::NB_COUNTRIES.' countries');
        self::assertCount(FilterJobsData::NB_COUNTRIES - 4, $crawler->filter('#left .filter ul')->eq(0)->filter('li[style="display:none"]'), 'Only 4 countries displayed by default');
        self::assertCount(1 + 5, $crawler->filter('#left .filter ul')->eq(1)->filter('li'), '5 contract types');
        self::assertCount(0, $crawler->filter('#left .filter ul')->eq(1)->filter('li[style="display:none"]'), 'No folded contract type');

        $link = $crawler->selectLink(Intl::getRegionBundle()->getCountryName($countryNames[0]))->link();
        self::assertSame('/?country='.$countryNames[0], $link->getNode()->getAttribute('href'));

        // One country selected
        $crawler = $this->client->click($link);
        self::assertCount(1 + 1, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), '1 country selected');
        $link = $crawler->filter('#left .filter ul')->eq(1)->selectLink('Full Time')->link();
        self::assertsame('Full Time ('.FilterJobsData::getNbJobsForCountryAndContractType($countryNames[0], 'full-time').')', $link->getNode()->textContent);
        self::assertSame('/?country='.$countryNames[0].'&contractType=full-time', $link->getNode()->getAttribute('href'));

        // One country and one contract type
        $crawler = $this->client->click($link);
        self::assertCount(1 + 1, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), '1 country selected');
        self::assertCount(1 + 1, $crawler->filter('#left .filter ul')->eq(1)->filter('li'), '1 contract type selected');
        $crawler->filter('.box .details .filter')->each(function ($node, $i) {
            self::assertNotContains('Alternance', $node->text());
        });
        $link = $crawler->filter('.box .details .filter')->eq(0)->selectLink('Paris')->link();
        self::assertSame('/?country='.$countryNames[0].'&contractType=full-time&city=Paris', $link->getNode()->getAttribute('href'));

        // One country, one contract type and one city
        $crawler = $this->client->click($link);
        $crawler->filter('.box .details .filter')->each(function ($node, $i) {
            self::assertNotContains('Toulouse', $node->text());
        });
        $link = $crawler->selectLink('All countries')->link();
        self::assertSame('/?contractType=full-time&city=Paris', $link->getNode()->getAttribute('href'));

        // One contract type and one city
        $crawler = $this->client->click($link);
        self::assertCount(2 + FilterJobsData::NB_COUNTRIES, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), FilterJobsData::NB_COUNTRIES.' countries');
        self::assertCount(1 + 1, $crawler->filter('#left .filter ul')->eq(1)->filter('li'), '1 contract type selected');
        $link = $crawler->selectLink('All types of contracts')->link();
        self::assertSame('/?city=Paris', $link->getNode()->getAttribute('href'));

        // one city
        $crawler = $this->client->click($link);
        self::assertCount(2 + FilterJobsData::NB_COUNTRIES, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), FilterJobsData::NB_COUNTRIES.' countries');
        self::assertCount(1 + 5, $crawler->filter('#left .filter ul')->eq(1)->filter('li'), '5 contract types');
        $link = $crawler->selectLink('All jobs')->link();
        self::assertSame('/', $link->getNode()->getAttribute('href'));

        // no filter
        $crawler = $this->client->click($link);
        self::assertCount(2 + FilterJobsData::NB_COUNTRIES, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), FilterJobsData::NB_COUNTRIES.' countries');
        self::assertCount(FilterJobsData::NB_COUNTRIES - 4, $crawler->filter('#left .filter ul')->eq(0)->filter('li[style="display:none"]'), 'Only 4 countries displayed by default');
        self::assertCount(1 + 5, $crawler->filter('#left .filter ul')->eq(1)->filter('li'), '5 contract types');
        self::assertCount(0, $crawler->filter('#left .filter ul')->eq(1)->filter('li[style="display:none"]'), 'No folded contract type');
    }
}
