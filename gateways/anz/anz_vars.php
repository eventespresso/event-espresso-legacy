<?php
function espresso_display_anz($payment_data){
	global $wpdb;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'ANZ';
	
	//$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	
	global $wpdb, $org_options;
	$anz_settings = get_option('event_espresso_anz_settings');
	$merchant_id=$anz_settings['anz_id'];
	$access_code=$anz_settings['anz_access_code'];
	$secure_secret=$anz_settings['anz_secure_secret'];
	$bypass_payment_page = ($anz_settings['bypass_payment_page'] == 'Y')?true:false;
	$button_url = $anz_settings['button_url'];
	
	if ($anz_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	$return_url= $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $payment_data['registration_id']. '&type=anz';
	$server_url="https://migs.mastercard.com.au/vpcpay";
	
	if (empty($anz_settings['button_url'])) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/anz/anz.gif")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_DIR . "/anz/anz.gif";
		}
	} elseif (isset($anz_settings['button_url'])) {
		$button_url = $anz_settings['button_url'];
	} else {
		$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/anz/anz.gif";
	}
	if(!empty($button_url)){
		$submit_html="<input type='image' src='$button_url'/>";
	}else{
		$submit_html="<button>Submit Purchase</button>";
	}
	if($bypass_payment_page){
		$bypass_payment_page_js="<script>document.getElementById('anz_form').submit();</script>";
	}else{
		$bypass_payment_page_js="";
	}
	?>
<form action="<?php echo $server_url?>" method="get">
	<input type="text" name="vpc_Version" value="1" size="20" maxlength="8">
	<input type="text" name="vpc_Command" value="pay" size="20" maxlength="16">
	<input type="text" name="vpc_AccessCode" value="<?php echo $access_code?>" size="20" maxlength="8">
	<input type="text" name="vpc_MerchTxnRef" value="" size="20" maxlength="40">
	<input type="text" name="vpc_Merchant" value="<?php echo $merchant_id?>" size="20" maxlength="16">
	<input type="text" name="vpc_OrderInfo" value="VPC Example" size="20" maxlength="34">
	<input type="text" name="vpc_Amount" value="100" size="<?php echo $payment_data['total_cost']?>" maxlength="10">
	<input type="text" name="vpc_Locale" value="en" size="20" maxlength="5">
	<input type="text" name="vpc_ReturnURL" size="63" value="<?php echo $return_url?>" maxlength="250">
	<?php echo $submit_html?>
</form>
	<?php echo $bypass_payment_page_js;?>
<?php



	return $payment_data;
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_anz');

