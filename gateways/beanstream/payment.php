<?php
function espresso_display_beanstream($data) {
	extract($data);
	global $org_options;
	$beanstream_settings = get_option('event_espresso_beanstream_settings');
	$use_sandbox = $beanstream_settings['beanstream_use_sandbox'];
	?>
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
		<p class="section-title"><?php _e('Billing Information', 'event_espresso') ?></p>
		<div class = "event_espresso_form_wrapper">
			<form id="beanstream_payment_form" name="beanstream_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">
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
				<p class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></p>
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
	        <input type="text" name="cvv" id="ppp_exp_date" />
				</p>
				<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
				<input name="beanstream" type="hidden" value="true" />
				<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />

				<input name="beanstream_submit" id="beanstream_submit" class="btn_event_form_submit payment-submit" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
				<span id="processing"></span>
			</form>

		</div><!-- / .event_espresso_or_wrapper -->
		<script>
			jQuery(function(){

				jQuery('#beanstream_payment_form').validate();

				jQuery('#beanstream_payment_form').submit(function(){

					if (jQuery('#beanstream_payment_form').valid()){
						jQuery('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
						//jQuery(':input[name="beanstream_submit"]').attr('disabled', 'disabled');
					}

				})
			});
		</script>
	</div>
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway','espresso_display_beanstream');
