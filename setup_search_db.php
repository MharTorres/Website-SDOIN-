<?php
require_once 'backend/db.php';

echo "<h2>SDOIN Search Database Setup</h2>";

try {
    // Read and execute gallery table SQL
    echo "<h3>Setting up Gallery table...</h3>";
    $gallerySQL = file_get_contents('database/gallery_table.sql');
    $statements = explode(';', $gallerySQL);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✓ Gallery table created successfully<br>";
    
    // Read and execute resources table SQL
    echo "<h3>Setting up Resources table...</h3>";
    $resourcesSQL = file_get_contents('database/resources_table.sql');
    $statements = explode(';', $resourcesSQL);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✓ Resources table created successfully<br>";
    
    // Verify tables exist
    echo "<h3>Verifying tables...</h3>";
    $tables = ['gallery', 'resources'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists<br>";
            
            // Count records
            $countStmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $countStmt->fetchColumn();
            echo "  - Records in $table: $count<br>";
        } else {
            echo "✗ Table '$table' not found<br>";
        }
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>The search functionality is now ready to use.</p>";
    echo "<p><a href='search.html'>Go to Search Page</a></p>";
    
} catch (PDOException $e) {
    echo "<h3>Error during setup:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 