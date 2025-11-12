<?php
/**
 * Evocart — My Orders Page (User Side)
 * Fully dynamic progress bar + clean visuals
 */

session_start();
require_once __DIR__ . '/includes/config.php';

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=order_status.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'User');
$is_logged_in = true;

// Connect DB & fetch user orders
try {
    $pdo = connectDB();
    $stmt = $pdo->prepare("
        SELECT id, order_date, status, subtotal, shipping, tax, grand_total
        FROM orders
        WHERE user_id = ?
        ORDER BY order_date DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
    error_log("Order Fetch Error: " . $e->getMessage());
}

function format_price($amount) {
    return '₹' . number_format((float)$amount, 2);
}

// Progress mapping
function get_progress_width($status) {
    return match ($status) {
        'Pending Payment' => '10%',
        'Paid' => '25%',
        'Processing' => '50%',
        'Departure' => '70%',
        'Arrival' => '85%',
        'Delivered' => '100%',
        'Cancelled' => '0%',
        default => '10%',
    };
}

function get_status_color($status) {
    return match ($status) {
        'Pending Payment' => 'bg-gray-500 text-white',
        'Paid' => 'bg-blue-500 text-white',
        'Processing' => 'bg-yellow-400 text-black',
        'Departure' => 'bg-orange-500 text-black',
        'Arrival' => 'bg-indigo-600 text-white',
        'Delivered' => 'bg-green-600 text-white',
        'Cancelled' => 'bg-red-600 text-white',
        default => 'bg-gray-600 text-white',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders — Evocart</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
            fontFamily: { poppins: ['Poppins', 'sans-serif'] }
        }
    }
};
</script>
</head>

<body class="bg-darkbg text-gray-100 font-poppins">

<!-- NAVBAR -->
<!-- 🌐 HEADER NAVBAR -->
<header class="sticky top-0 z-50 backdrop-blur bg-black/40 border-b border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <!-- Left: Brand Logo -->
        <a href="index.php" class="flex items-center gap-3 group">
            <div class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                <i class="fas fa-shopping-bag text-black text-lg"></i>
            </div>
            <span class="text-2xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">
                Evocart
            </span>
        </a>

        <!-- Middle: Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-6 text-sm">
            <a href="index.php" class="text-gray-300 hover:text-primary transition">Home</a>
            <a href="index.php#products" class="text-gray-300 hover:text-primary transition">Products</a>
            <a href="index.php#exclusive-offers" class="text-gray-300 hover:text-primary transition">Exclusive</a>
            <a href="index.php#wholesale" class="text-gray-300 hover:text-primary transition">Wholesale</a>
            <a href="order_status.php" class="text-primary font-semibold transition">Track Orders</a>
        </nav>

        <!-- Right: User + Cart -->
        <div class="flex items-center gap-3">
            <!-- Cart Button -->
            <a href="carts.php" class="relative inline-flex items-center p-2 rounded-md bg-primary/90 hover:scale-105 transition">
                <i class="fas fa-shopping-cart text-black"></i>
            </a>

            <!-- User Info -->
            <?php if ($is_logged_in): ?>
                <div class="hidden sm:flex items-center gap-3">
                    <span class="text-sm text-gray-300">
                        Hi, <span class="text-primary font-semibold"><?= $user_name ?></span>
                    </span>
                    <a href="logout.php" class="text-red-500 hover:text-red-400 px-3 py-2 rounded-md bg-white/5">
                        Logout
                    </a>
                </div>
            <?php else: ?>
                <a href="login.php" class="text-sm px-3 py-2 rounded-md bg-white/5 hover:bg-primary/10 transition">
                    Login
                </a>
            <?php endif; ?>

            <!-- Mobile Menu Button -->
            <button id="menu-toggle" class="md:hidden p-2 bg-white/10 rounded-md hover:bg-white/20 transition">
                <i class="fas fa-bars text-gray-200"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div id="mobile-nav" class="hidden md:hidden bg-card border-t border-white/10">
        <div class="flex flex-col p-4 space-y-3 text-gray-300">
            <a href="index.php" class="hover:text-primary transition">Home</a>
            <a href="index.php#products" class="hover:text-primary transition">Products</a>
            <a href="index.php#exclusive-offers" class="hover:text-primary transition">Exclusive</a>
            <a href="index.php#wholesale" class="hover:text-primary transition">Wholesale</a>
            <a href="order_status.php" class="text-primary font-semibold transition">Track Orders</a>

            <?php if ($is_logged_in): ?>
                <a href="logout.php" class="text-red-400 hover:text-red-300 transition">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-primary hover:text-accent transition">Login / Signup</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- MAIN -->
<main class="max-w-6xl mx-auto px-6 py-12">
    <h1 class="text-3xl sm:text-4xl font-bold mb-8 text-primary flex items-center gap-3">
        <i class="fas fa-box-open"></i> My Orders
    </h1>

    <?php if (empty($orders)): ?>
        <div class="text-center py-12 bg-card rounded-xl border border-white/10">
            <i class="fas fa-inbox text-5xl text-gray-600 mb-4"></i>
            <p class="text-gray-400">You haven’t placed any orders yet.</p>
            <a href="index.php#products" class="inline-block mt-4 px-6 py-3 bg-primary text-black font-semibold rounded-full hover:bg-primary/80 transition">Shop Now</a>
        </div>
    <?php else: ?>
        <div class="space-y-10">
            <?php foreach ($orders as $order): ?>
                <?php
                // Fetch order items
                $stmtItems = $pdo->prepare("
                    SELECT oi.product_name, oi.unit_price, oi.quantity, oi.line_total, p.image_url
                    FROM order_items oi
                    LEFT JOIN products p ON p.id = oi.product_id
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$order['id']]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                $status = $order['status'];
                $statusColor = get_status_color($status);
                $progressWidth = get_progress_width($status);
                ?>
                <div class="bg-card p-6 rounded-xl border border-white/10 shadow-lg hover:border-primary/40 transition-all duration-300">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
                        <h2 class="text-lg font-bold text-primary flex items-center gap-2">
                            <i class="fas fa-truck"></i> Order #<?= $order['id'] ?>
                        </h2>
                        <span class="text-sm px-3 py-1 rounded-full <?= $statusColor ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </div>

                    <!-- Items -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($items as $it): ?>
                            <div class="flex items-center gap-4 bg-black/20 rounded-lg p-3 border border-white/5">
                                <img src="<?= htmlspecialchars($it['image_url'] ?? 'uploads/placeholder.jpg') ?>" alt="<?= htmlspecialchars($it['product_name']) ?>" class="w-16 h-16 object-cover rounded-lg">
                                <div>
                                    <p class="font-semibold"><?= htmlspecialchars($it['product_name']) ?></p>
                                    <p class="text-gray-400 text-sm">Qty: <?= $it['quantity'] ?></p>
                                    <p class="text-primary font-semibold"><?= format_price($it['unit_price']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Order Info -->
                    <div class="mt-5 flex justify-between items-center flex-wrap gap-2 border-t border-white/10 pt-3">
                        <p class="text-sm text-gray-400">
                            Ordered on <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
                        </p>
                        <p class="text-lg font-bold text-accent">
                            Total: <?= format_price($order['grand_total']) ?>
                        </p>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="flex justify-between text-xs text-gray-400 font-semibold mb-2">
                            <span>Pending</span>
                            <span>Paid</span>
                            <span>Processing</span>
                            <span>Departure</span>
                            <span>Arrival</span>
                            <span>Delivered</span>
                        </div>
                        <div class="relative h-2 bg-gray-700 rounded-full overflow-hidden">
                            <div class="absolute top-0 left-0 h-2 bg-gradient-to-r from-primary to-accent rounded-full transition-all duration-700 ease-in-out"
                                 style="width: <?= $progressWidth ?>;"></div>
                        </div>
                        <p class="mt-2 text-xs text-gray-400 text-right italic">
                            <?= htmlspecialchars($status) ?> — <?= $progressWidth ?> complete
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- FOOTER -->
<footer class="mt-12 border-t border-white/10 py-8 text-center text-gray-400">
    <p>&copy; <?= date('Y') ?> Evocart. All rights reserved.</p>
</footer>

<style>
.gradient-text {
    background: linear-gradient(90deg,#06b6d4,#f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
</body>
</html>
