<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\JobStatus;
use SensioLabs\JobBoardBundle\Entity\User;

class SingleNotValidatedJobData extends AbstractFixture
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

        $job = new Job();
        $job
            ->setTitle('FooBar Job')
            ->setDescription('This is the description of an amazing job!')
            ->setCompany($this->getCompany($manager))
            ->setContractType(Job::CONTRACT_FULL_TIME)
            ->setUser($user)
            ->setHowToApply('Send an email to jobs@foobar.com')
            ->setStatus(JobStatus::create(JobStatus::NEW_JOB))
        ;

        $this->setReference('job', $job);

        $manager->persist($job);
        $manager->flush();
    }
}
