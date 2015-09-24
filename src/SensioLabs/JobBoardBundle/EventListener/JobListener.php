<?php

namespace SensioLabs\JobBoardBundle\EventListener;

use SensioLabs\JobBoardBundle\EmailSender;
use SensioLabs\JobBoardBundle\Event\JobBoardEvents;
use SensioLabs\JobBoardBundle\Event\JobsDisplayedEvent;
use SensioLabs\JobBoardBundle\Event\JobUpdatedEvent;
use SensioLabs\JobBoardBundle\Repository\JobRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobListener implements EventSubscriberInterface
{
    private $emailSender;
    private $repository;

    public function __construct(EmailSender $emailSender, JobRepository $repository)
    {
        $this->emailSender = $emailSender;
        $this->repository = $repository;
    }

    public function onJobUpdate(JobUpdatedEvent $event)
    {
        if ($event->getOldJob()->isValidated()) {
            $this->emailSender->send(
                '@SensioLabsJobBoard/Mail/updateNotification.html.twig',
                [
                    'old_job' => $event->getOldJob(),
                    'new_job' => $event->getNewJob(),
                ]
            );
        }
    }

    public function onJobDisplayed(JobsDisplayedEvent $event)
    {
        $this->repository->view($event->getJobs(), $event->getLocation());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            JobBoardEvents::JOB_UPDATE => 'onJobUpdate',
            JobBoardEvents::JOB_DISPLAYED => 'onJobDisplayed',
        ];
    }
}
