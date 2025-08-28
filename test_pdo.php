<?php
echo "Available PDO drivers:\n";
print_r(PDO::getAvailableDrivers());

echo "\nLoaded PHP extensions:\n";
$extensions = get_loaded_extensions();
foreach($extensions as $ext) {
    if(stripos($ext, 'pdo') !== false) {
        echo "- $ext\n";
    }
}

echo "\nPHP Version: " . phpversion() . "\n";
?>
