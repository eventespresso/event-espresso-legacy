<?php

function espresso_display_paytrace($payment_data) {
	extract($payment_data);
	global $org_options;
	$paytrace_settings = get_option('event_espresso_paytrace_settings');
	
	wp_register_script( 'paytrace', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/paytrace/paytrace.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'paytrace' );
?>
<div id="paytrace-payment-option-dv" class="payment-option-dv">

	<a id="paytrace-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="paytrace-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using a Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="paytrace-payment-option-form-dv" class="hide-if-js">
	
		<?php if ($paytrace_settings['display_header']) { ?><h3 class="payment_header"><?php echo $paytrace_settings['header']; ?></h3><?php } ?>

		<div class = "event_espresso_form_wrapper">
			<form id="paytrace_payment_form" name="paytrace_payment_form" method="post" action="<?php echo add_query_arg(array('r_id'=>$registration_id), get_permalink($org_options['return_url'])); ?>">

				<fieldset id="paytrace-billing-info-dv">
					<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
					<p>
						<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
						<input name="first_name" type="text" id="pt_first_name" class="required" value="<?php echo $fname ?>" />
					</p>
					<p>
						<label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
						<input name="last_name" type="text" id="pt_last_name" class="required" value="<?php echo $lname ?>" />
					</p>
					<p>
						<label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
						<input name="email" type="text" id="pt_email" class="required" value="<?php echo $attendee_email ?>" />
					</p>
					<p>
						<label for="address"><?php _e('Address', 'event_espresso'); ?></label>
						<input name="address" type="text" id="pt_address" class="required" value="<?php echo $address ?>" />
					</p>
					<p>
						<label for="city"><?php _e('City', 'event_espresso'); ?></label>
						<input name="city" type="text" id="pt_city" class="required" value="<?php echo $city ?>" />
					</p>
					<p>
						<label for="state"><?php _e('State', 'event_espresso'); ?></label>
						<input name="state" type="text" id="pt_state" class="required" value="<?php echo $state ?>" />
					</p>
					<p>
						<label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
						<input name="zip" type="text" id="pt_zip" class="required" value="<?php echo $zip ?>" />
					</p>
				</fieldset>

				<fieldset id="paytrace-credit-card-info-dv">
					<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
					<p>
						<label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
						<input type="text" name="cc" class="required" id="pt_cc"  autocomplete="off"/>
					</p>
					<p>
						<label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
						<select id="pt_card-exp" name ="exp_month" class="med required">
							<?php
							for ($i = 1; $i < 13; $i++)
								echo "<option value='$i'>$i</option>";
							?>
						</select>
					</p>
					<p>
						<label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
						<select id="pt_exp_year" name ="exp_year" class="med required">
							<?php
							$curr_year = date("y");
							for ($i = 0; $i < 10; $i++) {
								$disp_year = $curr_year + $i;
								echo "<option value='$disp_year'>$disp_year</option>";
							}
							?>
						</select>
					</p>
					<p>
						<label for="cvv"><?php _e('CVV Code', 'event_espresso'); ?></label>
						<input id="pt_cvv" type="text" name="csc" autocomplete="off"  class="small required"/>
					</p>
				</fieldset>
				<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
				<input name="paytrace" type="hidden" value="true" />
				<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />
				<p class="event_form_submit">
					<input name="paytrace_submit" id="paytrace_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
					<div class="clear"></div>
				</p>
			</form>
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="paytrace-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>	
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_paytrace');
