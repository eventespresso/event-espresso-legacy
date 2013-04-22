<?php

function espresso_process_infusionsoft($payment_data) {
	extract($payment_data);
	global $wpdb, $org_options;

	require_once 'isdk.php';



	$infusionsoft_settings = get_option('espresso_infusionsoft_settings');
	
//start transaction
	echo '<!--Event Espresso Infusionsoft Gateway Version ' . $transaction->gateway_version . '-->';
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
	
	$payment_data['txn_type'] = 'Infusionsoft';
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = 'No response from Infusionsoft';
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
//Capture response
	$response = $transaction->authorizeAndCapture();


	if (!empty($response)) {
		if ($infusionsoft_settings['use_sandbox']) {
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
		<p><?php _e('There was no response from Infusionsoft.', 'event_espresso'); ?></p>
		<?php
	}
	return $payment_data;
}
