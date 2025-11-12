<?php
/**
 * Evocart - Enhanced Login Page (login.php)
 * Matches full Evocart landing theme (Tailwind + AOS + Gradient + Pattern Backgrounds)
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$redirect_target = 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evocart — Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#06b6d4',
                        accent: '#f59e0b',
                        darkbg: '#0b1220',
                        card: '#0f1724'
                    },
                    fontFamily: { poppins: ['Poppins','sans-serif'] },
                }
            }
        }
    </script>
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0b1220; overflow-x: hidden; }

        /* Patterns */
        .pattern-dots {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.06) 1px, transparent 2px);
            background-size: 10px 10px;
        }
        .pattern-checked {
            background-image: linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        /* Glow animation */
        .floating {
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 9999px;
            filter: blur(80px);
            opacity: 0.5;
            z-index: 0;
            animation: float 10s ease-in-out infinite alternate;
        }
        .float-1 { background: #06b6d4; top: 10%; left: 10%; animation-delay: 0s; }
        .float-2 { background: #f59e0b; bottom: 15%; right: 15%; animation-delay: 3s; }

        @keyframes float {
            from { transform: translateY(0px) scale(1); }
            to { transform: translateY(-40px) scale(1.1); }
        }

        /* Glassmorphism */
        .glass {
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.08);
        }

        /* Micro interactions */
        .btn-animated { transition: transform .15s ease, box-shadow .15s ease; }
        .btn-animated:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 6px 20px rgba(6,182,212,0.3); }
        .password-toggle { position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#94a3b8; }
        .password-toggle:hover { color:#f8fafc; }
        .gradient-text { background: linear-gradient(90deg,#06b6d4,#f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="pattern-checked relative min-h-screen flex items-center justify-center">

    <!-- Floating blobs -->
    <div class="floating float-1"></div>
    <div class="floating float-2"></div>

    <div class="relative z-10 w-full max-w-md mx-auto p-8 glass rounded-2xl shadow-2xl space-y-8" data-aos="fade-up">
        <div class="text-center space-y-2">
            <div class="flex justify-center mb-2">
                <div class="w-14 h-14 bg-gradient-to-br from-primary to-accent rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-black text-xl"></i>
                </div>
            </div>
            <h1 class="text-3xl font-extrabold gradient-text">Evocart Login</h1>
            <p class="text-gray-400 text-sm">Welcome back! Access your account to continue shopping.</p>
        </div>

        <div id="alert-message" class="hidden p-3 rounded-lg text-center font-semibold mb-4 transition-all duration-300"></div>

        <form id="login-form" action="api/process_login.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium mb-1 text-gray-300">Email Address</label>
                <div class="relative">
                    <input type="email" id="email" name="email" required
                        class="w-full p-3 pl-10 bg-darkbg border border-gray-600 rounded-lg focus:ring-primary focus:border-primary text-white placeholder-gray-500"
                        placeholder="you@example.com">
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-1 text-gray-300">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        class="w-full p-3 pl-10 bg-darkbg border border-gray-600 rounded-lg focus:ring-primary focus:border-primary text-white placeholder-gray-500"
                        placeholder="Your password">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                    <i class="password-toggle fas fa-eye-slash" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" id="submit-button" class="w-full bg-primary hover:bg-teal-600 text-black font-bold py-3 rounded-lg transition duration-300 shadow-lg shadow-primary/30 flex items-center justify-center space-x-2 btn-animated">
                <i class="fas fa-sign-in-alt" id="submit-icon"></i>
                <span id="submit-text">Log In</span>
            </button>
        </form>

        <div class="text-center text-gray-400 text-sm mt-6 space-y-2">
            <p>Don’t have an account?
                <a href="signup.php" class="text-primary hover:text-accent font-semibold transition duration-300">Sign Up</a>
            </p>
            <p><a href="index.php" class="text-primary hover:text-accent font-semibold transition duration-300 inline-flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        const form = document.getElementById('login-form');
        const alertMessage = document.getElementById('alert-message');
        const submitButton = document.getElementById('submit-button');
        const submitText = document.getElementById('submit-text');
        const submitIcon = document.getElementById('submit-icon');
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const redirectTarget = '<?php echo $redirect_target; ?>';

        function showAlert(message, isSuccess) {
            alertMessage.textContent = message;
            alertMessage.classList.remove('hidden', 'bg-green-700', 'bg-red-700', 'text-white');
            if (isSuccess) {
                alertMessage.classList.add('bg-green-700', 'text-white');
            } else {
                alertMessage.classList.add('bg-red-700', 'text-white');
            }
        }

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            submitButton.disabled = true;
            submitText.textContent = 'Verifying...';
            submitIcon.classList.remove('fa-sign-in-alt');
            submitIcon.classList.add('fa-spinner', 'fa-spin');
            alertMessage.classList.add('hidden');

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error(`Server responded with status: ${response.status}`);
                const data = await response.json();

                if (data.success) {
                    showAlert(data.message || 'Login successful. Redirecting...', true);
                    setTimeout(() => window.location.href = redirectTarget, 700);
                } else {
                    showAlert(data.message || 'Login failed. Please check your credentials.', false);
                }

            } catch (err) {
                showAlert('Network error or server failed to respond.', false);
            } finally {
                if (!alertMessage.classList.contains('bg-green-700')) {
                    submitButton.disabled = false;
                    submitText.textContent = 'Log In';
                    submitIcon.classList.add('fa-sign-in-alt');
                    submitIcon.classList.remove('fa-spinner', 'fa-spin');
                }
            }
        });
    </script>
</body>
</html>
