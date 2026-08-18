<?php

require_once __DIR__ . '/../includes/functions.php';
loadEnv(__DIR__ . '/../.env');

$smtpEnabledRaw = $_ENV['SMTP_ENABLED'] ?? getenv('SMTP_ENABLED') ?: 'true';
$smtpEnabled = filter_var($smtpEnabledRaw, FILTER_VALIDATE_BOOLEAN);

$smtpConfig = [
    'enabled'    => $smtpEnabled,
    'host'       => $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'live.smtp.mailtrap.io',
    'port'       => (int)($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587),
    'username'   => $_ENV['SMTP_USER'] ?? getenv('SMTP_USER') ?: 'api',
    'password'   => $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS') ?: '0fbafac4751f830a9a34fed9def3db1d',
    'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? getenv('SMTP_ENCRYPTION') ?: 'tls',
    'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: 'hello@demomailtrap.co',
    'from_name'  => $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'Ferme Tarmast',
];