<?php
/**
 * Custom Widgets for LiftingTracker Pro
 * 
 * @package LiftingTrackerPro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recent Workouts Widget
 */
class LiftingTracker_Recent_Workouts_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'liftingtracker_recent_workouts',
            __('LiftingTracker: Recent Workouts', 'liftingtracker-pro'),
            array('description' => __('Display recent workouts', 'liftingtracker-pro'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $workouts = get_posts(array(
            'post_type' => 'workout',
            'posts_per_page' => $instance['number'] ?: 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if ($workouts) {
            echo '<ul class="recent-workouts-widget">';
            foreach ($workouts as $workout) {
                $date = get_post_meta($workout->ID, '_workout_date', true);
                echo '<li>';
                echo '<a href="' . get_permalink($workout->ID) . '">' . esc_html($workout->post_title) . '</a>';
                if ($date) {
                    echo '<span class="workout-date">' . esc_html($date) . '</span>';
                }
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No workouts found.</p>';
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Recent Workouts', 'liftingtracker-pro');
        $number = !empty($instance['number']) ? $instance['number'] : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Number of workouts to show:'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        return $instance;
    }
}

/**
 * Fitness Stats Widget
 */
class LiftingTracker_Stats_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'liftingtracker_stats',
            __('LiftingTracker: Fitness Stats', 'liftingtracker-pro'),
            array('description' => __('Display fitness statistics', 'liftingtracker-pro'))
        );
    }
    
    public function widget($args, $instance) {
        if (!is_user_logged_in()) {
            return;
        }
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $user_id = get_current_user_id();
        
        // Get workout stats
        $total_workouts = get_posts(array(
            'post_type' => 'workout',
            'author' => $user_id,
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        $this_month = get_posts(array(
            'post_type' => 'workout',
            'author' => $user_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => array(
                array(
                    'year' => date('Y'),
                    'month' => date('n'),
                ),
            ),
        ));
        
        ?>
        <div class="fitness-stats-widget">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($total_workouts); ?></span>
                <span class="stat-label">Total Workouts</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count($this_month); ?></span>
                <span class="stat-label">This Month</span>
            </div>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('My Fitness Stats', 'liftingtracker-pro');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        return $instance;
    }
}

// Register widgets
function liftingtracker_pro_register_widgets() {
    register_widget('LiftingTracker_Recent_Workouts_Widget');
    register_widget('LiftingTracker_Stats_Widget');
}
add_action('widgets_init', 'liftingtracker_pro_register_widgets');

// Widget styles
function liftingtracker_pro_widget_styles() {
    ?>
    <style>
    .recent-workouts-widget {
        list-style: none;
        padding: 0;
    }
    .recent-workouts-widget li {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .recent-workouts-widget li:last-child {
        border-bottom: none;
    }
    .recent-workouts-widget .workout-date {
        display: block;
        font-size: 0.8em;
        color: #666;
        margin-top: 4px;
    }
    .fitness-stats-widget {
        display: flex;
        justify-content: space-around;
    }
    .fitness-stats-widget .stat-item {
        text-align: center;
    }
    .fitness-stats-widget .stat-number {
        display: block;
        font-size: 2em;
        font-weight: bold;
        color: #1976d2;
    }
    .fitness-stats-widget .stat-label {
        display: block;
        font-size: 0.8em;
        color: #666;
    }
    </style>
    <?php
}
add_action('wp_head', 'liftingtracker_pro_widget_styles');
?>
