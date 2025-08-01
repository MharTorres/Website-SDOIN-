<?php
// backend/delete_gallery.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
  header("Location: login.html");
  exit();
}

// Only allow GET requests with ID parameter
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    header('Location: gallery_admin.php?error=' . urlencode('Invalid request.'));
    exit();
}

$image_id = $_GET['id'];

// Fetch image data
try {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$image_id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$image) {
        header('Location: gallery_admin.php?error=' . urlencode('Image not found.'));
        exit();
    }
} catch (PDOException $e) {
    header('Location: gallery_admin.php?error=' . urlencode('Database error.'));
    exit();
}

// Delete file from server
$filePath = __DIR__ . '/../uploads/gallery/' . $image['filename'];
if (file_exists($filePath)) {
    if (!unlink($filePath)) {
        header('Location: gallery_admin.php?error=' . urlencode('Failed to delete file from server.'));
        exit();
    }
}

// Delete from database
try {
    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$image_id]);
    
    header('Location: gallery_admin.php?success=' . urlencode('Image deleted successfully.'));
    exit();
} catch (PDOException $e) {
    header('Location: gallery_admin.php?error=' . urlencode('Database error. Please try again.'));
    exit();
}
?> 