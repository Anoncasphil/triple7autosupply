<?php
/**
 * Environment Configuration
 * Load environment variables from .env file or use defaults
 */

class Environment {
    private static $variables = [];
    
    /**
     * Load environment variables
     */
    public static function load($envFile = null) {
        if ($envFile === null) {
            $envFile = __DIR__ . '/.env';
        }
        
        // Load from .env file if it exists
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse key=value pairs
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    
                    self::$variables[$key] = $value;
                }
            }
        }
        
        // Set default values if not found in .env
        self::setDefaults();
    }
    
    /**
     * Set default environment variables
     */
    private static function setDefaults() {
        $defaults = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'triple7',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_PORT' => '3307',
            'DB_CHARSET' => 'utf8mb4',
            'APP_NAME' => 'Triple7 Auto',
            'APP_URL' => 'http://localhost/triple7auto',
            'APP_ENV' => 'development',
            'APP_KEY' => 'your-secret-key-here-change-this-in-production',
            'UPLOAD_MAX_SIZE' => '5242880',
            'ALLOWED_IMAGE_TYPES' => 'jpg,jpeg,png,gif,webp',
            'SESSION_LIFETIME' => '3600',
            'SESSION_SECURE' => 'false',
            'DISPLAY_ERRORS' => 'true',
            'LOG_ERRORS' => 'true'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset(self::$variables[$key])) {
                self::$variables[$key] = $value;
            }
        }
    }
    
    /**
     * Get environment variable
     */
    public static function get($key, $default = null) {
        return self::$variables[$key] ?? $default;
    }
    
    /**
     * Set environment variable
     */
    public static function set($key, $value) {
        self::$variables[$key] = $value;
    }
    
    /**
     * Check if environment variable exists
     */
    public static function has($key) {
        return isset(self::$variables[$key]);
    }
    
    /**
     * Get all environment variables
     */
    public static function all() {
        return self::$variables;
    }
}

// Load environment variables
Environment::load();

// Define constants for backward compatibility
if (!defined('DB_HOST')) define('DB_HOST', Environment::get('DB_HOST'));
if (!defined('DB_NAME')) define('DB_NAME', Environment::get('DB_NAME'));
if (!defined('DB_USER')) define('DB_USER', Environment::get('DB_USER'));
if (!defined('DB_PASS')) define('DB_PASS', Environment::get('DB_PASS'));
if (!defined('DB_PORT')) define('DB_PORT', Environment::get('DB_PORT'));
if (!defined('DB_CHARSET')) define('DB_CHARSET', Environment::get('DB_CHARSET'));
if (!defined('APP_NAME')) define('APP_NAME', Environment::get('APP_NAME'));
if (!defined('APP_URL')) define('APP_URL', Environment::get('APP_URL'));
if (!defined('APP_ENV')) define('APP_ENV', Environment::get('APP_ENV'));
if (!defined('APP_KEY')) define('APP_KEY', Environment::get('APP_KEY'));
?> 