jQuery(document).ready(function($) {

	var EE3_STRIPE;

	EE3_STRIPE = {

		handler : {},
		submit_button_id : '#ee-stripe-button-btn',
		submit_payment_button : {},
		token_string : {},
		transaction_email : {},
		transaction_total : {},
		product_description : {},
		initialized : false,
		selected : false,

		/**
		 * @function initialize
		 */
		initialize : function() {
			EE3_STRIPE.initialize_objects();
		
			// ensure that the StripeCheckout js class is loaded
			if ( typeof StripeCheckout === 'undefined' ) {
				return;
			}
			EE3_STRIPE.selected = true;
			EE3_STRIPE.set_up_handler();
			EE3_STRIPE.set_listener_for_submit_payment_button();
			//alert('EE3_STRIPE.initialized');
			EE3_STRIPE.initialized = true;
		},

		/**
		 * @function initialize_objects
		 */
		initialize_objects : function() {
			//console.log( JSON.stringify( '**EE3_STRIPE.initialize_objects**', null, 4 ) );
			EE3_STRIPE.submit_payment_button = $( EE3_STRIPE.submit_button_id );
			EE3_STRIPE.token_string = $('#ee-stripe-token');
			EE3_STRIPE.transaction_email = $('#ee-stripe-transaction-email');
			EE3_STRIPE.transaction_total = $('#ee-stripe-amount');
		},

		/**
		 * @function set_up_handler
		 */
		set_up_handler : function() {
			//console.log( 'initialize', 'set_up_handler', true );
			EE3_STRIPE.handler = StripeCheckout.configure({
				key: ee_stripe_args.stripe_pk_key,
				token: function( stripe_token ) {
					//console_log_object( 'stripe_token', stripe_token, 0 );
					// Use the token to create the charge with a server-side script.
					EE3_STRIPE.checkout_success( stripe_token );
				}
			});
		},

		/**
		 * @function checkout_success
		 * @param  {object} stripe_token
		 */
		checkout_success : function( stripe_token ) {
			if ( typeof stripe_token.used !== 'undefined' && ! stripe_token.used ) {
				//console.log( 'checkout_success > EE_STRIPE.token_string.attr(name)', EE_STRIPE.token_string.attr('name'), true );
				EE3_STRIPE.token_string.val( stripe_token.id );
				//console.log( 'checkout_success > stripe_token.id', stripe_token.id, true );
				// trigger submit on the Stripe form.
				EE3_STRIPE.submit_payment_button.parents( 'form:first' ).submit();
			}
		},

		/**
		 * @function set_listener_for_submit_payment_button
		 */
		set_listener_for_submit_payment_button : function() {
			//console.log( 'initialize', 'set_listener_for_submit_payment_button', true );
			EE3_STRIPE.submit_payment_button.on( 'click', function(e) {
				e.preventDefault();
				// Open a modal window with further Checkout options.
				EE3_STRIPE.handler.open({
					name: ee_stripe_args.stripe_org_name,					
					image: ee_stripe_args.stripe_org_image,
					description: ee_stripe_args.stripe_description,
					amount: parseInt(EE3_STRIPE.transaction_total.val()),
					email: EE3_STRIPE.transaction_email.val(),
					currency: ee_stripe_args.stripe_currency,
					panelLabel: ee_stripe_args.stripe_panel_label,
					//zipCode : ee_stripe_args.validate_zip === 'true',
					//billingAddress : ee_stripe_args.billing_address === 'true',
					//locale : ee_stripe_args.data_locale
				});
			});
		},

	}
	EE3_STRIPE.initialize();
});