<?php
/**
 * The main template file
 *
 * @package LiftingTrackerPro
 */

get_header(); ?>

<main class="bg-gray-50 min-h-screen">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-6">Transform Your Fitness Journey</h1>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Track workouts, monitor progress, and achieve your fitness goals with our professional-grade fitness tracking platform.</p>
            
            <?php if (!is_user_logged_in()) : ?>
                <div class="space-x-4">
                    <a href="<?php echo wp_registration_url(); ?>" class="btn-material btn-secondary text-lg px-8 py-3">Start Free Trial</a>
                    <a href="<?php echo wp_login_url(); ?>" class="btn-material btn-primary text-lg px-8 py-3 border-2 border-white bg-transparent hover:bg-white hover:text-blue-600">Log In</a>
                </div>
            <?php else : ?>
                <div class="space-x-4">
                    <a href="<?php echo home_url('/dashboard'); ?>" class="btn-material btn-secondary text-lg px-8 py-3">Go to Dashboard</a>
                    <a href="<?php echo home_url('/workouts'); ?>" class="btn-material btn-primary text-lg px-8 py-3 border-2 border-white bg-transparent hover:bg-white hover:text-blue-600">View Workouts</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Everything You Need to Succeed</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Our comprehensive fitness tracking platform provides all the tools you need to reach your goals.</p>
            </div>

            <div class="workout-grid">
                <!-- Workout Tracking Feature -->
                <div class="material-card text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-icons text-blue-600 text-3xl">fitness_center</span>
                    </div>
                    <h3 class="text-2xl font-semibold mb-3">Workout Tracking</h3>
                    <p class="text-gray-600 mb-4">Log exercises, sets, reps, and weights with our intuitive interface. Track your progress over time.</p>
                    <a href="<?php echo home_url('/workouts'); ?>" class="btn-material btn-primary">Start Tracking</a>
                </div>

                <!-- Progress Analytics Feature -->
                <div class="material-card text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-icons text-green-600 text-3xl">trending_up</span>
                    </div>
                    <h3 class="text-2xl font-semibold mb-3">Progress Analytics</h3>
                    <p class="text-gray-600 mb-4">Visualize your progress with detailed charts and statistics. See your strength gains over time.</p>
                    <a href="<?php echo home_url('/progress'); ?>" class="btn-material btn-primary">View Progress</a>
                </div>

                <!-- Exercise Library Feature -->
                <div class="material-card text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-icons text-purple-600 text-3xl">library_books</span>
                    </div>
                    <h3 class="text-2xl font-semibold mb-3">Exercise Library</h3>
                    <p class="text-gray-600 mb-4">Access hundreds of exercises with detailed instructions and muscle group targeting.</p>
                    <a href="<?php echo home_url('/exercises'); ?>" class="btn-material btn-primary">Browse Exercises</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Workouts Section (for logged-in users) -->
    <?php if (is_user_logged_in()) : ?>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Recent Workouts</h2>
                <a href="<?php echo home_url('/workouts/new'); ?>" class="btn-material btn-primary">
                    <span class="material-icons mr-2">add</span>
                    New Workout
                </a>
            </div>

            <?php
            $recent_workouts = new WP_Query(array(
                'post_type' => 'workout',
                'posts_per_page' => 3,
                'author' => get_current_user_id(),
                'meta_key' => '_workout_date',
                'orderby' => 'meta_value',
                'order' => 'DESC'
            ));

            if ($recent_workouts->have_posts()) : ?>
                <div class="workout-grid">
                    <?php while ($recent_workouts->have_posts()) : $recent_workouts->the_post(); ?>
                        <div class="exercise-card">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-xl font-semibold"><?php the_title(); ?></h3>
                                <span class="text-sm text-gray-500"><?php echo get_post_meta(get_the_ID(), '_workout_date', true); ?></span>
                            </div>
                            
                            <div class="mb-4">
                                <?php
                                $duration = get_post_meta(get_the_ID(), '_workout_duration', true);
                                $calories = get_post_meta(get_the_ID(), '_calories_burned', true);
                                ?>
                                <?php if ($duration) : ?>
                                    <span class="inline-flex items-center text-sm text-gray-600 mr-4">
                                        <span class="material-icons text-sm mr-1">schedule</span>
                                        <?php echo $duration; ?> min
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($calories) : ?>
                                    <span class="inline-flex items-center text-sm text-gray-600">
                                        <span class="material-icons text-sm mr-1">local_fire_department</span>
                                        <?php echo $calories; ?> cal
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="text-gray-600 mb-4">
                                <?php echo wp_trim_words(get_the_content(), 20); ?>
                            </div>

                            <a href="<?php the_permalink(); ?>" class="btn-material btn-primary btn-sm">View Details</a>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-icons text-gray-400 text-4xl">fitness_center</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No workouts yet</h3>
                    <p class="text-gray-500 mb-6">Start your fitness journey by logging your first workout!</p>
                    <a href="<?php echo home_url('/workouts/new'); ?>" class="btn-material btn-primary">Log Your First Workout</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Call to Action Section (for non-logged-in users) -->
    <?php if (!is_user_logged_in()) : ?>
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Start Your Journey?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Join thousands of users who have transformed their fitness with our platform.</p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
                <div class="text-center">
                    <div class="text-3xl font-bold">7</div>
                    <div class="text-blue-200">Day Free Trial</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold">$9.99</div>
                    <div class="text-blue-200">Per Month</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold">∞</div>
                    <div class="text-blue-200">Workouts Tracked</div>
                </div>
            </div>
            
            <div class="mt-8">
                <a href="<?php echo wp_registration_url(); ?>" class="btn-material btn-secondary text-lg px-8 py-3">Start Your Free Trial</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
