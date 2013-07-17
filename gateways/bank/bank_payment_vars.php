<?php

function espresso_display_bank($payment_data) {
	global $org_options;
	extract($payment_data);
// Setup class
	$bank_gateway_version = empty($bank_gateway_version) ? '' : $bank_gateway_version;
	echo '<!-- Event Espresso Electronic Funds Transfer Gateway Version ' . $bank_gateway_version . ' -->';

	$bank_deposit_settings = get_option('event_espresso_bank_deposit_settings');

	$args = array(
		'page_id' =>$org_options['return_url'],
		'r_id' =>$registration_id,
		'id' =>$attendee_id,
		'payment_type' => 'cash_check',
		'type' => 'bank',
	);
	$finalize_link = add_query_arg( $args, home_url() );	
?>
<div id="bank-payment-option-dv" class="payment-option-dv">

	<a id="bank-payment-option-lnk" class="payment-option-lnk algn-vrt display-the-hidden" rel="bank-payment-option-form">
		<div class="vrt-cell">
			<div>
				<?php echo stripslashes( $bank_deposit_settings['bank_title'] ) ?>
			</div>
		</div>
	</a>
	<br/>

	<div id="bank-payment-option-form-dv" class="hide-if-js">
		
		<div class="event-display-boxes">
			<h4 id="bank_title" class="payment_type_title section-heading"><?php echo stripslashes_deep(empty($bank_deposit_settings['bank_title']) ? '' : $bank_deposit_settings['bank_title']) ?></h4>
			<p class="instruct">
				<?php echo stripslashes_deep(empty($bank_deposit_settings['bank_instructions']) ? '' : $bank_deposit_settings['bank_instructions'] ); ?>					
			</p>
			<p>
				<span class="section-title"><?php _e('Name on Account:', 'event_espresso'); ?></span>
				<strong><span class="highlight"><?php echo stripslashes_deep( ! empty($bank_deposit_settings['account_name']) ? $bank_deposit_settings['account_name'] : '' ); ?></span></strong><br/>
				<span class="section-title"><?php _e('Account Number:', 'event_espresso'); ?></span>
				<strong><span class="highlight"><?php echo stripslashes_deep( ! empty($bank_deposit_settings['bank_account']) ? $bank_deposit_settings['bank_account'] : '' ); ?></span></strong><br/>
				<span class="section-title"><?php _e('Financial Institution:', 'event_espresso'); ?></span>
				<span class="highlight"><strong><?php echo stripslashes_deep( ! empty($bank_deposit_settings['bank_name']) ? $bank_deposit_settings['bank_name'] : '' ); ?></strong></span><br/>						
				<span class="section-title"><?php _e('Address:', 'event_espresso'); ?></span>
				<span class="highlight address-block">
					<strong><?php echo stripslashes_deep( ! empty($bank_deposit_settings['bank_address']) ?  $bank_deposit_settings['bank_address'] : '' ); ?></strong>
				</span>
			</p>
		</div>	
		<br/>
		<div class="event_espresso_attention event-messages ui-state-highlight">
			<span class="ui-icon ui-icon-alert"></span>
			<p>
				<strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
				<?php _e('If you wish to pay by an Electronic Funds Transfer via your bank, then please make note of the information above, and click to', 'event_espresso'); ?>
				<a id="finalize_bank" class="finalize_button allow-leave-page inline-link" href="<?php echo $finalize_link;?>" title="<?php _e('Complete your Registration', 'event_espresso'); ?>">
					<?php _e('Complete your Registration', 'event_espresso'); ?>					
				</a>
				<div class="clear"></div>
			</p>
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="bank-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>	<?php
}

add_action('action_hook_espresso_display_offline_payment_gateway_2', 'espresso_display_bank');
