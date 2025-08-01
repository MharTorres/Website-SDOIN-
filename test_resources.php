<?php
// test_resources.php - Simple test to verify database connection and resources
require_once 'backend/db.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test database connection
    echo "<p>✅ Database connection successful!</p>";
    
    // Check if resources table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'resources'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Resources table exists!</p>";
        
        // Count resources
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM resources");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p>📊 Total resources in database: $count</p>";
        
        // Show sample resources
        if ($count > 0) {
            echo "<h2>Sample Resources:</h2>";
            $stmt = $pdo->query("SELECT * FROM resources ORDER BY uploaded_at DESC LIMIT 5");
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Title</th><th>Category</th><th>File</th><th>Uploaded</th></tr>";
            foreach ($resources as $resource) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($resource['id']) . "</td>";
                echo "<td>" . htmlspecialchars($resource['title']) . "</td>";
                echo "<td>" . htmlspecialchars($resource['category']) . "</td>";
                echo "<td>" . htmlspecialchars($resource['filename']) . "</td>";
                echo "<td>" . htmlspecialchars($resource['uploaded_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>⚠️ No resources found in database. You can add some through the admin panel.</p>";
        }
    } else {
        echo "<p>❌ Resources table does not exist!</p>";
        echo "<p>You may need to create the table. Here's the SQL:</p>";
        echo "<pre>";
        echo "CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    filename VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    filetype VARCHAR(100),
    filesize INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='resources.php'>View Resources Page</a> | <a href='backend/resources_admin.php'>Admin Panel</a></p>";
?> 