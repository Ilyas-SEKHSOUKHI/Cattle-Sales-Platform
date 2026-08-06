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
$bovin = $_POST['bovin'] ?? 'vache';
$dateNaissance = parseDateNaissance(trim($_POST['date_naissance'] ?? ''));
$poids = filter_input(INPUT_POST, 'poids', FILTER_VALIDATE_FLOAT);
$description = trim($_POST['description'] ?? '');
$statut = $_POST['statut'] ?? 'disponible';

if (!$id || $nom === '' || !in_array($nom, getRaces(), true) || $dateNaissance === null) {
    redirect('../admin/liste_vaches.php');
}

if (!array_key_exists($bovin, getBovins())) {
    $bovin = 'vache';
}

if (!in_array($statut, ['disponible', 'vendue'], true)) {
    $statut = 'disponible';
}

$check = $pdo->prepare('SELECT id, image FROM vaches WHERE id = :id AND id_admin = :id_admin');
$check->execute([
    ':id' => $id,
    ':id_admin' => (int) $_SESSION['user_id'],
]);

$existing = $check->fetch(PDO::FETCH_ASSOC);

if (!$existing) {
    redirect('../admin/liste_vaches.php');
}

$age = calculateAgeFromBirthDate($dateNaissance);
$image = $existing['image'];

if (!empty($_FILES['image']['name'])) {
    $newImage = uploadVacheImage($_FILES['image']);

    if ($newImage !== null) {
        deleteVacheImage($image);
        $image = $newImage;
    }
}

$stmt = $pdo->prepare(
    'UPDATE vaches
     SET nom = :nom, bovin = :bovin, date_naissance = :date_naissance, age = :age, poids = :poids, description = :description, image = :image, statut = :statut
     WHERE id = :id AND id_admin = :id_admin'
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
    ':id' => $id,
    ':id_admin' => (int) $_SESSION['user_id'],
]);

redirect('../admin/liste_vaches.php');
