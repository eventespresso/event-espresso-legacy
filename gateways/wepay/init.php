<?php
add_action('action_hook_espresso_display_offsite_payment_header','espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer','espresso_display_offsite_payment_footer');
require_once($path . "/wepay_vars.php");
require_once($path . "/wepay_ipn.php");
if(!empty($_GET['checkout_id'])) {
add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_wepay_get_attendee_id');
add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_wepay');
add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_wepay_callback');
}