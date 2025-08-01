<?php
// resources.php - Display resources from database
require_once 'backend/db.php';

// Fetch all resources from database
$stmt = $pdo->query("SELECT * FROM resources ORDER BY uploaded_at DESC");
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filtering
$categories = [];
foreach ($resources as $resource) {
    if (!in_array($resource['category'], $categories)) {
        $categories[] = $resource['category'];
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

// Function to get category icon
function getCategoryIcon($category) {
    $icons = [
        'guides' => 'fas fa-chalkboard-teacher',
        'documents' => 'fas fa-file-alt',
        'tools' => 'fas fa-toolbox',
        'reports' => 'fas fa-chart-line',
        'advocacy' => 'fas fa-bullhorn',
        'policy' => 'fas fa-file-contract',
        'parents' => 'fas fa-users'
    ];
    return $icons[strtolower($category)] ?? 'fas fa-file';
}

// Function to get category color
function getCategoryColor($category) {
    $colors = [
        'guides' => 'linear-gradient(135deg, #f093fb, #f5576c)',
        'documents' => 'linear-gradient(135deg, #667eea, #764ba2)',
        'tools' => 'linear-gradient(135deg, #45b7d1, #96c93d)',
        'reports' => 'linear-gradient(135deg, #4ecdc4, #44a08d)',
        'advocacy' => 'linear-gradient(135deg, #ff6b6b, #ee5a24)',
        'policy' => 'linear-gradient(135deg, #667eea, #764ba2)',
        'parents' => 'linear-gradient(135deg, #4facfe, #00f2fe)'
    ];
    return $colors[strtolower($category)] ?? 'linear-gradient(135deg, #667eea, #764ba2)';
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Educational resources, guides, and materials from SDOIN - Schools Division of Ilocos Norte">
  <meta name="keywords" content="SDOIN, resources, education, guides, materials, Ilocos Norte, DepEd">
  <meta name="robots" content="index, follow">
  <link rel="icon" type="image/png" href="deped-seal.png" />
  <title>Resources - SDOIN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />
  <style>
    :root {
      --primary-color: #004aad;
      --secondary-color: #3b5bfe;
      --success-color: #198754;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --info-color: #0dcaf0;
      --light-color: #f8f9fa;
      --dark-color: #212529;
    }

    .resources-hero {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 3rem 0;
      text-align: center;
      margin-bottom: 2rem;
    }

    .resources-hero h1 {
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 1rem;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .resources-hero p {
      font-size: 1.1rem;
      opacity: 0.9;
      max-width: 600px;
      margin: 0 auto;
    }

    .resources-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1rem;
    }

    .resources-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
    }

    .resource-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: all 0.3s ease;
      position: relative;
      border: 1px solid #e9ecef;
    }

    .resource-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .resource-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .resource-header {
      padding: 1.5rem;
      border-bottom: 1px solid #f1f3f4;
    }

    .resource-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: white;
      margin-bottom: 1rem;
    }

    .resource-title {
      font-weight: 700;
      font-size: 1.25rem;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
    }

    .resource-description {
      color: #6c757d;
      font-size: 0.95rem;
      line-height: 1.5;
      margin-bottom: 1rem;
    }

    .resource-meta {
      display: flex;
      gap: 1rem;
      font-size: 0.85rem;
      color: #6c757d;
      margin-bottom: 1rem;
    }

    .resource-meta span {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .resource-content {
      padding: 1.5rem;
    }

    .resource-files {
      margin-bottom: 1.5rem;
    }

    .file-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.75rem;
      background: var(--light-color);
      border-radius: 8px;
      margin-bottom: 0.5rem;
      transition: all 0.3s ease;
    }

    .file-item:hover {
      background: #e9ecef;
      transform: translateX(5px);
    }

    .file-info {
      display: flex;
      align-items: center;
      gap: 0.75rem;
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

    .file-details h6 {
      margin: 0;
      font-weight: 600;
      color: var(--dark-color);
    }

    .file-details small {
      color: #6c757d;
      font-size: 0.8rem;
    }

    .file-actions {
      display: flex;
      gap: 0.5rem;
    }

    .btn-download {
      background: linear-gradient(135deg, var(--success-color), #20c997);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-download:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
      color: white;
    }

    .btn-preview {
      background: linear-gradient(135deg, var(--info-color), #0b5ed7);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-preview:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(13, 202, 240, 0.3);
      color: white;
    }

    .resource-categories {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }

    .category-tag {
      background: var(--light-color);
      color: var(--primary-color);
      padding: 0.25rem 0.75rem;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 500;
    }

    .search-filters {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
    }

    .filter-buttons {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      justify-content: center;
    }

    .filter-btn {
      background: white;
      border: 2px solid #e9ecef;
      color: #6c757d;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      font-weight: 500;
    }

    .filter-btn:hover,
    .filter-btn.active {
      background: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
      transform: translateY(-2px);
    }

    .resources-stats {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 2rem;
      border-radius: 16px;
      text-align: center;
      margin-bottom: 2rem;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 2rem;
      margin-top: 1.5rem;
    }

    .stat-item {
      text-align: center;
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .stat-label {
      font-size: 0.9rem;
      opacity: 0.9;
    }

    .no-resources {
      text-align: center;
      padding: 3rem 1rem;
      color: #6c757d;
    }

    .no-resources i {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
      .resources-hero h1 {
        font-size: 2rem;
      }

      .resources-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      .filter-buttons {
        flex-direction: column;
        align-items: center;
      }

      .filter-btn {
        width: 100%;
        max-width: 200px;
        text-align: center;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }

      .file-item {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
      }

      .file-actions {
        justify-content: center;
      }
    }

    /* Animation for cards */
    .resource-card {
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


    /* Focus styles for accessibility */
    .btn:focus,
    .filter-btn:focus {
      outline: 2px solid var(--primary-color);
      outline-offset: 2px;
    }
    .resources-hero {
      margin-bottom: 0 !important;
    }
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .footer {
      margin-top: auto;
      width: 100vw;
      background: darkgray;
      position: relative;
      left: 0;
      bottom: 0;
    }
    .resources-container,
    .resources-stats,
    .resources-hero,
    section:last-of-type {
      margin-bottom: 0 !important;
      padding-bottom: 0 !important;
    }
  </style>
</head>
<body>
<div class="ilocos-banner">"Ragsak ken Rag-omi ti Napudno nga Agserbi"</div>

  <nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="index.html">
        <i class="fas fa-home me-1"></i>Home
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="about.html"><i class="fas fa-info-circle me-1"></i>About</a></li>
          <li class="nav-item"><a class="nav-link" href="programs.html"><i class="fas fa-graduation-cap me-1"></i>Programs</a></li>
          <li class="nav-item"><a class="nav-link" href="initiatives.php"><i class="fas fa-lightbulb me-1"></i>Key Initiatives</a></li>
          <li class="nav-item"><a class="nav-link" href="resources.php"><i class="fas fa-book me-1"></i>Resources</a></li>
          <li class="nav-item"><a class="nav-link" href="news.html"><i class="fas fa-newspaper me-1"></i>News</a></li>
          <li class="nav-item"><a class="nav-link" href="gallery.php"><i class="fas fa-images me-1"></i>Gallery</a></li>
        </ul>

        <div id="searchBarContainer"></div>
      </div>
    </div>
  </nav>

  <div class="header-banner">
    <img src="deped-seal.png" alt="DepEd Logo" class="deped-logo">
    <div class="text-center flex-grow-1">
      <h3 class="fw-bold mb-1">SCHOOL DIVISION OF ILOCOS NORTE (SDOIN)</h3>
    </div>
    <div class="d-flex gap-2">
      <img src="toured-logo.png" alt="TourEd Logo" class="tourlogo">
      <img src="inherited-logo.png" alt="INheritEd Logo" class="inheritelogo">
      <img src="sdoin-great.png" alt="SDOIN Great Logo" class="sdoinlogo">
    </div>
  </div>

  <!-- Resources Hero Section -->
  <div class="resources-hero" style="background: rgba(255,255,255,0.95); color: #000;">
    <div class="container">
      <h1><i class="fas fa-book-open me-2"></i>Educational Resources</h1>
      <p>Access comprehensive materials, guides, and tools to support learning and teaching excellence in Ilocos Norte</p>
      <div class="resources-container">
    <!-- Resources Statistics -->
    <div class="resources-stats">
      <h3><i class="fas fa-chart-bar me-2"></i>Resources Overview</h3>
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-number"><?= count($resources) ?></div>
          <div class="stat-label">Total Resources</div>
        </div>
        <div class="stat-item">
          <div class="stat-number"><?= count($categories) ?></div>
          <div class="stat-label">Categories</div>
        </div>
        <div class="stat-item">
          <div class="stat-number"><?= array_sum(array_column($resources, 'filesize')) >= 1048576 ? number_format(array_sum(array_column($resources, 'filesize')) / 1048576, 1) : number_format(array_sum(array_column($resources, 'filesize')) / 1024, 1) ?></div>
          <div class="stat-label"><?= array_sum(array_column($resources, 'filesize')) >= 1048576 ? 'MB Total' : 'KB Total' ?></div>
        </div>
        <div class="stat-item">
          <div class="stat-number"><?= count(array_filter($resources, function($r) { return strtotime($r['uploaded_at']) >= strtotime('-30 days'); })) ?></div>
          <div class="stat-label">New This Month</div>
        </div>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="search-filters">
      <h4 class="text-center mb-3"><i class="fas fa-filter me-2"></i>Filter Resources</h4>
      <div class="filter-buttons">
        <a href="#" class="filter-btn active" data-filter="all">
          <i class="fas fa-th-large me-1"></i>All Resources (<?= count($resources) ?>)
        </a>
        <?php foreach ($categories as $category): ?>
          <a href="#" class="filter-btn" data-filter="<?= strtolower($category) ?>">
            <i class="<?= getCategoryIcon($category) ?> me-1"></i><?= ucfirst($category) ?> (<?= count(array_filter($resources, function($r) use ($category) { return strtolower($r['category']) === strtolower($category); })) ?>)
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Resources Grid -->
    <div class="resources-grid" id="resourcesGrid">
      <?php if (empty($resources)): ?>
        <div class="no-resources">
          <i class="fas fa-folder-open"></i>
          <h3>No Resources Available</h3>
          <p>No resources have been uploaded yet. Please check back later.</p>
        </div>
      <?php else: ?>
        <?php foreach ($resources as $resource): ?>
          <div class="resource-card" data-category="<?= strtolower($resource['category']) ?>">
            <div class="resource-header">
              <div class="resource-icon" style="background: <?= getCategoryColor($resource['category']) ?>;">
                <i class="<?= getCategoryIcon($resource['category']) ?>"></i>
              </div>
              <h5 class="resource-title"><?= htmlspecialchars($resource['title']) ?></h5>
              <p class="resource-description"><?= htmlspecialchars($resource['description']) ?></p>
              <div class="resource-meta">
                <span><i class="fas fa-calendar me-1"></i>Updated: <?= date('M Y', strtotime($resource['updated_at'])) ?></span>
                <span><i class="fas fa-file me-1"></i><?= formatFileSize($resource['filesize']) ?></span>
              </div>
              <div class="resource-categories">
                <span class="category-tag"><?= htmlspecialchars($resource['category']) ?></span>
              </div>
            </div>
            <div class="resource-content">
              <div class="resource-files">
                <div class="file-item">
                  <div class="file-info">
                    <div class="file-icon" style="background: <?= strpos($resource['filetype'], 'pdf') !== false ? '#dc3545' : (strpos($resource['filetype'], 'word') !== false ? '#0d6efd' : '#198754') ?>;">
                      <i class="<?= getFileIcon($resource['filetype']) ?>"></i>
                    </div>
                    <div class="file-details">
                      <h6><?= htmlspecialchars($resource['title']) ?></h6>
                      <small><?= strtoupper(pathinfo($resource['filename'], PATHINFO_EXTENSION)) ?> • <?= formatFileSize($resource['filesize']) ?></small>
                    </div>
                  </div>
                  <div class="file-actions">
                    <a href="uploads/resources/<?= urlencode($resource['filename']) ?>" download class="btn-download">
                      <i class="fas fa-download"></i>Download
                    </a>
                    <?php if (strpos($resource['filetype'], 'pdf') !== false): ?>
                      <a href="uploads/resources/<?= urlencode($resource['filename']) ?>" target="_blank" class="btn-preview">
                        <i class="fas fa-eye"></i>Preview
                      </a>
                    <?php else: ?>
                      <a href="uploads/resources/<?= urlencode($resource['filename']) ?>" target="_blank" class="btn-preview" onclick="return confirm('This file will open in a new tab. Some file types may download instead of previewing.')">
                        <i class="fas fa-external-link-alt"></i>Open
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer class="footer text-center-footer">
  <section id="contact" class="contact-section text-black">
    <div class="container py-4">
      <div class="row">
        <div class="col-md-6">
          <h5>Contacts</h5>
          <p><strong>Address:</strong> Brgy. 7B, Giron Street, Laoag City, Ilocos Norte</p>
          <p><strong>Email:</strong> <a href="mailto:ilocos.norte@deped.gov.ph" class="text-black text-decoration-underline">ilocos.norte@deped.gov.ph</a></p>
          <p><strong>Phone:</strong> <a href="tel:(077)771-0960">(077) 771-0960</a></p>
          <p><strong>Facebook:</strong> <a href="https://www.facebook.com/depedtayoilocosnorte/" target="_blank" class="text-black text-decoration-underline">DepEd Tayo Ilocos Norte</a></p>
        </div>
        <div class="col-md-6">
          <img src="deped-seal.png" alt="DepEd Logo" class="deped-logo2">
          <h5>Get in Touch</h5>
          <a href="send-inquiry.html" class="btn btn-primary text-white">Send an Inquiry</a>
        </div>
      </div>
    </div>
  </section>
  <div class="footer-note"> &copy; 2025 Schools Division Office of Ilocos Norte | Designed with love for learners and educators.</div>
</footer>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const resourceCards = document.querySelectorAll('.resource-card');
    
    filterButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active filter
        filterButtons.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        
        resourceCards.forEach(card => {
          if (filter === 'all' || card.dataset.category === filter) {
            card.style.display = 'block';
            card.style.animation = 'slideInUp 0.5s ease-out';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });

    // Download tracking
    document.querySelectorAll('.btn-download').forEach(btn => {
      btn.addEventListener('click', function() {
        const fileName = this.closest('.file-item').querySelector('h6').textContent;
        console.log(`Downloading: ${fileName}`);
        
        // You can add analytics tracking here
        // trackDownload(fileName);
      });
    });

    // Search functionality (if needed)
    const searchInput = document.querySelector('input[name="q"]');
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.resource-card');
        
        cards.forEach(card => {
          const title = card.querySelector('.resource-title').textContent.toLowerCase();
          const description = card.querySelector('.resource-description').textContent.toLowerCase();
          
          if (title.includes(searchTerm) || description.includes(searchTerm)) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
      });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + F to focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        if (searchInput) {
          searchInput.focus();
        }
      }
    });

    // Add loading animation to cards
    resourceCards.forEach((card, index) => {
      card.style.animationDelay = `${index * 0.1}s`;
    });
  });
</script>
<script src="search_bar.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchContainer = document.getElementById('searchBarContainer');
  if (searchContainer) {
    new SDOINSearchBar(searchContainer, {
      placeholder: 'Search SDOIN...',
      showSuggestions: true,
      autoComplete: true
    });
  }
});
</script>

<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 