<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;

class FifteenJobsData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 15; ++$i) {
            $job = new Job();
            $job
                ->setTitle("#$i - FooBar Job")
                ->setDescription('This is the description of an amazinf job!')
                ->setCompany('FooBar & Co')
                ->setContractType(Job::CONTRACT_FULL_TIME)
                ->setCity('Paris')
                ->setCountry('FR')
                ->setHowToApply('Send an email to jobs@foobar.com')
            ;

            $this->setReference('job', $job);

            $manager->persist($job);
        }

        $manager->flush();
    }
}
