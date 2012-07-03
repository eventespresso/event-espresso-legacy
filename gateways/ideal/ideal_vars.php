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
	$iDEAL = new iDEAL_Payment($partner_id);

	if ($ideal_mollie_settings['ideal_mollie_use_sandbox'])
		$iDEAL->setTestMode();
	if ($ideal_mollie_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	if (!empty($_POST['bank_id'])) {

		$return_url = $home . '/?page_id=' . $org_options['return_url'] . '&id=' . $payment_data['attendee_id'] . '&registration_id=' . $payment_data['registration_id'] . '&type=ideal';
		$report_url = $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $payment_data['attendee_id'] . '&registration_id=' . $payment_data['registration_id'] . '&event_id=' . $payment_data['event_id'] . '&attendee_action=post_payment&form_action=payment&ideal=1';
//Find the correct amount so that unsavory characters don't change it in the previous form

		$description = stripslashes_deep($payment_data['event_name']);

		if ($iDEAL->createPayment($_POST['bank_id'], $amount, $description, $return_url, $report_url)) {
			header("Location: " . $iDEAL->getBankURL());
			exit;
		} else {
			echo '<p>De betaling kon niet aangemaakt worden.</p>';

			echo '<p><strong>Foutmelding:</strong> ', $iDEAL->getErrorMessage(), '</p>';
		}
	} elseif (isset($_POST['bank_id']) && $_POST['bank_id'] == '') {
		echo "<p>" . __("Please use your browser's back button and select a bank.", 'event_espresso');
	}

	$bank_array = $iDEAL->getBanks();

	if ($bank_array == false) {
		echo '<p>Er is een fout opgetreden bij het ophalen van de banklijst: ', $iDEAL->getErrorMessage(), '</p>';
	}
	?>
	<div class="event-display-boxes">
		<form id="ideal-mollie-form" class="ee-forms" method="post" action="<?php echo $home . '/?page_id=' . $org_options['notify_url']; ?>">
			<select id ="bank_id" name="bank_id" class="required">
				<option value=''>Kies uw bank</option>

				<?php foreach ($bank_array as $bank_id => $bank_name) { ?>
					<option value="<?php echo $bank_id ?>"><?php echo $bank_name ?></option>
				<?php } ?>

			</select>
			<input name="amount" type="hidden" value="<?php echo $amount; ?>" />
			<input name="ideal" type="hidden" value="1" />
			<input name="id" type="hidden" value="<?php echo $payment_data['attendee_id']; ?>" />
			<input id="submit_ideal" type="submit" class="btn_event_form_submit payment-submit" name="submit" value="Betaal via iDEAL" />
		</form>
	</div>
	<?php
}


