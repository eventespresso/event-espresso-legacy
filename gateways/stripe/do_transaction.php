<?php

function espresso_transactions_stripe_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['attendee_id']))
		$attendee_id = $_REQUEST['attendee_id'];
	return $attendee_id;
}

function espresso_process_stripe($payment_data) {
	global $wpdb;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_id'] = 0;
	$payment_data['txn_type'] = 'Stripe';
	$payment_data['payment_status'] = 'Incomplete';
	$stripe_settings = get_option('event_espresso_stripe_settings');
	$intent = null;
	$intent_array = null;
	
	//Check for alternate Stripe settings
	if ( !empty($payment_data['event_meta']['stripe_secret_key'] ) ) {
		//Alternative Stripe settings set on the event, set the key to be used here.
		$stripe_settings['stripe_secret_key'] = $payment_data['event_meta']['stripe_secret_key'];
	}

	//Include the Stripe API
	if (! class_exists('Stripe\Stripe')) {
		require_once (dirname(__FILE__).'/stripe-php-6.43.1/init.php');
	}
	
	\Stripe\Stripe::setApiKey($stripe_settings['stripe_secret_key']);

	// The JS set the payment intend ID in a hidden field, so pull that value. 
	$payment_intent_id = isset($_REQUEST['espresso_stripe_payment_intent_id']) ? $_REQUEST['espresso_stripe_payment_intent_id'] : '';

	// Pull the payment intent object from Stripe.
	if ($payment_intent_id) {
		$intent = \Stripe\PaymentIntent::retrieve(
			$payment_intent_id
		);
		//Convert the Stripe payment intent to an array so we can use it.
		$intent_array = $intent->__toArray(true);
	}

	// Check we have values from the intent object in an array.
	if ( !empty( $intent_array ) ) {
		$payment_data['txn_details'] = serialize( $intent_array );
		if ( isset($intent_array['status'] ) ) {
			$stripe_charge = reset($intent_array['charges']['data']);
			echo "<div id='stripe_response'>";
			if ($intent_array['status'] === 'succeeded') {
				if(!empty($stripe_charge)){
					echo "<h3 class='stripe_status'>" . $stripe_charge['outcome']['seller_message'] . "</h3>";
				}
				$payment_data['payment_status'] = 'Completed';
				$payment_data['txn_id'] = $intent_array['id'];
			}
			if ( !empty($stripe_charge['failure_code']) ) {
				echo "<h3 class='stripe_error'>" . 
					sprintf(
						/* translators: 1: error code, 2: failure message */
						esc_html__('ERROR: %1$s - %2$s', 'event_espresso'),
						$intent_array['failure_code'],
						$intent_array['failure_message']

					)  . 
					"</h3>";
			}
			echo "</div>";
		}
	}
	if ( empty($intent) ) {
		echo "<div id='stripe_response' class='stripe_error'>Looks like something went wrong.  Please try again or notify the website administrator.</div>";
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
