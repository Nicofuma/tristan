<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\JobStatus;
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
                ->setCompany($this->getCompany($manager, 'SensioLabs', $countries[$i % count($countries)]))
                ->setContractType($contractTypes[$i % 5])
                ->setUser($this->getUser($manager, $i))
                ->setHowToApply('Send an email to jobs@foobar.com')
                ->setCreatedAt(new \DateTime('+'.$i.' month'))
                ->setIsValidated()
                ->setPublishedAt(new \DateTime())
                ->setEndedAt(new \DateTime('+1 year'))
                ->setStatus(JobStatus::create(JobStatus::PUBLISHED))
            ;

            $this->setReference('job-'.$i, $job);

            $manager->persist($job);
        }

        $manager->flush();
    }
}
