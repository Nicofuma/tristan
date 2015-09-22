<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class WebTestCase extends BaseWebTestCase
{
    /** @var Client  */
    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->client = null;
    }
}
