<?php

namespace SensioLabs\JobBoardBundle\Security\Voter;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

class JobUpdateVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return [Job::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedAttributes()
    {
        return ['JOB_UPDATE'];
    }

    /**
     * {@inheritdoc}
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        return $user->getUsername() === $object->getUserName();
    }
}
