<?php
/**
 * Add Users Script for Triple7 Auto
 * This script will add default users to the existing users table
 */

echo "<h2>👥 Adding Users to Triple7 Auto</h2>";

// Include database connection
require_once 'config/database.php';

try {
    $db = db();
    echo "✅ Database connection successful!<br>";
    
    // Check if users table exists
    if ($db->tableExists('users')) {
        echo "✅ Users table exists<br>";
        
        // Count existing users
        $users = $db->fetchAll("SELECT COUNT(*) as count FROM users");
        $userCount = $users[0]['count'];
        echo "📊 Current users in table: $userCount<br>";
        
        if ($userCount > 0) {
            echo "<h3>Existing Users:</h3>";
            $existingUsers = $db->fetchAll("SELECT id, username, email, role FROM users");
            foreach ($existingUsers as $user) {
                echo "- ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}<br>";
            }
        }
        
        echo "<h3>Adding Default Users...</h3>";
        
        // Check if admin user already exists
        $adminExists = $db->fetch("SELECT id FROM users WHERE username = 'admin' OR email = 'admin@triple7auto.com'");
        
        if (!$adminExists) {
            // Insert default admin user
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, first_name, last_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $adminId = $db->insert($sql, ['admin', 'Admin', 'User', 'admin@triple7auto.com', $adminPassword, 'admin', 'active']);
            echo "✅ Admin user created with ID: $adminId<br>";
            echo "📧 Email: admin@triple7auto.com<br>";
            echo "🔑 Password: admin123<br>";
        } else {
            echo "ℹ️ Admin user already exists<br>";
        }
        
        // Check if staff users exist
        $johnExists = $db->fetch("SELECT id FROM users WHERE username = 'john.manager' OR email = 'john@triple7auto.com'");
        $sarahExists = $db->fetch("SELECT id FROM users WHERE username = 'sarah.staff' OR email = 'sarah@triple7auto.com'");
        
        if (!$johnExists) {
            // Insert John Manager
            $staffPassword = password_hash('staff123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, first_name, last_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $johnId = $db->insert($sql, ['john.manager', 'John', 'Manager', 'john@triple7auto.com', $staffPassword, 'staff', 'active']);
            echo "✅ John Manager created with ID: $johnId<br>";
        } else {
            echo "ℹ️ John Manager already exists<br>";
        }
        
        if (!$sarahExists) {
            // Insert Sarah Staff
            $staffPassword = password_hash('staff123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, first_name, last_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $sarahId = $db->insert($sql, ['sarah.staff', 'Sarah', 'Staff', 'sarah@triple7auto.com', $staffPassword, 'staff', 'inactive']);
            echo "✅ Sarah Staff created with ID: $sarahId<br>";
        } else {
            echo "ℹ️ Sarah Staff already exists<br>";
        }
        
        // Show final user count
        $finalUsers = $db->fetchAll("SELECT COUNT(*) as count FROM users");
        $finalCount = $finalUsers[0]['count'];
        echo "<h3>📊 Final Results:</h3>";
        echo "Total users in table: $finalCount<br>";
        
        if ($finalCount > 0) {
            echo "<h3>All Users in Database:</h3>";
            $allUsers = $db->fetchAll("SELECT id, username, first_name, last_name, email, role, status FROM users ORDER BY id");
            foreach ($allUsers as $user) {
                echo "- ID: {$user['id']}, Name: {$user['first_name']} {$user['last_name']}, Email: {$user['email']}, Role: {$user['role']}, Status: {$user['status']}<br>";
            }
        }
        
        echo "<h3>✅ Users Added Successfully!</h3>";
        echo "<p>You can now:</p>";
        echo "<ul>";
        echo "<li><a href='admin/users/users.php'>View Users Page</a></li>";
        echo "<li><a href='http://localhost/phpmyadmin' target='_blank'>Open phpMyAdmin</a></li>";
        echo "</ul>";
        
    } else {
        echo "❌ Users table does not exist! Please run setup_database.php first.<br>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6;'>";
    echo "<h3>❌ Error Adding Users</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?> 