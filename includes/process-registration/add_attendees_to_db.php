<?php
if (!function_exists('event_espresso_add_attendees_to_db')) {
	//This entire function can be overridden using the "Custom Files" addon
	function event_espresso_add_attendees_to_db($event_id = null, $session_vars = null){
		global $wpdb, $org_options;

                $data_source = $_POST;
                $att_data_source = $_POST;
                $multi_reg = false;
                static $attendee_number = 1; //using this var to keep track of the first attendee
                static $loop_start = 1;
                if (!is_null($event_id) && !is_null($session_vars)){
                    $data_source = $session_vars['data']; //event details, ie qty, price, start..
                    $att_data_source = $session_vars['event_attendees']; //event attendee info ie name, questions....

                    $multi_reg = true;

                } else {
                    $event_id=$data_source['event_id'];
                }

                //echo "<pre>", print_r($data_source), "</pre>";
                //echo "<pre>", print_r($att_data_source), "</pre>";
               // exit;
		$Organization = $org_options['organization'];
		$Organization_street1 = $org_options['organization_street1'];
		$Organization_street2 = $org_options['organization_street2'];
		$Organization_city = $org_options['organization_city'];
		$Organization_state = $org_options['organization_state'];
		$Organization_zip = $org_options['organization_zip'];
		$contact = $org_options['contact_email'];
		$contact_email = $org_options['contact_email'];
		$paypal_id = $org_options['paypal_id'];
		$paypal_cur = $org_options['currency_format'];
		$return_url = $org_options['return_url'];
		$cancel_return = $org_options['cancel_return'];
		$notify_url = $org_options['notify_url'];
		
		$default_mail= $org_options['default_mail'];
		$conf_message = $org_options['message'];
		$email_before_payment = $org_options['email_before_payment'];

		$fname = $att_data_source['fname'];
		$lname = $att_data_source['lname'];
		$address = $att_data_source['address'];
		$address2 = $att_data_source['address2'];
		$city = $att_data_source['city'];
		$state = $att_data_source['state'];
		$zip = $att_data_source['zip'];
		$phone = $att_data_source['phone'];
		$email = $att_data_source['email'];
		//$num_people = $data_source ['num_people'];

		$questions = $wpdb->get_row ( "SELECT question_groups, event_meta FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "'" );

		$event_meta=unserialize($questions->event_meta);
		//$payment = $data_source['payment'];
		
		//Figure out if the person has registered using a price selection
		if($multi_reg){
			$event_cost = $_SESSION['event_espresso_grand_total'];
			$amount_pd = $attendee_number==1?$event_cost:0;
			$coupon_code = $attendee_number==1?$_SESSION['event_espresso_coupon_code']:'';
                        $price_type = (isset($data_source['price_type']))?$data_source['price_type']:espresso_ticket_information(array('type'=>'ticket','price_option'=>$data_source['price_id']));
			$attendee_number++;
		}                
		elseif ($data_source['price_select'] ==true){
			$price_options = explode('|',$data_source['price_option'], 2);
			$price_id = $price_options[0];
			$price_type = $price_options[1];
			$event_cost = event_espresso_get_final_price($price_id, $event_id);
		}else{
			$event_cost = event_espresso_get_final_price($data_source['price_id'], $event_id);
                        $coupon_code = '';
			$price_type = espresso_ticket_information(array('type'=>'ticket','price_option'=>$data_source['price_id']));
		}

		//Display the confirmation page
		if ($data_source['confirm_registration'] == 'true'){
			$registration_id = $data_source['registration_id'];
			echo espresso_confirm_registration($registration_id);
			return;
		}
		
	//Check to see if the registration id already exists
	$check_sql = $wpdb->get_results("SELECT attendee_session, id, registration_id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE attendee_session ='" . $_SESSION['espresso_session_id'] . "' AND event_id ='" . $event_id . "' AND payment_status ='Incomplete'");
	$num_rows = $wpdb->num_rows;
	
	$registration_id = $wpdb->last_result[0]->registration_id == '' ? $registration_id=uniqid('', true) : $wpdb->last_result[0]->registration_id;
	
	if (isset($data_source['admin'])) {
		 $payment_status = "Completed";
		 $payment = "Admin";
		 $payment_date = date("m-d-Y");
		 $amount_pd = $data_source["event_cost"];
		 $registration_id=uniqid('', true);
		 $_SESSION['espresso_session_id']='';
	} else{

		if ($org_options['use_captcha'] == 'Y'){//Recaptcha portion
			//require_once('includes/recaptchalib.php');
			if (!function_exists('recaptcha_check_answer')) {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/recaptchalib.php');
			}
			$resp = recaptcha_check_answer ($org_options['recaptcha_privatekey'], $_SERVER["REMOTE_ADDR"], $data_source["recaptcha_challenge_field"], $data_source["recaptcha_response_field"]);
			if (!$resp->is_valid) {
				echo '<h2 style="color:#FF0000;">'.__('Sorry, you did not enter the correct anti-spam phrase. Please click your browser\'s back button and try again.','event_espresso').'</h2>';
				return;
			}
		}
                
		// Automatically set payment status to Incomplete until a payment transaction is cleared
		$payment_status = "Incomplete";
        }
		//$times = $wpdb->get_results("SELECT * FROM ". EVENTS_START_END_TABLE ." WHERE id='" . $data_source['start_time_id'] . "'");
		
		$times_sql =  "SELECT ese.start_time, ese.end_time, e.start_date, e.end_date ";
		$times_sql .= "FROM " .EVENTS_START_END_TABLE. " ese ";
		$times_sql .= "LEFT JOIN " .EVENTS_DETAIL_TABLE. " e ON ese.id WHERE ese.id='" . $data_source['start_time_id'] . "' AND e.id='" . $event_id . "' ";

		$times = $wpdb->get_results($times_sql);
		foreach ($times as $time){
			$start_time = $time->start_time;
			$end_time = $time->end_time;
			$start_date = $time->start_date;
			$end_date = $time->end_date;
		}
	
	
	//If we are using the number of attendees dropdown, add that number to the DB
	//echo $data_source['espresso_addtl_limit_dd'];
	if (isset($data_source['espresso_addtl_limit_dd']))
		$num_people = $data_source ['num_people'];

        if (isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == 1)
            $num_people = $data_source ['attendee_quantitiy'];

		$sql=array('registration_id'=>$registration_id,'attendee_session'=>$_SESSION['espresso_session_id'], 'lname'=>$lname, 'fname'=>$fname, 'address'=>$address, 'address2'=>$address2, 'city'=>$city, 'state'=>$state, 'zip'=>$zip, 'email'=>$email, 'phone'=>$phone, 'payment'=>$payment, 'amount_pd'=>$amount_pd, 'coupon_code'=>$coupon_code, 'event_time'=>$start_time, 'end_time'=>$end_time, 'start_date'=>$start_date, 'end_date'=>$end_date, 'price_option'=>$price_type, 'organization_name'=>$organization_name, 'country_id'=>$country_id, 'payment_status'=>$payment_status, 'payment_date'=>$payment_date, 'event_id'=>$event_id, 'quantity'=>$num_people);
		$sql_data = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
						  '%s','%s','%s','%s','%s','%s','%d','%d');

		//Debugging output
		/* echo 'Debug: <br />';
	 	  print_r($sql);
		  echo '<br />';
          print 'Number of vars: ' . count ($sql);
          echo '<br />';
          print 'Number of cols: ' . count($sql_data); */

		if ($num_rows > 0 && $loop_start == 1){
		
			//echo 'Num rows:'.$num_rows;
			
			
			//Delete the old data.
			//$wpdb->query(" DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = '" . $registration_id . "'	AND attendee_session ='" . $_SESSION['espresso_session_id'] . "' ");
			//$wpdb->query(" DELETE FROM " . EVENTS_ANSWER_TABLE . " WHERE registration_id = '" . $registration_id . "' ");
			if (!isset($data_source['admin']))
			$wpdb->query(" DELETE t1, t2 FROM " . EVENTS_ATTENDEE_TABLE . "  t1 JOIN  " . EVENTS_ANSWER_TABLE . " t2 on t1.id = t2.attendee_id WHERE t1.attendee_session ='" . $_SESSION['espresso_session_id'] . "'  AND payment_status ='Incomplete' ");

                        $loop_start++;
		}
			//Add new or updated data		
			if (!$wpdb->insert( EVENTS_ATTENDEE_TABLE, $sql, $sql_data)){
				$error = true;
			}

			$attendee_id = $wpdb->insert_id;

			add_attendee_questions($questions, $registration_id, $attendee_id, array('session_vars'=>$att_data_source));
	
			//Add additional attendees to the database
			if (isset($att_data_source['x_attendee_fname'])) {
                                $amount_pd = 0; //additional attendee can't hold this info
				foreach ($att_data_source['x_attendee_fname'] as $k => $v){
					if (trim($v) != '' && trim($att_data_source['x_attendee_lname'][$k]) != ''){
				
						$sql_a=array('registration_id'=>$registration_id, 'attendee_session'=>$_SESSION['espresso_session_id'], 'lname'=>$att_data_source['x_attendee_lname'][$k], 'fname'=>$v, 'email'=>$att_data_source['x_attendee_email'][$k], 'address'=>$address, 'address2'=>$address2, 'city'=>$city, 'state'=>$state, 'zip'=>$zip, 'phone'=>$phone, 'payment'=>$payment, 'amount_pd'=>$amount_pd, 'event_time'=>$start_time, 'end_time'=>$end_time, 'start_date'=>$start_date, 'end_date'=>$end_date, 'price_option'=>$price_type, 'organization_name'=>$organization_name, 'country_id'=>$country_id, 'payment_status'=>$payment_status, 'payment_date'=>$payment_date, 'event_id'=>$event_id, 'quantity'=>$num_people);
						$sql_data_a = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
						  '%s','%s','%s','%s','%s','%s','%d','%d');
									  
						$wpdb->insert( EVENTS_ATTENDEE_TABLE, $sql_a, $sql_data_a);
                                                
							//Debugging output
							/* echo 'Debug: <br />';
							  print_r($sql);
							  echo '<br />';
							  print 'Number of vars: ' . count ($sql);
							  echo '<br />';
							  print 'Number of cols: ' . count($sql_data); */
		  
							echo add_attendee_questions($questions, $registration_id, $wpdb->insert_id, array('session_vars'=>$att_data_source));
								//echo 'Failed';
							//echo $wpdb->insert_id;
						
					}
					
				}
			}
	
	
			//Add user data if needed
			if (get_option('events_members_active') == 'true'){
				require_once(EVENT_ESPRESSO_MEMBERS_DIR . "member_functions.php"); //Load Members functions
				require_once(EVENT_ESPRESSO_MEMBERS_DIR . "user_vars.php"); //Load Members functions
				if ($userid != 0){
					event_espresso_add_user_to_event($event_id, $userid, $attendee_id);
				}
			}
				//This shows the payment page
				if (isset($data_source['admin'])) return $attendee_id;
				//return event_espresso_payment_confirmation($attendee_id);
                                if (!$multi_reg) return events_payment_page($attendee_id);


    }
}

if ( !function_exists( 'event_espresso_add_attendees_to_db_multi' ) )
{


    //This function is called from the shopping cart

    function event_espresso_add_attendees_to_db_multi() {
        global $wpdb, $org_options;

        $events_in_session = $_SESSION['events_in_session'];
        if (event_espresso_invoke_cart_error($events_in_session))
            return false;
        
        $count_of_events = count( $events_in_session );
        $current_session_id = $_SESSION['espresso_session_id'];
        //echo "<pre>", print_r($_SESSION), "</pre>";
        //echo "<pre>", print_r($events_in_session), "</pre>";
        //echo "<pre>", print_r($org_options), "</pre>";

        $event_name = $count_of_events . ' ' . $org_options[organization] . __( ' events', 'event_espresso' );

        $event_cost = $_SESSION['event_espresso_grand_total'];
        $multi_reg = true;

        // If there are events in the session, add them one by one to the attendee table
        if ( $count_of_events > 0 )
        {
            //first event key will be used to find the first attendee
            $first_event_id = key( $events_in_session );

            reset( $events_in_session );

            foreach ( $events_in_session as $key => $val ) {

                $event_attendees = $val['event_attendees'];


                foreach ( $event_attendees as $k => $v ) {

                    $session_vars['data'] = $val;
                    $session_vars['event_attendees'] = $v;

                    event_espresso_add_attendees_to_db( $key, $session_vars );
                }
            }



            //Post the gateway page with the payment options

            if ( $event_cost != '0.00' )
            {
                //find first registrant's name, email, count of registrants
                $sql = "SELECT id, fname, lname, email, address, city, state, zip, event_id, registration_id,
                        (SELECT count( id )
                            FROM " . EVENTS_ATTENDEE_TABLE .
                        " WHERE attendee_session = '" . $wpdb->escape( $current_session_id ) . "'
                            ) AS quantity
                            FROM " . EVENTS_ATTENDEE_TABLE
                        . " WHERE event_id = " . $wpdb->escape( $first_event_id )
                        . " AND attendee_session = '" . $wpdb->escape( $current_session_id ) . "' ORDER BY id LIMIT 1";

                $r = $wpdb->get_row( $sql );

                $event_id = $r->event_id;
                $attendee_id = $r->id;
                $fname = $r->fname;
                $lname = $r->lname;
                $address = $r->address;
                $city = $r->city;
                $state = $r->state;
                $zip = $r->zip;
                $attendee_email = $r->email;
                $registration_id = $r->registration_id;
                $quantity = $r->quantity;
?>

                <a href="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=show_shopping_cart">  <?php _e( 'Edit Cart', 'event_espresso' ); ?> </a>
<?php _e( ' or ', 'event_espresso' ); ?>
                <a href="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=load_checkout_page"> <?php _e( 'Edit Registrant Information', 'event_espresso' ); ?></a>


                <h3><?php _e( 'Your registration is not complete until payment is received.', 'event_espresso' ); ?></h3>

                <p><strong class="event_espresso_name">
        <?php _e( 'Amount due: ', 'event_espresso' ); ?>
            </strong> <span class="event_espresso_value"><?php echo $org_options['currency_symbol'] ?><?php echo $event_cost; ?></span></p>
  
        <p><?php echo $org_options['email_before_payment'] == 'Y' ? __( 'A confirmation email has been sent with additional details of your registration.', 'event_espresso' ) : ''; ?></p>

        <h2><?php _e( 'Please choose a payment option:', 'event_espresso' ); ?></h2>

<?

                //Show payment options
                if ( file_exists( EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php" ) )
                {
                    require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
                }
                else
                {
                    require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/gateway_display.php");
                }
                //Check to see if the site owner wants to send an confirmation eamil before payment is recieved.
                if ( $org_options['email_before_payment'] == 'Y' )
                {
                    event_espresso_email_confirmations( array( 'espresso_session_id' => $_SESSION['espresso_session_id'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true', 'multi_reg' => true ) );
                }
            }
            else
            {

                
                ?>

        <p><?php _e('Thank you! Your registration is confirmed for', 'event_espresso'); ?> <strong><?php echo stripslashes_deep($event_name) ?></strong></p>

		<p><?php _e('A confirmation email has been sent with additional details of your registration.', 'event_espresso'); ?></p>

        <?php
                event_espresso_email_confirmations( array( 'espresso_session_id' => $_SESSION['espresso_session_id'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true', 'multi_reg' => true ) );
                
                event_espresso_clear_session();

            }
        }
    }

}

