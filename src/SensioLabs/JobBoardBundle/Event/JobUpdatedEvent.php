<?php

namespace SensioLabs\JobBoardBundle\Event;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\EventDispatcher\Event;

class JobUpdatedEvent extends Event
{
    const BY_ADMIN = 'by-admin';
    const BY_USER = 'by-user';

    private $updatedBy;
    private $oldJob;
    private $newJob;

    public function __construct(Job $oldJob, Job $newJob, $updatedBy)
    {
        $this->oldJob = $oldJob;
        $this->newJob = $newJob;
        $this->updatedBy = $updatedBy;
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

    /**
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
