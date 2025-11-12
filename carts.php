<?php
/**
 * Evocart — Shopping Cart Page (carts.php)
 * - Manages cart session data (add, update, remove) via POST requests.
 * - Dynamically fetches and displays full product details from the database.
 * - Displays a responsive, modern cart interface with subtotal/total calculation.
 */

session_start();
// Assuming config.php contains connectDB() function, same as index.php
require_once __DIR__ . '/includes/config.php';

// --- Helper functions for consistency and readability ---

function get_product_image_src($url) {
    if (!$url) {
        // Placeholder for products without an image
        return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&q=80&auto=format&fit=crop&crop=entropy';
    }
    if (filter_var($url, FILTER_VALIDATE_URL) || strpos($url, '//') === 0) {
        return htmlspecialchars($url);
    }
    // Adjust path if necessary based on your project structure
    return '/evocart/' . ltrim(htmlspecialchars($url), '/');
}

function format_price($price) {
    // Formats price consistently
    return '₹' . number_format($price, 2);
}

// --- Cart Initialization ---
$cart = $_SESSION['cart'] ?? [];
$cart_item_ids = array_keys($cart);
$cart_details = [];
$cart_total = 0;
$error_message = '';

// --- POST Handler: Add, Update, Remove cart items ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);

    // 1. ADD: Used by index.php for AJAX adding (sends JSON response)
    if ($action === 'add' && $product_id > 0) {
        $quantity = intval($_POST['quantity'] ?? 1);
        $quantity = max(1, $quantity); // ensure positive quantity

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }

        // Send success response for AJAX
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'cart_count' => array_sum($_SESSION['cart'])]);
        exit;
    }

    // 2. UPDATE QTY or REMOVE: Used by carts.php forms (redirects back to cart page)
    if (($action === 'update_qty' || $action === 'remove') && $product_id > 0) {
        if ($action === 'update_qty') {
            $quantity = intval($_POST['quantity'] ?? 1);
            $quantity = max(0, $quantity); // allow 0 to trigger removal
            
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$product_id]);
        }

        // Redirect back to cart page to show updated contents
        header('Location: carts.php');
        exit;
    }

    // 3. BUY NOW REDIRECT: Used by index.php (clears cart and redirects to checkout)
    if ($action === 'buy_now_redirect' && $product_id > 0) {
         // Clear cart and add only the 'buy now' item
        $_SESSION['cart'] = [$product_id => intval($_POST['quantity'] ?? 1)];
        // Assuming a checkout.php file exists
        header('Location: checkout.php'); 
        exit;
    }
}

// --- Data Fetching: Get details for all products in the cart session ---
// We re-read the session cart here in case it was modified by a non-AJAX POST above
$cart = $_SESSION['cart'] ?? [];
$cart_item_ids = array_keys($cart);

if (!empty($cart) && !empty($cart_item_ids)) {
    try {
        $pdo = connectDB();
        $placeholders = implode(',', array_fill(0, count($cart_item_ids), '?'));
        
        // Fetch only necessary columns
        $stmt = $pdo->prepare("SELECT id, name, price, image_url, description FROM products WHERE id IN ({$placeholders})");
        $stmt->execute($cart_item_ids);
        $db_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map fetched product details to cart quantities and calculate totals
        foreach ($db_products as $p) {
            $qty = $cart[$p['id']] ?? 0;
            if ($qty > 0) {
                $subtotal = $p['price'] * $qty;
                $cart_details[$p['id']] = [
                    'product' => $p,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                    'price' => $p['price'] // Keep original price for calculation
                ];
                $cart_total += $subtotal;
            }
        }

        // Cleanup session cart: remove items that couldn't be fetched (e.g., deleted from DB)
        $fetched_ids = array_keys($cart_details);
        $ids_to_remove = array_diff($cart_item_ids, $fetched_ids);
        foreach ($ids_to_remove as $id) {
            unset($_SESSION['cart'][$id]);
        }

    } catch (\PDOException $e) {
        error_log('DB Error fetching cart products: ' . $e->getMessage());
        $error_message = 'Failed to load cart details due to a database error. Please try again.';
    }
}

// Final cart item count for display
$cart_item_count = array_sum($_SESSION['cart'] ?? []);

?><!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Evocart — Your Shopping Cart</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="" crossorigin="anonymous" />

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#06b6d4',
                        accent: '#f59e0b',
                        darkbg: '#0b1220',
                        card: '#0f1724'
                    },
                    fontFamily: { poppins: ['Poppins','sans-serif'] },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0b1220; }
        .glass { background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); backdrop-filter: blur(8px); }
        .pattern-dots {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.06) 1px, transparent 2px);
            background-size: 10px 10px;
        }
        /* Hide number input arrows */
        input[type='number']::-webkit-inner-spin-button, 
        input[type='number']::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
    </style>
</head>
<body class="text-gray-100 antialiased">
<div class="min-h-screen">
    <!-- Header/Nav Bar (Simplified) -->
    <header class="sticky top-0 z-50 backdrop-blur-sm bg-black/40 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="index.php" class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-gradient-to-br from-primary to-accent rounded-full flex items-center justify-center shadow-xl">
                        <i class="fas fa-shopping-bag text-black text-lg"></i>
                    </div>
                    <div class="text-2xl font-bold">Evocart <span class="text-primary">Cart</span></div>
                </a>
                <div class="flex items-center gap-4">
                    <a href="index.php" class="text-sm px-3 py-2 rounded-md bg-white/5 hover:bg-primary/10 transition"><i class="fas fa-home mr-2"></i>Continue Shopping</a>
                    <div class="relative inline-flex items-center p-2 rounded-md bg-primary/90">
                        <i class="fas fa-shopping-cart text-black"></i>
                        <?php if ($cart_item_count > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-darkbg"><?php echo $cart_item_count; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-extrabold text-white mb-8">Your Shopping Cart</h1>

        <?php if ($error_message): ?>
            <div class="bg-red-900/50 border border-red-500 p-4 rounded-lg text-red-100 mb-6"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (empty($cart_details)): ?>
            <div class="text-center p-12 bg-card/50 rounded-xl pattern-dots">
                <i class="fas fa-box-open text-6xl text-gray-500 mb-4"></i>
                <h2 class="text-2xl font-semibold">Your cart is empty!</h2>
                <p class="text-gray-400 mt-2">Looks like you haven't added anything yet. Start shopping now.</p>
                <a href="index.php#products" class="mt-6 inline-block px-6 py-3 bg-primary text-black font-bold rounded-full hover:bg-cyan-500 transition">Go to Products</a>
            </div>
        <?php else: ?>
            <div class="lg:flex lg:space-x-8">
                <!-- Cart Items List (2/3 width on desktop) -->
                <div class="lg:w-2/3 space-y-4">
                    <?php foreach ($cart_details as $item): ?>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center bg-card p-4 rounded-xl shadow-lg border border-white/5">
                            <img src="<?php echo get_product_image_src($item['product']['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product']['name']); ?>" class="w-20 h-20 object-cover rounded-lg mb-4 sm:mb-0 sm:mr-4">
                            
                            <div class="flex-grow w-full sm:w-auto">
                                <h3 class="font-semibold text-lg text-primary"><?php echo htmlspecialchars($item['product']['name']); ?></h3>
                                <p class="text-sm text-gray-400">Unit Price: <?php echo format_price($item['product']['price']); ?></p>
                            </div>

                            <!-- Controls and Subtotal -->
                            <div class="flex items-center justify-between w-full sm:w-auto mt-4 sm:mt-0 sm:space-x-6">
                                <form action="carts.php" method="POST" class="flex items-center space-x-2 cart-update-form">
                                    <input type="hidden" name="action" value="update_qty">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <label for="qty-<?php echo $item['product']['id']; ?>" class="text-sm text-gray-400 hidden sm:block">Qty:</label>
                                    
                                    <!-- Simple +/- buttons for better mobile experience -->
                                    <button type="button" 
                                            onclick="changeQty(<?php echo $item['product']['id']; ?>, -1)" 
                                            class="p-2 bg-darkbg rounded-l-lg border border-r-0 border-white/10 hover:bg-white/5 transition"
                                            aria-label="Decrease Quantity">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>

                                    <input type="number" 
                                           id="qty-<?php echo $item['product']['id']; ?>"
                                           name="quantity" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="99" 
                                           class="w-12 text-center p-2 bg-darkbg border border-white/10 text-base" 
                                           onchange="this.form.submit()"
                                           aria-live="polite">

                                    <button type="button" 
                                            onclick="changeQty(<?php echo $item['product']['id']; ?>, 1)" 
                                            class="p-2 bg-darkbg rounded-r-lg border border-l-0 border-white/10 hover:bg-white/5 transition"
                                            aria-label="Increase Quantity">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </form>
                                
                                <div class="font-bold text-lg w-20 text-right ml-4 sm:ml-0">
                                    <?php echo format_price($item['subtotal']); ?>
                                </div>
                                
                                <form action="carts.php" method="POST" onsubmit="return confirmRemove(event)" class="ml-4">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-400 p-2 transition rounded-full hover:bg-white/5" aria-label="Remove item">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Summary Card (1/3 width on desktop) -->
                <div class="lg:w-1/3 mt-8 lg:mt-0 glass p-6 rounded-xl shadow-2xl h-fit border border-white/10">
                    <h2 class="text-2xl font-bold mb-4 border-b border-white/10 pb-3">Order Summary</h2>

                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-300">
                            <span>Subtotal (<?php echo $cart_item_count; ?> items)</span>
                            <span><?php echo format_price($cart_total); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Shipping</span>
                            <span class="text-green-400">FREE</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Taxes (Estimated)</span>
                            <span><?php echo format_price(0); ?></span>
                        </div>
                    </div>

                    <div class="border-t border-white/20 mt-4 pt-4 flex justify-between font-extrabold text-xl">
                        <span>Order Total</span>
                        <span class="text-primary"><?php echo format_price($cart_total); ?></span>
                    </div>

                   <form action="checkout.php" method="POST">
    <button type="submit" class="w-full mt-6 px-6 py-3 bg-accent text-black font-bold rounded-full text-lg hover:bg-yellow-500 transition">
        Proceed to Checkout <i class="fas fa-arrow-right ml-2"></i>
    </button>
</form>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Modal for confirmation (replaces alert) -->
<div id="confirmation-modal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
    <div class="bg-card p-6 rounded-2xl max-w-sm w-full shadow-2xl border border-red-500/50">
        <div class="text-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-3"></i>
            <h3 class="font-bold text-xl mb-2">Confirm Removal</h3>
            <p class="text-gray-300">Are you sure you want to remove this item from your cart?</p>
        </div>
        <div class="mt-6 flex justify-around">
            <button id="modal-cancel-btn" class="px-5 py-2 bg-white/10 rounded-lg hover:bg-white/20 transition">Cancel</button>
            <button id="modal-confirm-btn" class="px-5 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">Remove</button>
        </div>
    </div>
</div>

<script>
    /**
     * Finds the quantity input element for a given product ID.
     * @param {number} productId 
     * @returns {HTMLInputElement | null}
     */
    function getQuantityInput(productId) {
        return document.getElementById(`qty-${productId}`);
    }

    /**
     * Handles the logic for increasing or decreasing the quantity and submitting the form.
     * @param {number} productId 
     * @param {number} delta - either 1 (increase) or -1 (decrease)
     */
    function changeQty(productId, delta) {
        const input = getQuantityInput(productId);
        if (input) {
            let currentQty = parseInt(input.value, 10);
            let newQty = currentQty + delta;
            
            // Ensure minimum quantity is 1 if decreasing, otherwise allow 0 for removal
            if (newQty < 1) {
                newQty = 0;
            }

            input.value = newQty;

            // Find the parent form and submit it to update the session
            const form = input.closest('form');
            if (form) {
                form.submit();
            }
        }
    }

    /**
     * Handles the custom confirmation modal for item removal (replaces browser confirm).
     */
    function confirmRemove(e) {
        e.preventDefault(); // Stop the form submission
        const form = e.target;
        const modal = document.getElementById('confirmation-modal');
        const confirmBtn = document.getElementById('modal-confirm-btn');
        const cancelBtn = document.getElementById('modal-cancel-btn');

        // Show modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Handler for confirmation
        const confirmHandler = () => {
            modal.classList.add('hidden');
            form.submit(); // Submit the original form
            cleanupListeners();
        };

        // Handler for cancel
        const cancelHandler = () => {
            modal.classList.add('hidden');
            cleanupListeners();
        };

        // Cleanup function to avoid multiple event listeners
        const cleanupListeners = () => {
             confirmBtn.removeEventListener('click', confirmHandler);
             cancelBtn.removeEventListener('click', cancelHandler);
             window.onkeydown = null;
        }

        confirmBtn.addEventListener('click', confirmHandler);
        cancelBtn.addEventListener('click', cancelHandler);
        
        // Allow ESC key to close modal
        window.onkeydown = (e) => {
            if (e.key === 'Escape') cancelHandler();
        };

        return false; // Prevent default submission
    }
</script>
</body>
</html>