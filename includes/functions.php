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

function telephoneDigits(?string $telephone): string
{
    return preg_replace('/\D/', '', $telephone ?? '');
}

function parseDateNaissance(string $input): ?string
{
    $date = DateTime::createFromFormat('Y-m-d', $input);

    if (!$date || $date->format('Y-m-d') !== $input) {
        return null;
    }

    $today = new DateTime('today');

    if ($date > $today) {
        return null;
    }

    return $input;
}

function calculateAgeFromBirthDate(?string $dateNaissance): ?int
{
    if ($dateNaissance === null || $dateNaissance === '') {
        return null;
    }

    try {
        $birth = new DateTime($dateNaissance);
        $today = new DateTime('today');

        if ($birth > $today) {
            return null;
        }

        return (int) $birth->diff($today)->y;
    } catch (Exception $e) {
        return null;
    }
}

function vacheAge(?string $dateNaissance, ?int $storedAge = null): ?int
{
    $calculated = calculateAgeFromBirthDate($dateNaissance);

    if ($calculated !== null) {
        return $calculated;
    }

    return $storedAge;
}

function vacheAgeFormatted(?string $dateNaissance, ?int $storedAge = null): string
{
    if (!empty($dateNaissance)) {
        try {
            $birth = new DateTime($dateNaissance);
            $today = new DateTime('today');

            if ($birth <= $today) {
                $diff = $birth->diff($today);
                $years  = $diff->y;
                $months = $diff->m;
                $days   = $diff->d;

                $parts = [];
                if ($years > 0) {
                    $parts[] = $years . ' an' . ($years > 1 ? 's' : '');
                    if ($months > 0) {
                        $parts[] = $months . ' mois';
                    }
                } elseif ($months > 0) {
                    $parts[] = $months . ' mois';
                    if ($days > 0) {
                        $parts[] = $days . ' jour' . ($days > 1 ? 's' : '');
                    }
                } else {
                    $parts[] = $days . ' jour' . ($days > 1 ? 's' : '');
                }

                return implode(' ', $parts);
            }
        } catch (Exception $e) {
            // fallback
        }
    }

    if ($storedAge !== null && $storedAge >= 0) {
        return $storedAge . ' an' . ($storedAge > 1 ? 's' : '');
    }

    return 'Non renseigné';
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

function tableHasColumn(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
    $columns = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    $exists = in_array($column, $columns, true);
    $cache[$key] = $exists;

    return $exists;
}

function ensureColumnExists(PDO $pdo, string $table, string $column, string $definition): bool
{
    if (tableHasColumn($pdo, $table, $column)) {
        return true;
    }

    $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");

    return tableHasColumn($pdo, $table, $column);
}

/**
 * Convertit un montant numérique en toutes lettres en français (Dirhams & Centimes)
 */
function nombreEnLettres(float $montant): string
{
    if ($montant < 0) {
        return 'moins ' . nombreEnLettres(abs($montant));
    }

    $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
    $dizaines = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];

    $convertChunk = function (int $n) use (&$convertChunk, $unites, $dizaines): string {
        if ($n === 0) return '';
        if ($n < 20) return $unites[$n];
        if ($n < 100) {
            $d = (int) ($n / 10);
            $r = $n % 10;
            if ($d === 7) {
                return 'soixante' . ($r === 1 ? ' et onze' : '-' . $unites[10 + $r]);
            }
            if ($d === 9) {
                return 'quatre-vingt' . '-' . $unites[10 + $r];
            }
            if ($d === 8 && $r === 0) {
                return 'quatre-vingts';
            }
            return $dizaines[$d] . ($r === 1 ? ' et un' : ($r > 0 ? '-' . $unites[$r] : ''));
        }
        if ($n < 1000) {
            $c = (int) ($n / 100);
            $r = $n % 100;
            $prefix = ($c === 1) ? 'cent' : $unites[$c] . ' cent';
            if ($c > 1 && $r === 0) $prefix .= 's';
            return $prefix . ($r > 0 ? ' ' . $convertChunk($r) : '');
        }
        return '';
    };

    $convertNumber = function (int $num) use ($convertChunk): string {
        if ($num === 0) return 'zéro';
        $res = '';

        if ($num >= 1000000) {
            $m = (int) ($num / 1000000);
            $num %= 1000000;
            $res .= ($m === 1 ? 'un million' : $convertChunk($m) . ' millions') . ' ';
        }

        if ($num >= 1000) {
            $k = (int) ($num / 1000);
            $num %= 1000;
            $res .= ($k === 1 ? 'mille' : $convertChunk($k) . ' mille') . ' ';
        }

        if ($num > 0) {
            $res .= $convertChunk($num);
        }

        return trim($res);
    };

    $dirhams = (int) floor($montant);
    $centimes = (int) round(($montant - $dirhams) * 100);

    $strDirhams = $convertNumber($dirhams);
    $text = ucfirst($strDirhams) . ' ' . ($dirhams <= 1 ? 'Dirham' : 'Dirhams');

    if ($centimes > 0) {
        $strCentimes = $convertNumber($centimes);
        $text .= ' et ' . $strCentimes . ' ' . ($centimes <= 1 ? 'Centime' : 'Centimes');
    }

    return $text;
}

/**
 * Assure la création de la table factures si elle n'existe pas
 */
function ensureFacturesTableExists(PDO $pdo): void
{
    static $created = false;
    if ($created) return;

    $sql = "CREATE TABLE IF NOT EXISTS factures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero_facture VARCHAR(30) UNIQUE NOT NULL,
        id_offre INT NOT NULL UNIQUE,
        id_utilisateur INT NOT NULL,
        id_vache INT NOT NULL,
        montant_ht DECIMAL(10,2) NOT NULL,
        montant_ttc DECIMAL(10,2) NOT NULL,
        tva_taux DECIMAL(5,2) DEFAULT 20.00,
        date_facture DATETIME DEFAULT CURRENT_TIMESTAMP,
        statut ENUM('payee', 'annulee') DEFAULT 'payee',
        FOREIGN KEY (id_offre) REFERENCES offres(id),
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
        FOREIGN KEY (id_vache) REFERENCES vaches(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    $created = true;
}

/**
 * Re-numérote de manière strictement séquentielle toutes les factures existantes
 */
function resyncSequentialInvoiceNumbers(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT id, date_facture FROM factures ORDER BY date_facture ASC, id ASC");
    $factures = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $countsByYear = [];
    $updateStmt = $pdo->prepare("UPDATE factures SET numero_facture = :num WHERE id = :id");

    foreach ($factures as $fact) {
        $year = date('Y', strtotime($fact['date_facture']));
        if (!isset($countsByYear[$year])) {
            $countsByYear[$year] = 1;
        } else {
            $countsByYear[$year]++;
        }

        $numFacture = 'FACT-' . $year . '-' . str_pad((string)$countsByYear[$year], 4, '0', STR_PAD_LEFT);
        $updateStmt->execute([':num' => $numFacture, ':id' => $fact['id']]);
    }
}

/**
 * Génère ou récupère une facture pour une offre acceptée
 */
function generateInvoiceForOffre(PDO $pdo, int $idOffre): ?array
{
    ensureFacturesTableExists($pdo);

    // Vérifier si la facture existe déjà
    $stmt = $pdo->prepare("SELECT * FROM factures WHERE id_offre = :id_offre");
    $stmt->execute([':id_offre' => $idOffre]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($facture) {
        return $facture;
    }

    // Récupérer l'offre acceptée
    $stmtOffre = $pdo->prepare("SELECT id, montant_propose, id_utilisateur, id_vache, date_offre FROM offres WHERE id = :id AND statut = 'acceptee'");
    $stmtOffre->execute([':id' => $idOffre]);
    $offre = $stmtOffre->fetch(PDO::FETCH_ASSOC);

    if (!$offre) {
        return null;
    }

    $year = date('Y', strtotime($offre['date_offre'] ?? 'now'));
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM factures WHERE numero_facture LIKE :prefix");
    $countStmt->execute([':prefix' => 'FACT-' . $year . '-%']);
    $nextSeq = (int)$countStmt->fetchColumn() + 1;
    $numFacture = 'FACT-' . $year . '-' . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

    $montantTTC = (float) $offre['montant_propose'];
    $montantHT  = round($montantTTC / 1.20, 2);
    $dateFacture = $offre['date_offre'] ?? date('Y-m-d H:i:s');

    $insertStmt = $pdo->prepare("INSERT INTO factures (numero_facture, id_offre, id_utilisateur, id_vache, montant_ht, montant_ttc, tva_taux, date_facture, statut) VALUES (:num, :id_offre, :id_user, :id_vache, :ht, :ttc, 20.00, :date_f, 'payee')");
    $insertStmt->execute([
        ':num' => $numFacture,
        ':id_offre' => $idOffre,
        ':id_user' => $offre['id_utilisateur'],
        ':id_vache' => $offre['id_vache'],
        ':ht' => $montantHT,
        ':ttc' => $montantTTC,
        ':date_f' => $dateFacture,
    ]);

    $stmt->execute([':id_offre' => $idOffre]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Synchronise les factures pour toutes les offres acceptées n'ayant pas encore de facture enregistrée
 */
function syncAllFactures(PDO $pdo): void
{
    ensureFacturesTableExists($pdo);

    $stmt = $pdo->query("SELECT id FROM offres WHERE statut = 'acceptee' AND id NOT IN (SELECT id_offre FROM factures) ORDER BY date_offre ASC, id ASC");
    $offres = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

    foreach ($offres as $idOffre) {
        generateInvoiceForOffre($pdo, (int)$idOffre);
    }

    resyncSequentialInvoiceNumbers($pdo);
}


