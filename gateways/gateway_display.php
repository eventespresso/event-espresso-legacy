<?php

function espresso_display_onsite_payment_header() {
//	echo '<div id="on_site_payment_container" class="payment_container event-display-boxes">';
//	echo '<h3 id="on_site_payment" class="payment_option_title section-heading">' . __('On-site Payment Processing', 'event_espresso') . '</h3>';
}

function espresso_display_onsite_payment_footer() {
//	echo '</div><!-- / #onsite-payments -->';
}

function espresso_display_offsite_payment_header() {
//	echo '<div id="off_site_payment_container" class="payment_container event-display-boxes">';
//	echo '<h3 id="off_site_payment" class="payment_option_title section-heading">' . __('Off-site Payments', 'event_espresso') . '</h3>';
//	echo '<ul id="espresso_payment_buttons">';
}

function espresso_display_offsite_payment_footer() {
//	echo '</ul>';
//	echo '</div><!-- / #off_site_payment_container -->';
}

function espresso_display_offline_payment_header() {
//	echo '<div id="off_line_payment_container" class="payment_container event-display-boxes">';
	echo '<h3 id="off_line_payment" class="payment_option_title section-heading">' . __('Off-line Payment Options', 'event_espresso') . '</h3>';
}

function espresso_display_offline_payment_footer() {
//	echo '</div><!-- / #off_line_payment_container -->';
}



global $gateway_formal_names;
$gateway_formal_names = array();

$gateway_formal_names = apply_filters( 'action_hook_espresso_gateway_formal_name', $gateway_formal_names );

$data['fname'] = $fname;
$data['lname'] = $lname;
if (empty($attendee_email)) {
	$data['attendee_email'] = $email;
} else {
	$data['attendee_email'] = $attendee_email;
}
$data['address'] = isset($address) && !empty($address) ? $address : '';
$data['city'] = isset($city) && !empty($city) ? $city : '';
$data['state'] = isset($state) && !empty($state) ? $state : '';
$data['country'] = isset($country) && !empty($country) ? $country : '';
$data['country'] = isset($country_id) && !empty($country_id)  && $data['country'] == '' ? $country_id : '';
$data['zip'] = isset($zip) && !empty($zip) ? $zip : '';
if (empty($event_cost)) {
	$data['event_cost'] = $total_cost;
} else {
	$data['event_cost'] = $event_cost;
}
$data['attendee_id'] = $attendee_id;
$data['event_id'] = $event_id;
$data['event_name'] = isset($event_name) && !empty($event_name) ? $event_name : '';
$data['registration_id'] = $registration_id;
$data['phone'] = isset($phone) && !empty($phone) ? $phone : '';
$data['event_meta'] = event_espresso_get_event_meta($event_id);

//This file builds the gateways that are available
echo '<div id="payment-options-dv" class="event-display-boxes ui-widget">';
echo '<h2 class="section-heading ui-widget-header ui-corner-top">' . __('Please choose a payment option', 'event_espresso') . '</h2>';
echo '<div class="event-data-display ui-widget-content ui-corner-bottom">';

do_action('action_hook_espresso_display_onsite_payment_header');
do_action('action_hook_espresso_display_onsite_payment_gateway', $data);
do_action('action_hook_espresso_display_onsite_payment_footer');

do_action('action_hook_espresso_display_offsite_payment_header');
do_action('action_hook_espresso_display_offsite_payment_gateway', $data);
do_action('action_hook_espresso_display_offsite_payment_footer');

do_action('action_hook_espresso_display_offline_payment_header');
do_action('action_hook_espresso_display_offline_payment_gateway', $data);
do_action('action_hook_espresso_display_offline_payment_gateway_2', $data);
do_action('action_hook_espresso_display_offline_payment_footer');


echo '</div><!-- / .event-data-display -->';
echo '</div><!-- / .event-display-boxes payment opts -->';
echo '<p id="external-link-msg-pg"><img width="16" height="16" src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/icons/external-link.png" alt="click to visit this payment gateway">&nbsp;&nbsp;' . __('denotes an external link. clicking will take you to another website for payment processing.', 'event_espresso') . '</p>';

wp_register_script( 'espresso_payment_page', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/espresso_payment_page.js', array( 'jquery' ), '1.0', TRUE );
wp_enqueue_script( 'espresso_payment_page' );

