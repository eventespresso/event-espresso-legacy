<?php

function espresso_display_paypal($payment_data) {
	extract($payment_data);
	global $wpdb;
	include_once ('Paypal.php');
	$myPaypal = new Paypal();
	echo '<!-- Event Espresso PayPal Gateway Version ' . $myPaypal->gateway_version . '-->';
	global $org_options;
	$paypal_settings = get_option('event_espresso_paypal_settings');
	$paypal_id = empty($paypal_settings['paypal_id']) ? '' : $paypal_settings['paypal_id'];
	$paypal_cur = empty($paypal_settings['currency_format']) ? '' : $paypal_settings['currency_format'];
	$no_shipping = isset($paypal_settings['no_shipping']) ? $paypal_settings['no_shipping'] : '0';
	$use_sandbox = $paypal_settings['use_sandbox'];
	if ($use_sandbox) {
		$myPaypal->enableTestMode();
	}

	$myPaypal->addField('business', $paypal_id);
	$myPaypal->addField('return', home_url() . '/?page_id=' . $org_options['return_url'] . '&id=' . $attendee_id);
	$myPaypal->addField('cancel_return', home_url() . '/?page_id=' . $org_options['cancel_return']);
	$myPaypal->addField('notify_url', home_url() . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment');
	$event_name = $wpdb->get_var('SELECT event_name FROM ' . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
	$myPaypal->addField('cmd', '_cart');
	$myPaypal->addField('upload', '1');
	$i = 1;
	$sql = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'";
	$session_id = $wpdb->get_var($sql);
	$sql = "SELECT amount_pd FROM " . EVENTS_ATTENDEE_TABLE . " WHERE attendee_session='" . $session_id . "'";
	$amount_pds = $wpdb->get_col($sql);
	$cost = 0;
	foreach ($amount_pds as $amount_pd) {
		$cost += $amount_pd;
	}
	$myPaypal->addField('item_name_' . $i, $event_name);
	$myPaypal->addField('amount_' . $i, $cost);
	$myPaypal->addField('quantity_' . $i, '1');
	$myPaypal->addField('currency_code', $paypal_cur);
	$myPaypal->addField('image_url', empty($paypal_settings['image_url']) ? '' : $paypal_settings['image_url']);
	$myPaypal->addField('no_shipping ', $no_shipping);
	$myPaypal->addField('first_name', $fname);
	$myPaypal->addField('last_name', $lname);
	$myPaypal->addField('email', $attendee_email);
	$myPaypal->addField('address1', $address);
	$myPaypal->addField('city', $city);
	$myPaypal->addField('state', $state);
	$myPaypal->addField('zip', $zip);

	if (!empty($paypal_settings['bypass_payment_page']) && $paypal_settings['bypass_payment_page'] == 'Y') {
		$myPaypal->submitPayment();
	} else {
		if (empty($paypal_settings['button_url'])) {
			if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/btn_stdCheckout2.gif")) {
				$button_url = EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/btn_stdCheckout2.gif";
			} else {
				$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/paypal/btn_stdCheckout2.gif";
			}
		} elseif (file_exists($paypal_settings['button_url'])) {
			$button_url = $paypal_settings['button_url'];
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/paypal/btn_stdCheckout2.gif";
		}
		$myPaypal->submitButton($button_url, 'paypal');
	}

	if ($use_sandbox) {

		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Paypal Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myPaypal->dump_fields();
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_paypal');