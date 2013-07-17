<?php

function espresso_display_qbms($payment_data) {
	extract($payment_data);
	global $org_options;
	
	$qbms_settings = get_option('event_espresso_qbms_settings');
	if (isset($_SERVER['HTTPS'])) {
		$home = str_replace('http://', 'https://', home_url());
	} else {
		$home = home_url();
	}
	
	wp_register_script( 'qbms', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/qbms/qbms.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'qbms' );	
	?>

	<div id="qbms-payment-option-dv" class="payment-option-dv">

		<a id="qbms-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="qbms-payment-option-form" style="cursor:pointer;">
			<img alt="Pay using a Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
		</a>	

		<div id="qbms-payment-option-form-dv" class="hide-if-js">

			<?php
			if ($qbms_settings['display_header']) { ?>			
			<h3 class="payment_header"><?php echo $qbms_settings['header']; ?></h3>
			<?php } ?>

			<div class = "event_espresso_form_wrapper">
				<form id="qbms_payment_form" name="qbms_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">

					<fieldset id="qbms-billing-info-dv">
						<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
						<p>
							<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
							<input name="qbms_first_name" type="text" id="qbms_first_name" class="required" value="<?php echo $fname ?>" />
						</p>
						<p>
							<label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
							<input name="qbms_last_name" type="text" id="qbms_last_name" class="required" value="<?php echo $lname ?>" />
						</p>
						<p>
							<label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
							<input name="email" type="text" id="qbms_email" class="required" value="<?php echo $attendee_email ?>" />
						</p>
						<p>
							<label for="address"><?php _e('Address', 'event_espresso'); ?></label>
							<input name="qbms_address" type="text" id="qbms_address" class="required" value="<?php echo $address ?>" />
						</p>
						<p>
							<label for="city"><?php _e('City', 'event_espresso'); ?></label>
							<input name="qbms_city" type="text" id="qbms_city" class="required" value="<?php echo $city ?>" />
						</p>
						<p>
							<label for="state"><?php _e('State', 'event_espresso'); ?></label>
							<input name="qbms_state" type="text" id="qbms_state" class="required" value="<?php echo $state ?>" />
						</p>
						<p>
							<label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
							<input name="qbms_zip" type="text" id="qbms_zip" class="required" value="<?php echo $zip ?>" />
						</p>
					</fieldset>

					<fieldset id="qbms-credit-card-info-dv">
						<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
						<p>
							<label>Card Number <span class="required">*</span></label>
							<input class="required input-text" type="text" size="16" maxlength="16" name="qbms_creditcard" />
						</p>
						<p>
							<label>Expiration Month <span class="required">*</span></label>
							<select name="qbms_expdatemonth" class="med required">
								<option value=01> 1 - January</option>
								<option value=02> 2 - February</option>
								<option value=03> 3 - March</option>
								<option value=04> 4 - April</option>
								<option value=05> 5 - May</option>
								<option value=06> 6 - June</option>
								<option value=07> 7 - July</option>
								<option value=08> 8 - August</option>
								<option value=09> 9 - September</option>
								<option value=10>10 - October</option>
								<option value=11>11 - November</option>
								<option value=12>12 - December</option>
							</select>
						</p>
						<p>
							<label>Expiration Year  <span class="required">*</span></label>
							<select name="qbms_expdateyear" class="med required">
								<?php
								$today = (int)date('y', time());
								$today1 = (int)date('Y', time());
								for($i = 0; $i < 8; $i++)
								{
									?>
									<option value="<?php echo $today; ?>"><?php echo $today1; ?></option>
									<?php
									$today++;
									$today1++;
								}
								?>
							</select>
						</p>
						<p>
							<label>Card CVV <span class="required">*</span></label>
							<input class="small required" type="text" size="5" maxlength="5" name="qbms_cvv" />
						</p>
						<div class="clear"></div>
					</fieldset>
					<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
					<input name="qbms" type="hidden" value="true" />
					<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />
					<p class="event_form_submit">
						<input name="qbms_submit" id="qbms_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />						
						<div class="clear"></div>
					</p>
				</form>
			</div>
			<br/>
			<p class="choose-diff-pay-option-pg">
				<a class="hide-the-displayed" rel="qbms-payment-option-form" 

				style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
			</p>

		</div>
	</div>
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_qbms');

