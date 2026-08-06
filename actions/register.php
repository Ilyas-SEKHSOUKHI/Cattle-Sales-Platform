<?php
require '../config/database.php';

if (isset($_POST['register'])) {

    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // --- Validation ---
    $errors = [];

    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (!$email) {
        $errors[] = "L'email n'est pas valide.";
    }
    if (strlen($mot_de_passe) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    if (!empty($errors)) {
        foreach ($errors as $e) {
            echo $e . "<br>";
        }
        exit;
    }

    // --- Vérifier que l'email n'existe pas déjà ---
    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        echo "Cet email est déjà utilisé.";
        exit;
    }

    // --- Hash du mot de passe ---
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // --- Insertion ---
    // Le rôle n'est PAS pris depuis le formulaire.
    // La colonne "role" a un DEFAULT 'acheteur' dans la table,
    // donc on ne l'insère pas du tout ici : MySQL s'en charge.
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (:nom, :email, :mot_de_passe)");

    try {
        $stmt->execute([
            ':nom' => $nom,
            ':email' => $email,
            ':mot_de_passe' => $hash,
        ]);
        header('Location: ../login.php');
        exit;
    } catch (PDOException $e) {
        echo "Erreur lors de l'inscription : " . $e->getMessage();
    }
}