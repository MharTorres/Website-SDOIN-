<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

class SearchAPI {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function search($query, $filters = []) {
        $results = [];
        $query = trim($query);
        
        if (empty($query)) {
            return ['results' => [], 'total' => 0, 'query' => $query];
        }
        
        // Search in gallery
        $galleryResults = $this->searchGallery($query, $filters);
        $results = array_merge($results, $galleryResults);
        
        // Search in resources
        $resourceResults = $this->searchResources($query, $filters);
        $results = array_merge($results, $resourceResults);
        
        // Search in static content
        $staticResults = $this->searchStaticContent($query, $filters);
        $results = array_merge($results, $staticResults);
        
        // Sort results by relevance
        usort($results, function($a, $b) {
            return $b['relevance'] - $a['relevance'];
        });
        
        return [
            'results' => $results,
            'total' => count($results),
            'query' => $query
        ];
    }
    
    private function searchGallery($query, $filters) {
        $results = [];
        $searchTerms = explode(' ', strtolower($query));
        
        try {
            $sql = "SELECT * FROM gallery WHERE 1=1";
            $params = [];
            
            // Add search conditions
            $searchConditions = [];
            foreach ($searchTerms as $term) {
                $searchConditions[] = "(LOWER(title) LIKE ? OR LOWER(description) LIKE ? OR LOWER(category) LIKE ?)";
                $params[] = "%$term%";
                $params[] = "%$term%";
                $params[] = "%$term%";
            }
            
            if (!empty($searchConditions)) {
                $sql .= " AND (" . implode(' OR ', $searchConditions) . ")";
            }
            
            // Add category filter
            if (!empty($filters['category'])) {
                $sql .= " AND category = ?";
                $params[] = $filters['category'];
            }
            
            $sql .= " ORDER BY uploaded_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $relevance = $this->calculateRelevance($row, $searchTerms);
                
                $results[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'url' => 'gallery.php',
                    'type' => 'gallery',
                    'category' => $row['category'],
                    'image' => 'uploads/gallery/' . $row['filename'],
                    'date' => $row['uploaded_at'],
                    'relevance' => $relevance
                ];
            }
        } catch (PDOException $e) {
            error_log("Gallery search error: " . $e->getMessage());
        }
        
        return $results;
    }
    
    private function searchResources($query, $filters) {
        $results = [];
        $searchTerms = explode(' ', strtolower($query));
        
        try {
            // Check if resources table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'resources'");
            if ($stmt->rowCount() > 0) {
                $sql = "SELECT * FROM resources WHERE 1=1";
                $params = [];
                
                // Add search conditions
                $searchConditions = [];
                foreach ($searchTerms as $term) {
                    $searchConditions[] = "(LOWER(title) LIKE ? OR LOWER(description) LIKE ? OR LOWER(category) LIKE ?)";
                    $params[] = "%$term%";
                    $params[] = "%$term%";
                    $params[] = "%$term%";
                }
                
                if (!empty($searchConditions)) {
                    $sql .= " AND (" . implode(' OR ', $searchConditions) . ")";
                }
                
                $sql .= " ORDER BY uploaded_at DESC";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $relevance = $this->calculateRelevance($row, $searchTerms);
                    
                    $results[] = [
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'url' => 'resources.php',
                        'type' => 'resource',
                        'category' => $row['category'] ?? 'general',
                        'file' => 'uploads/resources/' . $row['filename'],
                        'date' => $row['uploaded_at'],
                        'relevance' => $relevance
                    ];
                }
            }
        } catch (PDOException $e) {
            error_log("Resources search error: " . $e->getMessage());
        }
        
        return $results;
    }
    
    private function searchStaticContent($query, $filters) {
        $results = [];
        $searchTerms = explode(' ', strtolower($query));
        
        // Define static pages and their content
        $staticPages = [
            'about' => [
                'title' => 'About SDOIN',
                'url' => 'about.html',
                'keywords' => ['about', 'sdoin', 'schools division', 'ilocos norte', 'education', 'deped'],
                'description' => 'Learn about the Schools Division of Ilocos Norte, our mission, vision, and commitment to quality education.'
            ],
            'programs' => [
                'title' => 'Educational Programs',
                'url' => 'programs.html',
                'keywords' => ['programs', 'education', 'toured', 'inherited', 'learning', 'curriculum'],
                'description' => 'Explore our educational programs including TourEd and INheritEd initiatives.'
            ],
            'initiatives' => [
                'title' => 'Key Initiatives',
                'url' => 'initiatives.html',
                'keywords' => ['initiatives', 'projects', 'innovation', 'development', 'improvement'],
                'description' => 'Discover our key initiatives and projects aimed at improving education in Ilocos Norte.'
            ],
            'news' => [
                'title' => 'News and Updates',
                'url' => 'news.html',
                'keywords' => ['news', 'updates', 'announcements', 'events', 'activities'],
                'description' => 'Stay updated with the latest news, announcements, and events from SDOIN.'
            ],
            'resources' => [
                'title' => 'Educational Resources',
                'url' => 'resources.php',
                'keywords' => ['resources', 'materials', 'documents', 'files', 'downloads'],
                'description' => 'Access educational resources, documents, and materials for teachers and students.'
            ],
            'gallery' => [
                'title' => 'Photo Gallery',
                'url' => 'gallery.php',
                'keywords' => ['gallery', 'photos', 'images', 'pictures', 'visual'],
                'description' => 'Browse through our photo gallery showcasing events, activities, and achievements.'
            ],
            'contact' => [
                'title' => 'Contact Information',
                'url' => 'send-inquiry.html',
                'keywords' => ['contact', 'inquiry', 'email', 'phone', 'address', 'location'],
                'description' => 'Get in touch with SDOIN. Find our contact information and send inquiries.'
            ]
        ];
        
        foreach ($staticPages as $key => $page) {
            $relevance = 0;
            $matchedTerms = [];
            
            // Check title match
            foreach ($searchTerms as $term) {
                if (strpos(strtolower($page['title']), $term) !== false) {
                    $relevance += 10;
                    $matchedTerms[] = $term;
                }
            }
            
            // Check keywords match
            foreach ($searchTerms as $term) {
                if (in_array($term, $page['keywords'])) {
                    $relevance += 8;
                    $matchedTerms[] = $term;
                }
            }
            
            // Check description match
            foreach ($searchTerms as $term) {
                if (strpos(strtolower($page['description']), $term) !== false) {
                    $relevance += 5;
                    $matchedTerms[] = $term;
                }
            }
            
            // Check URL/key match
            foreach ($searchTerms as $term) {
                if (strpos(strtolower($key), $term) !== false) {
                    $relevance += 3;
                    $matchedTerms[] = $term;
                }
            }
            
            if ($relevance > 0) {
                $results[] = [
                    'id' => $key,
                    'title' => $page['title'],
                    'description' => $page['description'],
                    'url' => $page['url'],
                    'type' => 'page',
                    'category' => 'general',
                    'relevance' => $relevance,
                    'matched_terms' => array_unique($matchedTerms)
                ];
            }
        }
        
        return $results;
    }
    
    private function calculateRelevance($row, $searchTerms) {
        $relevance = 0;
        
        // Title match (highest weight)
        foreach ($searchTerms as $term) {
            if (strpos(strtolower($row['title']), $term) !== false) {
                $relevance += 10;
            }
        }
        
        // Description match
        if (isset($row['description'])) {
            foreach ($searchTerms as $term) {
                if (strpos(strtolower($row['description']), $term) !== false) {
                    $relevance += 5;
                }
            }
        }
        
        // Category match
        if (isset($row['category'])) {
            foreach ($searchTerms as $term) {
                if (strpos(strtolower($row['category']), $term) !== false) {
                    $relevance += 3;
                }
            }
        }
        
        // Recency bonus (newer content gets slight boost)
        if (isset($row['uploaded_at'])) {
            $uploadDate = new DateTime($row['uploaded_at']);
            $now = new DateTime();
            $daysDiff = $now->diff($uploadDate)->days;
            if ($daysDiff <= 30) {
                $relevance += 1;
            }
        }
        
        return $relevance;
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = $_GET['q'] ?? '';
    $category = $_GET['category'] ?? '';
    
    $filters = [];
    if (!empty($category)) {
        $filters['category'] = $category;
    }
    
    $searchAPI = new SearchAPI($pdo);
    $results = $searchAPI->search($query, $filters);
    
    echo json_encode($results);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 