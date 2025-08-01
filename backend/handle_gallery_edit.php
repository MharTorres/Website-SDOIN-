<?php
// backend/handle_gallery_edit.php
session_start();
require_once 'db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gallery_admin.php');
    exit();
}

// Validate form fields
$image_id = $_POST['image_id'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$theme = isset($_POST['theme']) ? trim($_POST['theme']) : null;
if ($category !== 'initiatives') {
    $theme = null;
}

if (!$image_id || !$title || !$category) {
    header('Location: edit_gallery.php?id=' . $image_id . '&error=' . urlencode('Missing required fields.'));
    exit();
}

// Fetch current image data
try {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$image_id]);
    $currentImage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentImage) {
        header('Location: gallery_admin.php?error=' . urlencode('Image not found.'));
        exit();
    }
} catch (PDOException $e) {
    header('Location: gallery_admin.php?error=' . urlencode('Database error.'));
    exit();
}

// Handle new image upload if provided
$newFilename = $currentImage['filename']; // Keep current filename by default
$newFiletype = $currentImage['filetype'];
$newFilesize = $currentImage['filesize'];

if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/gallery/';
    $file = $_FILES['new_image'];
    
    $allowedTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif'
    ];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        header('Location: edit_gallery.php?id=' . $image_id . '&error=' . urlencode('Invalid file type. Only JPG, PNG, and GIF are allowed.'));
        exit();
    }

    if ($file['size'] > $maxSize) {
        header('Location: edit_gallery.php?id=' . $image_id . '&error=' . urlencode('File is too large. Maximum size is 5MB.'));
        exit();
    }

    // Generate new filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $newFilename = $basename . '.' . $ext;
    $targetPath = $uploadDir . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        header('Location: edit_gallery.php?id=' . $image_id . '&error=' . urlencode('Failed to move uploaded file.'));
        exit();
    }

    // Delete old file if it's different
    if ($newFilename !== $currentImage['filename']) {
        $oldFilePath = $uploadDir . $currentImage['filename'];
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
    }

    $newFiletype = $file['type'];
    $newFilesize = $file['size'];
}

// Update database
try {
    $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, filename = ?, category = ?, theme = ?, filetype = ?, filesize = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([
        $title,
        $description,
        $newFilename,
        $category,
        $theme,
        $newFiletype,
        $newFilesize,
        $image_id
    ]);

    header('Location: gallery_admin.php?updated=1');
    exit();
} catch (PDOException $e) {
    // Delete new file if database update fails
    if ($newFilename !== $currentImage['filename']) {
        $newFilePath = __DIR__ . '/../uploads/gallery/' . $newFilename;
        if (file_exists($newFilePath)) {
            unlink($newFilePath);
        }
    }
    header('Location: edit_gallery.php?id=' . $image_id . '&error=' . urlencode('Database error. Please try again.'));
    exit();
}
?> 