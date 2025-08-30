<?php
// Test file to check auth.php for errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing auth.php...\n";

try {
    require_once 'includes/config.php';
    echo "✅ config.php loaded successfully\n";
    
    require_once 'includes/db.php';
    echo "✅ db.php loaded successfully\n";
    
    require_once 'includes/auth.php';
    echo "✅ auth.php loaded successfully\n";
    
    echo "✅ All files loaded without syntax errors\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "❌ File: " . $e->getFile() . "\n";
    echo "❌ Line: " . $e->getLine() . "\n";
}
