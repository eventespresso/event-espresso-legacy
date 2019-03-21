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
	
	//Check for alternate Stripe settings
	if ( !empty($payment_data['event_meta']['stripe_secret_key'] ) ) {
		//Alternative Stripe settings set on the event, set the key to be used here.
    	$stripe_settings['stripe_secret_key'] = $payment_data['event_meta']['stripe_secret_key'];
	}

	//Include the Stripe API
	require_once (dirname(__FILE__).'/stripe-php-1.18.0/lib/Stripe.php');

	//Pull the stripeToken posted by Checkout.
	$token = $_POST['stripeToken'];

	//Set the Stripe API secret key.
	Stripe::setApiKey( $stripe_settings['stripe_secret_key'] );
    
    //Build the Stripe data array
    $stripe_data = array(
        'amount' => str_replace( array(',', '.'), '', number_format( $payment_data['total_cost'], 2) ),
        'currency' => $stripe_settings['stripe_currency_symbol'],
        'card' => $token,
        'description' => $payment_data["event_name"] . 
        				"[" . date('m-d-Y', strtotime($payment_data['start_date'])) . "]" . 
        				" >> " . $payment_data["fname"] . " " . $payment_data["lname"]
    );
	
	//Create a charge with Stripe.
	$charge = Stripe_Charge::create( $stripe_data );

	//Convert the Stripe charge to an array so we can use it.
	$charge_array = $charge->__toArray(true);
	
	if ( !empty( $charge_array ) ) {
		$payment_data['txn_details'] = serialize( $charge_array );
		if ( isset($charge_array['status'] ) ) {
			echo "<div id='stripe_response'>";
			if ($charge_array['paid'] === TRUE) {
				echo "<div class='stripe_status'>" . $charge_array['outcome']['seller_message'] . "</div>";
				$payment_data['payment_status'] = 'Completed';
				$payment_data['txn_id'] = $charge_array['id'];
			}
			if ( !empty($charge_array['failure_code']) ) {
				echo "<div class='stripe_error'>ERROR: " . $charge_array['failure_code'] . " - " . $charge_array['failure_message'] . "  </div>";
			}
			echo "</div>";
		}
	}
	if ( empty($charge_array['status'] ) ) {
		echo "<div id='stripe_response' class='stripe_error'>Looks like something went wrong.  Please try again or notify the website administrator.</div>";
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
