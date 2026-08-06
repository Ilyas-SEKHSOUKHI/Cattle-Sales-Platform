<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/offres.php');
}

$id_offre = filter_input(INPUT_POST, 'id_offre', FILTER_VALIDATE_INT);

if (!$id_offre) {
    redirect('../admin/offres.php');
}

$offreStmt = $pdo->prepare(
    'SELECT o.id, o.id_vache, o.statut, v.id_admin
     FROM offres o
     JOIN vaches v ON o.id_vache = v.id
     WHERE o.id = :id'
);
$offreStmt->execute([':id' => $id_offre]);
$offre = $offreStmt->fetch(PDO::FETCH_ASSOC);

if (!$offre || (int) $offre['id_admin'] !== (int) $_SESSION['user_id'] || $offre['statut'] !== 'en_attente') {
    redirect('../admin/offres.php');
}

try {
    $pdo->beginTransaction();

    $pdo->prepare('UPDATE offres SET statut = \'acceptee\' WHERE id = :id')
        ->execute([':id' => $id_offre]);

    $pdo->prepare('UPDATE vaches SET statut = \'vendue\' WHERE id = :id')
        ->execute([':id' => $offre['id_vache']]);

    $pdo->prepare(
        'UPDATE offres SET statut = \'refusee\'
         WHERE id_vache = :id_vache AND id != :id AND statut = \'en_attente\''
    )->execute([
        ':id_vache' => $offre['id_vache'],
        ':id' => $id_offre,
    ]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
}

redirect('../admin/offres.php');
