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
        $to = isset($context['to']) ? $context['to'] : $this->to;
        $from = isset($context['from']) ? $context['from'] : $this->from;

        $template = $this->twig->loadTemplate($templateName);
        $message = \Swift_Message::newInstance()
            ->setSubject($template->renderBlock('subject', $context))
            ->setFrom($from)
            ->setTo($to)
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
