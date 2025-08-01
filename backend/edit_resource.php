<?php
// backend/edit_resource.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
  header("Location: login.html");
  exit();
}

// Get resource ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die('Invalid resource ID.');
}

// Fetch resource
$stmt = $pdo->prepare("SELECT * FROM resources WHERE id = ?");
$stmt->execute([$id]);
$resource = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$resource) {
    die('Resource not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $updateFile = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;

    if (!$title || !$category) {
        $error = 'Title and category are required.';
    } else {
        $filename = $resource['filename'];
        $filetype = $resource['filetype'];
        $filesize = $resource['filesize'];
        $uploadDir = __DIR__ . '/../uploads/resources/';

        // If a new file is uploaded, replace the old file
        if ($updateFile) {
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

            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Invalid file type.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'File is too large.';
            } else {
                // Remove old file
                $oldPath = $uploadDir . $filename;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                // Save new file
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $basename = bin2hex(random_bytes(8));
                $filename = $basename . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $error = 'Failed to move uploaded file.';
                } else {
                    $filetype = $file['type'];
                    $filesize = $file['size'];
                }
            }
        }

        if (!isset($error)) {
            // Update database
            $stmt = $pdo->prepare("UPDATE resources SET title=?, description=?, category=?, filename=?, filetype=?, filesize=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([
                $title,
                $description,
                $category,
                $filename,
                $filetype,
                $filesize,
                $id
            ]);
            header('Location: resources_admin.php?updated=1');
            exit();
        }
    }
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Resource - SDOIN Admin</title>
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

    .edit-container {
      max-width: 800px;
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

    .edit-panel {
      background: #fff;
      padding: 2.5rem;
      border-radius: 20px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
    }

    .edit-panel::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
    }

    .form-section {
      margin-bottom: 2rem;
      padding: 1.5rem;
      background: var(--light-color);
      border-radius: 12px;
      border-left: 4px solid var(--primary-color);
    }

    .form-section h4 {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-label {
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-label.required::after {
      content: '*';
      color: var(--danger-color);
      font-weight: bold;
      margin-left: 0.25rem;
    }

    .form-control, .form-select, .form-textarea {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 0.75rem;
      transition: all 0.3s ease;
      font-size: 0.95rem;
      background: white;
    }

    .form-control:focus, .form-select:focus, .form-textarea:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
      outline: none;
    }

    .form-control.is-invalid {
      border-color: var(--danger-color);
    }

    .form-control.is-invalid:focus {
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .current-file {
      background: white;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
    }

    .current-file-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .file-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
    }

    .file-details h5 {
      margin: 0;
      font-weight: 600;
      color: var(--dark-color);
    }

    .file-details p {
      margin: 0;
      color: #6c757d;
      font-size: 0.9rem;
    }

    .file-actions {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
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

    .btn-secondary {
      background: linear-gradient(135deg, #6c757d, #495057);
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

    .category-info {
      background: linear-gradient(135deg, var(--info-color), #0b5ed7);
      color: white;
      padding: 1rem;
      border-radius: 8px;
      margin-top: 0.5rem;
    }

    .category-info h6 {
      margin: 0 0 0.5rem 0;
      font-weight: 600;
    }

    .file-upload-area {
      border: 2px dashed #dee2e6;
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
      background: #f8f9fa;
      cursor: pointer;
      margin-top: 1rem;
    }

    .file-upload-area:hover {
      border-color: var(--primary-color);
      background: rgba(37, 117, 252, 0.05);
    }

    .file-upload-area.dragover {
      border-color: var(--success-color);
      background: rgba(25, 135, 84, 0.05);
    }

    .file-upload-icon {
      font-size: 2rem;
      color: var(--primary-color);
      margin-bottom: 1rem;
    }

    .file-info {
      margin-top: 1rem;
      padding: 1rem;
      background: white;
      border-radius: 8px;
      border: 1px solid #e9ecef;
      display: none;
    }

    .file-info.show {
      display: block;
    }

    .file-preview {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .file-preview .file-icon {
      width: 40px;
      height: 40px;
      font-size: 1rem;
    }

    .file-preview .file-details h6 {
      margin: 0;
      font-weight: 600;
      color: var(--dark-color);
    }

    .file-preview .file-details small {
      color: #6c757d;
      font-size: 0.8rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .edit-panel {
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
        justify-content: center;
      }

      .file-actions {
        flex-direction: column;
      }
    }

    /* Focus styles for accessibility */
    .btn:focus,
    .form-control:focus,
    .form-select:focus {
      outline: 2px solid var(--primary-color);
      outline-offset: 2px;
    }

    /* Animation for form sections */
    .form-section {
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
  <div class="edit-container">
    <div class="header-bar">
      <div class="user-info">
        <i class="fas fa-user-circle me-1"></i>
        Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
        <span class="ms-2">|</span>
        <a href="logout.php" class="text-white text-decoration-none ms-2">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
      <h1><i class="fas fa-edit me-2"></i>Edit Resource</h1>
      <p>Update resource information and replace files as needed</p>
    </div>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="resources_admin.php"><i class="fas fa-folder-open"></i> Resource Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Resource</li>
      </ol>
    </nav>

    <div class="edit-panel">
      <!-- Error Message -->
      <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form action="" method="POST" enctype="multipart/form-data" id="editForm" novalidate>
        <!-- Resource Information Section -->
        <div class="form-section">
          <h4><i class="fas fa-info-circle"></i>Resource Information</h4>
          
          <div class="mb-3">
            <label for="title" class="form-label required">
              <i class="fas fa-heading"></i>Resource Title
            </label>
            <input type="text" name="title" id="title" class="form-control" 
                   value="<?= htmlspecialchars($resource['title']) ?>" required 
                   placeholder="Enter a descriptive title for the resource">
            <div class="invalid-feedback">Please provide a resource title.</div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">
              <i class="fas fa-align-left"></i>Description
            </label>
            <textarea name="description" id="description" class="form-control form-textarea" rows="4"
                      placeholder="Provide a brief description of the resource content and purpose"><?= htmlspecialchars($resource['description']) ?></textarea>
            <div class="form-text">This description will help users understand what the resource contains.</div>
          </div>

          <div class="mb-3">
            <label for="category" class="form-label required">
              <i class="fas fa-tags"></i>Category
            </label>
            <select name="category" id="category" class="form-select" required>
              <option value="">Select a category</option>
              <option value="guides" <?= $resource['category'] === 'guides' ? 'selected' : '' ?>>📚 Guides</option>
              <option value="documents" <?= $resource['category'] === 'documents' ? 'selected' : '' ?>>📄 Documents</option>
              <option value="tools" <?= $resource['category'] === 'tools' ? 'selected' : '' ?>>🛠️ Tools</option>
              <option value="reports" <?= $resource['category'] === 'reports' ? 'selected' : '' ?>>📊 Reports</option>
              <option value="advocacy" <?= $resource['category'] === 'advocacy' ? 'selected' : '' ?>>📢 Advocacy</option>
              <option value="policy" <?= $resource['category'] === 'policy' ? 'selected' : '' ?>>📋 Policy</option>
              <option value="parents" <?= $resource['category'] === 'parents' ? 'selected' : '' ?>>👨‍👩‍👧‍👦 Parent Resources</option>
              <option value="others" <?= $resource['category'] === 'others' ? 'selected' : '' ?>>📁 Others</option>
            </select>
            <div class="invalid-feedback">Please select a category.</div>
            
            <div class="category-info" id="categoryInfo" style="display: none;">
              <h6><i class="fas fa-info-circle me-1"></i>Category Description</h6>
              <div id="categoryDescription"></div>
            </div>
          </div>
        </div>

        <!-- Current File Section -->
        <div class="form-section">
          <h4><i class="fas fa-file-alt"></i>Current File</h4>
          
          <div class="current-file">
            <div class="current-file-header">
              <div class="file-icon" style="background: <?= getFileColor($resource['filetype']) ?>;">
                <i class="<?= getFileIcon($resource['filetype']) ?>"></i>
              </div>
              <div class="file-details">
                <h5><?= htmlspecialchars($resource['title']) ?></h5>
                <p><?= formatFileSize($resource['filesize']) ?> • <?= strtoupper(pathinfo($resource['filename'], PATHINFO_EXTENSION)) ?></p>
              </div>
            </div>
            <div class="file-actions">
                             <?php if (strpos($resource['filetype'], 'pdf') !== false): ?>
                 <a href="../uploads/resources/<?= urlencode($resource['filename']) ?>" target="_blank" class="btn btn-info">
                   <i class="fas fa-eye me-1"></i>Preview File
                 </a>
               <?php else: ?>
                 <a href="../uploads/resources/<?= urlencode($resource['filename']) ?>" target="_blank" class="btn btn-info" onclick="return confirm('This file will open in a new tab. Some file types may download instead of previewing.')">
                   <i class="fas fa-external-link-alt me-1"></i>Open File
                 </a>
               <?php endif; ?>
              <a href="../uploads/resources/<?= urlencode($resource['filename']) ?>" download class="btn btn-success">
                <i class="fas fa-download me-1"></i>Download
              </a>
            </div>
          </div>
        </div>

        <!-- File Replacement Section -->
        <div class="form-section">
          <h4><i class="fas fa-file-upload"></i>Replace File (Optional)</h4>
          <p class="text-muted mb-3">Upload a new file to replace the current one. Leave blank to keep the existing file.</p>
          
          <div class="file-upload-area" id="fileUploadArea">
            <div class="file-upload-icon">
              <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h5>Choose a new file or drag it here</h5>
            <p class="text-muted">Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, TXT</p>
            <p class="text-muted">Maximum file size: 10MB</p>
            <input type="file" name="file" id="file" class="form-control" 
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt" style="display: none;">
            <button type="button" class="btn btn-primary mt-2" onclick="document.getElementById('file').click()">
              <i class="fas fa-folder-open me-1"></i>Browse Files
            </button>
          </div>

          <div class="file-info" id="fileInfo">
            <div class="file-preview">
              <div class="file-icon" id="fileIcon" style="background: #6c757d;">
                <i class="fas fa-file"></i>
              </div>
              <div class="file-details">
                <h6 id="fileName">No file selected</h6>
                <small id="fileSize">0 KB</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <div>
            <button type="submit" class="btn btn-success" id="updateBtn">
              <i class="fas fa-save me-1"></i>
              <span id="updateText">Update Resource</span>
              <span id="updateSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;">
                <span class="visually-hidden">Updating...</span>
              </span>
            </button>
            
            <button type="reset" class="btn btn-secondary ms-2">
              <i class="fas fa-undo me-1"></i>Reset Changes
            </button>
          </div>
          
          <div>
            <a href="resources_admin.php" class="btn btn-danger">
              <i class="fas fa-arrow-left me-1"></i>Go Back
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Category descriptions
    const categoryDescriptions = {
      'guides': 'Teaching guides, instructional materials, and educational manuals for educators.',
      'documents': 'Official documents, forms, templates, and reference materials.',
      'tools': 'Educational tools, software, applications, and digital resources.',
      'reports': 'Analytical reports, performance data, and statistical information.',
      'advocacy': 'Promotional materials, campaign resources, and awareness content.',
      'policy': 'Policy documents, guidelines, procedures, and regulatory information.',
      'parents': 'Resources specifically designed for parents and guardians.',
      'others': 'Miscellaneous resources that don\'t fit into other categories.'
    };

    // File type icons and colors
    const fileTypeIcons = {
      'pdf': { icon: 'fas fa-file-pdf', color: '#dc3545' },
      'doc': { icon: 'fas fa-file-word', color: '#0d6efd' },
      'docx': { icon: 'fas fa-file-word', color: '#0d6efd' },
      'xls': { icon: 'fas fa-file-excel', color: '#198754' },
      'xlsx': { icon: 'fas fa-file-excel', color: '#198754' },
      'jpg': { icon: 'fas fa-file-image', color: '#fd7e14' },
      'jpeg': { icon: 'fas fa-file-image', color: '#fd7e14' },
      'png': { icon: 'fas fa-file-image', color: '#fd7e14' },
      'txt': { icon: 'fas fa-file-alt', color: '#6c757d' }
    };

    // Category selection handler
    document.getElementById('category').addEventListener('change', function() {
      const category = this.value;
      const categoryInfo = document.getElementById('categoryInfo');
      const categoryDescription = document.getElementById('categoryDescription');
      
      if (category && categoryDescriptions[category]) {
        categoryDescription.innerHTML = categoryDescriptions[category];
        categoryInfo.style.display = 'block';
      } else {
        categoryInfo.style.display = 'none';
      }
    });

    // Initialize category info if category is already selected
    const currentCategory = document.getElementById('category').value;
    if (currentCategory && categoryDescriptions[currentCategory]) {
      document.getElementById('categoryDescription').innerHTML = categoryDescriptions[currentCategory];
      document.getElementById('categoryInfo').style.display = 'block';
    }

    // File upload area handlers
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileIcon = document.getElementById('fileIcon');

    // Drag and drop functionality
    fileUploadArea.addEventListener('dragover', function(e) {
      e.preventDefault();
      this.classList.add('dragover');
    });

    fileUploadArea.addEventListener('dragleave', function(e) {
      e.preventDefault();
      this.classList.remove('dragover');
    });

    fileUploadArea.addEventListener('drop', function(e) {
      e.preventDefault();
      this.classList.remove('dragover');
      
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        fileInput.files = files;
        updateFileInfo(files[0]);
      }
    });

    // File input change handler
    fileInput.addEventListener('change', function() {
      if (this.files.length > 0) {
        updateFileInfo(this.files[0]);
      }
    });

    function updateFileInfo(file) {
      const extension = file.name.split('.').pop().toLowerCase();
      const fileType = fileTypeIcons[extension] || { icon: 'fas fa-file', color: '#6c757d' };
      
      fileName.textContent = file.name;
      fileSize.textContent = formatFileSize(file.size);
      fileIcon.innerHTML = `<i class="${fileType.icon}"></i>`;
      fileIcon.style.background = fileType.color;
      
      fileInfo.classList.add('show');
    }

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form validation and submission
    const editForm = document.getElementById('editForm');
    const updateBtn = document.getElementById('updateBtn');
    const updateText = document.getElementById('updateText');
    const updateSpinner = document.getElementById('updateSpinner');

    function setLoadingState(loading) {
      if (loading) {
        updateBtn.disabled = true;
        updateText.style.display = 'none';
        updateSpinner.style.display = 'inline-block';
        editForm.classList.add('loading');
      } else {
        updateBtn.disabled = false;
        updateText.style.display = 'inline';
        updateSpinner.style.display = 'none';
        editForm.classList.remove('loading');
      }
    }

    function validateForm() {
      let isValid = true;
      const requiredFields = ['title', 'category'];
      
      requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
          field.classList.add('is-invalid');
          isValid = false;
        } else {
          field.classList.remove('is-invalid');
        }
      });
      
      return isValid;
    }

    // Form submission
    editForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      if (!validateForm()) {
        alert('Please fill in all required fields.');
        return;
      }
      
      setLoadingState(true);
      
      // Submit form
      this.submit();
    });

    // Reset form handler
    editForm.addEventListener('reset', function() {
      fileInfo.classList.remove('show');
      document.getElementById('categoryInfo').style.display = 'none';
      
      // Remove validation classes
      document.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
      });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + Enter to submit
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        editForm.dispatchEvent(new Event('submit'));
      }
      
      // Escape to reset
      if (e.key === 'Escape') {
        editForm.dispatchEvent(new Event('reset'));
      }
    });

    // Show keyboard shortcuts help
    console.log('Keyboard shortcuts: Ctrl+Enter (Submit), Escape (Reset)');
  </script>
</body>
</html> 