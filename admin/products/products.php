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

// Handle form submission for creating new product
$message = '';
$messageType = '';

// Check for success/error messages from redirect
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $message = "Product created successfully!";
            $messageType = "success";
            break;
        case '2':
            $message = "Product updated successfully!";
            $messageType = "success";
            break;
        case '3':
            $message = "Product deleted successfully!";
            $messageType = "success";
            break;
    }
} elseif (isset($_GET['error'])) {
    $message = urldecode($_GET['error']);
    $messageType = "error";
}

// Handle create product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_product') {
    try {
        // Validate required fields
        $requiredFields = ['name', 'category', 'car_model', 'condition', 'price', 'availability', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required.");
            }
        }
        
        // Validate price
        if (!is_numeric($_POST['price']) || $_POST['price'] < 0) {
            throw new Exception("Price must be a valid positive number.");
        }
        
        // Handle file upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/images/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception("Only JPG, JPEG, PNG, and GIF files are allowed.");
            }
            
            $timestamp = time();
            $randomString = bin2hex(random_bytes(8));
            $fileName = $timestamp . '_' . $randomString . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imagePath = 'uploads/images/' . $fileName;
            } else {
                throw new Exception("Failed to upload image.");
            }
        }
        
        // Insert new product
        $sql = "INSERT INTO products (name, category, car_model, `condition`, price, image, availability, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $productId = $db->insert($sql, [
            $_POST['name'],
            $_POST['category'],
            $_POST['car_model'],
            $_POST['condition'],
            $_POST['price'],
            $imagePath,
            $_POST['availability'],
            $_POST['description']
        ]);
        
        // Redirect to prevent form resubmission on page reload
        $redirectUrl = $_SERVER['PHP_SELF'];
        if (isset($_GET['page'])) {
            $redirectUrl .= "?page=" . $_GET['page'] . "&success=1";
        } else {
            $redirectUrl .= "?success=1";
        }
        header("Location: " . $redirectUrl);
        exit();
        
    } catch (Exception $e) {
        // Redirect to prevent form resubmission on page reload
        $redirectUrl = $_SERVER['PHP_SELF'];
        if (isset($_GET['page'])) {
            $redirectUrl .= "?page=" . $_GET['page'] . "&error=" . urlencode($e->getMessage());
        } else {
            $redirectUrl .= "?error=" . urlencode($e->getMessage());
        }
        header("Location: " . $redirectUrl);
        exit();
    }
}

// Handle edit product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    try {
        // Validate required fields
        $requiredFields = ['product_id', 'name', 'category', 'car_model', 'condition', 'price', 'availability', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required.");
            }
        }
        
        // Validate price
        if (!is_numeric($_POST['price']) || $_POST['price'] < 0) {
            throw new Exception("Price must be a valid positive number.");
        }
        
        // Handle file upload for edit
        $imagePath = $_POST['current_image']; // Keep current image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/images/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception("Only JPG, JPEG, PNG, and GIF files are allowed.");
            }
            
            $timestamp = time();
            $randomString = bin2hex(random_bytes(8));
            $fileName = $timestamp . '_' . $randomString . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imagePath = 'uploads/images/' . $fileName;
                
                // Delete old image if it exists
                if (!empty($_POST['current_image']) && file_exists('../../' . $_POST['current_image'])) {
                    unlink('../../' . $_POST['current_image']);
                }
            } else {
                throw new Exception("Failed to upload image.");
            }
        }
        
        // Build update query
        $sql = "UPDATE products SET name = ?, category = ?, car_model = ?, `condition` = ?, price = ?, image = ?, availability = ?, description = ? WHERE id = ?";
        $affected = $db->update($sql, [
            $_POST['name'],
            $_POST['category'],
            $_POST['car_model'],
            $_POST['condition'],
            $_POST['price'],
            $imagePath,
            $_POST['availability'],
            $_POST['description'],
            $_POST['product_id']
        ]);
        
        if ($affected > 0) {
            // Redirect to prevent form resubmission
            $redirectUrl = $_SERVER['PHP_SELF'];
            if (isset($_GET['page'])) {
                $redirectUrl .= "?page=" . $_GET['page'] . "&success=2";
            } else {
                $redirectUrl .= "?success=2";
            }
            header("Location: " . $redirectUrl);
            exit();
        } else {
            throw new Exception("No changes were made or product not found.");
        }
        
    } catch (Exception $e) {
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Handle delete product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    try {
        if (empty($_POST['product_id'])) {
            throw new Exception("Product ID is required.");
        }
        
        // Get product info to delete image
        $product = $db->fetch("SELECT id, name, image FROM products WHERE id = ?", [$_POST['product_id']]);
        if (!$product) {
            throw new Exception("Product not found.");
        }
        
        // Delete product
        $deleted = $db->delete("DELETE FROM products WHERE id = ?", [$_POST['product_id']]);
        
        if ($deleted > 0) {
            // Delete image file if it exists
            if (!empty($product['image']) && file_exists('../../' . $product['image'])) {
                unlink('../../' . $product['image']);
            }
            
            // Redirect to prevent form resubmission
            $redirectUrl = $_SERVER['PHP_SELF'];
            if (isset($_GET['page'])) {
                $redirectUrl .= "?page=" . $_GET['page'] . "&success=3";
            } else {
                $redirectUrl .= "?success=3";
            }
            header("Location: " . $redirectUrl);
            exit();
        } else {
            throw new Exception("Failed to delete product.");
        }
        
    } catch (Exception $e) {
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Pagination settings
$itemsPerPage = 8;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Get total count for pagination
$totalProducts = $db->fetch("SELECT COUNT(*) as count FROM products")['count'];
$totalPages = ceil($totalProducts / $itemsPerPage);

// Fetch products for current page
$products = $db->fetchAll("SELECT * FROM products ORDER BY created_at DESC LIMIT ? OFFSET ?", [$itemsPerPage, $offset]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Triple 7 Auto Supply</title>
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
                        <i class="fas fa-box text-white text-xl lg:text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">Product Management</h2>
                        <p class="text-green-700 font-medium mt-1 text-sm lg:text-base">Manage auto parts and inventory</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openCreateProductModal()" class="bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-3 lg:py-4 px-6 lg:px-8 rounded-xl transition-all duration-300 hover:scale-105 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 text-sm lg:text-base">
                        <i class="fas fa-plus mr-2 lg:mr-3 text-base lg:text-lg"></i>
                        Add Product
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
                            <input type="text" id="searchInput" placeholder="Search products..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <select class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Categories</option>
                            <option value="Engine">Engine</option>
                            <option value="Brakes">Brakes</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Suspension">Suspension</option>
                            <option value="Wheels & Tires">Wheels & Tires</option>
                            <option value="Body Parts">Body Parts</option>
                            <option value="Bumpers">Bumpers</option>
                            <option value="Lights">Lights</option>
                            <option value="Interior">Interior</option>
                            <option value="Exhaust">Exhaust</option>
                            <option value="Transmission">Transmission</option>
                            <option value="Cooling System">Cooling System</option>
                            <option value="Fuel System">Fuel System</option>
                            <option value="Steering">Steering</option>
                            <option value="Air Conditioning">Air Conditioning</option>
                            <option value="Audio & Electronics">Audio & Electronics</option>
                            <option value="Safety & Security">Safety & Security</option>
                            <option value="Tools & Accessories">Tools & Accessories</option>
                            <option value="Oils & Fluids">Oils & Fluids</option>
                            <option value="Filters">Filters</option>
                            <option value="Belts & Hoses">Belts & Hoses</option>
                            <option value="Sensors">Sensors</option>
                            <option value="Clutch">Clutch</option>
                            <option value="Differential">Differential</option>
                            <option value="Axles">Axles</option>
                            <option value="Drivetrain">Drivetrain</option>
                            <option value="Other">Other</option>
                        </select>
                        <select class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Conditions</option>
                            <option value="New">New</option>
                            <option value="Used">Used</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Product</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Category</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Car Model</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Price</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Condition</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Availability</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 px-6 text-center text-gray-500">
                                        <i class="fas fa-box text-4xl mb-4 text-gray-300"></i>
                                        <p class="text-lg font-medium">No products found</p>
                                        <p class="text-sm">Add your first product to get started</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-12 h-12 bg-gradient-to-r from-primary to-accent rounded-lg flex items-center justify-center overflow-hidden">
                                                    <?php if ($product['image']): ?>
                                                        <img src="../../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <i class="fas fa-box text-white"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo htmlspecialchars($product['category']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-gray-900"><?php echo htmlspecialchars($product['car_model']); ?></td>
                                        <td class="py-4 px-6">
                                            <span class="font-semibold text-green-600">₱<?php echo number_format($product['price'], 2); ?></span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <?php 
                                            $conditionClass = $product['condition'] === 'New' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $conditionClass; ?>">
                                                <?php echo htmlspecialchars($product['condition']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <?php 
                                            $availabilityClass = $product['availability'] === 'In Stock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $availabilityClass; ?>">
                                                <?php echo htmlspecialchars($product['availability']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-2">
                                                <button onclick="editProduct(<?php echo $product['id']; ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
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
                    <?php if (empty($products)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-box text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No products found</p>
                            <p class="text-sm">Add your first product to get started</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gradient-to-r from-primary to-accent rounded-lg flex items-center justify-center overflow-hidden">
                                            <?php if ($product['image']): ?>
                                                <img src="../../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i class="fas fa-box text-white"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editProduct(<?php echo $product['id']; ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Category:</span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><?php echo htmlspecialchars($product['category']); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Car Model:</span>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($product['car_model']); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Price:</span>
                                        <span class="font-semibold text-green-600">₱<?php echo number_format($product['price'], 2); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Condition:</span>
                                        <?php 
                                        $conditionClass = $product['condition'] === 'New' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $conditionClass; ?>"><?php echo htmlspecialchars($product['condition']); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Availability:</span>
                                        <?php 
                                        $availabilityClass = $product['availability'] === 'In Stock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $availabilityClass; ?>"><?php echo htmlspecialchars($product['availability']); ?></span>
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
                    <?php if ($totalProducts > 0): ?>
                        <?php 
                        $startItem = $offset + 1;
                        $endItem = min($offset + $itemsPerPage, $totalProducts);
                        ?>
                        Showing <?php echo $startItem; ?> to <?php echo $endItem; ?> of <?php echo $totalProducts; ?> results
                    <?php else: ?>
                        No results found
                    <?php endif; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                <div class="flex items-center space-x-2">
                    <!-- Previous Button -->
                    <a href="?page=<?php echo max(1, $currentPage - 1); ?>" 
                       class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 <?php echo $currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                       <?php echo $currentPage <= 1 ? 'onclick="return false;"' : ''; ?>>
                        Previous
                    </a>
                    
                    <!-- Page Numbers -->
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    // Show first page if not in range
                    if ($startPage > 1) {
                        echo '<a href="?page=1" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="px-2 text-gray-400">...</span>';
                        }
                    }
                    
                    // Show page numbers
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $activeClass = $i === $currentPage ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700';
                        echo '<a href="?page=' . $i . '" class="px-3 py-2 text-sm rounded-lg ' . $activeClass . '">' . $i . '</a>';
                    }
                    
                    // Show last page if not in range
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="px-2 text-gray-400">...</span>';
                        }
                        echo '<a href="?page=' . $totalPages . '" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">' . $totalPages . '</a>';
                    }
                    ?>
                    
                    <!-- Next Button -->
                    <a href="?page=<?php echo min($totalPages, $currentPage + 1); ?>" 
                       class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 <?php echo $currentPage >= $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                       <?php echo $currentPage >= $totalPages ? 'onclick="return false;"' : ''; ?>>
                        Next
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Create Product Modal -->
    <div id="createProductModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-start justify-center min-h-screen p-4 pt-8 pb-8">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Add New Product</h3>
                    <button onclick="closeCreateProductModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="createProductForm" method="POST" action="" enctype="multipart/form-data" class="p-4 lg:p-6">
                    <input type="hidden" name="action" value="create_product">
                    
                    <div class="space-y-4 mb-6">
                        <!-- Product Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                            <input type="text" name="name" id="name" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Category and Car Model -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select name="category" id="category" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Category</option>
                                    <option value="Engine">Engine</option>
                                    <option value="Brakes">Brakes</option>
                                    <option value="Electrical">Electrical</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Wheels & Tires">Wheels & Tires</option>
                                    <option value="Body Parts">Body Parts</option>
                                    <option value="Bumpers">Bumpers</option>
                                    <option value="Lights">Lights</option>
                                    <option value="Interior">Interior</option>
                                    <option value="Exhaust">Exhaust</option>
                                    <option value="Transmission">Transmission</option>
                                    <option value="Cooling System">Cooling System</option>
                                    <option value="Fuel System">Fuel System</option>
                                    <option value="Steering">Steering</option>
                                    <option value="Air Conditioning">Air Conditioning</option>
                                    <option value="Audio & Electronics">Audio & Electronics</option>
                                    <option value="Safety & Security">Safety & Security</option>
                                    <option value="Tools & Accessories">Tools & Accessories</option>
                                    <option value="Oils & Fluids">Oils & Fluids</option>
                                    <option value="Filters">Filters</option>
                                    <option value="Belts & Hoses">Belts & Hoses</option>
                                    <option value="Sensors">Sensors</option>
                                    <option value="Clutch">Clutch</option>
                                    <option value="Differential">Differential</option>
                                    <option value="Axles">Axles</option>
                                    <option value="Drivetrain">Drivetrain</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Car Model</label>
                                <input type="text" name="car_model" id="car_model" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>

                        <!-- Condition, Price, and Availability -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                                <select name="condition" id="condition" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Condition</option>
                                    <option value="New">New</option>
                                    <option value="Used">Used</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (₱)</label>
                                <input type="number" name="price" id="price" step="0.01" min="0" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Availability</label>
                                <select name="availability" id="availability" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="In Stock">In Stock</option>
                                    <option value="Out of Stock">Out of Stock</option>
                                </select>
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                            <div class="mt-1 border-2 border-gray-300 border-dashed rounded-xl hover:border-primary transition-colors">
                                <!-- Upload Zone -->
                                <div id="createUploadZone" class="flex justify-center px-6 pt-5 pb-6">
                                    <div class="space-y-1 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-accent focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                    <span>Upload a file</span>
                                                    <input id="image" name="image" type="file" accept="image/*" class="sr-only" onchange="previewImage(this, 'createPreview')">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Image Preview -->
                                <div id="createPreview" class="hidden p-4">
                                    <div class="flex items-center justify-center">
                                        <div class="relative">
                                            <img id="createPreviewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-gray-200 shadow-sm">
                                            <button type="button" onclick="removeImage('createPreview')" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="description" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-3 px-6 rounded-xl transition-all duration-300">
                            Add Product
                        </button>
                        <button type="button" onclick="closeCreateProductModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all duration-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-start justify-center min-h-screen p-4 pt-8 pb-8">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Edit Product</h3>
                    <button onclick="closeEditProductModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="editProductForm" method="POST" action="" enctype="multipart/form-data" class="p-4 lg:p-6">
                    <input type="hidden" name="action" value="edit_product">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <input type="hidden" name="current_image" id="edit_current_image">
                    
                    <div class="space-y-4 mb-6">
                        <!-- Product Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                            <input type="text" name="name" id="edit_name" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Category and Car Model -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select name="category" id="edit_category" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Category</option>
                                    <option value="Engine">Engine</option>
                                    <option value="Brakes">Brakes</option>
                                    <option value="Electrical">Electrical</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Wheels & Tires">Wheels & Tires</option>
                                    <option value="Body Parts">Body Parts</option>
                                    <option value="Bumpers">Bumpers</option>
                                    <option value="Lights">Lights</option>
                                    <option value="Interior">Interior</option>
                                    <option value="Exhaust">Exhaust</option>
                                    <option value="Transmission">Transmission</option>
                                    <option value="Cooling System">Cooling System</option>
                                    <option value="Fuel System">Fuel System</option>
                                    <option value="Steering">Steering</option>
                                    <option value="Air Conditioning">Air Conditioning</option>
                                    <option value="Audio & Electronics">Audio & Electronics</option>
                                    <option value="Safety & Security">Safety & Security</option>
                                    <option value="Tools & Accessories">Tools & Accessories</option>
                                    <option value="Oils & Fluids">Oils & Fluids</option>
                                    <option value="Filters">Filters</option>
                                    <option value="Belts & Hoses">Belts & Hoses</option>
                                    <option value="Sensors">Sensors</option>
                                    <option value="Clutch">Clutch</option>
                                    <option value="Differential">Differential</option>
                                    <option value="Axles">Axles</option>
                                    <option value="Drivetrain">Drivetrain</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Car Model</label>
                                <input type="text" name="car_model" id="edit_car_model" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>

                        <!-- Condition, Price, and Availability -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                                <select name="condition" id="edit_condition" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Condition</option>
                                    <option value="New">New</option>
                                    <option value="Used">Used</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (₱)</label>
                                <input type="number" name="price" id="edit_price" step="0.01" min="0" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Availability</label>
                                <select name="availability" id="edit_availability" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="In Stock">In Stock</option>
                                    <option value="Out of Stock">Out of Stock</option>
                                </select>
                            </div>
                        </div>

                        <!-- Current Image Display -->
                        <div id="currentImageContainer" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <img id="currentImage" src="" alt="Current product image" class="w-24 h-24 object-cover rounded-lg border border-gray-200 shadow-sm">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition-all duration-200 rounded-lg flex items-center justify-center">
                                        <span class="text-white text-xs font-medium opacity-0 hover:opacity-100 transition-opacity">Current</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600">This is the current product image. Upload a new image below to replace it.</p>
                                </div>
                            </div>
                        </div>

                        <!-- New Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Image (optional)</label>
                            <div class="mt-1 border-2 border-gray-300 border-dashed rounded-xl hover:border-primary transition-colors">
                                <!-- Upload Zone -->
                                <div id="editUploadZone" class="flex justify-center px-6 pt-5 pb-6">
                                    <div class="space-y-1 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="edit_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-accent focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                    <span>Upload a new image</span>
                                                    <input id="edit_image" name="image" type="file" accept="image/*" class="sr-only" onchange="previewImage(this, 'editPreview')">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                            <p class="text-xs text-gray-400 mt-1">Leave empty to keep current image</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- New Image Preview -->
                                <div id="editPreview" class="hidden p-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New Image Preview</label>
                                    <div class="flex items-center justify-center">
                                        <div class="relative">
                                            <img id="editPreviewImg" src="" alt="New image preview" class="w-32 h-32 object-cover rounded-lg border border-gray-200 shadow-sm">
                                            <button type="button" onclick="removeImage('editPreview')" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 text-center mt-2">This will replace the current image when you save.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="edit_description" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-3 px-6 rounded-xl transition-all duration-300">
                            Update Product
                        </button>
                        <button type="button" onclick="closeEditProductModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all duration-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteProductModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-start justify-center min-h-screen p-4 pt-8 pb-8">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Confirm Delete</h3>
                    <button onclick="closeDeleteProductModal()" class="text-gray-400 hover:text-gray-600">
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
                            <h4 class="text-lg font-semibold text-gray-900">Delete Product</h4>
                            <p class="text-sm text-gray-600">This action cannot be undone.</p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-700 mb-2">Are you sure you want to delete this product?</p>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-primary to-accent rounded-full flex items-center justify-center">
                                <i class="fas fa-box text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900" id="deleteProductName"></p>
                            </div>
                        </div>
                    </div>

                    <form id="deleteProductForm" method="POST" action="">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="product_id" id="delete_product_id">
                        
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                            <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Product
                            </button>
                            <button type="button" onclick="closeDeleteProductModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all duration-300">
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

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            const mobileCards = document.querySelectorAll('.lg\\:hidden > div');
            
            // Search in desktop table
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Search in mobile cards
            mobileCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Modal functions
        function openCreateProductModal() {
            document.getElementById('createProductModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCreateProductModal() {
            document.getElementById('createProductModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('createProductForm').reset();
            // Reset image preview and upload zone
            document.getElementById('createPreview').classList.add('hidden');
            document.getElementById('createUploadZone').classList.remove('hidden');
        }

        function openEditProductModal(productId) {
            // Fetch product data and populate form
            fetch(`../../api/get_product_data.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        document.getElementById('edit_product_id').value = product.id;
                        document.getElementById('edit_name').value = product.name;
                        document.getElementById('edit_category').value = product.category;
                        document.getElementById('edit_car_model').value = product.car_model;
                        document.getElementById('edit_price').value = product.price;
                        document.getElementById('edit_condition').value = product.condition;
                        document.getElementById('edit_availability').value = product.availability;
                        document.getElementById('edit_description').value = product.description;
                        document.getElementById('edit_current_image').value = product.image || '';
                        
                        // Show current image if exists
                        const currentImageContainer = document.getElementById('currentImageContainer');
                        const currentImage = document.getElementById('currentImage');
                        if (product.image) {
                            currentImage.src = '../../' + product.image;
                            currentImageContainer.classList.remove('hidden');
                        } else {
                            currentImageContainer.classList.add('hidden');
                        }
                        
                        document.getElementById('editProductModal').classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    } else {
                        showToast('Failed to load product data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to load product data', 'error');
                });
        }

        function closeEditProductModal() {
            document.getElementById('editProductModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Reset image preview and upload zone
            document.getElementById('editPreview').classList.add('hidden');
            document.getElementById('editUploadZone').classList.remove('hidden');
        }

        function openDeleteProductModal(productId, productName) {
            document.getElementById('delete_product_id').value = productId;
            document.getElementById('deleteProductName').textContent = productName;
            
            document.getElementById('deleteProductModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteProductModal() {
            document.getElementById('deleteProductModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Image preview functions
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const previewImg = document.getElementById(previewId + 'Img');
            const uploadZone = previewId === 'createPreview' ? document.getElementById('createUploadZone') : document.getElementById('editUploadZone');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                    uploadZone.classList.add('hidden');
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removeImage(previewId) {
            const preview = document.getElementById(previewId);
            const input = previewId === 'createPreview' ? document.getElementById('image') : document.getElementById('edit_image');
            const uploadZone = previewId === 'createPreview' ? document.getElementById('createUploadZone') : document.getElementById('editUploadZone');
            
            preview.classList.add('hidden');
            uploadZone.classList.remove('hidden');
            input.value = '';
        }

        // Product actions
        function editProduct(productId) {
            openEditProductModal(productId);
        }

        function deleteProduct(productId, productName) {
            openDeleteProductModal(productId, productName);
        }

        // Close modal when clicking outside
        document.getElementById('createProductModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateProductModal();
            }
        });

        document.getElementById('editProductModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditProductModal();
            }
        });

        document.getElementById('deleteProductModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteProductModal();
            }
        });

        // Show toast notifications after page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                <?php if (isset($_GET['success'])): ?>
                <?php 
                $successMessage = '';
                switch ($_GET['success']) {
                    case '1':
                        $successMessage = 'Product created successfully!';
                        break;
                    case '2':
                        $successMessage = 'Product updated successfully!';
                        break;
                    case '3':
                        $successMessage = 'Product deleted successfully!';
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
