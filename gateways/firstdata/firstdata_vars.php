<?php

function espresso_display_firstdata($data) {
	extract($data);
	global $org_options;
	$firstdata_settings = get_option('event_espresso_firstdata_settings');
	$use_sandbox = $firstdata_settings['use_sandbox'];
	if ($use_sandbox) {
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
	}
	
	wp_register_script( 'firstdata', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/firstdata/firstdata.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'firstdata' );	
	
	?>
<div id="firstdata-payment-option-dv" class="payment-option-dv">

	<a id="firstdata-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="firstdata-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using a Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="firstdata-payment-option-form-dv" class="hide-if-js">	
		<div class="event-display-boxes">
			<?php if ($firstdata_settings['display_header']) { ?>
				<h3 class="payment_header"><?php echo $firstdata_settings['header']; ?></h3><?php } ?>
			<div class = "event_espresso_form_wrapper">
				<form id="firstdata_payment_form" name="firstdata_payment_form" method="post" action="<?php echo add_query_arg(array('r_id'=>$registration_id), get_permalink($org_options['return_url'])); ?>">
					<fieldset id="firstdata-billing-info-dv">
						<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
						<p>
							<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
							<input name="first_name" type="text" id="fd_first_name" class="required" value="<?php echo $fname ?>" />
						</p>
						<p>
							<label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
							<input name="last_name" type="text" id="fd_last_name" class="required" value="<?php echo $lname ?>" />
						</p>
						<p>
							<label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
							<input name="email" type="text" id="fd_email" class="required" value="<?php echo $attendee_email ?>" />
						</p>
						<p>
							<label for="address"><?php _e('Address', 'event_espresso'); ?></label>
							<input name="address" type="text" id="fd_address" class="required" value="<?php echo $address ?>" />
						</p>
						<p>
							<label for="city"><?php _e('City', 'event_espresso'); ?></label>
							<input name="city" type="text" id="fd_city" class="required" value="<?php echo $city ?>" />
						</p>
						<p>
							<label for="state"><?php _e('State', 'event_espresso'); ?></label>
							<input name="state" type="text" id="fd_state" class="required" value="<?php echo $state ?>" />
						</p>
						<p>
							<label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
							<input name="zip" type="text" id="fd_zip" class="required" value="<?php echo $zip ?>" />
						</p>
					</fieldset>

					<fieldset id="firstdata-credit-card-info-dv">
						<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
						<p>
							<label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
							<input type="text" name="card_num" class="required" id="fd_card_num" autocomplete="off" />
						</p>
						
						<p>
							<label for="card_num"><?php _e('Card Card Type', 'event_espresso'); ?></label>
							<select name ="creditcardtype" class="wide required">
								<?php
								foreach (explode(",", $firstdata_settings['firstdata_credit_cards']) as $k => $v)
									echo "<option value='$v'>$v</option>";
								?>
							</select>
						</p>
						
						<p>
							<label for="card_num"><?php _e('Expiration Month', 'event_espresso'); ?></label>
							<select name ="expmonth" id="fd_expmonth" class="med required">
								<?php
								for ($i = 1; $i < 13; $i++)
									echo "<option value='$i'>$i</option>";
								?>
							</select>
						</p>

						<p>
							<label for=""><?php _e('Expiration Year', 'event_espresso'); ?></label>
							<select name ="expyear" id="fd_expyear" class="med required">
								<?php
								$curr_year = date("Y");
								for ($i = 0; $i < 10; $i++) {
									$disp_year = $curr_year + $i;
									echo "<option value='" . substr($disp_year, 2, 2) . "'>$disp_year</option>";
								}
								?>
							</select>
						</p>

						<p>
							<label for="cvv"><?php _e('CVV Code', 'event_espresso'); ?></label>
							<input type="text" name="cvv" id="fd_cvv" class="small required" autocomplete="off" />
						</p>
					</fieldset>
					
					<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
					<input name="firstdata" type="hidden" value="1" />
					<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />

					<p class="event_form_submit">
						<input name="firstdata_submit" id="firstdata_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
						<div class="clear"></div>
					</p>
					<span id="processing"></span>
				</form><!-- / close firstdata form -->
			</div>
		</div>

		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="firstdata-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_firstdata');
