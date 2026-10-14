<?php
// PDO-Verbindung zur Datenbank. In anderen Dateien: require __DIR__ . '/config/db.php';

$host = 'localhost';
$db   = 'forensite';
$user = 'root';
$pass = '';   // XAMPP-Standard: root ohne Passwort

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Fehler als Exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Ergebnisse als Array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // echte Prepared Statements
]);
