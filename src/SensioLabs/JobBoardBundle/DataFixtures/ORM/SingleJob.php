<?php

namespace SensioLabs\JobBoardBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;

class SingleJob extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $job = new Job();
        $job
            ->setTitle('FooBar Job')
            ->setDescription('This is the description of an amazinf job!')
            ->setCompany('FooBar & Co')
            ->setContractType(Job::CONTRACT_FULL_TIME)
            ->setCity('Paris')
            ->setCountry('FR')
            ->setHowToApply('Send an email to jobs@foobar.com')
        ;

        $this->setReference('job', $job);

        $manager->persist($job);
        $manager->flush();
    }
}
