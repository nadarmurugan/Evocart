<?php
/**
 * Evocart Admin Dashboard — Final Enhanced Version
 * Features:
 * - Real CRUD: Products, Users, Orders
 * - Order Status Management (live dropdown updates)
 * - Predefined Product Categories
 * - Image Upload + URL Preview
 * - Custom Modal Confirmation (no redirects)
 * - Modern Tailwind Glassmorphism UI
 */

session_start();

// --- AUTH GUARD ---
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$admin_user = htmlspecialchars($_SESSION['admin_user'] ?? 'Admin');

// --- CONFIG INCLUDE ---
$config_path = __DIR__ . '/../includes/config.php';
if (!file_exists($config_path)) {
    die('<h2 style="color:red">Fatal Error: includes/config.php not found!</h2>');
}
require_once $config_path;

if (!function_exists('connectDB')) {
    die('<h2 style="color:red">connectDB() missing in includes/config.php</h2>');
}

$pdo = connectDB();

// --- FETCH DATA ---
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT id, name, email, created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$orders = $pdo->query("SELECT o.id, o.user_id, o.grand_total, o.status, o.order_date, u.name AS user_name 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id
                       ORDER BY o.id DESC")->fetchAll(PDO::FETCH_ASSOC);

// --- STATIC OPTIONS ---
$statuses = ['Processing', 'Departure', 'Arrival', 'Delivered', 'Cancelled'];
$categories = ['Electronics', 'Fashion', 'Books', 'Home Appliances', 'Beauty', 'Toys', 'Sports', 'Groceries'];

function format_price($v) {
    return '₹' . number_format($v, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Evocart Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#06b6d4',
        accent: '#f59e0b',
        card: '#0f1724',
        dark: '#0b1220',
        success: '#10B981',
        error: '#EF4444',
      },
      fontFamily: { sans: ['Poppins','sans-serif'] }
    }
  }
}
</script>
<style>
body { font-family: 'Poppins', sans-serif; background:#0b1220; color:#e5e7eb; }
::-webkit-scrollbar{width:8px;}::-webkit-scrollbar-thumb{background:rgba(6,182,212,.5);border-radius:5px;}
.glass{backdrop-filter:blur(12px);background-color:rgba(15,23,36,.8);}
</style>
</head>
<body>

<!-- HEADER -->
<header class="bg-card shadow-lg sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-3xl font-extrabold text-primary">Evocart Admin Dashboard</h1>
    <div class="flex items-center gap-4">
      <span class="text-sm text-gray-400">Logged in as <span class="text-primary font-semibold"><?php echo $admin_user; ?></span></span>
      <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</header>

<main class="max-w-7xl mx-auto px-6 py-10 space-y-10">

  <h2 class="text-4xl font-bold mb-6 border-b border-primary/50 pb-2 flex items-center gap-3">
    <i class="fa fa-cogs text-primary"></i> Dashboard Overview
  </h2>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- LEFT: ADD FORMS -->
    <div class="space-y-8">

      <!-- ADD PRODUCT -->
      <div class="glass p-6 rounded-xl border border-gray-700 shadow-xl hover:shadow-cyan-700/20 transition">
        <h3 class="text-2xl font-bold mb-4 border-b border-primary/40 pb-2 text-primary flex items-center gap-2"><i class="fa fa-plus-circle"></i> Add Product</h3>
        <form id="addProductForm" enctype="multipart/form-data" class="space-y-4">
          <input type="text" name="name" placeholder="Product Name" required class="w-full p-3 rounded bg-dark border border-gray-600 text-white">
          <select name="category" required class="w-full p-3 rounded bg-dark border border-gray-600 text-white">
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="number" step="0.01" name="price" placeholder="Price (₹)" required class="w-full p-3 rounded bg-dark border border-gray-600 text-white">
          <textarea name="description" placeholder="Short Description" rows="2" class="w-full p-3 rounded bg-dark border border-gray-600 text-white"></textarea>

          <div class="bg-dark border border-gray-700 rounded-lg p-4 space-y-2">
            <label class="text-sm text-gray-400">Upload Image or Paste URL</label>
            <input type="file" id="imageFile" accept="image/*" class="w-full text-gray-300 file:bg-primary file:text-black file:font-semibold file:px-3 file:py-2 file:rounded-md">
            <input type="url" id="imageUrl" name="image_url" placeholder="Paste Image URL (optional)" class="w-full p-2 rounded bg-dark border border-gray-600 text-white">
            <div id="previewContainer" class="hidden mt-2">
              <img id="previewImage" src="" class="w-full rounded-lg border border-gray-600 max-h-40 object-cover">
            </div>
          </div>

          <div class="flex gap-2 items-center">
            <input type="checkbox" id="exclusive" name="is_exclusive_offer" value="1" class="w-4 h-4 accent-primary">
            <label for="exclusive" class="text-sm text-gray-300">Exclusive Offer</label>
          </div>

          <button class="w-full bg-primary text-black font-semibold py-3 rounded-lg hover:bg-cyan-400 transition"><i class="fa fa-magic mr-2"></i>Add Product</button>
        </form>
      </div>

      <!-- ADD USER -->
      <div class="glass p-6 rounded-xl border border-gray-700 shadow-xl hover:shadow-cyan-700/20 transition">
        <h3 class="text-2xl font-bold mb-4 border-b border-primary/40 pb-2 text-primary flex items-center gap-2"><i class="fa fa-user-plus"></i> Add User</h3>
        <form id="addUserForm" class="space-y-4">
          <input type="text" name="name" placeholder="Name" required class="w-full p-3 rounded bg-dark border border-gray-600 text-white">
          <input type="email" name="email" placeholder="Email" required class="w-full p-3 rounded bg-dark border border-gray-600 text-white">
          <input type="password" name="password" placeholder="Password" required class="w-full p-3 rounded bg-dark border border-gray-600 text-white">
          <button class="w-full bg-primary text-black font-semibold py-3 rounded-lg hover:bg-cyan-400 transition"><i class="fa fa-user-plus mr-2"></i>Create User</button>
        </form>
      </div>
    </div>

    <!-- RIGHT: DATA TABLES -->
    <div class="lg:col-span-2 space-y-10">

      <!-- ORDERS -->
      <div class="glass p-6 rounded-xl border border-gray-700 shadow-xl">
        <h3 class="text-2xl font-bold mb-4 border-b border-gray-700 pb-2 text-primary flex items-center gap-2"><i class="fa fa-truck"></i> Orders (<?= count($orders) ?>)</h3>
        <div class="max-h-96 overflow-y-auto space-y-3 pr-2">
          <?php if (!$orders): ?>
            <p class="text-gray-400 text-center py-6">No orders found.</p>
          <?php else: foreach ($orders as $o): ?>
            <div class="flex justify-between items-center p-4 bg-dark border border-gray-700 rounded-lg hover:border-primary/40 transition">
              <div>
                <h4 class="font-bold text-white">Order #<?= $o['id'] ?></h4>
                <p class="text-sm text-gray-400">
                  User: <?= htmlspecialchars($o['user_name'] ?? 'Guest') ?> |
                  <?= date('M j, Y g:i A', strtotime($o['order_date'])) ?>
                </p>
              </div>
              <div class="flex flex-col items-end gap-2">
                <p class="text-primary font-bold"><?= format_price($o['grand_total']) ?></p>
                <select class="bg-card border border-gray-600 rounded text-white p-2 text-sm order-status" data-id="<?= $o['id'] ?>">
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= ($o['status'] === $s ? 'selected' : '') ?>><?= $s ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- PRODUCTS -->
      <div class="glass p-6 rounded-xl border border-gray-700 shadow-xl">
        <h3 class="text-2xl font-bold mb-4 border-b border-gray-700 pb-2 text-warning flex items-center gap-2"><i class="fa fa-box"></i> Products (<?= count($products) ?>)</h3>
        <div class="max-h-96 overflow-y-auto space-y-3 pr-2">
          <?php if (!$products): ?><p class="text-gray-400 text-center py-6">No products yet.</p>
          <?php else: foreach ($products as $p): ?>
          <div class="flex justify-between items-center p-4 bg-dark border border-gray-700 rounded-lg hover:border-primary/40 transition">
            <div class="flex items-center gap-3">
              <?php if (!empty($p['image_url'])): ?>
                <img src="<?= htmlspecialchars($p['image_url']) ?>" class="w-12 h-12 rounded object-cover border border-gray-700">
              <?php endif; ?>
              <div>
                <p class="font-semibold text-white"><?= htmlspecialchars($p['name']) ?></p>
                <p class="text-sm text-gray-400"><?= htmlspecialchars($p['category']) ?></p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-primary font-bold"><?= format_price($p['price']) ?></span>
              <button class="delete-product text-red-500 hover:text-red-400" data-id="<?= $p['id'] ?>"><i class="fa fa-trash"></i></button>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- USERS -->
      <div class="glass p-6 rounded-xl border border-gray-700 shadow-xl">
        <h3 class="text-2xl font-bold mb-4 border-b border-gray-700 pb-2 text-info flex items-center gap-2"><i class="fa fa-users"></i> Users (<?= count($users) ?>)</h3>
        <div class="max-h-72 overflow-y-auto space-y-3 pr-2">
          <?php if (!$users): ?><p class="text-gray-400 text-center py-6">No users yet.</p>
          <?php else: foreach ($users as $u): ?>
          <div class="flex justify-between items-center p-4 bg-dark border border-gray-700 rounded-lg hover:border-primary/40 transition">
            <div>
              <p class="font-semibold text-white"><?= htmlspecialchars($u['name']) ?></p>
              <p class="text-sm text-gray-400"><?= htmlspecialchars($u['email']) ?></p>
            </div>
            <button class="delete-user text-red-500 hover:text-red-400" data-id="<?= $u['id'] ?>"><i class="fa fa-trash"></i></button>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- MODAL -->
<div id="modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
  <div class="bg-card p-8 rounded-xl border border-primary/40 shadow-2xl max-w-sm w-full text-center">
    <i class="fa fa-check-circle text-green-400 text-5xl mb-4"></i>
    <h3 class="text-xl font-bold text-white mb-2">Success!</h3>
    <p id="modalMsg" class="text-gray-400 mb-6">Action completed successfully.</p>
    <button id="modalOk" class="bg-primary text-black px-6 py-2 rounded-lg font-semibold hover:bg-cyan-400">OK</button>
  </div>
</div>

<script>
// Image Preview
const fileInput=document.getElementById('imageFile');
const urlInput=document.getElementById('imageUrl');
const preview=document.getElementById('previewImage');
const container=document.getElementById('previewContainer');
fileInput.onchange=()=>{const f=fileInput.files[0];if(!f)return;const r=new FileReader();r.onload=()=>{preview.src=r.result;container.classList.remove('hidden');};r.readAsDataURL(f);}
urlInput.oninput=()=>{if(urlInput.value.trim()){preview.src=urlInput.value;container.classList.remove('hidden');}else container.classList.add('hidden');}

// Modal
const modal=document.getElementById('modal');const msg=document.getElementById('modalMsg');
document.getElementById('modalOk').onclick=()=>location.reload();
function showModal(m){msg.textContent=m;modal.classList.remove('hidden');modal.classList.add('flex');}

// ADD PRODUCT
document.getElementById('addProductForm').onsubmit=async e=>{
 e.preventDefault();
 const fd=new FormData(e.target);
 fd.append('action','add_product');
 const res=await fetch('api/product_crud_api.php',{method:'POST',body:fd});
 const j=await res.json();showModal(j.message);
}

// ADD USER
document.getElementById('addUserForm').onsubmit=async e=>{
 e.preventDefault();
 const fd=new FormData(e.target);
 fd.append('action','add_user');
 const res=await fetch('api/user_crud.php',{method:'POST',body:fd});
 const j=await res.json();showModal(j.message);
}

// DELETE PRODUCT
document.querySelectorAll('.delete-product').forEach(b=>b.onclick=async()=>{
 if(!confirm('Delete product?'))return;
 const fd=new FormData();fd.append('action','delete_product');fd.append('product_id',b.dataset.id);
 const res=await fetch('api/product_crud_api.php',{method:'POST',body:fd});
 const j=await res.json();showModal(j.message);
});

// DELETE USER
document.querySelectorAll('.delete-user').forEach(b=>b.onclick=async()=>{
 if(!confirm('Delete user?'))return;
 const fd=new FormData();fd.append('action','delete_user');fd.append('user_id',b.dataset.id);
 const res=await fetch('api/user_crud.php',{method:'POST',body:fd});
 const j=await res.json();showModal(j.message);
});

// UPDATE ORDER STATUS
document.querySelectorAll('.order-status').forEach(sel=>{
 sel.onchange=async()=>{
   const fd=new FormData();
   fd.append('action','update_order_status');
   fd.append('order_id',sel.dataset.id);
   fd.append('status',sel.value);
   const res=await fetch('api/order_crud.php',{method:'POST',body:fd});
   const j=await res.json();showModal(j.message);
 }
});
</script>
</body>
</html>
