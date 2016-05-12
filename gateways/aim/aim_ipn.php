<?php

function espresso_transactions_aim_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['x_cust_id'])) {
		$attendee_id = $_REQUEST['x_cust_id'];
	}
	return $attendee_id;
}

function espresso_process_aim($payment_data) {
	extract($payment_data);
	global $wpdb, $org_options;

	require_once 'AuthorizeNet.php';



	$authnet_aim_settings = get_option('event_espresso_authnet_aim_settings');
	$authnet_aim_login_id = $authnet_aim_settings['authnet_aim_login_id'];
	$authnet_aim_transaction_key = $authnet_aim_settings['authnet_aim_transaction_key'];

// Enable test mode if needed
//4007000000027  <-- test successful visa
//4222222222222  <-- test failure card number
	if ($authnet_aim_settings['use_sandbox']) {
		define("AUTHORIZENET_SANDBOX", true);
		define("AUTHORIZENET_LOG_FILE", true);
	} else {
		define("AUTHORIZENET_SANDBOX", false);
	}
	//if in debug mode, use authorize.net's sandbox id; otherwise use the Event Espresso partner id
	$authnet_aim_partner_id = defined( 'AUTHORIZENET_SANDBOX' ) && AUTHORIZENET_SANDBOX === true ? 'AAA100302' : 'AAA105363';
//start transaction
	$transaction = new Espresso_AuthorizeNetAIM($authnet_aim_login_id, $authnet_aim_transaction_key);
	echo '<!--Event Espresso Authorize.net AIM Gateway Version ' . $transaction->gateway_version . '-->';
	$transaction->solution_id = $authnet_aim_partner_id; 
	$transaction->amount = $_POST['amount'];
	$transaction->card_num = $_POST['card_num'];
	$transaction->exp_date = $_POST['exp_month'].$_POST['exp_year'];
	$transaction->card_code = $_POST['ccv_code'];
	$transaction->first_name = $_POST['first_name'];
	$transaction->last_name = $_POST['last_name'];
	$transaction->email = $_POST['email'];
	$transaction->address = $_POST['address'];
	$transaction->city = $_POST['city'];
	$transaction->state = $_POST['state'];
	$transaction->zip = $_POST['zip'];
	$transaction->cust_id = $_POST['x_cust_id'];
	$transaction->invoice_num = $_POST['invoice_num'];
	if ($authnet_aim_settings['test_transactions']) {
		$transaction->test_request = "true";
	}
	
	//Prevent duplicate transactions within a certain amount of time:
	$transaction->duplicate_window = apply_filters('filter_hook_espresso_aim_duplicate_window', 300);//300 seconds = 5 minutes
	//The largest value Authorize.net will accept for x_duplicate_window is 28800, which equals eight hours. If a value greater than 28800 sent, the payment gateway will default to 28800. If x_duplicate_window is set to 0 or to a negative number, no duplicate transaction window will be enforced for your software's transactions. If no value is sent, the default value of 120 (two minutes) would be used.
	

	$sql = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'";
	$session_id = $wpdb->get_var($sql);
	$sql = "SELECT a.final_price, a.quantity, ed.event_name, a.price_option, a.fname, a.lname FROM " . EVENTS_ATTENDEE_TABLE . " a JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$sql .= " WHERE attendee_session='" . $session_id . "' ORDER BY a.id ASC";
	$items = $wpdb->get_results($sql);
	foreach ($items as $key=>$item) {
		$item_num=$key+1;
		$transaction->addLineItem(
				$item_num,
				substr_replace($item->event_name, '...', 28),
				substr($item->price_option . ' for ' . $item->event_name . '. Attendee: '. $item->fname . ' ' . $item->lname, 0, 255),
				$item->quantity,
				$item->final_price,
				FALSE
		);
	}
	
	$payment_data['txn_type'] = 'authorize.net AIM';
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = 'No response from authorize.net';
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
//Capture response
	$response = $transaction->authorizeAndCapture();


	if (!empty($response)) {
		if ($authnet_aim_settings['use_sandbox']) {
			$payment_data['txn_id'] = $response->invoice_number;
		} else {
			$payment_data['txn_id'] = $response->transaction_id;
		}
		$payment_data['txn_details'] = serialize(get_object_vars($response));
		if ($response->approved) {
			$payment_data['payment_status'] = 'Completed';
			?>
			<p><?php _e('Your transaction has been processed.', 'event_espresso'); ?></p>
			<p><?php __('Transaction ID:', 'event_espresso') . $response->transaction_id; ?></p>
			<?php
		} else {
			print $response->error_message;
			$payment_data['payment_status'] = 'Payment Declined';
		}
	} else {
		?>
		<p><?php _e('There was no response from Authorize.net.', 'event_espresso'); ?></p>
		<?php
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
