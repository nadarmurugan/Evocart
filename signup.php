<?php
/**
 * Evocart - Styled Sign Up Page (signup.php)
 * Full visual match to Evocart's landing & login pages
 */
$redirect_target = 'login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evocart — Sign Up</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Tailwind CSS -->
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

    <!-- AOS Animations -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0b1220; overflow-x: hidden; }

        /* Background patterns */
        .pattern-dots {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.06) 1px, transparent 2px);
            background-size: 10px 10px;
        }
        .pattern-checked {
            background-image: linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        /* Floating glowing blobs */
        .floating {
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 9999px;
            filter: blur(80px);
            opacity: 0.45;
            animation: float 8s ease-in-out infinite alternate;
        }
        .float-1 { background: #06b6d4; top: 10%; left: 15%; }
        .float-2 { background: #f59e0b; bottom: 15%; right: 15%; animation-delay: 2s; }

        @keyframes float {
            from { transform: translateY(0px) scale(1); }
            to { transform: translateY(-40px) scale(1.1); }
        }

        /* Glassmorphism form card */
        .glass {
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.08);
        }

        /* Buttons & transitions */
        .btn-animated { transition: transform .18s ease, box-shadow .18s ease; }
        .btn-animated:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 6px 20px rgba(6,182,212,0.35); }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            transition: color .25s;
        }
        .password-toggle:hover { color: #f8fafc; }

        .gradient-text { background: linear-gradient(90deg,#06b6d4,#f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="relative min-h-screen flex items-center justify-center pattern-checked">

    <!-- Floating blobs -->
    <div class="floating float-1"></div>
    <div class="floating float-2"></div>

    <!-- Sign Up Card -->
    <div class="relative z-10 w-full max-w-md mx-auto p-8 glass rounded-2xl shadow-2xl space-y-8" data-aos="fade-up">
        <div class="text-center space-y-2">
            <div class="flex justify-center mb-2">
                <div class="w-14 h-14 bg-gradient-to-br from-primary to-accent rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-black text-xl"></i>
                </div>
            </div>
            <h1 class="text-3xl font-extrabold gradient-text">Create Your Evocart Account</h1>
            <p class="text-gray-400 text-sm">Join our community — shop smarter, faster, and better.</p>
        </div>

        <div id="alert-message" class="hidden p-3 rounded-lg text-center font-semibold mb-4 transition-all duration-300"></div>

        <!-- Signup Form -->
        <form id="signup-form" action="api/process_signup.php" method="POST" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium mb-1 text-gray-300">Full Name</label>
                <div class="relative">
                    <input type="text" id="name" name="name" required
                        class="w-full p-3 pl-10 bg-darkbg border border-gray-600 rounded-lg focus:ring-primary focus:border-primary text-white placeholder-gray-500"
                        placeholder="John Doe">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                </div>
            </div>

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
                    <input type="password" id="password" name="password" required minlength="6"
                        class="w-full p-3 pl-10 bg-darkbg border border-gray-600 rounded-lg focus:ring-primary focus:border-primary text-white placeholder-gray-500"
                        placeholder="Minimum 6 characters">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                    <i class="password-toggle fas fa-eye-slash" id="togglePassword"></i>
                </div>
            </div>

            <div class="text-xs text-gray-500 flex items-center gap-2 mt-2">
                <input type="checkbox" required class="accent-primary w-4 h-4">
                <span>I agree to Evocart’s <a href="#" class="text-primary hover:text-accent underline">Terms</a> and <a href="#" class="text-primary hover:text-accent underline">Privacy Policy</a>.</span>
            </div>

            <button type="submit" id="submit-button" class="w-full bg-primary hover:bg-teal-600 text-black font-bold py-3 rounded-lg transition duration-300 shadow-lg shadow-primary/30 flex items-center justify-center space-x-2 btn-animated">
                <i class="fas fa-user-plus" id="submit-icon"></i>
                <span id="submit-text">Create Account</span>
            </button>
        </form>

        <div class="text-center text-gray-400 text-sm mt-6 space-y-2">
            <p>Already have an account?
                <a href="<?php echo $redirect_target; ?>" class="text-primary hover:text-accent font-semibold transition duration-300">Log In</a>
            </p>
            <p><a href="index.php" class="text-primary hover:text-accent font-semibold transition duration-300 inline-flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-[100]">
        <div class="bg-card p-8 rounded-xl shadow-2xl max-w-sm w-full m-4 modal-content-transition transform scale-95 opacity-0" id="modal-content">
            <div class="text-center space-y-4">
                <i class="fas fa-check-circle text-6xl text-primary animate-pulse"></i>
                <h3 class="text-3xl font-bold text-white">Registration Successful!</h3>
                <p class="text-gray-400">Your account has been created. You will be redirected to the login page shortly.</p>
                <div class="w-full bg-darkbg rounded-full h-2.5 mt-4">
                    <div id="progress-bar" class="bg-primary h-2.5 rounded-full" style="width: 100%; transition: width 3s linear;"></div>
                </div>
                <p id="countdown" class="text-sm font-semibold text-primary">Redirecting in 3 seconds...</p>
            </div>
            <div class="mt-6 text-center">
                <a href="<?php echo $redirect_target; ?>" class="text-gray-400 hover:text-white transition duration-200 text-sm underline">Click here to go now</a>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        const form = document.getElementById('signup-form');
        const alertMessage = document.getElementById('alert-message');
        const submitButton = document.getElementById('submit-button');
        const submitText = document.getElementById('submit-text');
        const submitIcon = document.getElementById('submit-icon');
        const successModal = document.getElementById('success-modal');
        const modalContent = document.getElementById('modal-content');
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const redirectTarget = '<?php echo $redirect_target; ?>';
        const REDIRECT_TIME = 3000;

        function showAlert(message, isSuccess) {
            alertMessage.textContent = message;
            alertMessage.classList.remove('hidden','bg-green-700','bg-red-700','text-white');
            if (isSuccess) alertMessage.classList.add('bg-green-700','text-white');
            else alertMessage.classList.add('bg-red-700','text-white');
        }

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });

        function showRedirectModal() {
            successModal.classList.remove('hidden');
            successModal.style.display = 'flex';
            setTimeout(() => {
                modalContent.classList.remove('scale-95','opacity-0');
                modalContent.classList.add('scale-in','opacity-100');
            }, 50);
            document.getElementById('progress-bar').style.width = '0%';
            let count = 3;
            document.getElementById('countdown').textContent = `Redirecting in ${count} seconds...`;
            const interval = setInterval(() => {
                count--;
                document.getElementById('countdown').textContent = `Redirecting in ${count} seconds...`;
                if (count <= 0) clearInterval(interval);
            }, 1000);
            setTimeout(() => { window.location.href = redirectTarget; }, REDIRECT_TIME);
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (passwordInput.value.length < 6) {
                showAlert('Password must be at least 6 characters long.', false);
                return;
            }
            submitButton.disabled = true;
            submitText.textContent = 'Processing...';
            submitIcon.classList.remove('fa-user-plus');
            submitIcon.classList.add('fa-spinner','fa-spin');
            alertMessage.classList.add('hidden');
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, { method: 'POST', body: formData, headers: {'Accept':'application/json'} });
                if (!response.ok) throw new Error(`Status: ${response.status}`);
                const data = await response.json();
                if (data.success) {
                    showAlert(data.message, true);
                    showRedirectModal();
                } else {
                    showAlert(data.message || 'An error occurred.', false);
                }
            } catch (err) {
                showAlert('Network error or server failed to respond.', false);
            } finally {
                if (successModal.classList.contains('hidden')) {
                    submitButton.disabled = false;
                    submitText.textContent = 'Create Account';
                    submitIcon.classList.add('fa-user-plus');
                    submitIcon.classList.remove('fa-spinner','fa-spin');
                }
            }
        });
    </script>
</body>
</html>
