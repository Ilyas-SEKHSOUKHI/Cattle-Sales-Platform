<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAcheteur();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../client/accueil.php');
}

$id_vache = filter_input(INPUT_POST, 'id_vache', FILTER_VALIDATE_INT);
$montant = filter_input(INPUT_POST, 'montant_propose', FILTER_VALIDATE_FLOAT);

if (!$id_vache || !$montant || $montant <= 0) {
    redirect('../client/details_vache.php?id=' . (int) $id_vache);
}

$vacheStmt = $pdo->prepare('SELECT id, statut FROM vaches WHERE id = :id');
$vacheStmt->execute([':id' => $id_vache]);
$vache = $vacheStmt->fetch(PDO::FETCH_ASSOC);

if (!$vache || $vache['statut'] !== 'disponible') {
    redirect('../client/accueil.php');
}

$existingStmt = $pdo->prepare(
    'SELECT id, statut FROM offres
     WHERE id_utilisateur = :id_utilisateur AND id_vache = :id_vache
     ORDER BY date_offre DESC
     LIMIT 1'
);
$existingStmt->execute([
    ':id_utilisateur' => (int) $_SESSION['user_id'],
    ':id_vache' => $id_vache,
]);
$existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

if ($existing && in_array($existing['statut'], ['en_attente', 'acceptee'], true)) {
    redirect('../client/details_vache.php?id=' . $id_vache);
}

$stmt = $pdo->prepare(
    'INSERT INTO offres (montant_propose, id_utilisateur, id_vache)
     VALUES (:montant_propose, :id_utilisateur, :id_vache)'
);
$stmt->execute([
    ':montant_propose' => $montant,
    ':id_utilisateur' => (int) $_SESSION['user_id'],
    ':id_vache' => $id_vache,
]);

redirect('../client/details_vache.php?id=' . $id_vache);
