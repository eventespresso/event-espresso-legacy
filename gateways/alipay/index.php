<?php

function espresso_display_alipay($payment_data) {
	extract($payment_data);
	global $org_options;
	$alipay_settings = get_option('event_espresso_alipay_settings');
	require_once("alipay_service.php");
	require_once("alipay_config.php");
	$parameter = array(
			"service" => "create_forex_trade", //this is the service name
			"partner" => $partner,
			"return_url" => $return_url,
			"notify_url" => $notify_url,
			"_input_charset" => $_input_charset,
			"subject" => stripslashes_deep($event_name), //subject is the name of the product, you'd better change it
			"body" => stripslashes_deep($event_name), //body is the description of the product , you'd beeter change it
			"out_trade_no" => time(),
			"total_fee" => '0.01', //number_format( $event_cost, 2 ), //the price of products
			"currency" => "USD", // change it as the currency which you used on your website
	);
	$alipay = new Espresso_Alipay_Service($parameter, $security_code, $sign_type);
//echo "<pre>", print_r( $parameter ), "</pre>";
	$link = $alipay->create_url();

	$link_anchor = isset($alipay_settings['button_url']) ? "<img src='" . $alipay_settings['button_url'] . "' alt='" . __('Pay using Alipay', 'event espresso') . "' />" : __('Pay using Alipay', 'event espresso');

	$ee_images_url = EVENT_ESPRESSO_PLUGINFULLURL;
	print <<<EOT
		 <div id="alipay-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
			<img class="off-site-payment-gateway-img" width="16" height="16" src="$ee_images_url/images/icons/external-link.png" alt="click to visit this payment gateway">
			<a class="payment-option-lnk" href= $link  target= "">$link_anchor</a>
		</div>
EOT;
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_alipay');
