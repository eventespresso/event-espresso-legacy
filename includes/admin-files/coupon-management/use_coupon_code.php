<?php
// check for coupon 
if ( ! function_exists( 'event_espresso_process_coupon' )) {
	function event_espresso_process_coupon( $event_id, $event_cost, $mer ) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		

		$use_coupon_code = isset( $_POST['use_coupon'][$event_id] ) ? $_POST['use_coupon'][$event_id] : 'N';				

		if ( $mer ) {
			if ( isset( $_SESSION['espresso_session']['events_in_session'][$event_id]['coupon'] )) {
				$coupon_code = isset( $_SESSION['espresso_session']['events_in_session'][$event_id]['coupon']['code'] ) ? wp_strip_all_tags( $_SESSION['espresso_session']['events_in_session'][$event_id]['coupon']['code'] ) : FALSE;
				$use_coupon_code = $coupon_code ? 'Y' : 'N';
			}
			
		} else {
			$coupon_code = isset( $_POST['event_espresso_coupon_code'] ) ? wp_strip_all_tags( $_POST['event_espresso_coupon_code'] ) : '';
		}

		return event_espresso_coupon_payment_page( $event_id, $event_cost, $mer, $use_coupon_code );

	}
}



if ( ! function_exists( 'event_espresso_coupon_payment_page' )) {
	function event_espresso_coupon_payment_page( $event_id = FALSE, $event_cost = 0.00, $mer = TRUE, $use_coupon_code = 'N' ) {
	
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		
 
		global $espresso_premium,$org_options;		
		if ( ! $espresso_premium ) {
			return FALSE;
		}

		$event_cost = (float)$event_cost;
//		echo '<h4>$event_id : ' . $event_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//		echo '<h4>$event_cost : ' . $event_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
		
		$coupon_code = isset( $_POST['event_espresso_coupon_code'] ) ? wp_strip_all_tags( $_POST['event_espresso_coupon_code'] ) : FALSE;
		if ( $coupon_code === FALSE ) {
			$coupon_code = isset( $_SESSION['espresso_session']['event_espresso_coupon_code'] ) ? wp_strip_all_tags( $_SESSION['espresso_session']['event_espresso_coupon_code'] ) : FALSE;
		}
//		echo '<h4>$coupon_code : ' . $coupon_code . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';			
		
		if ( ! $use_coupon_code ) {
			$use_coupon_code = isset( $_POST['use_coupon'][$event_id] ) ? $_POST['use_coupon'][$event_id] : 'N';			
		}
//		echo '<h4>$use_coupon_code : ' . $use_coupon_code . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
		
		if ( apply_filters( 'filter_hook_espresso_admin_use_any_coupon_code', true ) && is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return array( 
				'event_cost' => $event_cost, 
				'valid'      => TRUE,
				'code'       => $coupon_code
			);
		}
		if ( in_array($use_coupon_code, array('Y',"G","A")) && ( $event_cost > 0 || is_admin() ) ) {
//			echo "cuopon code $coupon_code";
			if ( $coupon_code ){

				global $wpdb;
				$percentage = FALSE;
				$discount_type_price = '';
				$msg = '';
				$error = '';
				$event_id = absint( $event_id );
				$coupon_id = FALSE;
						
				if ( isset( $_SESSION['espresso_session']['events_in_session'][ $event_id ] ) && isset( $_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon']['code'] ) && $_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon']['code'] == $coupon_code) {
					// check if coupon has already been added to session
						// grab values from session
						$coupon = $_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon'];
						//printr( $coupon, '$coupon  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
	                	$valid = TRUE;
	                	$coupon_id = $coupon['id'];
						$coupon_code = $coupon['code'];
	                	$coupon_amount = (float)$coupon['coupon_code_price'];
	                	$coupon_code_description = $coupon['coupon_code_description'];
	                	$use_percentage = $coupon['use_percentage'];

					
				} else {//ask the DB if the promocode is valid
					
					$SQL = "SELECT d.* FROM " . EVENTS_DISCOUNT_CODES_TABLE . " d ";
					$SQL .= " LEFT JOIN " . EVENTS_DISCOUNT_REL_TABLE . " r ON r.discount_id  = d.id ";
					$SQL .= "WHERE d.coupon_code = %s ";
					if($use_coupon_code != 'A'){//if $use_coupon_code is 'A', then we use ALL coupon codes, regardless of whether htey 'apply_to_all', or have a relation to this event
						$SQL .= " AND ";
						$SQL .= $event_id ? " (r.event_id = '" . $event_id . "' OR " : '';
						$SQL .= " d.apply_to_all = 1";
						$SQL .= $event_id ? " ) ": '';
					}
					$prepared_SQL = $wpdb->prepare( $SQL, $coupon_code );
					if ( $coupon = $wpdb->get_row( $prepared_SQL )) {	
					
						$valid = TRUE;
						$coupon_id = $coupon->id;
						$coupon_code = $coupon->coupon_code;
						$coupon_amount = (float)$coupon->coupon_code_price;
						$coupon_code_description = $coupon->coupon_code_description;
						$use_percentage = $coupon->use_percentage;
					}
					
				}
				
//				echo '<h4>$coupon_id : ' . $coupon_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
		 
				if ( $coupon_id ) {				
							
					$discount_type_price = $use_percentage == 'Y' ? number_format( $coupon_amount, 1, '.', '' ) . '%' : $org_options['currency_symbol'] . number_format( $coupon_amount, 2, '.', '' );
					$discount = 0;

//				echo '<h4>$coupon_code : ' . $coupon_code . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//				echo '<h4>$coupon_amount : ' . $coupon_amount . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//				echo '<h4>$use_percentage : ' . $use_percentage . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					
					if ( $use_percentage == 'Y' ) {
						$percentage = TRUE;
						$pdisc = (float)$coupon_amount / 100;
//						echo '<h4>$pdisc : ' . $pdisc . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
						$discount = (float)$event_cost * (float)$pdisc;
						$event_cost = $event_cost - (float)$discount;
					} else {
						$event_cost = $event_cost - $coupon_amount;
						$discount = $coupon_amount;
					}
										
					$event_cost = (float)$event_cost > 0.00 ? (float)$event_cost : 0.00;
//					echo '<h4>$discount : ' . $discount . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//					echo '<h4>$event_cost : ' . $event_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//					echo '<h4>$mer : ' . $mer . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

					do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'line '. __LINE__ .' : $event_cost=' . $event_cost );
					
					if ( $mer ) {
					
						$coupon_details = array();					
						$coupon_details['id'] = $coupon_id;
						$coupon_details['code'] = $coupon_code;
						$coupon_details['coupon_code_price'] = $coupon_amount;
						$coupon_details['coupon_code_description'] = $coupon_code_description;
						$coupon_details['use_percentage'] = $use_percentage;
						$coupon_details['discount'] = $discount;
//						printr( $coupon_details, '$coupon_details  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
						$_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon'] = $coupon_details;
						
						$msg = '<p id="event_espresso_valid_coupon" style="margin:0;">';
						$msg .= '<strong>' . __('Promotional code ', 'event_espresso') . $coupon_code . '</strong> ( ' . $discount_type_price . __(' discount', 'event_espresso') . ' )<br/>';
	          		    $msg .= __('has been successfully applied to the following events', 'event_espresso') . ':<br/>';						
					} else {
					
						$msg = '<p id="event_espresso_valid_coupon" style="margin:0;">';
						$msg .= '<strong>' . __('Promotional code ', 'event_espresso') . $coupon_code . '</strong> ( ' . $discount_type_price . __(' discount', 'event_espresso') . ' )<br/>';
	          		    $msg .= __('has been successfully applied to your registration', 'event_espresso');
	          		    $msg .= '</p>';
						
					}							

	            } else {
				
					$valid = FALSE;
					if ( $mer ) {
					
						$error = '<p id="event_espresso_invalid_coupon" style="margin:0;color:red;">' . __('Sorry, promotional code ', 'event_espresso') . '<strong>' . $coupon_code . '</strong>' . __(' is invalid, expired, or can not be used for the event(s) you are applying it to.', 'event_espresso') . '</p>';
						
					} else {
					
						$msg = '<p id="event_espresso_invalid_coupon" style="margin:0;color:red;">';
						$msg .= __('Sorry, promotional code ', 'event_espresso') . '<strong>' . $coupon_code . '</strong>';
						$msg .= __(' is either invalid, expired, or can not be used for the event(s) you are applying it to.', 'event_espresso');
	          		    $msg .= '</p>';
						
					}
					
	            }
//				printr( $_SESSION, '$_SESSION  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				return array( 'event_cost'=>$event_cost, 'valid'=>$valid, 'percentage'=>$percentage, 'discount'=>$discount_type_price, 'msg' => $msg, 'error' => $error, 'code' => $coupon_code );

			}
        }

		return FALSE;		
 
   }
}




function espresso_update_attendee_coupon_info( $primary_att_id = FALSE, $coupon_code = FALSE ) {

	if ( ! $primary_att_id || ! $coupon_code ) {
		return FALSE;
	}
//	echo '<h4>$primary_att_id : ' . $primary_att_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>$final_price : ' . $final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
	
		global $wpdb;
		
		$set_cols_and_values = array( 'coupon_code' => $coupon_code );
		$set_format = array( '%s' );
		$where_cols_and_values = array( 'id' => $primary_att_id );
		$where_format = array( '%d' );

		if ( $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format )) {
			
			//Get Registration ID
			$SQL = "SELECT registration_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = %d";
			if ( $registration_ID = $wpdb->get_var( $wpdb->prepare( $SQL, $primary_att_id ))) {
				// Update OTHER attendees that share the same registration ID
				$SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . ' ';
				$SQL .= "SET amount_pd = %f, coupon_code = %s ";
				$SQL .= "WHERE registration_id = %d AND id != %d";
				$wpdb->query( $wpdb->prepare( $SQL, 0.00, $coupon_code, $registration_ID, $primary_att_id ));
			}				
		}		

}

if (!function_exists('event_espresso_coupon_registration_page')) {
    function event_espresso_coupon_registration_page( $use_coupon_code = 'N', $event_id, $class =' ee-reg-page-text-input ', $multi_reg = FALSE ) {
	
        global $espresso_premium;
        if ( ! $espresso_premium ) {
			return;
		}
            
        if ( in_array($use_coupon_code,array("Y", "G", "A")) ) {

            $multi_reg_adjust = $multi_reg ? "[$event_id]" : '';	 

            $output ='
	<p class="event_form_field coupon_code" id="coupon_code-' . $event_id . '">
		<label for="coupon_code" class="inline">' . __('Promo Code', 'event_espresso') . ':</label>
					<input type="text" class="espresso-coupon-code ' . $class . '" name="event_espresso_coupon_code'. $multi_reg_adjust . '" id="coupon_code-' . $event_id . '">
	</p>';
			
        } else {
			$output = '';
		}
		
        return $output;
		
    }
}
