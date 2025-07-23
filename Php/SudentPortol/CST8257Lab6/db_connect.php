<?php
function getDbConnection() {
    $config = parse_ini_file("Lab5.ini", true);
    $db = $config['database'];
    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8";

    try {
        return new PDO($dsn, $db['username'], $db['password']);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
