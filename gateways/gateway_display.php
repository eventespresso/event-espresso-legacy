<?php

function espresso_display_onsite_payment_header() {
	echo '<div id="on_site_payment_container" class="payment_container event-display-boxes">';
	echo '<h3 id="on_site_payment" class="payment_option_title section-heading">' . __('On-site Payment Processing', 'event_espresso') . '</h3>';
}

function espresso_display_onsite_payment_footer() {
	echo '</div><!-- / #onsite-payments -->';
}

function espresso_display_offsite_payment_header() {
	echo '<div id="off_site_payment_container" class="payment_container event-display-boxes">';
	echo '<h3 id="off_site_payment" class="payment_option_title section-heading">' . __('Off-site Payments', 'event_espresso') . '</h3>';
	echo '<ul id="espresso_payment_buttons">';
}

function espresso_display_offsite_payment_footer() {
	echo '</ul>';
	echo '</div><!-- / #off_site_payment_container -->';
}

function espresso_display_offline_payment_header() {
	echo '<div id="off_line_payment_container" class="payment_container event-display-boxes">';
	echo '<h3 id="off_line_payment" class="payment_option_title section-heading">' . __('Off-line Payments', 'event_espresso') . '</h3>';
}

function espresso_display_offline_payment_footer() {
	echo '</div><!-- / #off_line_payment_container -->';
}

function espresso_display_finalize_payment_header($data) {
	global $org_options;
	?>
	<div class="event_espresso_attention event-messages ui-state-highlight">
		<span class="ui-icon ui-icon-alert"></span>
		<p><strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
	<?php _e('If using one of the offline payment options, please make note of the information below, then', 'event_espresso'); ?>
			<a href="<?php echo home_url() . '/?page_id=' . $org_options['return_url']; ?>&amp;payment_type=cash_check&amp;id=<?php echo $data['attendee_id'] . '&r_id=' . $data['registration_id'] ?>" class="inline-link" title="<?php _e('Finalize your registration', 'event_espresso'); ?>"><?php _e('click here to finalize your registration', 'event_espresso'); ?></a>
		</p>
	</div>
	<?php
}

$active_gateways = get_option('event_espresso_active_gateways', array());
foreach ($active_gateways as $gateway => $path) {
	event_espresso_require_gateway($gateway . "/init.php");
}
$data['fname'] = $fname;
$data['lname'] = $lname;
if (empty($attendee_email)) {
	$data['attendee_email'] = $email;
} else {
	$data['attendee_email'] = $attendee_email;
}
$data['address'] = $address;
$data['city'] = $city;
$data['state'] = $state;
$data['zip'] = $zip;
if (empty($event_cost)) {
	$data['event_cost'] = $total_cost;
} else {
	$data['event_cost'] = $event_cost;
}
$data['attendee_id'] = $attendee_id;
$data['event_id'] = $event_id;
$data['event_name'] = $event_name;
$data['registration_id'] = $registration_id;
$data['phone'] = $phone;
//This file builds the gateways that are available
echo '<div id="onsite-payments" class="event-display-boxes ui-widget">';
echo '<h3 class="section-heading ui-widget-header ui-corner-top">' . __('Please choose a payment option:', 'event_espresso') . '</h3>';
echo '<div class="event-data-display ui-widget-content ui-corner-bottom">';

do_action('action_hook_espresso_display_onsite_payment_header');
do_action('action_hook_espresso_display_onsite_payment_gateway', $data);
do_action('action_hook_espresso_display_onsite_payment_footer');

do_action('action_hook_espresso_display_offsite_payment_header');
do_action('action_hook_espresso_display_offsite_payment_gateway', $data);
do_action('action_hook_espresso_display_offsite_payment_footer');

do_action('action_hook_espresso_display_offline_payment_header');
do_action('action_hook_espresso_display_offline_payment_gateway', $data);
do_action('action_hook_espresso_display_finalize_payment_header', $data);
do_action('action_hook_espresso_display_offline_payment_gateway_2', $data);
do_action('action_hook_espresso_display_offline_payment_footer');


echo '</div><!-- / .event-data-display -->';
echo '</div><!-- / .event-display-boxes payment opts -->';
