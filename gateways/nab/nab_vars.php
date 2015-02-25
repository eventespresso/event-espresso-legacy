<?php

function espresso_display_nab($payment_data) {
	include_once ('Nab.php');
	$mynab = new Espresso_nab(); // initiate an instance of the class
	global $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$nab_result_url = espresso_build_gateway_url('return_url', $payment_data, 'nab');
	$nab_settings = get_option('event_espresso_nab_settings');
	$nab_id = $nab_settings['nab_merchant_id'];
	$nab_pass = $nab_settings['nab_merchant_password'];
	$use_sandbox = $nab_settings['nab_use_sandbox'];
	$temp_timezone_holder = date_default_timezone_get();
	date_default_timezone_set('UTC');
	$timestamp = date('YmdHis');
	date_default_timezone_set($temp_timezone_holder);
	if ($use_sandbox == 1) {
		$nab_post_url = "https://transact.nab.com.au/test/directpost/authorise";
	} else {
		$nab_post_url = "https://transact.nab.com.au/live/directpost/authorise";
	}
	$quantity = isset($quantity) && $quantity > 0 ? $quantity : espresso_count_attendees_for_registration($payment_data['attendee_id']);
	$mynab->addField('EPS_MERCHANT', $nab_id);
	$mynab->addField('EPS_PASSWORD', $nab_pass);
	$mynab->addField('EPS_REFERENCEID', $payment_data['registration_id']);
	$mynab->addField('EPS_AMOUNT', number_format($payment_data['event_cost'], 2, '.', ''));
	$mynab->addField('EPS_TIMESTAMP', $timestamp);
	wp_register_script( 'nab', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/nab/nab.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'nab' );		
	?>
<div id="nab-payment-option-dv" class="payment-option-dv">

	<a id="nab-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="nab-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using a Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="nab-payment-option-form-dv" class="hide-if-js">	
	<div class = "event_espresso_form_wrapper">
		<form id="nab_payment_form" method="post" action="<?php echo $nab_post_url; ?>">
			<input type="hidden" name="EPS_MERCHANT" value="<?php echo $nab_id; ?>">
			<input type="hidden" name="EPS_PASSWORD" value="<?php echo $nab_pass; ?>">
			<input type="hidden" name="EPS_REFERENCEID" value="<?php echo $payment_data['registration_id']; ?>">
			<input type="hidden" name="EPS_AMOUNT" value="<?php echo number_format($payment_data['event_cost'], 2, '.', ''); ?>">
			<input type="hidden" name="EPS_TIMESTAMP" value="<?php echo $timestamp; ?>">
			<input type="hidden" name="EPS_FINGERPRINT" value="<?php echo $mynab->prepareSubmit(); ?>">
			<input type="hidden" name="EPS_RESULTURL" value="<?php echo $nab_result_url; ?>">
			<input type="hidden" name="EPS_FIRSTNAME" value="<?php echo $payment_data['fname']; ?>">
			<input type="hidden" name="EPS_LASTNAME" value="<?php echo $payment_data['lname']; ?>">
			<input type="hidden" name="EPS_ZIPCODE" value="<?php echo $payment_data['zip']; ?>">
			<input type="hidden" name="EPS_TOWN" value="<?php echo $payment_data['city']; ?>">
			<input type="hidden" name="EPS_EMAILADDRESS" value="<?php echo $payment_data['attendee_email']; ?>">

			<fieldset id="nab-billing-info-dv">
				<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso') ?></h4>
				<p>
					<label for="EPS_CARDTYPE"><?php _e('Card Type:', 'event_espresso');?></label>
					<select name="EPS_CARDTYPE" class="wide required inputbox">
						<option value="visa"><?php _e('Visa', 'event_espresso');?></option>
						<option value="mastercard"><?php _e('MasterCard', 'event_espresso');?></option>
						<option value="amex"><?php _e('Amex', 'event_espresso');?></option>
					</select>
				</p>
				<p>
					<label for="EPS_CARDNUMBER"><?php _e('Card Number:', 'event_espresso');?></label>
						<input type="text" class="required inputbox" name="EPS_CARDNUMBER" size="27" autocomplete="off"/>
				</p>
				<p>
					<label for="EPS_CCV"><?php _e('Card CCV:', 'event_espresso');?></label>
					<input type="text"   class="small required inputbox" name="EPS_CCV" size="27" autocomplete="off" />
				</p>
				<p>
					<label for="EPS_EXPIRYMONTH"><?php _e('Card Expires:', 'event_espresso');?></label>
					<select name="EPS_EXPIRYMONTH" class="med required inputbox">
							<option value="">- <?php _e('Month', 'event_espresso');?> -</option>
							<option value="1">01</option>
							<option value="2">02</option>
							<option value="3">03</option>
							<option value="4">04</option>
							<option value="5">05</option>
							<option value="6">06</option>
							<option value="7">07</option>
							<option value="8">08</option>
							<option value="9">09</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
						</select>
						&nbsp;
						<select name="EPS_EXPIRYYEAR" class="med required inputbox">
							<option value="">- <?php _e('Year', 'event_espresso');?> -</option>
							<option value="2009">2009</option>
							<option value="2010">2010</option>
							<option value="2011">2011</option>
							<option value="2012">2012</option>
							<option value="2013">2013</option>
							<option value="2014">2014</option>
							<option value="2015">2015</option>
							<option value="2016">2016</option>
							<option value="2017">2017</option>
							<option value="2018">2018</option>
							<option value="2019">2019</option>
							<option value="2020">2020</option>
							<option value="2021">2021</option>
							<option value="2022">2022</option>
							<option value="2023">2023</option>
							<option value="2024">2024</option>
							<option value="2025">2025</option>
						</select>
				</p>
				<p class="event_form_submit">
					<input type="submit" value="<?php _e('Complete Purchase', 'event_espresso');?>" class="submit-payment-btn allow-leave-page"/>
					<div class="clear"></div>
				</p>
			</fieldset>
		</form>
	<?php
	wp_deregister_script('jquery.validate.pack');


	if ($use_sandbox == true) {
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$mynab->dump_fields();
	}
?>	

		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="nab-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
	</div>
</div>
<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_nab');
