<?php

function espresso_display_paypal($payment_data) {
	global $wpdb;
	extract($payment_data);
	include_once ('Paypal.php');
	$myPaypal = new EE_Paypal();
	echo '<!-- Event Espresso PayPal Gateway Version ' . $myPaypal->gateway_version . '-->';
	global $org_options;
	$paypal_settings = get_option('event_espresso_paypal_settings');

	//Check for an alternate PayPal email address
	if (isset($event_meta['paypal_email']) && !empty($event_meta['paypal_email']) && filter_var($event_meta['paypal_email'], FILTER_VALIDATE_EMAIL) != FALSE) {
		//Alternate PayPal email - using the paypal meta key field.
		$paypal_id = $event_meta['paypal_email'];
	} else {
		$paypal_id = empty($paypal_settings['paypal_id']) ? '' : $paypal_settings['paypal_id'];
	}

	$paypal_cur = empty($paypal_settings['currency_format']) ? '' : $paypal_settings['currency_format'];
	$no_shipping = isset($paypal_settings['no_shipping']) ? $paypal_settings['no_shipping'] : '0';
	if (!empty($event_meta['paypal_sandbox'])) {
		$use_sandbox = $event_meta['paypal_sandbox'];
	} else {
		$use_sandbox = $paypal_settings['use_sandbox'];
	}
	if ($use_sandbox) {
		$myPaypal->enableTestMode();
	}

	do_action('action_hook_espresso_use_add_on_functions');

	// get attendee_session
	$SQL = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id=%d";
	$session_id = $wpdb->get_var($wpdb->prepare($SQL, $attendee_id));
	// now get all registrations for that session
	$SQL = "SELECT a.final_price, a.orig_price, a.quantity, ed.event_name, a.price_option, a.fname, a.lname ";
	$SQL .= " FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$SQL .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$SQL .= " WHERE attendee_session=%s ORDER BY a.id ASC";

	$items = $wpdb->get_results($wpdb->prepare($SQL, $session_id));
	//printr( $items, '$items  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	foreach ($items as $key => $item) {

		//sanatize event name for paypal
	 	$item->event_name = espresso_sanitize_gateway_value( $item->event_name );
	 	$item->fname = espresso_sanitize_gateway_value( $item->fname );
	 	$item->lname = espresso_sanitize_gateway_value( $item->lname );
	 	$item->price_option = espresso_sanitize_gateway_value( $item->price_option );



		$item_num = $key + 1;
		$item_name = remove_accents( $item->price_option . ' ' . __('for', 'event_espresso') . ' ' . $item->event_name . ' ' . __('Attendee:', 'event_espresso') . ' ' . $item->fname . ' ' . $item->lname );
		$myPaypal->addField( 'item_name_' . $item_num, $item_name );
		$myPaypal->addField('quantity_' . $item_num, absint($item->quantity));

		if ($item->final_price < $item->orig_price) {
			$adjustment = abs($item->orig_price - $item->final_price);
			if (absint($item->quantity) > 1) {
				$adjustment = $adjustment * absint($item->quantity);
			}
			$myPaypal->addField('amount_' . $item_num, $item->orig_price);
			$myPaypal->addField('discount_amount_' . $item_num, $adjustment);
			//$myPaypal->addField('discount_amount2_' . $item_num, $adjustment);//Not sure this line is needed.
		} else {

			$myPaypal->addField('amount_' . $item_num, $item->final_price);
		}

		if (isset($paypal_settings['tax_override']) && $paypal_settings['tax_override'] == true) {
			$myPaypal->addField('tax_' . $item_num, '0.00');
		}
		if (isset($paypal_settings['shipping_override']) && $paypal_settings['shipping_override'] == true) {
			$myPaypal->addField('shipping_' . $item_num, '0.00');
		}
	}
	//printr( $myPaypal, '$myPaypal  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	$myPaypal->addField('business', $paypal_id);
	if ($paypal_settings['force_ssl_return']) {
		$scheme = 'https';
	} else {
		$scheme = NULL;
	}
	$myPaypal->addField('charset', "utf-8");
	$myPaypal->addField(
		'return',
		set_url_scheme(
			add_query_arg(
				array(
					'r_id'=>$registration_id,
					'type'=>'paypal'),
				get_permalink($org_options['return_url'] ) ),
			$scheme ) );
	$myPaypal->addField(
		'cancel_return',
		set_url_scheme(
			get_permalink(
				$org_options['cancel_return'] ),
			$scheme ) );
  $myPaypal->addField(  
		'notify_url', 
		set_url_scheme(
			add_query_arg(
				array(
					'r_id'=>$registration_id,
					'type'=>'paypal',
					'id'=>$attendee_id,
					'event_id'=>$event_id,
					'attendee_action'=>'post_payment',
					'form_action'=>'payment'),
				get_permalink($org_options['notify_url'] ) ),
			$scheme ) );
	$event_name = $wpdb->get_var('SELECT event_name FROM ' . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
	$myPaypal->addField('cmd', '_cart');
	$myPaypal->addField('bn', 'EventEspresso_SP');//EE3 will blow up if you change this.
	$myPaypal->addField('upload', '1');

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
		$button_url = espresso_select_button_for_display($paypal_settings['button_url'], "paypal/btn_stdCheckout2.gif");
		$myPaypal->submitButton($button_url, 'paypal');
	}

	if ($use_sandbox) {

		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('PayPal Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myPaypal->dump_fields();
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_paypal');
