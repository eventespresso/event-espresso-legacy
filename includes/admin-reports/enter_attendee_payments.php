<?php
function enter_attendee_payments() {

	global $wpdb, $org_options;
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH."includes/functions/attendee_functions.php");

 
	$notifications['success'] = array(); 
	$notifications['error']	 = array(); 
	
	$failed_nonce_msg = '
<div id="message" class="error">
	<p>
		<strong>' . __( 'An Error Occurred. The request failed to pass a security check.', 'event_espresso' ) . '</strong><br/>
		<span style="font-size:.9em;">' . __( 'Please press the back button on your browser to return to the previous page.', 'event_espresso') . '</span>
	</p>
</div>';



	$multi_reg = FALSE;
	$event_id = isset($_POST['event_id']) ? absint( $_POST['event_id'] ) : isset($_REQUEST['event_id']) ? absint( $_REQUEST['event_id'] ) : '';
	$registration_id = isset( $_POST['registration_id'] ) ? sanitize_text_field( $_POST['registration_id'] ) : isset( $_REQUEST['registration_id'] ) ? sanitize_text_field( $_REQUEST['registration_id'] ) : FALSE;
	$registration_ids = array();
	//echo '<h4>$registration_id : ' . $registration_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

	$SQL = "SELECT * FROM " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " WHERE registration_id =%s LIMIT 1";	
	
	if ( $check = $wpdb->get_row( $wpdb->prepare( $SQL, $registration_id ))) {	
		//printr( $check, '$check  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		$registration_id = $check->primary_registration_id;
		$SQL = "SELECT * FROM " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " WHERE primary_registration_id =%s";
		$registration_ids = $wpdb->get_results($wpdb->prepare( $SQL, $registration_id ), ARRAY_A );
		$registration_ids = $registration_ids !== FALSE ? $registration_ids : array();
		$multi_reg = TRUE;
	}
	//echo '<h4>$registration_id : ' . $registration_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
	//printr( $registration_ids, '$registration_ids  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	switch ( $_REQUEST[ 'form_action' ] ) {

		//Add payment info
		case 'payment':

			if ( isset($_POST[ 'attendee_action' ]) && $_POST[ 'attendee_action' ] == 'post_payment' ){

				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'payment_' . $registration_id . '_post_payment_nonce' )) {
					wp_die( $failed_nonce_msg );
				}
				
				$attendees_to_email = array();

				// get the primary attendee id because amount paid info is kept with the primary attendee
				$SQL = "SELECT id, payment_status FROM ".EVENTS_ATTENDEE_TABLE." WHERE registration_id =%s AND is_primary = 1 ORDER BY id LIMIT 0,1 ";
				$primary_att = $wpdb->get_row( $wpdb->prepare( $SQL, $registration_id ));
				if ( ! $primary_att ) {
					$notifications['error'][] = __('An error occured. The primary attendee details could not be retrieved from the database.', 'event_espresso'); 
				} else {

					$txn_type = isset($_POST[ 'txn_type' ]) ? $_POST[ 'txn_type' ] : apply_filters('filter_hook_event_espresso_enter_attendee_payments_remove_require_txn_type', FALSE);
					$txn_id = isset($_POST[ 'txn_id' ]) ? $_POST[ 'txn_id' ] : apply_filters('filter_hook_event_espresso_enter_attendee_payments_remove_require_txn_id', FALSE);
					$payment_date = isset($_POST[ 'payment_date' ]) ? date_i18n( get_option('date_format'), strtotime( $_POST[ 'payment_date' ] )) : FALSE;
					$coupon_code = isset($_POST[ 'coupon_code' ]) ? $_POST[ 'coupon_code' ] : '';
					$total_owing = isset($_POST[ 'total_owing' ]) ? (float)number_format( sanitize_text_field( $_POST[ 'total_owing' ] ), 2, '.', '' ) : 0.00;
					$amount_pd = isset($_POST[ 'amount_pd' ]) ? (float)number_format( sanitize_text_field( $_POST[ 'amount_pd' ] ), 2, '.', '' ) : 0.00;
					$new_payment = isset($_POST[ 'new_payment' ]) && $_POST[ 'new_payment' ] != '' ? (float)number_format( sanitize_text_field( $_POST[ 'new_payment' ] ), 2, '.', '' ) : 0.00;
					$upd_payment_status = isset($_POST[ 'payment_status' ]) ? $_POST[ 'payment_status' ] : 'Pending';
					
					// if making a payment, we are going to require the txn type and txn id
					if ( $new_payment != 0.00  ) {						
						$fail = FALSE;
						if ( ! $txn_type ) {
							$notifications['error'][] = __('You must enter a Transaction Type when making a payment.', 'event_espresso'); 
							$fail = TRUE;
						}
						if ( ! $txn_id ) {
							$notifications['error'][] = __('You must enter a Transaction ID when making a payment.', 'event_espresso'); 
							$fail = TRUE;
						}
						if ( $fail ) {
							break;
						}
					
						$upd_total = $amount_pd + $new_payment;  

					} else {
						$upd_total = $amount_pd;
					}

					// compare new total_cost with amount_pd
					if ( $new_payment != 'Cancelled' ) {
						if ( $new_payment == $total_owing ) {
							$upd_payment_status = 'Completed';
						} elseif ( $new_payment < $total_owing ) {
							$upd_payment_status = isset($_POST[ 'payment_status' ]) && $_POST[ 'payment_status' ] == 'Incomplete' ? 'Incomplete' : 'Pending';
						} elseif ( $new_payment > $total_owing ) {
							$upd_payment_status = 'Refund';
						}
					}
					
					//Update payment status information for primary attendee
					$set_cols_and_values = array( 
							'payment_status' => $upd_payment_status,
							'txn_type' => $txn_type,
							'txn_id' => $txn_id,
							'payment_date' => $payment_date,
							'coupon_code' => $coupon_code,
							'amount_pd' => $upd_total
					);
					$set_format = array( '%s', '%s', '%s', '%s', '%s', '%f' );
					$where_cols_and_values = array( 'id'=> $primary_att->id );
					$where_format = array( '%d' );
					// run the update
					$upd_success = $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format );
					// if there was an actual error
					if ( $upd_success === FALSE ) {
						$notifications['error'][] = __('An error occured. The attendee payment details could not be updated.', 'event_espresso'); 
					} else {
						
						$attendee_data = array('attendee_id'=> $primary_att->id, 'payment_status'=>$upd_payment_status, 'registration_id'=> $registration_id, 'total_cost'=>$upd_total, 'txn_type'=>__('Manual Website Payment', 'event_espresso'), 'txn_id'=>$txn_id);
				
						do_action('action_hook_espresso_update_attendee_payment_status', $attendee_data);
				
						if ( count($registration_ids) > 0 ) {
						
							foreach($registration_ids as $reg_id) {
								// Update payment status information for all attendees
								// remove amount_pd from update data, since that only applies to the primary attendee
								unset( $set_cols_and_values['amount_pd'] );
								$set_format = array( '%s', '%s', '%s', '%s', '%s' );
								$where_cols_and_values = array( 'registration_id'=> $reg_id['registration_id'] );
								$where_format = array( '%s' );
								// run the update
								$upd_success = $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format );
								// if there was an actual error
								if ( $upd_success === FALSE ) {
									$notifications['error'][] = __('An error occured. The payment details for the additional attendees could not be updated.', 'event_espresso'); 
								} 
								
								$attendees_to_email[] = array( 'registration_id'=>$reg_id['registration_id'] );
							}
							
						} else {
						
							// Update payment status information for all attendees
							// remove amount_pd from update data, since that only applies to the primary attendee
							unset( $set_cols_and_values['amount_pd'] );
							$set_format = array( '%s', '%s', '%s', '%s', '%s' );
							$where_cols_and_values = array( 'registration_id'=> $registration_id );
							$where_format = array( '%s' );
							// run the update
							$upd_success = $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format );
							// if there was an actual error
							if ( $upd_success === FALSE ) {
								$notifications['error'][] = __('An error occured. The payment details for the additional attendees could not be updated.', 'event_espresso'); 
							} 

						}

		                //Send Payment Recieved Email
						$send_payment_rec = isset( $_POST[ 'send_payment_rec' ] ) ? $_POST[ 'send_payment_rec' ] : FALSE; 
		                if ( $send_payment_rec == "send_message" ) {
		                    //event_espresso_send_payment_notification( $id );
							if ( count($attendees_to_email) > 0 ) {								
								//printr( $attendees_to_email, '$attendees_to_email  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' ); wp_die();    
								foreach ( $attendees_to_email as $attendee_to_email ){
									$result = event_espresso_send_payment_notification( $attendee_to_email );
									if ( ! empty( $result )) {
										$notifications['error'][] = $result;
									}
								}								
							} else {
								$result = event_espresso_send_payment_notification(array('registration_id'=>$registration_id));
								if ( ! empty( $result )) {
									$notifications['error'][] = $result;
								}
							}
		                }
					
					}

						// let's base our success on the lack of errors
						$notifications['success'][] = empty( $notifications['error'] ) ? __('All attendee payment details have been successfully updated.', 'event_espresso') : __('Some attendee payment details were successfully updated, but the following error(s) may have prevented others from being updated:', 'event_espresso'); 	
				 					
				}

		   }
		 
            break;

        //Send Invoice
        case 'send_invoice':

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'send_invoice_' . $registration_id . '_nonce' )) {
				wp_die( $failed_nonce_msg );
			}

			if ( $org_options["use_attendee_pre_approval"] == "Y" ) {
			
				$pre_approve = $_POST['pre_approve'];
				if ( count($registration_ids) > 0 ) {
					foreach($registration_ids as $reg_id) {
						$SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET pre_approve = %s WHERE registration_id = %s";
						$wpdb->query( $wpdb->prepare( $SQL, $pre_approve, $reg_id['registration_id'] ));
					}
					
				} else {
					$SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET pre_approve = %s WHERE registration_id = %s";
					$wpdb->query( $wpdb->prepare( $SQL, $pre_approve, $registration_id ));
				}

			} else {
				$pre_approve = 0;
			}
			
			if ( $pre_approve == "0" ) {

				if ( count($registration_ids) > 0 ) {

					$reg_attendees = array();

					foreach($registration_ids as $reg_id) {			
					
							$SQL = 'SELECT * FROM ' . EVENTS_ATTENDEE_TABLE . ' WHERE registration_id =%s';
							$more_reg_attendees = $wpdb->get_results( $wpdb->prepare( $SQL, $reg_id['registration_id'] ), 'OBJECT_K' );

							foreach ( $more_reg_attendees as $another_reg_attendee ) {
								$reg_attendees[ $another_reg_attendee->email ] = $another_reg_attendee;			
							}
							
						}
						
						foreach ( $reg_attendees as $reg_attendee ){
							event_espresso_send_invoice( $reg_attendee->registration_id, $_POST[ 'invoice_subject' ], $_POST[ 'invoice_message' ] );
						}

					
				} else {
					event_espresso_send_invoice( $registration_id , $_POST[ 'invoice_subject' ], $_POST[ 'invoice_message' ] );
				}
				
				$notifications['success'][] = __('Invoice Sent.', 'event_espresso'); 	
			}
			
            break;
    }
 

	$SQL = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id ='%s' ORDER BY id LIMIT 1";
    $attendee = $wpdb->get_row($wpdb->prepare( $SQL, $registration_id ));
 	
	if ( $attendee === FALSE ) {	
		echo '<div id="message" class="error"><p><strong>'.__('An error occured. The requested attendee data could not be found.', 'event_espresso').'</strong></p></div>';
		exit();
	}
	
	$id = $attendee->id;
	$lname = $attendee->lname;
	$fname = $attendee->fname;
	$address = $attendee->address;
	$city = $attendee->city;
	$state = $attendee->state;
	$zip = $attendee->zip;
	$email = $attendee->email;
	$phone = $attendee->phone;
	$date = $attendee->date;
	$payment_status = $attendee->payment_status;
	$txn_type = $attendee->txn_type;
	$txn_id = $attendee->txn_id;
	$quantity = $attendee->quantity;
	$payment_date = $attendee->payment_date;
	$event_id = $attendee->event_id;
	$coupon_code = $attendee->coupon_code;
	$pre_approve = $attendee->pre_approve;
	$start_date = $attendee->start_date;
	$event_time = $attendee->event_time;
	$amount_pd = $attendee->amount_pd;
	$total_cost = $attendee->total_cost;
	$orig_price = $attendee->orig_price;
	$final_price = $attendee->final_price;


	$SQL = "SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='%d'";
     $event = $wpdb->get_row( $wpdb->prepare( $SQL,$event_id ));

	if ( $event === FALSE ) {	
		echo '<div id="message" class="error"><p><strong>'.__('An error occured. The event data for this registration could not be found.', 'event_espresso').'</strong></p></div>';
		exit();
	}

    $event_id = $event->id;
    $event_name = $event->event_name;
    $event_desc = $event->event_desc;
    $event_description = $event->event_desc;
    $event_identifier = $event->event_identifier;
    $cost = isset($event->event_cost) ? $event->event_cost:0;
    $active = $event->is_active;

	$event_date = event_date_display($start_date .' '.$event_time, get_option('date_format') . ' g:i a');
//	$total_paid = espresso_attendee_price(array('registration_id'=>$_REQUEST['registration_id'], 'session_total'=>true));


	// display success messages
	if ( ! empty( $notifications['success'] )) { 
		$success_msg = implode( $notifications['success'], '<br />' );
	?>
			
<div id="message" class="updated fade">
<p>
	<strong><?php echo $success_msg; ?></strong>
</p>
</div>

	<?php
	 } 
	// display error messages
	if ( ! empty( $notifications['error'] )) {
		$error_msg = implode( $notifications['error'], '<br />' );
	?>
			
<div id="message" class="error">
<p>
	<strong><?php echo $error_msg; ?></strong>
</p>
</div>

	<?php } ?>

<div>
	<p>				
		<a href="admin.php?page=events&event_id=<?php echo $event_id; ?>&event_admin_reports=list_attendee_payments">
			 <strong><span class="laquo big-text">&laquo;&nbsp;</span><?php _e('Back to Attendees List', 'event_espresso'); ?></strong>
		</a>				
	</p>
</div>		
		
<div class="metabox-holder">
	<div class="postbox">
		<?php 
			// create attendee list link
			$list_att_url_params = array( 
				'event_admin_reports' => 'list_attendee_payments',
				'event_id' => $event_id
			);
			// add url params
			$list_attendee_link = add_query_arg( $list_att_url_params, 'admin.php?page=events' );
		?>
		<?php if ( !$multi_reg ) { ?>
		<h3>
			<?php _e( 'Name:', 'event_espresso' ); ?>
			<b><?php echo $fname ?> <?php echo $lname ?></b>&nbsp;&nbsp;|&nbsp;&nbsp;
			<?php _e( 'ID:', 'event_espresso' ); ?>
			<?php echo $id ?>&nbsp;&nbsp;|&nbsp;&nbsp;
			<?php _e( 'Registered For:', 'event_espresso' ); ?>
			<a href="<?php echo $list_attendee_link ?>"><?php echo stripslashes_deep($event_name) ?></a> - <?php echo $event_date; ?>
		</h3>
			
		<?php } else { ?>
		
		<h3>
			<?php echo __('Multiple Registration Payment for ', 'event_espresso'); ?> <a href="<?php echo $list_attendee_link ?>"><?php echo stripslashes_deep($event_name) ?></a> - <?php echo $event_date; ?>
		</h3>
		
		<?php } ?>
		
		<div class="inside">
			<table width="100%" border="0">
				<tr>
					<td>
						<h4 class="qrtr-margin"><strong><?php _e( 'Payment Details', 'event_espresso' ); ?></strong></h4>
					</td>
					<td>
						<h4 class="qrtr-margin"><strong><?php _e( 'Invoice/Payment Reminder', 'event_espresso' ); ?></strong></h4>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<?php 
							// create edit attendee link
							$edit_att_url_params = array( 
								'event_admin_reports' => 'edit_attendee_record',
								'form_action' => 'edit_attendee',
								'registration_id' => $registration_id,
								'event_id' => $event_id
							);
							// add url params
							$edit_attendee_link = add_query_arg( $edit_att_url_params, 'admin.php?page=events' );
						?>						
						<?php if ( count($registration_ids) > 0 ) { ?>
				
						<p>
							<strong><?php _e('Registration Ids:', 'event_espresso');?></strong>
						</p>
						<ul>
							<?php foreach( $registration_ids as $reg_id ) { ?>					
		                		<li>
								#&nbsp;<?php echo $reg_id['registration_id']; ?>&nbsp;&nbsp;
								<a href="<?php echo $edit_attendee_link; ?>"><?php _e('View / Edit Registration', 'event_espresso');?></a>
							</li>				
							<?php } ?>
						</ul>
						
						<?php } else { ?>
				
						<p>
							<strong><?php _e('Registration Id:', 'event_espresso');?></strong>
						</p>
						<p>
							#&nbsp;<?php echo $registration_id;?>&nbsp;&nbsp;<a href="<?php echo $edit_attendee_link; ?>"><?php _e('View/Edit Registration', 'event_espresso');?></a>
						</p>
						
						<?php } ?>
				
						<hr style="width:90%; margin:20px 0;" align="left" />

						<form method="POST" action="<?php echo $_SERVER[ 'REQUEST_URI' ] ?>" class="espresso_form">
							<fieldset>
								<ul>
									<li>
										<label>
											<?php _e( 'Payment Date:', 'event_espresso' ); ?>
										</label>
										<input type="text" class="medium-text" name="payment_date" size="45" value ="<?php echo !empty($payment_date) ? event_date_display($payment_date): event_date_display( date( "d-m-Y" )) ?>" />
									</li>
									<li>
										<label for="payment_status">
											<?php _e( 'Payment Status:', 'event_espresso' ); ?>
										</label>
										<?php
												$values=array(
													array('id'=>'','text'=> __('- please select -','event_espresso')),
													array('id'=>'Completed','text'=> __('Completed','event_espresso')),
													array('id'=>'Pending','text'=> __('Pending','event_espresso')),
													array('id'=>'Payment Declined','text'=> __('Payment Declined','event_espresso')),
													array('id'=>'Cancelled','text'=> __('Cancelled','event_espresso')),
													array('id'=>'Incomplete','text'=> __('Incomplete','event_espresso')),
													array('id'=>'Refund','text'=> __('Overpaid','event_espresso'))
												);
												echo select_input('payment_status', $values, $payment_status);
										
										?>
									</li>
									<li>
										<label>
											<?php _e( 'Total Amount Owing:', 'event_espresso' ); ?> ( <?php echo $org_options[ 'currency_symbol' ]; ?> )
										</label>
										<?php 
											$amount_owing = number_format( $total_cost - $amount_pd, 2, '.', '' );

											if ( $amount_owing == 0.00 ) {
												$amnt_class = ' full-payment';
											} elseif ( $amount_owing < $total_cost || $amount_owing > $total_cost ) {
												$amnt_class = ' part-payment';
											} elseif ( $amount_owing == $total_cost ) {
												$amnt_class = ' no-payment';
											} 
										?>									
										<input class="small-text algn-rght<?php echo $amnt_class;?>" type="text" name="ttl_ow" disabled="true" value ="<?php echo $amount_owing; ?>" />
										<input type="hidden" name="total_owing" value ="<?php echo $amount_owing; ?>" />&nbsp;&nbsp;
										<a href="admin.php?page=events&event_admin_reports=edit_attendee_record&event_id=<?php echo $event_id;?>&registration_id=<?php echo $registration_id;?>&form_action=edit_attendee&show_payment=true">
											<?php  _e('Edit Ticket Price(s)', 'event_espresso');?>
										</a>
									</li>
									<li>
										<label>
											<?php _e( 'Total Amount Paid to Date:', 'event_espresso' ); ?> ( <?php echo $org_options[ 'currency_symbol' ]; ?> )
										</label>										
										<input class="small-text algn-rght<?php echo $amnt_class;?>" type="text" name="amnt_pd" disabled="true" value ="<?php echo $amount_pd; ?>" />
										<input type="hidden" name="amount_pd" value ="<?php echo $amount_pd; ?>" />
									</li>
									<li>
										<label>
											<?php _e( 'Enter New Payment Amount:', 'event_espresso' ); ?> ( <?php echo $org_options[ 'currency_symbol' ]; ?> )
										</label>										
										<input class="small-text algn-rght" type="text" name="new_payment" value ="" />
									</li>
									<li>
										<label>
											<?php _e( 'Coupon Code:', 'event_espresso' ); ?>
										</label>
										<input type="text" class="medium-text" name="coupon_code" size="45" value ="<?php echo $coupon_code; ?>" />
									</li>
									<li>
										<label for="txn_type">
											<?php _e( 'Transaction Type:', 'event_espresso' ); ?>
										</label>
										<?php
//												$txn_values=array(
//													array('id' => '', 'text' => __('N/A', 'event_espresso')),
//													array('id' => 'web_accept', 'text' => espresso_payment_type('web_accept')),
//													array('id' => 'CC', 'text' => __('Credit Card', 'event_espresso')),
//													array('id' => 'INV', 'text' => espresso_payment_type('INV')),
//													array('id' => 'OFFLINE', 'text' => espresso_payment_type('OFFLINE')),
//												);
//												echo select_input('txn_type', $txn_values, $txn_type);
										?>
											<input type="text" class="medium-text" name="txn_type" size="45" value ="<?php echo stripslashes_deep(htmlentities($txn_type)); ?>" />
										</li>
									<li>
										<label>
											<?php _e( 'Transaction ID: ', 'event_espresso' );?> <span class="smaller-text"><?php _e( '( or cheque #, gateway response, etc )', 'event_espresso' ); ?></span>
										</label>
										<input type="text" name="txn_id" size="45" value ="<?php echo $txn_id; ?>" />
									</li>
									<li>
										<label>
											<h4><?php _e( 'Email Notice', 'event_espresso' ); ?></h4>
											<?php _e( 'Do you want to send a payment received notice to registrant?', 'event_espresso' ); ?>
										</label>
										<label class="radio-btn-lbl">
											<input type="radio" name="send_payment_rec" value="send_message">
											<span class="big-text"><?php _e( 'Yes', 'event_espresso' ); ?></span>
										</label>
										<label class="radio-btn-lbl">
											<input type="radio" name="send_payment_rec" checked value="N">
											<span class="big-text"><?php _e( 'No', 'event_espresso' ); ?></span>
										</label>
									</li>
									<li>
										<p><br/><input type="submit" name="Submit" class="button-primary action"   value="Update Payment"></p>
									</li>
								</ul>
							</fieldset>
							<input type="hidden" name="id" value="<?php echo $id ?>">
							<input type="hidden" name="registration_id" value="<?php echo $registration_id ?>">
							<input type="hidden" name="form_action" value="payment">
							<input type="hidden" name="event_id" value="<?php echo $event_id ?>">
							<input type="hidden" name="attendee_action" value="post_payment">
							<?php wp_nonce_field( 'payment_' . $registration_id . '_post_payment_nonce' ); ?>
						</form>
					</td>
					<td valign="top">
						<form class="espresso_form" method='post' action="<?php echo $_SERVER[ 'REQUEST_URI' ] ?>">
							<input type="hidden" name="id" value="<?php echo $id ?>">
							<input type="hidden" name="form_action" value="send_invoice">
							<input type="hidden" name="event_id" value="<?php echo $event_id ?>">
							<input type="hidden" name="registration_id" value="<?php echo $registration_id ?>">
							<?php wp_nonce_field( 'send_invoice_' . $registration_id . '_nonce' ); ?>
							<ul>
								<li>
									<?php _e('Use a ', 'event_espresso'); ?>
									<a href="admin.php?page=event_emails" target="_blank">
									<?php _e('pre-existing email', 'event_espresso'); ?>
									</a>? <?php echo espresso_db_dropdown('id', 'email_name', EVENTS_EMAIL_TABLE, 'email_name', '', 'desc') . ' <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=email_manager_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a>'; ?> </li>
								<li>
									<?php _e('OR', 'event_espresso'); ?>
								</li>
								<li>
									<?php _e('Create a custom email:', 'event_espresso'); ?>
								</li>
								<li>
									<?php _e( 'Invoice Subject', 'event_espresso' ); ?>
									:
									<input type="text" name="invoice_subject" size="45" value="<?php _e( 'Payment Reminder for [event]', 'event_espresso' ); ?>" />
								</li>
								<li>
									<p>
										<?php _e( 'Message:', 'event_espresso' ); ?>
									</p>
									<div class="postbox">
										<?php
											$email_content = __( 'Dear [fname] [lname], <p>Our records show that we have not received your payment of [cost] for [event_link].</p> <p>Please visit [payment_url] to view your payment options.</p><p>[invoice_link]</p><p>Sincerely,<br />' . $Organization = $org_options[ 'organization' ] . '</p>', 'event_espresso' );
											if (function_exists('wp_editor')){
												$args = array("textarea_rows" => 8, "textarea_name" => "invoice_message", "editor_class" => "my_editor_custom");
												wp_editor(espresso_admin_format_content($email_content), "invoice_message", $args);
											}else{
												echo  '<textarea name="invoice_message" class="theEditor" id="invoice_message">'.espresso_admin_format_content($email_content).'</textarea>';
												espresso_tiny_mce();
											}
										?>
										<table id="email-confirmation-form" cellspacing="0">
											<tbody>
												<tr>
													<td class="aer-word-count"></td>
													<td class="autosave-info"><span>&nbsp;</span></td>
												</tr>
											</tbody>
										</table>
										<p><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_info">
											<?php _e('View Custom Email Tags', 'event_espresso'); ?>
											</a> | <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_example">
											<?php _e('Email Example', 'event_espresso'); ?>
											</a></p>
									</div>
								</li>
								<?php
		if ( $org_options["use_attendee_pre_approval"] == "Y" ) {
			$pre_approve = is_attendee_approved($event_id,$id)==true?1:0;
		?>
								<li>
									<?php _e("Attendee approved?","event_espresso"); ?>
									:
									<?php
			$pre_approval_values=array(array('id'=>'0','text'=> __('Yes','event_espresso')), array('id'=>'1','text'=> __('No','event_espresso')));
			echo select_input("pre_approve",$pre_approval_values,$pre_approve);
			?>
									<br />
									<?php _e("(If not approved then invoice will not be sent.)","event_espresso"); ?>
								</li>
								<?php } ?>
								<li>
									<br/>
									<input type="submit" class="button-primary action"   name="Submit" value="Send Invoice">
								</li>
							</ul>
						</form>
					</td>
				</tr>
			</table>

		</div>
	</div>
</div>
<?php
	//This show what tags can be added to a custom email.
	event_espresso_custom_email_info();
	//event_list_attendees();
}
