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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Triple 7 Auto Supply</title>
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
                        <i class="fas fa-tachometer-alt text-white text-xl lg:text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">Dashboard</h2>
                        <p class="text-green-700 font-medium mt-1 text-sm lg:text-base">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Here's what's happening with your business.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-200">
                        <span class="text-sm text-gray-700 font-medium">Today: <?php echo date('M d, Y'); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="p-4 lg:p-6">
            <?php
            // Include database connection
            require_once '../../config/database.php';
            
            try {
                $db = db();
                
                // Get total products count
                $totalProducts = $db->fetch("SELECT COUNT(*) as count FROM products");
                $productsCount = $totalProducts ? $totalProducts['count'] : 0;
                
                // Get total users count
                $totalUsers = $db->fetch("SELECT COUNT(*) as count FROM users");
                $usersCount = $totalUsers ? $totalUsers['count'] : 0;
                
                // Get recent products (limit 4)
                $recentProducts = $db->fetchAll("SELECT name, category, car_model, price, availability FROM products ORDER BY created_at DESC LIMIT 4");
                
            } catch (Exception $e) {
                $productsCount = 0;
                $usersCount = 0;
                $recentProducts = [];
            }
            ?>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6 mb-8">
                <!-- Total Products -->
                <div class="bg-white rounded-2xl shadow-lg p-4 lg:p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Products</p>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900"><?php echo number_format($productsCount); ?></p>
                            <p class="text-sm text-green-600 flex items-center mt-1">
                                <i class="fas fa-box mr-1"></i>
                                Auto parts inventory
                            </p>
                        </div>
                        <div class="bg-gradient-to-r from-primary to-accent p-2 lg:p-3 rounded-xl shadow-lg">
                            <i class="fas fa-boxes text-white text-xl lg:text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white rounded-2xl shadow-lg p-4 lg:p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Users</p>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900"><?php echo number_format($usersCount); ?></p>
                            <p class="text-sm text-blue-600 flex items-center mt-1">
                                <i class="fas fa-users mr-1"></i>
                                Admin & Staff accounts
                            </p>
                        </div>
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-2 lg:p-3 rounded-xl shadow-lg">
                            <i class="fas fa-users text-white text-xl lg:text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Recent Products and Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                <!-- Recent Products -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-4 lg:p-6 border border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 lg:mb-6 space-y-3 sm:space-y-0">
                        <h3 class="text-lg lg:text-xl font-bold text-gray-900">Recent Products</h3>
                        <a href="../products/products.php" class="text-primary hover:text-accent font-medium transition-colors">View All</a>
                    </div>
                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-900">Product</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-900">Category</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-900">Car Model</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-900">Price</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-900">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (!empty($recentProducts)): ?>
                                    <?php foreach ($recentProducts as $product): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="py-4 px-6 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td class="py-4 px-6 text-sm text-gray-600"><?php echo htmlspecialchars($product['category']); ?></td>
                                            <td class="py-4 px-6 text-sm text-gray-600"><?php echo htmlspecialchars($product['car_model']); ?></td>
                                            <td class="py-4 px-6 text-sm font-medium text-gray-900">₱<?php echo number_format($product['price'], 2); ?></td>
                                            <td class="py-4 px-6">
                                                <?php 
                                                $statusClass = $product['availability'] === 'In Stock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                                $statusText = $product['availability'] === 'In Stock' ? 'In Stock' : 'Out of Stock';
                                                ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-8 px-6 text-center text-gray-500">
                                            <i class="fas fa-box text-2xl mb-2"></i>
                                            <p>No products found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Cards -->
                    <div class="lg:hidden">
                        <?php if (!empty($recentProducts)): ?>
                            <?php foreach ($recentProducts as $product): ?>
                                <div class="p-4 border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($product['category']); ?></p>
                                        </div>
                                        <?php 
                                        $statusClass = $product['availability'] === 'In Stock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        $statusText = $product['availability'] === 'In Stock' ? 'In Stock' : 'Out of Stock';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-1"><?php echo htmlspecialchars($product['car_model']); ?></div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Price:</span>
                                        <span class="text-gray-900 font-bold">₱<?php echo number_format($product['price'], 2); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-8 text-center text-gray-500">
                                <i class="fas fa-box text-2xl mb-2"></i>
                                <p>No products found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-lg p-4 lg:p-6 border border-gray-100">
                    <h3 class="text-lg lg:text-xl font-bold text-gray-900 mb-6">Quick Actions</h3>
                    <div class="space-y-4">
                        <a href="../products/products.php" class="w-full flex items-center justify-between p-4 bg-gradient-to-r from-primary to-accent text-white rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 transform">
                            <div class="flex items-center">
                                <i class="fas fa-plus mr-3"></i>
                                <span class="font-medium">Add New Product</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="../products/products.php" class="w-full flex items-center justify-between p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 transform">
                            <div class="flex items-center">
                                <i class="fas fa-boxes mr-3"></i>
                                <span class="font-medium">Manage Products</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="../users/users.php" class="w-full flex items-center justify-between p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 transform">
                            <div class="flex items-center">
                                <i class="fas fa-user-plus mr-3"></i>
                                <span class="font-medium">Add New User</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="../users/users.php" class="w-full flex items-center justify-between p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 transform">
                            <div class="flex items-center">
                                <i class="fas fa-users mr-3"></i>
                                <span class="font-medium">Manage Users</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Animate statistics cards on load
            const cards = document.querySelectorAll('.bg-white.rounded-2xl');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f9fafb';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>
</body>
</html>
