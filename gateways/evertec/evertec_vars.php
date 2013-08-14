<?php

function espresso_display_evertec($payment_data) {
	global $wpdb;
	extract($payment_data);
	include_once ('Evertec.php');
	$myEvertec = new EE_Evertec();
	echo '<!-- Event Espresso Evertec Gateway Version ' . $myEvertec->gateway_version . '-->';
	global $org_options;
	$evertec_settings = get_option('event_espresso_evertec_settings');
	
	//Check for an alternate Evertec email address
	if (isset($event_meta['evertec_email']) && !empty($event_meta['evertec_email']) && filter_var($event_meta['evertec_email'], FILTER_VALIDATE_EMAIL) != FALSE) {
		//Alternate Evertec email - using the evertec meta key field.
		$evertec_id = $event_meta['evertec_email'];
	} else {
		$evertec_id = empty($evertec_settings['evertec_id']) ? '' : $evertec_settings['evertec_id'];
	}
	
	$evertec_cur = empty($evertec_settings['currency_format']) ? '' : $evertec_settings['currency_format'];
	$no_shipping = isset($evertec_settings['no_shipping']) ? $evertec_settings['no_shipping'] : '0';
	$use_sandbox = $evertec_settings['use_sandbox'];
	if ($use_sandbox) {
		$myEvertec->enableTestMode();
	}

	do_action('action_hook_espresso_use_add_on_functions');

	// get attendee_session
	$SQL = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id=%d";
	$session_id = $wpdb->get_var( $wpdb->prepare( $SQL, $attendee_id ));
	// now get all registrations for that session
	$SQL = "SELECT a.final_price, a.orig_price, a.quantity, ed.event_name, a.price_option, a.fname, a.lname ";
	$SQL .= " FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$SQL .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$SQL .= " WHERE attendee_session=%s ORDER BY a.id ASC";
	
	$items = $wpdb->get_results( $wpdb->prepare( $SQL, $session_id ));
	//printr( $items, '$items  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
					
	foreach ( $items as $key => $item ) {	
	
		$item_num=$key+1;
		$myEvertec->addField('item_name_' . $item_num, $item->price_option . ' for ' . $item->event_name . '. Attendee: '. $item->fname . ' ' . $item->lname);
		$myEvertec->addField('quantity_' . $item_num, absint($item->quantity));

		if ( $item->final_price < $item->orig_price ) {
			$adjustment = abs( $item->orig_price - $item->final_price );
			if (absint($item->quantity) > 1){
				$adjustment = $adjustment * absint($item->quantity);
			}
			$myEvertec->addField('amount_' . $item_num, $item->orig_price);
			$myEvertec->addField('discount_amount_' . $item_num, $adjustment);
			//$myEvertec->addField('discount_amount2_' . $item_num, $adjustment);//Not sure this line is needed.
			
		} else {

			$myEvertec->addField('amount_' . $item_num, $item->final_price);
		}
		
		if (isset($evertec_settings['tax_override']) && $evertec_settings['tax_override'] == true) {
			$myEvertec->addField('tax_'.$item_num, '0.00');
		}
		if (isset($evertec_settings['shipping_override']) && $evertec_settings['shipping_override'] == true) {
			$myEvertec->addField('shipping_'.$item_num, '0.00');
		}
	
	}
	//printr( $myEvertec, '$myEvertec  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	$myEvertec->addField('business', $evertec_id);
	if ($evertec_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	$myEvertec->addField('charset', "utf-8");
	$myEvertec->addField('return', $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id. '&type=evertec');
	$myEvertec->addField('cancel_return', $home . '/?page_id=' . $org_options['cancel_return']);
	$myEvertec->addField('notify_url', $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=evertec');
	$event_name = $wpdb->get_var('SELECT event_name FROM ' . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
	$myEvertec->addField('cmd', '_cart');
	$myEvertec->addField('upload', '1');

	$myEvertec->addField('currency_code', $evertec_cur);
	$myEvertec->addField('image_url', empty($evertec_settings['image_url']) ? '' : $evertec_settings['image_url']);
	$myEvertec->addField('no_shipping ', $no_shipping);
	$myEvertec->addField('first_name', $fname);
	$myEvertec->addField('last_name', $lname);
	$myEvertec->addField('email', $attendee_email);
	$myEvertec->addField('address1', $address);
	$myEvertec->addField('city', $city);
	$myEvertec->addField('state', $state);
	$myEvertec->addField('zip', $zip);
		
	if (!empty($evertec_settings['bypass_payment_page']) && $evertec_settings['bypass_payment_page'] == 'Y') {
		$myEvertec->submitPayment();
	} else {
		if (empty($evertec_settings['button_url'])) {
			if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/evertec/btn_stdCheckout2.gif")) {
				$button_url = EVENT_ESPRESSO_GATEWAY_DIR . "/evertec/btn_stdCheckout2.gif";
			} else {
				$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/evertec/btn_stdCheckout2.gif";
			}
		} elseif (isset($evertec_settings['button_url'])) {
			$button_url = $evertec_settings['button_url'];
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/evertec/btn_stdCheckout2.gif";
		}
		$myEvertec->submitButton($button_url, 'evertec');
	}

	if ($use_sandbox) {

		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Evertec Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myEvertec->dump_fields();
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_evertec');