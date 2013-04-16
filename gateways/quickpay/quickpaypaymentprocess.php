<?php

function espresso_transactions_quickpay_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_quickpay($payment_data) {
//	echo '<h3>'. __CLASS__ . '->' . __FUNCTION__ . ' <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';
//	printr( $_REQUEST, '$_REQUEST  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
	global $wpdb;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_id'] = trim(stripslashes($_GET['transaction_id']));
	$payment_data['txn_type'] = 'QuickPay';
	$payment_data['payment_status'] = 'Incomplete';
	$quickpay_settings = get_option('event_espresso_quickpay_settings');
	$sql = "SELECT txn_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id=%d";
	$txn_id = $wpdb->get_var( $wpdb->prepare( $sql, $payment_data['attendee_id'] ));
//	echo '<h4>' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	$wpdb->insert( 
//		EVENTS_ATTENDEE_TABLE, 
//		array(  'registration_id' => __LINE__,  'lname' =>basename( __FILE__ ),  'fname' => __FUNCTION__ ), 
//		array(  '%s',  '%s',  '%s'  ) 
//	);	
//	echo '<h4>' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

	if ($_GET['chronopay_callback'] == 'true') {
		$sessionid = trim(stripslashes($_GET['sessionid']));
//		echo '<h4>txn_id from QP: ' . $payment_data['txn_id'] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//		echo '<h4>md5secret from QP: ' . $quickpay_settings['quickpay_md5secret'] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//		echo '<h4>md5 from QP: ' . md5($payment_data['txn_id'] . $quickpay_settings['quickpay_md5secret']). '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//		echo '<h4>txn_id from DB : ' . $txn_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
		if ( md5( $payment_data['txn_id'] . $quickpay_settings['quickpay_md5secret'] ) ==  md5( $txn_id . $quickpay_settings['quickpay_md5secret'] )) {
			$payment_data['payment_status'] = 'Completed';
		}
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
