<?php
add_action('action_hook_espresso_display_offsite_payment_header','espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer','espresso_display_offsite_payment_footer');
require_once($path . "/worldpay_vars.php");
require_once($path . "/worldpay_ipn.php");