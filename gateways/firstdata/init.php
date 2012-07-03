<?php
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
require_once($path . "/firstdata_vars.php");
if (!empty($_REQUEST['firstdata'])) {
	require_once($path . "/Firstdata.php");
}