<?php

function espresso_display_check($payment_data) {

	global $org_options;
	extract($payment_data);
// Setup class
	$default_gateway_version = empty($default_gateway_version) ? '' : $default_gateway_version;
	echo '<!-- Event Espresso Default Gateway Version ' . $default_gateway_version . '-->';

	$check_payment_settings = get_option('event_espresso_check_payment_settings');
	
	$args = array(
		'page_id' =>$org_options['return_url'],
		'r_id' =>$registration_id,
		'id' =>$attendee_id,
		'payment_type' => 'cash_check',
		'type' => 'check',
	);
	$finalize_link = add_query_arg( $args, home_url() );	
	
?>
<div id="check-payment-option-dv" class="payment-option-dv">

	<a id="check-payment-option-lnk" class="payment-option-lnk algn-vrt display-the-hidden" rel="check-payment-option-form" style="display: table">
		<div class="vrt-cell">
			<div>
				<?php echo stripslashes( $check_payment_settings['check_title'] ) ?>
			</div>
		</div>
	</a>
	<br/>

	<div id="check-payment-option-form-dv" class="hide-if-js">
		
		<div class="event-display-boxes">
			<h4 id="check_title" class="payment_type_title section-heading">
				<?php echo stripslashes_deep(empty($check_payment_settings['check_title']) ? '' : $check_payment_settings['check_title']) ?>
			</h4>
			<p class="instruct">
				<?php echo stripslashes_deep(empty($check_payment_settings['check_instructions']) ? '' : $check_payment_settings['check_instructions'] ); ?>
			</p>
			<p>
				<span class="section-title"><?php _e('Payable to:', 'event_espresso'); ?></span>
				<span class="highlight"><?php echo stripslashes_deep(empty($check_payment_settings['payable_to']) ? '' : $check_payment_settings['payable_to']); ?></span>
			</p>
			<p>
				<span class="section-title"><?php _e('Mailing Address:', 'event_espresso'); ?></span><br/>
				<span class="highlight address-block">
					<strong><?php echo stripslashes_deep( ! empty($check_payment_settings['payment_address']) ? $check_payment_settings['payment_address'] : '' ); ?></strong>
				</span>				
			</p>

		</div>
		<br/>
		<div class="event_espresso_attention event-messages ui-state-highlight">
			<span class="ui-icon ui-icon-alert"></span>
			<p>
				<strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
				<?php _e('If you wish to pay by check or money order, then please make note of the information above, and click to ', 'event_espresso'); ?>
				<a id="finalize_check" class="finalize_button allow-leave-page inline-link" href="<?php echo $finalize_link; ?>" title="<?php _e('Complete your Registration', 'event_espresso'); ?>">
					<?php _e('Complete your Registration', 'event_espresso'); ?>
				</a>
				<div class="clear"></div>
			</p>
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="check-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>
	<?php
}

add_action('action_hook_espresso_display_offline_payment_gateway_2', 'espresso_display_check');
