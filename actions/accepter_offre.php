<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

ensureColumnExists($pdo, 'offres', 'date_reprise', 'DATE NULL');
ensureColumnExists($pdo, 'vaches', 'date_reprise', 'DATE NULL');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/offres.php');
}

$id_offre = filter_input(INPUT_POST, 'id_offre', FILTER_VALIDATE_INT);
$date_reprise = filter_input(INPUT_POST, 'date_reprise', FILTER_SANITIZE_STRING);

if (!$id_offre) {
    redirect('../admin/offres.php');
}

if (empty($date_reprise)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Veuillez choisir une date de récupération avant d’accepter l’offre.'];
    redirect('../admin/offres.php');
}

$dateRepriseObj = DateTime::createFromFormat('Y-m-d', $date_reprise);
if (!$dateRepriseObj || $dateRepriseObj->format('Y-m-d') !== $date_reprise) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'La date de récupération est invalide.'];
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

    $pdo->prepare('UPDATE offres SET statut = \'acceptee\', date_reprise = :date_reprise WHERE id = :id')
        ->execute([
            ':date_reprise' => $date_reprise,
            ':id' => $id_offre,
        ]);

    $pdo->prepare('UPDATE vaches SET statut = \'vendue\', date_reprise = :date_reprise WHERE id = :id')
        ->execute([
            ':date_reprise' => $date_reprise,
            ':id' => $offre['id_vache'],
        ]);

    $pdo->prepare(
        'UPDATE offres SET statut = \'refusee\'
         WHERE id_vache = :id_vache AND id != :id AND statut = \'en_attente\''
    )->execute([
        ':id_vache' => $offre['id_vache'],
        ':id' => $id_offre,
    ]);

    $pdo->commit();
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Offre acceptée. La date de récupération a été enregistrée.'];
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Impossible d’accepter l’offre pour le moment.'];
}

redirect('../admin/offres.php');
