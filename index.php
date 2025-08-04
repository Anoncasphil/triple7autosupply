<?php
// Include database connection
require_once 'config/database.php';

// Get database instance
$db = db();

// Contact form processing removed - now using direct social media links

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Pagination settings
$itemsPerPage = 6; // Show 6 products per page (2 rows of 3 products each)
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Build query
$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(name LIKE ? OR car_model LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($category)) {
    $whereConditions[] = "category = ?";
    $params[] = $category;
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Get products with pagination
try {
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as count FROM products $whereClause";
    $totalProducts = $db->fetch($countSql, $params)['count'];
    $totalPages = ceil($totalProducts / $itemsPerPage);
    
    // Get products for current page
    $sql = "SELECT * FROM products $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $products = $db->fetchAll($sql, array_merge($params, [$itemsPerPage, $offset]));
    
    // Get unique categories for filter
    $categories = $db->fetchAll("SELECT DISTINCT category FROM products ORDER BY category");
} catch (Exception $e) {
    $products = [];
    $categories = [];
    $totalProducts = 0;
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Triple 7 Auto Supply</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
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
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-md shadow-lg border-b border-green-100/30 fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center space-x-4">
                            <img src="assets/images/logo.png" alt="Triple 7 Auto Supply Logo" class="h-10 w-auto">
                            <div>
                                <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-green-dark bg-clip-text text-transparent">
                                    Triple 7 Auto Supply
                                </h1>
                                <p class="text-xs text-gray-500 -mt-1 font-medium">Premium Auto Parts</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side with Navigation and Mobile Menu -->
                <div class="flex items-center space-x-6">
                    <!-- Desktop Navigation -->
                    <div class="hidden lg:block">
                        <div class="flex items-center space-x-2">
                            <a href="#home" class="group relative px-4 py-2 text-gray-700 hover:text-primary font-medium transition-all duration-300 rounded-lg hover:bg-green-50/80">
                                <span class="flex items-center">
                                    <i class="fas fa-home mr-2 text-sm"></i>
                                    Home
                                </span>
                                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-primary group-hover:w-1/2 transition-all duration-300 rounded-full"></span>
                            </a>
                            <a href="#products" class="group relative px-4 py-2 text-gray-700 hover:text-primary font-medium transition-all duration-300 rounded-lg hover:bg-green-50/80">
                                <span class="flex items-center">
                                    <i class="fas fa-boxes mr-2 text-sm"></i>
                                    Products
                                </span>
                                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-primary group-hover:w-1/2 transition-all duration-300 rounded-full"></span>
                            </a>
                            <a href="#about" class="group relative px-4 py-2 text-gray-700 hover:text-primary font-medium transition-all duration-300 rounded-lg hover:bg-green-50/80">
                                <span class="flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-sm"></i>
                                    About
                                </span>
                                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-primary group-hover:w-1/2 transition-all duration-300 rounded-full"></span>
                            </a>
                            <a href="#contact" class="group relative px-4 py-2 text-gray-700 hover:text-primary font-medium transition-all duration-300 rounded-lg hover:bg-green-50/80">
                                <span class="flex items-center">
                                    <i class="fas fa-envelope mr-2 text-sm"></i>
                                    Contact
                                </span>
                                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-primary group-hover:w-1/2 transition-all duration-300 rounded-full"></span>
                            </a>
                            <a href="#location" class="group relative px-4 py-2 text-gray-700 hover:text-primary font-medium transition-all duration-300 rounded-lg hover:bg-green-50/80">
                                <span class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-sm"></i>
                                    Location
                                </span>
                                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-primary group-hover:w-1/2 transition-all duration-300 rounded-full"></span>
                            </a>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <button class="lg:hidden flex items-center justify-center w-10 h-10 bg-green-50 hover:bg-green-100 text-primary rounded-lg transition-all duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-bars text-base"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div class="lg:hidden hidden bg-white/90 backdrop-blur-md border-t border-green-100/30">
            <div class="px-4 pt-4 pb-6 space-y-2">
                <a href="#home" class="flex items-center px-4 py-3 text-gray-700 hover:text-primary hover:bg-green-50/80 rounded-lg transition-all duration-300 font-medium">
                    <i class="fas fa-home mr-4 text-lg"></i>
                    Home
                </a>
                <a href="#products" class="flex items-center px-4 py-3 text-gray-700 hover:text-primary hover:bg-green-50/80 rounded-lg transition-all duration-300 font-medium">
                    <i class="fas fa-boxes mr-4 text-lg"></i>
                    Products
                </a>
                <a href="#about" class="flex items-center px-4 py-3 text-gray-700 hover:text-primary hover:bg-green-50/80 rounded-lg transition-all duration-300 font-medium">
                    <i class="fas fa-info-circle mr-4 text-lg"></i>
                    About
                </a>
                <a href="#contact" class="flex items-center px-4 py-3 text-gray-700 hover:text-primary hover:bg-green-50/80 rounded-lg transition-all duration-300 font-medium">
                    <i class="fas fa-envelope mr-4 text-lg"></i>
                    Contact
                </a>
                <a href="#location" class="flex items-center px-4 py-3 text-gray-700 hover:text-primary hover:bg-green-50/80 rounded-lg transition-all duration-300 font-medium">
                    <i class="fas fa-map-marker-alt mr-4 text-lg"></i>
                    Location
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="pt-20 text-white min-h-[60vh] flex items-center relative overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('assets/images/background-hero.png');"></div>
        
        <!-- Green Overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary/80 via-green-dark/70 to-accent/80"></div>
        
        <!-- Additional Overlay for Better Text Readability -->
        <div class="absolute inset-0 bg-black/20"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="text-left">
                    <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                        Find Parts for 
                        <span class="text-green-light bg-gradient-to-r from-green-light to-green-300 bg-clip-text text-transparent">Your Vehicle</span>
                    </h1>
                    
                    <p class="text-xl md:text-2xl text-gray-100 mb-8 leading-relaxed">
                        Quality surplus parts, expert service, and affordable prices since 2023
                    </p>
                    
                    <!-- Call to Action -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <a href="#products" class="bg-gradient-to-r from-accent to-green-dark hover:from-green-dark hover:to-accent text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 text-lg hover:scale-105 shadow-xl hover:shadow-2xl inline-flex items-center justify-center group">
                            <i class="fas fa-shopping-cart mr-3 group-hover:rotate-12 transition-transform duration-300"></i>
                            Browse Parts
                        </a>
                        <a href="#contact" class="border-2 border-white hover:bg-white hover:text-primary text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 text-lg hover:scale-105 hover:shadow-xl inline-flex items-center justify-center group">
                            <i class="fas fa-phone mr-3 group-hover:rotate-12 transition-transform duration-300"></i>
                            Get Quote
                        </a>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="flex flex-wrap gap-4 items-center">
                        <div class="flex items-center bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 border border-white/20 hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-check-circle text-green-light mr-2"></i>
                            <span class="text-sm font-medium">Tested Parts</span>
                        </div>
                        <div class="flex items-center bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 border border-white/20 hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-dollar-sign text-green-light mr-2"></i>
                            <span class="text-sm font-medium">Affordable Prices</span>
                        </div>
                        <div class="flex items-center bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 border border-white/20 hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-recycle text-green-light mr-2"></i>
                            <span class="text-sm font-medium">Eco-Friendly</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content -->
                <div class="text-center lg:text-right">
                    <div class="bg-white/15 backdrop-blur-sm rounded-3xl p-8 border border-white/30 shadow-2xl">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="text-center group">
                                <div class="bg-gradient-to-br from-accent to-green-dark rounded-2xl p-4 mb-4 inline-block shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-cog text-3xl text-white"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-2">Engine Parts</h3>
                                <p class="text-gray-200 text-sm">Engine components</p>
                            </div>
                            <div class="text-center group">
                                <div class="bg-gradient-to-br from-green-light to-accent rounded-2xl p-4 mb-4 inline-block shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-car text-3xl text-white"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-2">Body Parts</h3>
                                <p class="text-gray-200 text-sm">Exterior & interior parts</p>
                            </div>
                            <div class="text-center group">
                                <div class="bg-gradient-to-br from-green-dark to-primary rounded-2xl p-4 mb-4 inline-block shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-car-battery text-3xl text-white"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-2">Electrical</h3>
                                <p class="text-gray-200 text-sm">Batteries & components</p>
                            </div>
                            <div class="text-center group">
                                <div class="bg-gradient-to-br from-primary to-green-dark rounded-2xl p-4 mb-4 inline-block shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-cogs text-3xl text-white"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-2">Transmission</h3>
                                <p class="text-gray-200 text-sm">Transmission parts</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-20 bg-gradient-to-br from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Search and Filter Section -->
            <div class="mb-12">
                <div class="text-center mb-8">
                    <h3 class="text-4xl font-bold text-gray-900 mb-4">Our Products</h3>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">Browse our complete collection of automotive parts and accessories</p>
                </div>
                
                <!-- Search Form -->
                <form method="GET" action="#products" class="max-w-4xl mx-auto">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <div class="flex-1">
                            <div class="relative">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products, car models, or descriptions..." class="w-full pl-12 pr-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-lg">
                                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
            </div>
                    </div>
                        <div class="lg:w-64">
                            <select name="category" class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-lg">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                            <?php if (!empty($search) || !empty($category)): ?>
                                <a href="index.php#products" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg">
                                    <i class="fas fa-times mr-2"></i>Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                
                <!-- Results Summary -->
                <div class="text-center mt-6">
                    <p class="text-lg text-gray-600">
                        <?php if (!empty($search) || !empty($category)): ?>
                            Found <span class="font-bold text-primary"><?php echo $totalProducts; ?></span> product<?php echo $totalProducts !== 1 ? 's' : ''; ?>
                            <?php if (!empty($search)): ?>
                                for "<span class="font-bold text-primary"><?php echo htmlspecialchars($search); ?></span>"
                            <?php endif; ?>
                            <?php if (!empty($category)): ?>
                                in <span class="font-bold text-primary"><?php echo htmlspecialchars($category); ?></span> category
                            <?php endif; ?>
                        <?php else: ?>
                            Showing <span class="font-bold text-primary"><?php echo $totalProducts; ?></span> total products
                        <?php endif; ?>
                        <?php if ($totalPages > 1): ?>
                            <span class="text-gray-500">• Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
                        <?php endif; ?>
                    </p>
                    </div>
                        </div>
            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 min-h-[600px]">
                <?php if (empty($products)): ?>
                    <!-- No Products Found -->
                    <div class="col-span-full text-center py-16">
                        <div class="bg-white rounded-2xl shadow-lg p-12">
                            <i class="fas fa-search text-6xl text-gray-300 mb-6"></i>
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">No Products Found</h3>
                            <p class="text-gray-600 mb-6">
                                <?php if (!empty($search) || !empty($category)): ?>
                                    We couldn't find any products matching your search criteria.
                                <?php else: ?>
                                    No products are currently available.
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($search) || !empty($category)): ?>
                                <a href="index.php#products" class="bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-3 px-6 rounded-xl transition-all duration-300">
                                    <i class="fas fa-times mr-2"></i>Clear Search
                                </a>
                            <?php endif; ?>
                    </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group h-[420px] flex flex-col cursor-pointer" onclick="openProductModal(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                            <!-- Product Image -->
                            <div class="h-40 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center group-hover:bg-gradient-to-br group-hover:from-green-50 group-hover:to-green-100 transition-all duration-300 overflow-hidden">
                                <?php if ($product['image']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                <?php else: ?>
                                    <i class="fas fa-box text-6xl text-gray-400 group-hover:text-primary transition-colors duration-300"></i>
                                <?php endif; ?>
                </div>

                            <!-- Product Info -->
                            <div class="p-5 flex flex-col flex-1">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $product['availability'] === 'In Stock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo htmlspecialchars($product['availability']); ?>
                                    </span>
                    </div>
                                
                                <div class="space-y-1.5 mb-4">
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-tag mr-1"></i>
                                        <?php echo htmlspecialchars($product['category']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-car mr-1"></i>
                                        <?php echo htmlspecialchars($product['car_model']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <?php echo htmlspecialchars($product['condition']); ?>
                                    </p>
                        </div>
                                
                                <div class="flex justify-between items-center mt-auto">
                                    <span class="text-xl font-bold text-primary">₱<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="text-xs text-gray-500">Click to view details</span>
                    </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-12 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="text-sm text-gray-500 text-center sm:text-left">
                        <?php 
                        $startItem = $offset + 1;
                        $endItem = min($offset + $itemsPerPage, $totalProducts);
                        ?>
                        Showing <?php echo $startItem; ?> to <?php echo $endItem; ?> of <?php echo $totalProducts; ?> results
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <!-- Previous Button -->
                        <?php 
                        $prevPage = $currentPage - 1;
                        $prevUrl = "?page=$prevPage";
                        if (!empty($search)) $prevUrl .= "&search=" . urlencode($search);
                        if (!empty($category)) $prevUrl .= "&category=" . urlencode($category);
                        ?>
                        <a href="#products<?php echo $prevUrl; ?>" 
                           class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 <?php echo $currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                           <?php echo $currentPage <= 1 ? 'onclick="return false;"' : ''; ?>>
                            <i class="fas fa-chevron-left mr-1"></i>Previous
                        </a>
                        
                        <!-- Page Numbers -->
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        // Show first page if not in range
                        if ($startPage > 1) {
                            $firstUrl = "?page=1";
                            if (!empty($search)) $firstUrl .= "&search=" . urlencode($search);
                            if (!empty($category)) $firstUrl .= "&category=" . urlencode($category);
                            echo '<a href="#products' . $firstUrl . '" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">1</a>';
                            if ($startPage > 2) {
                                echo '<span class="px-2 text-gray-400">...</span>';
                            }
                        }
                        
                        // Show page numbers
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $pageUrl = "?page=$i";
                            if (!empty($search)) $pageUrl .= "&search=" . urlencode($search);
                            if (!empty($category)) $pageUrl .= "&category=" . urlencode($category);
                            
                            $activeClass = $i === $currentPage ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700';
                            echo '<a href="#products' . $pageUrl . '" class="px-3 py-2 text-sm rounded-lg ' . $activeClass . '">' . $i . '</a>';
                        }
                        
                        // Show last page if not in range
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<span class="px-2 text-gray-400">...</span>';
                            }
                            $lastUrl = "?page=$totalPages";
                            if (!empty($search)) $lastUrl .= "&search=" . urlencode($search);
                            if (!empty($category)) $lastUrl .= "&category=" . urlencode($category);
                            echo '<a href="#products' . $lastUrl . '" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">' . $totalPages . '</a>';
                        }
                        ?>
                        
                        <!-- Next Button -->
                        <?php 
                        $nextPage = $currentPage + 1;
                        $nextUrl = "?page=$nextPage";
                        if (!empty($search)) $nextUrl .= "&search=" . urlencode($search);
                        if (!empty($category)) $nextUrl .= "&category=" . urlencode($category);
                        ?>
                        <a href="#products<?php echo $nextUrl; ?>" 
                           class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 <?php echo $currentPage >= $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                           <?php echo $currentPage >= $totalPages ? 'onclick="return false;"' : ''; ?>>
                            Next<i class="fas fa-chevron-right ml-1"></i>
                        </a>
                        </div>
                    </div>
            <?php endif; ?>
                </div>
    </section>

    <!-- Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <!-- Modal Header -->
                <div class="flex justify-between items-start mb-6">
                    <h2 id="modalProductName" class="text-3xl font-bold text-gray-900"></h2>
                    <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors duration-300">
                        <i class="fas fa-times"></i>
                            </button>
                        </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Product Image -->
                    <div class="lg:order-1">
                        <div id="modalProductImage" class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl h-80 flex items-center justify-center overflow-hidden">
                            <!-- Image will be inserted here by JavaScript -->
                    </div>
                </div>

                    <!-- Product Details -->
                    <div class="lg:order-2">
                        <div class="space-y-6">
                            <!-- Price and Availability -->
                        <div class="flex justify-between items-center">
                                <span id="modalProductPrice" class="text-4xl font-bold text-primary"></span>
                                <span id="modalProductAvailability" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"></span>
                        </div>
                            
                            <!-- Product Information -->
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <i class="fas fa-tag text-green-light mr-3 text-lg"></i>
                                    <div>
                                        <span class="text-sm text-gray-500">Category</span>
                                        <p id="modalProductCategory" class="font-semibold text-gray-900"></p>
                    </div>
                </div>
                                
                                <div class="flex items-center">
                                    <i class="fas fa-car text-green-light mr-3 text-lg"></i>
                                    <div>
                                        <span class="text-sm text-gray-500">Car Model</span>
                                        <p id="modalProductCarModel" class="font-semibold text-gray-900"></p>
            </div>
        </div>
                                
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle text-green-light mr-3 text-lg"></i>
                                    <div>
                                        <span class="text-sm text-gray-500">Condition</span>
                                        <p id="modalProductCondition" class="font-semibold text-gray-900"></p>
                        </div>
                    </div>
                        </div>
                            
                            <!-- Description -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-3">Description</h4>
                                <p id="modalProductDescription" class="text-gray-600 leading-relaxed"></p>
                    </div>
                            
                            <!-- Action Buttons -->
                            <div class="pt-4">
                                <button onclick="contactUs()" class="w-full bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg">
                                    <i class="fas fa-phone mr-2"></i>Inquire Now
                                </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gradient-to-br from-white via-green-50 to-green-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Heading -->
            <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-6">About Triple 7 Auto Supply</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                        We are your trusted partner for all automotive needs, providing high-quality surplus parts and exceptional service since 2023.
                    </p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start mb-16">
                <!-- Left Content - About Details -->
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-6">Why Choose Triple 7 Auto Supply?</h3>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-primary text-white p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-calendar-alt text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Established in 2023</h4>
                                <p class="text-gray-600">New business with 20+ years of industry experience serving automotive enthusiasts and professionals.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-accent text-white p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-boxes text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Wide Product Range</h4>
                                <p class="text-gray-600">From engine components to body parts, we stock thousands of quality surplus automotive parts.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-green-dark text-white p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Expert Team</h4>
                                <p class="text-gray-600">Certified automotive technicians ready to help you find the perfect parts for your vehicle.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content - Mission Card -->
                <div class="relative">
                    <div class="bg-white rounded-3xl shadow-2xl p-8 border border-green-100">
                        <div class="text-center mb-8">
                            <div class="bg-gradient-to-r from-primary to-accent p-4 rounded-2xl inline-block mb-4">
                                <i class="fas fa-car text-4xl text-white"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Our Mission</h3>
                            <p class="text-gray-600">To provide quality surplus automotive parts at affordable prices while delivering exceptional customer service.</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-primary mb-2">20+</div>
                                <div class="text-sm text-gray-600">Years Experience</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-accent mb-2">1000+</div>
                                <div class="text-sm text-gray-600">Parts Available</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-dark mb-2">500+</div>
                                <div class="text-sm text-gray-600">Happy Customers</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-primary mb-2">Mon-Sat</div>
                                <div class="text-sm text-gray-600">Support Hours</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Decorative Elements -->
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-green-light rounded-full opacity-20"></div>
                    <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-accent rounded-full opacity-20"></div>
                </div>
            </div>
            
            <!-- Why Choose Us Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-2 group">
                    <div class="bg-gradient-to-br from-primary to-accent p-4 rounded-2xl mb-6 inline-block shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-medal text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Quality Assurance</h3>
                    <p class="text-gray-600">All our products meet or exceed OEM standards with comprehensive warranties.</p>
                </div>
                <div class="text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-2 group">
                    <div class="bg-gradient-to-br from-accent to-green-light p-4 rounded-2xl mb-6 inline-block shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-shipping-fast text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Fast Delivery</h3>
                    <p class="text-gray-600">Quick shipping and local pickup options available for your convenience.</p>
                </div>
                <div class="text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-2 group">
                    <div class="bg-gradient-to-br from-green-light to-primary p-4 rounded-2xl mb-6 inline-block shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-headset text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Expert Support</h3>
                    <p class="text-gray-600">Our automotive experts are here to help you find the right parts.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Contact Us</h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    Get in touch with us for any questions about our products or services
                </p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Left Column - Contact Information -->
                <div class="flex flex-col justify-center">
                    <h3 class="text-2xl font-bold mb-6">Contact Information</h3>
                    <p class="text-gray-300 mb-8 text-lg">Get in touch with us through our business details and operating hours.</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-green-light/20 p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-green-light text-xl"></i>
                            </div>
                <div>
                                <h4 class="font-semibold text-lg mb-1">Address</h4>
                                <p class="text-gray-300">60 KALIRAYA ST. COR BMA TATALON QUEZON CITY</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-light/20 p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-phone text-green-light text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Phone</h4>
                                <p class="text-gray-300">09614336074 / 09274847789</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-light/20 p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-envelope text-green-light text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Email</h4>
                                <p class="text-gray-300">triple7autoparts@gmail.com</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-light/20 p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-clock text-green-light text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Business Hours</h4>
                                <p class="text-gray-300">Monday to Saturday: 9AM to 5PM</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Info for Left Column -->
                    <div class="mt-8 p-6 bg-gray-800/50 rounded-xl border border-gray-700">
                        <h4 class="font-semibold text-lg mb-3 text-white">Visit Our Store</h4>
                        <div class="space-y-2 text-sm text-gray-300">
                        <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Browse our complete inventory</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Get expert advice in person</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Immediate pickup available</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Professional installation services</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Quick Contact -->
                <div class="flex flex-col justify-center">
                    <h3 class="text-2xl font-bold mb-6">Quick Contact</h3>
                    <p class="text-gray-300 mb-8 text-lg">Connect with us directly through your preferred platform for quick responses and personalized service.</p>
                    
                    <!-- Social Media Contact Buttons -->
                    <div class="space-y-4">
                        <!-- Facebook Button -->
                        <a href="https://www.facebook.com/triple7autosupplyshop" target="_blank" rel="noopener noreferrer" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-xl inline-flex items-center justify-center group">
                            <i class="fab fa-facebook text-2xl mr-4 group-hover:scale-110 transition-transform duration-300"></i>
                            <div class="text-left">
                                <div class="text-lg font-semibold">Chat on Facebook</div>
                                <div class="text-sm opacity-90">Get instant responses</div>
                            </div>
                            <i class="fas fa-external-link-alt ml-auto text-sm opacity-70"></i>
                        </a>
                        
                        <!-- WhatsApp Button -->
                        <a href="https://wa.me/639274847789" target="_blank" rel="noopener noreferrer" 
                           class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-xl inline-flex items-center justify-center group">
                            <i class="fab fa-whatsapp text-2xl mr-4 group-hover:scale-110 transition-transform duration-300"></i>
                            <div class="text-left">
                                <div class="text-lg font-semibold">Chat on WhatsApp</div>
                                <div class="text-sm opacity-90">Direct messaging</div>
                            </div>
                            <i class="fas fa-external-link-alt ml-auto text-sm opacity-70"></i>
                        </a>
                        
                        <!-- Phone Call Button -->
                        <a href="tel:+639274847789" 
                           class="w-full bg-accent hover:bg-green-dark text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-xl inline-flex items-center justify-center group">
                            <i class="fas fa-phone text-2xl mr-4 group-hover:scale-110 transition-transform duration-300"></i>
                            <div class="text-left">
                                <div class="text-lg font-semibold">Call Us Directly</div>
                                <div class="text-sm opacity-90">09274847789</div>
                            </div>
                            <i class="fas fa-phone-alt ml-auto text-sm opacity-70"></i>
                        </a>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="mt-8 p-6 bg-gray-800/50 rounded-xl border border-gray-700">
                        <h4 class="font-semibold text-lg mb-3 text-white">Why Chat With Us?</h4>
                        <div class="space-y-2 text-sm text-gray-300">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Instant responses to your questions</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Get quotes and pricing quickly</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Check product availability</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Schedule appointments or pickups</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section id="location" class="py-20 bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Find Us</h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    Visit our store location for the best selection of auto parts and professional service
                </p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Left Column - Interactive Map -->
                <div class="flex flex-col justify-center">
                    <h3 class="text-2xl font-bold mb-6">Interactive Map</h3>
                    <p class="text-gray-300 mb-8 text-lg">Find our exact location with our interactive Google Maps integration.</p>
                    
                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.5820617653885!2d121.00766281064153!3d14.62286837647222!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b615f6ae5f51%3A0xd2b1514de55bb691!2sTRIPLE%207%20AUTO%20SUPPLY!5e0!3m2!1sen!2sph!4v1754303233758!5m2!1sen!2sph" 
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            class="w-full h-80 lg:h-[400px]">
                        </iframe>
                    </div>
                    
                    <!-- Additional Info for Left Column -->
                    <div class="mt-8 p-6 bg-gray-800/50 rounded-xl border border-gray-700">
                        <h4 class="font-semibold text-lg mb-3 text-white">Map Features</h4>
                        <div class="space-y-2 text-sm text-gray-300">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Real-time location tracking</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Street view available</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Turn-by-turn directions</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-light mr-2"></i>
                                <span>Public transport routes</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Location Details -->
                <div class="flex flex-col justify-center">
                    <h3 class="text-2xl font-bold mb-6">Location Details</h3>
                    <p class="text-gray-300 mb-8 text-lg">Get all the information you need to visit our store location.</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-green-light/20 p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-green-light text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Address</h4>
                                <p class="text-gray-300">60 KALIRAYA ST. COR BMA TATALON QUEZON CITY</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-green-light/20 p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-clock text-green-light text-xl"></i>
                    </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Business Hours</h4>
                                <p class="text-gray-300">Monday to Saturday: 9AM to 5PM</p>
                                <p class="text-gray-400 text-sm">Closed on Sundays</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-green-light/20 p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-car text-green-light text-xl"></i>
                </div>
                <div>
                                <h4 class="font-semibold text-lg mb-1">Parking</h4>
                                <p class="text-gray-300">Free parking available</p>
                                <p class="text-gray-400 text-sm">Street parking and nearby lots</p>
                        </div>
                </div>
                        
                        <div class="flex items-start">
                            <div class="bg-green-light/20 p-3 rounded-xl mr-4 flex-shrink-0">
                                <i class="fas fa-bus text-green-light text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Transportation</h4>
                                <p class="text-gray-300">Near major bus routes and jeepney stops</p>
                                <p class="text-gray-400 text-sm">Easy access from main roads</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="mt-8">
                        <a href="https://maps.google.com/?q=TRIPLE+7+AUTO+SUPPLY+TATALON+QUEZON+CITY" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="w-full bg-gradient-to-r from-primary to-accent hover:from-green-dark hover:to-accent text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-xl inline-flex items-center justify-center group">
                            <i class="fas fa-directions text-2xl mr-4 group-hover:scale-110 transition-transform duration-300"></i>
                            <div class="text-left">
                                <div class="text-lg font-semibold">Get Directions</div>
                                <div class="text-sm opacity-90">Open in Google Maps</div>
                            </div>
                            <i class="fas fa-external-link-alt ml-auto text-sm opacity-70"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-gray-900 to-gray-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <div class="flex items-center mb-6">
                        <img src="assets/images/hero-logo.png" alt="Triple 7 Auto Supply" class="h-20 w-auto mr-6">
                    </div>
                    <p class="text-gray-300 mb-6 text-lg leading-relaxed">Your trusted partner for quality automotive parts and exceptional service.</p>
                    <div class="flex space-x-6">
                        <a href="https://www.facebook.com/triple7autosupplyshop" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-green-light transition-colors duration-300 transform hover:scale-110">
                            <i class="fab fa-facebook text-2xl"></i>
                        </a>
                        <a href="https://wa.me/639274847789" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-green-500 transition-colors duration-300 transform hover:scale-110">
                            <i class="fab fa-whatsapp text-2xl"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-xl mb-6 text-white">Quick Links</h4>
                    <ul class="space-y-4">
                        <li><a href="#home" class="text-gray-300 hover:text-green-light transition-colors duration-300 text-lg flex items-center group">
                            <i class="fas fa-chevron-right mr-3 text-green-light group-hover:translate-x-1 transition-transform duration-300"></i>
                            Home
                        </a></li>
                        <li><a href="#about" class="text-gray-300 hover:text-green-light transition-colors duration-300 text-lg flex items-center group">
                            <i class="fas fa-chevron-right mr-3 text-green-light group-hover:translate-x-1 transition-transform duration-300"></i>
                            About Us
                        </a></li>
                        <li><a href="#products" class="text-gray-300 hover:text-green-light transition-colors duration-300 text-lg flex items-center group">
                            <i class="fas fa-chevron-right mr-3 text-green-light group-hover:translate-x-1 transition-transform duration-300"></i>
                            Products
                        </a></li>
                        <li><a href="#contact" class="text-gray-300 hover:text-green-light transition-colors duration-300 text-lg flex items-center group">
                            <i class="fas fa-chevron-right mr-3 text-green-light group-hover:translate-x-1 transition-transform duration-300"></i>
                            Contact
                        </a></li>
                        <li><a href="#location" class="text-gray-300 hover:text-green-light transition-colors duration-300 text-lg flex items-center group">
                            <i class="fas fa-chevron-right mr-3 text-green-light group-hover:translate-x-1 transition-transform duration-300"></i>
                            Location
                        </a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-12 pt-8 text-center">
                <p class="text-gray-300 text-lg">&copy; 2024 Triple 7 Auto Supply. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Smooth Scrolling Script -->
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('button.lg\\:hidden');
        const mobileMenu = document.querySelector('.lg\\:hidden.hidden');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    if (!mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }
                }
            });
        });

        // Add to cart functionality
        document.querySelectorAll('button').forEach(button => {
            if (button.textContent.includes('Add to Cart')) {
                button.addEventListener('click', function() {
                    const productName = this.closest('.bg-white').querySelector('h3').textContent;
                    alert(`Added ${productName} to cart!`);
                });
            }
        });

        // Form submission (now handled by PHP)
        // Removed JavaScript form handling as it's now processed server-side

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('nav');
            if (window.scrollY > 50) {
                navbar.classList.add('bg-white/95', 'shadow-xl');
                navbar.classList.remove('bg-white/90');
            } else {
                navbar.classList.remove('bg-white/95', 'shadow-xl');
                navbar.classList.add('bg-white/90');
            }
        });

        // Product Modal Functions
        function openProductModal(product) {
            // Populate modal with product data
            document.getElementById('modalProductName').textContent = product.name;
            document.getElementById('modalProductPrice').textContent = '₱' + parseFloat(product.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('modalProductCategory').textContent = product.category;
            document.getElementById('modalProductCarModel').textContent = product.car_model;
            document.getElementById('modalProductCondition').textContent = product.condition;
            document.getElementById('modalProductDescription').textContent = product.description;
            
            // Set availability with proper styling
            const availabilityElement = document.getElementById('modalProductAvailability');
            availabilityElement.textContent = product.availability;
            if (product.availability === 'In Stock') {
                availabilityElement.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
            } else {
                availabilityElement.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
            }
            
            // Set product image
            const imageContainer = document.getElementById('modalProductImage');
            if (product.image) {
                imageContainer.innerHTML = `<img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover">`;
            } else {
                imageContainer.innerHTML = '<i class="fas fa-box text-6xl text-gray-400"></i>';
            }
            
            // Show modal
            document.getElementById('productModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
        }

        // Close modal when clicking outside
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProductModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProductModal();
            }
        });

        // Contact Us function
        function contactUs() {
            closeProductModal(); // Close the modal first
            // Scroll to contact section
            const contactSection = document.querySelector('#contact');
            if (contactSection) {
                contactSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    </script>
</body>
</html>
