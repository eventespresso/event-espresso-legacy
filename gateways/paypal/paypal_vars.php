<?php

function espresso_display_paypal($payment_data) {
	extract($payment_data);
	global $wpdb;
	include_once ('Paypal.php');
	$myPaypal = new EE_Paypal();
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
	if ($paypal_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	$myPaypal->addField('charset', "utf-8");
	$myPaypal->addField('return', $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id. '&type=paypal');
	$myPaypal->addField('cancel_return', $home . '/?page_id=' . $org_options['cancel_return']);
	$myPaypal->addField('notify_url', $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=paypal');
	$event_name = $wpdb->get_var('SELECT event_name FROM ' . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
	$myPaypal->addField('cmd', '_cart');
	$myPaypal->addField('upload', '1');
	$sql = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'";
	$session_id = $wpdb->get_var($sql);
	$sql = "SELECT ac.cost, ac.quantity, ed.event_name, a.price_option, a.fname, a.lname, dc.coupon_code_price, dc.use_percentage, gc.id FROM " . EVENTS_ATTENDEE_COST_TABLE . " ac JOIN " . EVENTS_ATTENDEE_TABLE . " a ON ac.attendee_id=a.id JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$sql .= " LEFT JOIN " . EVENTS_DISCOUNT_CODES_TABLE . " dc ON a.coupon_code=dc.coupon_code ";
	$sql .= " LEFT JOIN " . EVENTS_GROUPON_CODES_TABLE . " gc ON a.id=gc.attendee_id AND gc.groupon_status='0' ";
	$sql .= " WHERE attendee_session='" . $session_id . "'";
	$items = $wpdb->get_results($sql);
	$coupon_amount = empty($items[0]->coupon_code_price) ? 0 : $items[0]->coupon_code_price;
	$is_coupon_pct = (!empty($items[0]->use_percentage) && $items[0]->use_percentage=='Y') ? true : false;
	$groupon_used = false;
	foreach ($items as $key=>$item) {
		$item_num=$key+1;
		if (!empty($item->id)) {
			$groupon_text = ' (groupon code used)';
			$groupon_used = true;
			$item_cost = "0.00";
		} else {
			$groupon_text = '';
			$item_cost = $item->cost;
		}
		$myPaypal->addField('item_name_' . $item_num, $item->price_option . ' for ' . $item->event_name . '. Attendee: '. $item->fname . ' ' . $item->lname . $groupon_text);
		$myPaypal->addField('amount_' . $item_num, $item_cost);
		$myPaypal->addField('quantity_' . $item_num, $item->quantity);
	}
	if (!empty($coupon_amount) && !$groupon_used) {
		if ($is_coupon_pct) {
			$myPaypal->addField('discount_rate_cart', $coupon_amount);
		} else {
			$myPaypal->addField('discount_amount_cart', $coupon_amount);
		}
	}
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
		} elseif (isset($paypal_settings['button_url'])) {
			$button_url = $paypal_settings['button_url'];
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/paypal/btn_stdCheckout2.gif";
		}
		$myPaypal->submitButton($button_url, 'paypal');
	}

	if ($use_sandbox) {

		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('PayPal Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myPaypal->dump_fields();
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_paypal');