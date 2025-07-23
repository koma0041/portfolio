<?php
$host = 'localhost'; // Keep it as 'localhost'.
$dbname = 'cst8257project'; // This is your database name.
$username = 'root'; // Default username for MySQL.
$password = 'CST8250!'; // Leave it empty if you're using default XAMPP settings.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
