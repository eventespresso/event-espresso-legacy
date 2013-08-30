<?php

event_espresso_require_gateway("securepay_aus/securepay_aus_vars.php");

// And this is for the thank you page to process the transaction
if (!empty($_REQUEST['securepay_aus'])) {
	event_espresso_require_gateway("securepay_aus/DoDirectPayment.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_securepay_aus_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_securepay_aus');

}