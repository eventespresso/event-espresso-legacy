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
					  <?php _e('Payment Options:','event_espresso'); ?>
					 </p>
<?php
			if($event_cost != '0.00'){
			//Show payment options
				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php")){
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
				}else{
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH. "gateways/gateway_display.php");
				}
			}			
		}//End if ($payment_status == ("Incomplete") )