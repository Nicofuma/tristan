<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
use SensioLabs\Connect\Security\Authentication\Token\ConnectToken;
use SensioLabs\JobBoardBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;

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

    protected function signup(array $userInfo = array())
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $userData = [
            'email' => 'john.doe@example.com',
            'name' => 'John Doe',
            'username' => 'john'.mt_rand(),
            'roles' => ['USER_ROLE'],
        ];
        $userInfo = array_replace($userData, $userInfo);

        $user = new User(self::UUIDGeneratorV4());
        $user
            ->setEmail($userInfo['email'])
            ->setName($userInfo['name'])
            ->setUsername($userInfo['username'])
            ->setRoles($userInfo['roles'])
        ;
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function signin($username = 'user-1', $scope = null, $providerKey = 'secured_area', $apiUser = null)
    {
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('SensioLabsJobBoardBundle:User')->findOneByUsername($username);

        if (!$user) {
            throw new \LogicException(sprintf('The user "%s" does not have an account.', $username));
        }

        $token = new ConnectToken($user, null, $apiUser, $providerKey, $scope, $user->getRoles());

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

    protected static function UUIDGeneratorV4()
    {
        if (extension_loaded('uuid')) {
            return strtolower(uuid_create(UUID_TYPE_RANDOM));
        }

        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
