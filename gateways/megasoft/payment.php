<?php

function espresso_display_megasoft($data) {
	extract($data);
	global $org_options;
	?>
<div id="megasoft-payment-option-dv" class="payment-option-dv">

	<a id="megasoft-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="megasoft-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using Megasoft" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/megasoft/logo.png">
	</a>	

	<div id="megasoft-payment-option-form-dv" class="hide-if-js">
		<?php
		$megasoft_settings = get_option('event_espresso_megasoft_settings');
		$use_sandbox = $megasoft_settings['use_sandbox'];
		if ($use_sandbox) {
			echo '<p>Test credit card # 4007000000027</p>';
			echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		}
		if ($megasoft_settings['force_ssl_return']) {
			$home = str_replace('http://', 'https://', home_url());
		} else {
			$home = home_url();
		}
		if ($megasoft_settings['display_header']) {
?>
		<h3 class="payment_header"><?php echo $megasoft_settings['header']; ?></h3><?php } ?>

		<form id="megasoft_payment_form" name="megasoft_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">
			<div class = "event_espresso_form_wrapper">

				<fieldset id="megasoft-billing-info-dv">
					<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
					<p>
						<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
						<input name="first_name" type="text" id="megasoft_first_name" value="<?php echo $fname ?>" />
					</p>
					<p>
						<label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
						<input name="last_name" type="text" id="megasoft_last_name" value="<?php echo $lname ?>" />
					</p>
					<p>
						<label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
						<input name="email" type="text" id="megasoft_email" value="<?php echo $attendee_email ?>" />
					</p>
					<p>
						<label for="address"><?php _e('Address', 'event_espresso'); ?></label>
						<input name="address" type="text" id="megasoft_address" value="<?php echo $address ?>" />
					</p>
					<p>
						<label for="city"><?php _e('City', 'event_espresso'); ?></label>
						<input name="city" type="text" id="megasoft_city" value="<?php echo $city ?>" />
					</p>
					<p>
						<label for="state"><?php _e('State', 'event_espresso'); ?></label>
						<input name="state" type="text" id="megasoft_state" value="<?php echo $state ?>" />
					</p>
					<p>
						<label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
						<input name="zip" type="text" id="megasoft_zip" value="<?php echo $zip ?>" />
					</p>
					<p>
					  <label for="cid_code"><?php _e('ID code', 'event_espresso'); ?></label>
					  <select id="cid_code" name ="cid_code" class="required">
							<option value='V'><?php _e('Venezuelan Citizen','event_espresso'); ?></option>
							<option value='E'><?php _e('Non-Venezuelan Citizen living in Venezuela','event_espresso'); ?></option>
							<option value=''><?php _e('Non-Venezuelan Citizen living outside Venezuela','event_espresso'); ?></option>
						</select>
					</p>
					<p>
						<label for="cid"><?php _e('ID or passport number', 'event_espresso'); ?></label>
						<input name="cid" type="text" id="cid" value="" />
					</p>
				</fieldset>

				<fieldset id="megasoft-credit-card-info-dv">
					<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
					<p>
						<label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
						<input type="text" name="card_num" id="megasoft_card_num" />
					</p>
					<p>
						<label for="exp_date"><?php _e('Exp. Date', 'event_espresso'); ?></label>
						<input type="text" name="exp_date" id="megasoft_exp_date" />
					</p>
					<p>
						<label for="ccv_code"><?php _e('CCV Code', 'event_espresso'); ?></label>
						<input type="text" name="ccv_code" id="megasoft_ccv_code" />
					</p>
				</fieldset>
				<input name="invoice_num" type="hidden" value="<?php echo event_espresso_session_id() ?>" />
				<input name="megasoft" type="hidden" value="true" />
				<input name="cust_id" type="hidden" value="<?php echo $attendee_id ?>" />
				
				<p class="event_form_submit">
					<input name="megasoft_submit" id="megasoft_submit" class="submit-payment-btn" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
				</p>
			</div>
		</form>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="megasoft-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>
	<?php
}
add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_megasoft');
