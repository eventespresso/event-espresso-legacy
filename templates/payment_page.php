<?php
/* WARNING MODIFYING THIS AT YOUR OWN RISK  */
/* Payments template page. Currently this just shows the registration data block.*/

//Payment confirmation block
if ($event_cost != "0.00"  || $event_cost != "" ){
?>
            <p align="left"><strong>
              <?php _e('Please verify your registration details:','event_espresso'); ?>
              </strong></p>
            <table width="95%" border="0" id="event_espresso_attendee_verify">
              <tr>
                <td><strong class="event_espresso_name">
                  <?php _e('Event Name:','event_espresso'); ?>
                  </strong></td>
                <td><span class="event_espresso_value"><?php echo $event_name?></span></td>
              </tr>
               <tr>
                <td><strong class="event_espresso_name">
                  <?php echo $price_type == '' ? __('Price:','event_espresso') : __('Type/Price:','event_espresso'); ?>
                  </strong></td>
                <td><span class="event_espresso_value"><?php echo $price_type == '' ? $display_cost : $price_type . ' / ' .$display_cost; ?></span></td>
              </tr>
              <tr>
                <td><strong class="event_espresso_name">
                  <?php _e('Attendee Name:','event_espresso'); ?>
                  </strong></td>
                <td  valign="top"><span class="event_espresso_value"><?php echo $attendee_name?> (<?php echo $attendee_email?>)
				<?php
					if ( (isset($_REQUEST['x_attendee_fname'])) && (count($_REQUEST['x_attendee_fname'])>0) ) {
                		foreach ($_REQUEST['x_attendee_fname'] as $k => $v){
							if ($v != ''){
                				echo "<br/>" . $v . " " . $_REQUEST['x_attendee_lname'][$k] . "(" . $_REQUEST['x_attendee_email'][$k] . ")";
							}
                		}
                	}
				?>
				</span></td>
              </tr>
              <?php if ($num_people > 1){?>
              <tr>
                <td><strong class="event_espresso_name">
                  <?php _e('Total Registrants:','event_espresso'); ?>
                  </strong></td>
                <td><span class="event_espresso_value"><?php echo $num_people; ?> X <?php echo $org_options['currency_symbol']?><?php echo $event_price; ?> = <?php echo $org_options['currency_symbol']?><?php echo $event_price_x_attendees; ?></span></td>
              </tr>
              <?php		}?>
            </table>
<?php
			//This area displays the payment buttons. Modify at your own risk.
			if($event_cost == '0.00' || !file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php") ){
				if($event_cost == '0.00'){
					_e('<strong id="event_espresso_no_payment">No Payment Necessary</strong>','event_espresso');
				}
				if($event_cost != '0.00' && !file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php")){
					echo '<h3 style="color:red;">' . __('No payment gateways installed. Please install at least one payment gateway.','event_espresso') . '</h3>';
				}
			}else{
				//Show payment options
				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php")){
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
				}else{
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH. "gateways/gateway_display.php");
					//echo '<h3 style="color:red;">' . __('Please move your gateway_display.php file to the ' . EVENT_ESPRESSO_GATEWAY_DIR . ' folder.','event_espresso') . '</h3>';
				}
			}
}