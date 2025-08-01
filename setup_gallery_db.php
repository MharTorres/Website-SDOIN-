<?php
// setup_gallery_db.php
// This script creates the gallery table in your MySQL database

// Database configuration - Update these values according to your setup
$host = 'localhost';
$dbname = 'ojtproject'; // Update this to your database name
$username = 'root';     // Update this to your MySQL username
$password = '';         // Update this to your MySQL password

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL database successfully!\n\n";
    
    // Create gallery table
    $sql = "CREATE TABLE IF NOT EXISTS `gallery` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `filename` VARCHAR(255) NOT NULL,
        `category` ENUM('programs', 'initiatives', 'events', 'sports') NOT NULL,
        `theme` VARCHAR(100) NULL,
        `filetype` VARCHAR(100) NOT NULL,
        `filesize` INT NOT NULL,
        `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX `idx_category` (`category`),
        INDEX `idx_theme` (`theme`),
        INDEX `idx_uploaded_at` (`uploaded_at`),
        INDEX `idx_title` (`title`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Gallery table created successfully!\n";
    
    // Create uploads/gallery directory
    $uploadDir = __DIR__ . '/uploads/gallery/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        echo "✅ Uploads/gallery directory created successfully!\n";
    } else {
        echo "✅ Uploads/gallery directory already exists!\n";
    }
    
    // Check if table is empty and add sample data
    $stmt = $pdo->query("SELECT COUNT(*) FROM gallery");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample data
        $sampleData = [
            ['Sample Program Event', 'A sample image for educational programs', 'sample_program.jpg', 'programs', null, 'image/jpeg', 1024000],
            ['Sample Initiative - Literacy', 'A sample image for literacy initiative', 'sample_initiative_literacy.jpg', 'initiatives', 'literacy-numeracy', 'image/jpeg', 1024000],
            ['Sample Initiative - Sports', 'A sample image for sports wellness', 'sample_initiative_sports.jpg', 'initiatives', 'sports-wellness', 'image/jpeg', 1024000],
            ['Sample Initiative - Environment', 'A sample image for environment sustainability', 'sample_initiative_env.jpg', 'initiatives', 'environment-sustainability', 'image/jpeg', 1024000],
            ['Sample Initiative - Career', 'A sample image for career community', 'sample_initiative_career.jpg', 'initiatives', 'career-community', 'image/jpeg', 1024000],
            ['Sample Initiative - Technology', 'A sample image for technology learning', 'sample_initiative_tech.jpg', 'initiatives', 'technology-learning', 'image/jpeg', 1024000],
            ['Sample Initiative - Safe Spaces', 'A sample image for safe and resilient learning spaces', 'sample_initiative_safe.jpg', 'initiatives', 'safe-resilient', 'image/jpeg', 1024000],
            ['Sample School Event', 'A sample image for school events', 'sample_event.jpg', 'events', null, 'image/jpeg', 1024000],
            ['Sample Sports Activity', 'A sample image for sports activities', 'sample_sports.jpg', 'sports', null, 'image/jpeg', 1024000]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO gallery (title, description, filename, category, theme, filetype, filesize) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($sampleData as $data) {
            $stmt->execute($data);
        }
        
        echo "✅ Sample data inserted successfully!\n";
    } else {
        echo "ℹ️  Gallery table already contains data (skipping sample data)\n";
    }
    
    // Show table structure
    echo "\n📋 Gallery Table Structure:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("DESCRIBE gallery");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-15s %-15s %-10s %-10s %-10s %-10s\n", 
               $row['Field'], $row['Type'], $row['Null'], $row['Key'], $row['Default'], $row['Extra']);
    }
    
    // Show current data count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "\n📊 Total images in gallery: $total\n";
    
    // Show data by category
    echo "\n📂 Images by Category:\n";
    echo str_repeat("-", 30) . "\n";
    
    $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM gallery GROUP BY category");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo ucfirst($row['category']) . ": " . $row['count'] . " images\n";
    }
    
    echo "\n🎉 Gallery database setup completed successfully!\n";
    echo "\n📝 Next steps:\n";
    echo "1. Access your admin panel at: backend/admin.php\n";
    echo "2. Use the breadcrumb navigation to access 'Manage Gallery'\n";
    echo "3. Start uploading images to your gallery!\n";
    echo "4. Visit gallery.php to see your dynamic gallery\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "1. Make sure your MySQL server is running\n";
    echo "2. Check your database credentials in this script\n";
    echo "3. Ensure the database '$dbname' exists\n";
    echo "4. Verify your MySQL user has CREATE TABLE permissions\n";
}
?> 