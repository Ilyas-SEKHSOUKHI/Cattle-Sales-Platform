<?php

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function requireLogin(string $loginPath = '../login.php'): void
{
    if (empty($_SESSION['user_id'])) {
        redirect($loginPath);
    }
}

function requireAdmin(string $loginPath = '../login.php', string $fallback = '../client/accueil.php'): void
{
    requireLogin($loginPath);

    if (($_SESSION['role'] ?? '') !== 'admin') {
        redirect($fallback);
    }
}

function requireAcheteur(string $loginPath = '../login.php', string $fallback = '../admin/dashboard.php'): void
{
    requireLogin($loginPath);

    if (($_SESSION['role'] ?? '') !== 'acheteur') {
        redirect($fallback);
    }
}
