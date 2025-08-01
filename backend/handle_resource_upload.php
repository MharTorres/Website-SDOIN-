<?php
// backend/handle_resource_upload.php
session_start();
require_once 'db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: resources_upload.php');
    exit();
}

// Validate form fields
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');

if (!$title || !$category || !isset($_FILES['file'])) {
    die('Missing required fields.');
}

// File upload settings
$uploadDir = __DIR__ . '/../uploads/resources/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['file'];
$allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'text/plain',
];
$maxSize = 10 * 1024 * 1024; // 10MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    die('File upload error.');
}
if (!in_array($file['type'], $allowedTypes)) {
    die('Invalid file type.');
}
if ($file['size'] > $maxSize) {
    die('File is too large.');
}

// Generate a unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$basename = bin2hex(random_bytes(8));
$filename = $basename . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    die('Failed to move uploaded file.');
}

// Insert into database
$stmt = $pdo->prepare("INSERT INTO resources (title, description, filename, category, filetype, filesize, uploaded_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
$stmt->execute([
    $title,
    $description,
    $filename,
    $category,
    $file['type'],
    $file['size']
]);

header('Location: resources_upload.php?success=1');
exit(); 