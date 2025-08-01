<?php
// backend/resources_admin.php
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

// Fetch all resources
$stmt = $pdo->query("SELECT * FROM resources ORDER BY uploaded_at DESC");
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalResources = count($resources);
$totalSize = array_sum(array_column($resources, 'filesize'));
$categories = array_unique(array_column($resources, 'category'));
$totalCategories = count($categories);

// Function to get file icon based on file type
function getFileIcon($filetype) {
    if (strpos($filetype, 'pdf') !== false) {
        return 'fas fa-file-pdf';
    } elseif (strpos($filetype, 'word') !== false || strpos($filetype, 'document') !== false) {
        return 'fas fa-file-word';
    } elseif (strpos($filetype, 'excel') !== false || strpos($filetype, 'spreadsheet') !== false) {
        return 'fas fa-file-excel';
    } elseif (strpos($filetype, 'image') !== false) {
        return 'fas fa-file-image';
    } elseif (strpos($filetype, 'text') !== false) {
        return 'fas fa-file-alt';
    } else {
        return 'fas fa-file';
    }
}

// Function to get file color based on file type
function getFileColor($filetype) {
    if (strpos($filetype, 'pdf') !== false) {
        return '#dc3545';
    } elseif (strpos($filetype, 'word') !== false || strpos($filetype, 'document') !== false) {
        return '#0d6efd';
    } elseif (strpos($filetype, 'excel') !== false || strpos($filetype, 'spreadsheet') !== false) {
        return '#198754';
    } elseif (strpos($filetype, 'image') !== false) {
        return '#fd7e14';
    } elseif (strpos($filetype, 'text') !== false) {
        return '#6c757d';
    } else {
        return '#6c757d';
    }
}

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
        'guides' => 'fas fa-chalkboard-teacher',
        'documents' => 'fas fa-file-alt',
        'tools' => 'fas fa-toolbox',
        'reports' => 'fas fa-chart-line',
        'advocacy' => 'fas fa-bullhorn',
        'policy' => 'fas fa-file-contract',
        'parents' => 'fas fa-users',
        'others' => 'fas fa-folder'
    ];
    return $icons[strtolower($category)] ?? 'fas fa-file';
}

// Function to get category color
function getCategoryColor($category) {
    $colors = [
        'guides' => '#e83e8c',
        'documents' => '#6f42c1',
        'tools' => '#fd7e14',
        'reports' => '#20c997',
        'advocacy' => '#dc3545',
        'policy' => '#6f42c1',
        'parents' => '#0dcaf0',
        'others' => '#6c757d'
    ];
    return $colors[strtolower($category)] ?? '#6c757d';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resource Management - SDOIN Admin</title>
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

    .table-container {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .table {
      margin: 0;
    }

    .table thead th {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      border: none;
      padding: 1rem;
      font-weight: 600;
      text-align: center;
    }

    .table tbody td {
      padding: 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #e9ecef;
    }

    .table tbody tr:hover {
      background: rgba(37, 117, 252, 0.05);
    }

    .resource-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .file-icon {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1rem;
    }

    .resource-details h6 {
      margin: 0;
      font-weight: 600;
      color: var(--dark-color);
    }

    .resource-details small {
      color: #6c757d;
      font-size: 0.8rem;
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

    .file-link {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .file-link:hover {
      color: var(--secondary-color);
      text-decoration: underline;
    }

    .action-buttons {
      display: flex;
      gap: 0.5rem;
      justify-content: center;
    }

    .btn-sm {
      padding: 0.5rem 0.75rem;
      font-size: 0.85rem;
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

      .table-responsive {
        font-size: 0.9rem;
      }

      .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
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
      <h1><i class="fas fa-folder-open me-2"></i>Resource Management</h1>
      <p>Upload, edit, and manage downloadable resources for the SDOIN community</p>
    </div>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="resources_admin.php"><i class="fas fa-folder-open"></i> Manage Resources</a></li>
        <li class="breadcrumb-item"><a href="gallery_admin.php"><i class="fas fa-images"></i> Manage Gallery</a></li>
        <li class="breadcrumb-item active" aria-current="page">Resource Management</li>
      </ol>
    </nav>

    <div class="admin-panel">
      <!-- Statistics Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number"><?= $totalResources ?></div>
          <div class="stat-label">Total Resources</div>
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
          <div class="stat-number"><?= count(array_filter($resources, function($r) { return strtotime($r['uploaded_at']) >= strtotime('-7 days'); })) ?></div>
          <div class="stat-label">New This Week</div>
        </div>
      </div>

      <!-- Alert Messages -->
      <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          Resource uploaded successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if ($updated_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          Resource updated successfully!
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
          <a href="resources_upload.php" class="btn btn-success">
            <i class="fas fa-upload me-1"></i>Upload New Resource
          </a>
          <a href="../resources.php" class="btn btn-primary ms-2" target="_blank">
            <i class="fas fa-eye me-1"></i>View Public Page
          </a>
        </div>
        <div class="search-filter">
          <input type="text" id="searchInput" class="search-input" placeholder="Search resources...">
          <select id="categoryFilter" class="filter-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= htmlspecialchars($category) ?>"><?= ucfirst(htmlspecialchars($category)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Resources Table -->
      <div class="table-container">
        <?php if (empty($resources)): ?>
          <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No Resources Available</h3>
            <p>No resources have been uploaded yet. Start by uploading your first resource.</p>
            <a href="resources_upload.php" class="btn btn-success">
              <i class="fas fa-upload me-1"></i>Upload First Resource
            </a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Resource</th>
                  <th>Category</th>
                  <th>File</th>
                  <th>Size</th>
                  <th>Uploaded</th>
                  <th>Updated</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="resourcesTableBody">
                <?php foreach ($resources as $resource): ?>
                  <tr data-category="<?= htmlspecialchars($resource['category']) ?>">
                    <td>
                      <div class="resource-info">
                        <div class="file-icon" style="background: <?= getFileColor($resource['filetype']) ?>;">
                          <i class="<?= getFileIcon($resource['filetype']) ?>"></i>
                        </div>
                        <div class="resource-details">
                          <h6><?= htmlspecialchars($resource['title']) ?></h6>
                          <small><?= htmlspecialchars($resource['description']) ?: 'No description' ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="category-badge" style="background: <?= getCategoryColor($resource['category']) ?>20; color: <?= getCategoryColor($resource['category']) ?>;">
                        <i class="<?= getCategoryIcon($resource['category']) ?>"></i>
                        <?= htmlspecialchars(ucfirst($resource['category'])) ?>
                      </span>
                    </td>
                    <td>
                      <a href="../uploads/resources/<?= urlencode($resource['filename']) ?>" target="_blank" class="file-link">
                        <i class="fas fa-external-link-alt"></i>
                        <?= strtoupper(pathinfo($resource['filename'], PATHINFO_EXTENSION)) ?>
                      </a>
                    </td>
                    <td><?= formatFileSize($resource['filesize']) ?></td>
                    <td><?= date('M j, Y', strtotime($resource['uploaded_at'])) ?></td>
                    <td><?= date('M j, Y', strtotime($resource['updated_at'])) ?></td>
                    <td>
                      <div class="action-buttons">
                        <a href="edit_resource.php?id=<?= $resource['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                          <i class="fas fa-edit"></i>
                        </a>
                                                 <?php if (strpos($resource['filetype'], 'pdf') !== false): ?>
                           <a href="../uploads/resources/<?= urlencode($resource['filename']) ?>" target="_blank" class="btn btn-info btn-sm" title="Preview">
                             <i class="fas fa-eye"></i>
                           </a>
                         <?php else: ?>
                           <a href="../uploads/resources/<?= urlencode($resource['filename']) ?>" target="_blank" class="btn btn-info btn-sm" title="Open File" onclick="return confirm('This file will open in a new tab. Some file types may download instead of previewing.')">
                             <i class="fas fa-external-link-alt"></i>
                           </a>
                         <?php endif; ?>
                        <a href="../uploads/resources/<?= urlencode($resource['filename']) ?>" download class="btn btn-success btn-sm" title="Download">
                          <i class="fas fa-download"></i>
                        </a>
                        <a href="delete_resource.php?id=<?= $resource['id'] ?>" class="btn btn-danger btn-sm" title="Delete" 
                           onclick="return confirm('Are you sure you want to delete this resource? This action cannot be undone.')">
                          <i class="fas fa-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    // Search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const tableBody = document.getElementById('resourcesTableBody');

    function filterResources() {
      const searchTerm = searchInput.value.toLowerCase();
      const selectedCategory = categoryFilter.value.toLowerCase();
      const rows = tableBody.querySelectorAll('tr');

      rows.forEach(row => {
        const title = row.querySelector('h6').textContent.toLowerCase();
        const description = row.querySelector('small').textContent.toLowerCase();
        const category = row.dataset.category.toLowerCase();

        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        const matchesCategory = !selectedCategory || category === selectedCategory;

        if (matchesSearch && matchesCategory) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });

      // Show/hide empty state
      const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
      const emptyState = document.querySelector('.empty-state');
      if (emptyState) {
        if (visibleRows.length === 0) {
          emptyState.style.display = 'block';
        } else {
          emptyState.style.display = 'none';
        }
      }
    }

    // Event listeners
    searchInput.addEventListener('input', filterResources);
    categoryFilter.addEventListener('change', filterResources);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + F to focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        searchInput.focus();
      }
      
      // Ctrl/Cmd + N to upload new resource
      if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        window.location.href = 'resources_upload.php';
      }
    });

    // Auto-refresh functionality (optional)
    let autoRefreshTimer;
    
    function startAutoRefresh() {
      autoRefreshTimer = setInterval(() => {
        // You can implement auto-refresh here if needed
        // window.location.reload();
      }, 300000); // 5 minutes
    }

    // Start auto-refresh when page loads
    startAutoRefresh();

    // Clear auto-refresh timer when leaving page
    window.addEventListener('beforeunload', function() {
      clearInterval(autoRefreshTimer);
    });

    // Show keyboard shortcuts help
    console.log('Keyboard shortcuts: Ctrl+F (Search), Ctrl+N (New Resource)');

    // Add loading animation to stats cards
    document.querySelectorAll('.stat-card').forEach((card, index) => {
      card.style.animationDelay = `${index * 0.1}s`;
    });
  </script>
</body>
</html> 