<?php
//This file builds the gateways that are available
if (get_option('events_paypal_active') == 'true' || get_option('events_authnet_active') == 'true' || get_option('events_authnet_aim_active') == 'true' || get_option('events_alipay_active') == 'true'){
	echo '<hr /><h3>'.__('Online Payments', 'event_espresso').'</h3>';
}
if (get_option('events_paypal_pro_active') == 'true'){
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal-pro/paypal_pro_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal-pro/paypal_pro_vars.php");
	}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal-pro/paypal_pro_vars.php")){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal-pro/paypal_pro_vars.php");
	}
}
if (get_option('events_authnet_aim_active') == 'true'){
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/aim/aim_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/aim/aim_vars.php");
	}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/aim/aim_vars.php")){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/aim/aim_vars.php");
	}
}
if (get_option('events_firstdata_active') == 'true'){
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata/firstdata_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata/firstdata_vars.php");
	}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/firstdata/firstdata_vars.php")){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/firstdata/firstdata_vars.php");
	}
}
if (get_option('events_ideal_active') == 'true'){
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/ideal/ideal_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/ideal/ideal_vars.php");
	}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/ideal/ideal_vars.php")){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/ideal/ideal_vars.php");
	}
}
echo '<table id="espresso_payment_buttons" id="espresso_payment_buttons" width="95%">';
echo '<tr>';
if (get_option('events_paypal_active') == 'true'){
	echo '<td>';
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/paypal_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/paypal_vars.php");
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal/paypal_vars.php");
	}
	echo '</td>';
}
if (get_option('events_authnet_active') == 'true'){
	echo '<td>';
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/authnet_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/authnet_vars.php");
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/authnet/authnet_vars.php");
	}
	echo '</td>';
}


if ((file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/alipay/alipay_active.php") || file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/alipay/alipay_active.php")) && get_option('events_alipay_active') == 'true'){
        global $org_options;
        $alipay_settings = get_option('event_espresso_alipay_settings');
	echo '<td>';
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/alipay/index.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/alipay/index.php");
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/alipay/index.php");
	}
	echo '</td>';
}

if (get_option('events_plugnpay_active') == 'true'){
	echo '<td>';
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/plugnpay/plugnpay_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/plugnpay/plugnpay_vars.php");
	}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/plugnpay/plugnpay_vars.php")){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/plugnpay/plugnpay_vars.php");
	}
	echo '</td>';
}
if (get_option('events_twoco_active') == 'true'){
	echo '<td>';
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/twoco_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/twoco_vars.php");
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/twoco/twoco_vars.php");
	}
	echo '</td>';
}
echo '</tr>';
echo '</table>';
if (get_option('events_bank_payment_active') == 'true'||get_option('events_check_payment_active') == 'true' || get_option('events_invoice_payment_active') == 'true'){
	echo '<hr /><h3>'.__('Off-line Payments', 'event_espresso').'</h3>';
}
if (get_option('events_invoice_payment_active') == 'true'){
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/invoice_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/invoice_vars.php");
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/invoice/invoice_vars.php");
	}
}
if (get_option('events_bank_payment_active') == 'true'||get_option('events_check_payment_active') == 'true'){
?>
	<div class="event_espresso_attention"><strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
<?php _e('If paying with cash or check, please make note of the information below, then', 'event_espresso'); ?>
		<a href="<?php echo home_url(). '/?page_id='.$org_options['return_url'] ;?>&amp;payment_type=cash_check&amp;registration_id=<?php echo $registration_id ?>" title="<?php _e('Finalize your registration', 'event_espresso'); ?>"><?php _e('click here to finalize your registration', 'event_espresso'); ?></a></div>
<?php        
}
				
if (get_option('events_check_payment_active') == 'true'){
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/check/check_payment_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/check/check_payment_vars.php");
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/check/check_payment_vars.php");
	}
}
					
if (get_option('events_bank_payment_active') == 'true'){
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/bank_payment_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/bank_payment_vars.php");
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/bank/bank_payment_vars.php");
	}
}