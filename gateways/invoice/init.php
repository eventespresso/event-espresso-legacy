<?php
// This is for the gateway display
$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');
if ($invoice_payment_settings['show'] != 'N') {
	add_action('action_hook_espresso_display_offline_payment_header', 'espresso_display_offline_payment_header');
	add_action('action_hook_espresso_display_offline_payment_footer', 'espresso_display_offline_payment_footer');
	event_espresso_require_gateway("invoice/invoice_vars.php");
}