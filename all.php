<?php
/**
 * All Products Page - Displays products categorized and fetched from DB.
 * Location: all.php
 */

// 1. Initialize session and DB connection
session_start();
require_once __DIR__ . '/includes/config.php'; 

// Check login status and safely retrieve cart count
$is_logged_in = isset($_SESSION['user_id']);
$user_name = htmlspecialchars($_SESSION['user_name'] ?? null); 
$cart_item_count = array_sum($_SESSION['cart'] ?? []); 

// --- DATABASE PRODUCT LOGIC ---
$products = [];
$categorized_products = [];

try {
    $pdo = connectDB();
    // Fetch all products, excluding placeholder/conditional flags unless they are real DB fields
    $stmt = $pdo->query("SELECT id, name, category, price, description, image_url, is_exclusive_offer FROM products ORDER BY category ASC, name ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group Products by Category
    foreach ($products as $product) {
        $category = $product['category'];
        if (!isset($categorized_products[$category])) {
            $categorized_products[$category] = [];
        }
        $categorized_products[$category][] = $product;
    }
} catch (\PDOException $e) {
    error_log("DB Error fetching products on all.php: " . $e->getMessage());
}
// --- END DATABASE PRODUCT LOGIC ---

// Function to format currency
function format_price($amount) {
    return '₹' . number_format($amount, 2);
}

// Function to generate unique anchor ID from category name
function get_category_id($category) {
    return strtolower(str_replace(' ', '-', $category));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Evocart</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#0D9488', // Teal accent color
                        'dark-bg': '#0F172A', // Slate 900
                        'card-bg': '#1E293B', // Slate 800
                        'exclusive': '#F59E0B', // Amber for exclusive offers
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'], 
                    },
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-dark-bg text-gray-100 min-h-screen">

    <header class="sticky top-0 z-50 bg-dark-bg bg-opacity-95 shadow-lg backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-primary tracking-widest">EVOCART</h1>
            
            <div class="flex items-center space-x-4">
                <a href="order_status.php" class="text-gray-300 hover:text-primary transition duration-300 hidden sm:inline"><i class="fas fa-truck"></i> Track Order</a>
                <a href="index.php" class="text-gray-300 hover:text-primary transition duration-300 hidden sm:inline"><i class="fas fa-home"></i> Home</a>
                <?php if ($is_logged_in): ?>
                    <span class="text-sm font-semibold text-primary hidden sm:block">Hello, <?php echo $user_name; ?>!</span>
                <?php endif; ?>

                <a href="carts.php" class="bg-primary hover:bg-teal-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 flex items-center space-x-2 relative">
                    <i class="fas fa-shopping-cart text-lg"></i>
                    <?php if ($cart_item_count > 0): ?>
                        <span class="font-bold absolute -top-1 -right-1 bg-red-600 text-xs w-5 h-5 flex items-center justify-center rounded-full border-2 border-dark-bg">
                            <?php echo $cart_item_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <h1 class="text-4xl font-bold text-white mb-4 border-b border-primary/50 pb-2">
            Explore All Collections
        </h1>
        <p class="text-gray-400 text-lg mb-10">
            Browse our full inventory, categorized for easy navigation.
        </p>

        <?php if (empty($categorized_products)): ?>
            <div class="text-center py-12">
                <p class="text-xl text-gray-500">No products are currently available in the store.</p>
            </div>
        <?php else: ?>
            <nav class="sticky top-20 z-40 bg-card-bg/90 backdrop-blur-sm p-3 rounded-xl shadow-xl mb-12 border border-gray-700">
                <ul class="flex overflow-x-auto space-x-6 whitespace-nowrap py-1">
                    <?php foreach (array_keys($categorized_products) as $category): ?>
                        <li>
                            <a href="#<?php echo get_category_id($category); ?>" 
                               class="text-gray-300 hover:text-primary font-semibold text-sm sm:text-base px-3 py-1 rounded-lg transition duration-300 hover:bg-dark-bg">
                                <?php echo htmlspecialchars($category); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            
            <?php foreach ($categorized_products as $category => $products_in_category): ?>
                <section id="<?php echo get_category_id($category); ?>" class="py-10">
                    <h2 class="text-3xl font-bold text-primary mb-6 border-b border-gray-700 pb-2">
                        <i class="fas fa-tags mr-3"></i><?php echo htmlspecialchars($category); ?>
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                        <?php foreach ($products_in_category as $product): ?>
                            <div class="bg-card-bg p-6 rounded-xl shadow-xl hover:shadow-primary/20 transition duration-300 relative">
                                <?php if ($product['is_exclusive_offer']): ?>
                                    <span class="absolute top-3 right-3 bg-exclusive text-dark-bg text-xs font-bold px-2 py-1 rounded-full uppercase">EXCL</span>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'uploads/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-48 object-cover rounded-lg mb-4">
                                <h4 class="text-xl font-bold mt-1 mb-2 text-white hover:text-primary transition duration-300"><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p class="text-gray-400 mb-4 text-sm h-10 overflow-hidden"><?php echo htmlspecialchars($product['description']); ?></p>
                                
                                <div class="flex justify-between items-center pt-2 border-t border-gray-700">
                                    <span class="text-xl font-extrabold text-white">
                                        <?php echo format_price($product['price']); ?>
                                    </span>
                                    
                                    <form action="carts.php" method="POST">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit"
                                                 class="text-sm bg-primary hover:bg-teal-700 text-white font-semibold py-2 px-3 rounded-lg transition duration-300 flex items-center space-x-1">
                                            <i class="fas fa-cart-plus"></i>
                                            <span>Add to Cart</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>

    <footer class="bg-card-bg mt-24 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> Evocart. All rights reserved. | Core PHP & Tailwind.</p>
        </div>
    </footer>
</body>
</html>