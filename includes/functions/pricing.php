<?php
//Functions that deal with pricing should be placed here

function event_espresso_paid_status_icon($payment_status ='') {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
    switch ($payment_status) {
       case 'Cancelled':
            echo '<img align="absmiddle" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/cancel.png" width="16" height="16" alt="' . __('Cancelled', 'event_espresso') . '" title="' . __('Cancelled', 'event_espresso') . '" />';
            break;
	    case 'Checkedin':
            echo '<img align="absmiddle" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/accept.png" width="16" height="16" alt="' . __('Checked-in', 'event_espresso') . '" title="' . __('Checked-in', 'event_espresso') . '" />';
            break;
        case 'NotCheckedin':
            echo '<img align="absmiddle" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/exclamation.png" width="16" height="16" alt="' . __('Not Checked-in', 'event_espresso') . '" title="' . __('Not Checked-in', 'event_espresso') . '" />';
            break;
        case 'Refund':
        case 'Completed':
            echo '<img align="absmiddle" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/accept.png" width="16" height="16" alt="' . __('Completed', 'event_espresso') . '" title="' . __('Completed', 'event_espresso') . '" />';
            break;

        case 'Pending':
            echo '<img align="absmiddle" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/error.png" width="16" height="16" alt="' . __('Pending', 'event_espresso') . '" title="' . __('Pending', 'event_espresso') . '" />';
            break;
        case 'Payment Declined':
            echo '<img align="absmiddle" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/exclamation.png" width="16" height="16" alt="' . __('Payment Declined', 'event_espresso') . '" title="' . __('Payment Declined', 'event_espresso') . '" />';
            break;
        default:
            echo '<img align="absmiddle" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/exclamation.png" width="16" height="16" alt="' . __('Incomplete', 'event_espresso') . '" title="' . __('Incomplete', 'event_espresso') . '" />';
            break;
    }
}

//Retturns the first price assocaited with an event. If an event has more that one price, you can pass the number of the second price.
if (!function_exists('espresso_return_price')) {

    function espresso_return_single_price($event_id, $number=0) {
		
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
        global $wpdb, $org_options;
		
        $number = $number == 0 ? '0,1' : $number . ',' . $number;

        $results = $wpdb->get_results("SELECT id, event_cost, surcharge, surcharge_type FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='" . $event_id . "' ORDER BY id ASC LIMIT " . $number);
        if ($wpdb->num_rows > 0) {
            foreach ($results as $result) {
                if ($result->event_cost > 0.00) {
					// is there a surcharge ???
                    if ( $result->surcharge > 0 ) {
                        if ( 'flat_rate' == $result->surcharge_type ) {
                            $event_cost = $result->event_cost + $result->surcharge;
                        } else {
                            $event_cost = $result->event_cost + ( $result->event_cost * $result->surcharge / 100 );
                        }
                    } else {
						// no surcharge
						$event_cost = $result->event_cost;
					}
                    $event_cost = number_format( $event_cost, 2, '.', '');

                    // Addition for Early Registration discount
                    if ($early_price_data = early_discount_amount($event_id, $event_cost)) {
                        $event_cost = $early_price_data['event_price'];
                    }
                } else {
                    $event_cost = '0.00';
                }
            }
        } else {
            $event_cost = '0.00';
        }

        return $event_cost;
    }

}

/*
  Returns the price of an event
 */
if (!function_exists('event_espresso_get_price')) {

    function event_espresso_get_price( $event_id ) {
 
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		global $wpdb, $org_options;

		$SQL = "SELECT id, event_cost, surcharge, surcharge_type, price_type FROM " . EVENTS_PRICES_TABLE . " WHERE event_id=%d ORDER BY id ASC LIMIT 1";
		$results = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id ));
	 
        $surcharge = '';
        $surcharge_text = isset($org_options['surcharge_text']) ? $org_options['surcharge_text'] : __('Surcharge', 'event_espresso');
	 
        foreach ($results as $result) {
            if ($wpdb->num_rows == 1) {
                if ($result->event_cost > 0.00) {
                    $event_cost = $org_options['currency_symbol'] . $result->event_cost;
                    if ($result->surcharge > 0 && $result->event_cost > 0.00) {
                        $surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} " . $surcharge_text;
                        if ($result->surcharge_type == 'pct') {
                            $surcharge = " + {$result->surcharge}% " . $surcharge_text;
                        }
                    }
                    // Addition for Early Registration discount
                    if ($early_price_data = early_discount_amount($event_id, $result->event_cost)) {
                        $result->event_cost = $early_price_data['event_price'];
                        $message = sprintf(__(' (including %s early discount) ', 'event_espresso'), $early_price_data['early_disc']);
                        //$surcharge = ($result->surcharge > 0.00 && $result->event_cost > 0.00)?" +{$result->surcharge}% " . __('Surcharge','event_espresso'):'';
                        $event_cost = '<span class="event_price_value">' . $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . '</span>';
                    }

                    $event_cost .= '<input type="hidden"name="event_cost" value="' . $result->event_cost . '">';
                } else {
                    $event_cost = __('Free Event', 'event_espresso');
                }
            } else if ($wpdb->num_rows == 0) {
                $event_cost = __('Free Event', 'event_espresso');
            }
        }
        return $event_cost . $surcharge;
    }

}




/*
  Returns the orig price of an event before modifiers are applied
 *
 * @params int $price_id
 */
if (!function_exists('event_espresso_get_orig_price')) {
	function event_espresso_get_orig_price( $price_id = FALSE ) {
		
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

		if ( ! $price_id ) {
			return FALSE;
		}
		
		global $wpdb;
		
		if ( is_array( $price_id )) {
			$price_id = key( $price_id );
		}

		$event_cost = 0.00;
		$SQL = "SELECT event_cost FROM " . EVENTS_PRICES_TABLE . " WHERE id=%d ORDER BY id ASC LIMIT 1";
		if ( $event_cost = $wpdb->get_var( $wpdb->prepare( $SQL, absint( $price_id ) ))) {		
			// if price is anything other than zero
			if ( ! $event_cost > 0 ) {			
				$event_cost = 0.00;
			} 
		}
		
		return (float)number_format( $event_cost, 2, '.', '' );
	}

}

/*
  Verifies that a price id is valid
 *
 * @params int $price_id
 */
if (!function_exists('event_espresso_verify_price_id')) {
	function event_espresso_verify_price_id( $price_id = FALSE, $event_id = FALSE ) {
		
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

		if ( ! $price_id || ! $event_id ) {
			return FALSE;
		}
		
		global $wpdb;
		
		if ( is_array( $price_id )) {
			$price_id = key( $price_id );
		}

		$SQL = "SELECT event_cost FROM " . EVENTS_PRICES_TABLE . " WHERE id=%d AND event_id=%d ORDER BY id ASC LIMIT 1";
		$wpdb->get_var( $wpdb->prepare( $SQL, absint( $price_id ), absint( $event_id ) ));
		if ($wpdb->num_rows > 0) {
			return TRUE;
		}else{
			return FALSE;	
		}
	
	}

}

/*
  Returns the orig price of an event before modifiers are applied
 *
 * @params int $price_id
 */
if (!function_exists('event_espresso_get_orig_price_and_surcharge')) {
	function event_espresso_get_orig_price_and_surcharge( $price_id = FALSE, $event_id = FALSE ) {
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

		if ( ! $price_id || ! $event_id ) {
			return FALSE;
		}
		
		global $wpdb;
		
		if ( is_array( $price_id )) {
			$price_id = key( $price_id );
		}
		

		$SQL = "SELECT id, event_cost, surcharge, surcharge_type FROM " . EVENTS_PRICES_TABLE . " WHERE id=%d AND event_id=%d ORDER BY id ASC LIMIT 1";
		// filter SQL statement
		$SQL = apply_filters( 'filter_hook_espresso_orig_price_and_surcharge_sql', $SQL );
		// get results
		if ( $result = $wpdb->get_row( $wpdb->prepare( $SQL, absint( $price_id ), absint( $event_id ) ))) {		
//		echo '<h4>LQ : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
			// if price is anything other than zero
			if ( ! (float)$result->event_cost > 0 ) {			
				$result->event_cost = 0.00;
			}					
		}
		
		if ( event_espresso_verify_price_id( $price_id, $event_id ) == FALSE ){
			$result = event_espresso_get_first_price($price_id);
			//$result = espresso_return_single_price($event_id);
		}
		
		//printr( $result, '$result  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		return $result;
		
	}

}

/**
 * Gets teh price result for this event. Used when we just want one of them.
 * @global type $wpdb
 * @param type $event_id
 * @return type
 */
function event_espresso_get_first_price($event_id){
	global $wpdb;
	$SQL = "SELECT id, event_cost, surcharge, surcharge_type FROM " . EVENTS_PRICES_TABLE . " WHERE event_id=%d ORDER BY id ASC LIMIT 1";
	$result = $wpdb->get_row( $wpdb->prepare( $SQL, absint( $event_id )));
	return $result;
}




/*
 *	calculate surcharge
 *
 * @params int $price_id
 * @params int $event_id
 */
function event_espresso_calculate_surcharge( $event_cost = 0.00, $surcharge_amount = 0.00, $surcharge_type = 'pct' ) {
	// if >0 and is percent, calculate surcharge amount, if flat rate, will just be formatted, surcharge by default is 0. 
	$surcharge = ( $surcharge_amount > 0 ) && ( $surcharge_type == 'pct' ) ? $event_cost * $surcharge_amount / 100 : $surcharge_amount;
	$surcharge = number_format( $surcharge, 2, '.', '' ); 
	return $surcharge;
}




/*
  Returns the final price of an event
 *
 * @params int $price_id
 * @params int $event_id
 */
if (!function_exists('event_espresso_get_final_price')) {

	function event_espresso_get_final_price( $price_id = FALSE, $event_id = FALSE, $orig_price = FALSE ) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		if ( ! $price_id || ! $event_id ) {
			return FALSE;
		}
		
		if ( event_espresso_verify_price_id( $price_id, $event_id ) == FALSE ){
			$orig_price = espresso_return_single_price($event_id);
		}
			
		$result = $orig_price !== FALSE ? $orig_price : event_espresso_get_orig_price_and_surcharge( $price_id, $event_id );
		
		if ( isset( $result->event_cost )) {
			$result->event_cost = (float)$result->event_cost;
		} else {
			$result = new stdClass();
			$result->event_cost = (float)$orig_price;
		}

		
		// if price is anything other than zero
		if ( $result->event_cost > 0.00 ) {	
			// Addition for Early Registration discount
			if ( $early_price_data = early_discount_amount( $event_id, $result->event_cost )) {
				$result->event_cost = $early_price_data['event_price'];
			}
		}
		
		if ( event_espresso_verify_price_id( $price_id, $event_id ) == FALSE ){
			$result->event_cost = espresso_return_single_price($event_id);
		}

		$surcharge = event_espresso_calculate_surcharge( $result->event_cost , $result->surcharge, $result->surcharge_type );
		$surcharge = ! empty($surcharge) ? (float)$surcharge : 0;
		$event_cost = $result->event_cost + $surcharge;
		
		
		return (float)number_format( $event_cost, 2, '.', '' ); 
	}

}



//Get the early bird pricing
if (!function_exists('early_discount_amount')) {

    function early_discount_amount( $event_id, $event_cost ) {
 
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		global $wpdb, $org_options;

		$event_id = absint( $event_id );
		$SQL = "SELECT early_disc, early_disc_date, early_disc_percentage FROM " . EVENTS_DETAIL_TABLE . " WHERE id=%d LIMIT 1";

        $eventdata = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id ));
	 
        if ((strlen($eventdata->early_disc) > 0) && (strtotime($eventdata->early_disc_date) > strtotime(date("Y-m-d")))) {
	 
            $early_price_display = $eventdata->early_disc_percentage == 'Y' ? $eventdata->early_disc . '%' : $org_options['currency_symbol'] . $eventdata->early_disc;
		 
            if ($eventdata->early_disc_percentage == 'Y') {
                $pdisc = $eventdata->early_disc / 100;
                $event_cost = $event_cost - ($event_cost * $pdisc);
            } else {
                // Use max function to prevent negative cost when discount exceeds price.
                $event_cost = max(0, $event_cost - $eventdata->early_disc);
            }

            $early_price_data = array('event_price' => $event_cost, 'early_disc' => $early_price_display);
            return $early_price_data;
		 
        } else {
            return false;
        }
    }

}

/* 
 Creates dropdowns if multiple prices are associated with an event
 * @params int $event_id
 * @params int $atts
 *  - bool multi_reg If this is a mutliple regsitration, then it cahnges the registration proerties
 *  - bool show_label Show the label above the dropdown
 *  - var current_value pass the price id to show a selected price by default
*/

if (!function_exists('event_espresso_price_dropdown')) {

    function event_espresso_price_dropdown($event_id, $atts) {
		
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		//Attention:
		//If changes to this function are not appearing, you may have the members addon installed and will need to update the function there.
		//echo "<pre>".print_r($atts,true)."</pre>";
		extract($atts);
        global $wpdb, $org_options;
       	
		$html = '';
		
		$label = $label == '' ? '<span class="section-title">'.__('Choose an Option: ', 'event_espresso').'</span>' : $label;
		
		//Will make the name an array and put the time id as a key so we know which event this belongs to
        $multi_name_adjust = isset($multi_reg) && $multi_reg == true ? "[$event_id]" : '';
       
	    $surcharge_text = isset($org_options['surcharge_text']) ? $org_options['surcharge_text'] : __('Surcharge', 'event_espresso');

        $results = $wpdb->get_results( $wpdb->prepare("SELECT id, event_cost, surcharge, surcharge_type, price_type FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='" . $event_id . "' ORDER BY id ASC", '') );

        if ($wpdb->num_rows > 1) {
           //Create the label for the drop down
			$html .= $show_label == 1 ? '<label for="event_cost">' . $label . '</label>' : '';
	
			//Create a dropdown of prices
			$html .= '<select name="price_option' . $multi_name_adjust . '" id="price_option-' . $event_id . '">';

            foreach ($results as $result) {

                $selected = isset($current_value) && $current_value == $result->id ? ' selected="selected" ' : '';

                // Addition for Early Registration discount
                if ($early_price_data = early_discount_amount($event_id, $result->event_cost)) {
                    $result->event_cost = $early_price_data['event_price'];
                    $message = __(' Early Pricing', 'event_espresso');
                } else {
					$message = '';
				}

                $surcharge = '';

                if ($result->surcharge > 0 && $result->event_cost > 0.00) {
                    $surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} " . $surcharge_text;
                    if ($result->surcharge_type == 'pct') {
                        $surcharge = " + {$result->surcharge}% " . $surcharge_text;
                    }
                }

                //Using price ID
                $html .= '<option' . $selected . ' value="' . $result->id . '|' . stripslashes_deep($result->price_type) . '">' . stripslashes_deep($result->price_type) . ' (' . $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . ') ' . $surcharge . ' </option>';
            }
            $html .= '</select><input type="hidden" name="price_select" id="price_select-' . $event_id . '" value="true" />';
        } else if ($wpdb->num_rows == 1) {
            foreach ($results as $result) {

                // Addition for Early Registration discount
                if ($early_price_data = early_discount_amount($event_id, $result->event_cost)) {
                    $result->event_cost = $early_price_data['event_price'];
                    $message = sprintf(__(' (including %s early discount) ', 'event_espresso'), $early_price_data['early_disc']);
                }

                $surcharge = '';

                if ($result->surcharge > 0) {
                    $surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} " . $surcharge_text;
                    if ($result->surcharge_type == 'pct') {
                        $surcharge = " + {$result->surcharge}% " . $surcharge_text;
                    }
                }
                $message = isset($message) ? $message : '';

              
                $html .= '<span class="event_price_label">' . __('Price:', 'event_espresso') . '</span> <span class="event_price_value">' . $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . $surcharge . '</span>';
                $html .= '<input type="hidden" name="price_id' . $multi_name_adjust . '" id="price_id-' . $result->id . '" value="' . $result->id . '" />';
               
            }
        }
       	echo $html;
		return;
    }
	add_action('espresso_price_select', 'event_espresso_price_dropdown', 20, 2);
}


function espresso_attendee_admin_price_dropdown($event_id, $atts) {
	extract($atts);
	global $wpdb, $org_options, $espresso_premium;

	if ($espresso_premium != true)
		return;
		
	$html = '';
	$label = isset($label) && $label != '' ? $label : '<span class="section-title">'.__('Choose an Option: ', 'event_espresso').'</span>';
	
	$results = $wpdb->get_results("SELECT id, event_cost, surcharge, surcharge_type, price_type FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='" . $event_id . "' ORDER BY id ASC");
	//echo "<pre>".print_r($results,true)."</pre>";
	
	//If more than one price was added to an event, we need to create a drop down to select the price.
	if ($wpdb->num_rows > 1) {
		
		//Create the label for the drop down
		$html .= $show_label == 1 ? '<label for="event_cost">' . $label . '</label>' : '';

		//Create a dropdown of prices
		$html .= '<select name="price_option" id="price_option-' . $event_id . '">';

		 foreach ($results as $result) {
			 
			$selected = isset($current_value) && $current_value == $result->price_type ? ' selected="selected" ' : '';

			// Addition for Early Registration discount
			if ($early_price_data = early_discount_amount($event_id, $result->event_cost)) {
				$result->event_cost = $early_price_data['event_price'];
				$message = __(' Early Pricing', 'event_espresso');
			} else {
				$message = '';
			}

			$surcharge = '';

			if ($result->surcharge > 0 && $result->event_cost > 0.00) {
				$surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} ";
				if ($result->surcharge_type == 'pct') {
					$surcharge = " + {$result->surcharge}% ";
				}
			}

			//Using price ID
			$html .= '<option' . $selected . ' value="' . $result->id . '|' . $result->price_type . '">' . $result->price_type . ' (' . $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . ') ' . $surcharge . ' </option>';
		}
		$html .= '</select><input type="hidden" name="price_select" id="price_select-' . $event_id . '" value="true" />';
		
	}
	//echo 'ts';
	echo $html;
}
add_action('action_hook_espresso_attendee_admin_price_dropdown', 'espresso_attendee_admin_price_dropdown', 10, 2);



//This function gets the first price id associated with an event and displays a hidden field.
function espresso_hidden_price_id($event_id) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
    global $wpdb;
    $wpdb->get_results("SELECT id FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='" . $event_id . "' LIMIT 0,1 ");
    $num_rows = $wpdb->num_rows;
    if ($num_rows > 0) {
        return '<input type="hidden" name="price_id" id="price_id-' . $wpdb->last_result[0]->id . '" value="' . $wpdb->last_result[0]->id . '">';
    } else {
        return '<div style="display:none">' . __('No prices id results.', 'event_espresso') . '</div>';
    }
}

//This function returns the first price id associated with an event
function espresso_get_price_id($event_id) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
    global $wpdb, $org_options;
    $wpdb->get_results("SELECT id FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='" . $event_id . "' LIMIT 0,1 ");
    $num_rows = $wpdb->num_rows;
    if ($num_rows > 0) {
        return $wpdb->last_result[0]->id;
    } else {
        return 0;
    }
}

if (!function_exists('espresso_payment_type')) {

    function espresso_payment_type($type) {
			do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
        switch ($type) {
            case 'web_accept':
                return __('PayPal', 'event_espresso');
                break;
            case 'EW':
                return __('eWay', 'event_espresso');
                break;
            case 'CC':
            case 'PPP':
            case 'auth_capture':
            case 'FD':
                return __('CC', 'event_espresso');
                break;
            case 'INV':
                return __('Invoice', 'event_espresso');
                break;
            case 'OFFLINE':
                return __('Offline payment', 'event_espresso');
                break;
            default:
                return __($type, 'event_espresso');
                break;
        }
    }

}


/**
 * espresso_attendee_price()
 *
 * @return float|null  the price paid for an event by attendee id or the registration id, if information not found then it will return null
 */
if (!function_exists('espresso_attendee_price')) {
	function espresso_attendee_price($atts) {
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		global $wpdb;
		isset($atts) ? extract($atts) : '';
	
		/**
		 * If the registration_id is empty, then retrieve it
		 * */
		$generated_registration_id = false;
		if (!isset($registration_id)){
			if (!isset($attendee_id)){
				return;
			}else{
				$registration_id = espresso_registration_id($attendee_id);
			}
		}
	
		//Found use of single price only in payment option in attendee record edit page for admin.
		if (isset($single_price) && $single_price = true && isset($attendee_id) && $attendee_id > 0 ) {
			$sql = "SELECT final_price FROM " . EVENTS_ATTENDEE_TABLE;
			$sql .= " WHERE id ='%d' LIMIT 0,1";
	
			$res = $wpdb->get_row($wpdb->prepare($sql,$attendee_id));
			if ($res) {
				return number_format($res->final_price, 2, '.', '');
			}
		}
	
		//Return the total amount paid for this registration
		if (isset($reg_total) && $reg_total = true) {
			$sql = "SELECT amount_pd as total FROM " . EVENTS_ATTENDEE_TABLE . " where registration_id = '%s' order by id limit 1";
			$total_cost = $wpdb->get_var($wpdb->prepare($sql,$registration_id));
			return number_format($total_cost, 2, '.', '');
		}
	
	
		//Return the total amount paid for a session. Uses the registration id.
		if (isset($session_total) && $session_total = true) {
			$attendee_session = $wpdb->get_var($wpdb->prepare("select attendee_session from ".EVENTS_ATTENDEE_TABLE." where registration_id = '%s' ",$registration_id));
			if ( !empty($attendee_session) ){
				//If attendee_session is empty then return only single attendee information
				$total_cost = 0;
				$total_cost = $wpdb->get_var($wpdb->prepare("select sum(amount_pd) as amount_pd from ".EVENTS_ATTENDEE_TABLE." where attendee_session = '%s'",$attendee_session));
				return number_format($total_cost, 2, '.', '');
			}else{
				$primary_registration_id = $registration_id;
				$rs = $wpdb->get_row($wpdb->prepare("select primary_registration_id from ".EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE." where registration_id = '%s' limit 0,1 ",$registration_id));
				if ( $rs !== NULL ){
					$primary_registration_id = $rs->primary_registration_id;
				}
				$sql = "select sum(amount_pd) as total from " . EVENTS_ATTENDEE_TABLE . " where registration_id = '%s' ";
				$total_cost = $wpdb->get_var($wpdb->prepare($sql,$primary_registration_id));
				return number_format($total_cost, 2, '.', '');
			}
		}
	
	
		//Return the amount paid for an individual attendee
		if (isset($attendee_id) && $attendee_id > 0) {
			$sql = "SELECT final_price, quantity FROM " . EVENTS_ATTENDEE_TABLE;
			$sql .= " WHERE id ='%d' LIMIT 0,1";
	
			$res = $wpdb->get_row($wpdb->prepare($sql,$attendee_id));
						
			if ($res) {
				$total_cost = $res->final_price * $res->quantity;
				return number_format($total_cost, 2, '.', '');
			}
		}
	
		//If no results are returned above or the registration id was passed, then get the price by looking in EVENTS_ATTENDEE_TABLE
		$sql = "SELECT amount_pd FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id ='" . $registration_id . "' ORDER BY id LIMIT 0,1";
		$wpdb->get_results($sql);
		if ($wpdb->num_rows >= 1) {
			return number_format($wpdb->last_result[0]->amount_pd, 2, '.', '');
		}
		return NULL;
	}
}

function get_reg_total_price($registration_id){
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
}

function espresso_selected_price_option($selected){
	if (empty($selected)) return false;
	$price_options = explode( '|', $selected, 2 );
	$price_id = $price_options[0];
	$price_type = $price_options[1];
	
	return array('price_id' => $price_id, 'price_type' => $price_type);
}
