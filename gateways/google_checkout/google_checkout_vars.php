<?php

function espresso_google_checkout_get_items($attendeeId){
	global $wpdb;
	// get attendee_session
	$SQL = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id=%d";
	$session_id = $wpdb->get_var( $wpdb->prepare( $SQL, $attendeeId ));
	// now get all registrations for that session
	$SQL = "SELECT a.final_price, a.orig_price, a.quantity, ed.event_name, a.price_option, a.fname, a.lname ";
	$SQL .= " FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$SQL .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$SQL .= " WHERE attendee_session=%s ORDER BY a.id ASC";
	
	$items = $wpdb->get_results( $wpdb->prepare( $SQL, $session_id ));
	return $items;
}
function espresso_display_google_checkout($payment_data) {
	global $wpdb;
	extract($payment_data);
	require_once('library/googlecart.php');
	require_once('library/googleitem.php');
	global $org_options;
	$google_checkout_settings = get_option('event_espresso_google_checkout_settings');
	$google_checkout_id = empty($google_checkout_settings['google_checkout_id']) ? '' : $google_checkout_settings['google_checkout_id'];
	$google_checkout_key = empty($google_checkout_settings['google_checkout_key']) ? '' : $google_checkout_settings['google_checkout_key'];
	//$google_checkout_cur = empty($google_checkout_settings['currency_format']) ? '' : $google_checkout_settings['currency_format'];
	$serverToUse = $google_checkout_settings['use_sandbox']?'sandbox':'production';
	$gCart=new Espresso_GoogleCart($google_checkout_id,$google_checkout_key,$serverToUse);
	$gCart->setMerchantPrivateData("attendee_id={$payment_data['attendee_id']},registration_id={$payment_data['registration_id']}");
	do_action('action_hook_espresso_use_add_on_functions');
	$items=espresso_google_checkout_get_items($attendee_id);
	
				
	foreach ( $items as $key => $item ) {	
		$gItem=new Espresso_GoogleItem( $item->price_option,//name
								$item->price_option . ' for ' . $item->event_name . '. Attendee: '. $item->fname . ' ' . $item->lname,//description
								$item->quantity,
								$item->final_price);
		$gCart->AddItem($gItem);	
	}
	

	if ($google_checkout_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	$gCart->SetEditCartUrl( $home . '/?page_id=' . $org_options['event_page_id']);
	$gCart->SetContinueShoppingUrl($home.'?page_id='.$org_options['return_url'].'&r_id=' . $payment_data['registration_id'].'&id='.$payment_data['attendee_id'].'&type=google_checkout');
	if(array_key_exists('payment_status',$payment_data) && $payment_data['payment_status']=='Pending'){
		_e("Your payment with Google Wallet is underway. It should take about 15 minutes for it to be verified and charged.","event_espresso");

	}else{
		echo $gCart->CheckoutButtonCode("SMALL");
	}
	if ($google_checkout_settings['use_sandbox']) {
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Google Checkout Debug Mode Is Turned On', 'event_espresso') . '</h3>';
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_google_checkout');
