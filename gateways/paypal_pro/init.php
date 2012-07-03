<?php
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
require_once($path . "/paypal_pro_vars.php");
if (!empty($_REQUEST['paypal_pro'])) {
	require_once($path . "/DoDirectPayment.php");
}