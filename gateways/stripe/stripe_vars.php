<?php

function espresso_display_stripe($payment_data) {
	extract($payment_data);
	global $org_options, $wpdb;
	$stripe_settings = get_option('event_espresso_stripe_settings');
	if ($stripe_settings['force_ssl_return']) {
		$home = str_replace('http://', 'https://', home_url());
	} else {
		$home = home_url();
	}
	
	$SQL = "SELECT start_date FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id=%s ORDER BY id LIMIT 1";
	$payment_data['start_date'] = $wpdb->get_var( $wpdb->prepare( $SQL, $payment_data['registration_id']));

	$stripe_description = sprintf(
		/* translators: 1: event name, 2: event date, 3: attendee first name, 4: attendee last name */
		esc_html__('%1$s [%2$s] >> %3$s %4$s', 'event_espresso'),
		$payment_data["event_name"],
		date('m-d-Y', strtotime($payment_data['start_date'])),
		$payment_data["fname"],
		$payment_data["lname"]
	);
	
	//Check for an alternate Stripe settings
	if ( !empty($payment_data['event_meta']['stripe_publishable_key'] ) ) {
		//Alternate Stripe settings
		$stripe_settings['stripe_publishable_key'] = $payment_data['event_meta']['stripe_publishable_key'];
	}

	wp_enqueue_script('stripe_payment_js', 'https://checkout.stripe.com/v2/checkout.js', array(), false, true);
	wp_enqueue_script('espresso_stripe', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/stripe/espresso_stripe.js', array('stripe_payment_js'), false, true);

	$ee_stripe_args = array(
        'stripe_pk_key' => $stripe_settings['stripe_publishable_key'],
        'stripe_org_name' => $org_options['organization'],
        'stripe_org_image' => $org_options['default_logo_url'],
        'stripe_description' => $stripe_description,
        'stripe_currency' => !empty($stripe_settings['stripe_currency_symbol']) ? $stripe_settings['stripe_currency_symbol'] : 'USD',
        'stripe_panel_label' => sprintf(__('Pay %1$s Now', 'event_espresso'), '{{amount}}'),
        'card_error_message' => esc_html__('Payment Error! Please refresh the page and try again or contact support.', 'event_espresso'),
	);
	wp_localize_script('espresso_stripe', 'ee_stripe_args', $ee_stripe_args);

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
			<form id="ee-stripe-form" action="<?php echo add_query_arg(array('r_id'=>$registration_id, 'attendee_id'=>$attendee_id), get_permalink($org_options['return_url'])); ?>" method="POST" class="allow-leave-page">
				<button id="ee-stripe-button-btn"><?php esc_html_e( 'Pay Now', 'event_espresso' );?></button>
				<input id="ee-stripe-token" name="eeStripeToken" type="hidden" value="" />
				<input id="ee-stripe-transaction-email" name="eeStripeEmail" type="hidden" value="<?php echo $payment_data['attendee_email']; ?>" />
				<input id="ee-stripe-amount" name="eeStripeAmount" type="hidden" value="<?php echo str_replace( array(',', '.'), '', number_format( $event_cost, get_stripe_decimal_places($stripe_settings['stripe_currency_symbol'])) ) ?>" />
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


/**
 * Gets the number of decimal places Stripe expects a currency to have.
 * See https://stripe.com/docs/currencies#charge-currencies for the list.
 *
 * @param string $currency Accepted currency.
 * @return int
 */
function espresso_get_stripe_decimal_places($currency = '')
{
    if (!$currency) {
    	$stripe_settings = get_option('event_espresso_stripe_settings');
        $currency = !empty($stripe_settings['stripe_currency_symbol']) ? $stripe_settings['stripe_currency_symbol'] : 'USD';
    }
    switch (strtoupper($currency)) {
        // Zero decimal currencies.
        case 'BIF' :
        case 'CLP' :
        case 'DJF' :
        case 'GNF' :
        case 'JPY' :
        case 'KMF' :
        case 'KRW' :
        case 'MGA' :
        case 'PYG' :
        case 'RWF' :
        case 'VND' :
        case 'VUV' :
        case 'XAF' :
        case 'XOF' :
        case 'XPF' :
            return 0;
        default :
            return 2;
    }
}
