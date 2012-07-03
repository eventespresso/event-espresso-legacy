<?php 
/* WARNING MODIFY THIS AT YOUR OWN RISK  */
/* Return to Payments template page. Currently this just shows the return to paayment information data block.*/
?>
<p class="payment_details thank_you"><?php  _e('Thank You ','event_espresso'); 
                     echo $fname.' '.$lname;  
                     _e(' for registering for ','event_espresso');
                     echo $event_name;?>
                     </p>
<?php
			/*if ($payment_status == "Completed"){echo "<p><font color='red' size='3'>".__('Our records indicate you have paid','event_espresso')." ".$currency_symbol.$event_cost."</font></p>";}*/
			//Alessandro
			if ($payment_status == "Completed"){echo '<p class="payment_details payment_paid">'.__('Our records indicate you have paid','event_espresso')." ".$org_options['currency_symbol'].$event_cost."</p>";}
			
			if ($payment_status == "Pending"){echo '<p class="payment_details payment_pending">'.__('Our records indicate your payment is pending.','event_espresso')."<br />".__('Amount pending:','event_espresso')." ". $org_options['currency_symbol'].$event_cost."</p>";}

			if ($payment_status == "Incomplete" || $payment_status == "" ){
?>	
				
					<p class="payment_details payment_amount">
					  <?php _e('Payment will be in the amount of','event_espresso'); ?>
					  <?php echo  $org_options['currency_symbol'].$event_cost;?>.</p>
                      
                      <p class="payment_details payment_options">
					  <?php _e('Payment Options','event_espresso'); ?>
					 </p>
                     
<?php                      
				if($event_cost == '0.00' || !file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php") ){
					if($event_cost == '0.00'){
						_e('<strong id="event_espresso_no_payment">No Payment Necessary</strong>','event_espresso');
					}
					if($event_cost != '0.00' && !file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php")){
						echo '<h3 style="color:red;">' . __('No payment gateways installed. Please install at least one payment gateway.','event_espresso') . '</h3>';
					}
				}else{
					//Show payment options
					if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php")){
						echo '<table id="payment_butons" width="95%">';
						echo '<tr>';
						if (get_option('events_paypal_active') == 'true'){
							echo '<td>';
							require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/paypal_vars.php"); //Load PayPal vars
							echo '</td>';
						}
						if (get_option('events_authnet_active') == 'true'){
							echo '<td>';
							require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/authnet_vars.php"); //Load Authorize.net vars
							echo '</td>';
						}
						if (get_option('events_twoco_active') == 'true'){
							echo '<td>';
							require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/twoco_vars.php"); //Load Authorize.net vars
							echo '</td>';
						}
						echo '</tr>';
						echo '</table>';
						
						if (get_option('events_invoice_payment_active') == 'true'){
							require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/invoice_vars.php"); //Load PayPal vars
						}
						
						if (get_option('events_check_payment_active') == 'true'){
							require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/check/check_payment_vars.php"); //Load PayPal vars
						}
						
						if (get_option('events_bank_payment_active') == 'true'){
							require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/bank_payment_vars.php"); //Load PayPal vars
						}

					}else{
						_e('<h3>No payment gateways installed. Please install at least one payment gateway.</h3>','event_espresso');
					}
				}

			
		}//End if ($payment_status == ("Incomplete") )
		