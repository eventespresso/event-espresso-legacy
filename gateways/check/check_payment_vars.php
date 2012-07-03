<?php
// Setup class
	echo '<!--Advanced Events Registration Default Gateway Version ' . $default_gateway_version . '-->';
			
	$check_payment_settings = get_option('event_espresso_check_payment_settings');
?>
<hr />
<h3><?php echo $check_payment_settings['check_title'] ?></h3>
<p><?php echo wpautop($check_payment_settings['check_instructions']) ?></p>
<p><strong><?php _e('Payable to:', 'event_espresso'); ?></strong> <?php echo $check_payment_settings['payable_to'] ?></p>
<p><strong><?php _e('Payment Address:', 'event_espresso'); ?></strong><br />
<?php echo wpautop($check_payment_settings['payment_address']); ?></p>