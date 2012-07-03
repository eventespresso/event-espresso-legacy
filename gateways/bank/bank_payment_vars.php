<?php
// Setup class
	echo '<!-- Event Espresso Electronic Funds Transfer Gateway Version ' . $bank_gateway_version . ' -->';
			
	$bank_deposit_settings = get_option('event_espresso_bank_deposit_settings');
?>
<hr />
<h3><?php echo $bank_deposit_settings['bank_title'] ?></h3>
<p><?php echo wpautop($bank_deposit_settings['bank_instructions']) ?></p>
<p><strong><?php _e('Name on Account:', 'event_espresso'); ?></strong> <?php echo $bank_deposit_settings['account_name']; ?></p>
<p><strong><?php _e('Account Number:', 'event_espresso'); ?></strong> <?php echo $bank_deposit_settings['bank_account']; ?></p>
<p><strong><?php _e('Financial Institution:', 'event_espresso'); ?></strong> <?php echo $bank_deposit_settings['bank_name'] ?>
<p><strong><?php _e('Address:', 'event_espresso'); ?></strong><br />
<?php echo wpautop($bank_deposit_settings['bank_address']); ?></p>