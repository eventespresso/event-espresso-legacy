<?php

function espresso_display_moneris_hpp( $payment_data ) {

	global $wpdb, $org_options;
	
	extract($payment_data);
	
    if( ! class_exists( 'WP_Http' )) {
		require_once('EE_Moneris_HPP.class.php');
	}
	$EE_Moneris_HPP = new EE_Moneris_HPP();
	
	$moneris_hpp_settings = get_option('event_espresso_moneris_hpp_settings');

	if ( $moneris_hpp_settings['moneris_hpp_txn_mode'] != 'prod' ) {
		$EE_Moneris_HPP->enableTestMode();
	}

	do_action('action_hook_espresso_use_add_on_functions');

	// ps_store_id  	Identifies the configuration for the Hosted Paypage.
	// hpp_key  		This is a security key that corresponds to the ps_store_id.
	// charge_total  	Final purchase Amount - no $, must include 2 decimal places
	$EE_Moneris_HPP->addField( 'ps_store_id', $moneris_hpp_settings['moneris_hpp_ps_store_id'] );
	$EE_Moneris_HPP->addField( 'hpp_key', $moneris_hpp_settings['moneris_hpp_key'] );

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
		// idn  					Product Code - SKU (max 10 chars)
		// descriptionn  	Product Description - (max 15 chars)
		// quantityn  		Quantity of Goods Purchased - (max - 4 digits)
		// pricen  			Unit Price - (max - "7"."2" digits, i.e. min 0.00 & max 9999999.99)
		// subtotaln  		Quantity X Price of Product - ( max - "7"."2" digits, i.e. min 0.00 & max 9999999.99)		
		$EE_Moneris_HPP->addField( 'id' . $item_num, $item->reg_id );
		$EE_Moneris_HPP->addField( 'description' . $item_num, $item->price_option . ' for ' . $item->event_name . '. Attendee: '. $item->fname . ' ' . $item->lname );
		$EE_Moneris_HPP->addField( 'quantity' . $item_num, absint( $item->quantity ));
		$EE_Moneris_HPP->addField( 'amount_' . $item_num, $item->final_price );
		$EE_Moneris_HPP->addField( 'subtotal' . $item_num, $item->final_price * absint( $item->quantity ));	
	}


	// cust_id   	 			This is an ID field that can be used to identify the client  MAX 50 chars.
	// order_id   			MUST be unique per transaction MAX 50 chars.
	// lang   	 				en-ca = English  fr-ca = French
	// gst   	 				This is where you would include Goods and Services Tax charged,  (min 0.00 & max 9999999.99)
	// pst   	 				This is where you would include Provincial Sales Tax charged,  (min 0.00 & max 9999999.99)
	// hst   	 				This is where you would include Harmonized Sales Tax charged,  (min 0.00 & max 9999999.99)
	// shipping_cost   	This is where you would include shipping charges,  (min 0.00 & max 9999999.99)
	// note 		text 		This is any special instructions that you or the cardholder might like to store. MAX 50 chars.
	// email  		text 		Customer email address. MAX 50 chars. 

	$EE_Moneris_HPP->addField( '$attendee_id', $registration_id );
	$EE_Moneris_HPP->addField( 'order_id', $registration_id );

	$EE_Moneris_HPP->addField( 'lang', $moneris_hpp_settings['moneris_hpp_lang'] );
	$EE_Moneris_HPP->addField( 'email', $attendee_email );
	
	if ($moneris_hpp_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}

	$EE_Moneris_HPP->addField( 'return', $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id. '&type=moneris_hpp');
	$EE_Moneris_HPP->addField( 'cancel_return', $home . '/?page_id=' . $org_options['cancel_return']);
	$EE_Moneris_HPP->addField( 'notify_url', $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=moneris_hpp');

	$EE_Moneris_HPP->addField('currency_code', $moneris_hpp_cur);
	$EE_Moneris_HPP->addField('image_url', empty($moneris_hpp_settings['image_url']) ? '' : $moneris_hpp_settings['image_url']);
	
	$EE_Moneris_HPP->addField('no_shipping ', $no_shipping);
	$EE_Moneris_HPP->addField('first_name', $fname);
	$EE_Moneris_HPP->addField('last_name', $lname);
	$EE_Moneris_HPP->addField('address1', $address);
	$EE_Moneris_HPP->addField('city', $city);
	$EE_Moneris_HPP->addField('state', $state);
	$EE_Moneris_HPP->addField('zip', $zip);
	

	if ( empty( $moneris_hpp_settings['button_url'] )) {
		$moneris_hpp_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/pay-by-credit-card.png';
	} 
	$EE_Moneris_HPP->submitButton( $moneris_hpp_settings['button_url'], 'moneris_hpp' );


	if ( $moneris_hpp_settings['moneris_hpp_txn_mode'] != 'prod' ) {
		echo '
		<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Moneris Hosted Pay Page Debug Mode Is Turned On', 'event_espresso') . '</h3>
		<h5>Credit Card Test Numbers</h5>
		<p>
			MasterCard &nbsp; 5454545454545454
			Visa &nbsp; 4242424242424242
			Amex &nbsp; 373599005095005
			Diners &nbsp; 36462462742008		
		</p>';
		$EE_Moneris_HPP->dump_fields();
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_moneris_hpp');