<?php
/**
 * User Dashboard functionality for LiftingTracker Pro
 * 
 * @package LiftingTrackerPro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LiftingTracker_User_Dashboard {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Add dashboard page
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'dashboard_template_redirect'));
        
        // AJAX handlers for dashboard functionality
        add_action('wp_ajax_liftingtracker_save_workout', array($this, 'save_workout'));
        add_action('wp_ajax_liftingtracker_delete_workout', array($this, 'delete_workout'));
        add_action('wp_ajax_liftingtracker_get_progress_data', array($this, 'get_progress_data'));
        
        // Enqueue dashboard scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_scripts'));
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule('^dashboard/?$', 'index.php?dashboard=home', 'top');
        add_rewrite_rule('^dashboard/workouts/?$', 'index.php?dashboard=workouts', 'top');
        add_rewrite_rule('^dashboard/progress/?$', 'index.php?dashboard=progress', 'top');
        add_rewrite_rule('^dashboard/settings/?$', 'index.php?dashboard=settings', 'top');
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'dashboard';
        return $vars;
    }
    
    public function dashboard_template_redirect() {
        $dashboard_page = get_query_var('dashboard');
        
        if ($dashboard_page) {
            // Check if user is logged in
            if (!is_user_logged_in()) {
                wp_redirect(wp_login_url(home_url('/dashboard')));
                exit;
            }
            
            // Check if user has active subscription (except for settings page)
            if ($dashboard_page !== 'settings' && !liftingtracker_user_has_subscription()) {
                wp_redirect(home_url('/subscription-required'));
                exit;
            }
            
            // Load appropriate template
            switch ($dashboard_page) {
                case 'workouts':
                    $this->load_template('dashboard-workouts.php');
                    break;
                case 'progress':
                    $this->load_template('dashboard-progress.php');
                    break;
                case 'settings':
                    $this->load_template('dashboard-settings.php');
                    break;
                default:
                    $this->load_template('dashboard-home.php');
                    break;
            }
            exit;
        }
    }
    
    private function load_template($template) {
        $template_path = get_template_directory() . '/templates/' . $template;
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback to default dashboard
            include get_template_directory() . '/templates/dashboard-home.php';
        }
    }
    
    public function enqueue_dashboard_scripts() {
        if (get_query_var('dashboard')) {
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
            wp_enqueue_script('liftingtracker-dashboard', get_template_directory_uri() . '/assets/js/dashboard.js', array('jquery', 'chart-js'), '1.0.0', true);
            
            wp_localize_script('liftingtracker-dashboard', 'liftingtracker_dashboard', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('liftingtracker_dashboard_nonce'),
            ));
        }
    }
    
    public function save_workout() {
        // Verify nonce and user permissions
        if (!wp_verify_nonce($_POST['nonce'], 'liftingtracker_dashboard_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        $user_id = get_current_user_id();
        $workout_data = wp_unslash($_POST['workout_data']);
        
        // Create workout post
        $workout_id = wp_insert_post(array(
            'post_title' => sanitize_text_field($workout_data['title']),
            'post_content' => sanitize_textarea_field($workout_data['notes']),
            'post_status' => 'publish',
            'post_type' => 'workout',
            'post_author' => $user_id,
        ));
        
        if ($workout_id && !is_wp_error($workout_id)) {
            // Save workout metadata
            update_post_meta($workout_id, '_workout_date', sanitize_text_field($workout_data['date']));
            update_post_meta($workout_id, '_workout_duration', intval($workout_data['duration']));
            update_post_meta($workout_id, '_calories_burned', intval($workout_data['calories']));
            
            // Save exercises data
            if (isset($workout_data['exercises']) && is_array($workout_data['exercises'])) {
                update_post_meta($workout_id, '_workout_exercises', $workout_data['exercises']);
            }
            
            wp_send_json_success(array(
                'workout_id' => $workout_id,
                'message' => 'Workout saved successfully',
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Failed to save workout',
            ));
        }
    }
    
    public function delete_workout() {
        // Verify nonce and user permissions
        if (!wp_verify_nonce($_POST['nonce'], 'liftingtracker_dashboard_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        $workout_id = intval($_POST['workout_id']);
        $user_id = get_current_user_id();
        
        // Check if user owns this workout
        $workout = get_post($workout_id);
        if ($workout && $workout->post_author == $user_id && $workout->post_type === 'workout') {
            wp_delete_post($workout_id, true);
            
            wp_send_json_success(array(
                'message' => 'Workout deleted successfully',
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Permission denied or workout not found',
            ));
        }
    }
    
    public function get_progress_data() {
        // Verify nonce and user permissions
        if (!wp_verify_nonce($_POST['nonce'], 'liftingtracker_dashboard_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        $user_id = get_current_user_id();
        $exercise_name = sanitize_text_field($_POST['exercise_name']);
        $period = sanitize_text_field($_POST['period']); // 'week', 'month', 'year'
        
        // Calculate date range
        $end_date = current_time('Y-m-d');
        switch ($period) {
            case 'week':
                $start_date = date('Y-m-d', strtotime('-1 week'));
                break;
            case 'month':
                $start_date = date('Y-m-d', strtotime('-1 month'));
                break;
            case 'year':
                $start_date = date('Y-m-d', strtotime('-1 year'));
                break;
            default:
                $start_date = date('Y-m-d', strtotime('-1 month'));
        }
        
        // Query workouts with the specific exercise
        $workouts = get_posts(array(
            'post_type' => 'workout',
            'author' => $user_id,
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_workout_date',
                    'value' => array($start_date, $end_date),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_workout_date',
            'order' => 'ASC',
        ));
        
        $progress_data = array();
        
        foreach ($workouts as $workout) {
            $exercises = get_post_meta($workout->ID, '_workout_exercises', true);
            if (is_array($exercises)) {
                foreach ($exercises as $exercise) {
                    if (isset($exercise['name']) && $exercise['name'] === $exercise_name) {
                        $workout_date = get_post_meta($workout->ID, '_workout_date', true);
                        $progress_data[] = array(
                            'date' => $workout_date,
                            'weight' => floatval($exercise['weight']),
                            'reps' => intval($exercise['reps']),
                            'sets' => intval($exercise['sets']),
                        );
                    }
                }
            }
        }
        
        wp_send_json_success($progress_data);
    }
}

// Initialize the dashboard
new LiftingTracker_User_Dashboard();
?>
