<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\FifteenJobsData;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\FilterJobsData;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\RSSData;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\SingleNotValidatedJobData;
use SensioLabs\JobBoardBundle\TestsFunctional\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            'job[company][country]' => 'FR',
            'job[company][city]' => 'Bar',
            'job[contractType]' => Job::CONTRACT_FULL_TIME,
            'job[description]' => 'This is a description',
            'job[howToApply]' => 'How to apply?',
            'job[company][name]' => 'FooBar',
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

    public function testIndexActionOnlyValidatedJobs()
    {
        $this->loadFixtures([SingleNotValidatedJobData::class]);

        $crawler = $this->client->request('GET', '/');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        self::assertCount(0, $crawler->filter('#job-container .box'));
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

        $link = $crawler->selectLink('All countries')->link();
        self::assertSame('/?contractType=full-time', $link->getNode()->getAttribute('href'));

        // One contract type
        $crawler = $this->client->click($link);
        self::assertCount(2 + FilterJobsData::NB_COUNTRIES, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), FilterJobsData::NB_COUNTRIES.' countries');
        self::assertCount(1 + 1, $crawler->filter('#left .filter ul')->eq(1)->filter('li'), '1 contract type selected');
        $link = $crawler->selectLink('All types of contracts')->link();
        self::assertSame('/', $link->getNode()->getAttribute('href'));

        // No filter
        $crawler = $this->client->click($link);
        self::assertCount(2 + FilterJobsData::NB_COUNTRIES, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), FilterJobsData::NB_COUNTRIES.' countries');
        self::assertCount(1 + 5, $crawler->filter('#left .filter ul')->eq(1)->filter('li'), '5 contract types');
        $link = $crawler->selectLink(Intl::getRegionBundle()->getCountryName($countryNames[0]))->link();
        self::assertSame('/?country='.$countryNames[0], $link->getNode()->getAttribute('href'));

        // One country selected
        $crawler = $this->client->click($link);
        self::assertCount(1 + 1, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), '1 country selected');
        $link = $crawler->filter('#left .filter ul')->eq(1)->selectLink('Full Time')->link();
        self::assertsame('Full Time ('.FilterJobsData::getNbJobsForCountryAndContractType($countryNames[0], 'full-time').')', $link->getNode()->textContent);
        self::assertSame('/?country='.$countryNames[0].'&contractType=full-time', $link->getNode()->getAttribute('href'));
        $link = $crawler->selectLink('All jobs')->link();
        self::assertSame('/', $link->getNode()->getAttribute('href'));

        // no filter
        $crawler = $this->client->click($link);
        self::assertCount(2 + FilterJobsData::NB_COUNTRIES, $crawler->filter('#left .filter ul')->eq(0)->filter('li'), FilterJobsData::NB_COUNTRIES.' countries');
        self::assertCount(FilterJobsData::NB_COUNTRIES - 4, $crawler->filter('#left .filter ul')->eq(0)->filter('li[style="display:none"]'), 'Only 4 countries displayed by default');
        self::assertCount(1 + 5, $crawler->filter('#left .filter ul')->eq(1)->filter('li'), '5 contract types');
        self::assertCount(0, $crawler->filter('#left .filter ul')->eq(1)->filter('li[style="display:none"]'), 'No folded contract type');
    }

    public function testManage()
    {
        $fixtures = $this->loadFixtures([FifteenJobsData::class])->getReferenceRepository();

        /** @var Job $reference */
        $reference = $fixtures->getReference('job-1');
        $jobBaseUrl = '/'.$reference->getCompany()->getCountry().'/'.$reference->getContractType();

        $this->signin('user-1');

        $crawler = $this->client->request('GET', '/manage');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertCount(1, $crawler->filter('.box table tbody tr'));

        $previewLink = $crawler->filter('.box table tbody tr')->eq(0)->filter('a')->eq(0)->link();
        self::assertSame($jobBaseUrl.'/'.$reference->getSlug().'/preview', $previewLink->getNode()->getAttribute('href'));

        $makeChangesLink = $crawler->filter('.box table tbody tr')->eq(0)->filter('.action a')->eq(0)->link();
        self::assertSame($jobBaseUrl.'/'.$reference->getSlug().'/update', $makeChangesLink->getNode()->getAttribute('href'));

        $deleteLink = $crawler->filter('.box table tbody tr')->eq(0)->filter('.action a')->eq(1)->link();
        self::assertRegExp('#^'.$jobBaseUrl.'/'.$reference->getSlug().'/delete-[0-9a-zA-Z_-]+$#', $deleteLink->getNode()->getAttribute('href'));

        $this->client->click($deleteLink);
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->followRedirect();
        self::assertCount(1, $crawler->filter('.box table tbody tr'));
        self::assertSame('You have no jobs.', trim($crawler->filter('.box table tbody tr')->eq(0)->filter('td')->eq(0)->text()));
    }

    public function testRSSAction()
    {
        $this->loadFixtures([RSSData::class]);
        $baseUrl = $this->getContainer()->get('router')->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $crawler = $this->client->request('GET', '/rss');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertCount(2, $crawler->filterXPath('//item'));

        self::assertStringMatchesFormat("%A
%A  <item>
      <title><![CDATA[Job OK]]></title>
      <description><![CDATA[This is the description of an amazing job!]]></description>
      <link>{$baseUrl}FR/full-time/job-ok</link>
      <pubDate>%A</pubDate>
    </item>
    <item>
      <title><![CDATA[Job Later]]></title>
      <description><![CDATA[This is the description of an amazing job!]]></description>
      <link>{$baseUrl}US/full-time/job-later</link>
      <pubDate>%A</pubDate>
    </item>
%A", $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/rss?country=FR');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertCount(1, $crawler->filterXPath('//item'));

        self::assertStringMatchesFormat("%A
%A  <item>
      <title><![CDATA[Job OK]]></title>
      <description><![CDATA[This is the description of an amazing job!]]></description>
      <link>{$baseUrl}FR/full-time/job-ok</link>
      <pubDate>%A</pubDate>
    </item>
%A", $this->client->getResponse()->getContent());
    }
}
