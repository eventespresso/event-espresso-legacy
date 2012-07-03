<?php
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
require_once($path . "/paytrace_vars.php");
if (!empty($_POST['paytrace'])) {
	require_once($path . "/do_transaction.php");
}