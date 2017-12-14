<?php

function espresso_display_stripe($payment_data) {
	extract($payment_data);
	global $org_options;
	$stripe_settings = get_option('event_espresso_stripe_settings');
	if ($stripe_settings['force_ssl_return']) {
		$home = str_replace('http://', 'https://', home_url());
	} else {
		$home = home_url();
	}

	wp_register_script( 'stripe', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/stripe/stripe.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'stripe' );	

?>

<div id="stripe-payment-option-dv" class="payment-option-dv">

	<a id="stripe-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="stripe-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using a Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="stripe-payment-option-form-dv" class="hide-if-js">

<?php
		if ($stripe_settings['display_header']) { ?>			
		<h3 class="payment_header"><?php echo $stripe_settings['header']; ?></h3>
<?php } ?>

		<div class = "event_espresso_form_wrapper">

			<form action="<?php echo add_query_arg(array('r_id'=>$registration_id, 'attendee_id'=>$attendee_id), get_permalink($org_options['return_url'])); ?>" method="POST" class="allow-leave-page">
			  <script
				src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
				data-key="<?php echo $stripe_settings['stripe_publishable_key'] ?>"
				data-amount="<?php echo str_replace( array(',', '.'), '', number_format( $event_cost, 2) ) ?>"
				data-name="<?php echo $org_options['organization']; ?>"
				data-description="<?php echo $event_name; ?>">
			  </script>
			</form>

		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="stripe-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_stripe');
