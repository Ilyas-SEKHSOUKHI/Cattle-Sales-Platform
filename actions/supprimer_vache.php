<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/liste_vaches.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect('../admin/liste_vaches.php');
}

$check = $pdo->prepare('SELECT id FROM vaches WHERE id = :id AND id_admin = :id_admin');
$check->execute([
    ':id' => $id,
    ':id_admin' => (int) $_SESSION['user_id'],
]);

if (!$check->fetch()) {
    redirect('../admin/liste_vaches.php');
}

$pdo->prepare('DELETE FROM offres WHERE id_vache = :id')->execute([':id' => $id]);
$pdo->prepare('DELETE FROM vaches WHERE id = :id AND id_admin = :id_admin')->execute([
    ':id' => $id,
    ':id_admin' => (int) $_SESSION['user_id'],
]);

redirect('../admin/liste_vaches.php');
