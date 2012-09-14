<?php
if ( ! function_exists( 'event_espresso_coupon_payment_page' )) {
	function event_espresso_coupon_payment_page( $use_coupon_code = 'N', $event_id = FALSE, $event_cost = 0.00, $attendee_id = FALSE, $mer = TRUE ) {
	
        global $espresso_premium;		
        if ( ! $espresso_premium ) {
            return;
		}
		
        if ( $use_coupon_code == 'Y' && $event_cost > 0 ) {
            if ( ! empty( $_REQUEST['coupon_code'] ) || ! empty( $_POST['event_espresso_coupon_code'] )) {

				global $wpdb;
				$percentage = FALSE;
				$discount_type_price = '';
				$msg = '';
				$event_id = absint( $event_id );
				
               $coupon_code = ! empty( $_POST['event_espresso_coupon_code'] ) ? wp_strip_all_tags( $_POST['event_espresso_coupon_code'] ) : wp_strip_all_tags( $_REQUEST['coupon_code'] );

				$SQL = "SELECT d.* FROM " . EVENTS_DISCOUNT_CODES_TABLE . " d ";
				$SQL .= "JOIN " . EVENTS_DISCOUNT_REL_TABLE . " r ON r.discount_id  = d.id ";
				$SQL .= "WHERE d.coupon_code = %s";
		        $SQL .= $event_id ? " AND r.event_id = '" . $event_id . "'" : '';
				
				if ( $coupon = $wpdb->get_row( $wpdb->prepare( $SQL, $coupon_code ))) {	
				
                    $valid = TRUE;
                    $coupon_code = $coupon->coupon_code;
                    $coupon_amount = $coupon->coupon_code_price;
                    $coupon_code_description = $coupon->coupon_code_description;
                    $use_percentage = $coupon->use_percentage;
					
                    $discount_type_price = $use_percentage == 'Y' ? number_format( $coupon_amount, 1, '.', '' ) . '%' : $org_options['currency_symbol'] . number_format( $coupon_amount, 2, '.', '' );
					
                    if ( $use_percentage == 'Y' ) {
						$percentage = TRUE;
                        $pdisc = $coupon_amount / 100;
                        $event_cost = $event_cost - ($event_cost * $pdisc);
                    } else {
                        $event_cost = $event_cost - $coupon_amount;
                    }

					if ( ! $mer ) {
					
	                    $payment_status = 'Incomplete';
	                    if ($event_cost == 0.00) {
	                        $event_cost = '0.00';
	                        $payment_status = 'Completed';
	                        //event_espresso_email_confirmations($attendee_id, 'TRUE', 'TRUE' );
	                        //event_espresso_email_confirmations(array('attendee_id' => $attendee_id, 'send_admin_email' => 'TRUE', 'send_attendee_email' => 'TRUE'));
	                    }

	                    //if attendee id is supplied, update
	                    if ( $attendee_id ) {
					
							$today = date("m-d-Y");
	                       	$set_cols_and_values = array( 'coupon_code' => $coupon_code, 'amount_pd' => $event_cost, 'payment_status' => $payment_status, 'payment_date' => $today );
	                        $set_format = array( '%s', '%f', '%s', '%s' );
	                        $where_cols_and_values = array( 'id' => $attendee_id );
							$where_format = array( '%d' );

							if ( $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format )) {

		                        //Get Registration ID
		                        $reg_ID = "SELECT registration_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = %d";
		                        if ( $registration_ID = $wpdb->get_var( $wpdb->prepare( $SQL, $attendee_id ))) {
									
			                        //Update OTHER attendees that share the same registration ID
							        $SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = %s, amount_pd = %f, payment_date= %s, coupon_code = %s WHERE registration_id = %d AND id != %d";
									$wpdb->query( $wpdb->prepare( $SQL, $payment_status, 0.00, $today, $coupon_code, $registration_ID, $attendee_id ));
																		
								}															
							}
						}
					
					} else {
					
						$coupon_details = array();					
						$coupon_details['id'] = $coupon->id;
						$coupon_details['code'] = $coupon->coupon_code;
						$coupon_details['status'] = $coupon->coupon_status;
						$coupon_details['holder'] = $coupon->coupon_holder;
						$coupon_details['discount'] = $event_cost;
						$_SESSION['espresso_session']['events_in_session'][ $event_id ]['coupon'] = $coupon_details;
						$msg = '<p id="event_espresso_valid_coupon" style="margin:0;">';
						$msg .= '<strong>' . __('Promotional code ', 'event_espresso') . $coupon_code . '</strong> ( ' . $discount_type_price . __(' discount', 'event_espresso') . ' )<br/>';
              		    $msg .= __('has being successfully applied to the following events', 'event_espresso') . ':<br/>';
						
					}								

                } else {
				
					$valid = FALSE;
					if ( $mer ) {
						$msg = '<p id="event_espresso_invalid_coupon" style="margin:0;">' . __('Sorry, promotional code ', 'event_espresso') . '<strong>' . $coupon_code . '</strong>' . __(' is invalid or expired.', 'event_espresso') . '</p>';
					}
					
                }
				
				return array( 'event_cost'=>$event_cost, 'valid'=>$valid, 'percentage'=>$percentage, 'discount'=>$discount_type_price, 'msg' => $msg );

			}
        }

		return FALSE;		
 
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

            $output ='<p class="event_form_field coupon_code" id="coupon_code-' . $event_id . '">';
            $output .= '<label for="coupon_code">' . __('Enter Promotional/Discount Code', 'event_espresso') . ':</label>';
            $output .= '<input type="text" tabIndex="9" maxLength="25" size="35" name="coupon_code'. $multi_reg_adjust . '" id="coupon_code-' . $event_id . '"></p>';
			
        } else {
			$output = '';
		}
		
        return $output;
    }

}
