<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<header id="masthead" class="w-full bg-black site-header">
		<div class="p-3 mx-auto">
			<div class="container mx-auto flex items-center justify-between text-white relative">
				<!-- Brand - Left Side -->
				<div class="flex-shrink-0">
					<?php if (has_custom_logo()) : ?>
						<div class="custom-logo">
							<?php the_custom_logo(); ?>
						</div>
					<?php else : ?>
						<a href="<?php echo esc_url(home_url('/')); ?>" class="cursor-pointer py-1.5 font-bold text-white hover:text-gray-300 transition-colors">
							<?php bloginfo('name'); ?>
						</a>
					<?php endif; ?>
				</div>
				
				<!-- Nav Links - Center (Desktop only) -->
				<div class="hidden lg:flex lg:items-center lg:justify-center flex-1">
					<?php
					wp_nav_menu(array(
						'theme_location' => 'primary',
						'container' => false,
						'menu_class' => 'mb-4 mt-2 flex flex-col gap-2 text-inherit lg:mb-0 lg:mt-0 lg:flex-row lg:items-center lg:gap-6 list-none',
						'link_before' => '<span class="flex items-center gap-1 p-1 font-bold text-white hover:text-gray-300 transition-colors capitalize nav-link">',
						'link_after' => '</span>',
						'fallback_cb' => 'liftingtracker_pro_default_menu',
					));
					?>
				</div>
				
				<!-- User Menu/Auth - Right Side (Desktop only) -->
				<div class="hidden lg:flex lg:items-center gap-2 flex-shrink-0">
					<?php if (is_user_logged_in()) : ?>
						<!-- User Menu Dropdown -->
						<div class="relative group user-menu">
							<button class="flex items-center gap-2 text-white hover:text-gray-300 transition-colors p-2 rounded-md hover:bg-gray-800 user-profile-button">
								<span class="material-icons text-lg">account_circle</span>
								<span class="font-medium"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
								<span class="material-icons text-sm">expand_more</span>
							</button>
							<div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-modal opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 dropdown-menu">
								<div class="py-2">
									<a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
										<span class="material-icons text-lg">dashboard</span>
										Dashboard
									</a>
									<a href="<?php echo esc_url(home_url('/workouts')); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
										<span class="material-icons text-lg">fitness_center</span>
										Workouts
									</a>
									<a href="<?php echo esc_url(home_url('/profile')); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
										<span class="material-icons text-lg">person</span>
										Profile
									</a>
									<a href="<?php echo esc_url(home_url('/settings')); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
										<span class="material-icons text-lg">settings</span>
										Settings
									</a>
									<hr class="my-1">
									<a href="<?php echo esc_url(wp_logout_url()); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
										<span class="material-icons text-lg">logout</span>
										Sign Out
									</a>
								</div>
							</div>
						</div>
					<?php else : ?>
						<!-- Sign In Button -->
						<a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline border-white text-white hover:bg-white hover:text-black transition-all duration-200 flex items-center gap-2">
							<span class="material-icons text-lg">person</span>
							Sign In
						</a>
					<?php endif; ?>
				</div>
				
				<!-- Mobile Menu Toggle -->
				<button id="mobile-menu-button" class="lg:hidden text-white hover:text-gray-300 transition-colors p-2 hover:bg-transparent focus:bg-transparent active:bg-transparent rounded-md">
					<span class="material-icons text-2xl mobile-menu-icon">menu</span>
				</button>
			</div>
			
			<!-- Mobile Menu -->
			<div id="mobile-menu" class="lg:hidden hidden">
				<div class="rounded-xl bg-white px-4 pt-2 pb-4 text-blue-gray-900 mt-2">
					<div class="container mx-auto">
						<!-- Mobile Navigation Links -->
						<ul class="mb-4 mt-2 flex flex-col gap-2 text-inherit list-none">
							<?php
							$nav_items = wp_get_nav_menu_items(get_nav_menu_locations()['primary'] ?? '');
							if ($nav_items) :
								foreach ($nav_items as $item) : ?>
									<li class="list-none">
										<a href="<?php echo esc_url($item->url); ?>" class="flex items-center gap-1 p-1 font-bold text-gray-900 hover:bg-gray-100 rounded-md transition-colors capitalize">
											<?php echo esc_html($item->title); ?>
										</a>
									</li>
								<?php endforeach;
							else : ?>
								<li class="list-none"><a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-1 p-1 font-bold text-gray-900 hover:bg-gray-100 rounded-md transition-colors">Home</a></li>
								<li class="list-none"><a href="<?php echo esc_url(home_url('/workouts')); ?>" class="flex items-center gap-1 p-1 font-bold text-gray-900 hover:bg-gray-100 rounded-md transition-colors">Workouts</a></li>
								<li class="list-none"><a href="<?php echo esc_url(home_url('/exercises')); ?>" class="flex items-center gap-1 p-1 font-bold text-gray-900 hover:bg-gray-100 rounded-md transition-colors">Exercises</a></li>
							<?php endif; ?>
						</ul>
						
						<!-- Mobile User Actions -->
						<?php if (is_user_logged_in()) : ?>
							<div class="border-t pt-4 mt-4">
								<a href="<?php echo esc_url(home_url('/profile')); ?>" class="btn btn-outline w-full flex items-center gap-2 justify-center mb-2">
									<span class="material-icons text-lg">person</span>
									Profile
								</a>
								<a href="<?php echo esc_url(wp_logout_url()); ?>" class="btn btn-secondary w-full flex items-center gap-2 justify-center">
									<span class="material-icons text-lg">logout</span>
									Sign Out
								</a>
							</div>
						<?php else : ?>
							<div class="border-t pt-4 mt-4">
								<a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-primary w-full flex items-center gap-2 justify-center">
									<span class="material-icons text-lg">person</span>
									Sign In
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</header>

<?php
// Default menu fallback
function liftingtracker_pro_default_menu() {
	echo '<ul class="mb-4 mt-2 flex flex-col gap-2 text-inherit lg:mb-0 lg:mt-0 lg:flex-row lg:items-center lg:gap-6 list-none">';
	echo '<li class="list-none"><a href="' . esc_url(home_url('/')) . '" class="flex items-center gap-1 p-1 font-bold text-white hover:text-gray-300 transition-colors capitalize nav-link">Home</a></li>';
	echo '<li class="list-none"><a href="' . esc_url(home_url('/workouts')) . '" class="flex items-center gap-1 p-1 font-bold text-white hover:text-gray-300 transition-colors capitalize nav-link">Workouts</a></li>';
	echo '<li class="list-none"><a href="' . esc_url(home_url('/exercises')) . '" class="flex items-center gap-1 p-1 font-bold text-white hover:text-gray-300 transition-colors capitalize nav-link">Exercises</a></li>';
	echo '</ul>';
}
?>
