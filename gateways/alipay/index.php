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
	$alipay = new alipay_service($parameter, $security_code, $sign_type);
//echo "<pre>", print_r( $parameter ), "</pre>";
	$link = $alipay->create_url();

	$link_anchor = isset($alipay_settings['button_url']) ? "<li><img src='" . $alipay_settings['button_url'] . "' alt='" . __('Pay using Alipay', 'event espresso') . "' />" : __('Pay using Alipay', 'event espresso');


	print <<<EOT
<br/>
<a href= $link  target= "">$link_anchor</a></li>
EOT;
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_alipay');
