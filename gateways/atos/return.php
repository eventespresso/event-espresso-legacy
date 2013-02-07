<?php

function espresso_transactions_atos_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_atos($payment_data) {
	$settings = get_option('event_espresso_atos_settings');
	$parm = " 'pathfile=".dirname(__FILE__).DS.$settings['provider'].DS."pathfile'";
	$parm .= " message=" . $_POST['DATA'];
	$path_bin = dirname(__FILE__).DS.'bin'.DS.'response';
	$command = "$path_bin $parm";
	$sips_raw_resp = shell_exec("$command 2>&1");
	$hash = array ();
	$sips_resp = split ( "!", $sips_raw_resp );
	list (,
	$hash['code'],
	$hash['error'],
	$hash['merchant_id'],
	$hash['merchant_country'],
	$hash['amount'],
	$hash['transaction_id'],
	$hash['payment_means'],
	$hash['transmission_date'],
	$hash['payment_time'],
	$hash['payment_date'],
	$hash['response_code'],
	$hash['payment_certificate'],
	$hash['authorisation_id'],
	$hash['currency_code'],
	$hash['card_number'],
	$hash['cvv_flag'],
	$hash['cvv_response_code'],
	$hash['bank_response_code'],
	$hash['complementary_code'],
	$hash['return_context'],
	$hash['caddie'],
	$hash['receipt_complement'],
	$hash['merchant_language'],
	$hash['language'],
	$hash['customer_id'],
	$hash['order_id'],
	$hash['customer_email'],
	$hash['customer_ip_address'],
	$hash['capture_day'],
	$hash['capture_mode'],
	$hash['data']
	) = $sips_resp;

	$hash['command'] = $command;
	$hash['output']  = $sips_resp;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$payment_data['txn_type'] = 'Atos';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);
	if (!empty($hash)) {
		$payment_data['txn_details'] = serialize($hash);
		$payment_data['txn_id'] = $hash['transaction_id'];
		if (($hash['amount']/100) == $payment_data['total_cost'] && $hash['response_code'] == '00') {
			$payment_data['payment_status'] = 'Completed';
			//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
		}
	}
	return $payment_data;
}