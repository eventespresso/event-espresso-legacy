<?php

function espresso_transactions_stripe_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_stripe($payment_data) {
	global $wpdb;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_id'] = 0;
	$payment_data['txn_type'] = 'Stripe';
	$payment_data['payment_status'] = 'Incomplete';
	require_once(dirname(__FILE__) . '/stripe.class.php');

	$cls_stripe = new Espresso_ClsStripe();
	$stripe_settings = get_option('event_espresso_stripe_settings');

	$cc = $_POST['cc'];
	$exp_month = $_POST['exp_month'];
	$exp_year = $_POST['exp_year'];
	$csc = $_POST['csc'];
	$bname = $_POST['first_name'] . " " . $_POST['last_name'];
	$baddress = $_POST['address'];
	$bcity = $_POST['city'];
	$bzip = $_POST['zip'];
	$email = $_POST['email'];
	$line_item = "LINEITEM~PRODUCTID=" . $payment_data['attendee_id'] . "+DESCRIPTION=" . $payment_data["event_name"] . "[" . date('m-d-Y', strtotime($payment_data['start_date'])) . "]" . " >> " . $payment_data["fname"] . " " . $payment_data["lname"] . "
							QUANTITY=1 UNITCOST=" . $payment_data['total_cost'];

	$response = $cls_stripe->do_transaction($payment_data['total_cost'], $cc, $csc, $exp_month, $exp_year, $bname, $line_item, $payment_data);
	if (!empty($response)) {
		$payment_data['txn_details'] = serialize($response);
		if (isset($response['status'])) {
			echo "<div id='stripe_response'>";
			if ($response['status'] > 0) {
				echo "<div class='stripe_status'>" . $response['msg'] . "</div>";
				$payment_data['payment_status'] = 'Completed';
				$payment_data['txn_id'] = $response['txid'];
			}
			if (isset($response['error_msg']) && strlen(trim($response['error_msg'])) > 0) {
				echo "<div class='stripe_error'>ERROR: " . $response['error_msg'] . "  </div>";
			}
			echo "</div>";
		}
	}
	if ($payment_data['payment_status'] != 'Completed') {
		echo "<div id='stripe_response' class='stripe_error'>Looks like something went wrong.  Please try again or notify the website administrator.</div>";
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
