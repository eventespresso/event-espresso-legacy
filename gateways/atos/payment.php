<?php
if (!defined('DS')) {
	define( 'DS', DIRECTORY_SEPARATOR );
}
function espresso_display_atos($payment_data) {
	extract($payment_data);
	global $org_options;
	$settings = get_option('event_espresso_atos_settings');
	$parm = "merchant_id=".$settings['merchant_id'];
	$parm .= " merchant_country=".$settings['merchant_country'];
	$parm .= " amount=";
	if ( ! in_array($settings['currency_code'], array('392','484','953','952')) ) {
		$event_cost = $event_cost * 100;
	}
	$parm .= number_format($event_cost, 0, '', '');
	$parm .= " currency_code=".$settings['currency_code'];
	$parm .= " pathfile=".dirname(__FILE__).DS.$settings['provider'].DS.'pathfile';
	$normal_return_url = home_url() . '/?page_id=' . $org_options['return_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=atos';
	$parm .= " normal_return_url=".$normal_return_url;
	$cancel_return_url = home_url() . '/?page_id=' . $org_options['cancel_return'];
	$parm .= " cancel_return_url=".$cancel_return_url;
	$automatic_response_url = home_url() . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=atos';
	$parm .= " automatic_response_url=".$automatic_response_url;
	$parm .= " language=".$settings['language'];
	$payment_means = '';
	foreach ($settings['payment_means'] as $card=>$block) {
		if ( !$block ) continue;
		$payment_means .= $card.','.$block.',';
	}
	$payment_means = rtrim($payment_means,',');
	$parm .= " payment_means=".$payment_means;
	$path_bin = dirname(__FILE__).DS.'bin'.DS.'request';
	$parm = escapeshellcmd($parm);
	$result = exec("$path_bin $parm");
	$tableau = explode ("!", "$result");
	$code = isset($tableau[1]) ? $tableau[1] : '';
	$error = isset($tableau[2]) ? $tableau[2] : '';
	$message = isset($tableau[3]) ? $tableau[3] : '';
	if (($code == "") && ($error == "")) {
		printf(__('ATOS Payment Gateway improperly configured. Cannot execute file %1$s. Does it exist and is it executable?', "event_espresso"), $path_bin);
	} elseif ($code != 0) {
		printf(__('ATOS error! %1$s', "event_espresso"),$error);
	} else {
		echo "$message<br>";
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_atos');