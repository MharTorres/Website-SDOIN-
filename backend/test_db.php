<?php
require 'db.php';

echo "Database connection test:<br>";

try {
    // Test if we can query the contents table
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM contents");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "Database connection: SUCCESS<br>";
    echo "Contents table has " . $result['count'] . " records<br>";
    
    // Test a specific key
    $stmt = $pdo->prepare("SELECT * FROM contents WHERE `key` = 'programs_title'");
    $stmt->execute();
    $row = $stmt->fetch();
    
    if ($row) {
        echo "Key 'programs_title' found: " . $row['value'] . "<br>";
    } else {
        echo "Key 'programs_title' NOT found<br>";
    }
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?> 