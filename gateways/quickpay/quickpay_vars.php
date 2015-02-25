<?php

function espresso_display_quickpay($payment_data) {
	//echo '<h3>'. __CLASS__ . '->' . __FUNCTION__ . ' <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';
	extract($payment_data);
	global $wpdb, $org_options;
	$quickpay_settings = get_option('event_espresso_quickpay_settings');
	$sessionid = $_SESSION['espresso_session']['id'];
	$ordernumber = $registration_id;
	// Set the transaction id to a unique value for reference in the system.
	$transaction_id = uniqid(md5(rand(1, 666)), true); 
	$button_url = $quickpay_settings['button_url'];
	$md5secret = $quickpay_settings['quickpay_md5secret'];
	$payurl = "https://secure.quickpay.dk/form/";
	$protocol = '7';
	$msgtype = 'authorize';
	$merchant = $quickpay_settings['quickpay_merchantid'];
	$language = $quickpay_settings['quickpay_language'];
	$amount = 0.00;
	if (isset($attendee_id) && is_numeric($attendee_id) && $attendee_id > 0) {
		$tmp_row = $wpdb->get_row("select registration_id from " . EVENTS_ATTENDEE_TABLE . " where id = $attendee_id");
		if ($tmp_row !== NULL) {
			$tmp_registration_id = $tmp_row->registration_id;
			$tmp_row = $wpdb->get_row("select * from " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " where registration_id = '{$tmp_registration_id}' ");
			if ($tmp_row !== NULL) {
				$primary_registration_id = $tmp_row->primary_registration_id;
				$multi_reg = true;
			} else {
				$primary_registration_id = $tmp_registration_id;
			}
		}
	}
	

	if ($attendee_id > 0 && !empty($primary_registration_id) && strlen($primary_registration_id) > 0) {
		$registration_ids = array();
		$rs = $wpdb->get_results("select * from " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " where primary_registration_id = '{$primary_registration_id}' ");
		if ($wpdb->num_rows > 0) {
			foreach ($rs as $row) {
				$registration_ids[] = $row->registration_id;
			}
		} else {
			$registration_ids[] = $primary_registration_id;
		}
		foreach ($registration_ids as $other_attendee_registration_id) {
			$sql = "select ea.registration_id, ea.id as attendee_id, ea.amount_pd, ed.id as event_id, ";
			$sql .= " ed.event_name, ed.start_date, ea.fname, ea.lname, ea.quantity, ea.final_price from " . EVENTS_ATTENDEE_TABLE . " ea ";
			//$sql .= " inner join " . EVENTS_ATTENDEE_COST_TABLE. " eac on ea.id = eac.attendee_id ";
			$sql .= " inner join " . EVENTS_DETAIL_TABLE. " ed on ea.event_id = ed.id ";
			$sql .= " where ea.registration_id = '" . $other_attendee_registration_id . "' order by ed.event_name ";
			$tmp_attendees = $wpdb->get_results($sql, ARRAY_A);
			foreach ($tmp_attendees as $tmp_attendee) {
				$amount += $tmp_attendee["final_price"] * $tmp_attendee["quantity"];
			}
		}
	}
	
	$amount = number_format($amount, 2, '', '');
	$currency = $quickpay_settings['quickpay_currency'];

	if ($quickpay_settings['force_ssl_return']) {
		$home = str_replace('http://', 'https://', home_url());
	} else {
		$home = home_url();
	}
	$transact_url = $home . '/?page_id=' . $org_options['return_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&attendee_action=post_payment&form_action=payment&type=quickpay';
	$params = array('chronopay_callback' => 'true', 'transaction_id' => $transaction_id, 'sessionid' => $sessionid);
	$continueurl = add_query_arg($params, $transact_url);

	$transact_url = $home . '/?page_id=' . $org_options['cancel_return'];
	$params = array('chronopay_callback' => 'cancel', 'transaction_id' => $transaction_id, 'sessionid' => $sessionid);
	$cancelurl = add_query_arg($params, $transact_url);

	$transact_url = $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&attendee_action=post_payment&form_action=payment&type=quickpay';
	$params = array('chronopay_callback' => 'true', 'transaction_id' => $transaction_id, 'sessionid' => $sessionid);
	$callbackurl = add_query_arg($params, $transact_url);

	$autocapture = $quickpay_settings['quickpay_autocapture'];
	$cardtypelock = 'creditcard';
	$sandbox = ($quickpay_settings['use_sandbox']) ? '1' : '';
	$md5check = md5($protocol . $msgtype . $merchant . $language . $ordernumber . $amount . $currency . $continueurl . $cancelurl . $callbackurl . $autocapture . $cardtypelock . $sandbox . $md5secret);
	?>
 <div id="quickpay-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
	<img class="off-site-payment-gateway-img" width="16" height="16" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL;?>/images/icons/external-link.png" alt="click to visit this payment gateway">
	<form id="quickpay_form" name="quickpay_form" action="<?php echo $payurl; ?>" method="post">
		<input type="hidden" name="protocol" value="<?php echo $protocol; ?>" />
		<input type="hidden" name="msgtype" value="<?php echo $msgtype; ?>" />
		<input type="hidden" name="merchant" value="<?php echo $merchant; ?>" />
		<input type="hidden" name="language" value="<?php echo $language; ?>" />
		<input type="hidden" name="ordernumber" value="<?php echo $ordernumber; ?>" />
		<input type="hidden" name="amount" value="<?php echo $amount; ?>" />
		<input type="hidden" name="currency" value="<?php echo $currency; ?>" />
		<input type="hidden" name="continueurl" value="<?php echo $continueurl; ?>" />
		<input type="hidden" name="cancelurl" value="<?php echo $cancelurl; ?>" />
		<input type="hidden" name="callbackurl" value="<?php echo $callbackurl; ?>" />
		<input type="hidden" name="autocapture" value="<?php echo $autocapture; ?>" />
		<input type="hidden" name="cardtypelock" value="<?php echo $cardtypelock; ?>" />
		<?php if ($quickpay_settings['use_sandbox']) { ?><input type="hidden" name="testmode" value="1" /><?php } ?>
		<input type="hidden" name="md5check" value="<?php echo $md5check; ?>" />
		<input id="quickpay-payment-option-lnk" class="payment-option-lnk allow-leave-page" value="Payvalue" type="image" alt="Pay using QuickPay" src="<?php echo $button_url; ?>" />
	</form>
</div>
	<?php
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_quickpay');