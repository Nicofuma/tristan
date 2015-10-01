<?php

namespace SensioLabs\JobBoardBundle\Security\Voter;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

class JobVoter extends AbstractVoter
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
        return ['JOB_UPDATE', 'JOB_DELETE'];
    }

    /**
     * {@inheritdoc}
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        if (!$user instanceof User) {
            return false;
        }

        return $user->getUuid() === $object->getUser()->getUuid();
    }
}
