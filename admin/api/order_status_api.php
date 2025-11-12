<?php
/**
 * Evocart — Admin API for Order Status Update
 * Location: /admin/api/order_status_api.php
 *
 * - Updates order status in the `orders` table.
 * - Fully compatible with current table structure.
 * - Returns clean JSON responses for use in admin dashboard.
 */

session_start();
header('Content-Type: application/json');

// --- SECURITY: Admin only ---
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access Denied: Admin privileges required.']);
    exit;
}

require_once __DIR__ . '/../../includes/config.php';

/**
 * Sends a consistent JSON response
 */
function send_json($success, $message, $http_status = 200, $extra = []) {
    http_response_code($http_status);
    $res = ['success' => $success, 'message' => $message];
    if (!empty($extra)) $res['data'] = $extra;
    echo json_encode($res);
    exit;
}

// --- VALIDATE REQUEST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(false, 'Invalid request method. Use POST.', 405);
}

$action = $_POST['action'] ?? '';
if ($action !== 'update_status') {
    send_json(false, 'Invalid or missing action.', 400);
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$new_status = trim($_POST['new_status'] ?? '');

if ($order_id <= 0) {
    send_json(false, 'Invalid order ID.', 400);
}

// --- Define valid statuses ---
$valid_statuses = ['Processing', 'Departure', 'Arrival', 'Delivered', 'Cancelled'];
if (!in_array($new_status, $valid_statuses, true)) {
    send_json(false, 'Invalid order status provided.', 400);
}

// --- DATABASE OPERATION ---
try {
    $pdo = connectDB();

    // Ensure order exists before updating
    $check = $pdo->prepare("SELECT id, status FROM orders WHERE id = ?");
    $check->execute([$order_id]);
    $order = $check->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        send_json(false, "Order #{$order_id} not found.", 404);
    }

    // If same status, skip unnecessary update
    if ($order['status'] === $new_status) {
        send_json(false, "Order #{$order_id} already has status '{$new_status}'.", 409);
    }

    // Update the status
    $update = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update->execute([$new_status, $order_id]);

    if ($update->rowCount() > 0) {
        send_json(true, "Order #{$order_id} status successfully updated to '{$new_status}'.", 200, [
            'order_id' => $order_id,
            'new_status' => $new_status
        ]);
    } else {
        send_json(false, "Failed to update order #{$order_id}.", 500);
    }

} catch (PDOException $e) {
    error_log("DB Error in order_status_api.php: " . $e->getMessage());
    send_json(false, 'Database error occurred during update.', 500);
}
