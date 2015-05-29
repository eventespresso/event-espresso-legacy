<?php

function espresso_process_ideal($payment_data) {
	global $org_options, $wpdb;
	$ideal_mollie_settings = get_option('event_espresso_ideal_mollie_settings');
	require_once('ideal.class.php');
	$partner_id = $ideal_mollie_settings['ideal_mollie_partner_id'];
	$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
//amount needs to be in cents
	$amount = (int) ($payment_data['total_cost'] * 100);
	$iDEAL = new Espresso_iDEAL_Payment($partner_id);

	if ($ideal_mollie_settings['ideal_mollie_use_sandbox'])
		$iDEAL->setTestMode();
	if ($ideal_mollie_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	if (!empty($_POST['bank_id'])) {

		$return_url = $home . '/?page_id=' . $org_options['return_url'] . '&id=' . $payment_data['attendee_id'] . '&r_id=' . $payment_data['registration_id'] . '&type=ideal';
		$report_url = $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $payment_data['attendee_id'] . '&r_id=' . $payment_data['registration_id'] . '&event_id=' . $payment_data['event_id'] . '&attendee_action=post_payment&form_action=payment&ideal=1';
//Find the correct amount so that unsavory characters don't change it in the previous form

		$description = stripslashes_deep($payment_data['event_name']);
		//echo sprintf("bank id%s,amoun%s,description%s,returnurl%sreporturl%s",$_POST['bank_id'], $amount, $description, $return_url, $report_url);
		if ($iDEAL->createPayment($_POST['bank_id'], $amount, $description, $return_url, $report_url)) {
			header("Location: " . $iDEAL->getBankURL());
			exit;
		} else {
			echo '<p>De betaling kon niet aangemaakt worden.</p>';
			echo '<p><strong>Foutmelding:</strong> ', $iDEAL->getErrorMessage(), '</p>';
			$payment_data['txn_details'] = '';
		}
	} elseif (isset($_POST['bank_id']) && $_POST['bank_id'] == '') {
		echo "<p>" . __("Please use your browser's back button and select a bank.", 'event_espresso');
	}

	$bank_array = $iDEAL->getBanks();

	if ($bank_array == false) {
		echo '<p>Er is een fout opgetreden bij het ophalen van de banklijst: ', $iDEAL->getErrorMessage(), '</p>';
	}
	?>
<div id="ideal-payment-option-dv" class="payment-option-dv">

	<a id="ideal-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="ideal-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using a Credit Card" src="<?php echo ( ! empty( $ideal_mollie_settings['button_url'] ) ? $ideal_mollie_settings['button_url'] : EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/ideal/ideal-mollie-logo.png') ?>">
	</a>	

	<div id="ideal-payment-option-form-dv" class="hide-if-js">	
		<div class="event-display-boxes">
		<div class = "event_espresso_form_wrapper">
			<form id="ideal-mollie-form" class="ee-forms" method="post" action="<?php echo $home . '/?page_id=' . $org_options['notify_url']; ?>">
			
				<fieldset id="ideal-billing-info-dv">
					<h4 class="section-title"><?php _e('Select Bank', 'event_espresso') ?></h4>
					<p>
						<select id ="bank_id" name="bank_id" class="required">

							<?php foreach ($bank_array as $bank_id => $bank_name) { ?>
								<option value="<?php echo $bank_id ?>"><?php echo $bank_name ?></option>
							<?php } ?>

						</select>
					</p>
				</fieldset>
				<input name="amount" type="hidden" value="<?php echo $amount; ?>" />
				<input name="ideal" type="hidden" value="1" />
				<input name="id" type="hidden" value="<?php echo $payment_data['attendee_id']; ?>" />				
				<input name='registration_id' type='hidden' value='<?php echo $payment_data['registration_id']?>'/>
				<p class="event_form_submit">
					<input id="submit_ideal" type="submit" class="submit-payment-btn allow-leave-page" name="submit" value="Betaal via iDEAL" />
					<div class="clear"></div>
				</p>
			</form>
		</div>

		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="ideal-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
	</div>
</div>
<?php
}


