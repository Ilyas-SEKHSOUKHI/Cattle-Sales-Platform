<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/ajouter_vache.php');
}

$nom = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
$bovin = $_POST['bovin'] ?? 'vache';
$dateNaissance = parseDateNaissance(trim($_POST['date_naissance'] ?? ''));
$poids = filter_input(INPUT_POST, 'poids', FILTER_VALIDATE_FLOAT);
$description = trim($_POST['description'] ?? '');
$statut = $_POST['statut'] ?? 'disponible';

if ($nom === '' || !in_array($nom, getRaces(), true) || $dateNaissance === null) {
    redirect('../admin/ajouter_vache.php');
}

if (!array_key_exists($bovin, getBovins())) {
    $bovin = 'vache';
}

if (!in_array($statut, ['disponible', 'vendue'], true)) {
    $statut = 'disponible';
}

$age = calculateAgeFromBirthDate($dateNaissance);
$image = uploadVacheImage($_FILES['image'] ?? []);

$stmt = $pdo->prepare(
    'INSERT INTO vaches (nom, bovin, date_naissance, age, poids, description, image, statut, id_admin)
     VALUES (:nom, :bovin, :date_naissance, :age, :poids, :description, :image, :statut, :id_admin)'
);

$stmt->execute([
    ':nom' => $nom,
    ':bovin' => $bovin,
    ':date_naissance' => $dateNaissance,
    ':age' => $age,
    ':poids' => $poids !== false && $poids !== null ? $poids : null,
    ':description' => $description !== '' ? $description : null,
    ':image' => $image,
    ':statut' => $statut,
    ':id_admin' => (int) $_SESSION['user_id'],
]);

redirect('../admin/liste_vaches.php');
