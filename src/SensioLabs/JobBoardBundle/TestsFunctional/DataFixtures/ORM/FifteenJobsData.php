<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Intl\Intl;

class FifteenJobsData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $contractTypes = Job::getContractTypes();
        $countries = array_keys(Intl::getRegionBundle()->getCountryNames());

        for ($i = 0; $i < 15; ++$i) {
            $job = new Job();
            $job
                ->setTitle("#$i - FooBar Job")
                ->setDescription('This is the description of an amazing job!')
                ->setCompany('FooBar & Co')
                ->setContractType($contractTypes[$i % 5])
                ->setCity('Paris')
                ->setCountry($countries[$i % count($countries)])
                ->setHowToApply('Send an email to jobs@foobar.com')
            ;

            $this->setReference('job', $job);

            $manager->persist($job);
        }

        $manager->flush();
    }
}
