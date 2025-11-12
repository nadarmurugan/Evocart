<?php
/**
 * Database Configuration File
 * Location: includes/config.php
 */

// IMPORTANT: Replace these mock variables with your actual database credentials.
// For security, never expose this file or your credentials publicly.
// Ensure you have a secure way to manage these credentials in a production environment.
$db_host = 'sql100.infinityfree.com';
$db_user = 'if0_40348717';
$db_pass = 'oBbUI0JTsXXwB7j';
$db_name = 'if0_40348717_evocart'; // Database name

/**
 * Establishes and returns a PDO database connection.
 * @return PDO
 * @throws \PDOException if the connection fails.
 */
function connectDB() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    // Data Source Name
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    
    $options = [
        // Throw exceptions on errors
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Fetch results as associative arrays
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Disable emulated prepared statements for security and consistency
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        return $pdo;
    } catch (\PDOException $e) {
        // Log the full error to a server file for debugging, but only show a generic message to the user/API caller.
        // error_log("Database Connection Failed: " . $e->getMessage());
        throw new \PDOException("A database connection error occurred.", (int)$e->getCode());
    }
}
// Removed the extra closing brace here