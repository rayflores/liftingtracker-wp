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
?>
