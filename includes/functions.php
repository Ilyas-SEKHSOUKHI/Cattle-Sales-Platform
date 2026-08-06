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

function getRaces(): array
{
    return ['Holstein', 'Charolaise', 'Montbeliade'];
}

function getBovins(): array
{
    return [
        'vache' => 'Vache',
        'veau' => 'Veau',
        'velle' => 'Velle',
        'genisse' => 'Génisse',
        'boeuf' => 'Boeuf',
    ];
}

function labelBovin(?string $bovin): string
{
    $labels = getBovins();

    return $labels[$bovin] ?? 'Vache';
}

function uploadVacheImage(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $extensionMap = [
        'jpg' => 'jpg',
        'jpeg' => 'jpg',
        'png' => 'png',
        'webp' => 'webp',
        'gif' => 'gif',
    ];

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $mime = null;

    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
    }

    if ($mime !== null && isset($allowed[$mime])) {
        $extension = $allowed[$mime];
    } elseif (!isset($extensionMap[$extension])) {
        return null;
    } else {
        $extension = $extensionMap[$extension];
    }

    $uploadDir = __DIR__ . '/../uploads/vaches';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        return null;
    }

    @chmod($uploadDir, 0777);

    $filename = uniqid('vache_', true) . '.' . $extension;
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    @chmod($destination, 0644);

    return 'uploads/vaches/' . $filename;
}

function vacheImageUrl(?string $imagePath): ?string
{
    if ($imagePath === null || $imagePath === '') {
        return null;
    }

    return '../' . ltrim($imagePath, '/');
}

function deleteVacheImage(?string $imagePath): void
{
    if ($imagePath === null || $imagePath === '') {
        return;
    }

    $fullPath = __DIR__ . '/../' . ltrim($imagePath, '/');

    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}
