<?php
// check for coupon 
if ( ! function_exists( 'event_espresso_process_coupon' )) {
	function event_espresso_process_coupon( $event_id, $event_cost, $attendee_id, $mer ) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		
 
		if ( isset( $_POST['event_espresso_coupon_code'] )) {		
			$use_coupon = isset( $_POST['use_coupon'][$event_id] ) ? $_POST['use_coupon'][$event_id] : 'N';
			//echo '<h4>$use_coupon : ' . $use_coupon . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
			return event_espresso_coupon_payment_page( $use_coupon, $event_id, $event_cost, $attendee_id, $mer );
		} else {
			return FALSE;
		}
	}
}



if ( ! function_exists( 'event_espresso_coupon_payment_page' )) {
	function event_espresso_coupon_payment_page( $use_coupon_code = 'N', $event_id = FALSE, $event_cost = 0.00, $attendee_id = FALSE, $mer = TRUE ) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		
 
		global $espresso_premium;		
		if ( ! $espresso_premium ) {
			return FALSE;
		}

		$event_cost = (float)$event_cost;
		//echo '<h4>$event_cost : ' . $event_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
		
		if ( $use_coupon_code == 'Y' && $event_cost > 0 ) {
			if ( ! empty( $_REQUEST['event_espresso_coupon_code'] ) || ! empty( $_POST['event_espresso_coupon_code'] )){

			global $wpdb;
			$percentage = FALSE;
			$discount_type_price = '';
			$msg = '';
			$error = '';
			$event_id = absint( $event_id );
			$coupon_id = FALSE;
					
			
			$coupon_code = ! empty( $_POST['event_espresso_coupon_code'] ) ? wp_strip_all_tags( $_POST['event_espresso_coupon_code'] ) : '';
			
			if ( isset( $_SESSION['espresso_session']['events_in_session'][ $event_id ] )) {
			// check if coupon has already been added to session
				if ( isset( $_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon']['code'] ) && $_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon']['code'] == $coupon_code ) {
					// grab values from session
					$coupon = $_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon'];
			                	$valid = TRUE;
			                	$coupon_id = $coupon['id'];
					$coupon_code = $coupon['code'];
			                	$coupon_amount = (float)$coupon['coupon_code_price'];
			                	$coupon_code_description = $coupon['coupon_code_description'];
			                	$use_percentage = $coupon['use_percentage'];
				}
				
			} else {
			
				$SQL = "SELECT d.* FROM " . EVENTS_DISCOUNT_CODES_TABLE . " d ";
				$SQL .= "JOIN " . EVENTS_DISCOUNT_REL_TABLE . " r ON r.discount_id  = d.id ";
				$SQL .= "WHERE d.coupon_code = %s";
			        $SQL .= $event_id ? " AND r.event_id = '" . $event_id . "'" : '';
				
				if ( $coupon = $wpdb->get_row( $wpdb->prepare( $SQL, $coupon_code ))) {	
				
					$valid = TRUE;
					$coupon_id = $coupon->id;
					$coupon_code = $coupon->coupon_code;
					$coupon_amount = (float)$coupon->coupon_code_price;
					$coupon_code_description = $coupon->coupon_code_description;
					$use_percentage = $coupon->use_percentage;
				}
				
			}
			
//			echo '<h4>$coupon_id : ' . $coupon_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
	 
			if ( $coupon_id ) {				
						
				$discount_type_price = $use_percentage == 'Y' ? number_format( $coupon_amount, 1, '.', '' ) . '%' : $org_options['currency_symbol'] . number_format( $coupon_amount, 2, '.', '' );
				$discount = 0;
					
					if ( $use_percentage == 'Y' ) {
						$percentage = TRUE;
						$pdisc = $coupon_amount / 100;
						$discount = $event_cost * $pdisc;
						$event_cost = $event_cost - (float)$discount;
					} else {
						$event_cost = $event_cost - $coupon_amount;
						$discount = $coupon_amount;
					}
										
					$event_cost = (float)$event_cost > 0.00 ? (float)$event_cost : 0.00;

do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'line '. __LINE__ .' : $event_cost=' . $event_cost );
					
//					if ( $mer ) {
					
						$coupon_details = array();					
						$coupon_details['id'] = $coupon->id;
						$coupon_details['code'] = $coupon->coupon_code;
						$coupon_details['coupon_code_price'] = $coupon->coupon_code_price;
						$coupon_details['coupon_code_description'] = $coupon->coupon_code_description;
						$coupon_details['use_percentage'] = $coupon->use_percentage;
						$coupon_details['discount'] = $discount;
						
						$_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon'] = $coupon_details;
						
						$msg = '<p id="event_espresso_valid_coupon" style="margin:0;">';
						$msg .= '<strong>' . __('Promotional code ', 'event_espresso') . $coupon_code . '</strong> ( ' . $discount_type_price . __(' discount', 'event_espresso') . ' )<br/>';
	          		    $msg .= __('has being successfully applied to the following events', 'event_espresso') . ':<br/>';
						
//					}								

	            } else {
				
					$valid = FALSE;
					if ( $mer ) {
						$error = '<p id="event_espresso_invalid_coupon" style="margin:0;"><font color="red">' . __('Sorry, promotional code ', 'event_espresso') . '<strong>' . $coupon_code . '</strong>' . __(' is invalid or expired.', 'event_espresso') . '</font></p>';
					}
					
	            }
				
				return array( 'event_cost'=>$event_cost, 'valid'=>$valid, 'percentage'=>$percentage, 'discount'=>$discount_type_price, 'msg' => $msg, 'error' => $error );

			}
        }

		return FALSE;		
 
   }
}




function espresso_update_attendee_coupon_info( $attendee_id = FALSE, $event_id, $final_price = FALSE ) {

	if ( ! $attendee_id || ! $event_id || ! $final_price ) {
		return FALSE;
	}

	if ( isset( $_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon'] )) {
	
		$coupon_details = $_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon'];
	
		global $wpdb;
		
		$set_cols_and_values = array( 'coupon_code' => $coupon_details['code'], 'final_price' => $final_price );
		$set_format = array( '%s', '%f', '%s', '%s' );
		$where_cols_and_values = array( 'id' => $attendee_id );
		$where_format = array( '%d' );

		if ( $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format )) {

			//Get Registration ID
			$reg_ID = "SELECT registration_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = %d";
			if ( $registration_ID = $wpdb->get_var( $wpdb->prepare( $SQL, $attendee_id ))) {

				// Update OTHER attendees that share the same registration ID
				$SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . ' ';
				$SQL .= "SET amount_pd = %f, payment_date= %s, coupon_code = %s ";
				$SQL .= "WHERE registration_id = %d AND id != %d";
				$wpdb->query( $wpdb->prepare( $SQL, 0.00, date('m-d-Y'), $coupon_details['code'], $registration_ID, $attendee_id ));

			}
		}		
	}

}





if (!function_exists('event_espresso_coupon_registration_page')) {

    function event_espresso_coupon_registration_page( $use_coupon_code = 'N', $event_id, $multi_reg = FALSE ) {
	
        global $espresso_premium;
        if ( ! $espresso_premium ) {
			return;
		}
            
        if ( $use_coupon_code == "Y" ) {

            $multi_reg_adjust = $multi_reg ? "[$event_id]" : '';	 

            $output ='
	<p class="event_form_field coupon_code" id="coupon_code-' . $event_id . '">
		<label for="coupon_code">' . __('Enter Promotional/Discount Code', 'event_espresso') . ':</label>
		<input type="text" tabIndex="9" maxLength="25" size="35" name="event_espresso_coupon_code'. $multi_reg_adjust . '" id="coupon_code-' . $event_id . '">
	</p>';
			
        } else {
			$output = '';
		}
		
        return $output;
    }

}
