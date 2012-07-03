<?php
echo '<table id="payment_butons" width="95%">';
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
					
if (get_option('events_invoice_payment_active') == 'true'){
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/invoice_vars.php")){
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/invoice_vars.php");
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/invoice/invoice_vars.php");
	}
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