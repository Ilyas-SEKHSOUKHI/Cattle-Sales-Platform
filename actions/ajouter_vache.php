<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/ajouter_vache.php');
}

$nom = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
$age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
$poids = filter_input(INPUT_POST, 'poids', FILTER_VALIDATE_FLOAT);
$description = trim($_POST['description'] ?? '');
$statut = $_POST['statut'] ?? 'disponible';

if ($nom === '') {
    redirect('../admin/ajouter_vache.php');
}

if (!in_array($statut, ['disponible', 'vendue'], true)) {
    $statut = 'disponible';
}

$stmt = $pdo->prepare(
    'INSERT INTO vaches (nom, age, poids, description, statut, id_admin)
     VALUES (:nom, :age, :poids, :description, :statut, :id_admin)'
);

$stmt->execute([
    ':nom' => $nom,
    ':age' => $age !== false && $age !== null ? $age : null,
    ':poids' => $poids !== false && $poids !== null ? $poids : null,
    ':description' => $description !== '' ? $description : null,
    ':statut' => $statut,
    ':id_admin' => (int) $_SESSION['user_id'],
]);

redirect('../admin/liste_vaches.php');
