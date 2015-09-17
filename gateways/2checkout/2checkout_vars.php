<?php

function espresso_display_2checkout($payment_data) {
	extract($payment_data);
// Setup class
	include_once ('2checkout.php');
	echo '<!-- Event Espresso 2Checkout Gateway Version ' . $twocheckout_gateway_version . '-->';
	$my2checkout = new Espresso_TwoCo(); // initiate an instance of the class
	global $org_options, $wpdb;
//global $attendee_id;
	$twocheckout_settings = get_option('event_espresso_2checkout_settings');
	$twocheckout_id = empty($twocheckout_settings['2checkout_id']) ? 0 : $twocheckout_settings['2checkout_id'];
	$twocheckout_username = empty($twocheckout_settings['2checkout_username']) ? '' : $twocheckout_settings['2checkout_username'];
//$image_url = $2checkout_settings['button_url'];
	$twocheckout_cur = empty($twocheckout_settings['currency_format']) ? 'USD' : $twocheckout_settings['currency_format'];
	$no_shipping = empty($twocheckout_settings['no_shipping']) ? '' : $twocheckout_settings['no_shipping'];
	$use_sandbox = empty($twocheckout_settings['use_sandbox']) ? false : true;
	if ($use_sandbox) {
		// Enable test mode if needed
		$my2checkout->enableTestMode();
	}
	$session_id = $wpdb->get_var("SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'");
	$sql = "SELECT ed.id, ed.event_name, ed.event_desc, a.final_price, a.quantity FROM " . EVENTS_DETAIL_TABLE . " ed ";
	$sql .= " JOIN " . EVENTS_ATTENDEE_TABLE . " a ON ed.id=a.event_id ";
	//$sql .= " JOIN " . EVENTS_ATTENDEE_COST_TABLE . " ac ON a.id=ac.attendee_id ";
	$sql .= " WHERE a.attendee_session='$session_id'";
	$tickets = $wpdb->get_results($sql, ARRAY_A);
	$item_num = 0;
	foreach ($tickets as $ticket) {
		$my2checkout->addField('li_' . $item_num . '_type', 'product');
		$my2checkout->addField('li_' . $item_num . '_name', $ticket['event_name']);
		$my2checkout->addField('li_' . $item_num . '_quantity', $ticket['quantity']);
		$my2checkout->addField('li_' . $item_num . '_price', $ticket['final_price']);
		$my2checkout->addField('li_' . $item_num . '_tangible', 'N');
		$item_num++;
	}
	$my2checkout->addField('sid', $twocheckout_id);
	$my2checkout->addField('mode', '2CO');
	$my2checkout->addField('merchant_order_id', rand(1, 100));
	if ($twocheckout_settings['force_ssl_return']) {
		$return_url = str_replace("http://", "https://", get_permalink($org_options['return_url']));
	} else {
		$return_url = get_permalink($org_options['return_url']);
	}
	$my2checkout->addField('x_receipt_link_url', $return_url);
	$my2checkout->addField('id', $payment_data['attendee_id']);
	$my2checkout->addField('r_id',$payment_data['registration_id']);
	$my2checkout->addField('payment_method', '2checkout');
	$my2checkout->addField('attendee_action', 'post_payment');
	$my2checkout->addField('form_action', 'payment');
	$my2checkout->addField('currency_code', $twocheckout_cur);

//Enable this function if you want to send payment notification before the person has paid.
//This function is copied on the payment processing page
//event_espresso_send_payment_notification($attendee_id, $txn_id, $amount_pd);
//Decide if you want to auto redirect to your payment website or display a payment button.
	if (!empty($twocheckout_settings['bypass_payment_page']) && $twocheckout_settings['bypass_payment_page'] == 'Y') {
		$my2checkout->submitPayment(); //Enable auto redirect to payment site
	} else {
		$button_url = espresso_select_button_for_display($twocheckout_settings['button_url'], "2checkout/logo.png");
		$my2checkout->submitButton($button_url, '2checkout'); //Display payment button
		wp_deregister_script('jquery.validate.pack');
	}

	if ($use_sandbox) {
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __(' 2Checkout.com Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$my2checkout->dump_fields(); // for debugging, output a table of all the fields
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_2checkout');
