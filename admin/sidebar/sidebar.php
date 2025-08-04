<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Check current path for better active state detection
$current_path = $_SERVER['REQUEST_URI'];

// Check if we're in specific directories
$is_dashboard_page = strpos($current_path, '/dashboard/') !== false;
$is_products_page = strpos($current_path, '/products/') !== false;
$is_users_page = strpos($current_path, '/users/') !== false;

// Get current user information
$current_user_name = 'Admin User';
$current_user_role = 'Administrator';

if (isset($_SESSION['user_id'])) {
    try {
        require_once '../../config/database.php';
        $db = db();
        
        $user = $db->fetch("SELECT first_name, last_name, role FROM users WHERE id = ?", [$_SESSION['user_id']]);
        
        if ($user) {
            $current_user_name = $user['first_name'] . ' ' . $user['last_name'];
            $current_user_role = ucfirst($user['role']);
        }
    } catch (Exception $e) {
        // Keep default values if database error occurs
    }
}
?>

<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0" id="sidebar">
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
        <div class="flex items-center">
            <img src="../../assets/images/logo.png" alt="Triple 7 Auto Supply Logo" class="h-8 w-auto mr-3">
            <h2 class="text-lg font-semibold text-gray-800">Triple 7 Auto</h2>
        </div>
        <button id="sidebarClose" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="mt-6 px-4">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="../dashboard" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 <?php echo $is_dashboard_page ? 'bg-green-50 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                <span>Dashboard</span>
            </a>

            <!-- Products -->
            <a href="../products" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 <?php echo $is_products_page ? 'bg-green-50 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                <i class="fas fa-boxes w-5 h-5 mr-3"></i>
                <span>Products</span>
            </a>

            <!-- Users -->
            <a href="../users" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 <?php echo $is_users_page ? 'bg-green-50 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                <i class="fas fa-users w-5 h-5 mr-3"></i>
                <span>Users</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-6"></div>

        <!-- Logout -->
        <a href="../../login/logout.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
            <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
            <span>Logout</span>
        </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($current_user_name); ?></p>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($current_user_role); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden hidden"></div>

<!-- Sidebar Toggle Button (for mobile) -->
<button id="sidebarToggle" class="fixed top-4 right-4 z-50 lg:hidden p-2 bg-white rounded-lg shadow-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors duration-200">
    <i class="fas fa-bars"></i>
</button>

<script>
// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Toggle sidebar on mobile
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.remove('hidden');
        sidebarToggle.classList.add('hidden'); // Hide hamburger when sidebar opens
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    });

    // Close sidebar
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
        sidebarToggle.classList.remove('hidden'); // Show hamburger when sidebar closes
        document.body.style.overflow = ''; // Restore scrolling
    }

    sidebarClose.addEventListener('click', closeSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);

    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) { // lg breakpoint
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            sidebarToggle.classList.add('hidden'); // Hide hamburger on desktop
            document.body.style.overflow = ''; // Restore scrolling
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebarToggle.classList.remove('hidden'); // Show hamburger on mobile
        }
    });

    // Initialize sidebar state based on screen size
    if (window.innerWidth < 1024) {
        sidebar.classList.add('-translate-x-full');
        sidebarToggle.classList.remove('hidden'); // Show hamburger on mobile
    } else {
        sidebarToggle.classList.add('hidden'); // Hide hamburger on desktop
    }
});
</script>


