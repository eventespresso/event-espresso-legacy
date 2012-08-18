<?php

function espresso_display_usaepay_offsite($payment_data) {
	global $org_options;
	extract($payment_data);
	$settings = get_option('espresso_usaepay_offsite_settings');
	echo '<li><form  method="post" name="payment_form" action="https://sandbox.usaepay.com/interface/epayform/">';
	echo "<input type=\"hidden\" name=\"UMkey\" value=\"" . $settings['key'] . "\"/>\n";
	echo "<input type=\"hidden\" name=\"UMamount\" value=\"" . $event_cost . "\"/>\n";
	echo "<input type=\"hidden\" name=\"UMinvoice\" value=\"" . $attendee_id . "\"/>\n";
	echo "<input type=\"hidden\" name=\"UMredirApproved\" value=\"" . home_url() . '/?page_id=' . $org_options['return_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=usaepay_offsite' . "\"/>\n";
	echo '<input class="espresso_payment_button_usaepay_offsite" type="image" alt="Pay using USAePay" src="' . $settings['button_url'] . '" />';
	echo '</form></li>';
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_usaepay_offsite');
