<?php
// Database connection (PDO). Change these values if your MySQL settings are different.
$host   = 'localhost';
$dbname = 'portal_db';
$user   = 'root';   // XAMPP default
$pass   = '';       // XAMPP default is an empty password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed. Check includes/db.php and make sure MySQL is running.');
}
