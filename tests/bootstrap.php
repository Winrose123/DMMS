<?php
require_once __DIR__ . '/../database/connection.php';

// Set up autoloading for test classes
spl_autoload_register(function ($class) {
    // Convert namespace separators to directory separators
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    
    // Check in tests directory
    if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $file)) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . $file;
        return;
    }
    
    // Check in src directory
    if (file_exists(__DIR__ . '/../src/' . $file)) {
        require_once __DIR__ . '/../src/' . $file;
        return;
    }
});
