<?php
    if(isset($_POST['register'])){
        // Recuperation des donnee
        $nom = $_POST['nom'];
        $email = $_POST['email'];
        $password = $_POST['mot_de_passe'];

        // Validation du nom
        $nom = filter_input(INPUT_POST,'nom',FILTER_SANITIZE_SPECIAL_CHARS);

        // Validation du mail
        $email = filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);

        // Password Hash
        $hash = password_hash($password,PASSWORD_DEFAULT);

        // Rediriger vers login.php
        //a continuer
    }
?>