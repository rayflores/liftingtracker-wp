/**
 * LiftingTracker Pro Stripe Integration JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Stripe
    if (typeof liftingtracker_stripe !== 'undefined' && liftingtracker_stripe.publishable_key) {
        const stripe = Stripe(liftingtracker_stripe.publishable_key);
        const elements = stripe.elements();

        // Create card element
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
                invalid: {
                    color: '#9e2146',
                },
            },
        });

        // Mount card element
        const cardElementContainer = document.getElementById('card-element');
        if (cardElementContainer) {
            cardElement.mount('#card-element');
        }

        // Handle form submission
        const form = document.getElementById('subscription-form');
        if (form) {
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                
                const submitButton = form.querySelector('button[type="submit"]');
                const errorElement = document.getElementById('card-errors');
                
                // Disable submit button
                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';
                
                try {
                    // Create payment method
                    const {error, paymentMethod} = await stripe.createPaymentMethod({
                        type: 'card',
                        card: cardElement,
                        billing_details: {
                            name: document.getElementById('cardholder-name').value,
                            email: document.getElementById('email').value,
                        },
                    });

                    if (error) {
                        showError(errorElement, error.message);
                        resetSubmitButton(submitButton);
                        return;
                    }

                    // Send payment method to server
                    const formData = new FormData();
                    formData.append('action', 'liftingtracker_create_subscription');
                    formData.append('payment_method_id', paymentMethod.id);
                    formData.append('nonce', liftingtracker_stripe.nonce);

                    const response = await fetch(liftingtracker_stripe.ajax_url, {
                        method: 'POST',
                        body: formData,
                    });

                    const result = await response.json();

                    if (result.success) {
                        if (result.data.client_secret) {
                            // Confirm payment if needed
                            const {error: confirmError} = await stripe.confirmCardPayment(result.data.client_secret);
                            
                            if (confirmError) {
                                showError(errorElement, confirmError.message);
                                resetSubmitButton(submitButton);
                            } else {
                                // Success - redirect to dashboard
                                window.location.href = '/dashboard?subscription=success';
                            }
                        } else {
                            // Success - subscription is active
                            window.location.href = '/dashboard?subscription=success';
                        }
                    } else {
                        showError(errorElement, result.data.message || 'An error occurred while processing your subscription.');
                        resetSubmitButton(submitButton);
                    }

                } catch (error) {
                    showError(errorElement, 'An unexpected error occurred. Please try again.');
                    resetSubmitButton(submitButton);
                }
            });
        }

        // Handle card element changes
        cardElement.on('change', function(event) {
            const errorElement = document.getElementById('card-errors');
            if (event.error) {
                showError(errorElement, event.error.message);
            } else {
                errorElement.textContent = '';
                errorElement.className = 'hidden';
            }
        });
    }

    // Subscription cancellation
    const cancelButton = document.getElementById('cancel-subscription');
    if (cancelButton) {
        cancelButton.addEventListener('click', async function(event) {
            event.preventDefault();
            
            if (!confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your current billing period.')) {
                return;
            }

            const button = event.target;
            button.disabled = true;
            button.textContent = 'Canceling...';

            try {
                const formData = new FormData();
                formData.append('action', 'liftingtracker_cancel_subscription');
                formData.append('nonce', liftingtracker_stripe.nonce);

                const response = await fetch(liftingtracker_stripe.ajax_url, {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    alert('Your subscription has been canceled successfully.');
                    location.reload();
                } else {
                    alert(result.data.message || 'An error occurred while canceling your subscription.');
                    button.disabled = false;
                    button.textContent = 'Cancel Subscription';
                }

            } catch (error) {
                alert('An unexpected error occurred. Please try again.');
                button.disabled = false;
                button.textContent = 'Cancel Subscription';
            }
        });
    }

    /**
     * Helper Functions
     */
    function showError(element, message) {
        element.textContent = message;
        element.className = 'text-red-600 text-sm mt-2';
    }

    function resetSubmitButton(button) {
        button.disabled = false;
        button.textContent = 'Start Subscription';
    }
});
