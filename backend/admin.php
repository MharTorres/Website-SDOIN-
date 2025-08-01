<?php
session_start();

// Enhanced security checks
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
  header("Location: login.html");
  exit();
}

// Check session timeout (30 minutes)
$timeout = 30 * 60; // 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
  session_unset();
  session_destroy();
  header("Location: login.html?error=" . urlencode("Session expired. Please login again."));
  exit();
}
$_SESSION['last_activity'] = time();

require 'db.php';

// Fetch content with error handling
try {
  $stmt = $pdo->query("SELECT * FROM contents ORDER BY page, `key`");
  $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  $grouped = [];
  foreach ($contents as $item) {
    $grouped[$item['page']][] = $item;
  }
} catch (PDOException $e) {
  $error_message = "Database error: Unable to load content.";
  $grouped = [];
}

// Get success/error messages from URL
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : $error_message ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="SDOIN Admin Content Management Panel">
  <meta name="robots" content="noindex, nofollow">
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="X-Frame-Options" content="DENY">
  <meta http-equiv="X-XSS-Protection" content="1; mode=block">
  <title>Admin Panel - Content Manager | SDOIN</title>
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

    * {
      box-sizing: border-box;
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
      position: relative;
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

    .user-info {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 0.95rem;
      color: white;
      margin-bottom: 0.5rem;
      position: static;
      background: none;
      padding: 0;
      border-radius: 0;
      backdrop-filter: none;
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

    .page-section {
      border-left: 5px solid var(--primary-color);
      padding: 1.5rem;
      margin-bottom: 2.5rem;
      background: var(--light-color);
      border-radius: 0 12px 12px 0;
      transition: all 0.3s ease;
    }

    .page-section:hover {
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transform: translateX(5px);
    }

    .page-section h4 {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .page-section h4::before {
      content: '';
      width: 4px;
      height: 20px;
      background: var(--primary-color);
      border-radius: 2px;
    }

    .form-label {
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-label::after {
      content: '*';
      color: var(--danger-color);
      font-weight: bold;
    }

    .form-control, .form-select {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 0.75rem;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
    }

    .form-control.is-invalid {
      border-color: var(--danger-color);
    }

    .form-control.is-invalid:focus {
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .content-field {
      margin-bottom: 1.5rem;
      position: relative;
    }

    .content-field textarea {
      min-height: 100px;
      resize: vertical;
    }

    .char-counter {
      position: absolute;
      bottom: 5px;
      right: 10px;
      font-size: 0.75rem;
      color: #6c757d;
      background: rgba(255, 255, 255, 0.9);
      padding: 2px 6px;
      border-radius: 10px;
    }

    .btn {
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-success {
      background: linear-gradient(135deg, var(--success-color), #20c997);
    }

    .btn-danger {
      background: linear-gradient(135deg, var(--danger-color), #e74c3c);
    }

    .btn-secondary {
      background: linear-gradient(135deg, #6c757d, #495057);
    }

    .action-buttons {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      margin-top: 2rem;
      padding-top: 2rem;
      border-top: 2px solid #e9ecef;
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

    .loading {
      opacity: 0.7;
      pointer-events: none;
    }

    .spinner-border-sm {
      width: 1rem;
      height: 1rem;
    }

    .stats-card {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 2rem;
      text-align: center;
    }

    .stats-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .stats-label {
      font-size: 1rem;
      opacity: 0.9;
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
    }

    .breadcrumb-item.active {
      color: rgba(255, 255, 255, 0.8);
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

      .action-buttons {
        flex-direction: column;
        gap: 1rem;
      }

      .btn {
        width: 100%;
      }

      .user-info {
        position: static;
        margin-bottom: 1rem;
        display: inline-block;
      }
    }

    /* Focus styles for accessibility */
    .btn:focus,
    .form-control:focus,
    .form-select:focus {
      outline: 2px solid var(--primary-color);
      outline-offset: 2px;
    }

    /* Animation for page sections */
    .page-section {
      animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <div class="header-bar">
      <div class="user-info mb-2">
        <i class="fas fa-user-circle me-1"></i>
        Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
        <span class="ms-2">|</span>
        <a href="logout.php" class="text-white text-decoration-none ms-2">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
      
      <h1><i class="fas fa-cogs me-2"></i>SDOIN Admin Content Manager</h1>
      <p>Edit dynamic content across all pages</p>
    </div>

    <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="breadcrumb-item"><a href="resources_admin.php"><i class="fas fa-folder-open"></i> Manage Resources</a></li>
    <li class="breadcrumb-item"><a href="gallery_admin.php"><i class="fas fa-images"></i> Manage Gallery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Content Management</li>
  </ol>
</nav>

    <div class="admin-panel">
      <!-- Statistics Card -->
      <div class="stats-card">
        <div class="stats-number"><?= count($contents) ?></div>
        <div class="stats-label">Content Fields</div>
      </div>

      <!-- Alert Messages -->
      <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          <?= htmlspecialchars(urldecode($success_message)) ?>
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

      <form id="contentForm" action="save_content.php" method="POST" novalidate>
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        
        <?php if (empty($grouped)): ?>
          <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
            <h4 class="mt-3 text-muted">No content found</h4>
            <p class="text-muted">There are no content fields to edit at the moment.</p>
          </div>
        <?php else: ?>
          <?php foreach ($grouped as $page => $items): ?>
            <div class="page-section">
              <h4>
                <i class="fas fa-file-alt"></i>
                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $page))) ?> Page
                <span class="badge bg-primary ms-2"><?= count($items) ?> fields</span>
              </h4>
              
              <?php foreach ($items as $content): ?>
                <div class="content-field">
                  <label for="content_<?= htmlspecialchars($content['key']) ?>" class="form-label">
                    <i class="fas fa-edit"></i>
                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $content['key']))) ?>
                  </label>
                  
                  <textarea 
                    name="content[<?= htmlspecialchars($content['key']) ?>]" 
                    id="content_<?= htmlspecialchars($content['key']) ?>"
                    class="form-control" 
                    rows="4"
                    placeholder="Enter content for <?= htmlspecialchars(ucwords(str_replace('_', ' ', $content['key']))) ?>"
                    maxlength="2000"
                    aria-describedby="char_count_<?= htmlspecialchars($content['key']) ?>"
                  ><?= htmlspecialchars($content['value']) ?></textarea>
                  
                  <div class="char-counter" id="char_count_<?= htmlspecialchars($content['key']) ?>">
                    <span class="current-count"><?= strlen($content['value']) ?></span>/2000
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <div class="action-buttons">
          <div>
            <button type="submit" class="btn btn-success" id="saveBtn">
              <i class="fas fa-save me-1"></i>
              <span id="saveText">Save All Changes</span>
              <span id="saveSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;">
                <span class="visually-hidden">Saving...</span>
              </span>
            </button>
            
            <button type="button" class="btn btn-secondary ms-2" id="previewBtn">
              <i class="fas fa-eye me-1"></i>Preview Changes
            </button>
          </div>
          
          <div>
            <a href="logout.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">
              <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Character counter functionality
    document.querySelectorAll('textarea').forEach(textarea => {
      const key = textarea.name.match(/\[(.*?)\]/)[1];
      const counter = document.getElementById(`char_count_${key}`);
      const currentCount = counter.querySelector('.current-count');
      
      textarea.addEventListener('input', function() {
        const length = this.value.length;
        currentCount.textContent = length;
        
        if (length > 1800) {
          counter.style.color = '#dc3545';
        } else if (length > 1500) {
          counter.style.color = '#ffc107';
        } else {
          counter.style.color = '#6c757d';
        }
      });
    });

    // Form validation and submission
    const contentForm = document.getElementById('contentForm');
    const saveBtn = document.getElementById('saveBtn');
    const saveText = document.getElementById('saveText');
    const saveSpinner = document.getElementById('saveSpinner');

    function setLoadingState(loading) {
      if (loading) {
        saveBtn.disabled = true;
        saveText.style.display = 'none';
        saveSpinner.style.display = 'inline-block';
        contentForm.classList.add('loading');
      } else {
        saveBtn.disabled = false;
        saveText.style.display = 'inline';
        saveSpinner.style.display = 'none';
        contentForm.classList.remove('loading');
      }
    }

    function validateForm() {
      let isValid = true;
      const textareas = document.querySelectorAll('textarea');
      
      textareas.forEach(textarea => {
        if (textarea.value.trim() === '') {
          textarea.classList.add('is-invalid');
          isValid = false;
        } else {
          textarea.classList.remove('is-invalid');
        }
      });
      
      return isValid;
    }

    // Form submission
    contentForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      if (!validateForm()) {
        alert('Please fill in all required fields.');
        return;
      }
      
      setLoadingState(true);
      
      // Submit form
      this.submit();
    });

    // Preview functionality
    document.getElementById('previewBtn').addEventListener('click', function() {
      const formData = new FormData(contentForm);
      const previewData = {};
      
      for (let [key, value] of formData.entries()) {
        if (key.startsWith('content[')) {
          const fieldName = key.match(/\[(.*?)\]/)[1];
          previewData[fieldName] = value;
        }
      }
      
      // Store preview data in sessionStorage
      sessionStorage.setItem('previewData', JSON.stringify(previewData));
      
      // Open preview in new tab
      window.open('../index.html?preview=true', '_blank');
    });

    // Auto-save functionality (every 30 seconds)
    let autoSaveTimer;
    
    function startAutoSave() {
      autoSaveTimer = setInterval(() => {
        const formData = new FormData(contentForm);
        const hasChanges = Array.from(formData.entries()).some(([key, value]) => {
          if (key.startsWith('content[')) {
            const fieldName = key.match(/\[(.*?)\]/)[1];
            const originalValue = document.querySelector(`textarea[name="content[${fieldName}]"]`).defaultValue;
            return value !== originalValue;
          }
          return false;
        });
        
        if (hasChanges) {
          console.log('Auto-saving changes...');
          // You can implement auto-save AJAX call here
        }
      }, 30000);
    }

    // Start auto-save when page loads
    startAutoSave();

    // Clear auto-save timer when leaving page
    window.addEventListener('beforeunload', function() {
      clearInterval(autoSaveTimer);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + S to save
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        contentForm.dispatchEvent(new Event('submit'));
      }
      
      // Ctrl/Cmd + P to preview
      if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        document.getElementById('previewBtn').click();
      }
    });

    // Show keyboard shortcuts help
    console.log('Keyboard shortcuts: Ctrl+S (Save), Ctrl+P (Preview)');
  </script>
</body>
</html>
