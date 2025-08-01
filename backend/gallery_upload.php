<?php
// backend/gallery_upload.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
  header("Location: login.html");
  exit();
}

// Get success/error messages from URL
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Gallery Image - SDOIN Admin</title>
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

    .upload-container {
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

    .upload-panel {
      background: #fff;
      padding: 2.5rem;
      border-radius: 20px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
    }

    .upload-panel::before {
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

    .image-upload-area {
      border: 2px dashed #dee2e6;
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
      background: #f8f9fa;
      cursor: pointer;
    }

    .image-upload-area:hover {
      border-color: var(--primary-color);
      background: rgba(37, 117, 252, 0.05);
    }

    .image-upload-area.dragover {
      border-color: var(--success-color);
      background: rgba(25, 135, 84, 0.05);
    }

    .image-upload-icon {
      font-size: 3rem;
      color: var(--primary-color);
      margin-bottom: 1rem;
    }

    .image-preview {
      margin-top: 1rem;
      display: none;
    }

    .image-preview.show {
      display: block;
    }

    .preview-container {
      position: relative;
      display: inline-block;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .preview-image {
      max-width: 300px;
      max-height: 300px;
      object-fit: cover;
    }

    .preview-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.7);
      color: white;
      padding: 0.5rem;
      font-size: 0.8rem;
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

    /* Responsive Design */
    @media (max-width: 768px) {
      .upload-panel {
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
  <div class="upload-container">
    <div class="header-bar">
      <div class="user-info">
        <i class="fas fa-user-circle me-1"></i>
        Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
        <span class="ms-2">|</span>
        <a href="logout.php" class="text-white text-decoration-none ms-2">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
      <h1><i class="fas fa-image me-2"></i>Upload Gallery Image</h1>
      <p>Add photos to showcase SDOIN programs, events, and activities</p>
    </div>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="resources_admin.php"><i class="fas fa-folder-open"></i> Manage Resources</a></li>
        <li class="breadcrumb-item"><a href="gallery_admin.php"><i class="fas fa-images"></i> Gallery Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Upload Image</li>
      </ol>
    </nav>

    <div class="upload-panel">
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

      <form action="handle_gallery_upload.php" method="POST" enctype="multipart/form-data" id="uploadForm" novalidate>
        <!-- Image Information Section -->
        <div class="form-section">
          <h4><i class="fas fa-info-circle"></i>Image Information</h4>
          
          <div class="mb-3">
            <label for="title" class="form-label required">
              <i class="fas fa-heading"></i>Image Title
            </label>
            <input type="text" name="title" id="title" class="form-control" required 
                   placeholder="Enter a descriptive title for the image">
            <div class="invalid-feedback">Please provide an image title.</div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">
              <i class="fas fa-align-left"></i>Description
            </label>
            <textarea name="description" id="description" class="form-control form-textarea" rows="3"
                      placeholder="Provide a brief description of the image content"></textarea>
            <div class="form-text">This description will help users understand what the image shows.</div>
          </div>

          <div class="mb-3">
            <label for="category" class="form-label required">
              <i class="fas fa-tags"></i>Category
            </label>
            <select name="category" id="category" class="form-select" required>
              <option value="">Select a category</option>
              <option value="programs">📚 Programs</option>
              <option value="initiatives">💡 Initiatives</option>
              <option value="events">🎉 Events</option>
              <option value="sports">⚽ Sports</option>
            </select>
            <div class="invalid-feedback">Please select a category.</div>
            
            <div class="category-info" id="categoryInfo" style="display: none;">
              <h6><i class="fas fa-info-circle me-1"></i>Category Description</h6>
              <div id="categoryDescription"></div>
            </div>
          </div>
          <div class="mb-3" id="themeField" style="display:none;">
            <label for="theme" class="form-label">
              <i class="fas fa-layer-group"></i>Initiative Theme (for Initiatives only)
            </label>
            <select name="theme" id="theme" class="form-select">
              <option value="">Select a theme</option>
              <option value="literacy-numeracy">Literacy & Numeracy Advancement</option>
              <option value="sports-wellness">Sports Development & Wellness</option>
              <option value="environment-sustainability">Environment & Sustainability Advocacy</option>
              <option value="career-community">Career Programming & Community Immersion</option>
              <option value="technology-learning">Technology Integration & Asynchronous Learning</option>
              <option value="safe-resilient">Safe & Resilient Learning Spaces</option>
              <option value="initiativeposter">Initiative Posters</option>
            </select>
            <div class="form-text">Select a theme if this is an initiative image.</div>
          </div>
        </div>

        <!-- Image Upload Section -->
        <div class="form-section">
          <h4><i class="fas fa-image"></i>Image Upload</h4>
          
          <div class="image-upload-area" id="imageUploadArea">
            <div class="image-upload-icon">
              <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h5>Choose an image or drag it here</h5>
            <p class="text-muted">Supported formats: JPG, JPEG, PNG, GIF</p>
            <p class="text-muted">Maximum file size: 5MB</p>
            <input type="file" name="image" id="image" class="form-control" required 
                   accept="image/*" style="display: none;">
            <button type="button" class="btn btn-primary mt-2" onclick="document.getElementById('image').click()">
              <i class="fas fa-folder-open me-1"></i>Browse Images
            </button>
          </div>

          <div class="image-preview" id="imagePreview">
            <div class="preview-container">
              <img id="previewImage" class="preview-image" alt="Preview">
              <div class="preview-overlay">
                <div id="imageInfo">No image selected</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <div>
            <button type="submit" class="btn btn-success" id="uploadBtn">
              <i class="fas fa-upload me-1"></i>
              <span id="uploadText">Upload Image</span>
              <span id="uploadSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;">
                <span class="visually-hidden">Uploading...</span>
              </span>
            </button>
            
            <button type="reset" class="btn btn-secondary ms-2">
              <i class="fas fa-undo me-1"></i>Reset Form
            </button>
          </div>
          
          <div>
            <a href="gallery_admin.php" class="btn btn-danger">
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
      'programs': 'Educational programs, training sessions, and academic activities.',
      'initiatives': 'Special projects, campaigns, and innovative educational initiatives.',
      'events': 'School events, ceremonies, celebrations, and gatherings.',
      'sports': 'Sports activities, competitions, and physical education events.'
    };

    // Show/hide theme field based on category
    document.getElementById('category').addEventListener('change', function() {
      const category = this.value;
      const themeField = document.getElementById('themeField');
      if (category === 'initiatives') {
        themeField.style.display = 'block';
      } else {
        themeField.style.display = 'none';
        document.getElementById('theme').value = '';
      }
      const categoryInfo = document.getElementById('categoryInfo');
      const categoryDescription = document.getElementById('categoryDescription');
      
      if (category && categoryDescriptions[category]) {
        categoryDescription.innerHTML = categoryDescriptions[category];
        categoryInfo.style.display = 'block';
      } else {
        categoryInfo.style.display = 'none';
      }
    });

    // Image upload area handlers
    const imageUploadArea = document.getElementById('imageUploadArea');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const imageInfo = document.getElementById('imageInfo');

    // Drag and drop functionality
    imageUploadArea.addEventListener('dragover', function(e) {
      e.preventDefault();
      this.classList.add('dragover');
    });

    imageUploadArea.addEventListener('dragleave', function(e) {
      e.preventDefault();
      this.classList.remove('dragover');
    });

    imageUploadArea.addEventListener('drop', function(e) {
      e.preventDefault();
      this.classList.remove('dragover');
      
      const files = e.dataTransfer.files;
      if (files.length > 0 && files[0].type.startsWith('image/')) {
        imageInput.files = files;
        updateImagePreview(files[0]);
      }
    });

    // Image input change handler
    imageInput.addEventListener('change', function() {
      if (this.files.length > 0) {
        updateImagePreview(this.files[0]);
      }
    });

    function updateImagePreview(file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        previewImage.src = e.target.result;
        imageInfo.textContent = `${file.name} (${formatFileSize(file.size)})`;
        imagePreview.classList.add('show');
      };
      reader.readAsDataURL(file);
    }

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form validation and submission
    const uploadForm = document.getElementById('uploadForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadText = document.getElementById('uploadText');
    const uploadSpinner = document.getElementById('uploadSpinner');

    function setLoadingState(loading) {
      if (loading) {
        uploadBtn.disabled = true;
        uploadText.style.display = 'none';
        uploadSpinner.style.display = 'inline-block';
        uploadForm.classList.add('loading');
      } else {
        uploadBtn.disabled = false;
        uploadText.style.display = 'inline';
        uploadSpinner.style.display = 'none';
        uploadForm.classList.remove('loading');
      }
    }

    function validateForm() {
      let isValid = true;
      const requiredFields = ['title', 'category', 'image'];
      
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
    uploadForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      if (!validateForm()) {
        alert('Please fill in all required fields and select an image.');
        return;
      }
      
      setLoadingState(true);
      
      // Submit form
      this.submit();
    });

    // Reset form handler
    uploadForm.addEventListener('reset', function() {
      imagePreview.classList.remove('show');
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
        uploadForm.dispatchEvent(new Event('submit'));
      }
      
      // Escape to reset
      if (e.key === 'Escape') {
        uploadForm.dispatchEvent(new Event('reset'));
      }
    });

    // Show keyboard shortcuts help
    console.log('Keyboard shortcuts: Ctrl+Enter (Submit), Escape (Reset)');
  </script>
</body>
</html> 