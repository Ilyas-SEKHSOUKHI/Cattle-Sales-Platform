<?php

$smtpConfig = [
    'enabled' => false,
    'host' => getenv('SMTP_HOST') ?: '',
    'port' => (int) (getenv('SMTP_PORT') ?: 587),
    'username' => getenv('SMTP_USERNAME') ?: '',
    'password' => getenv('SMTP_PASSWORD') ?: '',
    'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
    'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'no-reply@fermetarmast.local',
    'from_name' => getenv('SMTP_FROM_NAME') ?: 'Ferme Tarmast',
];

if (!empty($smtpConfig['host']) && !empty($smtpConfig['username']) && !empty($smtpConfig['password'])) {
    $smtpConfig['enabled'] = true;
}
