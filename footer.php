	<footer class="bg-gray-800 text-white py-12">
		<div class="container mx-auto px-4">
			<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
				<!-- Company Info -->
				<div>
					<h3 class="text-xl font-semibold mb-4">
						<?php bloginfo('name'); ?>
					</h3>
					<p class="text-gray-400 mb-4">
						<?php bloginfo('description'); ?>
					</p>
					<div class="flex space-x-4">
						<a href="#" class="text-gray-400 hover:text-white">
							<span class="material-icons">facebook</span>
						</a>
						<a href="#" class="text-gray-400 hover:text-white">
							<span class="material-icons">mail</span>
						</a>
					</div>
				</div>

				<!-- Quick Links -->
				<div>
					<h4 class="text-lg font-semibold mb-4">Quick Links</h4>
					<ul class="space-y-2">
						<li><a href="<?php echo esc_url(home_url('/')); ?>" class="text-gray-400 hover:text-white">Home</a></li>
						<li><a href="<?php echo esc_url(home_url('/workouts')); ?>" class="text-gray-400 hover:text-white">Workouts</a></li>
						<li><a href="<?php echo esc_url(home_url('/exercises')); ?>" class="text-gray-400 hover:text-white">Exercises</a></li>
						<li><a href="<?php echo esc_url(home_url('/progress')); ?>" class="text-gray-400 hover:text-white">Progress</a></li>
					</ul>
				</div>

				<!-- Support -->
				<div>
					<h4 class="text-lg font-semibold mb-4">Support</h4>
					<ul class="space-y-2">
						<li><a href="<?php echo esc_url(home_url('/help')); ?>" class="text-gray-400 hover:text-white">Help Center</a></li>
						<li><a href="<?php echo esc_url(home_url('/contact')); ?>" class="text-gray-400 hover:text-white">Contact Us</a></li>
						<li><a href="<?php echo esc_url(home_url('/privacy')); ?>" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
						<li><a href="<?php echo esc_url(home_url('/terms')); ?>" class="text-gray-400 hover:text-white">Terms of Service</a></li>
					</ul>
				</div>

				<!-- Newsletter -->
				<div>
					<h4 class="text-lg font-semibold mb-4">Stay Updated</h4>
					<p class="text-gray-400 mb-4">Get fitness tips and updates delivered to your inbox.</p>
					<form class="flex">
						<input type="email" placeholder="Your email" class="flex-1 px-3 py-2 bg-gray-700 text-white rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
						<button type="submit" class="bg-blue-600 px-4 py-2 rounded-r-md hover:bg-blue-700 transition-colors">
							<span class="material-icons">send</span>
						</button>
					</form>
				</div>
			</div>

			<!-- Footer Bottom -->
			<div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
				<div class="text-gray-400 text-sm">
					&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.
				</div>
				
				<?php if (has_nav_menu('footer')) : ?>
					<div class="mt-4 md:mt-0">
						<?php
						wp_nav_menu(array(
							'theme_location' => 'footer',
							'container' => false,
							'menu_class' => 'flex space-x-6 text-sm',
						));
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</footer>

</div><!-- #page -->

<!-- Theme JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
	// Mobile menu toggle
	const mobileMenuButton = document.getElementById('mobile-menu-button');
	const mobileMenu = document.getElementById('mobile-menu');
	
	if (mobileMenuButton && mobileMenu) {
		mobileMenuButton.addEventListener('click', function() {
			mobileMenu.classList.toggle('hidden');
		});
	}
});
</script>

<?php wp_footer(); ?>

</body>
</html>
