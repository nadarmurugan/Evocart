<?php
/**
 * Admin API Endpoint for Product CRUD Operations
 * Location: admin/api/product_crud_api.php
 * Handles creating, deleting, and updating product records.
 * 
 * ✅ Added: Description field (matches dashboard.php form)
 * ✅ Validations for all fields
 * ✅ Returns proper JSON responses for all CRUD operations
 */

session_start();

// --- Verify admin privileges ---
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access Denied. Admin privileges required.']);
    exit;
}

ob_start();
require_once __DIR__ . '/../../includes/config.php';
ob_clean();

header('Content-Type: application/json');

// Utility JSON response helper
function send_response($success, $message, $http_status = 200, $data = []) {
    if (ob_get_length()) ob_end_clean();
    http_response_code($http_status);
    $res = ['success' => $success, 'message' => $message];
    if (!empty($data)) $res['data'] = $data;
    echo json_encode($res);
    exit;
}

// Enforce POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method.', 405);
}

$action = $_POST['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);

try {
    $pdo = connectDB();

    switch ($action) {

        // =======================================================
        // CREATE PRODUCT
        // =======================================================
        case 'add_product':
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price_raw = $_POST['price'] ?? '';
            $price = (is_numeric($price_raw) && (float)$price_raw > 0) ? (float)$price_raw : false;
            $is_exclusive = isset($_POST['is_exclusive_offer']) ? 1 : 0;
            $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
            $image_url = trim($_POST['image_url'] ?? '');

            if (empty($name) || empty($category) || $price === false) {
                send_response(false, 'Product Name, Category, and Price (must be positive) are required.', 400);
            }

            if ($image_url === '') {
                $image_url = 'uploads/placeholder.jpg';
            }

            $sql = "INSERT INTO products 
                    (name, category, description, price, image_url, is_exclusive_offer, is_best_seller, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $category, $description, $price, $image_url, $is_exclusive, $is_best_seller]);

            $new_id = $pdo->lastInsertId();
            send_response(true, "✅ Product '{$name}' added successfully!", 201, [
                'id' => $new_id,
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'price' => $price,
                'is_exclusive_offer' => $is_exclusive,
                'is_best_seller' => $is_best_seller,
                'image_url' => $image_url
            ]);
            break;

        // =======================================================
        // DELETE PRODUCT
        // =======================================================
        case 'delete_product':
            if ($product_id <= 0) send_response(false, 'Invalid Product ID.', 400);

            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);

            if ($stmt->rowCount()) {
                send_response(true, "🗑️ Product ID {$product_id} deleted successfully.");
            } else {
                send_response(false, "Product ID {$product_id} not found.", 404);
            }
            break;

        // =======================================================
        // UPDATE PRODUCT
        // =======================================================
        case 'update_product':
            if ($product_id <= 0) send_response(false, 'Invalid Product ID.', 400);

            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price_raw = $_POST['price'] ?? '';
            $price = (is_numeric($price_raw) && (float)$price_raw > 0) ? (float)$price_raw : false;
            $is_exclusive = isset($_POST['is_exclusive_offer']) ? 1 : 0;
            $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
            $image_url = trim($_POST['image_url'] ?? '');

            if (empty($name) || empty($category) || $price === false) {
                send_response(false, 'Name, Category, and Price are required fields.', 400);
            }

            // If no image given, do not overwrite
            if ($image_url === '') {
                $stmt = $pdo->prepare("UPDATE products 
                    SET name=?, category=?, description=?, price=?, is_exclusive_offer=?, is_best_seller=?, updated_at=NOW() 
                    WHERE id=?");
                $stmt->execute([$name, $category, $description, $price, $is_exclusive, $is_best_seller, $product_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE products 
                    SET name=?, category=?, description=?, price=?, image_url=?, is_exclusive_offer=?, is_best_seller=?, updated_at=NOW() 
                    WHERE id=?");
                $stmt->execute([$name, $category, $description, $price, $image_url, $is_exclusive, $is_best_seller, $product_id]);
            }

            if ($stmt->rowCount() > 0) {
                send_response(true, "✅ Product '{$name}' updated successfully!");
            } else {
                send_response(false, "No changes made or Product not found.", 200);
            }
            break;

        // =======================================================
        // INVALID ACTION
        // =======================================================
        default:
            send_response(false, 'Unknown action specified.', 400);
    }

} catch (PDOException $e) {
    error_log("DB Error in product_crud_api.php: " . $e->getMessage());
    send_response(false, 'Database error occurred. Please try again.', 500);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    send_response(false, 'Unexpected server error.', 500);
}

send_response(false, 'Request failed unexpectedly.', 500);
