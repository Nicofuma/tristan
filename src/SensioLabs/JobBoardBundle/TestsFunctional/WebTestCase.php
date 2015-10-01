<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
use SensioLabs\Connect\Security\Authentication\Token\ConnectToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\User;

class WebTestCase extends BaseWebTestCase
{
    /** @var Client  */
    protected $client;

    protected function setUp()
    {
        // reset $_GET because it is used directly by KnpPaginator
        $_GET = [];
        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->client = null;
    }

    protected function signin($user = 'user-1', $roles = ['ROLE_USER', 'ROLE_CONNECT_USER'], $scope = null, $providerKey = 'secured_area', $apiUser = null)
    {
        if (!is_object($user)) {
            $user = new User($user, 'password', $roles);
        }

        $token = new ConnectToken($user, null, $apiUser, $providerKey, $scope, $roles);

        $this->getContainer()->get('security.token_storage')->setToken($token);

        $session = $this->getContainer()->get('session');
        $session->set('_security_'.$providerKey, serialize($token));
        $session->save();

        // the client must register the session cookie
        // taken from TestSessionListener
        $params = session_get_cookie_params();
        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId(), 0 === $params['lifetime'] ? 0 : time() + $params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly']));

        return $user;
    }

    protected function signout($providerKey = 'main')
    {
        $session = $this->getContainer()->get('session');
        $session->remove('_security_'.$providerKey);
        $session->save();

        $this->getContainer()->get('security.token_storage')->setToken(null);
    }
}
