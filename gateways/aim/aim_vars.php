<?php

function espresso_display_aim($data) {
	extract($data);
	global $org_options;
	wp_register_script( 'aim', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/aim/aim.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'aim' );	
	
	?>
<div id="aim-payment-option-dv" class="payment-option-dv">

	<a id="aim-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="aim-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using a Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="aim-payment-option-form-dv" class="hide-if-js">
		<?php
		$authnet_aim_settings = get_option('event_espresso_authnet_aim_settings');
		$use_sandbox = $authnet_aim_settings['use_sandbox'] || $authnet_aim_settings['test_transactions'];
		if ($use_sandbox) {
			echo '<p>Test credit card # 4007000000027</p>';
			echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		}
		
		if ($authnet_aim_settings['display_header']) {
?>
		<h3 class="payment_header"><?php echo $authnet_aim_settings['header']; ?></h3><?php } ?>

		<form id="aim_payment_form" name="aim_payment_form" method="post" action="<?php echo add_query_arg(array('r_id'=>$registration_id), get_permalink($org_options['return_url'])); ?>">
			<div class = "event_espresso_form_wrapper">

				<fieldset id="aim-billing-info-dv">
					<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
					<p>
						<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
						<input name="first_name" type="text" id="aim_first_name" value="<?php echo $fname ?>"  class="required"/>
					</p>
					<p>
						<label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
						<input name="last_name" type="text" id="aim_last_name" value="<?php echo $lname ?>"  class="required"/>
					</p>
					<p>
						<label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
						<input name="email" type="text" id="aim_email" value="<?php echo $attendee_email ?>"  class="required"/>
					</p>
					<p>
						<label for="address"><?php _e('Address', 'event_espresso'); ?></label>
						<input name="address" type="text" id="aim_address" value="<?php echo $address ?>"  class="required" />
					</p>
					<p>
						<label for="city"><?php _e('City', 'event_espresso'); ?></label>
						<input name="city" type="text" id="aim_city" value="<?php echo $city ?>"  class="required" />
					</p>
					<p>
						<label for="state"><?php _e('State', 'event_espresso'); ?></label>
						<input name="state" type="text" id="aim_state" value="<?php echo $state ?>"  class="required" />
					</p>
					<p>
						<label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
						<input name="zip" type="text" id="aim_zip" value="<?php echo $zip ?>"  class="required" />
					</p>
				</fieldset>

				<fieldset id="aim-credit-card-info-dv">
					<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
					<p>
						<label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
						<input type="text" name="card_num" id="aim_card_num" autocomplete="off" class="required" />
					</p>
					<p>
						<label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
						<select id="aim_card-exp" name ="exp_month" class="med required">
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
						<select id="aim_exp_year" name ="exp_year" class="med required">
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
						<input type="text" name="ccv_code" id="aim_ccv_code" autocomplete="off"  class="small required"/>
					</p>
				</fieldset>
					
				<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
				<input name="invoice_num" type="hidden" value="<?php echo 'au-' . event_espresso_session_id() ?>" />
				<input name="authnet_aim" type="hidden" value="true" />
				<input name="x_cust_id" type="hidden" value="<?php echo $attendee_id ?>" />

				<input name="aim_submit" id="aim_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
				<div class="clear"></div>

			</div>
		</form>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="aim-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>
	<?php
}
add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_aim');
