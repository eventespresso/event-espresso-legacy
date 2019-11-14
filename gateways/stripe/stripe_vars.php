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
		__('%1$s [%2$s] >> %3$s %4$s', 'event_espresso'),
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

	wp_enqueue_script('stripe_payment_js', 'https://js.stripe.com/v3/', array(), false, true);
	wp_enqueue_script('espresso_stripe', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/stripe/espresso_stripe.js', array('stripe_payment_js'), false, true);
	wp_enqueue_style('espresso_stripe_css', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/stripe/espresso_stripe.css');

	//Include the Stripe API
	if (! class_exists('Stripe\Stripe')) {
		require_once (dirname(__FILE__).'/stripe-php-6.43.1/init.php');
	}
	// Setup a Stripe object using the secret key.
	\Stripe\Stripe::setApiKey( $stripe_settings['stripe_secret_key']);

	// Create PaymentIntent object.
	$intent = \Stripe\PaymentIntent::create(array(
		'amount' => str_replace( array(',', '.'), '', number_format( $event_cost, espresso_get_stripe_decimal_places($stripe_settings['stripe_currency_symbol'])) ),
		'currency' => $stripe_settings['stripe_currency_symbol'],
		'description' => $stripe_description,
	), array(
		'idempotency_key' => $registration_id,
	));

	// If we have a PaymentIntent object, use the values from it.
	if( $intent instanceof \Stripe\PaymentIntent) {
		$intent_id     = $intent->id;
		$intent_client_secret = $intent->client_secret;
	} else {
		$intent_id     = '';
		$intent_client_secret = '';
	}

	$ee_stripe_args = array(
		'stripe_pk_key' => $stripe_settings['stripe_publishable_key'],
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
				<fieldset id="stripe-billing-info-dv">
						<h4 class="section-title"><?php esc_html_e('Billing Information', 'event_espresso') ?></h4>
						<p>
							<label for="first_name"><?php esc_html_e('First Name', 'event_espresso'); ?></label>
							<input name="first_name" type="text" id="espresso_stripe_first_name" class="required" value="<?php echo $fname ?>" />
						</p>
						<p>
							<label for="last_name"><?php esc_html_e('Last Name', 'event_espresso'); ?></label>
							<input name="last_name" type="text" id="espresso_stripe_last_name" class="required" value="<?php echo $lname ?>" />
						</p>
						<p>
							<label for="email"><?php esc_html_e('Email Address', 'event_espresso'); ?></label>
							<input name="email" type="text" id="espresso_stripe_email" class="required" value="<?php echo $attendee_email ?>" />
						</p>
						<?php if(! empty($stripe_settings['stripe_collect_billing_address']) && $stripe_settings['stripe_collect_billing_address']) { ?>
							<p>
								<label for="address"><?php esc_html_e('Address', 'event_espresso'); ?></label>
								<input name="address" type="text" id="espresso_stripe_address" class="required" value="<?php echo $address ?>" />
							</p>
							<p>
								<label for="city"><?php esc_html_e('City', 'event_espresso'); ?></label>
								<input name="city" type="text" id="espresso_stripe_city" class="required" value="<?php echo $city ?>" />
							</p>
							<p>
								<label for="state"><?php esc_html_e('State', 'event_espresso'); ?></label>
								<input name="state" type="text" id="espresso_stripe_state" class="required" value="<?php echo $state ?>" />
							</p>
							<p>
								<label for="country"><?php esc_html_e('Country', 'event_espresso'); ?></label>
								<input name="country" type="text" id="espresso_stripe_country" class="required" value="<?php echo $country ?>" />
							</p>
						<?php } ?>
				</fieldset>
				<!-- placeholder for Elements -->
				<label><?php esc_html_e('Card Details', 'event_espresso');?></label>
				<div id="ee-stripe-card-element"></div>
				<button id="ee-stripe-button-btn" data-secret="<?php echo $intent_client_secret ?>"><?php esc_html_e('Pay Now', 'event_espresso');?></button>
				<h4 id="espresso_stripe_errors" style="color:#ff0000; display:none"></h4>
				<input id="espresso_stripe_payment_intent_id" type="hidden" name="espresso_stripe_payment_intent_id" value="<?php echo $intent_id ?>" />
			</form>
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="stripe-payment-option-form" style="cursor:pointer;"><?php esc_html_e('Choose a different payment option', 'event_espresso'); ?></a>
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
