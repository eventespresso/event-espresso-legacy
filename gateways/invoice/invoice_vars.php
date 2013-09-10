<?php

function espresso_display_invoice($payment_data) {
	global $org_options;
	extract($payment_data);
// Setup payment page
	$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');
	
	if ($invoice_payment_settings['show'] == 'N') {
		return;
	}
		
	if (isset($default_gateway_version)) {
		echo '<!--Event Espresso Default Gateway Version ' . $default_gateway_version . '-->';
	}

	$args = array(
		'page_id' =>$org_options['return_url'],
		'r_id' =>$registration_id,
		'id' =>$attendee_id,
		'payment_type' => 'invoice',
		'type' => 'invoice',
	);
	$finalize_link = add_query_arg( $args, home_url() );	

?>
<div id="invoice-payment-option-dv" class="payment-option-dv">

	<a id="invoice-payment-option-lnk" class="payment-option-lnk algn-vrt display-the-hidden" rel="invoice-payment-option-form" style="display: table">
		<div class="vrt-cell">
			<div>
				<?php
					if ( isset( $invoice_payment_settings['invoice_title'] )) {
						echo stripslashes( $invoice_payment_settings['invoice_title'] );
					}
				?>
			</div>
		</div>
	</a>
	<br/>

	<div id="invoice-payment-option-form-dv" class="hide-if-js">		
		<div class="event-display-boxes">
			<h4 id="invoice_title" class="payment_type_title section-heading">
				<?php
					if ( isset( $invoice_payment_settings['invoice_title'] )) {
						echo stripslashes( $invoice_payment_settings['invoice_title'] );
					}
				?>
			</h4>
			<p class="instruct">
				<?php echo wpautop( stripslashes_deep($invoice_payment_settings['invoice_instructions'] )); ?>		
			</p>
		
			<p>
				<a href="<?php echo home_url(); ?>/?invoice_type=<?php echo empty($invoice_type) ? '' : $invoice_type; ?>&amp;download_invoice=true&amp;attendee_id=<?php echo $attendee_id; ?>&amp;r_id=<?php echo $registration_id ?>" class="inline-link allow-leave-page" id="invoice_download_link"><?php _e('Download PDF Invoice', 'event_espresso'); ?></a>
			</p>
		
		<?php if ( isset( $invoice_payment_settings['payment_address'] )) { ?>
			<p>
				<span class="section-title"><?php _e('Mailing Address:', 'event_espresso'); ?></span><br/>
				<span class="highlight address-block">
					<strong><?php echo stripslashes_deep( ! empty($invoice_payment_settings['payment_address']) ? $invoice_payment_settings['payment_address'] : '' ); ?></strong>
				</span>				
			</p>
		<?php } ?>	
			
		</div>
		<br/>
		<div class="event_espresso_attention event-messages ui-state-highlight">
			<span class="ui-icon ui-icon-alert"></span>
			<p>
				<strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
				<?php _e('If you wish to pay by invoice, then please make note of the information above, and click to ', 'event_espresso'); ?>
				<a class="finalize_button allow-leave-page inline-link" href="<?php echo $finalize_link; ?>" title="<?php _e('Complete your Registration', 'event_espresso'); ?>">
					<?php _e('Complete your Registration', 'event_espresso'); ?>				
				</a>
				<div class="clear"></div>
			</p>
		</div>	
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="invoice-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>		
<?php		
}

add_action('action_hook_espresso_display_offline_payment_gateway', 'espresso_display_invoice');
