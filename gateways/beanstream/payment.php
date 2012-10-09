<?php
function espresso_display_beanstream($data) {
	extract($data);
	global $org_options;
	$beanstream_settings = get_option('event_espresso_beanstream_settings');
	$use_sandbox = $beanstream_settings['beanstream_use_sandbox'];

	wp_register_script( 'beanstream', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/beanstream/beanstream.js', array( 'jquery', 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'beanstream' );	
	
	?>
<div id="beanstream-payment-option-dv" class="payment-option-dv">

	<a id="beanstream-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="beanstream-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using Beanstream" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/beanstream/beanstream-logo.png">
	</a>	

	<div id="beanstream-payment-option-form-dv" class="hide-if-js">	
		<div class="event-display-boxes">
			<?php
			if ($use_sandbox) {
				echo '<div id="sandbox-panel"><h2 class="section-title">' . __('Beanstream Sandbox Mode', 'event_espreso') . '</h2><p>Test Master Card # 5100000010001004</p>';
				echo '<p>Exp: 10/2012</p>';
				echo '<p>CVV2: 123 </p>';
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3></div>';
			}
			if ($beanstream_settings['force_ssl_return']) {
				$home = str_replace('http://', 'https://', home_url());
			} else {
				$home = home_url();
			}
			if ($beanstream_settings['display_header']) {
?>

			<h3 class="payment_header"><?php echo $beanstream_settings['header']; ?></h3><?php } ?>
			<div class = "event_espresso_form_wrapper">
				<form id="beanstream_payment_form" name="beanstream_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">

					<fieldset id="beanstream-billing-info-dv">
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
							<label for="state">
								<?php _e('State / Province', 'event_espresso'); ?>
							</label>
							<?php
							$values = array(
									array('id' => 'AB', 'text' => __('Alberta', 'event_espresso')),
									array('id' => 'AK', 'text' => __('Alaska', 'event_espresso')),
									array('id' => 'AL', 'text' => __('Alabama', 'event_espresso')),
									array('id' => 'AS', 'text' => __('American Somoa', 'event_espresso')),
									array('id' => 'AR', 'text' => __('Arkansas', 'event_espresso')),
									array('id' => 'AZ', 'text' => __('Arizona', 'event_espresso')),
									array('id' => 'BC', 'text' => __('British Columbia', 'event_espresso')),
									array('id' => 'CA', 'text' => __('California', 'event_espresso')),
									array('id' => 'CO', 'text' => __('Colorado', 'event_espresso')),
									array('id' => 'CT', 'text' => __('Connecticut', 'event_espresso')),
									array('id' => 'DC', 'text' => __('District of Columbia', 'event_espresso')),
									array('id' => 'DE', 'text' => __('Delaware', 'event_espresso')),
									array('id' => 'FL', 'text' => __('Florida', 'event_espresso')),
									array('id' => 'GA', 'text' => __('Georgia', 'event_espresso')),
									array('id' => 'GU', 'text' => __('Guam', 'event_espresso')),
									array('id' => 'HI', 'text' => __('Hawaii', 'event_espresso')),
									array('id' => 'IA', 'text' => __('Iowa', 'event_espresso')),
									array('id' => 'ID', 'text' => __('Idaho', 'event_espresso')),
									array('id' => 'IL', 'text' => __('Illinois', 'event_espresso')),
									array('id' => 'IN', 'text' => __('Indiana', 'event_espresso')),
									array('id' => 'KS', 'text' => __('Kansas', 'event_espresso')),
									array('id' => 'KY', 'text' => __('Kentucky', 'event_espresso')),
									array('id' => 'LA', 'text' => __('Louisiana', 'event_espresso')),
									array('id' => 'MA', 'text' => __('Massachusetts', 'event_espresso')),
									array('id' => 'MB', 'text' => __('Manitoba', 'event_espresso')),
									array('id' => 'MD', 'text' => __('Maryland', 'event_espresso')),
									array('id' => 'ME', 'text' => __('Maine', 'event_espresso')),
									array('id' => 'MI', 'text' => __('Michigan', 'event_espresso')),
									array('id' => 'FM', 'text' => __('Micronesia', 'event_espresso')),
									array('id' => 'MN', 'text' => __('Minnesota', 'event_espresso')),
									array('id' => 'MO', 'text' => __('Missouri', 'event_espresso')),
									array('id' => 'MS', 'text' => __('Mississippi', 'event_espresso')),
									array('id' => 'MT', 'text' => __('Montana', 'event_espresso')),
									array('id' => 'NB', 'text' => __('New Brunswick', 'event_espresso')),
									array('id' => 'NC', 'text' => __('North Carolina', 'event_espresso')),
									array('id' => 'ND', 'text' => __('North Dakota', 'event_espresso')),
									array('id' => 'NE', 'text' => __('Nebraska', 'event_espresso')),
									array('id' => 'NL', 'text' => __('Newfoundland/Labrador', 'event_espresso')),
									array('id' => 'NH', 'text' => __('New Hampshire', 'event_espresso')),
									array('id' => 'NJ', 'text' => __('New Jersey', 'event_espresso')),
									array('id' => 'NM', 'text' => __('New Mexico', 'event_espresso')),
									array('id' => 'NS', 'text' => __('Nova Scotia', 'event_espresso')),
									array('id' => 'NT', 'text' => __('Northwest Territories', 'event_espresso')),
									array('id' => 'NU', 'text' => __('Nunavut', 'event_espresso')),
									array('id' => 'NV', 'text' => __('Nevada', 'event_espresso')),
									array('id' => 'NY', 'text' => __('New York', 'event_espresso')),
									array('id' => 'OH', 'text' => __('Ohio', 'event_espresso')),
									array('id' => 'OK', 'text' => __('Oklahoma', 'event_espresso')),
									array('id' => 'ON', 'text' => __('Ontario', 'event_espresso')),
									array('id' => 'OR', 'text' => __('Oregon', 'event_espresso')),
									array('id' => 'PA', 'text' => __('Pennsylvania', 'event_espresso')),
									array('id' => 'PE', 'text' => __('Prince Edward Island', 'event_espresso')),
									array('id' => 'PR', 'text' => __('Puerto Rico', 'event_espresso')),
									array('id' => 'QC', 'text' => __('Quebec', 'event_espresso')),
									array('id' => 'RI', 'text' => __('Rhode Island', 'event_espresso')),
									array('id' => 'SC', 'text' => __('South Carolina', 'event_espresso')),
									array('id' => 'SD', 'text' => __('South Dakota', 'event_espresso')),
									array('id' => 'SK', 'text' => __('Saskatchewan', 'event_espresso')),
									array('id' => 'TN', 'text' => __('Tennessee', 'event_espresso')),
									array('id' => 'TX', 'text' => __('Texas', 'event_espresso')),
									array('id' => 'UT', 'text' => __('Utah', 'event_espresso')),
									array('id' => 'VA', 'text' => __('Virginia', 'event_espresso')),
									array('id' => 'VI', 'text' => __('Virgin Islands', 'event_espresso')),
									array('id' => 'VT', 'text' => __('Vermont', 'event_espresso')),
									array('id' => 'WA', 'text' => __('Washington', 'event_espresso')),
									array('id' => 'WI', 'text' => __('Wisconsin', 'event_espresso')),
									array('id' => 'WV', 'text' => __('West Virginia', 'event_espresso')),
									array('id' => 'WY', 'text' => __('Wyoming', 'event_espresso')),
									array('id' => 'YT', 'text' => __('Yukon', 'event_espresso')),
									array('id' => '--', 'text' => __('Outside U.S./Canada', 'event_espresso'))
								);
								echo select_input( 'state', $values, 'AB' );
							?>
						</p>
						<p>
					        <label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
					        <input name="zip" type="text" id="ppp_zip" class="required" value="<?php echo $zip ?>" />
						</p>
						<p>
					        <label for="phone"><?php _e('Phone', 'event_espresso'); ?></label>
					        <input name="phone" type="text" id="ppp_phone" class="required" value="<?php echo $phone ?>" />
						</p>
					</fieldset>

					<fieldset id="beanstream-credit-card-info-dv">
						<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
							<p>
						        <label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
						        <input type="text" name="card_num" class="required" id="ppp_card_num" />
							</p>
							<p>
						        <label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
						        <select id="ppp_card-exp" name ="expmonth" class="required">
									<?php
									for ($i = 1; $i < 13; $i++)
										echo "<option value='$i'>$i</option>";
									?>
						        </select>
							</p>
							<p>
						        <label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
						        <select id="ppp_exp-year" name ="expyear" class="required">
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
						        <input type="text" name="cvv" id="ppp_exp_date" />
							</p>
						</fieldset>

						<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
						<input name="beanstream" type="hidden" value="true" />
						<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />

						<input name="beanstream_submit" id="beanstream_submit" class="submit-payment-btn" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
						<span id="processing"></span>
					</form>

				</div><!-- / .event_espresso_or_wrapper -->
			</div>

		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="beanstream-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>		
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway','espresso_display_beanstream');
