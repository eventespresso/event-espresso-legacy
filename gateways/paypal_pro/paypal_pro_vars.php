<?php
function espresso_display_paypal_pro($data) {
	extract($data);
	global $org_options;
	$paypal_pro_settings = get_option('event_espresso_paypal_pro_settings');
	$use_sandbox = $paypal_pro_settings['paypal_pro_use_sandbox'];
	wp_register_script( 'paypal_pro', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/paypal_pro/paypal_pro.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'paypal_pro' );		
	?>
<div id="paypal_pro-payment-option-dv" class="payment-option-dv">

	<a id="paypal_pro-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="paypal_pro-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="paypal_pro-payment-option-form-dv" class="hide-if-js">	
		<div class="event-display-boxes">
			<?php
			if ($use_sandbox) {
				echo '<div id="sandbox-panel"><h2 class="section-title">' . __('PayPal Sandbox Mode', 'event_espresso') . '</h2><p>Test Master Card # 5424180818927383</p>';
				echo '<p>Exp: 10/2012</p>';
				echo '<p>CVV2: 123 </p>';
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3></div>';
			}
			if ($paypal_pro_settings['force_ssl_return']) {
				$home = str_replace('http://', 'https://', home_url());
			} else {
				$home = home_url();
			}
			if ($paypal_pro_settings['display_header']) {
?>
			<h3 class="payment_header"><?php echo $paypal_pro_settings['header']; ?></h3><?php } ?>

			<div class = "event_espresso_form_wrapper">
				<form id="paypal_pro_payment_form" name="paypal_pro_payment_form" method="post" action="<?php echo add_query_arg(array('r_id'=>$registration_id), get_permalink($org_options['return_url'])); ?>">
					
					<fieldset id="paypal-billing-info-dv">
						<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
						<p>
							<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
				        	<input name="first_name" type="text" id="ppp_first_name" class="required" value="<?php echo $fname ?>" />
						</p>
						<p>
					        <label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
					        <input name="last_name" type="text" id="ppp_last_name" class="required" value="<?php echo $lname ?>" />
						</p>
						<p>
					        <label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
					        <input name="email" type="text" id="ppp_email" class="required" value="<?php echo $attendee_email ?>" />
						</p>
						<p>
					        <label for="address"><?php _e('Address', 'event_espresso'); ?></label>
					        <input name="address" type="text" id="ppp_address" class="required" value="<?php echo $address ?>" />
						</p>
						<p>
					        <label for="city"><?php _e('City', 'event_espresso'); ?></label>
					        <input name="city" type="text" id="ppp_city" class="required" value="<?php echo $city ?>" />
						</p>
						<p>
					        <label for="state"><?php _e('State', 'event_espresso'); ?></label>
					        <input name="state" type="text" id="ppp_state" class="required" value="<?php echo $state ?>" />
						</p>
						<p>
					        <label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
					        <input name="zip" type="text" id="ppp_zip" class="required" value="<?php echo $zip ?>" />
						</p>
					</fieldset>

					<fieldset id="paypal-credit-card-info-dv">
						<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
						<p>
					        <label for="card-type"><?php _e('Card Card Type', 'event_espresso'); ?></label>
					        <select id="ppp_card-type" name ="creditcardtype" class="wide required">
										<?php
										foreach (explode(",", $paypal_pro_settings['paypal_api_credit_cards']) as $k => $v)
											echo "<option value='$v'>$v</option>";
										?>
					        </select>
						</p>
						<p>
					        <label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
					        <input type="text" name="card_num" class="required" id="ppp_card_num" autocomplete="off" />
						</p>
						<p>
					        <label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
					        <select id="ppp_card-exp" name ="expmonth" class="med required">
										<?php
										for ($i = 1; $i < 13; $i++)
											echo "<option value='$i'>$i</option>";
										?>
					        </select>
						</p>
						<p>
					        <label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
					        <select id="ppp_exp-year" name ="expyear" class="med required">
										<?php
										$curr_year = date("Y");
										for ($i = 0; $i < 10; $i++) {
											$disp_year = $curr_year + $i;
											echo "<option value='$disp_year'>$disp_year</option>";
										}
										?>
					        </select>
						</p>
						<p>
					        <label for="cvv"><?php _e('CVV Code', 'event_espresso'); ?></label>
					        <input type="text" name="cvv" id="ppp_exp_date" autocomplete="off"  class="small required"/>
						</p>
					</fieldset>
					
					<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
					<input name="paypal_pro" type="hidden" value="true" />
					<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />
					<input name='invoice' type='hidden' value='<?php echo md5(uniqid(rand(), true)) ?>'/>
					<p class="event_form_submit">
						<input name="paypal_pro_submit" id="paypal_pro_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />						
						<div class="clear"></div>
					</p>
					<span id="processing"></span>
				</form>

			</div><!-- / .event_espresso_or_wrapper -->
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="paypal_pro-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>		
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway','espresso_display_paypal_pro');
