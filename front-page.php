<?php
/**
 * The front page template file
 *
 * This template mirrors the home.jsx React component design
 * with a hero section, dashboard cards, and quick actions.
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">
		
		<!-- Hero Section -->
		<div class="hero-section relative flex content-center items-center justify-center pt-16 pb-32">
			<div class="bg-black inset-0 absolute"></div>
			<div class="hero-content max-w-8xl container relative mx-auto z-10">
				<div class="flex flex-wrap items-center">
					<div class="ml-auto mr-auto w-full px-4 text-center lg:w-8/12">
						<h1 class="hero-title mb-6 font-black text-4xl md:text-5xl lg:text-6xl text-white">
							Welcome to Lifting Tracker
						</h1>
						<p class="hero-subtitle opacity-80 mb-8 text-lg md:text-xl text-white">
							Track your workouts, monitor your progress, and crush your fitness goals. 
							Your complete workout companion for strength training.
						</p>
						<div class="flex gap-4 justify-center flex-col sm:flex-row">
							<?php if (is_user_logged_in()) : ?>
								<a href="<?php echo esc_url(home_url('/workouts')); ?>" class="btn btn-primary btn-lg">
									<span class="material-icons">fitness_center</span>
									Start Today's Workout
								</a>
								<a href="<?php echo esc_url(home_url('/profile')); ?>" class="btn btn-outline btn-lg border-white">
									View Progress
								</a>
							<?php else : ?>
								<a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-primary btn-lg">
									<span class="material-icons">person</span>
									Sign Up Free
								</a>
								<a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline btn-lg border-white">
									Login
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Dashboard Cards Section -->
		<section class="dashboard-section -mt-32 bg-black px-4 pb-20 pt-4">
			<div class="container mx-auto">
				<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
					
					<!-- Today's Workout Card -->
					<div class="dashboard-card mt-6">
						<div class="dashboard-card-header blue">
							<div class="text-center">
								<span class="material-icons text-white text-5xl mb-2">today</span>
								<h2 class="text-2xl font-bold text-white">Today's Workout</h2>
							</div>
						</div>
						<div class="dashboard-card-content">
							<h3 class="dashboard-card-title">
								<?php echo esc_html(date('l')); ?> Schedule
							</h3>
							<div class="space-y-2 mb-4">
								<?php 
								// Mock workout data - in real app this would come from database
								$todays_exercises = [
									['name' => 'Barbell Squat', 'sets' => 4, 'completed' => 2],
									['name' => 'Bench Press', 'sets' => 3, 'completed' => 0],
									['name' => 'Bent-Over Row', 'sets' => 3, 'completed' => 0],
								];
								
								foreach ($todays_exercises as $exercise) :
									$is_completed = $exercise['completed'] === $exercise['sets'];
									$badge_class = $is_completed ? 'exercise-badge completed' : 'exercise-badge incomplete';
								?>
									<div class="exercise-item">
										<span class="exercise-name"><?php echo esc_html($exercise['name']); ?></span>
										<span class="<?php echo $badge_class; ?>">
											<?php echo esc_html($exercise['completed'] . '/' . $exercise['sets']); ?>
										</span>
									</div>
								<?php endforeach; ?>
							</div>
							<a href="<?php echo esc_url(home_url('/workouts')); ?>" class="btn btn-primary w-full">
								Start Workout
								<span class="material-icons text-lg">arrow_forward</span>
							</a>
						</div>
					</div>

					<!-- Weekly Progress Card -->
					<div class="dashboard-card mt-6">
						<div class="dashboard-card-header green">
							<div class="text-center">
								<span class="material-icons text-white text-5xl mb-2">bar_chart</span>
								<h2 class="text-2xl font-bold text-white">Weekly Progress</h2>
							</div>
						</div>
						<div class="dashboard-card-content">
							<h3 class="dashboard-card-title">This Week's Goals</h3>
							<div class="mb-4">
								<?php
								// Mock progress data
								$weekly_progress = [
									'completed' => 3,
									'total' => 6,
									'percentage' => 50
								];
								?>
								<div class="flex justify-between mb-2">
									<span class="text-sm text-gray-600">Workouts Completed</span>
									<span class="text-sm text-gray-800 font-bold">
										<?php echo esc_html($weekly_progress['completed'] . '/' . $weekly_progress['total']); ?>
									</span>
								</div>
								<div class="progress-bar">
									<div class="progress-fill" style="width: <?php echo esc_attr($weekly_progress['percentage']); ?>%"></div>
								</div>
							</div>
							<a href="<?php echo esc_url(home_url('/progress')); ?>" class="btn btn-outline w-full">
								View Detailed Stats
							</a>
						</div>
					</div>

					<!-- Achievements Card -->
					<div class="dashboard-card mt-6">
						<div class="dashboard-card-header amber">
							<div class="text-center">
								<span class="material-icons text-white text-5xl mb-2">emoji_events</span>
								<h2 class="text-2xl font-bold text-white">Achievements</h2>
							</div>
						</div>
						<div class="dashboard-card-content">
							<h3 class="dashboard-card-title">Recent Milestones</h3>
							<div class="space-y-2 mb-4">
								<?php
								// Mock achievements data
								$recent_achievements = [
									"Completed Monday's workout",
									"New PR: Deadlift 185 lbs",
									"7-day streak maintained"
								];
								
								foreach ($recent_achievements as $achievement) :
								?>
									<div class="achievement-item">
										<div class="achievement-dot"></div>
										<span class="achievement-text"><?php echo esc_html($achievement); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
							<a href="<?php echo esc_url(home_url('/achievements')); ?>" class="btn btn-outline w-full" style="border-color: #f59e0b; color: #f59e0b;">
								View All Achievements
							</a>
						</div>
					</div>
				</div>

				<!-- Quick Actions Section -->
				<div class="mt-12">
					<h2 class="section-title">Quick Actions</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
						
						<a href="<?php echo esc_url(home_url('/workouts')); ?>" class="quick-action-card">
							<div class="quick-action-content">
								<span class="material-icons text-red-500 quick-action-icon">fitness_center</span>
								<h3 class="quick-action-title">Start Workout</h3>
							</div>
						</a>

						<a href="<?php echo esc_url(home_url('/progress')); ?>" class="quick-action-card">
							<div class="quick-action-content">
								<span class="material-icons text-blue-500 quick-action-icon">bar_chart</span>
								<h3 class="quick-action-title">View Progress</h3>
							</div>
						</a>

						<a href="<?php echo esc_url(home_url('/achievements')); ?>" class="quick-action-card">
							<div class="quick-action-content">
								<span class="material-icons text-amber-500 quick-action-icon">emoji_events</span>
								<h3 class="quick-action-title">Achievements</h3>
							</div>
						</a>

						<a href="<?php echo esc_url(home_url('/schedule')); ?>" class="quick-action-card">
							<div class="quick-action-content">
								<span class="material-icons text-green-500 quick-action-icon">event</span>
								<h3 class="quick-action-title">Schedule</h3>
							</div>
						</a>
					</div>
				</div>
			</div>
		</section>
	</main>
</div>

<?php get_footer(); ?>
