<?php
/**
 * Evocart — Revamped Landing (evocart_index_revamp_updated.php)
 * - Full updated single-file landing page
 * - Fixes: Add-to-cart now works reliably, product description displays correctly in modal,
 *   safer product encoding using base64(json), safer DOM building, cart-badge updates client-side.
 */

session_start();
require_once __DIR__ . '/includes/config.php';

// User/session
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? null;
$user_name = $user_name ? htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') : null;

// Cart count (stored in session as associative product_id => qty)
$cart_item_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_item_count = array_sum($_SESSION['cart']);
}

// Fetch products (safe fallback)
$products = [];
$best_sellers = [];
$exclusive_offers = [];
try {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT id, name, category, price, description, image_url, is_best_seller, is_exclusive_offer FROM products ORDER BY id DESC LIMIT 60");
    $db_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($db_products as $p) {
        // Ensure values are present
        $p['name'] = $p['name'] ?? 'Untitled product';
        $p['description'] = $p['description'] ?? '';
        $p['price'] = isset($p['price']) ? (float)$p['price'] : 0.0;
        $products[] = $p;
        if (!empty($p['is_best_seller'])) $best_sellers[] = $p;
        if (!empty($p['is_exclusive_offer'])) $exclusive_offers[] = $p;
    }
} catch (\PDOException $e) {
    error_log('DB Error fetching products: ' . $e->getMessage());
    // leave arrays empty
}

function get_product_image_src($url) {
    if (!$url) {
        // variety of unsplash placeholders by category
        return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=1200&q=80&auto=format&fit=crop&crop=entropy';
    }
    if (filter_var($url, FILTER_VALIDATE_URL) || strpos($url, '//') === 0) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
    return '/evocart/' . ltrim(htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), '/');
}

// Wholesale handler (server-side minimal mock)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wholesale_action']) && $_POST['wholesale_action'] === 'place_wholesale') {
    if (!$is_logged_in) {
        http_response_code(401);
        echo json_encode(['error' => 'login_required']);
        exit;
    }
    $qty = intval($_POST['wholesale_quantity'] ?? 0);
    $allowed = [12,24,36,48];
    if (!in_array($qty, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_quantity']);
        exit;
    }
    // TODO: persist wholesale order to DB with prepared statements
    echo json_encode(['success' => true, 'message' => "Wholesale order placed for {$qty} pcs"]);
    exit;
}

?><!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Evocart — Curated. Evolved.</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />

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
                    transitionProperty: { 'height': 'height', 'spacing': 'margin, padding' }
                }
            }
        }
    </script>

    <!-- AOS (Animate on Scroll) -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        /* Base */
        body { font-family: 'Poppins', sans-serif; }
        .glass { background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); backdrop-filter: blur(8px); }
        .fancy-shape { clip-path: polygon(0 0, 100% 0, 100% 86%, 0 100%); }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-thumb { background: rgba(6,182,212,0.45); border-radius: 6px; }

        /* Card hover */
        .card-hover { transition: transform .35s cubic-bezier(.2,.9,.2,1), box-shadow .35s, filter .35s; }
        .card-hover:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 22px 50px rgba(2,6,23,0.6); filter: brightness(1.03); }

        .hero-blob { filter: blur(56px) saturate(120%); opacity: .42; }

        /* responsive adjustments for better card sizing */
        @media (min-width: 1280px) { .product-card-img { height: 320px; } }
        @media (max-width: 767px) { .desktop-only { display:none; } }

        /* Patterns: checked, dots, criss-cross */
        .pattern-checked {
            background-image: linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 18px 18px, 18px 18px;
            background-position: 0 0, 9px 9px;
        }
        .pattern-dots {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.06) 1px, transparent 2px);
            background-size: 10px 10px;
        }
        .pattern-cross {
            background-image: linear-gradient(45deg, rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(-45deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 16px 16px;
        }

        /* Decorative gradient text */
        .gradient-text { background: linear-gradient(90deg,#06b6d4,#f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* Micro interactions */
        .btn-animated { transition: transform .18s ease, box-shadow .18s ease; }
        .btn-animated:active { transform: translateY(2px) scale(.99); }
        .pulse-on-add { animation: addPulse .6s ease; }
        @keyframes addPulse { 0%{ transform: scale(1); } 50%{ transform: scale(1.06); } 100%{ transform: scale(1); } }

        /* subtle entrance */
        .fade-up { transform: translateY(12px); opacity: 0; transition: all .7s cubic-bezier(.2,.9,.2,1); }
        .fade-up.in { transform: translateY(0); opacity: 1; }

        /* mobile Evocart visibility */
        .brand-responsive { display:flex; align-items:center; gap:.6rem; }
        .brand-responsive .brand-text { font-weight:700; letter-spacing:.4px; }

        /* small helpers */
        .soft-shadow { box-shadow: 0 6px 22px rgba(2,6,23,0.45); }
    </style>
</head>
<body class="bg-darkbg text-gray-100 antialiased">

<!-- NAVBAR -->
<header class="sticky top-0 z-50 backdrop-blur-sm bg-black/40 border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-4">
                <a href="#" class="flex items-center gap-3 brand-responsive">
                    <div class="w-11 h-11 bg-gradient-to-br from-primary to-accent rounded-full flex items-center justify-center shadow-xl">
                        <i class="fas fa-shopping-bag text-black text-lg"></i>
                    </div>
                    <!-- Brand text ALWAYS visible (responsive sizing) -->
                    <div class="brand-text text-lg sm:text-xl md:text-2xl">
                        <span class="gradient-text">Evocart</span>
                    </div>
                </a>
            </div>

            <nav class="hidden md:flex items-center gap-6 text-sm">
                <a href="#hero" class="text-gray-300 hover:text-primary transition">Home</a>
                <a href="#features" class="text-gray-300 hover:text-primary transition">About</a>
                <a href="#products" class="text-gray-300 hover:text-primary transition">Products</a>
                <a href="#exclusive-offers" class="text-gray-300 hover:text-primary transition">Exclusive</a>
                <a href="#wholesale" class="text-gray-300 hover:text-primary transition">Wholesale</a>
                <a href="order_status.php" class="text-gray-300 hover:text-primary transition">Track your orders</a>

            </nav>

            <div class="flex items-center gap-3">
                <?php if ($is_logged_in): ?>
                    <div class="hidden sm:flex items-center gap-3">
                        <span class="text-sm text-gray-300">Hello, <span class="text-primary font-semibold"><?php echo $user_name; ?></span></span>

                        <a href="logout.php" class="text-red-500 hover:text-red-400 px-3 py-2 rounded-md bg-white/5">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-sm px-3 py-2 rounded-md bg-white/5 hover:bg-primary/10 btn-animated">Login</a>
                <?php endif; ?>

                <a href="carts.php" id="cart-btn" class="relative inline-flex items-center p-2 rounded-md bg-primary/90 hover:scale-105 transition soft-shadow">
                    <i class="fas fa-shopping-cart text-black"></i>
                    <?php if ($cart_item_count > 0): ?>
                        <span id="cart-badge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-darkbg"><?php echo $cart_item_count; ?></span>
                    <?php else: ?>
                        <span id="cart-badge" class="hidden absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-darkbg">0</span>
                    <?php endif; ?>
                </a>

                <button id="burger" aria-label="menu" class="md:hidden p-2 rounded-md bg-white/5 btn-animated">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile panel -->
    <div id="mobile-panel" class="hidden md:hidden bg-card/80 border-t border-white/5">
        <div class="px-4 pt-4 pb-6 space-y-3 pattern-checked">
            <a href="#hero" class="block">Home</a>
            <a href="#features" class="block">About</a>
            <a href="#products" class="block">Products</a>
            <a href="#exclusive-offers" class="block">Exclusive</a>
            <a href="#wholesale" class="block">Wholesale</a>
            <?php if ($is_logged_in): ?>
                <a href="logout.php" class="block text-red-400">Logout</a>
            <?php else: ?>
                <a href="login.php" class="block text-primary">Login / Signup</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">

    <!-- HERO -->
    <section id="hero" class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center py-12 pattern-dots">
        <div class="space-y-6" data-aos="fade-right">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 text-sm text-accent font-semibold">New Arrivals • Limited</span>
            <?php if ($is_logged_in): ?>
                <p class="text-accent text-lg font-semibold animate-pulse">👋 Welcome back, <?= $user_name ?>!</p>
            <?php else: ?>
                <p class="text-accent text-lg font-semibold">👋 Welcome to Evocart</p>
            <?php endif; ?>
            <h2 class="text-4xl sm:text-5xl font-extrabold leading-tight">Curated picks for the modern you — <span class="text-primary">Evolved shopping</span></h2>
            <p class="text-gray-300 max-w-xl">Evocart blends trend-forward electronics, premium fashion, and beauty essentials — handpicked and delivered with care. Shop with confidence, save with wholesale, and enjoy curated collections updated weekly.</p>

            <div class="flex gap-4">
                <a href="#products" class="inline-flex items-center gap-2 bg-primary text-black font-semibold px-5 py-3 rounded-full shadow-lg hover:scale-[1.02] transition btn-animated">Shop Now <i class="fas fa-arrow-right text-sm"></i></a>
                <a href="#features" class="inline-flex items-center gap-2 border border-white/10 px-5 py-3 rounded-full hover:border-primary transition">Learn More</a>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mt-6 text-center">
                <div>
                    <div class="text-2xl font-bold">10k+</div>
                    <div class="text-xs text-gray-400">Happy Customers</div>
                </div>
                <div>
                    <div class="text-2xl font-bold">200+</div>
                    <div class="text-xs text-gray-400">Products</div>
                </div>
                <div class="hidden sm:block">
                    <div class="text-2xl font-bold">24/7</div>
                    <div class="text-xs text-gray-400">Support</div>
                </div>
                <div class="hidden lg:block">
                    <div class="text-2xl font-bold">Free</div>
                    <div class="text-xs text-gray-400">Shipping over ₹999</div>
                </div>
            </div>
        </div>

        <div class="relative" data-aos="zoom-in">
            <div class="absolute -left-8 -top-8 w-64 h-64 rounded-3xl bg-gradient-to-br from-primary/40 to-accent/30 hero-blob"></div>

            <div class="glass p-6 rounded-2xl shadow-2xl">
                <div class="grid grid-cols-2 sm:grid-cols-2 gap-4 items-center">
                    <?php if (!empty($best_sellers)): ?>
                        <?php foreach (array_slice($best_sellers, 0, 4) as $p): ?>
                            <div class="flex gap-3 items-center">
                                <img src="<?php echo get_product_image_src($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-20 h-20 object-cover rounded-lg shadow-inner">
                                <div>
                                    <div class="text-sm font-semibold"><?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-gray-400">₹<?php echo number_format($p['price'],2); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-gray-400">No featured items yet. Add products via Admin.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

   <!-- ABOUT: Beautiful, defined, patterned, animated -->
    <section id="features" class="py-12 md:py-20">
        <div class="text-center mb-12 pattern-checked p-6 rounded-3xl">
            <h2 class="text-3xl md:text-4xl font-bold gradient-text">About Evocart</h2>
            <p class="text-gray-400 max-w-2xl mx-auto mt-4">Evocart was born from a simple idea — make curated, quality products accessible to everyone. We focus on thoughtful sourcing, sustainable partners and delightful experiences so every purchase feels premium.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="p-6 bg-card rounded-2xl shadow-lg card-hover pattern-dots" data-aos="fade-up">
                <div class="w-14 h-14 bg-primary/20 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-bullseye text-primary text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-primary">Our Mission</h3>
                <p class="text-gray-300 mt-2">Bring high-quality products to curious shoppers with a seamless digital experience.</p>
            </div>
            <div class="p-6 bg-card rounded-2xl shadow-lg card-hover pattern-cross" data-aos="fade-up" data-aos-delay="80">
                <div class="w-14 h-14 bg-accent/20 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-leaf text-accent text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-accent">Sustainable Sourcing</h3>
                <p class="text-gray-300 mt-2">We partner with ethical suppliers and emphasize durability and value.</p>
            </div>
            <div class="p-6 bg-card rounded-2xl shadow-lg card-hover pattern-checked" data-aos="fade-up" data-aos-delay="160">
                <div class="w-14 h-14 bg-primary/20 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-headset text-primary text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-primary">Premium Support</h3>
                <p class="text-gray-300 mt-2">24/7 support and hassle-free returns — because shopping should be joyful.</p>
            </div>
            <div class="p-6 bg-card rounded-2xl shadow-lg card-hover pattern-dots" data-aos="fade-up">
                <div class="w-14 h-14 bg-accent/20 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-shipping-fast text-accent text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-accent">Fast Delivery</h3>
                <p class="text-gray-300 mt-2">Get your orders delivered quickly with our optimized logistics network.</p>
            </div>
            <div class="p-6 bg-card rounded-2xl shadow-lg card-hover pattern-cross" data-aos="fade-up" data-aos-delay="80">
                <div class="w-14 h-14 bg-primary/20 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-primary text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-primary">Secure Payments</h3>
                <p class="text-gray-300 mt-2">Your transactions are protected with industry-leading security measures.</p>
            </div>
            <div class="p-6 bg-card rounded-2xl shadow-lg card-hover pattern-checked" data-aos="fade-up" data-aos-delay="160">
                <div class="w-14 h-14 bg-accent/20 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-award text-accent text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-accent">Quality Guarantee</h3>
                <p class="text-gray-300 mt-2">Every product is carefully vetted to meet our high quality standards.</p>
            </div>
        </div>
    </section>

    <!-- EXCLUSIVE OFFERS -->
    <?php if (!empty($exclusive_offers)): ?>
    <section id="exclusive-offers" class="py-12 pattern-cross p-4 rounded-2xl">
        <h3 class="text-2xl font-bold mb-4">Exclusive Offers</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($exclusive_offers as $product): ?>
                <article class="relative bg-gradient-to-b from-black/30 to-black/10 p-4 rounded-2xl shadow-xl hover:scale-[1.02] transition card-hover" data-aos="flip-left">
                    <div class="relative">
                        <span class="absolute top-3 left-3 bg-accent text-black px-3 py-1 rounded-full text-xs font-bold">HOT</span>
                        <img src="<?php echo get_product_image_src($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-44 object-cover rounded-lg mb-3">
                        <h4 class="font-semibold"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <p class="text-sm text-gray-400 h-14 overflow-hidden"><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="flex items-center justify-between mt-3">
                            <div class="text-lg font-bold">₹<?php echo number_format($product['price'],2); ?></div>
                            <!-- Use base64 encoded JSON to avoid attribute quoting issues -->
                            <button class="add-ajax inline-flex items-center gap-2 px-3 py-2 rounded-md bg-primary text-black font-semibold" data-product="<?php echo base64_encode(json_encode($product, JSON_UNESCAPED_SLASHES)); ?>">
                                <i class="fas fa-cart-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- PRODUCTS GRID -->
    <section id="products" class="py-12 pattern-dots">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold">Shop All</h3>
            <a href="all.php" class="text-sm text-primary">View Catalog <i class="fas fa-arrow-right ml-2"></i></a>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center text-gray-400 py-12">No products available. Add items in Admin panel.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $p): ?>
                    <div class="bg-card p-4 rounded-2xl shadow card-hover transform transition" data-aos="fade-up">
                        <?php if (!empty($p['is_exclusive_offer'])): ?>
                            <span class="absolute ml-4 mt-3 bg-accent text-black px-2 py-1 rounded-full text-xs font-bold">EXCL</span>
                        <?php endif; ?>
                        <div class="overflow-hidden rounded-lg mb-3">
                            <img src="<?php echo get_product_image_src($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full object-cover product-card-img">
                        </div>
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-semibold"><?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="text-lg font-bold">₹<?php echo number_format($p['price'],2); ?></div>
                        </div>
                        <p class="text-gray-400 mt-2 text-sm h-14 overflow-hidden"><?php echo htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="mt-4 flex gap-3">
                            <!-- Use base64 encoded JSON to avoid attribute quoting issues -->
                            <button class="add-ajax flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md bg-primary text-black font-semibold btn-animated" data-product="<?php echo base64_encode(json_encode($p, JSON_UNESCAPED_SLASHES)); ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <button class="buy-now-btn flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md border border-white/10 btn-animated" data-product="<?php echo base64_encode(json_encode($p, JSON_UNESCAPED_SLASHES)); ?>">
                                <i class="fas fa-bolt"></i> Buy Now
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- WHOLESALE SECTION (Conditional Product: order multiples of 12) -->
    <section id="wholesale" class="py-12">
        <div class="max-w-4xl mx-auto p-6 bg-gradient-to-br from-black/20 to-black/10 rounded-3xl shadow-xl pattern-checked" data-aos="zoom-in">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <div>
                    <h3 class="text-2xl font-bold">Wholesale: "Aurora Desk Lamp"</h3>
                    <p class="text-gray-300 mt-2">Premium smart lamp — unique anodized finish, wireless charging base, and ambient color modes. Wholesale only: order in multiples of 12 (12–48).</p>

                    <ul class="mt-4 text-sm text-gray-400 space-y-2">
                        <li><i class="fas fa-check-circle text-primary mr-2"></i>High-margin accessory</li>
                        <li><i class="fas fa-check-circle text-primary mr-2"></i>Attractive retail packaging</li>
                        <li><i class="fas fa-check-circle text-primary mr-2"></i>MOQ-friendly (12 pcs)</li>
                    </ul>

                    <div class="mt-6">
                        <form id="wholesale-form" class="flex gap-3 items-center" onsubmit="return handleWholesale(event)">
                            <label for="wholesale-qty" class="text-sm">Quantity</label>
                            <select id="wholesale-qty" name="quantity" class="p-3 bg-darkbg rounded-md" required>
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="36">36</option>
                                <option value="48">48</option>
                            </select>
                            <button id="wholesale-btn" type="submit" class="ml-auto px-4 py-3 bg-accent text-black rounded-full font-bold btn-animated">Order Wholesale</button>
                        </form>
                        <p id="wholesale-feedback" class="text-sm mt-3 text-gray-300"></p>
                    </div>
                </div>

                <div class="p-4 bg-card rounded-xl pattern-dots">
                    <img src="https://images.unsplash.com/photo-1519710164239-da123dc03ef4?w=1200&q=80&auto=format&fit=crop&crop=entropy" alt="Aurora Desk Lamp" class="w-full h-64 object-cover rounded-lg shadow-inner">
                    <div class="mt-3 flex items-center justify-between">
                        <div>
                            <div class="font-semibold">Aurora Desk Lamp</div>
                            <div class="text-sm text-gray-400">Wholesale price: ₹1,499 / pc</div>
                        </div>
                        <div class="text-lg font-bold">₹17,988 — 12 pcs</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-12 mt-12 border-t border-white/5 pattern-cross p-4 rounded-lg">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 px-4">
            <div>
                <h4 class="font-bold">Evocart</h4>
                <p class="text-gray-400 text-sm mt-2">Curated • Evolved. © <?php echo date('Y'); ?></p>
            </div>
            <div>
                <h5 class="font-semibold">Company</h5>
                <ul class="text-gray-400 text-sm mt-2 space-y-1">
                    <li><a href="#">About</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press</a></li>
                </ul>
            </div>
            <div>
                <h5 class="font-semibold">Help</h5>
                <ul class="text-gray-400 text-sm mt-2 space-y-1">
                    <li><a href="order_status.php">Order Status</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="dashboard.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </footer>
</main>

<!-- Modal: Generic Modal (now used for Quantity Selection and Prompts) -->
<div id="modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
    <div class="bg-card p-6 rounded-2xl max-w-md w-full relative">
        <button id="modal-close" class="absolute top-3 right-3 p-2 rounded-md bg-white/5 hover:bg-white/10">
            <i class="fas fa-times"></i>
        </button>
        <div id="modal-body" class="min-h-[80px]"></div>
    </div>
</div>

<!-- Floating quick-cart preview -->
<a href="carts.php" id="quick-cart" class="fixed right-6 bottom-6 z-40 inline-flex items-center gap-3 bg-primary text-black px-4 py-3 rounded-full shadow-lg btn-animated">
    <i class="fas fa-shopping-cart"></i>
    <span class="hidden sm:inline">Cart</span>
    <?php if ($cart_item_count > 0): ?>
        <span id="quick-cart-badge" class="ml-2 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-darkbg"><?php echo $cart_item_count; ?></span>
    <?php else: ?>
        <span id="quick-cart-badge" class="hidden ml-2 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-darkbg">0</span>
    <?php endif; ?>
</a>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({ duration: 700, once: true, easing: 'ease-out-cubic' });

    // Mobile panel toggle
    const burger = document.getElementById('burger');
    const mobilePanel = document.getElementById('mobile-panel');
    burger?.addEventListener('click', () => mobilePanel.classList.toggle('hidden'));

    // Modal utilities
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        // clear body for next render
        modalBody.innerHTML = '';
    }
    modalClose?.addEventListener('click', closeModal);

    // accessibility: close modal on ESC
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    // Small util: animate element in-view (add fade-up class)
    function revealOnLoad() {
        document.querySelectorAll('.fade-up').forEach(el => setTimeout(()=> el.classList.add('in'), 80));
    }
    window.addEventListener('load', revealOnLoad);

    // Helper: safely decode base64 product and parse JSON
    function decodeProductData(encoded) {
        try {
            // atob might throw on invalid base64
            const json = atob(encoded);
            return JSON.parse(json);
        } catch (err) {
            console.error('Failed to parse product data', err);
            return null;
        }
    }

    // Helper to increment cart badge(s)
    function incrementCartBadges(by = 1) {
        // helper to modify a badge element text or create/show it
        function updateBadge(id, add) {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.classList.contains('hidden')) {
                // reveal and set value
                el.classList.remove('hidden');
                el.textContent = String(add);
                return;
            }
            const current = parseInt(el.textContent || '0', 10) || 0;
            el.textContent = String(current + add);
        }
        updateBadge('cart-badge', by);
        updateBadge('quick-cart-badge', by);
    }

    // ====================================================================
    // NEW CART LOGIC: Quantity Modal and AJAX Submission (fixed)
    // ====================================================================

    /**
     * Handles the AJAX call to add a product with a specific quantity to the cart.
     */
    async function addToCartAjax(productId, quantity) {
        try {
            const form = new FormData();
            form.append('action', 'add');
            form.append('product_id', productId);
            form.append('quantity', quantity);

            const res = await fetch('carts.php', { method: 'POST', body: form });

            if (res.ok) {
                // Micro interaction: pulse cart
                const quick = document.getElementById('quick-cart');
                quick.classList.add('pulse-on-add');
                setTimeout(() => quick.classList.remove('pulse-on-add'), 650);

                // Update client-side badges optimistically
                incrementCartBadges(quantity);

                // Show success modal briefly
                modalBody.innerHTML = '<div class="text-center font-semibold text-lg py-4">Item added to cart successfully! ✅</div>';
                openModal();
                setTimeout(() => closeModal(), 1400);
            } else if (res.status === 401) {
                // force login
                modalBody.innerHTML = '<div class="text-center font-semibold text-red-400 py-4">Please login to continue.</div>';
                openModal();
            } else {
                // show server-provided message if JSON
                let msg = 'Failed to add to cart. Try again later.';
                try {
                    const j = await res.json();
                    if (j && j.error) msg = j.error;
                } catch (e) {}
                modalBody.innerHTML = `<div class="text-center font-semibold text-red-400 py-4">${msg}</div>`;
                openModal();
            }
        } catch (err) {
            console.error(err);
            modalBody.innerHTML = '<div class="text-center font-semibold text-red-400 py-4">Network error.</div>';
            openModal();
        }
    }

    /**
     * Builds and opens the quantity modal using safe DOM methods.
     * @param {Object} product - The product data object.
     */
    function openQuantityModal(product) {
        const isLogged = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        if (!isLogged) {
            modalBody.innerHTML = `<div class="text-center"><h3 class="font-bold text-xl">Login required</h3><p class="mt-2 text-sm">Please <a href="login.php" class="text-primary hover:underline">login</a> to add items to cart.</p></div>`;
            openModal();
            return;
        }

        // Clear body and build elements safely
        modalBody.innerHTML = '';
        const title = document.createElement('h3');
        title.className = 'font-bold text-xl mb-1 text-white';
        title.textContent = product.name || 'Product';

        const price = document.createElement('p');
        price.className = 'text-lg text-primary font-semibold mb-3';
        const priceNumber = parseFloat(product.price || 0);
        price.textContent = priceNumber.toLocaleString('en-IN', { style: 'currency', currency: 'INR' });

        const desc = document.createElement('p');
        desc.className = 'text-sm text-gray-400 mb-4';
        desc.textContent = (product.description || '').substring(0, 300);

        const form = document.createElement('form');
        form.id = 'quantity-form';
        form.className = 'space-y-4';

        const label = document.createElement('label');
        label.setAttribute('for', 'modal-qty');
        label.className = 'block text-sm font-medium text-gray-300';
        label.textContent = 'Select Quantity';

        const input = document.createElement('input');
        input.type = 'number';
        input.id = 'modal-qty';
        input.value = '1';
        input.min = '1';
        input.max = '99';
        input.className = 'w-full p-3 bg-darkbg border border-white/10 rounded-lg text-lg text-center focus:ring-primary focus:border-primary transition';
        input.setAttribute('aria-label', 'Quantity');

        const controls = document.createElement('div');
        controls.className = 'flex justify-end pt-2';

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'px-4 py-2 bg-white/10 text-white rounded-lg hover:bg-white/20 transition mr-3';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });

        const addBtn = document.createElement('button');
        addBtn.type = 'submit';
        addBtn.id = 'modal-add-btn';
        addBtn.className = 'px-6 py-3 bg-accent text-black font-bold rounded-lg hover:bg-yellow-500 transition btn-animated';
        addBtn.innerHTML = '<i class="fas fa-cart-plus mr-2"></i> Add to Cart';

        controls.appendChild(cancelBtn);
        controls.appendChild(addBtn);

        form.appendChild(label);
        form.appendChild(input);
        form.appendChild(controls);

        modalBody.appendChild(title);
        modalBody.appendChild(price);
        modalBody.appendChild(desc);
        modalBody.appendChild(form);

        openModal();

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            let quantity = parseInt(input.value, 10);
            if (isNaN(quantity) || quantity < 1) {
                input.value = 1;
                quantity = 1;
            }

            // Disable button while processing
            addBtn.disabled = true;
            addBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Adding...';

            // Close modal immediately for faster flow (UI already shows spinner)
            closeModal();

            await addToCartAjax(product.id, quantity);
        });
    }

    // Event delegation for add-ajax buttons (works for present and future elements)
    document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('.add-ajax');
        if (!btn) return;
        e.preventDefault();

        const encoded = btn.getAttribute('data-product');
        const product = decodeProductData(encoded);
        if (!product) {
            modalBody.innerHTML = '<div class="text-center font-semibold text-red-400 py-4">Product data invalid.</div>';
            openModal();
            return;
        }
        openQuantityModal(product);
    });

    // Buy now: either redirect to checkout or prompt login
    document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('.buy-now-btn');
        if (!btn) return;
        e.preventDefault();

        const encoded = btn.getAttribute('data-product');
        const prod = decodeProductData(encoded);
        if (!prod) {
            modalBody.innerHTML = '<div class="text-center font-semibold text-red-400 py-4">Product data invalid.</div>';
            openModal();
            return;
        }

        const isLogged = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        if (!isLogged) {
            modalBody.innerHTML = `<div class="text-center"><h3 class="font-bold">Login to Checkout</h3><p class="mt-2">Please <a href="login.php?redirect=checkout.php" class="text-primary hover:underline">login</a> to proceed to checkout.</p></div>`;
            openModal();
            return;
        }

        // Logic to clear cart and add only the 'buy now' item — handled by carts.php buy_now_redirect action
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'carts.php';
        form.style.display = 'none';
        form.innerHTML = `<input name="action" value="buy_now_redirect" /><input name="product_id" value="${encodeURIComponent(prod.id)}" /><input name="quantity" value="1" />`;
        document.body.appendChild(form);
        form.submit();
    });

    // ====================================================================
    // Wholesale handler (client-side) — posts to this same page endpoint which returns JSON
    // ====================================================================
    async function handleWholesale(e) {
        e.preventDefault();
        const select = document.getElementById('wholesale-qty');
        const qty = parseInt(select.value, 10);
        const allowed = [12,24,36,48];
        const feedback = document.getElementById('wholesale-feedback');

        if (!allowed.includes(qty)) {
            feedback.textContent = 'Please select a valid quantity (12,24,36,48).';
            feedback.classList.remove('text-green-400');
            feedback.classList.add('text-red-400');
            return false;
        }

        const logged = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        if (!logged) {
            window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            return false;
        }

        try {
            const form = new FormData();
            form.append('wholesale_action','place_wholesale');
            form.append('wholesale_quantity', qty);
            const res = await fetch(window.location.href, { method:'POST', body: form });
            const json = await res.json();
            if (res.ok && json.success) {
                feedback.classList.remove('text-red-400');
                feedback.classList.add('text-green-400');
                feedback.textContent = json.message || 'Wholesale order placed successfully.';
            } else {
                feedback.classList.remove('text-green-400');
                feedback.classList.add('text-red-400');
                if (json.error === 'login_required') {
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                } else if (json.error === 'invalid_quantity') {
                    feedback.textContent = 'Invalid quantity — choose 12,24,36 or 48.';
                } else {
                    feedback.textContent = 'Error placing wholesale order — try again later.';
                }
            }
        } catch (err) {
            feedback.classList.remove('text-green-400');
            feedback.classList.add('text-red-400');
            feedback.textContent = 'Network error — try again.';
        }

        return false;
    }

    // Expose form submit handler for inline form attribute
    window.handleWholesale = handleWholesale;

    // small helper to reinitialize AOS when DOM changes (if needed)
    function refreshAOS() {
        if (window.AOS) window.AOS.refresh();
    }

    // optional: refresh AOS after load (safe)
    setTimeout(refreshAOS, 500);

</script>
</body>
</html>
