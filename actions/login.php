<?php
// Vérifie les identifiants de connexion
session_start();
require '../config/database.php';

if (isset($_POST['login'])) {

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // --- Validation basique ---
    if (!$email || empty($mot_de_passe)) {
        echo "Email ou mot de passe invalide.";
        exit;
    }

    // --- Récupérer l'utilisateur par email ---
    $stmt = $pdo->prepare("SELECT id, nom, email, mot_de_passe, role FROM utilisateurs WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- Vérifier que l'utilisateur existe ET que le mot de passe correspond ---
    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {

        // Ne jamais garder le hash en session
        $_SESSION['user_id'] = $utilisateur['id'];
        $_SESSION['nom'] = $utilisateur['nom'];
        $_SESSION['role'] = $utilisateur['role'];

        // Redirection selon le rôle
        if ($utilisateur['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../client/accueil.php');
        }
        exit;

    } else {
        // Message volontairement générique : ne pas dire si c'est l'email
        // ou le mot de passe qui est faux (évite de révéler quels emails existent)
        echo "Email ou mot de passe incorrect.";
    }
}