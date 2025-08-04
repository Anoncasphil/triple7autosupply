<?php
/**
 * Example usage of the Database class
 * This file demonstrates how to use the database connection
 */

// Include the database file
require_once __DIR__ . '/database.php';

// Example 1: Using the helper function
try {
    $db = db();
    echo "Database connection successful!\n";
    
    // Example query
    $users = $db->fetchAll("SELECT * FROM users LIMIT 5");
    echo "Found " . count($users) . " users\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 2: Using the Database class directly
try {
    $database = Database::getInstance();
    
    // Insert example
    $sql = "INSERT INTO users (username, email, created_at) VALUES (?, ?, NOW())";
    $userId = $database->insert($sql, ['john_doe', 'john@example.com']);
    echo "Inserted user with ID: " . $userId . "\n";
    
    // Select example
    $user = $database->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    echo "Retrieved user: " . $user['username'] . "\n";
    
    // Update example
    $affected = $database->update("UPDATE users SET email = ? WHERE id = ?", 
                                 ['john.updated@example.com', $userId]);
    echo "Updated " . $affected . " rows\n";
    
    // Delete example
    $deleted = $database->delete("DELETE FROM users WHERE id = ?", [$userId]);
    echo "Deleted " . $deleted . " rows\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 3: Check if table exists
try {
    $db = db();
    if ($db->tableExists('users')) {
        echo "Users table exists\n";
        
        // Get table structure
        $structure = $db->getTableStructure('users');
        echo "Users table has " . count($structure) . " columns\n";
    } else {
        echo "Users table does not exist\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 