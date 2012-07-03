<?php
function espresso_help_popup($name){
	return  '<a class="thickbox" href="#TB_inline?height=400&amp;width=500&amp;inlineId=' . $name . '" target="_blank"><span class="question">[?]</span></a>';
}
add_filter('espresso_help', 'espresso_help_popup');

function before_gateways() {
	$before_gateways = '<div id="event_reg_theme" class="wrap"><div id="icon-options-event" class="icon32"></div>';
	$before_gateways .= '<h2>' . __('Manage Payment Gateways', 'event_espresso') . '</h2>';
	$before_gateways .= '<div id="poststuff" class="metabox-holder has-right-sidebar">';
	$before_gateways .= event_espresso_get_right_column();
	$before_gateways .= '<div id="post-body"><div id="post-body-content"><div class="meta-box-sortables ui-sortables">';
	return $before_gateways;
}

function after_gateways() {
	$output = '';
	global $espresso_premium;
	if ($espresso_premium != true)
		$output .= '<h2>' . __('Need more payment options?', 'event_espresso') . ' <a href="http://eventespresso.com/download/" target="_blank">' . __('Upgrade Now!', 'event_espresso') . '</a></h2>';
	$output .= '</div><!-- / .meta-box-sortables --></div><!-- / #post-body-content --></div><!-- / #post-body --></div><!-- / #poststuff --></div><!-- / #wrap -->';
	$output .= '<div id="button_image" style="display:none"><h2>' . __('Button Image URL', 'event_espresso') . '</h2>';
	$output .= '<p>' . __('A default payment button is provided. A custom payment button may be used, choose your image or upload a new one, and just copy the "file url" here (optional.)', 'event_espresso') . '</p>';
	$output .= '</div><div id="bypass_confirmation" style="display:none">';
	$output .= '<h2>' . __('By-passing the Confirmation Page', 'event_espresso') . '</h2>';
	$output .= '<p>' . __('This will allow you to send your customers directly to the payment gateway of your choice.', 'event_espresso') . '</p></div>';
	$output .= '<script type="text/javascript" charset="utf-8">
        //<![CDATA[
         jQuery(document).ready(function() {
          postboxes.add_postbox_toggles("payment_gateways");
          });
        //]]>
        </script>';
	return $output;
}

//This is the payment gateway settings page.
function event_espresso_gateways_options() {
	global $active_gateways;
	$active_gateways = get_option('event_espresso_active_gateways', array());
	echo before_gateways();

	$gateways_glob = glob(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/*/settings.php");
	$upload_gateways_glob = glob(EVENT_ESPRESSO_GATEWAY_DIR . '*/settings.php');
	if(!is_array($upload_gateways_glob)) $upload_gateways_glob = array();
	foreach ($upload_gateways_glob as $upload_gateway) {
		$pos = strpos($upload_gateway, 'gateways');
		$sub = substr($upload_gateway, $pos);
		foreach ($gateways_glob as &$gateway) {
			$pos2 = strpos($gateway, 'gateways');
			$sub2 = substr($gateway, $pos2);
			if ($sub == $sub2) {
				$gateway = $upload_gateway;
			}
		}
		unset($gateway);
	}
	$gateways = array_merge($upload_gateways_glob, $gateways_glob);
	$gateways = array_unique($gateways);

	foreach ($gateways as $gateway) {
		require_once($gateway);
	}

	do_action('action_hook_espresso_display_gateway_settings');

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/gateway_developer.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/gateway_developer.php');
	}
	echo after_gateways();
}

function espresso_update_active_gateways() {
  //upgrade script for those updating from versions prior to 3.1.16.P
	//hooked to plugin activation
	$twocheckout_settings = get_option('event_espresso_2checkout_settings');
	if (!empty($twocheckout_settings) && strpos($twocheckout_settings['button_url'], "/2checkout/logo.png")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/2checkout/logo.png")) {
			$twocheckout_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/2checkout/logo.png";
		} else {
			$twocheckout_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/2checkout/logo.png";
		}
		update_option('event_espresso_2checkout_settings', $twocheckout_settings);
	}
	$alipay_settings = get_option('event_espresso_alipay_settings');
	if (!empty($alipay_settings) && strpos($alipay_settings['button_url'], "/alipay/new_logo.jpg")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/alipay/new_logo.jpg")) {
			$alipay_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/alipay/new_logo.jpg";
		} else {
			$alipay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/alipay/new_logo.jpg";
		}
		update_option('event_espresso_alipay_settings', $alipay_settings);
	}
	$authnet_settings = get_option('event_espresso_authnet_settings');
	if (!empty($authnet_settings) && strpos($authnet_settings['button_url'], "/authnet/btn_cc_vmad.gif")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/btn_cc_vmad.gif")) {
			$authnet_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/authnet/btn_cc_vmad.gif";
		} else {
			$authnet_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/authnet/btn_cc_vmad.gif";
		}
		update_option('event_espresso_authnet_settings', $authnet_settings);
	}
	$eway_settings = get_option('event_espresso_eway_settings');
	if (!empty($eway_settings) && strpos($eway_settings['button_url'], "/eway/eway_logo.png")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/eway/eway_logo.png")) {
			$eway_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/eway/eway_logo.png";
		} else {
			$eway_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/eway/eway_logo.png";
		}
		update_option('event_espresso_eway_settings', $eway_settings);
	}

	$exact_settings = get_option('event_espresso_exact_settings');
	if (!empty($exact_settings) && strpos($exact_settings['button_url'], "/exact/btn_cc_vmad.gif")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/exact/btn_cc_vmad.gif")) {
			$exact_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/exact/btn_cc_vmad.gif";
		} else {
			$exact_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/exact/btn_cc_vmad.gif";
		}
		update_option('event_espresso_exact_settings', $exact_settings);
	}

	$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
	if (!empty($firstdata_connect_2_settings) && strpos($firstdata_connect_2_settings['button_url'], "/firstdata_connect_2/standard_button.gif")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata_connect_2/standard_button.gif")) {
			$firstdata_connect_2_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/firstdata_connect_2/standard_button.gif";
		} else {
			$firstdata_connect_2_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/firstdata_connect_2/standard_button.gif";
		}
		update_option('event_espresso_firstdata_connect_2_settings', $firstdata_connect_2_settings);
	}
	$mwarrior_settings = get_option('event_espresso_mwarrior_settings');
	if (!empty($mwarrior_settings) && strpos($mwarrior_settings['button_url'], "/mwarrior/btn_checkout.png")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/mwarrior/btn_checkout.png")) {
			$mwarrior_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/mwarrior/btn_checkout.png";
		} else {
			$mwarrior_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/mwarrior/btn_checkout.png";
		}
		update_option('event_espresso_mwarrior_settings', $mwarrior_settings);
	}
	$paypal_settings = get_option('event_espresso_paypal_settings');
	if (!empty($paypal_settings) && strpos($paypal_settings['button_url'], "/paypal/btn_stdCheckout2.gif")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/btn_stdCheckout2.gif")) {
			$paypal_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/paypal/btn_stdCheckout2.gif";
		} else {
			$paypal_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/paypal/btn_stdCheckout2.gif";
		}
		update_option('event_espresso_paypal_settings', $paypal_settings);
	}
	$realauth_settings = get_option('event_espresso_realauth_settings');
	if (!empty($realauth_settings) && strpos($realauth_settings['button_url'], "/realauth/logo.gif")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/realauth/logo.gif")) {
			$realauth_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/realauth/logo.gif";
		} else {
			$realauth_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/realauth/logo.gif";
		}
		update_option('event_espresso_realauth_settings', $realauth_settings);
	}
	$wepay_settings = get_option('event_espresso_wepay_settings');
	if (!empty($wepay_settings) && strpos($wepay_settings['button_url'], "/wepay/logo.png")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/wepay/logo.png")) {
			$wepay_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/wepay/logo.png";
		} else {
			$wepay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/wepay/logo.png";
		}
		update_option('event_espresso_wepay_settings', $wepay_settings);
	}
	$worldpay_settings = get_option('event_espresso_worldpay_settings');
	if (!empty($worldpay_settings) && strpos($worldpay_settings['button_url'], "/worldpay/WorldPaylogoBluetrans.png")) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/worldpay/WorldPaylogoBluetrans.png")) {
			$worldpay_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/worldpay/WorldPaylogoBluetrans.png";
		} else {
			$worldpay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/worldpay/WorldPaylogoBluetrans.png";
		}
		update_option('event_espresso_worldpay_settings', $worldpay_settings);
	}
	$active_gateways = get_option('event_espresso_active_gateways', array());
	$dir = dirname(__FILE__);
	if(get_option('events_2checkout_active')==true
					|| array_key_exists('2checkout', $active_gateways)) {
		$active_gateways['2checkout'] = $dir . "/2checkout";
	}
	if(get_option('events_authnet_aim_active')==true
					|| array_key_exists('aim', $active_gateways)) {
		$active_gateways['aim'] = $dir . "/aim";
	}
	if(get_option('events_alipay_active')==true
					|| array_key_exists('alipay', $active_gateways)) {
		$active_gateways['alipay'] = $dir . "/alipay";
	}
	if(get_option('events_authnet_active')==true
					|| array_key_exists('authnet', $active_gateways)) {
		$active_gateways['authnet'] = $dir . "/authnet";
	}
	if(get_option('events_bank_payment_active')==true
					|| array_key_exists('bank', $active_gateways)) {
		$active_gateways['bank'] = $dir . "/bank";
	}
	if(get_option('events_check_payment_active')==true
					|| array_key_exists('check', $active_gateways)) {
		$active_gateways['check'] = $dir . "/check";
	}
	if(get_option('events_eway_active')==true
					|| array_key_exists('eway', $active_gateways)) {
		$active_gateways['eway'] = $dir . "/eway";
	}
	if(get_option('events_exact_active')==true
					|| array_key_exists('exact', $active_gateways)) {
		$active_gateways['exact'] = $dir . "/exact";
	}
	if(get_option('events_firstdata_active')==true
					|| array_key_exists('firstdata', $active_gateways)) {
		$active_gateways['firstdata'] = $dir . "/firstdata";
	}
	if(get_option('events_firstdata_connect_2_active')==true
					|| array_key_exists('firstdata_connect_2', $active_gateways)) {
		$active_gateways['firstdata_connect_2'] = $dir . "/firstdata_connect_2";
	}
	if(get_option('events_ideal_active')==true
					|| array_key_exists('ideal', $active_gateways)) {
		$active_gateways['ideal'] = $dir . "/ideal";
	}
	if(get_option('events_invoice_payment_active')==true
					|| array_key_exists('invoice', $active_gateways)) {
		$active_gateways['invoice'] = $dir . "/invoice";
	}
	if(get_option('events_mwarrior_active')==true
					|| array_key_exists('mwarrior', $active_gateways)) {
		$active_gateways['mwarrior'] = $dir . "/mwarrior";
	}
	if(get_option('events_nab_active')==true
					|| array_key_exists('nab', $active_gateways)) {
		$active_gateways['nab'] = $dir . "/nab";
	}
	if(get_option('events_paypal_active')==true
					|| array_key_exists('paypal', $active_gateways)) {
		$active_gateways['paypal'] = $dir . "/paypal";
	}
	if(get_option('events_paypal_pro_active')==true
					|| array_key_exists('paypal_pro', $active_gateways)) {
		$active_gateways['paypal_pro'] = $dir . "/paypal_pro";
	}
	if(get_option('events_paytrace_active')==true
					|| array_key_exists('paytrace', $active_gateways)) {
		$active_gateways['paytrace'] = $dir . "/paytrace";
	}
	if(get_option('events_quickpay_active')==true
					|| array_key_exists('quickpay', $active_gateways)) {
		$active_gateways['quickpay'] = $dir . "/quickpay";
	}
	$payment_settings = get_option('event_espresso_realauth_settings');
	if(!empty($payment_settings['active'])
					|| array_key_exists('realauth', $active_gateways)) {
		$active_gateways['realauth'] = $dir . "/realauth";
	}
	if(get_option('events_stripe_active')==true
					|| array_key_exists('stripe', $active_gateways)) {
		$active_gateways['stripe'] = $dir . "/stripe";
	}
	if(get_option('events_worldpay_active')==true
					|| array_key_exists('worldpay', $active_gateways)) {
		$active_gateways['worldpay'] = $dir . "/worldpay";
	}
	update_option('event_espresso_active_gateways', $active_gateways);
}