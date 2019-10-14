jQuery(document).ready(function($) {
	
	function ee3StripeElements() {
		this.submitButtonId = '#ee-stripe-button-btn';
		this.submitPaymentButton = {};
		this.paymentIntentInput = {};
		this.stripeErrorContainer = {};
		this.stripeElements = {};
		this.stripeCardElement = {};
		this.cardHolderFirstName = {};
		this.cardHolderLastName = {};
		this.cardHolderAddress = {};
		this.cardHolderAddress2 = {};
		this.cardHolderCity = {};
		this.cardHolderState = {};
		this.cardHolderCountry = {};
		this.cardHolderZip = {};
		this.initialized = false;

		/**
		 * @function initialize
		 */
		this.initialize = function() {
			this.initialize_objects();
		
			// ensure that the Stripe js class is loaded
			if ( typeof Stripe === 'undefined' ) {
				return;
			}

			this.stripe = Stripe( ee_stripe_args.stripe_pk_key );
			var args = {};
			if ( ee_stripe_args.data_locale ) {
				args.locale = ee_stripe_args.data_locale;
			}
			this.stripeElements = this.stripe.elements( args );
			this.stripeCardElement = this.stripeElements.create( 'card' );
			this.stripeCardElement.mount( '#ee-stripe-card-element' );
			
			// Set up listener for payment button
			this.set_listener_for_submit_payment_button();

			//alert('this.initialized');
			this.initialized = true;
		};

		/**
		 * @function initialize_objects
		 */
		this.initialize_objects = function() {
			//console.log( JSON.stringify( '**this.initialize_objects**', null, 4 ) );
			this.submitPaymentButton = $( this.submitButtonId );
			this.paymentIntentInput = $( '#espresso_stripe_payment_intent_id' );
			this.stripeErrorContainer = $( '#espresso_stripe_errors' );
			this.cardHolderFirstName = $( '#espresso_stripe_first_name' );
			this.cardHolderLastName = $( '#espresso_stripe_last_name' );
			this.cardHolderEmail = $( '#espresso_stripe_email' );
			this.cardHolderAddress = $( '#espresso_stripe_address' );
			this.cardHolderCity = $( '#espresso_stripe_city' );
			this.cardHolderState = $( '#espresso_stripe_state' );
			this.cardHolderCountry = $( '#espresso_stripe_country' );
		};

		/**
		 * @function set_listener_for_submit_payment_button
		 */
		this.set_listener_for_submit_payment_button = function() {
			var stripe_instance = this;
			//console.log( 'initialize', 'set_listener_for_submit_payment_button', true );
			this.submitPaymentButton.on( 'click', function(e) {
				e.preventDefault();
				var billingDetails = {};
				if ( stripe_instance.cardHolderFirstName.val() || stripe_instance.cardHolderLastName.val() ) {
					billingDetails.name = stripe_instance.cardHolderFirstName.val() + ' ' + stripe_instance.cardHolderLastName.val();
				}
				if ( stripe_instance.cardHolderEmail.val() ) {
					billingDetails.email = stripe_instance.cardHolderEmail.val();
				}

				var address = {};
				if ( stripe_instance.cardHolderAddress.val() ) {
					address.line1 = stripe_instance.cardHolderAddress.val();
				}
				if ( stripe_instance.cardHolderCity.val() ) {
					address.city = stripe_instance.cardHolderCity.val();
				}
				if ( stripe_instance.cardHolderState.val() ) {
					address.state = stripe_instance.cardHolderState.val();
				}
				if ( stripe_instance.cardHolderCountry.val() ) {
					address.country = stripe_instance.cardHolderCountry.val();
				}
				if ( Object.keys( address ).length > 0 ) {
					billingDetails.address = address;
				}
				stripe_instance.stripe.handleCardPayment( 
					this.dataset.secret, stripe_instance.stripeCardElement, 
					{	
						payment_method_data: {
							billing_details: billingDetails
						}
					} 
				).then( function( response ) {
					stripe_instance.handleStripeResponse( response );
				} );
			} );
		};

		this.handleStripeResponse = function( result ) {
			if ( result.error ) {
				this.handleCardPaymentError( result );
			} else {
				this.handleCardPaymentSuccess( result );
			}
		};

		this.handleCardPaymentError = function( result ) {
			this.stripeErrorContainer.text(result.error.message)
			this.stripeErrorContainer.show()
		};
		/**
		 * @function handleCardPaymentSuccess
		 * @param  {Object} result
		 */
		this.handleCardPaymentSuccess = function( result ) {
			let stripeApiPaymentIntentId = '';
			if ( typeof result.paymentIntent === 'object' && result.paymentIntent !== null ) {
				stripeApiPaymentIntentId = result.paymentIntent.id;
			}
			//console.log( JSON.stringify( 'handleCardPaymentSuccess', null, 4 ) );
			this.paymentIntentInput.val( stripeApiPaymentIntentId );
			this.submitPaymentButton.parents( 'form:first' ).submit();
		};

	}
	// end of ee3StripeElements
	var EE3_STRIPE = new ee3StripeElements();
	EE3_STRIPE.initialize();
} );