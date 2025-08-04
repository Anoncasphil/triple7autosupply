<?php
/**
 * Triple 7 Auto Supply - Setup Script
 * This script helps users configure their environment
 */

// Prevent direct access if already configured
if (file_exists(__DIR__ . '/config/.env')) {
    echo "Environment already configured. Remove config/.env to run setup again.\n";
    exit(1);
}

echo "=== Triple 7 Auto Supply Setup ===\n\n";

// Check if env.template exists
if (!file_exists(__DIR__ . '/config/env.template')) {
    echo "Error: config/env.template not found!\n";
    exit(1);
}

// Copy template to .env
if (copy(__DIR__ . '/config/env.template', __DIR__ . '/config/.env')) {
    echo "✅ Environment template copied to config/.env\n";
} else {
    echo "❌ Failed to create config/.env\n";
    exit(1);
}

echo "\n📝 Please edit config/.env with your database settings:\n";
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
echo "   - Ensure config/.env is not accessible via web\n\n";

echo "🎉 Setup complete! Edit config/.env and start using the system.\n";
?> 