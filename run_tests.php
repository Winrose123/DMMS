<?php
require_once __DIR__ . '/tests/TestCase.php';

// Get all test files
$testFiles = glob(__DIR__ . '/tests/*Test.php');
$totalTests = 0;
$passedTests = 0;
$failedTests = [];

echo "\nRunning DMMS Tests...\n";
echo "===================\n\n";

foreach ($testFiles as $testFile) {
    require_once $testFile;
    
    // Get the class name from the file name
    $className = '\\DMMS\\Tests\\' . basename($testFile, '.php');
    
    if (class_exists($className)) {
        $testClass = new $className();
        $methods = get_class_methods($testClass);
        
        // Find and run test methods
        foreach ($methods as $method) {
            if (strpos($method, 'test') === 0) {
                $totalTests++;
                try {
                    if (method_exists($testClass, 'setUp')) {
                        $testClass->setUp();
                    }
                    
                    $testClass->$method();
                    echo ".";
                    $passedTests++;
                    
                    if (method_exists($testClass, 'tearDown')) {
                        $testClass->tearDown();
                    }
                } catch (Exception $e) {
                    echo "F";
                    $failedTests[] = [
                        'class' => basename($testFile, '.php'),
                        'method' => $method,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }
    }
}

echo "\n\nTest Results\n";
echo "============\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . count($failedTests) . "\n\n";

if (!empty($failedTests)) {
    echo "Failed Tests:\n";
    echo "============\n";
    foreach ($failedTests as $failure) {
        echo "{$failure['class']}::{$failure['method']}\n";
        echo "Error: {$failure['error']}\n\n";
    }
}

exit(count($failedTests) > 0 ? 1 : 0);
