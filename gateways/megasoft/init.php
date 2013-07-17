<?php
// This is for the gateway display
add_action( 'init', 'espresso_megasoft_enqueue_scripts' );
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
event_espresso_require_gateway("megasoft/payment.php");

// This is for the thank you page to process the transaction
if (!empty($_REQUEST['megasoft'])) {
	event_espresso_require_gateway("megasoft/return.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_megasoft_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_megasoft');
}

function espresso_megasoft_enqueue_scripts() {
	wp_register_script( 'megasoft', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/megasoft/megasoft.js', array( 'jquery', 'jquery.validate.js' ), '1.0', TRUE );
}
