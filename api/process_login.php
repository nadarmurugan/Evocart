<?php
/**
 * API Endpoint for User Login
 * Location: api/process_login.php
 * Handles form validation, credential verification, and session creation.
 */

// 1. Initialize session and include configuration
session_start();
// Assumes config.php is located in the includes folder one level up
require_once __DIR__ . '/../includes/config.php'; 

// --- RESPONSE HANDLING ---
header('Content-Type: application/json');

// Function to handle JSON error response and exit
function send_error($message, $http_status = 200) {
    http_response_code($http_status);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Invalid request method.', 405);
}

// 2. Sanitize and Validate Input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    send_error('Email and password are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Keep this generic for security
    send_error('Invalid credentials. Please try again.'); 
}

// 3. Database Authentication Logic
try {
    $pdo = connectDB(); 

    // FIX: Included the 'name' column in the SELECT statement
    $stmt = $pdo->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Verify Credentials
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // Authentication SUCCESSFUL: Create session
        $_SESSION['user_id'] = $user['id']; 
        // This line now works correctly because 'name' is fetched
        $_SESSION['user_name'] = $user['name']; 
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful. Redirecting to your dashboard...',
            'redirect' => 'index.php'
        ]);

    } else {
        // Authentication FAILED: Generic error message for security
        send_error('Invalid credentials. Please try again.');
    }

} catch (\PDOException $e) {
    // Handle database connection or query errors
    // error_log("DB Error in process_login: " . $e->getMessage());
    send_error('A critical system error occurred during login. Please try again later.');

} catch (\Exception $e) {
    // General error handling
    send_error('An unexpected error occurred.');
}

exit;
?>