<?php
/**
 * Triple 7 Auto Supply - Setup Script
 * This script helps users configure their environment
 */

// Prevent direct access if already configured
if (file_exists(__DIR__ . '/../../.env')) {
    echo "Environment already configured. Remove .env file (two directories up) to run setup again.\n";
    exit(1);
}

echo "=== Triple 7 Auto Supply Setup ===\n\n";

// Check if env.template exists
if (!file_exists(__DIR__ . '/config/env.template')) {
    echo "Error: config/env.template not found!\n";
    exit(1);
}

// Copy template to .env (two directories up)
$envPath = __DIR__ . '/../../.env';
$envDir = dirname($envPath);

// Create directory if it doesn't exist
if (!is_dir($envDir)) {
    if (!mkdir($envDir, 0755, true)) {
        echo "❌ Failed to create directory: $envDir\n";
        exit(1);
    }
}

if (copy(__DIR__ . '/config/env.template', $envPath)) {
    echo "✅ Environment template copied to .env (two directories up)\n";
} else {
    echo "❌ Failed to create .env file at: $envPath\n";
    exit(1);
}

echo "\n📝 Please edit .env file (two directories up) with your database settings:\n";
echo "   - DB_HOST: Your database host (usually 'localhost')\n";
echo "   - DB_NAME: Your database name\n";
echo "   - DB_USER: Your database username\n";
echo "   - DB_PASS: Your database password\n";
echo "   - DB_PORT: Your database port (usually '3306' or '3307' for XAMPP)\n";
echo "   - APP_URL: Your website URL\n";
echo "   - APP_KEY: A random secret key for security\n\n";

echo "🔧 After editing config/.env, you can:\n";
echo "   - Access the website: " . (isset($_SERVER['HTTP_HOST']) ? "http://{$_SERVER['HTTP_HOST']}" : "http://localhost") . "\n";
echo "   - Access admin panel: " . (isset($_SERVER['HTTP_HOST']) ? "http://{$_SERVER['HTTP_HOST']}/login/" : "http://localhost/login/") . "\n\n";

echo "⚠️  Important Security Notes:\n";
echo "   - Change APP_KEY to a random string\n";
echo "   - Set APP_ENV=production for live sites\n";
echo "   - .env file is now outside web directory for security\n\n";

echo "🎉 Setup complete! Edit .env file (two directories up) and start using the system.\n";
?> 