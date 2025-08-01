<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && hash('sha256', $password) === $user['password']) {
    $_SESSION['admin'] = $user['username'];
    header("Location: admin.php");
    exit();
  } else {
    echo "Invalid credentials";
  }
}
?>
