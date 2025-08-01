<?php
// backend/delete_resource.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
  header("Location: login.html");
  exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: resources_admin.php');
    exit();
}

// Fetch resource to get filename
$stmt = $pdo->prepare("SELECT filename FROM resources WHERE id = ?");
$stmt->execute([$id]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$res) {
    header('Location: resources_admin.php');
    exit();
}

$uploadDir = __DIR__ . '/../uploads/resources/';
$filePath = $uploadDir . $res['filename'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
$stmt->execute([$id]);

header('Location: resources_admin.php?deleted=1');
exit(); 