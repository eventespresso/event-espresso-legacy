<?php
//a few globals
define('ESPRESSO_QBSM_DEV_APP_ID','245799785');
define('ESPRESSO_QBMS_DEV_APP_LOGIN','eventespresso.com2.eventespresso.com');
define('ESPRESSO_QBMS_LIVE_APP_ID','679873720');
define('ESPRESSO_QBMS_LIVE_APP_LOGIN','eventespresso.eventespresso.com');

//Display Gateway
add_action('action_hook_espresso_display_onsite_payment_header', 'espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer', 'espresso_display_onsite_payment_footer');
event_espresso_require_gateway("qbms/qbms_vars.php");

//Process Payment
if (!empty($_REQUEST['qbms'])) {
	event_espresso_require_gateway("qbms/do_transaction.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_qbms_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_qbms');
}

