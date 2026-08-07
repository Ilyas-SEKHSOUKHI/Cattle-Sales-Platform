<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

require __DIR__ . '/vendor/autoload.php';

$apiKey = '0fbafac4751f830a9a34fed9def3db1d';
$mailtrap = MailtrapClient::initSendingEmails(apiKey: $apiKey);

$email = (new MailtrapEmail())
    ->from(new Address('hello@demomailtrap.co', 'Mailtrap Test'))
    ->to(new Address('sekhsoukhiilyas@gmail.com'))
    ->subject('Test email depuis Ferme Tarmast')
    ->text('Bonjour, ceci est un test d’envoi avec Mailtrap depuis votre projet.')
    ->category('Integration Test');

$response = $mailtrap->send($email);

print_r(ResponseHelper::toArray($response));
