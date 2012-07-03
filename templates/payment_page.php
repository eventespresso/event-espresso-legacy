<?php
/* WARNING MODIFYING THIS AT YOUR OWN RISK  */
/* Payments template page. Currently this just shows the registration data block.*/

//Payment confirmation block
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
              <tr valign="top">
                <td><strong class="event_espresso_name">
                  <?php _e('Total Registrants:','event_espresso'); ?>
                  </strong></td>
                <td><span class="event_espresso_value"><?php echo $num_people; ?> X <?php echo $org_options['currency_symbol']?><?php echo $event_price; ?> = <?php echo $org_options['currency_symbol']?><?php echo $event_price_x_attendees; ?></span></td>
              </tr>
              
              <?php		}?>
              
              <tr valign="top">
              <td colspan="2"><p><strong class="event_espresso_name"><?php _e('Additional Information:', 'event_espresso'); ?></strong></p>
              <?php echo $display_questions ?></td>
              </tr>
            </table>
<?php
			//This area displays the payment buttons. Modify at your own risk.
			if($event_cost == '0.00' || !file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php") ){
				if($event_cost == '0.00'){
					//echo '<p>' . __('This is a free event. Details have been sent to your email.','event_espresso').'</p>';
					?>
					
					<form id="form1" name="form1" method="post" action="<?php echo get_option('siteurl')?>/?page_id=<?php echo $event_page_id?>">
                      <h2><?php _e('Confirm Registration', 'event_espresso'); ?> <input type="submit" name="confirm" id="confirm" value="Submit" /></h2>                      
                      <input name="confirm_registration" id="confirm_registration" type="hidden" value="true" />
                        <input type="hidden" name="registration_id" id="registration_id" value="<?php echo $registration_id ?>" />
                        <input type="hidden" name="regevent_action" id="regevent_action-<?php echo $event_id;?>" value="post_attendee">
                            <input type="hidden" name="event_id" id="event_id-<?php echo $event_id;?>" value="<?php echo $event_id;?>">
           			 </form>
				<?php
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
