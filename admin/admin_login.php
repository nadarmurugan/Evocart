<?php
/**
 * Admin Login Page
 * Location: admin/admin_login.php
 * - FIX: Added a client-side modal to confirm successful login before redirecting 
 * to the dashboard, improving user experience.
 * - MODIFIED: Updated styling to match Evocart's dark theme and improve responsiveness.
 */

// 1. Initialize session
session_start();

// --- CONFIGURATION: Mock Admin Credentials ---
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD = 'admin123'; // In a real app, this must be securely hashed!
const ADMIN_LOGIN_SUCCESS_REDIRECT = 'dashboard.php'; 

// Initialize variables
$login_message = '';
$username = '';
$login_successful = false; // Flag to trigger JS modal

// Check if the user is already logged in as admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: " . ADMIN_LOGIN_SUCCESS_REDIRECT);
    exit();
}

// 2. Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Simple validation and authentication
    if (empty($username) || empty($password)) {
        $login_message = 'Please enter both username and password.';
    } 
    // Mock Authentication check
    elseif ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        
        // --- SUCCESSFUL LOGIN ---
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_user'] = ADMIN_USERNAME;
        
        // Set flag to true to trigger the modal display via JavaScript
        $login_successful = true; 
        
        // Note: PHP does NOT perform the immediate redirect here. 
        // JavaScript will handle the delay and redirect after showing the modal.
        
    } else {
        $login_message = 'Invalid username or password.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Evocart</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Standard Evocart Theme Colors
                        primary: '#06b6d4', // Cyan
                        accent: '#f59e0b',  // Amber
                        darkbg: '#0b1220',  // Deep dark blue
                        card: '#0f1724',    // Card background
                        
                        // Specific use colors
                        'red-error': '#EF4444', 
                        'success': '#10B981',
                        'input-bg': '#1e293b' // Slate 800 for contrast input bg
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'], 
                    },
                    boxShadow: {
                        '3xl': '0 35px 60px -15px rgba(0, 0, 0, 0.7)',
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Poppins', sans-serif;
            /* Subtle radial background gradient for depth */
            background-image: radial-gradient(at top left, rgba(6, 182, 212, 0.05), #0b1220 75%);
        }
        
        /* Modal transitions */
        .modal {
            transition: opacity 0.3s ease-out, visibility 0.3s ease-out;
        }

        /* Gradient Text (used on the logo) */
        .gradient-text { 
            background: linear-gradient(90deg,#06b6d4,#f59e0b); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }
    </style>
</head>
<body class="bg-darkbg text-gray-100 min-h-screen flex items-center justify-center p-4">

    <!-- Login Card -->
    <div class="w-full max-w-sm sm:max-w-md p-8 rounded-2xl shadow-3xl bg-card border border-primary/20">
        
        <div class="text-center mb-10">
            <h1 class="text-5xl font-extrabold tracking-widest" style="color:transparent;">
                <span class="gradient-text">EVOCART</span>
            </h1>
            <p class="text-gray-300 mt-2 text-xl font-semibold">Admin Panel</p>
        </div>

        <?php if (!empty($login_message)): ?>
            <div class="p-3 mb-6 rounded-lg bg-red-error/20 text-red-error border border-red-error font-semibold text-center text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($login_message); ?>
            </div>
        <?php endif; ?>

        <form action="admin_login.php" method="POST" class="space-y-6">
            
            <div>
                <label for="username" class="block text-sm font-medium mb-2 text-gray-400">Username</label>
                <input type="text" id="username" name="username" required 
                        value="<?php echo htmlspecialchars($username); ?>"
                        placeholder="admin"
                        class="w-full p-3 bg-input-bg border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-primary focus:border-primary transition duration-200 shadow-inner">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-2 text-gray-400">Password</label>
                <input type="password" id="password" name="password" required 
                        placeholder="admin123"
                        class="w-full p-3 bg-input-bg border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-primary focus:border-primary transition duration-200 shadow-inner">
            </div>

            <button type="submit" id="login-button" class="w-full bg-primary hover:bg-cyan-500 text-black font-extrabold py-3 px-6 rounded-xl transition duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-primary/50">
                <i class="fas fa-sign-in-alt"></i>
                <span>Secure Log In</span>
            </button>
            
            <p class="text-center text-xs text-gray-500 mt-4 pt-2 border-t border-white/5">
                Mock Credentials: **U: admin**, **P: admin123**
            </p>
        </form>

        <p class="text-center text-gray-500 mt-8">
            <a href="../index.php" class="text-primary hover:text-cyan-500 transition duration-300 font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Back to Shop
            </a>
        </p>
    </div>
    
    <!-- Success Modal -->
    <div id="redirect-modal" class="modal fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-[100] p-4 opacity-0 invisible">
        <div class="bg-card p-10 rounded-2xl shadow-3xl max-w-xs w-full text-center border-t-8 border-success">
            <i class="fas fa-user-shield text-success text-6xl mb-4 animate-bounce"></i>
            <h3 class="text-2xl font-bold text-white mb-2">Access Granted</h3>
            <p class="text-gray-400 mb-4">Welcome back, Admin.</p>
            <p class="text-lg font-extrabold text-primary">Redirecting...</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginSuccessful = <?php echo $login_successful ? 'true' : 'false'; ?>;
            const modal = document.getElementById('redirect-modal');
            const dashboardUrl = '<?php echo ADMIN_LOGIN_SUCCESS_REDIRECT; ?>';

            if (loginSuccessful) {
                // 1. Show the modal
                modal.classList.remove('invisible', 'opacity-0');
                modal.classList.add('visible', 'opacity-100');
                
                // 2. Disable the form/button while redirecting
                document.getElementById('login-button').disabled = true;

                // 3. Wait for 2 seconds (for user to read the message) then redirect
                setTimeout(function() {
                    window.location.href = dashboardUrl;
                }, 2000);
            }
        });
    </script>
</body>
</html>