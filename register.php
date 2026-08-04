<?php
    //Affiche le formulaire d'inscription
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="">
    <link rel="icon" type="image/x-icon" href="">
    <title>Register</title>
</head>
<body>
    <form action="actions/register.php" method="POST">
        <h2>Register</h2>
        <label>Nom</label>
        <input type="text" name="nom" id="inputNomRegister" required>
        <label>Email</label>
        <input type="email" name="email" id="inputEmailRegister" required>
        <label>Password</label>
        <input type="password" name="mot_de_passe" id="inputPasswordRegister" required>
        <input type="submit" name="register" id="registerSubmit">
    </form>
</body>
</html>