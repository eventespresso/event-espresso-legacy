<?php

function espresso_transactions_usaepay_onsite_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_usaepay_onsite($payment_data) {
	extract($payment_data);
	global $wpdb;
// Included required files.

	require_once('EE_Usaepay.class.php');

	$settings = get_option('espresso_usaepay_onsite_settings');

	$ePay = new EE_umTransaction();
	
	$ePay->usesandbox = $settings['use_sandbox'];

	$ePay->key = $settings['key'];
	espresso_itemize_items($ePay, $attendee_id);
	$ePay->ip = $_SERVER['REMOTE_ADDR']; // IP address of the payer's browser.
	$ePay->card = $_POST['card_num']; // Required.  Credit card number.  No spaces or punctuation.
	$ePay->exp = $_POST['expmonth'] . $_POST['expyear']; // Required.  Credit card expiration date.  Format is MMYY
	$ePay->cvv2 = $_POST['cvv']; // Requirements determined by your account settings.  Security digits for credit card.

	$ePay->custemail = $_POST['email']; // Email address of payer.
	$ePay->cardholder = $_POST['first_name'] . ' ' . $_POST['last_name'];
	$ePay->street = $_POST['address']; // Required.  First street address.
	$ePay->zip = $_POST['zip'];
	$ePay->invoice = $attendee_id;
	$ePay->orderid = $attendee_session;
	$ePay->custid = $registration_id;
	
	$ePay->billfname = $_POST['first_name'];
	$ePay->billlname = $_POST['last_name'];
	$ePay->billstreet = $_POST['address'];
	$ePay->billcity = $_POST['city'];
	$ePay->billstate = $_POST['state'];
	$ePay->billcountry = 'US';
	$ePay->billzip = $_POST['zip'];
	$ePay->billphone = empty($_POST['phone']) ? '' : $_POST['phone'];
	$ePay->email = $_POST['email'];
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'USAePay';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	if ($ePay->Process()) {
		$payment_data['txn_id'] = $ePay->refnum;;
		$payment_data['txn_details'] = $ePay->rawresult;
		if ($ePay->resultcode == 'A')
			$payment_data['payment_status'] = 'Completed';
	} else {echo $ePay->error;
		?>
		<p><?php _e('There was no response from USAePay.', 'event_espresso'); ?></p>
		<?php
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}

function espresso_itemize_items($ePay, $attendee_id) {
	global $wpdb;
	$sql = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'";
	$session_id = $wpdb->get_var($sql);
	$sql = "SELECT a.final_price, a.quantity, ed.event_name, a.price_option, a.fname, a.lname ";
	$sql .= " FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$sql .= " WHERE attendee_session='" . $session_id . "' ORDER BY a.id ASC";
	$items = $wpdb->get_results($sql);
	
	$total_cost = 0;
	foreach ($items as $key=>$item) {
		$item_num=$key+1;
		$total_cost += $item->quantity * $item->final_price;
		$ePay->addLine($item_num, 'Attendee: '. $item->fname . ' ' . $item->lname, $item->price_option . ' for ' . $item->event_name, $item->final_price, $item->quantity, FALSE);
	}
	
	$ePay->amount = $total_cost;
}