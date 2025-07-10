<?php
/**
 * Custom Login Template
 * 
 * This template replaces the default WordPress login page with a modern design
 * inspired by the React sign-in.jsx component.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
    exit;
}

// Handle form submission
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    // Verify nonce
    if (!wp_verify_nonce($_POST['login_nonce'], 'custom_login_nonce')) {
        $error_message = 'Security check failed. Please try again.';
    } else {
        $email = sanitize_email($_POST['user_email']);
        $password = $_POST['user_password'];
        $remember = isset($_POST['remember_me']);
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            $error_message = 'Please fill in all required fields.';
        } elseif (!is_email($email)) {
            $error_message = 'Please enter a valid email address.';
        } else {
            // Attempt login
            $user = wp_authenticate($email, $password);
            
            if (is_wp_error($user)) {
                $error_message = $user->get_error_message();
            } else {
                // Login successful
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, $remember);
                
                // Apply login redirect filter
                $redirect_url = apply_filters('liftingtracker_login_redirect', home_url('/dashboard'), $user);
                wp_redirect($redirect_url);
                exit;
            }
        }
    }
}

// Get redirect URL
$redirect_to = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : home_url('/dashboard');

get_header();
?>

<main class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="w-full max-w-md">
        
        <!-- Back to Home Button -->
        <div class="mb-6">
            <a href="<?php echo esc_url(home_url('/')); ?>" 
               class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-2xl p-8">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Welcome Back</h1>
                <p class="text-lg text-gray-600">Sign in to continue your fitness journey.</p>
            </div>

            <!-- Login Form -->
            <form method="post" class="space-y-6" id="custom-login-form">
                <?php wp_nonce_field('custom_login_nonce', 'login_nonce'); ?>
                
                <!-- Email Field -->
                <div>
                    <label for="user_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input type="email" 
                           id="user_email" 
                           name="user_email" 
                           required
                           value="<?php echo isset($_POST['user_email']) ? esc_attr($_POST['user_email']) : ''; ?>"
                           placeholder="name@mail.com"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <!-- Password Field -->
                <div>
                    <label for="user_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input type="password" 
                           id="user_password" 
                           name="user_password" 
                           required
                           placeholder="••••••••"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember_me" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" 
                       class="text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors">
                        Forgot Password?
                    </a>
                </div>

                <!-- Error/Success Messages -->
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-800"><?php echo esc_html($error_message); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Submit Button -->
                <button type="submit" 
                        name="login_submit"
                        class="w-full bg-black text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    Sign In
                </button>
            </form>

            <!-- Social Login Options -->
            <?php if (apply_filters('liftingtracker_show_social_login', true)): ?>
                <div class="mt-8 space-y-4">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or continue with</span>
                        </div>
                    </div>

                    <!-- Google Login -->
                    <button type="button" 
                            class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors"
                            onclick="handleGoogleLogin()">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Sign in with Google
                    </button>
                </div>
            <?php endif; ?>

            <!-- Registration Link -->
            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Don't have an account?
                    <a href="<?php echo esc_url(wp_registration_url()); ?>" 
                       class="font-medium text-black hover:text-gray-700 transition-colors ml-1">
                        Create one here
                    </a>
                </p>
            </div>
        </div>
    </div>
</main>

<script>
// Handle Google login (placeholder for actual implementation)
function handleGoogleLogin() {
    // This would integrate with Google OAuth
    console.log('Google login clicked');
    alert('Google login integration would be implemented here');
}

// Form validation
document.getElementById('custom-login-form').addEventListener('submit', function(e) {
    const email = document.getElementById('user_email').value;
    const password = document.getElementById('user_password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
    }
    
    if (!isValidEmail(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return false;
    }
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
</script>

<?php
get_footer();
?>
