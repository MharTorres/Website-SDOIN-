<?php
session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
  header("Location: login.html");
  exit();
}
require 'db.php';

foreach ($_POST['content'] as $key => $value) {
  $stmt = $pdo->prepare("UPDATE contents SET value = ? WHERE `key` = ?");
  $stmt->execute([$value, $key]);
}

header("Location: admin.php");
?>
