<?php
/**
 * Admin Logout Script
 * Location: admin/logout.php
 */

session_start();

// Unset all admin-specific session variables
unset($_SESSION['is_admin']);
unset($_SESSION['admin_user']);

// Destroy the session (optional, but clean)
session_destroy();

// Redirect back to the admin login page
header("Location: admin_login.php");
exit();
?>