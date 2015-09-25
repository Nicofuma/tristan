<?php

namespace SensioLabs\JobBoardBundle\Event;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\EventDispatcher\Event;

class JobUpdatedEvent extends Event
{
    private $oldJob;
    private $newJob;

    public function __construct(Job $oldJob, Job $newJob)
    {
        $this->oldJob = $oldJob;
        $this->newJob = $newJob;
    }

    /**
     * @return Job
     */
    public function getOldJob()
    {
        return $this->oldJob;
    }

    /**
     * @return Job
     */
    public function getNewJob()
    {
        return $this->newJob;
    }
}
