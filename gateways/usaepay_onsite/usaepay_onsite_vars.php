<?php
function espresso_display_usaepay_onsite($data) {
	extract($data);
	global $org_options;
	$usaepay_onsite_settings = get_option('event_espresso_usaepay_onsite_settings');
	$use_sandbox = $usaepay_onsite_settings['usaepay_onsite_use_sandbox'];
	?>
		<?php
		if ($use_sandbox) {
			echo '<div id="sandbox-panel"><h2 class="section-title">' . __('PayPal Sandbox Mode', 'event_espresso') . '</h2><p>Test Master Card # 5424180818927383</p>';
			echo '<p>Exp: 10/2012</p>';
			echo '<p>CVV2: 123 </p>';
			echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3></div>';
		}
		if ($usaepay_onsite_settings['force_ssl_return']) {
			$home = str_replace('http://', 'https://', home_url());
		} else {
			$home = home_url();
		}

	wp_register_script( 'usaepay_onsite', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/usaepay_onsite/usaepay_onsite.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'usaepay_onsite' );	

?>
<div id="usaepay_onsite-payment-option-dv" class="payment-option-dv">

	<a id="usaepay_onsite-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="usaepay_onsite-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using a Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="usaepay_onsite-payment-option-form-dv" class="hide-if-js">
<?php		
		if ($usaepay_onsite_settings['display_header']) {
	?>
		<h3 class="payment_header"><?php echo $usaepay_onsite_settings['header']; ?></h3>
<?php } ?>

		<div class = "event_espresso_form_wrapper">
			<form id="usaepay_onsite_payment_form" name="usaepay_onsite_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">

				<fieldset id="usaepay_onsite-billing-info-dv">
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

				<fieldset id="usaepay_onsite-credit-card-info-dv">
					<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
					<p>
				        <label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
				        <input type="text" name="card_num" class="required" id="ppp_card_num" autocomplete="off" />
					</p>
					<p>
				        <label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
				        <select id="usaepay_onsite_card-exp" name ="expmonth" class="med required">

									<?php
									for ($i = 1; $i < 10; $i++)
										echo "<option value='0$i'>0$i</option>";
									for ($i = 10; $i < 13; $i++)
										echo "<option value='$i'>$i</option>";
									?>

				        </select>
					</p>
					<p>
				        <label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
				        <select id="usaepay_onsite_exp-year" name ="expyear" class="med required">

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
				        <input type="text" name="cvv" id="usaepay_onsite_exp_date" autocomplete="off" class="small required"/>
					</p>
				</fieldset>
				<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
				<input name="usaepay_onsite" type="hidden" value="true" />
				<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />
				<p class="event_form_submit">
					<input name="usaepay_onsite_submit" id="usaepay_onsite_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
					<div class="clear"></div>
				</p>
				<span id="processing"></span>
			</form>

		</div><!-- / .event_espresso_or_wrapper -->
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="usaepay_onsite-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 

'event_espresso'); ?></a>
		</p>

	</div>
</div>
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway','espresso_display_usaepay_onsite');
