<?php
/**
 * Order Success/Confirmation Page
 * Location: success.php
 * * This page simulates the final step after a successful payment (e.g., from a Stripe webhook).
 */

// 1. Initialize session
session_start();

// Get the mock Order ID from the URL parameter
$order_id = htmlspecialchars($_GET['order_id'] ?? 'N/A');

// Safely retrieve user data
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Valued Customer');

// --- CRITICAL STEP: CLEAR THE CART AND MOCK ORDER ID ---
// In a real application, you would also update the DB order status to 'Paid'.
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}
if (isset($_SESSION['mock_order_id'])) {
    unset($_SESSION['mock_order_id']);
}
// --- END CRITICAL STEP ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success! - Evocart</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#0D9488', // Teal accent color
                        'dark-bg': '#0F172A', // Slate 900
                        'card-bg': '#1E293B', // Slate 800
                        'success': '#10B981', // Green 500
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'], 
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-gray-100 min-h-screen">

    <header class="bg-card-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-center items-center">
            <a href="index.php" class="text-3xl font-extrabold text-primary tracking-widest">EVOCART</a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

        <div class="bg-card-bg p-8 md:p-12 rounded-xl shadow-2xl text-center border-t-4 border-success">
            
            <i class="fas fa-check-circle text-success text-6xl mb-6 animate-pulse"></i>

            <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">
                Thank You, <?php echo $user_name; ?>!
            </h1>
            
            <p class="text-gray-300 text-lg mb-8">
                Your order was successfully placed and payment was processed.
            </p>

            <div class="bg-dark-bg p-6 rounded-lg mb-8">
                <p class="text-xl font-semibold text-success mb-2">
                    Confirmation Number:
                </p>
                <p class="text-4xl font-black text-white tracking-wider">
                    #<?php echo $order_id; ?>
                </p>
            </div>

            <p class="text-gray-400 mb-10">
                A confirmation email with the details of your purchase has been sent to your registered email address. You can track your order using the link below.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="index.php" class="flex items-center justify-center space-x-2 bg-primary hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-xl transition duration-300 shadow-md">
                    <i class="fas fa-home"></i>
                    <span>Continue Shopping</span>
                </a>
                <a href="#" class="flex items-center justify-center space-x-2 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-xl transition duration-300 shadow-md">
                                <a href="order_status.php" class="flex items-center justify-center space-x-2 bg-primary hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-xl transition duration-300 shadow-md">
   
                <i class="fas fa-box-open"></i>
                    <span>View Order Status (Mock)</span>
                </a>
            </div>
        </div>

    </main>

    <footer class="bg-card-bg mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> Evocart. Simple Mockup E-commerce.</p>
        </div>
    </footer>
</body>
</html>