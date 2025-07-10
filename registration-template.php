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
            if (validate_current_step($current_step, $form_data)) {
                $current_step = min($current_step + 1, $total_steps);
                $form_data['current_step'] = $current_step;
            } else {
                $error_message = get_step_validation_message($current_step, $form_data);
            }
        } elseif (isset($_POST['prev_step'])) {
            $current_step = max($current_step - 1, 1);
            $form_data['current_step'] = $current_step;
        } elseif (isset($_POST['complete_registration'])) {
            if (validate_current_step($current_step, $form_data)) {
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
function validate_current_step($step, $data) {
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
                   validate_username($data['username']);
        case 3:
            return true; // Physical attributes are optional
        case 4:
            $total = (int)$data['protein_percentage'] + (int)$data['carbs_percentage'] + (int)$data['fat_percentage'];
            return $total === 100;
        default:
            return false;
    }
}

function get_step_validation_message($step, $data) {
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
            if (!validate_username($data['username'])) return 'Username must be 3-20 characters, letters, numbers, and underscores only';
            return '';
        case 4:
            $total = (int)$data['protein_percentage'] + (int)$data['carbs_percentage'] + (int)$data['fat_percentage'];
            if ($total !== 100) return 'Macro percentages must total 100%';
            return '';
        default:
            return '';
    }
}

function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Helper functions for step display
function get_step_title($step) {
    switch ($step) {
        case 1: return 'Create Account';
        case 2: return 'Personal Information';
        case 3: return 'Physical Attributes';
        case 4: return 'Fitness Goals';
        default: return 'Registration';
    }
}

function get_step_icon($step) {
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

<main class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="w-full max-w-md">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?php echo esc_url(home_url('/')); ?>" 
               class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Create Your Account</h1>
            <p class="text-lg text-gray-600">Complete your profile to get started.</p>
        </div>

        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <?php for ($i = 1; $i <= $total_steps; $i++): ?>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                            <?php echo $i < $current_step ? 'bg-green-600 text-white' : 
                                     ($i == $current_step ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'); ?>">
                            <?php if ($i < $current_step): ?>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            <?php else: ?>
                                <?php echo $i; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($i < $total_steps): ?>
                            <div class="w-12 h-0.5 <?php echo $i < $current_step ? 'bg-green-600' : 'bg-gray-200'; ?>"></div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                     style="width: <?php echo ($current_step / $total_steps) * 100; ?>%"></div>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="bg-white rounded-xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6 text-center">
                <?php echo get_step_title($current_step); ?>
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
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="terms_accepted" 
                                       name="terms_accepted" 
                                       value="1"
                                       <?php checked($form_data['terms_accepted']); ?>
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <label for="terms_accepted" class="ml-2 text-sm text-gray-600">
                                    I agree to the <a href="#" class="text-blue-600 hover:text-blue-500">Terms and Conditions</a>
                                </label>
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
                <div class="flex justify-between items-center">
                    <button type="submit" 
                            name="prev_step"
                            <?php if ($current_step === 1): ?>disabled<?php endif; ?>
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back
                    </button>
                    
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
                                class="flex items-center gap-2 bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                            Next
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    <?php else: ?>
                        <button type="submit" 
                                name="complete_registration"
                                class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            Complete Registration
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    <?php endif; ?>
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

// Handle macro percentage calculation
function updateMacroTotal() {
    const protein = parseInt(document.getElementById('protein_percentage').value) || 0;
    const carbs = parseInt(document.getElementById('carbs_percentage').value) || 0;
    const fat = parseInt(document.getElementById('fat_percentage').value) || 0;
    const total = protein + carbs + fat;
    
    const totalElement = document.getElementById('macro-total');
    totalElement.textContent = `Total: ${total}%`;
    
    if (total === 100) {
        totalElement.className = 'mt-2 text-sm text-center text-green-600';
    } else {
        totalElement.className = 'mt-2 text-sm text-center text-red-600';
    }
}

// Add event listeners for macro inputs
['protein_percentage', 'carbs_percentage', 'fat_percentage'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('input', updateMacroTotal);
    }
});

// Initialize macro total
updateMacroTotal();

// Start over function
function startOver() {
    if (confirm('Are you sure you want to start over? All progress will be lost.')) {
        window.location.href = '<?php echo esc_url(add_query_arg('start_over', '1')); ?>';
    }
}

// Form validation
document.getElementById('registration-form').addEventListener('submit', function(e) {
    const currentStep = <?php echo $current_step; ?>;
    
    if (currentStep === 1) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const termsAccepted = document.getElementById('terms_accepted').checked;
        
        if (!email || !password || !confirmPassword || !termsAccepted) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match.');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            return false;
        }
    }
    
    if (currentStep === 4) {
        const total = parseInt(document.getElementById('protein_percentage').value) || 0;
        const carbs = parseInt(document.getElementById('carbs_percentage').value) || 0;
        const fat = parseInt(document.getElementById('fat_percentage').value) || 0;
        
        if (total + carbs + fat !== 100) {
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
