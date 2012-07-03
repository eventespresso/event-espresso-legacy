<?php

function espresso_transactions_aim_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['x_cust_id'])) {
		$attendee_id = $_REQUEST['x_cust_id'];
	}
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_aim_get_attendee_id');

function espresso_process_aim($payment_data) {
	extract($payment_data);
	global $wpdb, $org_options;

	require_once 'AuthorizeNet.php';

	echo '<!--Event Espresso Authorize.net AIM Gateway Version ' . $authnet_aim_gateway_version . '-->';

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

//start transaction
	$transaction = new AuthorizeNetAIM($authnet_aim_login_id, $authnet_aim_transaction_key);
	$transaction->amount = $_POST['amount'];
	$transaction->card_num = $_POST['card_num'];
	$transaction->exp_date = $_POST['exp_date'];
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
//Capture response
	$response = $transaction->authorizeAndCapture();

	$txn_id = $response->transaction_id;
	$attendee_id = $response->customer_id;
	$txn_type = $response->transaction_type;
	$payment_date = date("d-m-Y");

	$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . espresso_registration_id($attendee_id) . "' ";
	$sql .= empty($attendee_id) ? '' : " AND id= '" . $attendee_id . "' ";
	$sql .= " ORDER BY id LIMIT 0,1";

	$attendees = $wpdb->get_results($sql);
	foreach ($attendees as $attendee) {
		$attendee_id = $attendee->id;
		$att_registration_id = $attendee->registration_id;
		$registration_id = $att_registration_id;
		$lname = $attendee->lname;
		$fname = $attendee->fname;
		$email = $attendee->email;
		$amount_pd = $attendee->amount_pd;
		$event_id = $attendee->event_id;
	}

	$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
	foreach ($events as $event) {
		$event_id = $event->id;
		$event_name = $event->event_name;
		$event_desc = $event->event_desc;
		$event_description = $event->event_desc;
		$event_identifier = $event->event_identifier;
		$active = $event->is_active;
	}
//Build links
	$event_url = espresso_reg_url($event_id);
	$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';

	if ($response->approved) {
		$payment_status = 'Completed';
		?>
		<h2><?php _e('Thank You!', 'event_espresso'); ?></h2>
		<p><?php _e('Your transaction has been processed.', 'event_espresso'); ?></p>
		<p><?php __('Transaction ID:', 'event_espresso') . $response->transaction_id; ?></p>
		<?php
		$payment_status = 'Completed';
		$payment_data['event_link'] = $event_link;
		$payment_data['fname'] = $fname;
		$payment_data['lname'] = $lname;
		$payment_data['txn_type'] = $txn_type;
		$payment_data['payment_date'] = $payment_date;
		$payment_data['total_cost'] = $total_cost;
		$payment_data['payment_status'] = $payment_status;
		$payment_data['att_registration_id'] = $att_registration_id;
		$payment_data['txn_id'] = $txn_id;
	} else {
		print $response->error_message;
		$payment_status = 'Payment Declined';
		$payment_failed = true;
	}

	$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '" . $payment_status . "', txn_type = '" . $txn_type . "', txn_id = '" . $txn_id . "', payment_date ='" . $payment_date . "', transaction_details = '" . serialize($response) . "'  WHERE registration_id ='" . espresso_registration_id($attendee_id) . "'";
	$wpdb->query($sql);

//Debug
//print_r($response);
//echo $att_registration_id;
//If the payment fails, then we display the payment page
	if ($payment_failed == true) {
		echo event_espresso_pay($att_registration_id);
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_aim');