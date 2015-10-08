<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\JobStatus;
use SensioLabs\JobBoardBundle\Entity\User;

class MixedStatusData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $connectUser = new \SensioLabs\Connect\Api\Entity\User();
        $connectUser
            ->set('email', 'user-1@example.org')
            ->set('username', 'user-1')
            ->set('name', 'User 1')
            ->set('uuid', '12345678-1234-1234-1234-123456789012')
        ;

        $user = new User($connectUser->get('uuid'));
        $user->updateFromConnect($connectUser);

        $manager->persist($user);

        foreach (JobStatus::getPossibleValues() as $status) {
            $job = new Job();
            $job
                ->setTitle($status.' Job')
                ->setDescription('This is the description of an amazing job!')
                ->setCompany($this->getCompany($manager))
                ->setContractType(Job::CONTRACT_FULL_TIME)
                ->setUser($user)
                ->setHowToApply('Send an email to jobs@foobar.com')
                ->setIsValidated()
                ->setPublishedAt(new \DateTime())
                ->setEndedAt(new \DateTime('+1 year'))
                ->setStatus(JobStatus::create($status))
                ->setStatusUpdatedAt(new \DateTime('-20 days'))
            ;

            $this->setReference($status, $job);
            $manager->persist($job);
        }

        $manager->flush();
    }
}
