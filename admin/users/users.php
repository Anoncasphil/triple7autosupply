<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // Redirect to login page
    header("Location: ../../login/index.php");
    exit();
}

// Optional: Check if user has admin/staff role
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    // Redirect to login page with error message
    header("Location: ../../login/index.php?error=unauthorized");
    exit();
}

// Include database connection
require_once '../../config/database.php';

// Get database instance
$db = db();

// Handle form submission for creating new user
$message = '';
$messageType = '';

// Check for success/error messages from redirect
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $message = "User created successfully!";
            $messageType = "success";
            break;
        case '2':
            $message = "User updated successfully!";
            $messageType = "success";
            break;
        case '3':
            $message = "User deleted successfully!";
            $messageType = "success";
            break;
    }
} elseif (isset($_GET['error'])) {
    $message = urldecode($_GET['error']);
    $messageType = "error";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_user') {
    try {
        // Validate required fields
        $requiredFields = ['username', 'firstName', 'lastName', 'email', 'password', 'confirmPassword', 'role', 'status'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required.");
            }
        }
        
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        // Validate password match
        if ($_POST['password'] !== $_POST['confirmPassword']) {
            throw new Exception("Passwords do not match.");
        }
        
        // Validate password strength
        if (strlen($_POST['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }
        
        // Hash password
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (username, first_name, last_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $userId = $db->insert($sql, [
            $_POST['username'],
            $_POST['firstName'],
            $_POST['lastName'],
            $_POST['email'],
            $hashedPassword,
            $_POST['role'],
            $_POST['status']
        ]);
        
        $message = "User created successfully!";
        $messageType = "success";
        
        // Redirect to prevent form resubmission on page reload
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "error";
        
        // Redirect to prevent form resubmission on page reload
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Handle edit user form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    try {
        // Validate required fields
        $requiredFields = ['user_id', 'username', 'firstName', 'lastName', 'email', 'role', 'status'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required.");
            }
        }
        
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        // Check if username already exists (excluding current user)
        $existingUser = $db->fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$_POST['username'], $_POST['user_id']]);
        if ($existingUser) {
            throw new Exception("Username already exists.");
        }
        
        // Check if email already exists (excluding current user)
        $existingEmail = $db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$_POST['email'], $_POST['user_id']]);
        if ($existingEmail) {
            throw new Exception("Email already exists.");
        }
        
        // Build update query
        $updateFields = [
            'username' => $_POST['username'],
            'first_name' => $_POST['firstName'],
            'last_name' => $_POST['lastName'],
            'email' => $_POST['email'],
            'role' => $_POST['role'],
            'status' => $_POST['status']
        ];
        
        // Add password update if provided
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                throw new Exception("Password must be at least 8 characters long.");
            }
            if ($_POST['password'] !== $_POST['confirmPassword']) {
                throw new Exception("Passwords do not match.");
            }
            $updateFields['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        // Build SQL query
        $sql = "UPDATE users SET ";
        $params = [];
        foreach ($updateFields as $field => $value) {
            $sql .= "`$field` = ?, ";
            $params[] = $value;
        }
        $sql = rtrim($sql, ', ');
        $sql .= " WHERE id = ?";
        $params[] = $_POST['user_id'];
        
        // Execute update
        $affected = $db->update($sql, $params);
        
        if ($affected > 0) {
            $message = "User updated successfully!";
            $messageType = "success";
        } else {
            throw new Exception("No changes were made or user not found.");
        }
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=2");
        exit();
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "error";
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Handle delete user form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    try {
        if (empty($_POST['user_id'])) {
            throw new Exception("User ID is required.");
        }
        
        // Check if user exists
        $user = $db->fetch("SELECT id, username, first_name, last_name FROM users WHERE id = ?", [$_POST['user_id']]);
        if (!$user) {
            throw new Exception("User not found.");
        }
        
        // Delete user
        $deleted = $db->delete("DELETE FROM users WHERE id = ?", [$_POST['user_id']]);
        
        if ($deleted > 0) {
            $message = "User deleted successfully!";
            $messageType = "success";
        } else {
            throw new Exception("Failed to delete user.");
        }
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=3");
        exit();
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "error";
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Fetch all users from database
$users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");

// Get total count for pagination
$totalUsers = count($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Triple 7 Auto Supply</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#065f46',
                        secondary: '#dc2626',
                        accent: '#047857',
                        'green-light': '#10b981',
                        'green-dark': '#064e3b'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Include Sidebar -->
    <?php include '../sidebar/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Top Header -->
        <header>
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between px-4 lg:px-6 py-6 lg:py-8 space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-3 lg:space-x-4">
                    <div class="bg-gradient-to-r from-primary to-accent p-2 lg:p-3 rounded-xl shadow-lg">
                        <i class="fas fa-users text-white text-xl lg:text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">User Management</h2>
                        <p class="text-green-700 font-medium mt-1 text-sm lg:text-base">Manage system users and their permissions</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openCreateUserModal()" class="bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-3 lg:py-4 px-6 lg:px-8 rounded-xl transition-all duration-300 hover:scale-105 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 text-sm lg:text-base">
                        <i class="fas fa-user-plus mr-2 lg:mr-3 text-base lg:text-lg"></i>
                        Create User
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-4 lg:p-6">

            
            <!-- Search and Filters -->
            <div class="bg-white rounded-2xl shadow-lg p-4 lg:p-6 mb-6 border border-gray-100">
                <div class="flex flex-col space-y-4 lg:space-y-0 lg:flex-row lg:gap-4 lg:items-center lg:justify-between">
                    <div class="flex-1 w-full lg:max-w-md">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search users..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <select class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                        </select>
                        <select class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">User</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Email</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Role</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Last Login</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" class="py-8 px-6 text-center text-gray-500">
                                        <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                                        <p class="text-lg font-medium">No users found</p>
                                        <p class="text-sm">Create your first user to get started</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-gradient-to-r from-primary to-accent rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                                    <p class="text-sm text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-gray-900"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="py-4 px-6">
                                            <?php 
                                            $roleClass = $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                                            $roleText = ucfirst($user['role']);
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $roleClass; ?>">
                                                <?php echo $roleText; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <?php 
                                            $statusClass = $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                            $statusText = ucfirst($user['status']);
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-gray-500">
                                            <?php 
                                            if ($user['last_login']) {
                                                $lastLogin = new DateTime($user['last_login']);
                                                $now = new DateTime();
                                                $diff = $now->diff($lastLogin);
                                                
                                                if ($diff->days > 0) {
                                                    echo $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
                                                } elseif ($diff->h > 0) {
                                                    echo $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                                                } elseif ($diff->i > 0) {
                                                    echo $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                                                } else {
                                                    echo 'Just now';
                                                }
                                            } else {
                                                echo 'Never';
                                            }
                                            ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-2">
                                                <button onclick="editUser(<?php echo $user['id']; ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['first_name'] . ' ' . $user['last_name']); ?>', '<?php echo addslashes($user['email']); ?>')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="lg:hidden">
                    <?php if (empty($users)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No users found</p>
                            <p class="text-sm">Create your first user to get started</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-primary to-accent rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                            <p class="text-sm text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editUser(<?php echo $user['id']; ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['first_name'] . ' ' . $user['last_name']); ?>', '<?php echo addslashes($user['email']); ?>')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Email:</span>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Role:</span>
                                        <?php 
                                        $roleClass = $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                                        $roleText = ucfirst($user['role']);
                                        ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $roleClass; ?>"><?php echo $roleText; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Status:</span>
                                        <?php 
                                        $statusClass = $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                        $statusText = ucfirst($user['status']);
                                        ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Last Login:</span>
                                        <span class="text-gray-900">
                                            <?php 
                                            if ($user['last_login']) {
                                                $lastLogin = new DateTime($user['last_login']);
                                                $now = new DateTime();
                                                $diff = $now->diff($lastLogin);
                                                
                                                if ($diff->days > 0) {
                                                    echo $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
                                                } elseif ($diff->h > 0) {
                                                    echo $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                                                } elseif ($diff->i > 0) {
                                                    echo $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                                                } else {
                                                    echo 'Just now';
                                                }
                                            } else {
                                                echo 'Never';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                <div class="text-sm text-gray-500 text-center sm:text-left">
                    <?php if ($totalUsers > 0): ?>
                        Showing 1 to <?php echo $totalUsers; ?> of <?php echo $totalUsers; ?> results
                    <?php else: ?>
                        No results found
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50" disabled>
                        Previous
                    </button>
                    <button class="px-3 py-2 text-sm bg-primary text-white rounded-lg">1</button>
                    <button class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50" disabled>
                        Next
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-start justify-center min-h-screen p-4 pt-8 pb-8">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Create New User</h3>
                    <button onclick="closeCreateUserModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="createUserForm" method="POST" action="" class="p-4 lg:p-6">
                    <input type="hidden" name="action" value="create_user">
                    
                    <!-- General Error Display -->
                    <div id="generalErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg hidden">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                            <div>
                                <h4 class="text-sm font-medium text-red-800">Please fix the following errors:</h4>
                                <ul id="errorList" class="mt-1 text-sm text-red-700 list-disc list-inside space-y-1">
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        <!-- Username - Full Width -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" id="username" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <div id="usernameError" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <!-- First Name and Last Name - Same Line -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" name="firstName" id="firstName" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" name="lastName" id="lastName" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>

                        <!-- Email - Full Width -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="email" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <div id="emailError" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <!-- Role and Status - Same Line -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                <select name="role" id="role" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="status" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Password - Full Width -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors duration-300">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <!-- Password Validation -->
                            <div id="passwordValidation" class="mt-2 text-xs space-y-1">
                                <div class="flex items-center" id="lengthCheck">
                                    <i class="fas fa-circle text-gray-300 mr-2"></i>
                                    <span class="text-gray-500">At least 8 characters</span>
                                </div>
                                <div class="flex items-center" id="uppercaseCheck">
                                    <i class="fas fa-circle text-gray-300 mr-2"></i>
                                    <span class="text-gray-500">One uppercase letter</span>
                                </div>
                                <div class="flex items-center" id="lowercaseCheck">
                                    <i class="fas fa-circle text-gray-300 mr-2"></i>
                                    <span class="text-gray-500">One lowercase letter</span>
                                </div>
                                <div class="flex items-center" id="numberCheck">
                                    <i class="fas fa-circle text-gray-300 mr-2"></i>
                                    <span class="text-gray-500">One number</span>
                                </div>
                                <div class="flex items-center" id="specialCheck">
                                    <i class="fas fa-circle text-gray-300 mr-2"></i>
                                    <span class="text-gray-500">One special character (!@#$%^&*(),.?":{}|<>_)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password - Full Width -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                            <div class="relative">
                                <input type="password" id="confirmPassword" name="confirmPassword" required class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <button type="button" id="toggleConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors duration-300">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="mt-2 text-xs">
                                <div class="flex items-center" id="matchCheck">
                                    <i class="fas fa-circle text-gray-300 mr-2"></i>
                                    <span class="text-gray-500">Passwords match</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-3 px-6 rounded-xl transition-all duration-300">
                            Create User
                        </button>
                        <button type="button" onclick="closeCreateUserModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all duration-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-start justify-center min-h-screen p-4 pt-8 pb-8">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Edit User</h3>
                    <button onclick="closeEditUserModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="editUserForm" method="POST" action="" class="p-4 lg:p-6">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <!-- General Error Display -->
                    <div id="editGeneralErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg hidden">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                            <div>
                                <h4 class="text-sm font-medium text-red-800">Please fix the following errors:</h4>
                                <ul id="editErrorList" class="mt-1 text-sm text-red-700 list-disc list-inside space-y-1">
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        <!-- Username - Full Width -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" id="edit_username" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <div id="editUsernameError" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <!-- First Name and Last Name - Same Line -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" name="firstName" id="edit_firstName" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" name="lastName" id="edit_lastName" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>

                        <!-- Email - Full Width -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="edit_email" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <div id="editEmailError" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <!-- Role and Status - Same Line -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                <select name="role" id="edit_role" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="edit_status" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Password - Full Width (Optional for edit) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password (leave blank to keep current)</label>
                            <div class="relative">
                                <input type="password" id="edit_password" name="password" class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <button type="button" id="toggleEditPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors duration-300">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password - Full Width -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" id="edit_confirmPassword" name="confirmPassword" class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <button type="button" id="toggleEditConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors duration-300">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-3 px-6 rounded-xl transition-all duration-300">
                            Update User
                        </button>
                        <button type="button" onclick="closeEditUserModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all duration-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-start justify-center min-h-screen p-4 pt-8 pb-8">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Confirm Delete</h3>
                    <button onclick="closeDeleteUserModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Delete User</h4>
                            <p class="text-sm text-gray-600">This action cannot be undone.</p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-700 mb-2">Are you sure you want to delete this user?</p>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-primary to-accent rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900" id="deleteUserName"></p>
                                <p class="text-sm text-gray-500" id="deleteUserEmail"></p>
                            </div>
                        </div>
                    </div>

                    <form id="deleteUserForm" method="POST" action="">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="delete_user_id">
                        
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                            <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300">
                                <i class="fas fa-trash mr-2"></i>
                                Delete User
                            </button>
                            <button type="button" onclick="closeDeleteUserModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all duration-300">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
                    </div>
    </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <script>
        // Modal functions
        function openCreateUserModal() {
            document.getElementById('createUserModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            formSubmitted = false; // Reset form submitted flag
            fetchExistingData(); // Fetch existing usernames and emails
        }

        function closeCreateUserModal() {
            document.getElementById('createUserModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            formSubmitted = false; // Reset form submitted flag
        }

        function openEditUserModal(userId) {
            console.log('Opening edit modal for user ID:', userId);
            
            // Fetch user data and populate form
            fetch(`../../api/get_user_data.php?id=${userId}`)
                .then(response => {
                    console.log('API Response status:', response.status);
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server returned non-JSON response. Please check server logs.');
                    }
                    
                    return response.json();
                })
                .then(data => {
                    console.log('API Response data:', data);
                    if (data.success) {
                        const user = data.user;
                        document.getElementById('edit_user_id').value = user.id;
                        document.getElementById('edit_username').value = user.username;
                        document.getElementById('edit_firstName').value = user.first_name;
                        document.getElementById('edit_lastName').value = user.last_name;
                        document.getElementById('edit_email').value = user.email;
                        document.getElementById('edit_role').value = user.role;
                        document.getElementById('edit_status').value = user.status;
                        document.getElementById('edit_password').value = '';
                        document.getElementById('edit_confirmPassword').value = '';
                        
                        document.getElementById('editUserModal').classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    } else {
                        console.error('API returned error:', data.error);
                        showToast('Failed to load user data: ' + (data.error || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showToast('Failed to load user data: ' + error.message, 'error');
                });
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openDeleteUserModal(userId, userName, userEmail) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteUserEmail').textContent = userEmail;
            
            document.getElementById('deleteUserModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteUserModal() {
            document.getElementById('deleteUserModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Password validation functions
        function validatePassword(password) {
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>_]/.test(password)
            };
            
            // Update visual indicators
            document.getElementById('lengthCheck').innerHTML = 
                `<i class="fas fa-${checks.length ? 'check text-green-500' : 'circle text-gray-300'} mr-2"></i>
                 <span class="${checks.length ? 'text-green-600' : 'text-gray-500'}">At least 8 characters</span>`;
            
            document.getElementById('uppercaseCheck').innerHTML = 
                `<i class="fas fa-${checks.uppercase ? 'check text-green-500' : 'circle text-gray-300'} mr-2"></i>
                 <span class="${checks.uppercase ? 'text-green-600' : 'text-gray-500'}">One uppercase letter</span>`;
            
            document.getElementById('lowercaseCheck').innerHTML = 
                `<i class="fas fa-${checks.lowercase ? 'check text-green-500' : 'circle text-gray-300'} mr-2"></i>
                 <span class="${checks.lowercase ? 'text-green-600' : 'text-gray-500'}">One lowercase letter</span>`;
            
            document.getElementById('numberCheck').innerHTML = 
                `<i class="fas fa-${checks.number ? 'check text-green-500' : 'circle text-gray-300'} mr-2"></i>
                 <span class="${checks.number ? 'text-green-600' : 'text-gray-500'}">One number</span>`;
            
            document.getElementById('specialCheck').innerHTML = 
                `<i class="fas fa-${checks.special ? 'check text-green-500' : 'circle text-gray-300'} mr-2"></i>
                 <span class="${checks.special ? 'text-green-600' : 'text-gray-500'}">One special character</span>`;
            
            return Object.values(checks).every(check => check);
        }

        function validatePasswordMatch(password, confirmPassword) {
            const matches = password === confirmPassword && password !== '';
            document.getElementById('matchCheck').innerHTML = 
                `<i class="fas fa-${matches ? 'check text-green-500' : 'circle text-gray-300'} mr-2"></i>
                 <span class="${matches ? 'text-green-600' : 'text-gray-500'}">Passwords match</span>`;
            return matches;
        }

        // Real-time validation event listeners (only show individual field errors)
        document.getElementById('username').addEventListener('input', function() {
            validateUsername(this.value);
        });

        document.getElementById('email').addEventListener('input', function() {
            validateEmail(this.value);
        });

        // Password validation event listeners
        document.getElementById('password').addEventListener('input', function() {
            validatePassword(this.value);
            validatePasswordMatch(this.value, document.getElementById('confirmPassword').value);
        });

        document.getElementById('confirmPassword').addEventListener('input', function() {
            validatePasswordMatch(document.getElementById('password').value, this.value);
        });

        // Get existing usernames and emails for validation
        let existingUsernames = [];
        let existingEmails = [];
        let formSubmitted = false; // Track if form has been submitted
        
        // Toast notification functions
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg transform translate-x-full transition-all duration-300 ease-out flex items-center space-x-3`;
            toast.innerHTML = `
                <i class="fas ${icon} text-lg"></i>
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            toastContainer.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }, 3000);
        }
        
        // Fetch existing data when modal opens
        function fetchExistingData() {
            fetch('../../api/get_existing_users.php')
                .then(response => {
                    console.log('Existing users API response status:', response.status);
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server returned non-JSON response for existing users');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Existing users data:', data);
                    existingUsernames = data.usernames || [];
                    existingEmails = data.emails || [];
                })
                .catch(error => {
                    console.error('Error fetching existing data:', error);
                    showToast('Failed to load existing user data: ' + error.message, 'error');
                });
        }
        
        // Collect all validation errors
        function collectValidationErrors() {
            const errors = [];
            const formData = new FormData(document.getElementById('createUserForm'));
            const userData = Object.fromEntries(formData);
            
            // Check required fields
            if (!userData.username.trim()) {
                errors.push('Username is required');
            }
            if (!userData.firstName.trim()) {
                errors.push('First name is required');
            }
            if (!userData.lastName.trim()) {
                errors.push('Last name is required');
            }
            if (!userData.email.trim()) {
                errors.push('Email is required');
            }
            if (!userData.password) {
                errors.push('Password is required');
            }
            if (!userData.confirmPassword) {
                errors.push('Confirm password is required');
            }
            if (!userData.role) {
                errors.push('Role is required');
            }
            if (!userData.status) {
                errors.push('Status is required');
            }
            
            // Check username uniqueness
            if (userData.username.trim() && existingUsernames.includes(userData.username.trim())) {
                errors.push('Username already exists');
            }
            
            // Check email uniqueness
            if (userData.email.trim() && existingEmails.includes(userData.email.trim())) {
                errors.push('Email already exists');
            }
            
            // Check password strength
            if (userData.password && userData.password.length < 8) {
                errors.push('Password must be at least 8 characters long');
            }
            
            // Check password match
            if (userData.password && userData.confirmPassword && userData.password !== userData.confirmPassword) {
                errors.push('Passwords do not match');
            }
            
            return errors;
        }
        
        // Display validation errors
        function displayValidationErrors(errors) {
            const generalErrors = document.getElementById('generalErrors');
            const errorList = document.getElementById('errorList');
            
            // Only show general errors if form has been submitted
            if (errors.length > 0 && formSubmitted) {
                errorList.innerHTML = '';
                errors.forEach(error => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    errorList.appendChild(li);
                });
                generalErrors.classList.remove('hidden');
            } else {
                generalErrors.classList.add('hidden');
            }
        }
        
        // Username validation
        function validateUsername(username) {
            if (existingUsernames.includes(username)) {
                document.getElementById('usernameError').textContent = 'Username already exists';
                document.getElementById('usernameError').style.display = 'block';
                return false;
            } else {
                document.getElementById('usernameError').style.display = 'none';
                return true;
            }
        }
        
        // Email validation
        function validateEmail(email) {
            if (existingEmails.includes(email)) {
                document.getElementById('emailError').textContent = 'Email already exists';
                document.getElementById('emailError').style.display = 'block';
                return false;
            } else {
                document.getElementById('emailError').style.display = 'none';
                return true;
            }
        }
        
        // Form submission
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            // Mark form as submitted
            formSubmitted = true;
            
            // Collect all validation errors
            const errors = collectValidationErrors();
            
            // Display errors if any
            displayValidationErrors(errors);
            
            // If there are errors, prevent form submission
            if (errors.length > 0) {
                e.preventDefault();
                return;
            }
            
            // If validation passes, let the form submit normally
            // The PHP will handle the database insertion and show success/error message
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // User actions
        function editUser(userId) {
            openEditUserModal(userId);
        }

        function deleteUser(userId, userName, userEmail) {
            openDeleteUserModal(userId, userName, userEmail);
        }

        // Close modal when clicking outside
        document.getElementById('createUserModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateUserModal();
            }
        });

        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Create modal password toggles
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            
            // Edit modal password toggles
            const toggleEditPassword = document.getElementById('toggleEditPassword');
            const editPassword = document.getElementById('edit_password');
            const toggleEditConfirmPassword = document.getElementById('toggleEditConfirmPassword');
            const editConfirmPassword = document.getElementById('edit_confirmPassword');
            
            // Create modal password toggle
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            }
            
            // Create modal confirm password toggle
            if (toggleConfirmPassword && confirmPassword) {
                toggleConfirmPassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPassword.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            }
            
            // Edit modal password toggle
            if (toggleEditPassword && editPassword) {
                toggleEditPassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = editPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    editPassword.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            }
            
            // Edit modal confirm password toggle
            if (toggleEditConfirmPassword && editConfirmPassword) {
                toggleEditConfirmPassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = editConfirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    editConfirmPassword.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            }
            
            // Add some animations
            // Animate table rows on load
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.6s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // If there's a success message, close the modal and reset form
            <?php if ($messageType === 'success'): ?>
            setTimeout(() => {
                closeCreateUserModal();
                document.getElementById('createUserForm').reset();
                // Reset validation indicators
                document.querySelectorAll('#passwordValidation i, #passwordMatch i').forEach(icon => {
                    icon.className = 'fas fa-circle text-gray-300 mr-2';
                });
                document.querySelectorAll('#passwordValidation span, #passwordMatch span').forEach(span => {
                    span.className = 'text-gray-500';
                });
            }, 2000);
            <?php endif; ?>
            
            // Show toast notifications after page loads
            setTimeout(() => {
                <?php if (isset($_GET['success'])): ?>
                <?php 
                $successMessage = '';
                switch ($_GET['success']) {
                    case '1':
                        $successMessage = 'User created successfully!';
                        break;
                    case '2':
                        $successMessage = 'User updated successfully!';
                        break;
                    case '3':
                        $successMessage = 'User deleted successfully!';
                        break;
                }
                if (!empty($successMessage)):
                ?>
                showToast('<?php echo addslashes($successMessage); ?>', 'success');
                <?php endif; ?>
                <?php elseif (isset($_GET['error'])): ?>
                showToast('<?php echo addslashes(urldecode($_GET['error'])); ?>', 'error');
                <?php endif; ?>
            }, 500);
        });
    </script>
</body>
</html>
