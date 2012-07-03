<?php

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
	global $wpdb, $active_gateways;
	$active_gateways = get_option('event_espresso_active_gateways', array());
	echo before_gateways();

	$gateways_glob = glob(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/*/settings.php");
	$upload_gateways_glob = glob(EVENT_ESPRESSO_GATEWAY_DIR . '*/settings.php');
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
