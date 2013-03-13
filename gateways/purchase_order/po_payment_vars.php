<?php

function espresso_display_purchase_order($payment_data) {

	global $org_options;
	extract($payment_data);
	$default_gateway_version = empty($default_gateway_version) ? '' : $default_gateway_version;
	echo '<!-- Event Espresso Purchase Order Gateway Version ' . $default_gateway_version . '-->';

	$po_payment_settings = get_option('event_espresso_purchase_order_payment_settings');
?>
<div id="po-payment-option-dv" class="payment-option-dv">

	<a id="po-payment-option-lnk" class="payment-option-lnk algn-vrt display-the-hidden" rel="po-payment-option-form" style="display: table-cell">
		<div class="vrt-cell">
			<div>
				<?php echo stripslashes( $po_payment_settings['purchase_order_title'] ) ?>
			</div>
		</div>
	</a>
	<br/>

	<div id="po-payment-option-form-dv" class="hide-if-js">
		<div class="event_espresso_attention event-messages ui-state-highlight">
		<form id="finalize_purchase_order" name="finalize_purchase_order" method="post" action="<?php echo home_url() . '/?page_id=' . $org_options['return_url']; ?>&amp;payment_type=purchase_order&amp;id=<?php echo $attendee_id . '&r_id=' . $registration_id ?>&type=purchase_order">
			<span class="ui-icon ui-icon-alert"></span>
			<p>
				<strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
				<?php _e('If you wish to pay by check or money order, then please make note of the information below, and enter a Purchase Order Number', 'event_espresso'); ?> <input type="text" name="po_number" id="po_number" /> <input name="submit_purcahse_order" value="Finalize Registration" type="submit" /></p>
		</form>
		</div>
		
		<div class="event-display-boxes">
			<h4 id="po_title" class="payment_type_title section-heading">
				<?php echo stripslashes_deep(empty($po_payment_settings['purchase_order_title']) ? '' : $po_payment_settings['purchase_order_title']) ?>
			</h4>
			<p class="instruct">
				<?php echo stripslashes_deep(empty($po_payment_settings['purchase_order_instructions']) ? '' : $po_payment_settings['purchase_order_instructions'] ); ?>
			</p>
			<p>
				<span class="section-title"><?php _e('Payable to:', 'event_espresso'); ?></span>
				<span class="highlight"><?php echo stripslashes_deep(empty($po_payment_settings['payable_to']) ? '' : $po_payment_settings['payable_to']); ?></span>
			</p>
			<p class="section-title">
				<?php _e('Payment Address: ', 'event_espresso'); ?>
			</p>
			<div class="address-block">
				<?php echo wpautop(stripslashes_deep(empty($po_payment_settings['payment_address']) ? '' : $po_payment_settings['payment_address'])); ?>
			</div>
		</div>
		<br/>
		
		
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="po-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>
		
		<?php /*?><a id="finalize_purchase_order" href="<?php echo home_url() . '/?page_id=' . $org_options['return_url']; ?>&amp;payment_type=purchase_order&amp;id=<?php echo $attendee_id . '&r_id=' . $registration_id ?>&type=purchase_order" class="inline-link" title="<?php _e('Finalize your registration', 'event_espresso'); ?>">
			<?php _e('click here to finalize your registration', 'event_espresso'); ?>
		</a><?php */?>
	</div>
</div>
	<?php
}

add_action('action_hook_espresso_display_offline_payment_gateway_2', 'espresso_display_purchase_order');
