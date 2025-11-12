<?php
/**
 * API Endpoint for User Signup
 * Location: api/process_signup.php
 * Handles form validation, password hashing, and real database insertion.
 */

// Include the configuration and database connection function
// Path is relative from api/ to includes/
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

// 1. Sanitize and Validate Input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($name) || empty($email) || empty($password)) {
    send_error('All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_error('Invalid email format.');
}

if (strlen($password) < 6) {
    send_error('Password must be at least 6 characters.');
}

// --- DATABASE LOGIC ---
try {
    // 1. Establish database connection
    $pdo = connectDB(); 

    // 2. Email Existence Check
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) { 
        throw new \Exception("This email is already registered."); 
    }
    
    // 3. Hash the Password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 4. Prepare and Execute Insertion
    // Note: Assuming a 'users' table with columns 'name', 'email', 'password_hash', and 'created_at'.
    $sql = "INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email, $password_hash]);
    
    $user_id = $pdo->lastInsertId();

    // Successful Response
    echo json_encode([
        'success' => true, 
        'message' => 'User registration successful. Redirecting to login...', 
        'user_id' => $user_id
    ]);

} catch (\PDOException $e) {
    // Handle database specific errors
    // error_log("DB Error in process_signup.php: " . $e->getMessage());
    send_error('A critical system error occurred during registration. Please try again later.');

} catch (\Exception $e) {
    // Handle application logic errors (e.g., email already exists)
    send_error($e->getMessage());
}

exit;
?>