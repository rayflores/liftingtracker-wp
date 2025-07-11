<?php
/**
 * Custom Registration Template
 * 
 * This template creates a multi-step registration process
 * inspired by the React sign-up.jsx component.
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

// Check if registration is enabled
if (!get_option('users_can_register')) {
    wp_redirect(wp_login_url());
    exit;
}

// Initialize session for multi-step form
if (!session_id()) {
    session_start();
}

// Initialize form data
if (!isset($_SESSION['registration_data'])) {
    $_SESSION['registration_data'] = [
        'current_step' => 1,
        'email' => '',
        'password' => '',
        'confirm_password' => '',
        'terms_accepted' => false,
        'first_name' => '',
        'last_name' => '',
        'username' => '',
        'date_of_birth' => '',
        'gender' => '',
        'bio' => '',
        'height_feet' => '',
        'height_inches' => '',
        'height_cm' => '',
        'current_weight' => '',
        'target_weight' => '',
        'body_fat_percentage' => '',
        'preferred_units' => 'imperial',
        'fitness_level' => 'beginner',
        'years_training' => '',
        'primary_goal' => 'build_muscle',
        'workout_frequency' => '3',
        'activity_level' => '1.4',
        'protein_percentage' => '30',
        'carbs_percentage' => '40',
        'fat_percentage' => '30',
        'dietary_restrictions' => [],
        'allergies' => []
    ];
}

$form_data = $_SESSION['registration_data'];
$current_step = $form_data['current_step'];
$total_steps = 4; // We'll skip payment for now
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!wp_verify_nonce($_POST['registration_nonce'], 'custom_registration_nonce')) {
        $error_message = 'Security check failed. Please try again.';
    } else {
        // Update form data with submitted values
        foreach ($_POST as $key => $value) {
            if (isset($form_data[$key])) {
                if (is_array($value)) {
                    $form_data[$key] = array_map('sanitize_text_field', $value);
                } else {
                    $form_data[$key] = sanitize_text_field($value);
                }
            }
        }
        
        // Handle navigation
        if (isset($_POST['next_step'])) {
            if (liftingtracker_validate_current_step($current_step, $form_data)) {
                $current_step = min($current_step + 1, $total_steps);
                $form_data['current_step'] = $current_step;
            } else {
                $error_message = liftingtracker_get_step_validation_message($current_step, $form_data);
            }
        } elseif (isset($_POST['prev_step'])) {
            $current_step = max($current_step - 1, 1);
            $form_data['current_step'] = $current_step;
        } elseif (isset($_POST['complete_registration'])) {
            if (liftingtracker_validate_current_step($current_step, $form_data)) {
                // Create user account
                $user_data = [
                    'user_login' => $form_data['username'],
                    'user_email' => $form_data['email'],
                    'user_pass' => $form_data['password'],
                    'first_name' => $form_data['first_name'],
                    'last_name' => $form_data['last_name'],
                    'display_name' => $form_data['first_name'] . ' ' . $form_data['last_name'],
                    'description' => $form_data['bio']
                ];
                
                $user_id = wp_insert_user($user_data);
                
                if (is_wp_error($user_id)) {
                    $error_message = $user_id->get_error_message();
                } else {
                    // Save additional user meta
                    $meta_fields = [
                        'date_of_birth', 'gender', 'height_feet', 'height_inches', 'height_cm',
                        'current_weight', 'target_weight', 'body_fat_percentage', 'preferred_units',
                        'fitness_level', 'years_training', 'primary_goal', 'workout_frequency',
                        'activity_level', 'protein_percentage', 'carbs_percentage', 'fat_percentage',
                        'dietary_restrictions', 'allergies'
                    ];
                    
                    foreach ($meta_fields as $field) {
                        if (!empty($form_data[$field])) {
                            update_user_meta($user_id, 'liftingtracker_' . $field, $form_data[$field]);
                        }
                    }
                    
                    // Auto-login user
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    
                    // Clear session data
                    unset($_SESSION['registration_data']);
                    
                    // Apply registration redirect filter
                    $redirect_url = apply_filters('liftingtracker_registration_redirect', home_url('/dashboard'), $user_id);
                    wp_redirect($redirect_url);
                    exit;
                }
            } else {
                $error_message = get_step_validation_message($current_step, $form_data);
            }
        }
        
        // Update session data
        $_SESSION['registration_data'] = $form_data;
    }
}

// Validation functions
function liftingtracker_validate_current_step($step, $data) {
    switch ($step) {
        case 1:
            return !empty($data['email']) && 
                   is_email($data['email']) && 
                   !empty($data['password']) && 
                   strlen($data['password']) >= 8 && 
                   $data['password'] === $data['confirm_password'] && 
                   $data['terms_accepted'];
        case 2:
            return !empty($data['first_name']) && 
                   !empty($data['last_name']) && 
                   !empty($data['username']) && 
                   liftingtracker_validate_username($data['username']);
        case 3:
            return true; // Physical attributes are optional
        case 4:
            $total = (int)$data['protein_percentage'] + (int)$data['carbs_percentage'] + (int)$data['fat_percentage'];
            return $total === 100;
        default:
            return false;
    }
}

function liftingtracker_get_step_validation_message($step, $data) {
    switch ($step) {
        case 1:
            if (empty($data['email'])) return 'Email is required';
            if (!is_email($data['email'])) return 'Please enter a valid email address';
            if (empty($data['password'])) return 'Password is required';
            if (strlen($data['password']) < 8) return 'Password must be at least 8 characters';
            if ($data['password'] !== $data['confirm_password']) return 'Passwords do not match';
            if (!$data['terms_accepted']) return 'You must accept the terms and conditions';
            return '';
        case 2:
            if (empty($data['first_name'])) return 'First name is required';
            if (empty($data['last_name'])) return 'Last name is required';
            if (empty($data['username'])) return 'Username is required';
            if (!liftingtracker_validate_username($data['username'])) return 'Username must be 3-20 characters, letters, numbers, and underscores only';
            return '';
        case 4:
            $total = (int)$data['protein_percentage'] + (int)$data['carbs_percentage'] + (int)$data['fat_percentage'];
            if ($total !== 100) return 'Macro percentages must total 100%';
            return '';
        default:
            return '';
    }
}

function liftingtracker_validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Helper functions for step display
function liftingtracker_get_step_title($step) {
    switch ($step) {
        case 1: return 'Create Account';
        case 2: return 'Personal Information';
        case 3: return 'Physical Attributes';
        case 4: return 'Fitness Goals';
        default: return 'Registration';
    }
}

function liftingtracker_get_step_icon($step) {
    switch ($step) {
        case 1: return 'user-plus';
        case 2: return 'user';
        case 3: return 'heart';
        case 4: return 'target';
        default: return 'check';
    }
}

get_header();
?>

<main class="auth-form">
    <div class="auth-container">
        
        <!-- Back Button -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="back-button">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Home
        </a>

        <!-- Header -->
        <div class="auth-header">
            <h1>Create Your Account</h1>
            <p>Complete your profile to get started.</p>
        </div>

        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="flex justify-between items-center mb-4">
                <?php for ($i = 1; $i <= $total_steps; $i++): ?>
                    <div class="flex items-center <?php echo $i < $total_steps ? 'flex-1' : ''; ?>">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 <?php echo $i < $current_step ? 'bg-green-500 border-green-500 text-white' : ($i == $current_step ? 'bg-blue-500 border-blue-500 text-white' : 'border-gray-300 text-gray-400'); ?>">
                            <?php if ($i < $current_step): ?>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            <?php else: ?>
                                <span class="text-sm font-medium"><?php echo $i; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($i < $total_steps): ?>
                            <div class="flex-1 h-1 mx-2 <?php echo $i < $current_step ? 'bg-green-500' : 'bg-gray-200'; ?> rounded-full"></div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            <div class="text-center mb-6">
                <p class="text-sm text-gray-600">
                    Step <?php echo $current_step; ?> of <?php echo $total_steps; ?>
                </p>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="auth-card registration-form">
            <h2 class="text-xl font-semibold text-gray-900 mb-6 text-center">
                <?php echo liftingtracker_get_step_title($current_step); ?>
            </h2>
            
            <form method="post" class="space-y-6" id="registration-form">
                <?php wp_nonce_field('custom_registration_nonce', 'registration_nonce'); ?>
                
                <?php
                // Render step content
                switch ($current_step):
                    case 1:
                        ?>
                        <div class="space-y-4">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       required
                                       value="<?php echo esc_attr($form_data['email']); ?>"
                                       placeholder="name@mail.com"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Password
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="••••••••"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <div id="password-strength" class="mt-2 text-xs text-gray-500">
                                    Password must be at least 8 characters long
                                </div>
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm Password
                                </label>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required
                                       placeholder="••••••••"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <div id="password-match" class="mt-2 text-xs text-gray-500">
                                    Passwords must match
                                </div>
                            </div>
                            <div class="flex items-start">
                                <input type="checkbox" 
                                       id="terms_accepted" 
                                       name="terms_accepted" 
                                       value="1"
                                       <?php checked($form_data['terms_accepted']); ?>
                                       class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-2">
                                <label for="terms_accepted" class="ml-3 text-sm text-gray-600">
                                    I agree to the <a href="#" class="text-blue-600 hover:text-blue-500 font-medium">Terms and Conditions</a>
                                </label>
                            </div>
                            <div id="terms-warning" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-2">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-800">
                                            Please accept the terms and conditions to continue.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        break;
                    
                    case 2:
                        ?>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name
                                    </label>
                                    <input type="text" 
                                           id="first_name" 
                                           name="first_name" 
                                           required
                                           value="<?php echo esc_attr($form_data['first_name']); ?>"
                                           placeholder="John"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name
                                    </label>
                                    <input type="text" 
                                           id="last_name" 
                                           name="last_name" 
                                           required
                                           value="<?php echo esc_attr($form_data['last_name']); ?>"
                                           placeholder="Doe"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                </div>
                            </div>
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                    Username
                                </label>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       required
                                       value="<?php echo esc_attr($form_data['username']); ?>"
                                       placeholder="johndoe"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">
                                        Date of Birth
                                    </label>
                                    <input type="date" 
                                           id="date_of_birth" 
                                           name="date_of_birth" 
                                           value="<?php echo esc_attr($form_data['date_of_birth']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                        Gender
                                    </label>
                                    <select id="gender" 
                                            name="gender"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        <option value="">Select gender</option>
                                        <option value="male" <?php selected($form_data['gender'], 'male'); ?>>Male</option>
                                        <option value="female" <?php selected($form_data['gender'], 'female'); ?>>Female</option>
                                        <option value="other" <?php selected($form_data['gender'], 'other'); ?>>Other</option>
                                        <option value="prefer_not_to_say" <?php selected($form_data['gender'], 'prefer_not_to_say'); ?>>Prefer not to say</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bio (Optional)
                                </label>
                                <textarea id="bio" 
                                          name="bio" 
                                          rows="3"
                                          placeholder="Tell us about yourself..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"><?php echo esc_textarea($form_data['bio']); ?></textarea>
                            </div>
                        </div>
                        <?php
                        break;
                    
                    case 3:
                        ?>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Preferred Units
                                </label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="flex items-center">
                                        <input type="radio" 
                                               name="preferred_units" 
                                               value="imperial" 
                                               <?php checked($form_data['preferred_units'], 'imperial'); ?>
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2">Imperial (lbs, ft)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" 
                                               name="preferred_units" 
                                               value="metric" 
                                               <?php checked($form_data['preferred_units'], 'metric'); ?>
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2">Metric (kg, cm)</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div id="imperial-fields" class="space-y-4" style="display: <?php echo $form_data['preferred_units'] === 'imperial' ? 'block' : 'none'; ?>">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Height</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <input type="number" 
                                                   name="height_feet" 
                                                   placeholder="Feet"
                                                   value="<?php echo esc_attr($form_data['height_feet']); ?>"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        </div>
                                        <div>
                                            <input type="number" 
                                                   name="height_inches" 
                                                   placeholder="Inches"
                                                   value="<?php echo esc_attr($form_data['height_inches']); ?>"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        </div>
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="current_weight" class="block text-sm font-medium text-gray-700 mb-2">
                                        Current Weight (lbs)
                                    </label>
                                    <input type="number" 
                                           id="current_weight" 
                                           name="current_weight" 
                                           placeholder="180"
                                           value="<?php echo esc_attr($form_data['current_weight']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label for="target_weight" class="block text-sm font-medium text-gray-700 mb-2">
                                        Target Weight (lbs)
                                    </label>
                                    <input type="number" 
                                           id="target_weight" 
                                           name="target_weight" 
                                           placeholder="200"
                                           value="<?php echo esc_attr($form_data['target_weight']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                </div>
                            </div>
                            
                            <div id="metric-fields" class="space-y-4" style="display: <?php echo $form_data['preferred_units'] === 'metric' ? 'block' : 'none'; ?>">
                                <div>
                                    <label for="height_cm" class="block text-sm font-medium text-gray-700 mb-2">
                                        Height (cm)
                                    </label>
                                    <input type="number" 
                                           id="height_cm" 
                                           name="height_cm" 
                                           placeholder="175"
                                           value="<?php echo esc_attr($form_data['height_cm']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label for="current_weight_kg" class="block text-sm font-medium text-gray-700 mb-2">
                                        Current Weight (kg)
                                    </label>
                                    <input type="number" 
                                           id="current_weight_kg" 
                                           name="current_weight" 
                                           placeholder="80"
                                           value="<?php echo esc_attr($form_data['current_weight']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label for="target_weight_kg" class="block text-sm font-medium text-gray-700 mb-2">
                                        Target Weight (kg)
                                    </label>
                                    <input type="number" 
                                           id="target_weight_kg" 
                                           name="target_weight" 
                                           placeholder="90"
                                           value="<?php echo esc_attr($form_data['target_weight']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                </div>
                            </div>
                            
                            <div>
                                <label for="body_fat_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                    Body Fat Percentage (Optional)
                                </label>
                                <input type="number" 
                                       id="body_fat_percentage" 
                                       name="body_fat_percentage" 
                                       placeholder="15"
                                       value="<?php echo esc_attr($form_data['body_fat_percentage']); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                        <?php
                        break;
                    
                    case 4:
                        ?>
                        <div class="space-y-4">
                            <div>
                                <label for="fitness_level" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fitness Level
                                </label>
                                <select id="fitness_level" 
                                        name="fitness_level"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="beginner" <?php selected($form_data['fitness_level'], 'beginner'); ?>>Beginner</option>
                                    <option value="intermediate" <?php selected($form_data['fitness_level'], 'intermediate'); ?>>Intermediate</option>
                                    <option value="advanced" <?php selected($form_data['fitness_level'], 'advanced'); ?>>Advanced</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="primary_goal" class="block text-sm font-medium text-gray-700 mb-2">
                                    Primary Goal
                                </label>
                                <select id="primary_goal" 
                                        name="primary_goal"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="build_muscle" <?php selected($form_data['primary_goal'], 'build_muscle'); ?>>Build Muscle</option>
                                    <option value="lose_weight" <?php selected($form_data['primary_goal'], 'lose_weight'); ?>>Lose Weight</option>
                                    <option value="maintain_weight" <?php selected($form_data['primary_goal'], 'maintain_weight'); ?>>Maintain Weight</option>
                                    <option value="increase_strength" <?php selected($form_data['primary_goal'], 'increase_strength'); ?>>Increase Strength</option>
                                    <option value="improve_endurance" <?php selected($form_data['primary_goal'], 'improve_endurance'); ?>>Improve Endurance</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="workout_frequency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Workout Frequency (days per week)
                                </label>
                                <select id="workout_frequency" 
                                        name="workout_frequency"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="2" <?php selected($form_data['workout_frequency'], '2'); ?>>2 days</option>
                                    <option value="3" <?php selected($form_data['workout_frequency'], '3'); ?>>3 days</option>
                                    <option value="4" <?php selected($form_data['workout_frequency'], '4'); ?>>4 days</option>
                                    <option value="5" <?php selected($form_data['workout_frequency'], '5'); ?>>5 days</option>
                                    <option value="6" <?php selected($form_data['workout_frequency'], '6'); ?>>6 days</option>
                                    <option value="7" <?php selected($form_data['workout_frequency'], '7'); ?>>7 days</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-4">
                                    Macro Distribution (must total 100%)
                                </label>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label for="protein_percentage" class="block text-xs text-gray-600 mb-1">Protein %</label>
                                        <input type="number" 
                                               id="protein_percentage" 
                                               name="protein_percentage" 
                                               min="0" 
                                               max="100"
                                               value="<?php echo esc_attr($form_data['protein_percentage']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    </div>
                                    <div>
                                        <label for="carbs_percentage" class="block text-xs text-gray-600 mb-1">Carbs %</label>
                                        <input type="number" 
                                               id="carbs_percentage" 
                                               name="carbs_percentage" 
                                               min="0" 
                                               max="100"
                                               value="<?php echo esc_attr($form_data['carbs_percentage']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    </div>
                                    <div>
                                        <label for="fat_percentage" class="block text-xs text-gray-600 mb-1">Fat %</label>
                                        <input type="number" 
                                               id="fat_percentage" 
                                               name="fat_percentage" 
                                               min="0" 
                                               max="100"
                                               value="<?php echo esc_attr($form_data['fat_percentage']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    </div>
                                </div>
                                <div id="macro-total" class="mt-2 text-sm text-center"></div>
                            </div>
                        </div>
                        <?php
                        break;
                endswitch;
                ?>
                
                <!-- Error Messages -->
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
                
                <!-- Navigation Buttons -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <button type="submit" 
                            name="prev_step"
                            <?php if ($current_step === 1): ?>disabled<?php endif; ?>
                            class="flex items-center gap-2 px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back
                    </button>
                    
                    <div class="flex items-center gap-3">
                        <?php if ($current_step > 1): ?>
                            <button type="button" 
                                    onclick="startOver()"
                                    class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                                Start Over
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($current_step < $total_steps): ?>
                            <button type="submit" 
                                    name="next_step"
                                    id="next-button"
                                    class="flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Next
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        <?php else: ?>
                            <button type="submit" 
                                    name="complete_registration"
                                    class="flex items-center gap-2 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Complete Registration
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Login Link -->
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Already have an account?
                <a href="<?php echo esc_url(wp_login_url()); ?>" 
                   class="font-medium text-black hover:text-gray-700 transition-colors ml-1">
                    Sign in here
                </a>
            </p>
        </div>
    </div>
</main>

<script>
// Handle unit preference toggle
document.querySelectorAll('input[name="preferred_units"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const imperialFields = document.getElementById('imperial-fields');
        const metricFields = document.getElementById('metric-fields');
        
        if (this.value === 'imperial') {
            imperialFields.style.display = 'block';
            metricFields.style.display = 'none';
        } else {
            imperialFields.style.display = 'none';
            metricFields.style.display = 'block';
        }
    });
});

// Terms checkbox validation and button state management
function updateNextButtonState() {
    const currentStep = <?php echo $current_step; ?>;
    const nextButton = document.getElementById('next-button');
    
    if (currentStep === 1 && nextButton) {
        const termsCheckbox = document.getElementById('terms_accepted');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const termsWarning = document.getElementById('terms-warning');
        
        // Check if all required fields are filled and terms are accepted
        const isEmailValid = email && email.value.trim() !== '' && email.validity.valid;
        const isPasswordValid = password && password.value.trim() !== '' && password.value.length >= 8;
        const isConfirmPasswordValid = confirmPassword && confirmPassword.value.trim() !== '' && 
                                     password && password.value === confirmPassword.value;
        const isTermsAccepted = termsCheckbox && termsCheckbox.checked;
        
        const isValid = isEmailValid && isPasswordValid && isConfirmPasswordValid && isTermsAccepted;
        
        // Update button state
        nextButton.disabled = !isValid;
        if (!isValid) {
            nextButton.classList.add('opacity-50', 'cursor-not-allowed');
            nextButton.classList.remove('hover:bg-blue-700');
        } else {
            nextButton.classList.remove('opacity-50', 'cursor-not-allowed');
            nextButton.classList.add('hover:bg-blue-700');
        }
        
        // Show/hide terms warning
        if (termsWarning) {
            if (!isTermsAccepted && (isEmailValid || isPasswordValid || isConfirmPasswordValid)) {
                termsWarning.classList.remove('hidden');
            } else {
                termsWarning.classList.add('hidden');
            }
        }
    }
}

// Real-time validation for step 1
document.addEventListener('DOMContentLoaded', function() {
    const currentStep = <?php echo $current_step; ?>;
    
    if (currentStep === 1) {
        const requiredFields = ['email', 'password', 'confirm_password'];
        const termsCheckbox = document.getElementById('terms_accepted');
        
        // Initial state check
        updateNextButtonState();
        
        // Add event listeners to all required fields
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', updateNextButtonState);
                field.addEventListener('change', updateNextButtonState);
            }
        });
        
        // Terms checkbox listener
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', function() {
                updateNextButtonState();
            });
        }
        
        // Password matching validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordStrength = document.getElementById('password-strength');
        const passwordMatch = document.getElementById('password-match');
        
        function validatePasswordStrength() {
            const passwordValue = password ? password.value : '';
            
            if (passwordStrength) {
                if (passwordValue.length === 0) {
                    passwordStrength.textContent = 'Password must be at least 8 characters long';
                    passwordStrength.className = 'mt-2 text-xs text-gray-500';
                } else if (passwordValue.length < 8) {
                    passwordStrength.textContent = `Password too short (${passwordValue.length}/8 characters)`;
                    passwordStrength.className = 'mt-2 text-xs text-red-500';
                } else {
                    passwordStrength.textContent = 'Password length is good';
                    passwordStrength.className = 'mt-2 text-xs text-green-500';
                }
            }
            
            if (password) {
                if (passwordValue.length >= 8) {
                    password.classList.remove('border-red-500', 'focus:border-red-500');
                    password.classList.add('border-gray-300', 'focus:border-blue-500');
                } else if (passwordValue.length > 0) {
                    password.classList.add('border-red-500', 'focus:border-red-500');
                    password.classList.remove('border-gray-300', 'focus:border-blue-500');
                }
            }
            
            validatePasswordMatch();
            updateNextButtonState();
        }
        
        function validatePasswordMatch() {
            const passwordValue = password ? password.value : '';
            const confirmValue = confirmPassword ? confirmPassword.value : '';
            
            if (passwordMatch) {
                if (confirmValue.length === 0) {
                    passwordMatch.textContent = 'Passwords must match';
                    passwordMatch.className = 'mt-2 text-xs text-gray-500';
                } else if (passwordValue !== confirmValue) {
                    passwordMatch.textContent = 'Passwords do not match';
                    passwordMatch.className = 'mt-2 text-xs text-red-500';
                } else {
                    passwordMatch.textContent = 'Passwords match';
                    passwordMatch.className = 'mt-2 text-xs text-green-500';
                }
            }
            
            if (confirmPassword) {
                if (confirmValue.length > 0 && passwordValue !== confirmValue) {
                    confirmPassword.classList.add('border-red-500', 'focus:border-red-500');
                    confirmPassword.classList.remove('border-gray-300', 'focus:border-blue-500');
                } else {
                    confirmPassword.classList.remove('border-red-500', 'focus:border-red-500');
                    confirmPassword.classList.add('border-gray-300', 'focus:border-blue-500');
                }
            }
            
            updateNextButtonState();
        }
        
        if (password && confirmPassword) {
            password.addEventListener('input', validatePasswordStrength);
            confirmPassword.addEventListener('input', validatePasswordMatch);
        }
    }
});

// Handle macro percentage calculation for step 4
function updateMacroTotal() {
    const protein = parseInt(document.getElementById('protein_percentage').value) || 0;
    const carbs = parseInt(document.getElementById('carbs_percentage').value) || 0;
    const fat = parseInt(document.getElementById('fat_percentage').value) || 0;
    const total = protein + carbs + fat;
    
    const totalElement = document.getElementById('macro-total');
    if (totalElement) {
        totalElement.textContent = `Total: ${total}%`;
        
        if (total === 100) {
            totalElement.className = 'mt-2 text-sm text-center text-green-600 font-medium';
        } else {
            totalElement.className = 'mt-2 text-sm text-center text-red-600 font-medium';
        }
    }
}

// Add event listeners for macro inputs if on step 4
<?php if ($current_step === 4): ?>
document.addEventListener('DOMContentLoaded', function() {
    ['protein_percentage', 'carbs_percentage', 'fat_percentage'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateMacroTotal);
        }
    });
    updateMacroTotal(); // Initial calculation
});
<?php endif; ?>

// Start over function
function startOver() {
    if (confirm('Are you sure you want to start over? All progress will be lost.')) {
        window.location.href = '<?php echo esc_url(add_query_arg('start_over', '1')); ?>';
    }
}

// Form validation on submit
document.getElementById('registration-form').addEventListener('submit', function(e) {
    const currentStep = <?php echo $current_step; ?>;
    
    if (currentStep === 1) {
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const termsAccepted = document.getElementById('terms_accepted');
        
        if (!email.value || !password.value || !confirmPassword.value || !termsAccepted.checked) {
            e.preventDefault();
            alert('Please fill in all required fields and accept the terms and conditions.');
            return false;
        }
        
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Passwords do not match.');
            return false;
        }
        
        if (password.value.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            return false;
        }
    }
    
    if (currentStep === 4) {
        const protein = parseInt(document.getElementById('protein_percentage').value) || 0;
        const carbs = parseInt(document.getElementById('carbs_percentage').value) || 0;
        const fat = parseInt(document.getElementById('fat_percentage').value) || 0;
        
        if (protein + carbs + fat !== 100) {
            e.preventDefault();
            alert('Macro percentages must total 100%.');
            return false;
        }
    }
});
</script>

<?php
get_footer();
?>
