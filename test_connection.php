<?php
/**
 * Database Connection Test
 * Test if the database connection is working on the live server
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Triple 7 Auto Supply - Database Connection Test</h2>";

try {
    // Include database configuration
    require_once __DIR__ . '/config/database.php';
    
    echo "<h3>✅ Environment Configuration Loaded</h3>";
    echo "<p><strong>DB_HOST:</strong> " . DB_HOST . "</p>";
    echo "<p><strong>DB_NAME:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>DB_USER:</strong> " . DB_USER . "</p>";
    echo "<p><strong>DB_PORT:</strong> " . DB_PORT . "</p>";
    
    // Test database connection
    echo "<h3>🔍 Testing Database Connection...</h3>";
    
    $db = db();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test a simple query
    echo "<h3>🔍 Testing Database Query...</h3>";
    $result = $db->fetch("SELECT COUNT(*) as count FROM products");
    echo "<p style='color: green;'>✅ Query successful! Products count: " . $result['count'] . "</p>";
    
    // Test if tables exist
    echo "<h3>🔍 Checking Database Tables...</h3>";
    $tables = $db->fetchAll("SHOW TABLES");
    echo "<p style='color: green;'>✅ Tables found:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>" . $tableName . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<p style='color: red;'><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p style='color: red;'><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "<p style='color: red;'><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p style='color: red;'><strong>Line:</strong> " . $e->getLine() . "</p>";
    
    // Show backtrace for debugging
    echo "<h3>🔍 Backtrace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Test PHP configuration
echo "<h3>🔍 PHP Configuration:</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? '✅ Available' : '❌ Not Available') . "</p>";
echo "<p><strong>Error Reporting:</strong> " . error_reporting() . "</p>";
echo "<p><strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>";
echo "<p><strong>Log Errors:</strong> " . (ini_get('log_errors') ? 'On' : 'Off') . "</p>";
echo "<p><strong>Error Log:</strong> " . ini_get('error_log') . "</p>";

// Test if we can connect to MySQL without database
echo "<h3>🔍 Testing MySQL Server Connection...</h3>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "<p style='color: green;'>✅ MySQL server connection successful!</p>";
    
    // Test if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Database '" . DB_NAME . "' exists!</p>";
    } else {
        echo "<p style='color: red;'>❌ Database '" . DB_NAME . "' does not exist!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL server connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 