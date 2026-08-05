<?php
/**
 * actions/logout.php
 * -------------------------------------------------
 * Déconnexion de l'utilisateur : destruction de la session
 * et redirection vers la page de connexion.
 * -------------------------------------------------
 */

require_once __DIR__ . '/../includes/session.php';

// Vide toutes les variables de session
$_SESSION = [];

// Supprime le cookie de session côté navigateur (si utilisé)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Détruit la session côté serveur
session_destroy();

// Redirige vers la page de connexion
header('Location: ../login.php');
exit;