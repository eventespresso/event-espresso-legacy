<?php

function espresso_display_usaepay_offsite($payment_data) {
	global $org_options;
	extract($payment_data);
	$settings = get_option('espresso_usaepay_offsite_settings');
?>
<div id="usaepay_offsite-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
	<img class="off-site-payment-gateway-img" width="16" height="16" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL;?>/images/icons/external-link.png" alt="click to visit this payment gateway">
<?php	
	echo '<form  method="post" name="payment_form" action="https://sandbox.usaepay.com/interface/epayform/">';
	echo "<input type=\"hidden\" name=\"UMkey\" value=\"" . $settings['key'] . "\"/>\n";
	echo "<input type=\"hidden\" name=\"UMamount\" value=\"" . $event_cost . "\"/>\n";
	echo "<input type=\"hidden\" name=\"UMinvoice\" value=\"" . $attendee_id . "\"/>\n";
	echo "<input type=\"hidden\" name=\"UMredirApproved\" value=\"" . 
				add_query_arg(array('id'=>$attendee_id,'r_id'=>$registration_id,'event_id'=>$event_id,'attendee_action'=>'post_payment','form_action'=>'payment','type'=>'usaepay_offsite'),  get_permalink($org_options['return_url'])) . "\"/>\n";
	echo '<input id="usaepay_offsite-payment-option-lnk" class="payment-option-lnk allow-leave-page" type="image" alt="Pay using USAePay" src="' . $settings['button_url'] . '" />';
	echo '</form>';
?>
</div>
<?php	
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_usaepay_offsite');
