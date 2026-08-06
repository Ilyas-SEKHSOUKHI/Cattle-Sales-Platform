<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/liste_vaches.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nom = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
$age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
$poids = filter_input(INPUT_POST, 'poids', FILTER_VALIDATE_FLOAT);
$description = trim($_POST['description'] ?? '');
$statut = $_POST['statut'] ?? 'disponible';

if (!$id || $nom === '') {
    redirect('../admin/liste_vaches.php');
}

if (!in_array($statut, ['disponible', 'vendue'], true)) {
    $statut = 'disponible';
}

$check = $pdo->prepare('SELECT id FROM vaches WHERE id = :id AND id_admin = :id_admin');
$check->execute([
    ':id' => $id,
    ':id_admin' => (int) $_SESSION['user_id'],
]);

if (!$check->fetch()) {
    redirect('../admin/liste_vaches.php');
}

$stmt = $pdo->prepare(
    'UPDATE vaches
     SET nom = :nom, age = :age, poids = :poids, description = :description, statut = :statut
     WHERE id = :id AND id_admin = :id_admin'
);

$stmt->execute([
    ':nom' => $nom,
    ':age' => $age !== false && $age !== null ? $age : null,
    ':poids' => $poids !== false && $poids !== null ? $poids : null,
    ':description' => $description !== '' ? $description : null,
    ':statut' => $statut,
    ':id' => $id,
    ':id_admin' => (int) $_SESSION['user_id'],
]);

redirect('../admin/liste_vaches.php');
