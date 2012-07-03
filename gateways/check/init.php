<?php

add_action('action_hook_espresso_display_finalize_payment_header', 'espresso_display_finalize_payment_header');
add_action('action_hook_espresso_display_offline_payment_header', 'espresso_display_offline_payment_header');
add_action('action_hook_espresso_display_offline_payment_footer', 'espresso_display_offline_payment_footer');
require_once($path . "/check_payment_vars.php");
require_once($path . "/check_ipn.php");