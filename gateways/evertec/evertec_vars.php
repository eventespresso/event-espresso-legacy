<?php

function espresso_display_evertec($payment_data) {	
	global $org_options;
	$registration_id = $payment_data['registration_id'];
	$evertec_settings = get_option('event_espresso_evertec_settings');
	$use_sandbox = $evertec_settings['use_sandbox'];
	wp_register_script( 'evertec', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/evertec/evertec.js', array( 'jquery', 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'evertec' );		
	?>
<div id="evertec-payment-option-dv" class="payment-option-dv">

	<a id="evertec-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="evertec-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="evertec-payment-option-form-dv" class="hide-if-js">	
		<div class="event-display-boxes">
			<?php
			if ($use_sandbox) {
				echo '<div id="sandbox-panel"><h2 class="section-title">' . __('Evertec Sandbox Mode', 'event_espreso') . '</h2><p>Test Master Card # 5310509031377124</p>';
				echo '<p>Exp: 12/2015</p>';
				echo '<p>CVV2: 996 </p>';
				echo '</h2><p>Test Visa Card # 4548400000000631</p>';
				echo '<p>Exp: 12/2019</p>';
				echo '<p>CVV2: 781 </p>';
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3></div>';
			}
			if ($evertec_settings['force_ssl_return']) {
				$home = str_replace('http://', 'https://', home_url());
			} else {
				$home = home_url();
			}
			if ($evertec_settings['display_header']) {
?>
			<h3 class="payment_header"><?php echo $evertec_settings['header']; ?></h3><?php } ?>

			<div class = "event_espresso_form_wrapper">
				<form id="evertec_payment_form" name="evertec_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">
					
					<fieldset id="evertec-billing-info-dv">
						<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
						<p>
							<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
				        	<input name="first_name" type="text" id="evertec_first_name" class="required" value="<?php echo isset($_REQUEST['fname']) ? $_REQUEST['fname'] : $payment_data['fname'] ?>" />
						</p>
						<p>
					        <label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
					        <input name="last_name" type="text" id="evertec_last_name" class="required" value="<?php echo isset($_REQUEST['lname']) ? $_REQUEST['lname'] : $payment_data['lname'] ?>" />
						</p>
						<p>
					        <label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
					        <input name="email" type="text" id="evertec_email" class="required" value="<?php echo isset($_REQUEST['attendee_email']) ? $_REQUEST['attendee_email'] : $payment_data['attendee_email'] ?>" />
						</p>
						<p>
					        <label for="address"><?php _e('Address', 'event_espresso'); ?></label>
					        <input name="address" type="text" id="evertec_address" class="required" value="<?php echo isset($_REQUEST['address']) ? $_REQUEST['address'] : $payment_data['address'] ?>" />
						</p>
						<p>
					        <label for="address2"><?php _e("Address (cont'd)", 'event_espresso'); ?></label>
					        <input name="address2" type="text" id="evertec_address2" value="<?php echo isset($payment_data['address2']) ? $payment_data['address2'] : '' ?>" />
						</p>
						<p>
					        <label for="city"><?php _e('City', 'event_espresso'); ?></label>
					        <input name="city" type="text" id="evertec_city" class="required" value="<?php echo isset($_REQUEST['city']) ? $_REQUEST['city'] : $payment_data['city'] ?>" />
						</p>
						<p>
					        <label for="state"><?php _e('State', 'event_espresso'); ?></label>
					        <input name="state" type="text" id="evertec_state" class="required" value="<?php echo isset($_REQUEST['state']) ? $_REQUEST['state'] : $payment_data['state'] ?>" />
						</p>
						<p>
					        <label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
					        <input name="zip" type="text" id="evertec_zip" class="required" value="<?php echo isset($_REQUEST['zip']) ? $_REQUEST['zip'] : $payment_data['zip'] ?>" />
						</p>
						<p>
					        <label for="phone"><?php _e('Phone', 'event_espresso'); ?></label>
					        <input name="phone" type="text" id="evertec_phone" class="required" value="<?php echo isset($_REQUEST['phone']) ? $_REQUEST['phone'] : $payment_data['phone'] ?>" />
						</p>
					</fieldset>
					<select id="evertec_payment_method" name="evertec_payment_method">
						<option id='none' value='none'><?php _e("Please select one...", "event_espresso");?></option>
						<?php foreach($evertec_settings['accepted_payment_methods'] as $name => $i18n_name){
							echo "<option id='$name' value='$name'>$i18n_name</option>";
						}?>
					</select>
					<fieldset id="evertec-credit-card-info-dv" style='display:none'>
						<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
						<p>
					        <label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
					        <input type="text" name="card_num" class="required" id="evertec_card_num" autocomplete="off" />
						</p>
						<p>
					        <label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
					        <select id="evertec_card-exp" name ="expmonth" class="med required">
										<?php
										for ($i = 1; $i < 13; $i++){
											$padded_month = str_pad($i,2,"0",STR_PAD_LEFT);
											echo "<option value='$padded_month'>$padded_month</option>";
										}
										?>
					        </select>
						</p>
						<p>
					        <label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
					        <select id="evertec_exp-year" name ="expyear" class="med required">
										<?php
										$curr_year = date("Y");
										for ($i = 0; $i < 10; $i++) {
											$disp_year = intval($curr_year) + $i;
											echo "<option value='$disp_year'>$disp_year</option>";
										}
										?>
					        </select>
						</p>
						<p>
					        <label for="cvv"><?php _e('CVV Code', 'event_espresso'); ?></label>
					        <input type="text" name="cvv" id="evertec_exp_date" autocomplete="off"  class="small required"/>
						</p>
					</fieldset>
					<fieldset id="evertec-bank-info-dv" style='display:none'>
						<h4 class="section-title"><?php _e('Bank Account Information', 'event_espresso'); ?></h4>
						<p>
					        <label for="bankRoutingNumber"><?php _e('Routing Number', 'event_espresso'); ?></label>
					        <input type="text" name="bankRoutingNumber" class="required" id="bankRoutingNumber" autocomplete="off" />
						</p>
						<p>
					        <label for="bankAccountNumber"><?php _e('Account Number', 'event_espresso'); ?></label>
					        <input type="text" name="bankAccountNumber" class="required" id="bankAccountNumber" autocomplete="off" />
						</p>
						<p>
					        <label for="bankClientName"><?php _e('Account Number', 'event_espresso'); ?></label>
					        <input type="text" name="bankClientName" class="required" id="bankClientName" autocomplete="off" />
						</p>
						<p>
							<label for='authorizationBit'><?php	_e("I authorize these funds be withdrawn from my account", "event_espresso");?></label>
							<input type="checkbox" name="authorizationBit" id="authorizationBit" value="1"/>
						</p>
					</fieldset>
					
					<input name="amount" type="hidden" value="<?php echo number_format($payment_data['event_cost'], 2) ?>" />
					<input name="evertec" type="hidden" value="true" />
					<input name="r_id" type="hidden" value="<?php echo $registration_id ?>" />
					<p class="event_form_submit" style="display:none">
						<input name="evertec_submit" id="evertec_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />					
						<div class="clear" id="processing" style="display:none"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/ajax-loader.gif"></div>
					</p>
					
				</form>

			</div><!-- / .event_espresso_or_wrapper -->
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="evertec-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>		<?php
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_evertec');