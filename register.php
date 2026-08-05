<?php
    //Affiche le formulaire d'inscription
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="">
    <title>Register</title>
</head>
<body>
    <form action="actions/register.php" method="POST">
        <h2>Inscription</h2>
        <label>Nom</label>
        <input type="text" name="nom" id="inputNomRegister" required><br>
        <label>Email</label>
        <input type="email" name="email" id="inputEmailRegister" required><br>
        <label>Password</label>
        <input type="password" name="mot_de_passe" id="inputPasswordRegister" required><br>
        <input type="submit" name="register" id="registerSubmit">
        <a href="login.php">Login</a>
    </form>
</body>
</html>