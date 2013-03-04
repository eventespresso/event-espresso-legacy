<?php

function espresso_transactions_luottokunta_get_attendee_id($attendee_id) {
	global $wpdb;
	if (!empty($_REQUEST['r_id'])) {
		$reg_id = $_REQUEST['r_id'];
		$attendee_id=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}events_attendee WHERE registration_id=%s LIMIT 1",$reg_id));
	}
	return $attendee_id;
}

function espresso_process_luottokunta($payment_data) {
	//on all requests, GET parameters present should be:
	//r_id which is the attendee's registration_id. we shouldn't use this thuogh, as we already ahve the registration id in $payment_data['registration_id']
	//type which should be set to luottokunta. we should have already checked that this is set to 'luottokunta'
	if(!array_key_exists('type',$_GET) || 'luottokunta'!=$_GET['type']){
		return $payment_data;
	}
	//order_id which is a string which is totally unique for every request sent to luottokunta
	$order_id= $_GET['order_id'];
	//success which is set to eitehr 1 or 0, depending on whether the payment was successful or not
	$success = $_GET['success'];
	//to teh failure url, we expect to receive
	//LKPRC primary error code
	//LKSRC secondary error code
	//LKMSGTXT basic error message text
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

	$payment_data['txn_type'] = 'Luottokunta';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);
	//http://monkey.com/?TransTime=Thu%20Feb%2021%2013:48:45%20EST%202013&OrderID=2013022113484500514&TransactionType=SALE&Approved=APPROVED&ReturnCode=Y:123456:0abcdef:::NYY&ErrMsg=&TaxTotal=15.00&ShipTotal=6.00&SubTotal=1305.00&FullTotal=1326.00&PaymentType=CC&CardNumber=......1111&TransRefNumber=1befb0f25f3fee22&CardIDResult=&AVSResult=&CardAuthNumber=123456&CardRefNumber=0abcdef&CardType=VISA&IPResult=NYY&IPCountry=CA&IPRegion=Ontario&IPCity=Toronto&CustomerRefNo=123456789
	//check that 'Err' is empty
	if(!$success){
		$display_text='2900'!=$_GET['LKSRC'] ? $_GET['LKMSGTXT'] : __('The card number, validity, or card verification code is invalid. This may also appear when double-authorization has occurred.','event_espresso');?>
		<h2>Payment Declined</h2>
		<p><strong class="credit_card_failure"><?php echo $display_text?></strong></p>
		<p><strong class="credit_card_failure"><?php _e('Please try again','event_espresso')?></strong></p>
		<p><?php printf(__("Order ID: %s, Primary Error code: %s, Secondary Error code: %s",'event_espresso'), $order_id, $_GET['LKPRC'],$_GET['LKSRC'])?></p>
		<p><?php _e("If this error persists, you may want to contact the site owners and provide them with the above data.",'event_espresso');?></p>
		<?php
	}else{
		$payment_data['txn_id']=$order_id;
		$payment_data['payment_status']='Completed';
	}
	return $payment_data;
}


