<?php
/**
 * Admin API Endpoint for User CRUD Operations
 * Location: admin/api/user_crud.php
 * Handles creating, deleting, and updating user records.
 */

// Start session to enforce admin check
session_start();

// Check for admin status
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access Denied. Admin privileges required.']);
    exit;
}

// Use output buffering to prevent header issues caused by include
ob_start();
// Include the configuration and database connection function
require_once __DIR__ . '/../../includes/config.php'; 
// Clean buffer immediately after include
ob_clean();

header('Content-Type: application/json');

function send_response($success, $message, $http_status = 200, $data = []) {
    // Ensure all output buffers are cleared before sending the JSON response
    if (ob_get_length()) {
        ob_end_clean();
    }
    http_response_code($http_status);
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method.', 405);
}

$action = $_POST['action'] ?? '';
$user_id = (int)($_POST['user_id'] ?? 0);

try {
    $pdo = connectDB();

    switch ($action) {
        
        // --- C: CREATE (Add New User) ---
        case 'add_user':
            $name = trim($_POST['new_username'] ?? '');
            $email = trim($_POST['new_email'] ?? '');
            $password = $_POST['new_password'] ?? '';

            if (empty($name) || empty($email) || empty($password) || strlen($password) < 6) {
                send_response(false, 'All fields are required, and password must be at least 6 characters.', 400);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                send_response(false, 'Invalid email format.', 400);
            }

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) { 
                send_response(false, "This email is already registered.", 409); // 409 Conflict
            }
            
            // Security: Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $password_hash]);
            
            $new_user_id = $pdo->lastInsertId();
            
            send_response(true, "User '{$name}' created successfully!", 201, [
                'id' => $new_user_id,
                'name' => $name,
                'email' => $email
            ]);
            break;

        // --- D: DELETE User ---
        case 'delete_user':
            if ($user_id <= 0) {
                send_response(false, 'Invalid User ID.', 400);
            }
            // Check to prevent deleting the current admin user (if IDs match)
            if ($user_id === (int)($_SESSION['user_id'] ?? 0)) { 
                send_response(false, "Cannot delete the currently logged-in admin account.", 403);
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            if ($stmt->rowCount()) {
                 send_response(true, "User ID {$user_id} deleted successfully.");
            } else {
                 send_response(false, "User ID {$user_id} not found.", 404);
            }
            break;

        // --- U: UPDATE User Email ---
        case 'update_user_email':
            $new_email = trim($_POST['new_email'] ?? '');
            if ($user_id <= 0 || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                send_response(false, 'Invalid User ID or email format.', 400);
            }
            
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$new_email, $user_id]);
            
            if ($stmt->rowCount()) {
                 send_response(true, "User ID {$user_id} email updated to {$new_email}.", 200, [
                     'user_id' => $user_id,
                     'new_email' => $new_email
                 ]);
            } else {
                 send_response(false, "Update failed: User ID {$user_id} not found or email is the same.", 404);
            }
            break;

        default:
            send_response(false, 'Unknown action specified.', 400);
            break;
    }

} catch (\PDOException $e) {
    // Log database error and send generic user-facing message
    error_log("DB Error in user_crud.php: " . $e->getMessage());
    send_response(false, 'A database error occurred. Check logs.', 500);
} catch (\Exception $e) {
    // Log general error
    error_log("General Error in user_crud.php: " . $e->getMessage());
    send_response(false, 'An unexpected error occurred.', 500);
}
// End of file