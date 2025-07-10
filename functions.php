<?php
/**
 * LiftingTracker Pro Theme Functions
 * 
 * @package LiftingTrackerPro
 * @version 1.0.0
 */

/** 
 * Prevent direct access
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function liftingtracker_pro_setup() {
    // Add theme support.
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    add_theme_support('custom-logo');
    add_theme_support('customize-selective-refresh-widgets');
    
    // Register navigation menus.
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'liftingtracker-pro'),
        'footer' => __('Footer Menu', 'liftingtracker-pro'),
    ));
}
add_action('after_setup_theme', 'liftingtracker_pro_setup');

/**
 * Enqueue scripts and styles
 */
function liftingtracker_pro_scripts() {
    // Check if we're in development mode.
    $is_development = defined('WP_DEBUG') && WP_DEBUG;
    
    // Main stylesheet (compiled from SCSS)
    wp_enqueue_style(
        'liftingtracker-pro-style',
        get_template_directory_uri() . '/build/main.css',
        array(),
        $is_development ? time() : '1.0.0'
    );
    
    // Material Icons
    wp_enqueue_style(
        'material-icons',
        'https://fonts.googleapis.com/icon?family=Material+Icons',
        array(),
        '1.0.0'
    );
    
    // Google Fonts
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap',
        array(),
        '1.0.0'
    );
    
    // Main JavaScript (compiled and bundled)
    wp_enqueue_script(
        'liftingtracker-pro-script',
        get_template_directory_uri() . '/build/main.js',
        array(),
        $is_development ? time() : '1.0.0',
        true
    );
    
    // Localize script for AJAX
    wp_localize_script('liftingtracker-pro-script', 'liftingtracker_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('liftingtracker_nonce'),
        'api_url' => home_url('/wp-json/wp/v2/'),
        'current_user_id' => get_current_user_id(),
        'is_user_logged_in' => is_user_logged_in(),
    ));
}
add_action('wp_enqueue_scripts', 'liftingtracker_pro_scripts');

// Register Custom Post Types
function liftingtracker_pro_custom_post_types() {
    
    // Workouts Post Type
    register_post_type('workout', array(
        'labels' => array(
            'name' => 'Workouts',
            'singular_name' => 'Workout',
            'add_new' => 'Add New Workout',
            'add_new_item' => 'Add New Workout',
            'edit_item' => 'Edit Workout',
            'new_item' => 'New Workout',
            'view_item' => 'View Workout',
            'search_items' => 'Search Workouts',
            'not_found' => 'No workouts found',
            'not_found_in_trash' => 'No workouts found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-heart',
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'show_in_rest' => true,
    ));
    
    // Exercises Post Type
    register_post_type('exercise', array(
        'labels' => array(
            'name' => 'Exercises',
            'singular_name' => 'Exercise',
            'add_new' => 'Add New Exercise',
            'add_new_item' => 'Add New Exercise',
            'edit_item' => 'Edit Exercise',
            'new_item' => 'New Exercise',
            'view_item' => 'View Exercise',
            'search_items' => 'Search Exercises',
            'not_found' => 'No exercises found',
            'not_found_in_trash' => 'No exercises found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-universal-access-alt',
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'liftingtracker_pro_custom_post_types');

// Register Custom Taxonomies
function liftingtracker_pro_custom_taxonomies() {
    
    // Exercise Categories
    register_taxonomy('exercise_category', 'exercise', array(
        'labels' => array(
            'name' => 'Exercise Categories',
            'singular_name' => 'Exercise Category',
            'search_items' => 'Search Exercise Categories',
            'all_items' => 'All Exercise Categories',
            'edit_item' => 'Edit Exercise Category',
            'update_item' => 'Update Exercise Category',
            'add_new_item' => 'Add New Exercise Category',
            'new_item_name' => 'New Exercise Category Name',
            'menu_name' => 'Categories',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'show_in_rest' => true,
    ));
    
    // Muscle Groups
    register_taxonomy('muscle_group', 'exercise', array(
        'labels' => array(
            'name' => 'Muscle Groups',
            'singular_name' => 'Muscle Group',
            'search_items' => 'Search Muscle Groups',
            'all_items' => 'All Muscle Groups',
            'edit_item' => 'Edit Muscle Group',
            'update_item' => 'Update Muscle Group',
            'add_new_item' => 'Add New Muscle Group',
            'new_item_name' => 'New Muscle Group Name',
            'menu_name' => 'Muscle Groups',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'liftingtracker_pro_custom_taxonomies');

// Add Custom Fields Support
function liftingtracker_pro_add_meta_boxes() {
    add_meta_box(
        'workout_details',
        'Workout Details',
        'liftingtracker_pro_workout_meta_box',
        'workout',
        'normal',
        'high'
    );
    
    add_meta_box(
        'exercise_details',
        'Exercise Details',
        'liftingtracker_pro_exercise_meta_box',
        'exercise',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'liftingtracker_pro_add_meta_boxes');

// Workout Meta Box
function liftingtracker_pro_workout_meta_box($post) {
    wp_nonce_field('liftingtracker_pro_workout_meta', 'liftingtracker_pro_workout_nonce');
    
    $duration = get_post_meta($post->ID, '_workout_duration', true);
    $calories = get_post_meta($post->ID, '_calories_burned', true);
    $notes = get_post_meta($post->ID, '_workout_notes', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="workout_duration">Duration (minutes)</label></th>
            <td><input type="number" id="workout_duration" name="workout_duration" value="<?php echo esc_attr($duration); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="calories_burned">Calories Burned</label></th>
            <td><input type="number" id="calories_burned" name="calories_burned" value="<?php echo esc_attr($calories); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="workout_notes">Notes</label></th>
            <td><textarea id="workout_notes" name="workout_notes" rows="4" class="large-text"><?php echo esc_textarea($notes); ?></textarea></td>
        </tr>
    </table>
    <?php
}

// Exercise Meta Box
function liftingtracker_pro_exercise_meta_box($post) {
    wp_nonce_field('liftingtracker_pro_exercise_meta', 'liftingtracker_pro_exercise_nonce');
    
    $sets = get_post_meta($post->ID, '_exercise_sets', true);
    $reps = get_post_meta($post->ID, '_exercise_reps', true);
    $weight = get_post_meta($post->ID, '_exercise_weight', true);
    $instructions = get_post_meta($post->ID, '_exercise_instructions', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="exercise_sets">Sets</label></th>
            <td><input type="number" id="exercise_sets" name="exercise_sets" value="<?php echo esc_attr($sets); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="exercise_reps">Reps</label></th>
            <td><input type="number" id="exercise_reps" name="exercise_reps" value="<?php echo esc_attr($reps); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="exercise_weight">Weight (lbs)</label></th>
            <td><input type="number" id="exercise_weight" name="exercise_weight" value="<?php echo esc_attr($weight); ?>" step="0.5" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="exercise_instructions">Instructions</label></th>
            <td><textarea id="exercise_instructions" name="exercise_instructions" rows="4" class="large-text"><?php echo esc_textarea($instructions); ?></textarea></td>
        </tr>
    </table>
    <?php
}

// Save Meta Box Data
function liftingtracker_pro_save_meta_boxes($post_id) {
    // Check if user has permission
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save workout meta
    if (isset($_POST['liftingtracker_pro_workout_nonce']) && wp_verify_nonce($_POST['liftingtracker_pro_workout_nonce'], 'liftingtracker_pro_workout_meta')) {
        if (isset($_POST['workout_duration'])) {
            update_post_meta($post_id, '_workout_duration', sanitize_text_field($_POST['workout_duration']));
        }
        if (isset($_POST['calories_burned'])) {
            update_post_meta($post_id, '_calories_burned', sanitize_text_field($_POST['calories_burned']));
        }
        if (isset($_POST['workout_notes'])) {
            update_post_meta($post_id, '_workout_notes', sanitize_textarea_field($_POST['workout_notes']));
        }
    }
    
    // Save exercise meta
    if (isset($_POST['liftingtracker_pro_exercise_nonce']) && wp_verify_nonce($_POST['liftingtracker_pro_exercise_nonce'], 'liftingtracker_pro_exercise_meta')) {
        if (isset($_POST['exercise_sets'])) {
            update_post_meta($post_id, '_exercise_sets', sanitize_text_field($_POST['exercise_sets']));
        }
        if (isset($_POST['exercise_reps'])) {
            update_post_meta($post_id, '_exercise_reps', sanitize_text_field($_POST['exercise_reps']));
        }
        if (isset($_POST['exercise_weight'])) {
            update_post_meta($post_id, '_exercise_weight', sanitize_text_field($_POST['exercise_weight']));
        }
        if (isset($_POST['exercise_instructions'])) {
            update_post_meta($post_id, '_exercise_instructions', sanitize_textarea_field($_POST['exercise_instructions']));
        }
    }
}
add_action('save_post', 'liftingtracker_pro_save_meta_boxes');

// Include Stripe Integration
require_once get_template_directory() . '/includes/stripe-integration.php';

// Include User Dashboard
require_once get_template_directory() . '/includes/user-dashboard.php';

// Include Custom Widgets
require_once get_template_directory() . '/includes/widgets.php';

/**
 * Custom Login and Registration System
 */

/**
 * Replace default WordPress login page
 */
function liftingtracker_custom_login_page() {
    $action = isset($_GET['action']) ? $_GET['action'] : 'login';
    
    if ($action === 'register') {
        // Load custom registration template
        get_template_part('registration-template');
        exit;
    } else {
        // Load custom login template
        get_template_part('login-template');
        exit;
    }
}

/**
 * Redirect login URL to custom template
 */
function liftingtracker_login_url($login_url, $redirect, $force_relogin) {
    if (!is_admin() && !$force_relogin) {
        return home_url('/login');
    }
    return $login_url;
}
add_filter('login_url', 'liftingtracker_login_url', 10, 3);

/**
 * Redirect registration URL to custom template
 */
function liftingtracker_registration_url($registration_url) {
    if (!is_admin()) {
        return home_url('/register');
    }
    return $registration_url;
}
add_filter('register_url', 'liftingtracker_registration_url');

/**
 * Add custom rewrite rules for login/registration
 */
function liftingtracker_custom_rewrite_rules() {
    add_rewrite_rule('^login/?$', 'index.php?custom_login=1', 'top');
    add_rewrite_rule('^register/?$', 'index.php?custom_register=1', 'top');
}
add_action('init', 'liftingtracker_custom_rewrite_rules');

/**
 * Add custom query vars
 */
function liftingtracker_custom_query_vars($vars) {
    $vars[] = 'custom_login';
    $vars[] = 'custom_register';
    return $vars;
}
add_filter('query_vars', 'liftingtracker_custom_query_vars');

/**
 * Template redirect for custom login/registration
 */
function liftingtracker_template_redirect() {
    if (get_query_var('custom_login') || get_query_var('custom_register')) {
        liftingtracker_custom_login_page();
    }
}
add_action('template_redirect', 'liftingtracker_template_redirect');

/**
 * Customize login redirect
 */
function liftingtracker_login_redirect($redirect_to, $request, $user) {
    // Check if user has errors
    if (isset($user->errors) && !empty($user->errors)) {
        return home_url('/login?error=1');
    }
    
    // Default redirect for successful login
    if (empty($redirect_to) || $redirect_to === 'wp-admin/' || $redirect_to === admin_url()) {
        return home_url('/dashboard');
    }
    
    return $redirect_to;
}
add_filter('login_redirect', 'liftingtracker_login_redirect', 10, 3);

/**
 * Customize registration redirect
 */
function liftingtracker_registration_redirect($redirect_to, $user_id) {
    return home_url('/dashboard');
}
add_filter('liftingtracker_registration_redirect', 'liftingtracker_registration_redirect', 10, 2);

/**
 * Hide admin bar for non-admin users
 */
function liftingtracker_hide_admin_bar() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'liftingtracker_hide_admin_bar');

/**
 * Customize login form styling
 */
function liftingtracker_login_form_style() {
    ?>
    <style>
        .login-form-custom {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-form-container {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 400px;
            width: 100%;
        }
        
        .login-form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-form-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .login-form-header p {
            color: #6b7280;
            font-size: 1.125rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary {
            width: 100%;
            background-color: #1f2937;
            color: white;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }
        
        .btn-primary:hover {
            background-color: #374151;
        }
        
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        .success-message {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
    </style>
    <?php
}
add_action('liftingtracker_login_head', 'liftingtracker_login_form_style');

/**
 * Add custom user meta fields
 */
function liftingtracker_add_custom_user_meta_fields($user) {
    $user_id = $user->ID;
    ?>
    <h3><?php _e('Fitness Profile', 'liftingtracker-pro'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="fitness_level"><?php _e('Fitness Level', 'liftingtracker-pro'); ?></label></th>
            <td>
                <select name="fitness_level" id="fitness_level">
                    <option value="beginner" <?php selected(get_user_meta($user_id, 'liftingtracker_fitness_level', true), 'beginner'); ?>>Beginner</option>
                    <option value="intermediate" <?php selected(get_user_meta($user_id, 'liftingtracker_fitness_level', true), 'intermediate'); ?>>Intermediate</option>
                    <option value="advanced" <?php selected(get_user_meta($user_id, 'liftingtracker_fitness_level', true), 'advanced'); ?>>Advanced</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="primary_goal"><?php _e('Primary Goal', 'liftingtracker-pro'); ?></label></th>
            <td>
                <select name="primary_goal" id="primary_goal">
                    <option value="build_muscle" <?php selected(get_user_meta($user_id, 'liftingtracker_primary_goal', true), 'build_muscle'); ?>>Build Muscle</option>
                    <option value="lose_weight" <?php selected(get_user_meta($user_id, 'liftingtracker_primary_goal', true), 'lose_weight'); ?>>Lose Weight</option>
                    <option value="maintain_weight" <?php selected(get_user_meta($user_id, 'liftingtracker_primary_goal', true), 'maintain_weight'); ?>>Maintain Weight</option>
                    <option value="increase_strength" <?php selected(get_user_meta($user_id, 'liftingtracker_primary_goal', true), 'increase_strength'); ?>>Increase Strength</option>
                    <option value="improve_endurance" <?php selected(get_user_meta($user_id, 'liftingtracker_primary_goal', true), 'improve_endurance'); ?>>Improve Endurance</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="current_weight"><?php _e('Current Weight', 'liftingtracker-pro'); ?></label></th>
            <td>
                <input type="number" name="current_weight" id="current_weight" value="<?php echo esc_attr(get_user_meta($user_id, 'liftingtracker_current_weight', true)); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="target_weight"><?php _e('Target Weight', 'liftingtracker-pro'); ?></label></th>
            <td>
                <input type="number" name="target_weight" id="target_weight" value="<?php echo esc_attr(get_user_meta($user_id, 'liftingtracker_target_weight', true)); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'liftingtracker_add_custom_user_meta_fields');
add_action('edit_user_profile', 'liftingtracker_add_custom_user_meta_fields');

/**
 * Save custom user meta fields
 */
function liftingtracker_save_custom_user_meta_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    $fields = [
        'fitness_level', 'primary_goal', 'current_weight', 'target_weight',
        'height_feet', 'height_inches', 'height_cm', 'body_fat_percentage',
        'preferred_units', 'years_training', 'workout_frequency', 'activity_level',
        'protein_percentage', 'carbs_percentage', 'fat_percentage',
        'dietary_restrictions', 'allergies', 'date_of_birth', 'gender'
    ];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, 'liftingtracker_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('personal_options_update', 'liftingtracker_save_custom_user_meta_fields');
add_action('edit_user_profile_update', 'liftingtracker_save_custom_user_meta_fields');

/**
 * Get user fitness data
 */
function liftingtracker_get_user_fitness_data($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $fitness_data = [];
    $fields = [
        'fitness_level', 'primary_goal', 'current_weight', 'target_weight',
        'height_feet', 'height_inches', 'height_cm', 'body_fat_percentage',
        'preferred_units', 'years_training', 'workout_frequency', 'activity_level',
        'protein_percentage', 'carbs_percentage', 'fat_percentage',
        'dietary_restrictions', 'allergies', 'date_of_birth', 'gender'
    ];
    
    foreach ($fields as $field) {
        $fitness_data[$field] = get_user_meta($user_id, 'liftingtracker_' . $field, true);
    }
    
    return $fitness_data;
}

/**
 * Check if user has completed fitness profile
 */
function liftingtracker_user_has_complete_profile($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $required_fields = ['fitness_level', 'primary_goal', 'current_weight'];
    
    foreach ($required_fields as $field) {
        $value = get_user_meta($user_id, 'liftingtracker_' . $field, true);
        if (empty($value)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Disable default WordPress login/registration emails
 */
function liftingtracker_disable_default_emails() {
    // Disable new user notification to admin
    remove_action('register_new_user', 'wp_send_new_user_notifications');
    remove_action('edit_user_created_user', 'wp_send_new_user_notifications');
    
    // Custom new user notification
    add_action('liftingtracker_user_registered', 'liftingtracker_send_welcome_email');
}
add_action('init', 'liftingtracker_disable_default_emails');

/**
 * Send custom welcome email
 */
function liftingtracker_send_welcome_email($user_id) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return;
    }
    
    $subject = __('Welcome to LiftingTracker Pro!', 'liftingtracker-pro');
    $message = sprintf(
        __('Hi %s,

Welcome to LiftingTracker Pro! We\'re excited to have you on board.

Your account has been created successfully. You can now:
- Track your workouts
- Monitor your progress
- Set and achieve your fitness goals
- Access your personalized dashboard

Get started: %s

If you have any questions, feel free to reach out to our support team.

Best regards,
The LiftingTracker Team', 'liftingtracker-pro'),
        $user->display_name,
        home_url('/dashboard')
    );
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    wp_mail($user->user_email, $subject, nl2br($message), $headers);
}

/**
 * Add login/logout menu items
 */
function liftingtracker_add_login_logout_menu_items($items, $args) {
    if ($args->theme_location === 'primary') {
        if (is_user_logged_in()) {
            $items .= '<li class="menu-item"><a href="' . esc_url(wp_logout_url()) . '">' . __('Logout', 'liftingtracker-pro') . '</a></li>';
        } else {
            $items .= '<li class="menu-item"><a href="' . esc_url(home_url('/login')) . '">' . __('Login', 'liftingtracker-pro') . '</a></li>';
            $items .= '<li class="menu-item"><a href="' . esc_url(home_url('/register')) . '">' . __('Register', 'liftingtracker-pro') . '</a></li>';
        }
    }
    
    return $items;
}
add_filter('wp_nav_menu_items', 'liftingtracker_add_login_logout_menu_items', 10, 2);

/**
 * Show social login options
 */
function liftingtracker_show_social_login_filter($show) {
    // You can add conditions here to show/hide social login options
    return apply_filters('liftingtracker_enable_social_login', true);
}
add_filter('liftingtracker_show_social_login', 'liftingtracker_show_social_login_filter');
