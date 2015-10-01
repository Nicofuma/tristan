<?php

namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SensioLabs\JobBoardBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    public function loadUserByUsername($uuid)
    {
        $user = $this->findOneByUuid($uuid);

        if (!$user) {
            $user = new User($uuid);
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('class %s is not supported', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUuid());
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
