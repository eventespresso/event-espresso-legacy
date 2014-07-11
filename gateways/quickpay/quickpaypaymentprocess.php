<?php

function espresso_transactions_quickpay_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_quickpay($payment_data) {
	
	$quickpay_settings = get_option('event_espresso_quickpay_settings');

	$msgtype		= isset( $_POST['msgtype'] ) ? sanitize_text_field( $_POST['msgtype'] ) : '';
	$ordernumber	= isset( $_POST['ordernumber'] ) ? sanitize_text_field( $_POST['ordernumber'] ) : '';
	$amount 		= isset( $_POST['amount'] ) ? sanitize_text_field( $_POST['amount'] ) : '';
	$currency 		= isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';
	$time	 		= isset( $_POST['time'] ) ? sanitize_text_field( $_POST['time'] ) : '';
	$state			= isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
	$qpstat			= isset( $_POST['qpstat'] ) ? sanitize_text_field( $_POST['qpstat'] ) : '';
	$qpstatmsg		= isset( $_POST['qpstatmsg'] ) ? sanitize_text_field( $_POST['qpstatmsg'] ) : '';
	$chstat 		= isset( $_POST['chstat'] ) ? sanitize_text_field( $_POST['chstat'] ) : '';
	$chstatmsg		= isset( $_POST['chstatmsg'] ) ? sanitize_text_field( $_POST['chstatmsg'] ) : '';
	$merchant		= isset( $_POST['merchant'] ) ? sanitize_text_field( $_POST['merchant'] ) : '';
	$merchantemail	= isset( $_POST['merchantemail'] ) ? sanitize_text_field( $_POST['merchantemail'] ) : '';
	$transaction	= isset( $_POST['transaction'] ) ? sanitize_text_field( $_POST['transaction'] ) : '';
	$cardtype		= isset( $_POST['cardtype'] ) ? sanitize_text_field( $_POST['cardtype'] ) : '';
	$cardnumber		= isset( $_POST['cardnumber'] ) ? sanitize_text_field( $_POST['cardnumber'] ) : '';
	$cardhash		= isset( $_POST['cardhash'] ) ? sanitize_text_field( $_POST['cardhash'] ) : '';
	$cardexpire		= isset( $_POST['cardexpire'] ) ? sanitize_text_field( $_POST['cardexpire'] ) : '';
	$acquirer		= isset( $_POST['acquirer'] ) ? sanitize_text_field( $_POST['acquirer'] ) : '';
	$splitpayment	= isset( $_POST['splitpayment'] ) ? sanitize_text_field( $_POST['splitpayment'] ) : '';
	$fraudprobability	= isset( $_POST['fraudprobability'] ) ? sanitize_text_field( $_POST['fraudprobability'] ) : '';
	$fraudremarks	= isset( $_POST['fraudremarks'] ) ? sanitize_text_field( $_POST['fraudremarks'] ) : '';
	$fraudreport	= isset( $_POST['fraudreport'] ) ? sanitize_text_field( $_POST['fraudreport'] ) : '';
	$fee		= isset( $_POST['fee'] ) ? sanitize_text_field( $_POST['fee'] ) : '';
	
	// concatenate above values,  add our SECRET QUIICKPAY SALT value, then  md5 the whole thing
	$response_md5_check = md5( $msgtype . $ordernumber . $amount . $currency . $time . $state . $qpstat . $qpstatmsg . $chstat . $chstatmsg . $merchant . $merchantemail . $transaction . $cardtype . $cardnumber . $cardhash . $cardexpire . $acquirer . $splitpayment . $fraudprobability . $fraudremarks . $fraudreport . $fee . $quickpay_settings['quickpay_md5secret'] );
	
	// if our md5 check value matches QuickPay's md5 check value, then nobody haxor'd  the data
	// oh... and the TXN also has to have been approved
	if ( $_POST['md5check'] == $response_md5_check && $qpstat == '000' ) {
		$payment_data['amount_pd'] 			= number_format( (float)$amount/100, 2 );
		$payment_data['payment_status'] 	= 'Completed';
	} else {
		$payment_data['amount_pd'] 			= 0.00;
	}
	
	$payment_data['txn_type'] 	= $cardtype;
	$payment_data['txn_id'] 	= $transaction;
	$payment_data['txn_details'] = serialize( $_POST );

	return $payment_data;

}


function espresso_verify_quickpay_status_code( $code = FALSE ) {
	// can't verify something if you don't send it to us!!
	if ( ! $code ) {
		return FALSE;
	}
	
	$quickpay_status_codes = array(
		'000' => 'Approved.',
		'001' => "Rejected by acquirer. See field 'chstat' and 'chstatmsg' for further explanation.",
		'002' => 'Communication error.',
		'003' => 'Card expired.',
		'004' => 'Transition is not allowed for transaction current state.',
		'005' => 'Authorization is expired.',
		'006' => 'Error reported by acquirer.',
		'007' => 'Error reported by QuickPay.',
		'008' => 'Error in request data.',
		'009' => 'Payment aborted by shopper'
	);	
	$is_quickpay_status_code = array_key_exists( $code, $quickpay_status_codes );
	return $is_quickpay_status_code;	 
}

// EXAMPLE OF POST DATA RETURNED FROM QUICKPAY
/*
a:16:{
	s:7:"msgtype";s:7:"capture";
	s:11:"ordernumber";s:15:"2-516ef44e2067f";
	s:6:"amount";s:4:"9995";
	s:8:"currency";s:3:"USD";
	s:4:"time";s:12:"130417211330";
	s:5:"state";s:1:"3";
	s:6:"qpstat";s:3:"000";
	s:9:"qpstatmsg";s:2:"OK";
	s:6:"chstat";s:3:"000";
	s:9:"chstatmsg";s:2:"OK";
	s:8:"merchant";s:19:"Quickpay Test konto";
	s:13:"merchantemail";s:17:"demo@quickpay.net";
	s:11:"transaction";s:8:"58226954";
	s:8:"cardtype";s:13:"mastercard-dk";
	s:10:"cardnumber";s:16:"XXXXXXXXXXXX0005";
	s:8:"md5check";s:32:"2d3216fc431ec95737b78a87ad3d8e3b";
} 
*/