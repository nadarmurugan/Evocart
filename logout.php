<?php
/**
 * Logout Page with Confirmation Modal
 * Location: logout.php
 * Handles session termination and redirection to the login page.
 */
session_start();

// Define target after successful logout (Login page)
$redirect_target = 'index.php';

// Check for explicit confirmation via a GET parameter from the modal button
if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
    // 1. Destroy all session variables
    $_SESSION = array();

    // 2. Destroy the session cookie (if applicable)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // 3. Destroy the session itself
    session_destroy();
    
    // Redirect immediately to the login page
    header("Location: $redirect_target");
    exit;
}

// If not confirmed, we proceed to display the confirmation page/modal below.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        // Custom Tailwind Config (Matching other files)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#0D9488', // Teal accent color
                        'dark-bg': '#0F172A', // Slate 900
                        'card-bg': '#1E293B', // Slate 800
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'], 
                    },
                }
            }
        }
    </script>
    <style>
        /* Base styles */
        body { font-family: 'Poppins', sans-serif; }
        .modal-content-transition {
            transition: all 0.3s ease-in-out;
        }
        .scale-in {
            transform: scale(1);
            opacity: 1;
        }
    </style>
</head>
<body class="bg-dark-bg min-h-screen flex items-center justify-center">

    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[100] p-4">
        <div class="bg-card-bg p-8 rounded-xl shadow-2xl max-w-sm w-full m-4 modal-content-transition transform scale-95 opacity-0" id="modal-content">
            <div class="text-center space-y-4">
                <i class="fas fa-sign-out-alt text-6xl text-red-500"></i>
                <h3 class="text-3xl font-bold text-white">Confirm Logout</h3>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <p class="text-gray-400">Are you sure you want to end your session?</p>
                <?php else: ?>
                    <p class="text-yellow-400">You are not currently logged in.</p>
                <?php endif; ?>
            </div>
            
            <div class="mt-8 flex justify-center space-x-4">
                <button onclick="window.location.href='index.php'" 
                        class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php?confirm=true" 
                       class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-sign-out-alt mr-2"></i> Confirm Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo $redirect_target; ?>"
                       class="bg-primary hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-user-circle mr-2"></i> Go to Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalContent = document.getElementById('modal-content');
            
            // Start modal animation
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-in', 'opacity-100');
            }, 50);
        });
    </script>
</body>
</html>