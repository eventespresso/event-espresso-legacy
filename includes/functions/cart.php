<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');		
/**
 * Event Espresso Multi Event Registration Functions
 *
 *
 * @package		Event Espresso
 * @subpackage          Multi Event Registration and shopping cart functions
 * @author		Abel Sekepyan
 * @link		http://eventespresso.com/support/
 */
/**
 * Add event or item (planned for shopping cart) to the session
 *
 * @param $_POST
 *
 * @return JSON object
 */
if (!function_exists('event_espresso_add_item_to_session')) {
	function event_espresso_add_item_to_session() {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		global $wpdb;
		// echo "<pre>", print_r( $_POST ), "</pre>";
		$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );

		/*
		 * added the cart_link_# to the page to prevent element id conflicts on the html page
		 *
		 */
		$id = $_POST['id'];
		$direct_to_cart = isset($_POST['direct_to_cart']) ? $_POST['direct_to_cart'] : 0;
		$moving_to_cart = isset($_POST['moving_to_cart']) ? urldecode($_POST['moving_to_cart']) : "Please wait redirecting to cart page";
		//One link, multiple events
		if (strpos($id, "-")) {

			$event_group = str_replace('cart_link_', '', $id);
			$event_group = explode("-", $event_group);

			foreach ($event_group as $event) {

				$event_title = get_event_field('event_name', EVENTS_DETAIL_TABLE, ' WHERE id = ' . $event);

				event_espresso_add_event_process((int) $event, $event_title);
			}
			
		} else { 
			//one event per click
			$id = str_replace('cart_link_', '', $id);
			event_espresso_add_event_process($id, $_POST['name']);
		}

		$r = event_espresso_cart_link(array('event_id' => $id, 'view_cart' => TRUE, 'event_page_id' => $_POST['event_page_id'], 'direct_to_cart' => $direct_to_cart, 'moving_to_cart' => $moving_to_cart));

		echo event_espresso_json_response(array('html' => $r, 'code' => 1));
		//echo '<a href="' . site_url() . '/events/?regevent_action=show_shopping_cart">' . __( 'View Cart', 'event_espresso' ) . '</a>';

		die();
		
	}
}



/**
 * Processor function for adding items to the session
 *
 * @param event_id
 * @param event_name
 *
 * @return true
 */
if (!function_exists('event_espresso_add_event_process')) {
	function event_espresso_add_event_process($event_id, $event_name) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

		$_SESSION['espresso_session']['events_in_session'][$event_id] = array(
				'id' => $event_id,
				'event_name' => stripslashes_deep($event_name),
				'attendee_quantity' => 1,
				'start_time_id' => '',
				'price_id' => array(),
				'cost' => 0.00,
				'event_attendees' => array()
		);

		return true;
		
	}
}



/**
 * Convert passed array to json object
 *
 * @param array
 *
 * @return JSON object
 */
if (!function_exists('event_espresso_json_response')) {
	function event_espresso_json_response($params = array()) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		$params['code'] = 1;

		return json_encode($params);
		
	}
}



/**
 * Return an individual Session variable
 *
 * @param key
 *
 * @return value of session key
 */
if (!function_exists('event_espresso_return_session_var')) {
	function event_espresso_return_session_var($k = null) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		if (is_null($k))
			return;

		return array_key_exists($k, $_SESSION) ? $_SESSION[$k] : null;
		
	}
}



/**
 * Updates item information in the session
 * @param  mixed 		$update_section 
 * @return 	true
 */
if (!function_exists('event_espresso_update_item_in_session')) {
	function event_espresso_update_item_in_session( $update_section = FALSE ) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		global $wpdb;

		// grab the event sessions
		// loop through the events and for each one
		// - update the pricing, time options
		//-  update the attendee information
		 
		$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );

		if ( ! is_array( $events_in_session )) {
			return false;
		}
			
		//holds the updated infromation
		$updated_events_in_session = $events_in_session;

		if ( $update_section == 'details' ) {

			foreach ($events_in_session as $event_id => $v) {

				$event_cost = 0;
				$event_individual_cost[$event_id] = 0;
				$updated_events_in_session[$event_id]['id'] = $event_id;
				/*
				 * if the array key exists, update that array key with the value from post
				 */


				//Start time selection
				$start_time_id = '';
				if (array_key_exists('start_time_id', $_POST) && array_key_exists($event_id, $_POST['start_time_id'])) {

					$updated_events_in_session[$event_id]['start_time_id'] = $wpdb->escape($_POST['start_time_id'][$event_id]);

					//unset the post key so it doesn't get added below
					unset($_POST['start_time_id'][$event_id]);
				}

				//Pricing selection
				$price_id = null;

				//resetting this session var for just in case the event organizer makes changes when someone is
				//registering, the old price ids don't stay in the session
				$updated_events_in_session[$event_id]['price_id'] = array();


				/*
				 * the price id comes this way
				 * - from a dropdown >> price_id[event_id][price_id]
				 * - from a radio >> price_id[event_id] with a value of price_id
				 */
				$attendee_quantity = 1;
				
				if ( isset( $_POST['price_id'][$event_id] )) {
					$price_id = $_POST['price_id'][$event_id];
				} else {
					return FALSE;
				}

				if (is_array($price_id)) {
					foreach ($price_id as $_price_id => $val) {
						//assign the event type and the quantity
						$updated_events_in_session[$event_id]['price_id'][$_price_id]['attendee_quantity'] = $wpdb->escape($val);
						$updated_events_in_session[$event_id]['price_id'][$_price_id]['price_type'] = $events_in_session[$event_id]['price_id'][$_price_id]['price_type'];

						$attendee_quantity++;
					}
				} else if ( $price_id !== FALSE ) {
					if (isset($price_id)) {
						$updated_events_in_session[$event_id]['price_id'][$price_id]['attendee_quantity'] = 1;
						$updated_events_in_session[$event_id]['price_id'][$price_id]['price_type'] = $events_in_session[$event_id]['price_id'][$price_id]['price_type'];
					}
				}

				$updated_events_in_session[$event_id]['attendee_quantity'] = $attendee_quantity;

				//Get Cost of each event
				//$updated_events_in_session[$event_id]['cost'] = $event_individual_cost[$event_id];
				//$updated_events_in_session[$event_id]['event_name'] = $wpdb->escape( $_POST['event_name'][$event_id] );

				if (isset($_POST['event_espresso_coupon_code'])) {
					$_SESSION['espresso_session']['event_espresso_coupon_code'] = $wpdb->escape($_POST['event_espresso_coupon_code']);
				}
				
				if (isset($_POST['event_espresso_groupon_code'])) {
					$_SESSION['espresso_session']['groupon_code'] = $wpdb->escape($_POST['event_espresso_groupon_code']);
				}
			}
			
		} elseif ( $update_section == 'attendees' ) {
			//show the empty cart error
			if (event_espresso_invoke_cart_error($events_in_session))
				return false;

			foreach ($events_in_session as $k_event_id => $v_event_id) {
				//unset the event attendees array because they may have decreased the number of attendees
				if (isset($updated_events_in_session[$k_event_id]['event_attendees']))
					$updated_events_in_session[$k_event_id]['event_attendees'] = array();

				$price_id = $v_event_id['price_id'];

				if (is_array($price_id)) {
					foreach ($price_id as $_price_id => $val) {
						$index = 1;
						//assign the event type and the quantity
						foreach ($_POST as $post_name => $post_value) {
							//$field_values come in as arrays since their names are designated as arrays,e.g. fname[eventid][price_id][index]
							if (is_array($post_value) && array_key_exists($k_event_id, $post_value) && array_key_exists($_price_id, $post_value[$k_event_id])) {

								foreach ($post_value[$k_event_id][$_price_id] as $mkey => $mval) {
						            if (is_array($mval)) {
						                array_walk_recursive($mval, 'sanitize_text_field');
						            } else {
						                $mval = sanitize_text_field($mval);
						            }
									$updated_events_in_session[$k_event_id]['event_attendees'][$_price_id][$mkey][$post_name] = $mval;
									//echo "multi $k > $field_name >" . $mkey . " > " . $mval . "<br />";
								}
							}
						}
					}
				}
			}
		}

		$_SESSION['espresso_session']['events_in_session'] = $updated_events_in_session;
		//echo "<pre>", print_r($updated_events_in_session), "</pre>";

		return true;

		die();
		
	}
}



/**
 * Calculates total of the items in the session
 *
 * @param $_POST
 *
 * @return JSON (grand total)
 */
if (!function_exists('event_espresso_calculate_total')) {
	function event_espresso_calculate_total( $update_section = FALSE, $mer = TRUE ) {

		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		
		//print_r($_POST);
		$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );
		
		$grand_total = 0.00;
		
		$coupon_events = array();
		$coupon_notifications = '';
		$coupon_errors = '';
		
		$groupon_events = array();
		$groupon_notifications = '';
		$groupon_errors = '';
		
		$notifications = '';
				
		if (is_array($events_in_session)) {

			$event_total_cost = 0;

			foreach ( $events_in_session as $event_id => $event ) {
			
				$event_id = absint( $event_id );				
				if ( $event_id ) {
					
					$event_cost = 0;
					$event_individual_cost[$event_id] = 0;
					$attendee_quantity = 0;
					$coupon_results = array(
						'event_cost' => 0,
						'valid' => FALSE,
						'error' => '',
						'msg' => ''
					);
					
					$groupon_results = array(
						'event_cost' => 0,
						'valid' => FALSE,
						'error' => '',
						'msg' => ''
					);
					
					$use_coupon_code = isset( $_POST['use_coupon'][$event_id] ) ? $_POST['use_coupon'][$event_id] : 'N';
					if ( $use_coupon_code == 'Y' ) {
						add_filter( 'filter_hook_espresso_coupon_results', 'espresso_filter_coupon_results', 10, 3 );
					}

					$use_groupon_code = isset( $_POST['use_groupon'][$event_id] ) ? $_POST['use_groupon'][$event_id] : 'N';
					if ( $use_groupon_code == 'Y' ) {
						add_filter( 'filter_hook_espresso_groupon_results', 'espresso_filter_groupon_results', 10, 3 );
					}


					$start_time_id = '';
					if (array_key_exists('start_time_id', $_POST) && array_key_exists($event_id, $_POST['start_time_id'])) {
						$start_time_id = $_POST['start_time_id'][$event_id];
					}

					/*
					 * two ways the price id comes this way
					 * - from a dropdown >> price_id[event_id][price_id]
					 * - from a radio >> price_id[event_id] with a value of price_id
					 */
					
					if ( isset( $_POST['price_id'][$event_id] )) {
						$event_price = $_POST['price_id'][$event_id];
					} else {
						$event_price = FALSE;
						$notifications = __('An error occured, a valid price is required.', 'event_espresso');
					}
					
					//echo '<h4>event_cost : ' . $event_individual_cost[$event_id] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

					if ( is_array( $event_price )) {
					
						foreach ( $event_price as $_price_id => $qty ) {					
							$attendee_quantity = absint( $qty );
							if ( $attendee_quantity > 0 ) {
							
								// Process coupons
								$coupon_results['event_cost'] = event_espresso_get_final_price( $_price_id, $event_id );
								$coupon_results = apply_filters( 'filter_hook_espresso_coupon_results', $coupon_results, $event_id, $mer );
								$coupon_notifications = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $coupon_notifications, $coupon_results['msg'] );
								$coupon_errors = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $coupon_errors, $coupon_results['error'] );
								if ( $coupon_results['valid'] ) {
									$coupon_events = apply_filters( 'filter_hook_espresso_cart_coupon_events_array', $coupon_events, $event['event_name'] );
								}
								$event_cost = $coupon_results['event_cost'];
								
								if (function_exists('event_espresso_groupon_payment_page') && isset($_POST['event_espresso_groupon_code'])) {	

									// Process Groupons
									$groupon_results['event_cost'] = $event_cost;
									$groupon_results = apply_filters( 'filter_hook_espresso_groupon_results', $groupon_results, $event_id, $mer );
									$groupon_notifications = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $groupon_notifications, $groupon_results['msg'] );
									$groupon_errors = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $groupon_errors, $groupon_results['error'] );
									if ( $groupon_results['valid'] ) {
										$groupon_events = apply_filters( 'filter_hook_espresso_cart_groupon_events_array', $groupon_events, $event['event_name'] );
									}
									//printr( $groupon_results, '$groupon_results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
									$event_cost = $groupon_results['event_cost'];
								
								} 
								
								// now sum up costs so far
								$event_individual_cost[$event_id] += number_format( $event_cost * $attendee_quantity, 2, '.', '' );
								do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'line '. __LINE__ .': event_cost='.$event_cost );
								
							}
						}
						 
					} else if ( $event_price !== FALSE ) {
					
						// Process coupons
						$coupon_results['event_cost'] = event_espresso_get_final_price( $event_price, $event_id );
						$coupon_results = apply_filters( 'filter_hook_espresso_coupon_results', $coupon_results, $event_id, $mer );
						$coupon_notifications = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $coupon_notifications, $coupon_results['msg'] );
						$coupon_errors = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $coupon_errors, $coupon_results['error'] );
						if ( $coupon_results['valid'] ) {
							$coupon_events = apply_filters( 'filter_hook_espresso_cart_coupon_events_array', $coupon_events, $event['event_name'] );
						}
						$event_cost = $coupon_results['event_cost'];


						if (function_exists('event_espresso_groupon_payment_page') && isset($_POST['event_espresso_groupon_code'])) {	

							// Process groupons
							$groupon_results['event_cost'] = $event_cost;
							$groupon_results = apply_filters( 'filter_hook_espresso_groupon_results', $groupon_results, $event_id, $mer );
							$groupon_notifications = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $groupon_notifications, $groupon_results['msg'] );
							$groupon_errors = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $groupon_errors, $groupon_results['error'] );
							if ( $groupon_results['valid'] ) {
								$groupon_events = apply_filters( 'filter_hook_espresso_cart_groupon_events_array', $groupon_events, $event['event_name'] );
							}
							//printr( $groupon_results, '$groupon_results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
							$event_cost = $groupon_results['event_cost'];
							
						}
						
						// now sum up costs so far
						$event_individual_cost[$event_id] += number_format( $event_cost, 2, '.', '' );
						//echo '<h4>event_cost : ' . $event_individual_cost[$event_id] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
						do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'line '. __LINE__ .': event_cost='.$event_cost );
						
					}


					$_SESSION['espresso_session']['events_in_session'][$event_id]['cost'] = $event_individual_cost[$event_id];
					//echo '<h4>event_cost : ' . $event_individual_cost[$event_id] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					$event_total_cost += $event_individual_cost[$event_id];

				}
			}
			
			$grand_total = number_format($event_total_cost, 2, '.', '');
			//echo '<h4>$grand_total : ' . $grand_total . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

			$_SESSION['espresso_session']['pre_discount_total'] = $grand_total;
			$_SESSION['espresso_session']['grand_total'] = $grand_total;
			event_espresso_update_item_in_session( 'details' );
			
		}
			
//		echo '$coupon_notifications = ' . $coupon_notifications . '<br/>';
//		echo '$coupon_errors = ' . $coupon_errors . '<br/>';
//		echo '$groupon_notifications = ' . $groupon_notifications . '<br/>';
//		echo '$groupon_errors = ' . $groupon_errors . '<br/>';	
		$coupon_events =array_unique( $coupon_events );
		$coupon_count = count( $coupon_events );
		if ( ! strpos( $coupon_notifications, 'event_espresso_invalid_coupon' ) && $coupon_count > 0 ) {
			$events = implode( $coupon_events, '<br/>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;' );
			$coupon_notifications .= '&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;' . $events . '</p>';
			$coupon_errors = FALSE;
		}

		$groupon_events =array_unique( $groupon_events );
		$groupon_count = count( $groupon_events );
		if ( ! strpos( $groupon_notifications, 'event_espresso_invalid_groupon' ) && $groupon_count > 0 ) {
			$events = implode( $groupon_events, '<br/>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;' );
			$groupon_notifications .= '&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;' . $events . '</p>';
			$groupon_errors = FALSE;
		}
//		echo '$coupon_notifications = ' . $coupon_notifications . '<br/>';
//		echo '$coupon_errors = ' . $coupon_errors . '<br/>';
//		echo '$groupon_notifications = ' . $groupon_notifications . '<br/>';
//		echo '$groupon_errors = ' . $groupon_errors . '<br/>';	

		// add space between $coupon_notifications and  $coupon_errors ( if any $coupon_errors exist )
		$coupon_notifications = $coupon_count && $coupon_errors ? $coupon_notifications . '<br/>' : $coupon_notifications;
		// combine $coupon_notifications & $coupon_errors
		$coupon_notifications .= $coupon_errors;
		// add space between $coupon_notifications and $groupon_notifications ( if any $groupon_notifications exist )
		$coupon_notifications = ( $coupon_count || $coupon_errors ) && ( $groupon_count || $groupon_errors )  ? $coupon_notifications . '<br/>' : $coupon_notifications;
		// add space between $groupon_notifications and  $groupon_errors ( if any $groupon_errors exist )
		$groupon_notifications = $groupon_count && $groupon_errors ? $groupon_notifications . '<br/>' : $groupon_notifications;
		// ALL together now!!!
		$notifications .= $coupon_notifications . $groupon_notifications . $groupon_errors;
		
		if ( ! $update_section ) {
			echo event_espresso_json_response(array('grand_total' => number_format( $grand_total, 2, '.', '' ), 'msg' => $notifications ));
			die();
		}
		
	}
}



/*
 * filter for applying groupons
 */
function espresso_filter_groupon_results( $groupon_results, $event_id, $mer ) {
//	echo '<h4>$event_id : ' . $event_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>$mer : ' . $mer . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
	if (function_exists('event_espresso_groupon_payment_page') && isset($_POST['event_espresso_groupon_code'])) {	
		$use_groupon_code = isset( $_POST['use_groupon'][$event_id] ) ? $_POST['use_groupon'][$event_id] : 'N';				
		if ( $results = event_espresso_groupon_payment_page( $event_id, $groupon_results['event_cost'], $mer, $use_groupon_code ) ) {
//			printr( $results, '$results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
			$groupon_results['valid'] = $results['valid'];
			$groupon_results['error'] = $results['error'];
			$groupon_results['msg'] = $results['msg'];
			$groupon_results['event_cost'] = $results['valid'] ? number_format( $results['event_cost'], 2, '.', '' ) : $groupon_results['event_cost'];
			add_filter( 'filter_hook_espresso_cart_modifier_strings', 'espresso_filter_cart_modifier_strings', 10, 2 );
			add_filter( 'filter_hook_espresso_cart_groupon_events_array', 'espresso_filter_cart_groupon_events_array', 10, 2 );
		} 
	}
	return $groupon_results;	
}



function espresso_filter_cart_groupon_events_array( $groupon_events, $event_name ) {
	$groupon_events[] = $event_name;
	return $groupon_events;
}



function espresso_filter_cart_modifier_strings( $orig_string, $new_string ) {
	$orig_string .= ( $new_string != $orig_string ) && ! empty( $new_string ) ? $new_string : '';
	return $orig_string;
}



/*
 * filter for applying coupons
 */
function espresso_filter_coupon_results( $coupon_results, $event_id, $mer ) {
//	echo '<h4>$event_id : ' . $event_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>$mer : ' . $mer . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
	if (function_exists('event_espresso_coupon_payment_page') && isset($_POST['event_espresso_coupon_code'])) {	
		$use_coupon_code = isset( $_POST['use_coupon'][$event_id] ) ? $_POST['use_coupon'][$event_id] : 'N';		
		//echo '<h4>$use_coupon_code : ' . $use_coupon_code . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';		
		if ( $results = event_espresso_coupon_payment_page( $event_id, $coupon_results['event_cost'], $mer, $use_coupon_code ) ) {
			//printr( $results, '$results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
			$coupon_results['valid'] = $results['valid'];
			$coupon_results['error'] = $results['error'];
			$coupon_results['msg'] = $results['msg'];
			$coupon_results['event_cost'] = $results['valid'] ? number_format( $results['event_cost'], 2, '.', '' ) : $coupon_results['event_cost'];
			add_filter( 'filter_hook_espresso_cart_modifier_strings', 'espresso_filter_cart_modifier_strings', 10, 2 );
			add_filter( 'filter_hook_espresso_cart_coupon_events_array', 'espresso_filter_cart_coupon_events_array', 10, 2 );
		} 
	}
	return $coupon_results;	
}



function espresso_filter_cart_coupon_events_array( $coupon_events, $event_name ) {
	$coupon_events[] = $event_name;
	return $coupon_events;
}



/**
 * Delete and item from the session
 *
 * @param $_POST
 *
 * @return JSON 0 or 1
 */
if (!function_exists('event_espresso_delete_item_from_session')) {
	function event_espresso_delete_item_from_session() {
	
		global $wpdb;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

		/*
		 * added the cart_link_# to the page to prevent element id conflicts on the html page
		 *
		 */
		$id = $_POST['id'];
		$id = str_replace('cart_link_', '', $id);

		unset( $_SESSION['espresso_session']['events_in_session'][$id] );

		if ( count( $_SESSION['espresso_session']['events_in_session'] ) == 0) {

			unset($_SESSION['espresso_session']['event_espresso_coupon_code']);
			unset($_SESSION['espresso_session']['groupon_code']);
			unset($_SESSION['espresso_session']['groupon_used']);
			unset($_SESSION['espresso_session']['events_in_session']);
			unset($_SESSION['espresso_session']['grand_total']);
			unset($_SESSION['espresso_session']['pre_discount_total']);
			do_action( 'action_hook_espresso_zero_vlm_dscnt_in_session' );
			
		} /*else {
			$_SESSION['espresso_session']['events_in_session'] = $events_in_session;
		}*/

		echo event_espresso_json_response();
		die();
		
	}
}



/**
 * Loads the registration form based on information in the session
 *
 * @return HTML form
 */
if (!function_exists('event_espresso_load_checkout_page')) {
	function event_espresso_load_checkout_page() {
	
		global $wpdb, $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );
//		printr( $events_in_session, '$events_in_session  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		$event_count = count( $events_in_session );

		if (event_espresso_invoke_cart_error($events_in_session))
			return false;

		//echo "<pre>", print_r( $_SESSION ), "</pre>";
		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "multi_registration_page.php")) {
			require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "multi_registration_page.php"); //This is the path to the template file if available
		} else {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/multi_registration_page.php");
		}

		$response['html'] = '';
		//if the counte of event in the session >0, ok to process
		if ( $event_count > 0 ) {
			//for each one of the events in session, grab the event ids, drop into temp array, impode to construct SQL IN clasue (IN(1,5,7))
			foreach ($events_in_session as $event) {
				// echo $event['id'];
				if (is_numeric($event['id']))
					$events_IN[] = $event['id'];
			}

			$events_IN = implode(',', $events_IN);


			$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
			$sql .= " WHERE e.id in ($events_IN) ";
			$sql .= " ORDER BY e.start_date ";

			$result = $wpdb->get_results($sql);

			//will hold data to pass to the form builder function
			$meta = array();
			//echo "<pre>", print_r($_POST), "</pre>";
			?>

<div class = "event_espresso_form_wrapper">
	<form id="event_espresso_checkout_form" method="post" action="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=post_multi_attendee">
		<?php
					$err = '';
					$edit_cart_link = '<a href="?page_id='.$org_options['event_page_id'].'&regevent_action=show_shopping_cart" rel="nofollow" class="btn_event_form_submit inline-link">'.__('Edit Cart', 'event_espresso').'</a>';
	
					ob_start();
					//will be used if sj is off or they somehow select more than allotted attendees
					$show_checkout_button = true;
					$counter = 1;
					foreach ($result as $r) {

						$event_id = $r->id;
						$event_meta = unserialize($r->event_meta);
						
						$event_meta['is_active'] = $r->is_active;
						$event_meta['event_status'] = $r->event_status;
						$event_meta['start_time'] = empty($r->start_time) ? '' : $r->start_time;
						$event_meta['start_date'] = $r->start_date;

						$event_meta['registration_startT'] = $r->registration_startT;
						$event_meta['registration_start'] = $r->registration_start;

						$event_meta['registration_endT'] = $r->registration_endT;
						$event_meta['registration_end'] = $r->registration_end;		
						
						$r->event_meta = serialize( $event_meta );		
						
						//If the event is still active, then show it.
						if (event_espresso_get_status($event_id) == 'ACTIVE') {
						
							//DEPRECATED
							//Pull the detail from the event detail row, find out which route to take for additional attendees
							//Can be 1) no questios asked, just record qty 2) ask for only personal info 3) ask all attendees the full reg questions
							//#1 is not in use as of ..P35
							$meta['additional_attendee_reg_info'] = (is_array($event_meta) && array_key_exists('additional_attendee_reg_info', $event_meta) && $event_meta['additional_attendee_reg_info'] > 1) ? $event_meta['additional_attendee_reg_info'] : 2;
	
							//In case the js is off, the attendee qty dropdowns will not
							//function properly, allowing for registering more than allowed limit.
							//The info from the following 5 lines will determine
							//if they have surpassed the limit.
							$available_spaces = get_number_of_attendees_reg_limit($event_id, 'number_available_spaces');
	
							$attendee_limit = $r->additional_limit + 1;
	
							if ($available_spaces != 'Unlimited')
								$attendee_limit = ($attendee_limit <= $available_spaces) ? $attendee_limit : $available_spaces;
	
							$total_attendees_per_event = 0;
	
							$attendee_overflow = false;
	
							//assign variable
							$meta['additional_attendee'] = 0;
							$meta['attendee_number'] = 1;
	
							//used for "Copy From" dropdown on the reg form
							$meta['copy_link'] = $counter;
	
							//Grab the event price ids from the session.  All event must have at least one price id
							$price_ids = $events_in_session[$event_id]['price_id'];
	
	
	
	
							//Just to make sure, check if is array
							if (is_array($price_ids)) {
								//for each one of the price ids, load an attendee question section
								foreach ($price_ids as $_price_id => $val) {
	
									if (isset($val['attendee_quantity']) && $val['attendee_quantity'] > 0) { //only show reg form if attendee qty is set
										$meta['price_id'] = $_price_id; //will be used to keep track of the attendee in the group
										$meta['price_type'] = $val['price_type']; //will be used to keep track of the attendee in the group
										$meta['attendee_quantity'] = $val['attendee_quantity'];
										$total_attendees_per_event += $val['attendee_quantity'];
										multi_register_attendees( null, $event_id, $meta, $r );
										$meta['attendee_number'] += $val['attendee_quantity'];
									}
								}
	
								//If they have selected more than allowed max group registration
								//display an error instead of the continue button
								if ($total_attendees_per_event > $attendee_limit || $total_attendees_per_event == 0) {
									$attendee_overflow = true;
									$show_checkout_button = false;
								}
							}
	
	
							if ($attendee_overflow) {
	
								$err .= "<div class='event_espresso_error'><p><em>Attention</em>";
								$err .= sprintf(__("For %s, please make sure to select between 1 and %d attendees or delete it from your cart.", 'event_espresso'), stripslashes($r->event_name), $attendee_limit);
								$err .= '<span class="remove-cart-item"><img class="ee_delete_item_from_cart" id="cart_link_' . $event_id . '" alt="Remove this item from your cart" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif" /></span> ';
								$err .= "</p></div>";
							}
	
	
							$counter++;
						}
					}

					$output = ob_get_contents();
					ob_end_clean();

					if ($err != '')
						echo $err;

					if ($show_checkout_button) {

						echo $output;
						
						//Recaptcha portion
						if ( $org_options['use_captcha'] == 'Y'  && ! is_user_logged_in()  ) { // && isset( $_REQUEST['edit_details'] ) && $_REQUEST['edit_details'] != 'true'
							// this is probably superfluous because it's already being loaded elsewhere...trying to cover all my bases ~c  ?>
							<script type="text/javascript">
								var RecaptchaOptions = {
									theme : '<?php echo $org_options['recaptcha_theme'] == '' ? 'red' : $org_options['recaptcha_theme']; ?>',
									lang : '<?php echo $org_options['recaptcha_language'] == '' ? 'en' : $org_options['recaptcha_language']; ?>'
								};
							</script>
						<?php
							if ( ! function_exists( 'recaptcha_get_html' )) {
								require_once( EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/recaptchalib.php' );
							}//End require captcha library
							# the response from reCAPTCHA
							$resp = true;
							# the error code from reCAPTCHA, if any
							$error = null;
							?>
							<p class="event_form_field" id="captcha-<?php echo $event_id; ?>">
								<?php _e('Anti-Spam Measure: Please enter the following phrase', 'event_espresso'); ?>
								<?php echo recaptcha_get_html($org_options['recaptcha_publickey'], $error, is_ssl() ? true : false); ?> 
							</p>
			<?php } //End use captcha	?>
			
		<div class="event-display-boxes ui-widget">
			<div class="mer-event-submit ui-widget-content ui-corner-all">
				<input type="submit" class="submit btn_event_form_submit ui-priority-primary ui-state-default ui-state-hover ui-state-focus ui-corner-all" name="payment_page" value="<?php _e('Confirm and go to payment page', 'event_espresso'); ?>&nbsp;&raquo;" />
			</div>
		</div>
		<?php } ?> 
				<p id="event_espresso_edit_cart">
					<a href="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=show_shopping_cart" class="btn_event_form_submit inline-link">
						<?php _e('Edit Cart', 'event_espresso'); ?>
					</a> 
				</p>
		
	</form>
</div>

<script>
	jQuery(function(){
		//Registration form validation
		jQuery('#event_espresso_checkout_form').validate();
	});
</script>
<?php
		}


		//echo json_encode( $response );
		//die();
	}
}



/**
 * Returns the "Copy from " dropdown.
 */
function event_espresso_copy_dd($event_id, $meta) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );
	$count_of_events = count($events_in_session);


	$var = '<div class = "copy_dropdown_wrapper"> ';
	$var .= '<label>Copy from: </label>';
	$var .= '<select id="multi_regis_form_fields-' . $event_id . '" class="event_espresso_copy_info">';
	$var .= "<option value=''></option>";

	/*
	 * 1) loop through all the events in the session
	 * 2) For each one of the events, loop through the price ids
	 * 3) If the attendee quantity is set and is >0,
	 * 4) TURNED OFF in P41 -produce the dropdown if it is not the same price id
	 */

	foreach ($events_in_session as $k_event_id => $v_event_id) {

		foreach ($v_event_id['price_id'] as $k_price_id => $v_price_id) {
			$event_meta = event_espresso_get_event_meta($v_event_id['id']);
			if (isset($v_price_id['attendee_quantity']) && $v_price_id['attendee_quantity'] > 0) {
				if ($event_meta['additional_attendee_reg_info'] == 1) {
					$i = 1;
					$event_name = strlen($v_event_id['event_name']) > 25 ? substr($v_event_id['event_name'], 0, 15) . '... ' : $v_event_id['event_name']; //if too long to display
					$var .= "<option value='$event_id|{$meta['price_id']}|{$meta['attendee_number']}|$k_event_id|$k_price_id|$i'>" . stripslashes_deep($event_name) . ' - ' . stripslashes_deep($v_price_id['price_type'] ). ' - Attendee ' . $i . "</option>";
				} else {
					for ($i = 1; $i <= $v_price_id['attendee_quantity']; $i++) {
						$event_name = strlen($v_event_id['event_name']) > 25 ? substr($v_event_id['event_name'], 0, 15) . '... ' : $v_event_id['event_name']; //if too long to display
						$var .= "<option value='$event_id|{$meta['price_id']}|{$meta['attendee_number']}|$k_event_id|$k_price_id|$i'>" . stripslashes_deep($event_name) . ' - ' . $v_price_id['price_type'] . ' - Attendee ' . $i . "</option>";
					}
				}
			}
		}
	}

	$var .= "<option value='$event_id|{$meta['price_id']}|{$meta['attendee_number']}'>CLEAR FIELDS</option>";
	$var .= "</select></div>";

	return $var;

	return "<a href='#' class='event_espresso_copy_link' id='event_espresso_copy_link-$event_id'> Copy from above</a>";
}



/**
 * Add event or item (planned for shopping cart) to the session
 *
 * @param $_POST
 *
 * @return JSON object
 */
if (!function_exists('event_espresso_confirm_and_pay')) {
	function event_espresso_confirm_and_pay() {
	
		global $wpdb;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );


		foreach ($events_in_session as $k => $v) {

			foreach ($_POST as $field_name => $field_value) {

				if (is_array($field_value) && array_key_exists($events_in_session, $field_value)) {

					if (is_multi($field_value)) {

						//$multi_key= $field_value[$k];
						foreach ($field_value[$k] as $mkey => $mval) {
				            if (is_array($mval)) {
				                array_walk_recursive($mval, 'sanitize_text_field');
				            } else {
				                $mval = sanitize_text_field($mval);
				            }
							echo "multi $k > $field_name >" . $mkey . " > " . $mval . "<br />";
						}
					} else {
			            if (is_array($field_value)) {
			                array_walk_recursive($field_value, 'sanitize_text_field');
			            } else {
			                $field_value = sanitize_text_field($field_value);
			            }
						echo "$k > $field_name >" . $field_value[$k] . "<br />";
					}
				}
			}
			echo "<hr />";
		}
		//echo "<pre>" , print_r($_POST) , "</pre>";

		die();
		
	}
}



/**
 * Creates the # of Attendees dropdown in the shopping cart page
 *
 * @param $event_id
 * @param $price_id
 * @param $qty - of attendees allowed in this registration
 * @param $value - previously selected value
 *
 * @return Dropdown
 */
if (!function_exists('event_espresso_multi_qty_dd')) {
	function event_espresso_multi_qty_dd($event_id, $price_id, $qty, $value = '') {
	
		$counter = 0;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		?>
<select name="price_id[<?php echo $event_id; ?>][<?php echo $price_id; ?>]" id="price_id-<?php echo $event_id; ?>" class="price_id">
	<?php
			for ($i = 0; $i <= $qty; $i++):
				$selected = ($i == $value) ? ' selected="selected" ' : '';
				?>
	<option <?php echo $selected; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
	<?php endfor; ?>
</select>
<?php

	}
}



/**
 * Additional attendees grid
 *
 * @param $additional_limit -limit of attendees
 * @param $available_spaces -available spaces
 * @param $event_id
 *
 * @return JSON object
 */
if (!function_exists('event_espresso_multi_additional_attendees')) {
	function event_espresso_multi_additional_attendees($additional_limit, $available_spaces, $event_id = null) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		
		if ($additional_limit == 0) {
			return;
		}
			
		$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );
?>

<div class="event_espresso_add_attendee_wrapper-<?php echo $event_id; ?>">
	<?php
			$i = 1;
			while (($i < $additional_limit) && ($i < $available_spaces)) {
				$i++;
?>
	<div class="additional_attendees-<?php echo $event_id . '-' . $i; ?>">
		<p class="event_form_field additional_header" id="">
			<?php _e('Additional Attendee', 'event_espresso'); ?>
			<?php echo $i; ?> </p>
		<div class="clone espresso_add_attendee">
			<p>
				<label for="x_attendee_fname">
					<?php _e('First Name', 'event_espresso'); ?>
					<em>*</em></label>
				<input type="text" name="x_attendee_fname[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required input' value="<?php echo $events_in_session[$event_id]['event_attendees']['x_attendee_fname'][$i] ?>" />
			</p>
			<p>
				<label for="x_attendee_lname">
					<?php _e('Last Name', 'event_espresso'); ?>
					<em>*</em></label>
				<input type="text" name="x_attendee_lname[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required input' value="<?php echo $events_in_session[$event_id]['event_attendees']['x_attendee_lname'][$i] ?>" />
			</p>
			<p>
				<label for="x_attendee_email">
					<?php _e('Email', 'event_espresso'); ?>
					<em>*</em></label>
				<input type="text" name="x_attendee_email[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required email input' value="<?php echo $events_in_session[$event_id]['event_attendees']['x_attendee_email'][$i] ?>" />
			</p>
		</div>
	</div>
	<?php
			}
			$i--;
			?>
</div>
<?php

	}
}


/**
 * Creates add to cart link or view cart
 *
 * @param $array
 *
 * @return JSON object
 */
if (!function_exists('event_espresso_cart_link')) {
	function event_espresso_cart_link($atts) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		global $org_options, $this_event_id;

		$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );
		
		extract(
			shortcode_atts(
				array(
					'event_id' => $this_event_id,
					'anchor' => __('Register', 'event_espresso'),
					'event_name' => ' ',
					'separator' => NULL,
					'view_cart' => FALSE,
					'event_page_id' => $org_options['event_page_id'], //instead of sending it in as a var, grab the id here.
					'direct_to_cart' => 0,
					'moving_to_cart' => "Please wait redirecting to cart page"
				), 
				$atts
			)
		);
		
		if ( empty( $event_id )) {
			$error = "<div class='event_espresso_error'><p><em>Attention</em>";
			$error .= __('An error occured, a valid event id is required for this shortcode to function properlly.', 'event_espresso');
			$error .= "</p></div>";
			return $error;
		}
		

		$registration_cart_class = '';
		ob_start();

		// if event is already in session, return the view cart link
		if ($view_cart || (is_array($events_in_session) && array_key_exists($event_id, $events_in_session))) {
		
			$registration_cart_url = get_option('siteurl') . '/?page_id=' . $event_page_id . '&regevent_action=show_shopping_cart';
			$registration_cart_anchor = __("View Cart", 'event_espresso');
			
		} else {
		
			//show them the add to cart link
			$registration_cart_url = isset($externalURL) && $externalURL != '' ? $externalURL : get_option('siteurl') . '/?page_id=' . $event_page_id . '&regevent_action=add_event_to_cart&event_id=' . $event_id . '&name_of_event=' . stripslashes_deep($event_name);
			$registration_cart_anchor = $anchor;
			$registration_cart_class = 'ee_add_item_to_cart';
			
		}

		if ($view_cart && $direct_to_cart == 1) {
			echo "<span id='moving_to_cart'>{$moving_to_cart}</span>";
			echo "<script language='javascript'>window.location='" . $registration_cart_url . "';</script>";
		} else {
			echo $separator . ' <a class="ee_view_cart ' . $registration_cart_class . '" id="cart_link_' . $event_id . '" href="' . $registration_cart_url . '" title="' . stripslashes_deep($event_name) . '" moving_to_cart="' . urlencode($moving_to_cart) . '" direct_to_cart="' . $direct_to_cart . '" >' . $registration_cart_anchor . '</a>';
		}

		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
		
	}
}
add_shortcode('ESPRESSO_CART_LINK', 'event_espresso_cart_link');




if (!function_exists('event_espresso_invoke_cart_error')) {
	function event_espresso_invoke_cart_error( $events_in_session ) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		if (!is_array($events_in_session)) {
			echo __('It looks like you are attempting to refresh a page after completing your registration or your cart is empty.  Please go to the events page and try again.', 'event_espresso') . "<br />";
			return true;
		}
		return false;
	}
}




if (!function_exists('event_espresso_clear_session')) {
	function event_espresso_clear_session( $return_events_in_session = FALSE ) {
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		$_SESSION['espresso_session'] = array();
		$_SESSION['espresso_session']['id'] = session_id() . '-' . uniqid('', true);
		$_SESSION['espresso_session']['events_in_session'] = '';
		$_SESSION['espresso_session']['grand_total'] = '';
		do_action( 'action_hook_espresso_zero_vlm_dscnt_in_session' ); 
		
		return $return_events_in_session ? $_SESSION['espresso_session']['events_in_session'] : NULL;
	}
}



//Creates dropdowns if multiple prices are associated with an event
if (!function_exists('event_espresso_group_price_dropdown')) {
	function event_espresso_group_price_dropdown($event_id, $label = 1, $multi_reg = 0, $value = '') {
	
		global $wpdb, $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		/*
		 * find out pricing type.
		 * - If multiple price options, for each one
		 * -- Create a row in a table with a name
		 * -- qty dropdown
		 *
		 */

		//Will make the name an array and put the time id as a key so we
		//know which event this belongs to
		$multi_name_adjust = $multi_reg == 1 ? "[$event_id]" : '';
		
		$SQL = "SELECT ept.id, ept.event_cost, ept.surcharge, ept.surcharge_type, ept.price_type, edt.allow_multiple, edt.additional_limit ";
		$SQL .= "FROM " . EVENTS_PRICES_TABLE . " ept ";
		$SQL .= "JOIN " . EVENTS_DETAIL_TABLE . "  edt ON ept.event_id =  edt.id ";
		$SQL .= "WHERE event_id=%d ORDER BY ept.id ASC";
		// filter SQL statement
		$SQL = apply_filters( 'filter_hook_espresso_group_price_dropdown_sql', $SQL );
		// get results
		$results = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ));

		if ($wpdb->num_rows > 0) {

			$attendee_limit = 1;
			//echo $label==1?'<label for="event_cost">' . __('Choose an Option: ','event_espresso') . '</label>':'';
			//echo '<input type="radio" name="price_option' . $multi_name_adjust . '" id="price_option-' . $event_id . '">';
			?>
			
<table class="price_list">
	<?php
			$available_spaces = get_number_of_attendees_reg_limit($event_id, 'number_available_spaces');
			foreach ($results as $result) {

				//Setting this field for use on the registration form
				$_SESSION['espresso_session']['events_in_session'][$event_id]['price_id'][$result->id]['price_type'] = stripslashes_deep($result->price_type);
				// Addition for Early Registration discount
				if ($early_price_data = early_discount_amount($event_id, $result->event_cost)) {
					$result->event_cost = $early_price_data['event_price'];
					$message = __(' Early Pricing', 'event_espresso');
				}


				$surcharge = '';

				if ($result->surcharge > 0 && $result->event_cost > 0.00) {
					$surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} " . $org_options['surcharge_text'];
					if ($result->surcharge_type == 'pct') {
						$surcharge = " + {$result->surcharge}% " . $org_options['surcharge_text'];
					}
				}

				?>
	<tr>
		<td class="price_type"><?php echo $result->price_type; ?></td>
		<td class="price"><?php
							if (!isset($message))
								$message = '';
							echo $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . ' ' . $surcharge;
							?></td>
		<td class="selection">
			<?php		
				$attendee_limit = 1;
				$att_qty = empty($_SESSION['espresso_session']['events_in_session'][$event_id]['price_id'][$result->id]['attendee_quantity']) ? '' : $_SESSION['espresso_session']['events_in_session'][$event_id]['price_id'][$result->id]['attendee_quantity'];
				
				if ($result->allow_multiple == 'Y') {			
					$attendee_limit = $result->additional_limit;
					if ($available_spaces != 'Unlimited') {
						$attendee_limit = ($attendee_limit <= $available_spaces) ? $attendee_limit : $available_spaces;
					}
				}
					
				event_espresso_multi_qty_dd( $event_id, $result->id,  $attendee_limit, $att_qty );
				
			?>
		</td>
	</tr>
	<?php
			}
			?>
	<tr>
		<td colspan="3" class="reg-allowed-limit">
			<?php printf(__("You can register a maximum of %d attendees for this event.", 'event_espresso'), $attendee_limit); ?>
		</td>
	</tr>
</table>

<input type="hidden" id="max_attendees-<?php echo $event_id; ?>" class="max_attendees" value= "<?php echo $attendee_limit; ?>" />
<?php
		} else if ($wpdb->num_rows == 0) {
			echo '<span class="free_event">' . __('Free Event', 'event_espresso') . '</span>';
			echo '<input type="hidden" name="payment' . $multi_name_adjust . '" id="payment-' . $event_id . '" value="' . __('free event', 'event_espresso') . '">';
		}
		
	}
}
