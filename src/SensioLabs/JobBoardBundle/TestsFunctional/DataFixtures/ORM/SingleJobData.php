<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;

class SingleJobData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $job = new Job();
        $job
            ->setTitle('FooBar Job')
            ->setDescription('This is the description of an amazing job!')
            ->setCompany('FooBar & Co')
            ->setContractType(Job::CONTRACT_FULL_TIME)
            ->setCity('Paris')
            ->setCountry('FR')
            ->setUserName('user-1')
            ->setHowToApply('Send an email to jobs@foobar.com')
        ;

        $this->setReference('job', $job);

        $manager->persist($job);
        $manager->flush();
    }
}
