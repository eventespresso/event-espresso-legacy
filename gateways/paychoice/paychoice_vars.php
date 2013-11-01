<?php

function espresso_display_paychoice($payment_data) {
	extract($payment_data);
	global $org_options;
	if ( file_exists( EVENT_ESPRESSO_GATEWAY_DIR . '/paychoice/paychoice.png' ))  {
		$button_url = EVENT_ESPRESSO_GATEWAY_DIR . '/paychoice/paychoice.png';
	} else {
		$button_url = EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/paychoice/paychoice.png';
	}
	
	$paychoice_settings = get_option('event_espresso_paychoice_settings');
	
	$paychoice_settings['header'] = $paychoice_settings['display_header'] ? '<h3 class="payment_header">' . $paychoice_settings['header'] . '</h3>' : '';

	wp_register_script( 'paychoice', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/paychoice/paychoice.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'paychoice' );		
?>

<div id="paychoice-payment-option-dv" class="payment-option-dv">

	<a id="paychoice-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="paychoice-payment-option-form" style="cursor:pointer;">
		<img alt="<?php _e('Secure payment gateway by PayChoice', 'event_espresso'); ?>" src="<?php echo $button_url ?>">
	</a>

	<div id="paychoice-payment-option-form-dv" class="hide-if-js">
		<?php echo $paychoice_settings['header']; ?>	
		<p class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></p>
		<div class = "event_espresso_form_wrapper">
			<form id="paychoice_payment_form" name="paychoice_payment_form" method="post" action="<?php echo add_query_arg(array('r_id'=>$registration_id), get_permalink($org_options['return_url'])); ?>">
				<p>
					<label for="cc_name"><?php _e('Name on Card', 'event_espresso'); ?> <em>*</em></label>
					<input type="text" name="cc_name" id="paychoice_cc_name" class="required" />
				</p>	
				<p>
					<label for="card_num"><?php _e('Card Number', 'event_espresso'); ?> <em>*</em></label>
					<input type="text" name="cc" class="required" id="paychoice_cc" />
				</p>
				<p>
					<label for="card-type"><?php _e('Card Type', 'event_espresso'); ?> <em>*</em></label>
					<select id="paychoice_card-type" name="cc_type" class="wide required">
						<option value=''></option>
						<option value='Visa'><?php _e('Visa', 'event_espresso'); ?></option>
						<option value='MasterCard'><?php _e('Mastercard', 'event_espresso'); ?></option>
						<option value='DinersClub'><?php _e('Diners', 'event_espresso'); ?></option>
						<option value='AmericanExpress'><?php _e('American Express', 'event_espresso'); ?></option>
					</select>
				</p>
				<p>
					<label for="exp_month"><?php _e('Expiration Date', 'event_espresso'); ?> <em>*</em></label>
					<select id="paychoice_card-exp" name="exp_month" class="med required">
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
					&nbsp;/&nbsp;
					<select id="paychoice_exp_year" name="exp_year" class="med required">
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
					<label for="csc"><?php _e('CVC Code', 'event_espresso'); ?> <em>*</em></label>
					<input type="text" name="csc" id="paychoice_csc" class="small required"/>
				</p>
				<input name="paychoice" type="hidden" value="true" />
				<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />
				<input name="paychoice_submit" id="paychoice_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
				<div class="clear"></div><br/>
			</form>
		</div>
	</div>
</div>
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_paychoice');