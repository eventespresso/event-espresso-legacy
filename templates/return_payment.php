<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	
/* WARNING MODIFY THIS AT YOUR OWN RISK  */
/* Return to Payments template page. Currently this just shows the return to paayment information data block. */

if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php")) {
	require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php"); //This is the path to the template file if available
} else {
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/payment_overview.php");
}
	
if ($payment_status == "Completed" || $payment_status == "Refund") {
	//echo '<p class="payment_details payment_paid">'.__('Our records indicate you have paid','event_espresso')." ".$org_options['currency_symbol'].$event_cost."</p>";
}

if ($payment_status == "Pending") {

	if ($org_options['show_pending_payment_options'] == 'Y') {

		wp_register_script( 'espresso_payment_page', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/espresso_payment_page.js', array( 'jquery' ), '1.0', TRUE );
		wp_enqueue_script( 'espresso_payment_page' );	
?>
		<div class="event_espresso_attention">
			<strong class="payment_details payment_pending"><?php _e('Pending Payment', 'event_espresso');?></strong><br />
			<?php _e('Would you like to choose a different payment option?', 'event_espresso');?>
		</div>
<?php
		//We need create the variables for the payment options
		$registration_id = $registration_id != '' ? $registration_id : $att_registration_id;
		//echo '$registration_id = '.$registration_id;
		if ($attendee_id == '' || $attendee_id == 0)
			$attendee_id = espresso_attendee_id($registration_id);

		//Show payment options
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php")) {
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
		} else {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/gateway_display.php");
		}
	}
}

if ( $payment_status == "Incomplete" || $payment_status == "Payment Declined" || $payment_status == "" ) {

	wp_register_script( 'espresso_payment_page', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/espresso_payment_page.js', array( 'jquery' ), '1.0', TRUE );
	wp_enqueue_script( 'espresso_payment_page' );	

	//Check the number of available sapce against this registration
	if ( get_number_of_attendees_reg_limit( $event_id, 'number_available_spaces' ) < $quantity ) {
?>
		<p class="espesso_event_full"> <?php _e('Sorry, there are not enough spaces available to complete your registration.', 'event_espresso'); ?></p>
		<p class="espesso_event_full"> <?php _e('Quantity in your Party:', 'event_espresso'); ?> <?php echo $quantity ?></p>
		<p class="espesso_event_full"><?php _e('Spaces Available:', 'event_espresso'); ?> <?php echo get_number_of_attendees_reg_limit($event_id, 'avail_spaces_slash_reg_limit') ?></p>
<?php
		return;
	}
	//Uncomment to check the number of available spaces
	//echo get_number_of_attendees_reg_limit($event_id, 'number_available_spaces');
	//Show payment options
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php")) {
		require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
	} else {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/gateway_display.php");
	}
}//End if ($payment_status == ("Incomplete") )