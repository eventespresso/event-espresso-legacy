<?php
// This is for the payment display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("quickpay/quickpay_vars.php");



// This is for the return from QuickPay's servers
if ( isset( $_POST['qpstat'] )) {	
	
	global $wpdb;
	event_espresso_require_gateway("quickpay/quickpaypaymentprocess.php");
	$qpstat = espresso_verify_quickpay_status_code( $_POST['qpstat'] );

	// track POST data
//	$wpdb->insert( 
//		EVENTS_ATTENDEE_TABLE, 
//		array(  'registration_id' => __LINE__,  'lname' =>basename( dirname(__FILE__) ),  'fname' => basename( __FILE__ ),  'transaction_details' => serialize( $_POST ) . ' qpstat = ' . $qpstat ), 
//		array(  '%s',  '%s',  '%s',  '%s'  ) 
//	);	

	if ( $qpstat ) {
		add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_quickpay_get_attendee_id');
		add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_quickpay');
//		add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_quickpay');
	}
}

