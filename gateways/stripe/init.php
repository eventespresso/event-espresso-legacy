<?php
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
require_once($path . "/stripe_vars.php");
require_once($path . "/do_transaction.php");