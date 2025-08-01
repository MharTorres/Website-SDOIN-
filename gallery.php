<?php
// gallery.php
require_once 'backend/db.php';

// Get category filter
$category_filter = $_GET['category'] ?? '';

// Build query

// Only allow programs, events, sports
$allowed_categories = ['programs', 'events', 'sports'];
$query = "SELECT * FROM gallery WHERE category IN ('programs','events','sports')";
$params = [];
if ($category_filter && in_array($category_filter, $allowed_categories)) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
}
$query .= " ORDER BY uploaded_at DESC";

// Fetch images
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $images = [];
}

// Get all categories for filter
$categories = $allowed_categories;

// Function to get category icon
function getCategoryIcon($category) {
    $icons = [
        'programs' => 'fas fa-graduation-cap',
        'events' => 'fas fa-calendar-alt',
        'sports' => 'fas fa-running'
    ];
    return $icons[strtolower($category)] ?? 'fas fa-image';
}

// Function to get category color
function getCategoryColor($category) {
    $colors = [
        'programs' => '#e83e8c',
        'events' => '#20c997',
        'sports' => '#0dcaf0'
    ];
    return $colors[strtolower($category)] ?? '#6c757d';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" type="image/png" href="deped-seal.png" />
  <title>Gallery - SDOIN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />
  <style>
    .gallery-section {
      padding: 3rem 0;
      background: linear-gradient(135deg,rgba(255, 255, 255, 0.95));
    }

    .gallery-header {
      text-align: center;
      margin-bottom: 3rem;
    }

    .gallery-header h2 {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .gallery-header p {
      color: #6c757d;
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
    }

    .filter-bar {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
    }

    .filter-buttons {
      display: flex;
      justify-content: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .filter-btn {
      padding: 0.75rem 1.5rem;
      border: 2px solid var(--primary-color);
      background: transparent;
      color: var(--primary-color);
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .filter-btn:hover,
    .filter-btn.active {
      background: var(--primary-color);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(37, 117, 252, 0.3);
    }

    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
    }

    .gallery-item {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .gallery-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .gallery-image-container {
      position: relative;
      height: 250px;
      overflow: hidden;
    }

    .gallery-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .gallery-item:hover .gallery-image {
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

    .gallery-item:hover .image-overlay {
      opacity: 1;
    }

    .overlay-content {
      text-align: center;
      color: white;
    }

    .overlay-content i {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .gallery-content {
      padding: 1.5rem;
    }

    .gallery-title {
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
      font-size: 1.1rem;
    }

    .gallery-description {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 1rem;
      line-height: 1.4;
    }

    .gallery-meta {
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

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #6c757d;
    }

    .empty-state i {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 1rem;
    }

    .empty-state h3 {
      margin-bottom: 1rem;
      color: #495057;
    }

    .empty-state p {
      margin-bottom: 2rem;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }

    /* Modal styles */
    .modal-image {
      max-width: 100%;
      max-height: 70vh;
      object-fit: contain;
    }

    .modal-title {
      font-weight: 600;
      color: var(--dark-color);
    }

    .modal-description {
      color: #6c757d;
      margin-bottom: 1rem;
    }

    .modal-meta {
      font-size: 0.9rem;
      color: #6c757d;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .gallery-grid {
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
        justify-content: center;
      }

      .gallery-header h2 {
        font-size: 2rem;
      }
    }

    /* Animation for gallery items */
    .gallery-item {
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
          <li class="nav-item"><a class="nav-link active" href="gallery.php"><i class="fas fa-images me-1"></i>Gallery</a></li>
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

  <section class="gallery-section">
    <div class="container">
      <div class="gallery-header">
        <h2><i class="fas fa-images me-2"></i>Media Gallery</h2>
        <p>Explore photos from our programs, initiatives, school events, and sports activities showcasing the vibrant educational community of SDOIN.</p>
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(8px);">
        <div class="filter-buttons">
          <a href="gallery.php" 
             class="filter-btn <?= !$category_filter ? 'active' : '' ?>"
             style="border-color: #2575fc; color: #2575fc;">
            <i class="fas fa-th-large"></i> All Images
          </a>
          <?php foreach ($categories as $category): ?>
            <a href="gallery.php?category=<?= urlencode($category) ?>" 
               class="filter-btn <?= $category_filter === $category ? 'active' : '' ?>"
               style="border-color: <?= getCategoryColor($category) ?>; color: <?= getCategoryColor($category) ?>;">
              <i class="<?= getCategoryIcon($category) ?>"></i><?= ucfirst(htmlspecialchars($category)) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Gallery Grid -->
      <?php if (empty($images)): ?>
        <div class="empty-state">
          <i class="fas fa-images"></i>
          <h3>No Images Available</h3>
          <p>No images have been uploaded to this category yet. Please check back later or browse other categories.</p>
          <a href="gallery.php" class="btn btn-primary">
            <i class="fas fa-th-large me-1"></i>View All Images
          </a>
        </div>
      <?php else: ?>
        <div class="gallery-grid">
          <?php foreach ($images as $image): ?>
            <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#imageModal" 
                 data-image="<?= htmlspecialchars($image['filename']) ?>"
                 data-title="<?= htmlspecialchars($image['title']) ?>"
                 data-description="<?= htmlspecialchars($image['description']) ?>"
                 data-category="<?= htmlspecialchars($image['category']) ?>"
                 data-date="<?= date('F j, Y', strtotime($image['uploaded_at'])) ?>">
              <div class="gallery-image-container">
                <img src="uploads/gallery/<?= urlencode($image['filename']) ?>" 
                     alt="<?= htmlspecialchars($image['title']) ?>" 
                     class="gallery-image">
                <div class="image-overlay">
                  <div class="overlay-content">
                    <i class="fas fa-expand"></i>
                    <div>Click to view</div>
                  </div>
                </div>
              </div>
              <div class="gallery-content">
                <div class="gallery-title"><?= htmlspecialchars($image['title']) ?></div>
                <div class="gallery-description"><?= htmlspecialchars($image['description']) ?: 'No description available' ?></div>
                <div class="gallery-meta">
                  <span class="category-badge" style="background: <?= getCategoryColor($image['category']) ?>20; color: <?= getCategoryColor($image['category']) ?>;">
                    <i class="<?= getCategoryIcon($image['category']) ?>"></i>
                    <?= htmlspecialchars(ucfirst($image['category'])) ?>
                  </span>
                  <span><?= date('M j, Y', strtotime($image['uploaded_at'])) ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Image Modal -->
  <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="imageModalLabel"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img id="modalImage" class="modal-image" alt="">
          <div class="modal-description mt-3"></div>
          <div class="modal-meta">
            <span id="modalCategory" class="category-badge me-2"></span>
            <span id="modalDate"></span>
          </div>
        </div>
        <div class="modal-footer">
          <a href="#" id="downloadLink" class="btn btn-primary" download>
            <i class="fas fa-download me-1"></i>Download
          </a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
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
    <div class="footer-note">
      &copy; 2025 Schools Division Office of Ilocos Norte | Designed with love for learners and educators.
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
  <script>
    // Modal functionality
    const imageModal = document.getElementById('imageModal');
    if (imageModal) {
      imageModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const image = button.getAttribute('data-image');
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');
        const category = button.getAttribute('data-category');
        const date = button.getAttribute('data-date');

        const modalTitle = imageModal.querySelector('.modal-title');
        const modalImage = imageModal.querySelector('#modalImage');
        const modalDescription = imageModal.querySelector('.modal-description');
        const modalCategory = imageModal.querySelector('#modalCategory');
        const modalDate = imageModal.querySelector('#modalDate');
        const downloadLink = imageModal.querySelector('#downloadLink');

        modalTitle.textContent = title;
        modalImage.src = 'uploads/gallery/' + image;
        modalImage.alt = title;
        modalDescription.textContent = description || 'No description available';
        modalCategory.textContent = category.charAt(0).toUpperCase() + category.slice(1);
        modalDate.textContent = date;
        downloadLink.href = 'uploads/gallery/' + image;
        downloadLink.download = title + '.' + image.split('.').pop();
      });
    }

    // Add animation delay to gallery items
    document.querySelectorAll('.gallery-item').forEach((item, index) => {
      item.style.animationDelay = `${index * 0.1}s`;
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
      // Escape to close modal
      if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(imageModal);
        if (modal) {
          modal.hide();
        }
      }
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 