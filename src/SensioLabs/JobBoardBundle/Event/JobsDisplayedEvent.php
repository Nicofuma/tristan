<?php

namespace SensioLabs\JobBoardBundle\Event;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\EventDispatcher\Event;

class JobsDisplayedEvent extends Event
{
    private $jobs;
    private $location;

    public function __construct(array $jobs, $location)
    {
        $this->jobs = $jobs;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return Job[]
     */
    public function getJobs()
    {
        return $this->jobs;
    }
}
