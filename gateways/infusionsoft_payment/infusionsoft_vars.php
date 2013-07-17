<?php
add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_infusionsoft_payment_form');

if ( ! function_exists( 'espresso_display_infusionsoft_payment_form' )) {
	function espresso_display_infusionsoft_payment_form() {}
}