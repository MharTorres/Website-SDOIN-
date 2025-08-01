<?php
require 'db.php'; // adjust path if needed

$page = $_GET['page'] ?? '';
$key = $_GET['key'] ?? '';

$stmt = $pdo->prepare("SELECT value FROM contents WHERE page = ? AND `key` = ?");
$stmt->execute([$page, $key]);

$row = $stmt->fetch();
echo $row ? $row['value'] : 'Content not found.';
?>
