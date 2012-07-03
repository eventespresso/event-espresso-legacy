<?php

function espresso_display_invoice($payment_data) {
	extract($payment_data);
// Setup payment page
	$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');
	if ($invoice_payment_settings['show'] == 'N')
		return;
	if (isset($default_gateway_version))
		echo '<!--Event Espresso Default Gateway Version ' . $default_gateway_version . '-->';
	if (isset($invoice_payment_settings['invoice_title'])) {
		?>
		<div class="event-display-boxes">
			<?php
			echo '<h4 id="invoice_title" class="payment_type_title section-heading">' . stripslashes_deep($invoice_payment_settings['invoice_title']) . '</h4>';
		}
		?>
		<p><a href="<?php echo home_url(); ?>/?invoice_type=<?php echo empty($invoice_type) ? '' : $invoice_type; ?>&amp;download_invoice=true&amp;attendee_id=<?php echo $attendee_id; ?>&amp;registration_id=<?php echo $registration_id ?>" class="inline-link" id="invoice_download_link"><?php _e('Download PDF Invoice', 'event_espresso'); ?></a></p>
		<?php
		if (isset($invoice_payment_settings['invoice_instructions'])) {
			echo '<p class="instruct">' . stripslashes_deep($invoice_payment_settings['invoice_instructions']) . '</p>';
		}
		if (isset($invoice_payment_settings['payment_address'])) {
			?>
			<div class="address-block">

		<?php echo wpautop(stripslashes_deep($invoice_payment_settings['payment_address'])); ?>

			</div>
		</div>
		<?php
	}
}

add_action('action_hook_espresso_display_offline_payment_gateway', 'espresso_display_invoice');
