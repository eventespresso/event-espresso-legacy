<?php
function espresso_display_securepay_aus($data) {
	extract($data);
	global $org_options;
	$securepay_aus_settings = get_option('event_espresso_securepay_aus_settings');
	$use_sandbox = $securepay_aus_settings['securepay_aus_use_sandbox'];
	
	wp_register_script( 'securepay_aus', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/securepay_aus/securepay_aus.js', array( 'jquery', 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'securepay_aus' );		
	?>
<div id="securepay_aus-payment-option-dv" class="payment-option-dv">

	<a id="securepay_aus-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="securepay_aus-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="securepay_aus-payment-option-form-dv" class="hide-if-js">	
		<div class="event-display-boxes">
			<?php
			if ($use_sandbox) {
				echo '<div id="sandbox-panel"><h2 class="section-title">' . __('SecurePay Test Mode', 'event_espresso') . '</h2><p>Test Master Card # 4444333322221111</p>';
				echo '<p>Exp: any future data</p>';
				echo '<p>CVV2: any </p>';
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3></div>';
			}
			if ($securepay_aus_settings['force_ssl_return']) {
				$home = str_replace('http://', 'https://', home_url());
			} else {
				$home = home_url();
			}
			if ($securepay_aus_settings['display_header']) {
?>
			<h3 class="payment_header"><?php echo $securepay_aus_settings['header']; ?></h3><?php } ?>

			<div class = "event_espresso_form_wrapper">
				<form id="securepay_aus_payment_form" name="securepay_aus_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">

					<fieldset id="securepay_aus-credit-card-info-dv">
						<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
						<p>
					        <label for="card-type"><?php _e('Card Card Type', 'event_espresso'); ?></label>
					        <?php echo select_input('creditcardtype',array(
								array('id'=>1,'text'=>  __("JCB", "event_espresso")),
								array('id'=>2,'text'=>  __("Amex", "event_espresso")),
								array('id'=>3,'text'=>  __("Diners Club", "event_espresso")),
								array('id'=>4,'text'=>  __("Bankcard", "event_espresso")),
								array('id'=>5,'text'=>  __("MasterCard", "event_espresso")),
								array('id'=>6,'text'=>  __("Visa", "event_espresso"))
							));?>
						</p>
						<p>
					        <label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
					        <input type="text" name="card_num" class="required" id="ppp_card_num" autocomplete="off" />
						</p>
						<p>
					        <label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
					        <select id="ppp_card-exp" name ="expmonth" class="med required">
										<?php
										for ($i = 1; $i < 13; $i++){
											$month_num_to_display = str_pad("$i", 2, "0", STR_PAD_LEFT);
											echo "<option value='$month_num_to_display'>$month_num_to_display</option>";
										}
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
											$value_year = $disp_year % 100;
											echo "<option value='$value_year'>$disp_year</option>";
										}
										?>
					        </select>
						</p>
						<p>
					        <label for="cvv"><?php _e('CVV Code', 'event_espresso'); ?></label>
					        <input type="text" name="cvv" id="ppp_exp_date" autocomplete="off"  class="small required"/>
						</p>
					</fieldset>
					<input name="securepay_aus" type="hidden" value="true" />
					<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />
					<p class="event_form_submit">
						<input name="securepay_aus_submit" id="securepay_aus_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />						
						<div class="clear"></div>
					</p>
					<span id="processing"></span>
				</form>

			</div><!-- / .event_espresso_or_wrapper -->
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="securepay_aus-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>		
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway','espresso_display_securepay_aus');
