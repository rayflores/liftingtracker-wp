<?php
/**
 * Dashboard Home Template
 * 
 * @package LiftingTrackerPro
 */

get_header(); 

$current_user = wp_get_current_user();
$user_id = get_current_user_id();

// Get recent workouts
$recent_workouts = get_posts(array(
    'post_type' => 'workout',
    'author' => $user_id,
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
));

// Get workout stats
$total_workouts = wp_count_posts('workout')->publish;
$this_month_workouts = get_posts(array(
    'post_type' => 'workout',
    'author' => $user_id,
    'posts_per_page' => -1,
    'date_query' => array(
        array(
            'year' => date('Y'),
            'month' => date('n'),
        ),
    ),
));
?>

<main class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                Welcome back, <?php echo esc_html($current_user->display_name); ?>!
            </h1>
            <p class="text-gray-600">Here's your fitness overview for today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Workouts -->
            <div class="material-card text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="material-icons text-blue-600 text-2xl">fitness_center</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo count($recent_workouts); ?></h3>
                <p class="text-gray-600">Total Workouts</p>
            </div>

            <!-- This Month -->
            <div class="material-card text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="material-icons text-green-600 text-2xl">calendar_today</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo count($this_month_workouts); ?></h3>
                <p class="text-gray-600">This Month</p>
            </div>

            <!-- Current Streak -->
            <div class="material-card text-center">
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="material-icons text-orange-600 text-2xl">local_fire_department</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">7</h3>
                <p class="text-gray-600">Day Streak</p>
            </div>

            <!-- Subscription Status -->
            <div class="material-card text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="material-icons text-purple-600 text-2xl">star</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">
                    <?php echo liftingtracker_user_has_subscription() ? 'Pro Member' : 'Free Trial'; ?>
                </h3>
                <p class="text-gray-600">Status</p>
            </div>
        </div>

        <!-- Main Dashboard Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Workouts -->
            <div class="lg:col-span-2">
                <div class="material-card">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Recent Workouts</h2>
                        <a href="<?php echo home_url('/dashboard/workouts'); ?>" class="btn-material btn-primary">
                            <span class="material-icons mr-2">add</span>
                            New Workout
                        </a>
                    </div>

                    <?php if (empty($recent_workouts)) : ?>
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="material-icons text-gray-400 text-3xl">fitness_center</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">No workouts yet</h3>
                            <p class="text-gray-500 mb-4">Start your fitness journey by logging your first workout!</p>
                            <a href="<?php echo home_url('/dashboard/workouts'); ?>" class="btn-material btn-primary">Log Your First Workout</a>
                        </div>
                    <?php else : ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_workouts as $workout) : 
                                $workout_date = get_post_meta($workout->ID, '_workout_date', true);
                                $duration = get_post_meta($workout->ID, '_workout_duration', true);
                                $calories = get_post_meta($workout->ID, '_calories_burned', true);
                            ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="text-lg font-semibold text-gray-800"><?php echo esc_html($workout->post_title); ?></h4>
                                        <span class="text-sm text-gray-500"><?php echo esc_html($workout_date); ?></span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-2">
                                        <?php if ($duration) : ?>
                                            <span class="flex items-center">
                                                <span class="material-icons text-sm mr-1">schedule</span>
                                                <?php echo esc_html($duration); ?> min
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($calories) : ?>
                                            <span class="flex items-center">
                                                <span class="material-icons text-sm mr-1">local_fire_department</span>
                                                <?php echo esc_html($calories); ?> cal
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($workout->post_content) : ?>
                                        <p class="text-gray-600 text-sm"><?php echo wp_trim_words($workout->post_content, 15); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-6 text-center">
                            <a href="<?php echo home_url('/dashboard/workouts'); ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                                View All Workouts →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions & Progress -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="material-card">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="<?php echo home_url('/dashboard/workouts'); ?>" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <span class="material-icons text-blue-600 mr-3">fitness_center</span>
                            <span class="font-medium text-gray-800">Log Workout</span>
                        </a>
                        <a href="<?php echo home_url('/dashboard/progress'); ?>" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <span class="material-icons text-green-600 mr-3">trending_up</span>
                            <span class="font-medium text-gray-800">View Progress</span>
                        </a>
                        <a href="<?php echo home_url('/exercises'); ?>" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                            <span class="material-icons text-purple-600 mr-3">library_books</span>
                            <span class="font-medium text-gray-800">Browse Exercises</span>
                        </a>
                    </div>
                </div>

                <!-- Progress Chart Preview -->
                <div class="material-card">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Weekly Progress</h3>
                    <div class="h-48 flex items-center justify-center bg-gray-50 rounded-lg">
                        <canvas id="weekly-progress-chart"></canvas>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="<?php echo home_url('/dashboard/progress'); ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                            View Detailed Progress →
                        </a>
                    </div>
                </div>

                <!-- Tips & Motivation -->
                <div class="material-card bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                    <h3 class="text-xl font-semibold mb-3">💪 Today's Tip</h3>
                    <p class="text-blue-100 mb-4">Consistency beats perfection. Even a 15-minute workout is better than no workout at all!</p>
                    <button class="bg-white text-blue-600 px-4 py-2 rounded-md font-medium hover:bg-blue-50 transition-colors">
                        Get More Tips
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Initialize dashboard chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('weekly-progress-chart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Workouts',
                    data: [1, 0, 1, 1, 0, 1, 1],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 2
                    }
                }
            }
        });
    }
});
</script>

<?php get_footer(); ?>
