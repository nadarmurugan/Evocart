<?php
/**
 * Evocart — Checkout Page (Final Stable Version - DB Compatible)
 * - Saves order to DB (Pending Payment)
 * - Updates to "Paid" after mock payment
 * - Fully matches your DB structure
 */

session_start();
require_once __DIR__ . '/includes/config.php';

// --- CONFIG ---
const TAX_RATE_PERCENT = 0.18;
const SHIPPING_COST_RUPEES = 500.00;

// --- CHECK LOGIN & CART ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['user_email'] ?? 'user@evocart.com';

if (empty($_SESSION['cart'])) {
    header("Location: carts.php");
    exit();
}

// --- HELPER FUNCTION ---
function format_rupees($amount) {
    return '₹' . number_format((float)$amount, 2);
}

// --- FETCH PRODUCTS FROM DB ---
try {
    $pdo = connectDB();
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $map = array_column($products, null, 'id');
} catch (PDOException $e) {
    error_log("DB Fetch Error: " . $e->getMessage());
    $_SESSION['cart_message'] = "Failed to fetch product data.";
    header("Location: carts.php");
    exit();
}

// --- CALCULATE TOTALS ---
$order_items = [];
$subtotal = 0;
foreach ($_SESSION['cart'] as $pid => $qty) {
    if (isset($map[$pid])) {
        $p = $map[$pid];
        $price = (float)$p['price'];
        $line_total = $price * $qty;
        $order_items[] = [
            'id' => $pid,
            'name' => $p['name'],
            'price' => $price,
            'qty' => $qty,
            'line_total' => $line_total
        ];
        $subtotal += $line_total;
    }
}
$shipping = SHIPPING_COST_RUPEES;
$tax = round($subtotal * TAX_RATE_PERCENT, 2);
$grand = $subtotal + $shipping + $tax;

// --- SAVE ORDER TO DB ---
function save_order($user_id, $items, $subtotal, $shipping, $tax, $grand) {
    global $pdo;

    // Prevent duplicate order creation
    if (isset($_SESSION['current_order_id'])) {
        return $_SESSION['current_order_id'];
    }

    try {
        $pdo->beginTransaction();

        // Insert order
        $sql = "INSERT INTO orders (user_id, subtotal, shipping, tax, grand_total, status) 
                VALUES (?, ?, ?, ?, ?, 'Pending Payment')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $subtotal, $shipping, $tax, $grand]);
        $order_id = $pdo->lastInsertId();

        // Insert order items
        $sql_items = "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
                      VALUES (?, ?, ?, ?, ?, ?)";
        $insert = $pdo->prepare($sql_items);
        foreach ($items as $it) {
            $insert->execute([$order_id, $it['id'], $it['name'], $it['price'], $it['qty'], $it['line_total']]);
        }

        $pdo->commit();
        $_SESSION['current_order_id'] = $order_id;
        return $order_id;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Order Save Error: " . $e->getMessage());
        throw new Exception("Could not save order.");
    }
}

// --- PAYMENT STATUS UPDATE (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['payment_action'] ?? '') === 'complete') {
    header('Content-Type: application/json');
    $order_id = (int)($_POST['order_id'] ?? 0);
    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
        exit;
    }

    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("UPDATE orders SET status = 'Paid' WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
        unset($_SESSION['cart'], $_SESSION['current_order_id']);
        echo json_encode(['success' => true, 'redirect' => 'order_status.php?order_id=' . $order_id]);
    } catch (PDOException $e) {
        error_log("Payment Update Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database update failed.']);
    }
    exit;
}

// --- CREATE ORDER ON FIRST LOAD ---
try {
    $order_id = save_order($user_id, $order_items, $subtotal, $shipping, $tax, $grand);
} catch (Exception $e) {
    $_SESSION['cart_message'] = "Could not create order.";
    header("Location: carts.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - Evocart</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
            fontFamily: { sans: ['Poppins', 'sans-serif'] }
        }
    }
}
</script>
<style>
body{font-family:'Poppins',sans-serif;background-color:#0b1220}
.modal{position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:50}
.modal-content{background:#0f1724;padding:2rem;border-radius:1rem;border:1px solid rgba(255,255,255,0.1);text-align:center;animation:fade .3s ease}
@keyframes fade{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
</style>
</head>
<body class="text-gray-100">

<header class="bg-black/40 border-b border-white/10 backdrop-blur-sm sticky top-0 z-40">
<div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
<a href="index.php" class="text-2xl font-bold text-primary flex items-center gap-2">
<i class="fas fa-shopping-bag text-accent"></i> EVOCART
</a>
<a href="carts.php" class="text-sm bg-white/10 px-4 py-2 rounded-lg hover:bg-primary/10">
<i class="fas fa-arrow-left"></i> Back to Cart
</a>
</div>
</header>

<main class="max-w-6xl mx-auto px-6 py-10">
<h1 class="text-3xl font-bold text-primary mb-6">Checkout</h1>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- LEFT -->
    <div class="lg:w-2/3 space-y-8">
        <div class="bg-card p-6 rounded-xl border border-white/10">
            <h2 class="text-2xl font-bold text-accent mb-3"><i class="fas fa-map-marker-alt mr-2"></i> Shipping Details</h2>
            <p><?= htmlspecialchars($user_name) ?></p>
            <p class="text-gray-400 text-sm"><?= htmlspecialchars($user_email) ?></p>
            <p class="text-gray-500 text-xs mt-2">123 Nebula Street, Orion City</p>
        </div>

        <div class="bg-card p-6 rounded-xl border border-white/10">
            <h2 class="text-2xl font-bold text-accent mb-4"><i class="fas fa-credit-card mr-2"></i> Payment</h2>
            <div class="flex justify-between bg-darkbg p-3 rounded-md border border-white/10 mb-4">
                <span>Total Payable:</span>
                <span class="text-accent font-bold"><?= format_rupees($grand) ?></span>
            </div>
            <button id="checkout-button" class="w-full bg-primary text-black font-bold py-4 rounded-lg text-lg flex justify-center items-center gap-2 hover:bg-cyan-500">
                <i class="fas fa-unlock-alt"></i> Pay Now
            </button>
            <div id="payment-status" class="hidden mt-4 text-center p-3 rounded-lg border"></div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="lg:w-1/3 bg-card p-6 rounded-xl border border-white/10">
        <h2 class="text-2xl font-bold mb-4 border-b border-white/10 pb-2">Order Summary</h2>
        <?php foreach ($order_items as $it): ?>
        <div class="flex justify-between text-gray-300 text-sm mb-2">
            <span><?= $it['qty'] ?> × <?= htmlspecialchars($it['name']) ?></span>
            <span><?= format_rupees($it['line_total']) ?></span>
        </div>
        <?php endforeach; ?>
        <div class="border-t border-white/10 mt-3 pt-3 text-sm text-gray-300 space-y-1">
            <div class="flex justify-between"><span>Subtotal</span><span><?= format_rupees($subtotal) ?></span></div>
            <div class="flex justify-between"><span>Shipping</span><span><?= format_rupees($shipping) ?></span></div>
            <div class="flex justify-between"><span>Tax</span><span><?= format_rupees($tax) ?></span></div>
            <div class="flex justify-between font-bold text-lg border-t border-primary/50 pt-2">
                <span>Total</span><span class="text-accent"><?= format_rupees($grand) ?></span>
            </div>
        </div>
    </div>
</div>
</main>

<!-- MODAL -->
<div id="confirmModal" class="hidden modal">
    <div class="modal-content">
        <i class="fas fa-check-circle text-green-400 text-5xl mb-3"></i>
        <h3 class="text-xl font-bold text-white mb-2">Order Saved Successfully!</h3>
        <p class="text-gray-300 mb-4">Your order has been stored. Click below to simulate payment.</p>
        <button id="proceedPaymentBtn" class="bg-primary text-black font-bold px-6 py-3 rounded-lg hover:bg-cyan-500">
            <i class="fas fa-credit-card mr-2"></i> Proceed to Payment
        </button>
    </div>
</div>

<script>
const orderId='<?= $order_id ?>';
const btn=document.getElementById('checkout-button');
const modal=document.getElementById('confirmModal');
const payBtn=document.getElementById('proceedPaymentBtn');
const statusBox=document.getElementById('payment-status');

btn.addEventListener('click',()=>{modal.classList.remove('hidden');});

payBtn.addEventListener('click',async()=>{
    payBtn.disabled=true;
    payBtn.innerHTML='<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
    statusBox.classList.remove('hidden');
    statusBox.className='mt-4 text-center p-3 rounded-lg border bg-blue-900 text-blue-100';
    statusBox.textContent='Processing payment...';
    await new Promise(r=>setTimeout(r,2000));
    const fd=new FormData();
    fd.append('payment_action','complete');
    fd.append('order_id',orderId);
    const res=await fetch('checkout.php',{method:'POST',body:fd});
    const data=await res.json();
    if(data.success){
        statusBox.className='mt-4 text-center p-3 rounded-lg border bg-green-800 text-green-100';
        statusBox.textContent='Payment Successful! Redirecting...';
        setTimeout(()=>window.location.href=data.redirect,1000);
    }else{
        statusBox.className='mt-4 text-center p-3 rounded-lg border bg-red-800 text-red-100';
        statusBox.textContent=data.message||'Payment failed.';
        payBtn.disabled=false;
        payBtn.innerHTML='<i class="fas fa-redo mr-2"></i> Retry Payment';
    }
});
</script>
</body>
</html>
