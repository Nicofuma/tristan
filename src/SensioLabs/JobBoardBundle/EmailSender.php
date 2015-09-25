<?php

namespace SensioLabs\JobBoardBundle;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class EmailSender
{
    /** @var \Swift_Mailer */
    private $mailer;

    /** @var \Twig_Environment */
    private $twig;

    /** @var LoggerInterface */
    private $logger;

    private $from;
    private $to;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, $from, $to, LoggerInterface $logger = null)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger ?: new NullLogger();
        $this->from = $from;
        $this->to = $to;
    }

    public function send($templateName, array $context = [])
    {
        $template = $this->twig->loadTemplate($templateName);
        $message = \Swift_Message::newInstance()
            ->setSubject($template->renderBlock('subject', $context))
            ->setFrom($this->from)
            ->setTo($this->to)
            ->setBody($template->renderBlock('body_html', $context), 'text/html')
            ->addPart($template->renderBlock('body_txt', $context), 'text/plain')
        ;

        $failedRecipients = [];
        try {
            $this->mailer->send($message, $failedRecipients);
        } catch (\Swift_SwiftException $e) {
            $this->logger->error('Exception occurred while sending email', ['exception' => $e]);
        }

        if (count($failedRecipients) > 0) {
            $this->logger->warning('Cannot deliver email to all recipients.', ['failedRecipients' => $failedRecipients]);
        }
    }
}
