<?php
    //Affiche le formulaire de connexion
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="">
    <title>Login</title>
</head>
<body>
    <form action="actions/login.php" method="POST">
        <h2>Connexion</h2>
        <label>Email</label>
        <input type="text" name="email" id="inputEmailLogin" required><br>
        <label>Password</label>
        <input type="password" name="mot_de_passe" id="inputPasswordLogin" required><br>
        <input type="submit" name="login" id="loginSubmit">
        <a href="register.php">Register</a>
    </form>
</body>
</html>