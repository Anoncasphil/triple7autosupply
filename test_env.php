<?php
/**
 * Environment Test File
 * Test if the environment configuration is working properly
 */

// Include the environment configuration
require_once __DIR__ . '/config/environment.php';

echo "<h2>Triple 7 Auto Supply - Environment Test</h2>";

// Test environment loading
Environment::debug();

echo "<h3>Database Configuration Test:</h3>";
echo "<p><strong>DB_HOST:</strong> " . DB_HOST . "</p>";
echo "<p><strong>DB_NAME:</strong> " . DB_NAME . "</p>";
echo "<p><strong>DB_USER:</strong> " . DB_USER . "</p>";
echo "<p><strong>DB_PORT:</strong> " . DB_PORT . "</p>";

echo "<h3>Application Configuration Test:</h3>";
echo "<p><strong>APP_NAME:</strong> " . APP_NAME . "</p>";
echo "<p><strong>APP_URL:</strong> " . APP_URL . "</p>";
echo "<p><strong>APP_ENV:</strong> " . APP_ENV . "</p>";

echo "<h3>Environment Variables Test:</h3>";
echo "<p><strong>UPLOAD_MAX_SIZE:</strong> " . Environment::get('UPLOAD_MAX_SIZE') . "</p>";
echo "<p><strong>ALLOWED_IMAGE_TYPES:</strong> " . Environment::get('ALLOWED_IMAGE_TYPES') . "</p>";
echo "<p><strong>SESSION_SECURE:</strong> " . Environment::get('SESSION_SECURE') . "</p>";

echo "<h3>File System Test:</h3>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Parent Directory:</strong> " . dirname(__DIR__) . "</p>";
echo "<p><strong>Grandparent Directory:</strong> " . dirname(dirname(__DIR__)) . "</p>";

$envPath = __DIR__ . '/../../.env';
echo "<p><strong>Expected .env path:</strong> " . $envPath . "</p>";
echo "<p><strong>Real .env path:</strong> " . realpath($envPath) . "</p>";
echo "<p><strong>.env file exists:</strong> " . (file_exists($envPath) ? 'YES' : 'NO') . "</p>";

if (file_exists($envPath)) {
    echo "<h3>.env File Contents:</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents($envPath)) . "</pre>";
} else {
    echo "<h3>Creating .env file for testing:</h3>";
    $templatePath = __DIR__ . '/config/env.template';
    if (file_exists($templatePath)) {
        $envDir = dirname($envPath);
        if (!is_dir($envDir)) {
            mkdir($envDir, 0755, true);
        }
        if (copy($templatePath, $envPath)) {
            echo "<p style='color: green;'>✅ Created .env file from template</p>";
            echo "<p>Please edit the .env file at: " . $envPath . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create .env file</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Template file not found at: " . $templatePath . "</p>";
    }
}
?> 