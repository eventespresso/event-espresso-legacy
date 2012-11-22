<?php

function espresso_display_bank($payment_data) {
	global $org_options;
	extract($payment_data);
// Setup class
	$bank_gateway_version = empty($bank_gateway_version) ? '' : $bank_gateway_version;
	echo '<!-- Event Espresso Electronic Funds Transfer Gateway Version ' . $bank_gateway_version . ' -->';

	$bank_deposit_settings = get_option('event_espresso_bank_deposit_settings');
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
		
		<div class="event_espresso_attention event-messages ui-state-highlight">
			<span class="ui-icon ui-icon-alert"></span>
			<p><strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
		<?php _e('If you wish to pay by an Electronic Funds Transfer via your bank, please make note of the information below, then', 'event_espresso'); ?>
				<a id="finalize_bank"href="<?php echo home_url() . '/?page_id=' . $org_options['return_url']; ?>&amp;payment_type=cash_check&amp;id=<?php echo $attendee_id . '&r_id=' . $registration_id ?>&type=bank" class="inline-link" title="<?php _e('Finalize your registration', 'event_espresso'); ?>"><?php _e('click here to finalize your registration', 'event_espresso'); ?></a>
			</p>
		</div>
		<div class="event-display-boxes">
			<h4 id="bank_title" class="payment_type_title section-heading"><?php echo stripslashes_deep(empty($bank_deposit_settings['bank_title']) ? '' : $bank_deposit_settings['bank_title']) ?></h4>
				<p class="instruct"><?php echo stripslashes_deep(empty($bank_deposit_settings['bank_instructions']) ? '' : $bank_deposit_settings['bank_instructions'] ); ?></p>
				<p><span class="section-title"><?php _e('Name on Account:', 'event_espresso'); ?></span>
					<?php echo stripslashes_deep(empty($bank_deposit_settings['account_name']) ? '' : '<span class="highlight">' . $bank_deposit_settings['account_name']) . '</span>'; ?></p>
				<p><span class="section-title"><?php _e('Account Number:', 'event_espresso'); ?></span>
					<?php echo stripslashes_deep(empty($bank_deposit_settings['bank_account']) ? '' : '<span class="highlight">' . $bank_deposit_settings['bank_account']) . '</span>'; ?></p>
				<p><span class="section-title"><?php _e('Financial Institution:', 'event_espresso'); ?></span>
					<?php echo stripslashes_deep(empty($bank_deposit_settings['bank_name']) ? '' : '<span class="highlight">' . $bank_deposit_settings['bank_name']) . '</span>' ?></p>
				<div class="address-block">
		<?php echo wpautop(stripslashes_deep(empty($bank_deposit_settings['bank_address']) ? '' : $bank_deposit_settings['bank_address'])); ?>
				</div>
		</div>	

		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="bank-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>	<?php
}

add_action('action_hook_espresso_display_offline_payment_gateway_2', 'espresso_display_bank');
