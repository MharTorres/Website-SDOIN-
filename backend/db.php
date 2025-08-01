<?php
$host = 'localhost';
$db = 'sdoin'; // your database name
$user = 'root'; // your DB username (default in XAMPP)
$pass = 'soulgain15';     // your DB password (empty by default in XAMPP)

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>
