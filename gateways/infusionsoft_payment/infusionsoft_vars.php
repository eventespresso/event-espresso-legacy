<?php

function espresso_display_infusionsoft($data) {
	extract($data);
	global $org_options, $ee_is_options;
	
	if ($ee_is_options['force_ssl_return']) {
		$home = str_replace('http://', 'https://', home_url());
	} else {
		$home = home_url();
	}
		
		
	?>
	<div id="infusionsoft-payment-option-dv" class="payment-option-dv">

	<a id="infusionsoft-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="infusionsoft-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using a Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="infusionsoft-payment-option-form-dv" class="hide-if-js">

		<form id="infusionsoft_payment_form" name="infusionsoft_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">
			<div class = "event_espresso_form_wrapper">

				<fieldset id="infusionsoft-billing-info-dv">
					<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
					<p>
						<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
						<input name="first_name" type="text" id="infusionsoft_first_name" value="<?php echo $fname ?>" />
					</p>
					<p>
						<label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
						<input name="last_name" type="text" id="infusionsoft_last_name" value="<?php echo $lname ?>" />
					</p>
					<p>
						<label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
						<input name="email" type="text" id="infusionsoft_email" value="<?php echo $attendee_email ?>" />
					</p>
					<p>
						<label for="address"><?php _e('Address', 'event_espresso'); ?></label>
						<input name="address" type="text" id="infusionsoft_address" value="<?php echo $address ?>" />
					</p>
					<p>
						<label for="city"><?php _e('City', 'event_espresso'); ?></label>
						<input name="city" type="text" id="infusionsoft_city" value="<?php echo $city ?>" />
					</p>
					<p>
						<label for="state"><?php _e('State', 'event_espresso'); ?></label>
						<input name="state" type="text" id="infusionsoft_state" value="<?php echo $state ?>" />
					</p>
					<p>
						<label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
						<input name="zip" type="text" id="infusionsoft_zip" value="<?php echo $zip ?>" />
					</p>
				</fieldset>

				<fieldset id="infusionsoft-credit-card-info-dv">
					<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
					<p>
						<label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
						<input type="text" name="card_num" id="infusionsoft_card_num" autocomplete="off" />
					</p>
					<p>
						<label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
						<select id="infusionsoft_card-exp" name ="exp_month" class="required">
							<?php
							$curr_month = date("m");
							for ($i = 1; $i < 13; $i++) {
								$val = $i;
								if ($i < 10) {
									$val = '0' . $i;
								}
								$selected = ($i == $curr_month) ? " selected" : "";
								echo "<option value='$val'$selected>$val</option>";
							}
							?>
						</select>
					</p>
					<p>
						<label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
						<select id="infusionsoft_exp_year" name ="exp_year" class="required">
							<?php
							$curr_year = date("Y");
							for ($i = 0; $i < 10; $i++) {
								$disp_year = $curr_year + $i;
								$selected = ($i == 0) ? " selected" : "";
								echo "<option value='$disp_year'$selected>$disp_year</option>";
							}
							?>
						</select>
					</p>
					<p>
						<label for="ccv_code"><?php _e('CCV Code', 'event_espresso'); ?></label>
						<input type="text" name="ccv_code" id="infusionsoft_ccv_code" autocomplete="off" />
					</p>
				</fieldset>
					
				<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
				<input name="invoice_num" type="hidden" value="<?php echo 'is-' . event_espresso_session_id() ?>" />
				<input name="infusionsoft" type="hidden" value="true" />
				<input name="x_cust_id" type="hidden" value="<?php echo $attendee_id ?>" />
				
				<p class="event_form_submit">
					<input name="infusionsoft_submit" id="infusionsoft_submit" class="submit-payment-btn" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
				</p>
			</div>
		</form>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="infusionsoft-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>
	<?php
}
add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_infusionsoft');
