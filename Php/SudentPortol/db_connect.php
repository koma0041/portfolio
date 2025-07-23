<?php
function getDbConnection() {
        $config = parse_ini_file("Lab5.ini", true);
    $db = $config['database connection'];
    $dsn = $db['dsn'];
    $username = $db['scriptUser'];
    $password = $db['scriptPassword'];

    try {
        return new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}


?>
