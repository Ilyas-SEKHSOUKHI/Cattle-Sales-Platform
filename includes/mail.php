<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

function sendMailWithFallback(string $to, string $subject, string $message, array $headers = []): bool
{
    require_once __DIR__ . '/../config/smtp.php';

    if (!empty($smtpConfig['enabled']) && !empty($smtpConfig['host'])) {
        $apiKey = $smtpConfig['password'];
        if (empty($apiKey)) {
            return false;
        }

        $mailtrap = MailtrapClient::initSendingEmails(apiKey: $apiKey);

        $email = (new MailtrapEmail())
            ->from(new Address($smtpConfig['from_email'], $smtpConfig['from_name']))
            ->to(new Address($to))
            ->subject($subject)
            ->text($message)
            ->category('Integration Test');

        $response = $mailtrap->send($email);
        $result = ResponseHelper::toArray($response);

        return !empty($result['success']) || !empty($result['id']);
    }

    return mail($to, $subject, $message, implode("\r\n", $headers));
}
