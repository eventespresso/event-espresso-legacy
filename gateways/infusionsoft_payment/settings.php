<?php
add_action('action_hook_espresso_display_gateway_settings','event_espresso_infusionsoft_payment_settings');

if ( ! function_exists( 'event_espresso_infusionsoft_payment_settings' )) {
	function event_espresso_infusionsoft_payment_settings() {}
}
