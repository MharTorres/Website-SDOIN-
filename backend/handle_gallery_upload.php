<?php
// backend/handle_gallery_upload.php
session_start();
require_once 'db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gallery_upload.php');
    exit();
}

// Validate form fields
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$theme = isset($_POST['theme']) ? trim($_POST['theme']) : null;
if ($category !== 'initiatives') {
    $theme = null;
}

if (!$title || !$category || !isset($_FILES['image'])) {
    header('Location: gallery_upload.php?error=' . urlencode('Missing required fields.'));
    exit();
}

// File upload settings
$uploadDir = __DIR__ . '/../uploads/gallery/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['image'];
$allowedTypes = [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif'
];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: gallery_upload.php?error=' . urlencode('File upload error.'));
    exit();
}

if (!in_array($file['type'], $allowedTypes)) {
    header('Location: gallery_upload.php?error=' . urlencode('Invalid file type. Only JPG, PNG, and GIF are allowed.'));
    exit();
}

if ($file['size'] > $maxSize) {
    header('Location: gallery_upload.php?error=' . urlencode('File is too large. Maximum size is 5MB.'));
    exit();
}

// Generate a unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$basename = bin2hex(random_bytes(8));
$filename = $basename . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    header('Location: gallery_upload.php?error=' . urlencode('Failed to move uploaded file.'));
    exit();
}

// Insert into database
try {
    $stmt = $pdo->prepare("INSERT INTO gallery (title, description, filename, category, theme, filetype, filesize, uploaded_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $title,
        $description,
        $filename,
        $category,
        $theme,
        $file['type'],
        $file['size']
    ]);

    header('Location: gallery_upload.php?success=' . urlencode('Image uploaded successfully!'));
    exit();
} catch (PDOException $e) {
    // Delete uploaded file if database insert fails
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    header('Location: gallery_upload.php?error=' . urlencode('Database error. Please try again.'));
    exit();
}
?> 