<?php

/**
 * modelled this function after event espresso/includes/progres-sregistration/payment_page.php, but 
 * put all the code into a small function to simplify. I realize it's only slightly better.
 * @global type $wpdb
 * @param int $registrationId
 * @return array 
 */
function espresso_gateway_get_payment_data($registrationId){
	global $wpdb;
	$SQL = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='%s' LIMIT 1";
	$payment_data['attendee_id'] = $wpdb->get_var( $wpdb->prepare( $SQL, $registrationId ) );
	
	$payment_data['attendee_id'] = apply_filters( 'filter_hook_espresso_transactions_get_attendee_id', $payment_data['attendee_id'] );
	$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	return $payment_data;
}
function espresso_display_eway_rapid3($data) {
	extract($data);
	global $org_options;
	$eway_rapid3_settings = get_option('event_espresso_eway_rapid3_settings');
	
	//if(empty($_GET['AccessCode']) || empty($_SESSION['eway_rapid3_url'])){
		require_once('includes/EWayRapid3Client.class.php');
		$payment_data=espresso_gateway_get_payment_data($data['registration_id']);
		//var_dump($payment_data);
		$rapid3Client=new Espresso_EWayRapid3Client(
			array(
				'apiKey'=>$eway_rapid3_settings['eway_rapid3_api_key'],
				'apiPassword'=>$eway_rapid3_settings['eway_rapid3_api_password'],
				'useSandbox'=>$eway_rapid3_settings['eway_rapid3_use_sandbox']
			));
		$totalCost=intval(floatval($payment_data['total_cost'])*100);

		$payment = array(
				'TotalAmount' =>$totalCost , // How you want to obtain payment.  Authorization indidicates the payment is a basic auth subject to settlement with Auth & Capture.  Sale indicates that this is a final sale for which you are requesting payment.  Default is Sale.
				'InvoiceDescription'=> $event_name,
				'CurrencyCode'=>$eway_rapid3_settings['currency_format']
		);
		$eway_rapid3RequestData = array('Payment'=>$payment);
		$redirectUrl = espresso_build_gateway_url('return_url', $payment_data, 'eway_rapid3', array('eway_rapid3'=>'true'));
		$rapid3Response= $rapid3Client->createAccessCode($eway_rapid3RequestData,$redirectUrl,'ProcessPayment');
		if( empty($rapid3Response)){
			echo '<div id="message" class="clear"><p class="error">**' . __( 'An error occcurred communicating with EWay Rapid 3 Gateway\'s Server. You probably have the wrong API Key', 'event_espresso' ) . '</p></div>';
			return;
		}
		$error=empty($rapid3Response->FormActionURL) || empty($rapid3Response->AccessCode);
		$_SESSION['eway_rapid3_url']=$rapid3Response->FormActionURL;
		$ewayRapid3AccessCode=$rapid3Response->AccessCode;
	/*	echo "use new access code!";
	}else{
		echo "use old access code!:".$_GET['AccessCode'].$_SESSION['eway_rapid3_url'];
		$ewayRapid3AccessCode=$_GET['AccessCode'];
	}*/
	
	wp_register_script( 'eway_rapid3', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/eway_rapid3/eway_rapid3.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'eway_rapid3' );		
	?>
<div id="eway_rapid3-payment-option-dv" class="payment-option-dv">

	<a id="eway_rapid3-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="eway_rapid3-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="eway_rapid3-payment-option-form-dv" class="hide-if-js">	
		<div class="event-display-boxes">
			<?php
			if ($eway_rapid3_settings['display_header']) {?>
				<h3 class="payment_header"><?php echo $eway_rapid3_settings['header']; ?></h3>
			<?php } ?>
			<?php if($error){?>
				<p class='error'><?php _e("An error has occured in the using of the Eway Rapid 3.0 payment gateway. Please try a different gateway","event_espresso");?></p>
			<?php }else{?>
				<div class = "event_espresso_form_wrapper">
					<form id="eway_rapid3_payment_form" name="eway_rapid3_payment_form" method="post" action="<?php echo $_SESSION['eway_rapid3_url'] ?>">

						<fieldset id="paypal-credit-card-info-dv">
							<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
							<p>
								<label for="first_name"><?php _e('Cardholder Full Name', 'event_espresso'); ?></label>
								<input name="EWAY_CARDNAME" type="text" id="ppp_first_name" class="required" value="<?php echo $fname ?> <?php echo $lname ?>" />
							</p>
							<p>
								<label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
								<input type="text" name="EWAY_CARDNUMBER" class="required" id="ppp_card_num" autocomplete="off" />
							</p>
							<p>
								<label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
								<select id="ppp_card-exp" name ="EWAY_CARDEXPIRYMONTH" class="med required">
											<?php
											for ($i = 1; $i < 13; $i++){
												$paddedMonth=str_pad($i,2,'0',STR_PAD_LEFT);
												echo "<option value='$paddedMonth'>$paddedMonth</option>";
											}
											?>
								</select>
							</p>
							<p>
								<label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
								<select id="ppp_exp-year" name ="EWAY_CARDEXPIRYYEAR" class="med required">
											<?php
											$curr_year = date("Y");
											for ($i = 0; $i < 10; $i++) {
												$disp_year = $curr_year + $i;
												echo "<option value='".(intval($disp_year)-2000)."'>$disp_year</option>";
											}
											?>
								</select>
							</p>
							<p>
								<label for="cvv"><?php _e('CVN Code', 'event_espresso'); ?></label>
								<input type="text" name="EWAY_CARDCVN" id="ppp_exp_date" autocomplete="off" class="small required" />
							</p>
						</fieldset>

						<input name="EWAY_ACCESSCODE" type='hidden' value='<?php echo $ewayRapid3AccessCode?>'/>
						<p class="event_form_submit">
							<input name="eway_rapid3_submit" id="eway_rapid3_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
							<div class="clear"></div>
						</p>
						<span id="processing"></span>
					</form>

				</div><!-- / .event_espresso_or_wrapper -->
			<?php }?>
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="eway_rapid3-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>		
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway','espresso_display_eway_rapid3');
