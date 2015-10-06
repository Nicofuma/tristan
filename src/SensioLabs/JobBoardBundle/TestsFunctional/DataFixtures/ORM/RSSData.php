<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\JobStatus;

class RSSData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $job = new Job();
        $job
            ->setTitle('Job OK')
            ->setDescription('This is the description of an amazing job!')
            ->setCompany($this->getCompany($manager, 'SensioLabs', 'FR', 'Paris'))
            ->setContractType('full-time')
            ->setHowToApply('Send an email to jobs@sensiolabs.com')
            ->setIsValidated()
            ->setPublishedAt(new \DateTime('-10 day'))
            ->setEndedAt(new \DateTime('+1 year'))
            ->setStatus(JobStatus::create(JobStatus::PUBLISHED))
        ;
        $this->setReference('job-ok', $job);
        $manager->persist($job);

        $job = new Job();
        $job
            ->setTitle('Job Later')
            ->setDescription('This is the description of an amazing job!')
            ->setCompany($this->getCompany($manager, 'SensioLabs', 'US', 'Paris'))
            ->setContractType('full-time')
            ->setHowToApply('Send an email to jobs@sensiolabs.com')
            ->setIsValidated()
            ->setPublishedAt(new \DateTime('-100 day'))
            ->setEndedAt(new \DateTime('+1 year'))
            ->setStatus(JobStatus::create(JobStatus::PUBLISHED))
        ;
        $this->setReference('job-later', $job);
        $manager->persist($job);

        $job = new Job();
        $job
            ->setTitle('Job Future')
            ->setDescription('This is the description of an amazing job!')
            ->setCompany($this->getCompany($manager, 'SensioLabs', 'FR', 'Paris'))
            ->setContractType('full-time')
            ->setHowToApply('Send an email to jobs@sensiolabs.com')
            ->setIsValidated()
            ->setPublishedAt(new \DateTime('+10 day'))
            ->setEndedAt(new \DateTime('+1 year'))
            ->setStatus(JobStatus::create(JobStatus::PUBLISHED))
        ;
        $this->setReference('job-future', $job);
        $manager->persist($job);

        $job = new Job();
        $job
            ->setTitle('Job Past')
            ->setDescription('This is the description of an amazing job!')
            ->setCompany($this->getCompany($manager, 'SensioLabs', 'FR', 'Paris'))
            ->setContractType('full-time')
            ->setHowToApply('Send an email to jobs@sensiolabs.com')
            ->setIsValidated()
            ->setPublishedAt(new \DateTime('-10 day'))
            ->setEndedAt(new \DateTime('-1 day'))
            ->setStatus(JobStatus::create(JobStatus::PUBLISHED))
        ;
        $this->setReference('job-past', $job);
        $manager->persist($job);

        $job = new Job();
        $job
            ->setTitle('Job Not Published')
            ->setDescription('This is the description of an amazing job!')
            ->setCompany($this->getCompany($manager, 'SensioLabs', 'FR', 'Paris'))
            ->setContractType('full-time')
            ->setHowToApply('Send an email to jobs@sensiolabs.com')
            ->setIsValidated()
            ->setPublishedAt(new \DateTime('-10 day'))
            ->setEndedAt(new \DateTime('+1 year'))
            ->setStatus(JobStatus::create(JobStatus::NEW_JOB))
        ;
        $this->setReference('job-not-published', $job);
        $manager->persist($job);

        $manager->flush();
    }
}
