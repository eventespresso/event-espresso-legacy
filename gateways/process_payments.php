<?php

/**
 * function espresso_prepare_payment_data_for_gateways
 * @global type $wpdb
 * @global type $org_options
 * @param type $payment_data
 * attendee_id
 * @return type $payment_data
 * contact
 * email
 * event_id
 * registration_id
 * attendee_session
 * event_name
 * lname
 * fname
 * payment_status
 * payment_date
 */
function espresso_prepare_payment_data_for_gateways( $payment_data ) {
	global $wpdb, $org_options;
	$SQL = "SELECT ea.email, ea.event_id, ea.registration_id, ea.txn_type, ed.start_date,";
	$SQL .= " ea.attendee_session, ed.event_name, ea.lname, ea.fname, ea.total_cost,";
	$SQL .= " ea.payment_status, ea.payment_date, ea.address, ea.city, ea.txn_id,";
	$SQL .= " ea.zip, ea.state, ea.phone, ed.event_meta FROM " . EVENTS_ATTENDEE_TABLE . " ea";
	$SQL .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=ea.event_id";
	$SQL .= " WHERE ea.id = %d";
	$temp_data = $wpdb->get_row( $wpdb->prepare( $SQL, $payment_data['attendee_id'] ), ARRAY_A );
	$payment_data = array_merge( $payment_data, $temp_data );
	$payment_data['contact'] = $org_options['contact_email'];
	$payment_data['event_meta'] = unserialize($temp_data['event_meta']);
	return $payment_data;
}

add_filter('filter_hook_espresso_prepare_payment_data_for_gateways', 'espresso_prepare_payment_data_for_gateways');

/**
 * function espresso_get_total_cost
 * @global type $wpdb
 * @param array $payment_data
 * attendee_session
 * @return array $payment_data
 * total_cost
 * quantity
 */
function espresso_get_total_cost($payment_data) {
	global $wpdb;
	//if for some reason attendee_session isn't setin the payment data, set it now
	if(!array_key_exists('attendee_session',$payment_data) || empty($payment_data['attendee_session'])){
		$SQL = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id=%d";
		$session_id = $wpdb->get_var( $wpdb->prepare( $SQL, $payment_data['attendee_id'] ));
		$payment_data['attendee_session']=$session_id;
	}
	//find all the attendee rows
	$sql = "SELECT a.final_price, a.quantity FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$sql .= " WHERE a.attendee_session='" . $payment_data['attendee_session'] . "' ORDER BY a.id ASC";
	$tickets = $wpdb->get_results($sql, ARRAY_A);
	$total_cost = 0;
	$total_quantity = 0;
	
	//sum up their final_prices, as this should already take into account discounts
	foreach ($tickets as $ticket) {
		$total_cost += $ticket['quantity'] * $ticket['final_price'];
		$total_quantity += $ticket['quantity'];
	}
	
//	if (!empty($tickets[0]['coupon_code_price'])) {
//		if ($tickets[0]['use_percentage'] == 'Y') {
//			$payment_data['total_cost'] = $total_cost * (1 - ($tickets[0]['coupon_code_price'] / 100));
//		} else {
//			$payment_data['total_cost'] = $total_cost - $tickets[0]['coupon_code_price'];
//		}
//	} else {
//		$payment_data['total_cost'] = $total_cost;
//	}
	
	$payment_data['total_cost'] = number_format( $total_cost, 2, '.', '' );
	$payment_data['quantity'] = $total_quantity;
	//printr( $payment_data, '$payment_data' );
	return $payment_data;
}

add_filter('filter_hook_espresso_get_total_cost', 'espresso_get_total_cost');

/**
 * function espresso_update_attendee_payment_status_in_db
 * @global type $wpdb
 * @param array $payment_data
 * attendee_id    set by function in individual gateway
 * attendee_session  set by filter_hook_espresso_prepare_payment_data_for_gateways
 * total_cost     set by filter_hook_espresso_get_total_cost
 *                 the rest are set by gateway
 * payment_status
 * txn_type
 * txn_id
 * txn_details
 *
 * @return array $payment_data
 * payment_date
 */
function espresso_update_attendee_payment_status_in_db($payment_data) {
//	echo '<h3>'. __CLASS__ . '->' . __FUNCTION__ . ' <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';
	global $wpdb;
	
	$payment_data['payment_date'] = date(get_option('date_format'));

	$payment = $payment_data['payment_status'] == "Completed" ? $payment_data['total_cost'] : 0.00;

	$wpdb->update( 
		EVENTS_ATTENDEE_TABLE, 
		array( 'amount_pd' => $payment ), 
		array( 'id' => $payment_data['attendee_id'] ), 
		array( '%f' ),
		array( '%d' ) 
	);

	$payment_data['txn_details'] = empty( $payment_data['txn_details'] ) ? serialize( $_REQUEST ) : $payment_data['txn_details']; 

	$wpdb->update( 
		EVENTS_ATTENDEE_TABLE, 
		array( 
			'payment_status' 		=> $payment_data['payment_status'],
			'txn_type' 					=> $payment_data['txn_type'],
			'txn_id' 						=> $payment_data['txn_id'],
			'payment_date' 		=> $payment_data['payment_date'],
			'transaction_details' 	=> $payment_data['txn_details'],
			'date'=>current_time('mysql')
		), 
		array( 'attendee_session' => $payment_data['attendee_session'] ), 
		array( '%s', '%s', '%s', '%s', '%s','%s' ),
		array( '%s' ) 
	);	
	
	do_action('action_hook_espresso_track_successful_sale',$payment_data);
	
	return $payment_data;
}
add_filter('filter_hook_espresso_update_attendee_payment_data_in_db', 'espresso_update_attendee_payment_status_in_db');

/**
 * function espresso_prepare_event_link
 * @param array $payment_data
 * attendee_session
 * @return array $payment_data
 * event_link
 */
function espresso_prepare_event_link($payment_data) {
	global $wpdb;
	$sql = "SELECT  ea.event_id, ed.event_name FROM " . EVENTS_ATTENDEE_TABLE . " ea";
	$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=ea.event_id";
	$sql .= " WHERE ea.attendee_session='" . $payment_data['attendee_session'] . "'";
	$events = $wpdb->get_results($sql, OBJECT_K);
	$payment_data['event_link'] = '';
	foreach ($events as $event) {
		$event_url = espresso_reg_url($event->event_id);
		$payment_data['event_link'] .= '<a href="' . $event_url . '">' . $event->event_name . '</a><br />';
	}
	return $payment_data;
}
add_filter('filter_hook_espresso_prepare_event_link', 'espresso_prepare_event_link');





function event_espresso_txn() {
	ob_start();

	global $wpdb, $org_options, $espresso_content;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	do_action('action_hook_espresso_transaction');
	$active_gateways = get_option('event_espresso_active_gateways', array());
	if (empty($active_gateways)) {
		$subject = __('Website Payment IPN Not Setup', 'event_espresso');
		$body = sprintf(__('The IPN for %s at %s has not been properly setup and is not working. Date/time %s', 'event_espresso'), $org_options['organization'], home_url(), date('g:i A'));
		wp_mail($org_options['contact_email'], $subject, $body);
		return;
	}
	
	/*foreach ($active_gateways as $gateway => $path) {
		event_espresso_require_gateway($gateway . "/init.php");
	}*/
	$payment_data = array( 'attendee_id' => NULL );
	$payment_data['attendee_id'] = apply_filters('filter_hook_espresso_transactions_get_attendee_id', '');
	if ( empty( $payment_data['attendee_id'] )) {
		echo "An error occurred. No ID or an invalid ID was supplied.";
	} else {
		$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
		if( espresso_return_reg_id() == false || $payment_data['registration_id'] != espresso_return_reg_id()) {
			wp_die(__('There was a problem finding your Registration ID', 'event_espresso'));
		}
		if ( $payment_data['payment_status'] != 'Completed' && $payment_data['payment_status'] != 'Refund' ) {
			$payment_data = apply_filters('filter_hook_espresso_transactions_get_payment_data', $payment_data);
			espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => 'Payment for: '. $payment_data['lname'] . ', ' . $payment_data['fname'] . '|| registration id: ' . $payment_data['registration_id'] . '|| transaction details: ' . (isset($payment_data['txn_details']) ? $payment_data['txn_details'] : '')));

			$payment_data = apply_filters('filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data);
			//add and then immediately do action, so developers can modify this behavior on 'after_payment'
			add_action('action_hook_espresso_email_after_payment','espresso_email_after_payment');
			do_action('action_hook_espresso_email_after_payment', $payment_data);
		}
		extract($payment_data);

		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php")) {
			require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php"); //This is the path to the template file if available
		} else {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/payment_overview.php");
		}
	}
	$_REQUEST['page_id'] = $org_options['return_url'];
	//include payment_page as it contains the next function we're going to call
	require_once(EVENT_ESPRESSO_INCLUDES_DIR . "process-registration/payment_page.php"); 
	event_espresso_clear_session_of_attendee($payment_data['attendee_session']);
	ee_init_session();

	$espresso_content = ob_get_contents();
	ob_end_clean();
	add_shortcode('ESPRESSO_TXN_PAGE', 'espresso_return_espresso_content');	
	return $espresso_content;
	
}





function deal_with_ideal() {
	if (!empty($_POST['bank_id'])) {
		$active_gateways = get_option('event_espresso_active_gateways', array());
		if (!empty($active_gateways['ideal'])) {
			$payment_data['attendee_id'] = apply_filters('filter_hook_espresso_transactions_get_attendee_id', '');
			espresso_process_ideal($payment_data);
		}
	}
}
add_action('action_hook_espresso_transaction', 'deal_with_ideal',99);//just before espresso_txn





function espresso_email_after_payment($payment_data) {
	global $org_options;
	if ($payment_data['payment_status'] == 'Completed') {
		event_espresso_send_payment_notification(array('attendee_id' => $payment_data['attendee_id'], 'registration_id' => $payment_data['registration_id']));
		if ($org_options['email_before_payment'] == 'N') {
			event_espresso_email_confirmations(array('session_id' => $payment_data['attendee_session'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
		}
	}
}





// Needed for WorldPay processing
if ( isset( $_POST[ 'name' ] ) && isset( $_POST[ 'MC_type'] ) && 'worldpay' == $_POST[ 'MC_type' ] ) {
	$_POST['_name'] = $_POST['name'];
	$_REQUEST['_name'] = $_POST['name'];
	unset($_POST['name']);
	unset($_REQUEST['name']);
}


/**
 * Builds strings which can be used for the notify_url, return_url, etc which are sent to gateways.
 * 
 * @param string $type usually either 'notify_url','return_url', or 'cancel_url' (keys in the global $org_options
 * @param array $payment_data specifically, this array should contain keys 'attendee_id', 'registration_id', 
 * @param string $gateway_slug name of teh gateway folder, eg 'paypal','mwarrior','eway_rapid3' etc. This is used to get the gateway's option, that option should have a key 'force_ssl_return',
 * @param array $extra_args any extra querystring args to be added to the URL.
 * @return string which can be sent to the gateway
 */
function espresso_build_gateway_url($type, $payment_data, $gateway_slug, $extra_args = array() ){
	global $org_options;
	$url = get_permalink($org_options[$type]);
	$gateway_settings = get_option("event_espresso_{$gateway_slug}_settings");
	if($gateway_settings['force_ssl_return']){
		$url = str_replace("http://","https://",$url);
	}
	
	$query_args = array(
		'id'=>$payment_data['attendee_id'],
		'r_id'=>$payment_data['registration_id'],
		'type'=>$gateway_slug
	);
	switch($type){
		case 'notify_url':
		case 'return_url':
			$query_args['attendee_action']='post_payment';
			$query_args['form_action']='payment';
	}
	$query_args = array_merge($query_args,$extra_args);
	$full_url = add_query_arg($query_args,$url);
	return $full_url;
}
