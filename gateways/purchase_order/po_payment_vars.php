<?php

function espresso_display_purchase_order($payment_data) {

	global $org_options;
	extract($payment_data);
	$default_gateway_version = empty($default_gateway_version) ? '' : $default_gateway_version;
	echo '<!-- Event Espresso Purchase Order Gateway Version ' . $default_gateway_version . '-->';

	$po_payment_settings = get_option('event_espresso_purchase_order_payment_settings');
	$args = array(
		'page_id' =>$org_options['return_url'],
		'r_id' =>$registration_id,
		'id' =>$attendee_id,
		'payment_type' => 'purchase_order',
		'type' => 'purchase_order',
	);
	$finalize_link = add_query_arg( $args, home_url() );	
?>
<div id="po-payment-option-dv" class="payment-option-dv">

	<a id="po-payment-option-lnk" class="payment-option-lnk algn-vrt display-the-hidden" rel="po-payment-option-form" style="display: table">
		<div class="vrt-cell">
			<div>
				<?php echo stripslashes( $po_payment_settings['purchase_order_title'] ) ?>
			</div>
		</div>
	</a>
	<br/>

	<div id="po-payment-option-form-dv" class="hide-if-js">
		
		<div class="event-display-boxes">
			<h4 id="po_title" class="payment_type_title section-heading">
				<?php echo stripslashes_deep(empty($po_payment_settings['purchase_order_title']) ? '' : $po_payment_settings['purchase_order_title']) ?>
			</h4>
			<p class="instruct">
				<?php echo stripslashes_deep(empty($po_payment_settings['purchase_order_instructions']) ? '' : $po_payment_settings['purchase_order_instructions'] ); ?>
			</p>
			<p>
				<span class="section-title"><?php _e('Payable to:', 'event_espresso'); ?></span>
				<span class="highlight"><strong><?php echo stripslashes_deep( ! empty($po_payment_settings['payable_to']) ? $po_payment_settings['payable_to'] : '' ); ?></strong></span>
			</p>
			<p>
				<span class="section-title"><?php _e('Mailing Address:', 'event_espresso'); ?></span><br/>
				<span class="highlight address-block">
					<strong><?php echo stripslashes_deep( ! empty($po_payment_settings['payment_address']) ? $po_payment_settings['payment_address'] : '' ); ?></strong>
				</span>				
			</p>

		</div>
		<br/>
		<div class="event_espresso_attention event-messages ui-state-highlight">
		<form id="finalize_purchase_order" name="finalize_purchase_order" method="GET" action="<?php echo home_url();//echo $finalize_link;?>">
			<p>
				<strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
				<?php _e('If you wish to pay by check or money order, then please make note of the information above, and<br />enter Purchase Order or Money Order Number :', 'event_espresso'); ?> 
				<input type="text" name="po_number" id="po_number" class="required" /><br/>
				<?php foreach($args as $key=>$value){
					echo "<input type='hidden' name='$key' value='$value'>";
				}?>
				<input class="finalize_button allow-leave-page inline-link" name="submit_purcahse_order" value="<?php _e('Complete your Registration', 'event_espresso'); ?>" type="submit" />
				<div class="clear"></div>
			</p>			
		</form>
		</div>		
		<br/>
		
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="po-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>
		

	</div>
</div>
<script>
jQuery(document).ready(function($) {
	$(function(){
		//po payment form validation if the po option is selected
		if (!$('#po-payment-option-dv').hasClass('.payment-option-closed')) {
			$('#finalize_purchase_order').validate();
		}
	});
});
</script>

	<?php
}

add_action('action_hook_espresso_display_offline_payment_gateway_2', 'espresso_display_purchase_order');
