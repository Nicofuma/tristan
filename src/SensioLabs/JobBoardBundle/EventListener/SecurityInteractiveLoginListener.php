<?php

namespace SensioLabs\JobBoardBundle\EventListener;

use Doctrine\ORM\EntityManager;
use SensioLabs\Connect\Security\Authentication\Token\ConnectToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SecurityInteractiveLoginListener implements EventSubscriberInterface
{
    private $em;
    private $admin_uuid;

    public function __construct(EntityManager $em, $admin_uuid)
    {
        $this->em = $em;
        $this->admin_uuid = $admin_uuid;
    }

    public function registerUser(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();

        if (!$token instanceof ConnectToken) {
            return;
        }

        $user = $token->getUser();

        if ($user->getUuid() === $this->admin_uuid && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $roles = $user->getRoles();
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
        }

        $user->updateFromConnect($token->getApiUser());

        $this->em->persist($user);
        $this->em->flush($user);
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'registerUser',
        ];
    }
}
