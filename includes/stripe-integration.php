<?php
/**
 * Stripe Integration for LiftingTracker Pro
 * 
 * @package LiftingTrackerPro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LiftingTracker_Stripe_Integration {
    
    private $stripe_secret_key;
    private $stripe_publishable_key;
    
    public function __construct() {
        // Get Stripe keys from WordPress options or define them in wp-config.php
        $this->stripe_secret_key = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : get_option('liftingtracker_stripe_secret_key');
        $this->stripe_publishable_key = defined('STRIPE_PUBLISHABLE_KEY') ? STRIPE_PUBLISHABLE_KEY : get_option('liftingtracker_stripe_publishable_key');
        
        // Include Stripe PHP library
        $this->include_stripe_library();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    private function include_stripe_library() {
        // Download and include Stripe PHP library
        // You'll need to download the Stripe PHP SDK to your theme
        if (file_exists(get_template_directory() . '/includes/stripe-php/init.php')) {
            require_once get_template_directory() . '/includes/stripe-php/init.php';
        }
    }
    
    private function init_hooks() {
        // AJAX handlers for subscription management
        add_action('wp_ajax_liftingtracker_create_subscription', array($this, 'create_subscription'));
        add_action('wp_ajax_nopriv_liftingtracker_create_subscription', array($this, 'create_subscription'));
        
        add_action('wp_ajax_liftingtracker_cancel_subscription', array($this, 'cancel_subscription'));
        
        // Enqueue Stripe.js
        add_action('wp_enqueue_scripts', array($this, 'enqueue_stripe_scripts'));
        
        // Add Stripe settings to admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function enqueue_stripe_scripts() {
        if (is_page('sign-up') || is_page('subscription')) {
            wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', array(), '3.0', true);
            wp_enqueue_script('liftingtracker-stripe', get_template_directory_uri() . '/assets/js/stripe.js', array('jquery', 'stripe-js'), '1.0.0', true);
            
            wp_localize_script('liftingtracker-stripe', 'liftingtracker_stripe', array(
                'publishable_key' => $this->stripe_publishable_key,
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('liftingtracker_stripe_nonce'),
            ));
        }
    }
    
    public function create_subscription() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'liftingtracker_stripe_nonce')) {
            wp_die('Security check failed');
        }
        
        try {
            \Stripe\Stripe::setApiKey($this->stripe_secret_key);
            
            $user_id = get_current_user_id();
            $user = get_userdata($user_id);
            $payment_method_id = sanitize_text_field($_POST['payment_method_id']);
            
            // Create or retrieve Stripe customer
            $stripe_customer_id = get_user_meta($user_id, 'stripe_customer_id', true);
            
            if (!$stripe_customer_id) {
                $customer = \Stripe\Customer::create([
                    'email' => $user->user_email,
                    'name' => $user->display_name,
                    'payment_method' => $payment_method_id,
                    'invoice_settings' => [
                        'default_payment_method' => $payment_method_id,
                    ],
                ]);
                
                $stripe_customer_id = $customer->id;
                update_user_meta($user_id, 'stripe_customer_id', $stripe_customer_id);
            } else {
                // Attach payment method to existing customer
                \Stripe\PaymentMethod::retrieve($payment_method_id)->attach([
                    'customer' => $stripe_customer_id,
                ]);
                
                // Update default payment method
                \Stripe\Customer::update($stripe_customer_id, [
                    'invoice_settings' => [
                        'default_payment_method' => $payment_method_id,
                    ],
                ]);
            }
            
            // Create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $stripe_customer_id,
                'items' => [[
                    'price' => 'price_1234567890', // Replace with your Stripe price ID
                ]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription'
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);
            
            // Save subscription data
            update_user_meta($user_id, 'stripe_subscription_id', $subscription->id);
            update_user_meta($user_id, 'subscription_status', $subscription->status);
            
            wp_send_json_success([
                'subscription_id' => $subscription->id,
                'client_secret' => $subscription->latest_invoice->payment_intent->client_secret,
                'status' => $subscription->status,
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    public function cancel_subscription() {
        // Verify nonce and user permissions
        if (!wp_verify_nonce($_POST['nonce'], 'liftingtracker_stripe_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        try {
            \Stripe\Stripe::setApiKey($this->stripe_secret_key);
            
            $user_id = get_current_user_id();
            $subscription_id = get_user_meta($user_id, 'stripe_subscription_id', true);
            
            if ($subscription_id) {
                $subscription = \Stripe\Subscription::retrieve($subscription_id);
                $subscription->cancel();
                
                update_user_meta($user_id, 'subscription_status', 'canceled');
                
                wp_send_json_success([
                    'message' => 'Subscription canceled successfully',
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'No active subscription found',
                ]);
            }
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    public function add_admin_menu() {
        add_options_page(
            'LiftingTracker Stripe Settings',
            'Stripe Settings',
            'manage_options',
            'liftingtracker-stripe',
            array($this, 'admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('liftingtracker_stripe_settings', 'liftingtracker_stripe_publishable_key');
        register_setting('liftingtracker_stripe_settings', 'liftingtracker_stripe_secret_key');
        register_setting('liftingtracker_stripe_settings', 'liftingtracker_stripe_price_id');
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>LiftingTracker Stripe Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('liftingtracker_stripe_settings'); ?>
                <?php do_settings_sections('liftingtracker_stripe_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Stripe Publishable Key</th>
                        <td>
                            <input type="text" name="liftingtracker_stripe_publishable_key" 
                                   value="<?php echo esc_attr(get_option('liftingtracker_stripe_publishable_key')); ?>" 
                                   class="regular-text" />
                            <p class="description">Your Stripe publishable key (starts with pk_)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stripe Secret Key</th>
                        <td>
                            <input type="password" name="liftingtracker_stripe_secret_key" 
                                   value="<?php echo esc_attr(get_option('liftingtracker_stripe_secret_key')); ?>" 
                                   class="regular-text" />
                            <p class="description">Your Stripe secret key (starts with sk_)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stripe Price ID</th>
                        <td>
                            <input type="text" name="liftingtracker_stripe_price_id" 
                                   value="<?php echo esc_attr(get_option('liftingtracker_stripe_price_id')); ?>" 
                                   class="regular-text" />
                            <p class="description">Your Stripe subscription price ID (starts with price_)</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    // Helper function to check if user has active subscription
    public static function user_has_active_subscription($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $subscription_status = get_user_meta($user_id, 'subscription_status', true);
        return in_array($subscription_status, ['active', 'trialing']);
    }
}

// Initialize the Stripe integration
new LiftingTracker_Stripe_Integration();

// Helper functions for templates
function liftingtracker_user_has_subscription($user_id = null) {
    return LiftingTracker_Stripe_Integration::user_has_active_subscription($user_id);
}
?>
