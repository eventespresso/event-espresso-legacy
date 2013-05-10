<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
//echo '<h3>'. basename( __FILE__ ) . ' LOADED <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';

// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway( 'moneris_hpp/moneris_hpp_vars.php' );


// This is for the transaction processing
event_espresso_require_gateway( 'moneris_hpp/moneris_hpp_ipn.php' );

// check for moneris response and that response was posted within the last 15 minutes
if ( isset( $_POST['rvar_moneris_hpp'] ) && $_POST['rvar_moneris_hpp'] <= time() && $_POST['rvar_moneris_hpp'] >= ( time() - 900 )) {
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_moneris_hpp_get_attendee_id');
//	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_moneris_hpp');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_moneris_hpp');
}

function espresso_moneris_hpp_formal_name( $gateway_formal_names ) {
	$gateway_formal_names['moneris_hpp'] = 'Moneris Hosted Pay Page';
	return $gateway_formal_names;
}
add_filter( 'action_hook_espresso_gateway_formal_name', 'espresso_moneris_hpp_formal_name', 10, 1 );

function espresso_moneris_hpp_payment_type( $gateway_payment_types ) {
	$gateway_payment_types['moneris_hpp'] = 'Moneris Hosted Pay Page / Credit Card';
	return $gateway_payment_types;
}
add_filter( 'action_hook_espresso_gateway_payment_type', 'espresso_moneris_hpp_payment_type', 10, 1 );
