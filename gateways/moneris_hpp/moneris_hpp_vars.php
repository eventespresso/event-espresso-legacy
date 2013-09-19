<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
//echo '<h3>'. basename( __FILE__ ) . ' LOADED <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';

function espresso_display_moneris_hpp( $payment_data ) {

	global $wpdb, $org_options;
	
	extract($payment_data);

	if( ! class_exists( 'EE_Moneris_HPP' )) {
		event_espresso_require_gateway( 'moneris_hpp/EE_Moneris_HPP.class.php');
	}
	$EE_Moneris_HPP = new EE_Moneris_HPP();
	
	if ( $EE_Moneris_HPP->settings['moneris_hpp_txn_mode'] != 'prod' ) {
		$EE_Moneris_HPP->enableTestMode();
	}

	do_action('action_hook_espresso_use_add_on_functions');

	// ps_store_id  	Identifies the configuration for the Hosted Paypage.
	$EE_Moneris_HPP->addField( 'ps_store_id', $EE_Moneris_HPP->settings['moneris_hpp_ps_store_id'] );
	// hpp_key  		This is a security key that corresponds to the ps_store_id.
	$EE_Moneris_HPP->addField( 'hpp_key', $EE_Moneris_HPP->settings['moneris_hpp_key'] );
	// the time the transaction was initiated
	$EE_Moneris_HPP->addField( 'rvar_moneris_hpp', time() );

	// lang   	 				en-ca = English  fr-ca = French
	// note 		text 		This is any special instructions that you or the cardholder might like to store. MAX 50 chars.
	$EE_Moneris_HPP->addField( 'lang', $EE_Moneris_HPP->settings['moneris_hpp_lang'] );
	switch ( $EE_Moneris_HPP->settings['moneris_hpp_country'] ) {
		case 'us' :
				$currency = 'USD';
			break;
		default :
				$currency = 'CAD';
	}	

	$EE_Moneris_HPP->addField('currency_code', $currency );


	// get attendee_session
	$SQL = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id=%d";
	$session_id = $wpdb->get_var( $wpdb->prepare( $SQL, $attendee_id ));
	// now get all registrations for that session
	$SQL = "SELECT a.id, a.registration_id, a.final_price, a.orig_price, a.quantity, a.price_option, a.fname, a.lname, ed.event_name";
	$SQL .= " FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$SQL .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$SQL .= " WHERE attendee_session=%s ORDER BY a.id ASC";
	
	$items = $wpdb->get_results( $wpdb->prepare( $SQL, $session_id ));
	//printr( $items, '$items  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
	
	$total = 0;					
	$paid = 0;
	foreach ( $items as $item_num => $item ) {		
		$item_num++;
		// if this the primary attendee
		if ( $item_num == 1 ) {
			// cust_id   		This is an ID field that can be used to identify the client  MAX 50 chars.
			$EE_Moneris_HPP->addField( 'cust_id', $registration_id );
			// order_id  	MUST be unique per transaction MAX 50 chars.
//			$EE_Moneris_HPP->addField( 'order_id', $registration_id );
			// email  			Customer email address. MAX 50 chars. 
			$EE_Moneris_HPP->addField( 'email', $attendee_email );		
		}
		// idn  					Product Code - SKU (max 10 chars)
		$EE_Moneris_HPP->addField( 'id' . $item_num, $item->id );
		// descriptionn  	Product Description - (max 15 chars)
		$EE_Moneris_HPP->addField( 'description' . $item_num, $item->event_name . '. Attendee: '. $item->fname . ' ' . $item->lname );
		// quantityn  		Quantity of Goods Purchased - (max - 4 digits)
		$EE_Moneris_HPP->addField( 'quantity' . $item_num, absint( $item->quantity ));
		// pricen  			Unit Price - (max - "7"."2" digits, i.e. min 0.00 & max 9999999.99)
		$EE_Moneris_HPP->addField( 'price' . $item_num, number_format( $item->final_price, 2, '.', '' ));
		// subtotaln  		Quantity X Price of Product - ( max - "7"."2" digits, i.e. min 0.00 & max 9999999.99)		
		$EE_Moneris_HPP->addField( 'subtotal' . $item_num, number_format( $item->final_price * absint( $item->quantity ), 2, '.', '' ));	
		$total += $item->final_price * absint( $item->quantity );
		$paid += $item->amount_pd;
	}


	if ( (float)$paid > 0 ) {
		// idn  					Product Code - SKU (max 10 chars)
		$EE_Moneris_HPP->addField( 'id' . $item_num, '' );
		// descriptionn  	Product Description - (max 15 chars)
		$EE_Moneris_HPP->addField( 'description' . $item_num, 'Total paid to date' );
		// quantityn  		Quantity of Goods Purchased - (max - 4 digits)
		$EE_Moneris_HPP->addField( 'quantity' . $item_num, 1 );
		// pricen  			Unit Price - (max - "7"."2" digits, i.e. min 0.00 & max 9999999.99)
		$EE_Moneris_HPP->addField( 'price' . $item_num, number_format( $paid * -1, 2, '.', '' ));
		// subtotaln  		Quantity X Price of Product - ( max - "7"."2" digits, i.e. min 0.00 & max 9999999.99)		
		$EE_Moneris_HPP->addField( 'subtotal' . $item_num, number_format( $paid * -1, 2, '.', '' ));			
	}
	// gst   	 				This is where you would include Goods and Services Tax charged,  (min 0.00 & max 9999999.99)
	// pst   	 				This is where you would include Provincial Sales Tax charged,  (min 0.00 & max 9999999.99)
	// hst   	 				This is where you would include Harmonized Sales Tax charged,  (min 0.00 & max 9999999.99)

	$total = number_format(( $total - $paid ), 2, '.', '' );
	
	if ( WP_DEBUG && current_user_can( 'update_core' )) {
//		$current_user = wp_get_current_user();
//		$user_id = $current_user->ID;
//		$total = $user_id < 3 ? 0.01 : $total;
	}
	// charge_total  	Final purchase Amount - no $, must include 2 decimal places
	$EE_Moneris_HPP->addField( 'charge_total', $total );
	
	$country = isset( $country ) ? $country : '';
	

	// bill_first_name text  -  max 30 chars
	// bill_last_name text  -  max 30 chars
	// bill_company_name text  -  max 30 chars
	// bill_address_one text  -  max 30 chars
	// bill_city text  -  max 30 chars
	// bill_state_or_province text  -  max 30 chars
	// bill_postal_code text  -  max 30 chars
	// bill_country text  -  max 30 chars
	// bill_phone text  -  max 30 chars
	// bill_fax text	  -  max 30 chars
	$EE_Moneris_HPP->addField( 'bill_first_name', $fname );
	$EE_Moneris_HPP->addField( 'bill_last_name', $lname );
	$EE_Moneris_HPP->addField( 'bill_address_one', $address );
	$EE_Moneris_HPP->addField( 'bill_city', $city );
	$EE_Moneris_HPP->addField( 'bill_state_or_province', $state );
	$EE_Moneris_HPP->addField( 'bill_country', $country );
	$EE_Moneris_HPP->addField( 'bill_postal_code', $zip );

	// if txn mode is not production (live site)
	if ( $EE_Moneris_HPP->settings['moneris_hpp_txn_mode'] != 'prod' ) {
		echo '
		<h4 style="color:#ff0000;" title="Payments will not be processed">' . __('Moneris Hosted Pay Page Debug Mode Is Turned On', 'event_espresso') . '</h4>
		<h5>Credit Card Test Numbers</h5>
		<ul>
			<li>MasterCard &nbsp; 5454545454545454</li>
			<li>Visa &nbsp; 4242424242424242</li>
			<li>Amex &nbsp; 373599005095005</li>
			<li>Diners &nbsp; 36462462742008</li>
		</ul>
';
		if ( $EE_Moneris_HPP->settings['moneris_hpp_txn_mode'] == 'debug' ) {
			$EE_Moneris_HPP->dump_fields();
		}
	}
	
	if ( empty( $EE_Moneris_HPP->settings['button_url'] )) {
		$EE_Moneris_HPP->settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/pay-by-credit-card.png';
	} 
	
	$EE_Moneris_HPP->submitButton( $EE_Moneris_HPP->settings['button_url'], 'moneris_hpp' );
	
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_moneris_hpp', 10, 1 );