<?php

namespace SensioLabs\JobBoardBundle\EventListener;

use SensioLabs\JobBoardBundle\EmailSender;
use SensioLabs\JobBoardBundle\Event\JobBoardEvents;
use SensioLabs\JobBoardBundle\Event\JobUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobListener implements EventSubscriberInterface
{
    private $emailSender;

    public function __construct(EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [JobBoardEvents::JOB_UPDATE => 'onJobUpdate'];
    }
}
