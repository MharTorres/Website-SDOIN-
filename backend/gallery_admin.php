<?php
// backend/gallery_admin.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
  header("Location: login.html");
  exit();
}

// Get success/error messages from URL
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$updated_message = isset($_GET['updated']) ? $_GET['updated'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';

// Fetch all gallery images
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY uploaded_at DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalImages = count($images);
$totalSize = array_sum(array_column($images, 'filesize'));
$categories = array_unique(array_column($images, 'category'));
$totalCategories = count($categories);
$themes = array_unique(array_filter(array_column($images, 'theme')));

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Function to get category icon
function getCategoryIcon($category) {
    $icons = [
        'programs' => 'fas fa-graduation-cap',
        'initiatives' => 'fas fa-lightbulb',
        'events' => 'fas fa-calendar-alt',
        'sports' => 'fas fa-running'
    ];
    return $icons[strtolower($category)] ?? 'fas fa-image';
}

// Function to get category color
function getCategoryColor($category) {
    $colors = [
        'programs' => '#e83e8c',
        'initiatives' => '#fd7e14',
        'events' => '#20c997',
        'sports' => '#0dcaf0'
    ];
    return $colors[strtolower($category)] ?? '#6c757d';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gallery Management - SDOIN Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    :root {
      --primary-color: #2575fc;
      --secondary-color: #6a11cb;
      --success-color: #198754;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
      --info-color: #0dcaf0;
      --dark-color: #212529;
      --light-color: #f8f9fa;
    }

    body {
      background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 1rem 0;
    }

    .admin-container {
      max-width: 1400px;
      margin: 0 auto;
    }

    .header-bar {
      text-align: center;
      color: white;
      margin-bottom: 2rem;
    }

    .header-bar h1 {
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .header-bar p {
      font-size: 1.1rem;
      opacity: 0.9;
      margin-bottom: 0;
    }

    .breadcrumb {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 25px;
      padding: 0.5rem 1rem;
      margin-bottom: 2rem;
    }

    .breadcrumb-item a {
      color: white;
      text-decoration: none;
      font-weight: 500;
    }

    .breadcrumb-item.active {
      color: rgba(255, 255, 255, 0.8);
    }

    .admin-panel {
      background: #fff;
      padding: 2.5rem;
      border-radius: 20px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
    }

    .admin-panel::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 1.5rem;
      border-radius: 12px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      transform: rotate(45deg);
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      position: relative;
      z-index: 1;
    }

    .stat-label {
      font-size: 0.9rem;
      opacity: 0.9;
      position: relative;
      z-index: 1;
    }

    .action-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2rem;
      padding: 1.5rem;
      background: var(--light-color);
      border-radius: 12px;
      border-left: 4px solid var(--primary-color);
    }

    .btn {
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-success {
      background: linear-gradient(135deg, var(--success-color), #20c997);
      color: white;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
    }

    .btn-warning {
      background: linear-gradient(135deg, var(--warning-color), #fd7e14);
      color: white;
    }

    .btn-danger {
      background: linear-gradient(135deg, var(--danger-color), #e74c3c);
      color: white;
    }

    .btn-info {
      background: linear-gradient(135deg, var(--info-color), #0b5ed7);
      color: white;
    }

    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .image-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .image-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .image-container {
      position: relative;
      height: 200px;
      overflow: hidden;
    }

    .gallery-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .image-card:hover .gallery-image {
      transform: scale(1.05);
    }

    .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .image-card:hover .image-overlay {
      opacity: 1;
    }

    .overlay-buttons {
      display: flex;
      gap: 0.5rem;
    }

    .btn-sm {
      padding: 0.5rem 0.75rem;
      font-size: 0.85rem;
    }

    .image-info {
      padding: 1rem;
    }

    .image-title {
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
      font-size: 1.1rem;
    }

    .image-description {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 1rem;
      line-height: 1.4;
    }

    .image-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.8rem;
      color: #6c757d;
    }

    .category-badge {
      background: var(--light-color);
      color: var(--primary-color);
      padding: 0.25rem 0.75rem;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
    }

    .alert {
      border-radius: 12px;
      border: none;
      font-weight: 500;
      margin-bottom: 2rem;
    }

    .alert-success {
      background: linear-gradient(135deg, #d1e7dd, #badbcc);
      color: var(--success-color);
    }

    .alert-danger {
      background: linear-gradient(135deg, #f8d7da, #f5c2c7);
      color: var(--danger-color);
    }

    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: #6c757d;
    }

    .empty-state i {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 1rem;
    }

    .search-filter {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin-bottom: 1rem;
    }

    .search-input {
      flex: 1;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 0.75rem;
      transition: all 0.3s ease;
    }

    .search-input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
      outline: none;
    }

    .filter-select {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 0.75rem;
      transition: all 0.3s ease;
      min-width: 150px;
    }

    .filter-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
      outline: none;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .admin-panel {
        padding: 1.5rem;
        margin: 0 1rem;
      }

      .header-bar h1 {
        font-size: 2rem;
      }

      .action-bar {
        flex-direction: column;
        align-items: stretch;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }

      .gallery-grid {
        grid-template-columns: 1fr;
      }

      .search-filter {
        flex-direction: column;
        align-items: stretch;
      }
    }

    /* Focus styles for accessibility */
    .btn:focus,
    .search-input:focus,
    .filter-select:focus {
      outline: 2px solid var(--primary-color);
      outline-offset: 2px;
    }

    /* Animation for cards */
    .stat-card {
      animation: slideInUp 0.5s ease-out;
    }

    .image-card {
      animation: slideInUp 0.5s ease-out;
    }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <div class="header-bar">
      <div class="user-info">
        <i class="fas fa-user-circle me-1"></i>
        Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
        <span class="ms-2">|</span>
        <a href="logout.php" class="text-white text-decoration-none ms-2">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
      <h1><i class="fas fa-images me-2"></i>Gallery Management</h1>
      <p>Upload, edit, and manage gallery images for the SDOIN website</p>
    </div>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="resources_admin.php"><i class="fas fa-folder-open"></i> Manage Resources</a></li>
        <li class="breadcrumb-item"><a href="gallery_admin.php"><i class="fas fa-images"></i> Manage Gallery</a></li>
        <li class="breadcrumb-item active" aria-current="page">Gallery Management</li>
      </ol>
    </nav>

    <div class="admin-panel">
      <!-- Statistics Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number"><?= $totalImages ?></div>
          <div class="stat-label">Total Images</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?= $totalCategories ?></div>
          <div class="stat-label">Categories</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?= formatFileSize($totalSize) ?></div>
          <div class="stat-label">Total Size</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?= count(array_filter($images, function($i) { return strtotime($i['uploaded_at']) >= strtotime('-7 days'); })) ?></div>
          <div class="stat-label">New This Week</div>
        </div>
      </div>

      <!-- Alert Messages -->
      <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          Image uploaded successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if ($updated_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          Image updated successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <?= htmlspecialchars(urldecode($error_message)) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Action Bar -->
      <div class="action-bar">
        <div>
          <a href="gallery_upload.php" class="btn btn-success">
            <i class="fas fa-upload me-1"></i>Upload New Image
          </a>
          <a href="../gallery.php" class="btn btn-primary ms-2" target="_blank">
            <i class="fas fa-eye me-1"></i>View Public Gallery
          </a>
        </div>
        <div class="search-filter">
          <input type="text" id="searchInput" class="search-input" placeholder="Search images...">
          <select id="categoryFilter" class="filter-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= htmlspecialchars($category) ?>"><?= ucfirst(htmlspecialchars($category)) ?></option>
            <?php endforeach; ?>
          </select>
          <select id="themeFilter" class="filter-select">
            <option value="">All Themes</option>
            <?php foreach ($themes as $theme): ?>
              <option value="<?= htmlspecialchars($theme) ?>"><?= ucwords(str_replace('-', ' ', htmlspecialchars($theme))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Gallery Grid -->
      <?php if (empty($images)): ?>
        <div class="empty-state">
          <i class="fas fa-images"></i>
          <h3>No Images Available</h3>
          <p>No images have been uploaded yet. Start by uploading your first image.</p>
          <a href="gallery_upload.php" class="btn btn-success">
            <i class="fas fa-upload me-1"></i>Upload First Image
          </a>
        </div>
      <?php else: ?>
        <div class="gallery-grid" id="galleryGrid">
          <?php foreach ($images as $image): ?>
            <div class="image-card" data-category="<?= htmlspecialchars($image['category']) ?>">
              <div class="image-container">
                <img src="../uploads/gallery/<?= urlencode($image['filename']) ?>" 
                     alt="<?= htmlspecialchars($image['title']) ?>" 
                     class="gallery-image">
                <div class="image-overlay">
                  <div class="overlay-buttons">
                    <a href="edit_gallery.php?id=<?= $image['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="../uploads/gallery/<?= urlencode($image['filename']) ?>" target="_blank" class="btn btn-info btn-sm" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="../uploads/gallery/<?= urlencode($image['filename']) ?>" download class="btn btn-success btn-sm" title="Download">
                      <i class="fas fa-download"></i>
                    </a>
                    <a href="delete_gallery.php?id=<?= $image['id'] ?>" class="btn btn-danger btn-sm" title="Delete" 
                       onclick="return confirm('Are you sure you want to delete this image? This action cannot be undone.')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </div>
              </div>
              <div class="image-info">
                <div class="image-title"><?= htmlspecialchars($image['title']) ?></div>
                <div class="image-description"><?= htmlspecialchars($image['description']) ?: 'No description' ?></div>
                <div class="image-meta">
                  <span class="category-badge" style="background: <?= getCategoryColor($image['category']) ?>20; color: <?= getCategoryColor($image['category']) ?>;">
                    <i class="<?= getCategoryIcon($image['category']) ?>"></i>
                    <?= htmlspecialchars(ucfirst($image['category'])) ?>
                  </span>
                  <?php if (!empty($image['theme'])): ?>
                    <span class="category-badge bg-info text-dark ms-1">
                      <i class="fas fa-layer-group"></i>
                      <?= ucwords(str_replace('-', ' ', htmlspecialchars($image['theme']))) ?>
                    </span>
                  <?php endif; ?>
                  <span><?= date('M j, Y', strtotime($image['uploaded_at'])) ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    // Search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const themeFilter = document.getElementById('themeFilter');
    const galleryGrid = document.getElementById('galleryGrid');

    function filterImages() {
      const searchTerm = searchInput.value.toLowerCase();
      const selectedCategory = categoryFilter.value.toLowerCase();
      const selectedTheme = themeFilter.value.toLowerCase();
      const cards = galleryGrid.querySelectorAll('.image-card');

      cards.forEach(card => {
        const title = card.querySelector('.image-title').textContent.toLowerCase();
        const description = card.querySelector('.image-description').textContent.toLowerCase();
        const category = card.dataset.category.toLowerCase();
        const theme = (card.querySelector('.category-badge.bg-info') ? card.querySelector('.category-badge.bg-info').textContent.toLowerCase() : '');

        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        const matchesCategory = !selectedCategory || category === selectedCategory;
        const matchesTheme = !selectedTheme || theme.includes(selectedTheme);

        if (matchesSearch && matchesCategory && matchesTheme) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });

      // Show/hide empty state
      const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none');
      const emptyState = document.querySelector('.empty-state');
      if (emptyState) {
        if (visibleCards.length === 0) {
          emptyState.style.display = 'block';
        } else {
          emptyState.style.display = 'none';
        }
      }
    }

    // Event listeners
    searchInput.addEventListener('input', filterImages);
    categoryFilter.addEventListener('change', filterImages);
    themeFilter.addEventListener('change', filterImages);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + F to focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        searchInput.focus();
      }
      
      // Ctrl/Cmd + N to upload new image
      if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        window.location.href = 'gallery_upload.php';
      }
    });

    // Show keyboard shortcuts help
    console.log('Keyboard shortcuts: Ctrl+F (Search), Ctrl+N (New Image)');

    // Add loading animation to cards
    document.querySelectorAll('.stat-card').forEach((card, index) => {
      card.style.animationDelay = `${index * 0.1}s`;
    });

    document.querySelectorAll('.image-card').forEach((card, index) => {
      card.style.animationDelay = `${index * 0.05}s`;
    });
  </script>
</body>
</html> 