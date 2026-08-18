<?php
    require_once __DIR__ . '/../includes/functions.php';
    loadEnv(__DIR__ . '/../.env');

    // Connexion à MySQL
    $host     = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    $port     = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
    $dbname   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'tarmast_db';
    $user     = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    $password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>