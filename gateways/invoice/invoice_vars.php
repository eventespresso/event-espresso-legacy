<?php

$invoice_type = '';
 if ($_REQUEST['regevent_action'] == 'post_multi_attendee')
    $invoice_type = 'multi_invoice_';
 
// Setup payent page
echo '<!--Event Espresso Default Gateway Version ' . $default_gateway_version . '-->';
			
$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');
?>
<hr />
<h3><?php echo $invoice_payment_settings['invoice_title'] ?></h3>
<p><a href="<?php echo home_url(); ?>/?invoice_type=<?php echo $invoice_type; ?>&amp;download_invoice=true&amp;attendee_id=<?php echo $attendee_id ?>&amp;registration_id=<?php echo $registration_id ?>" target="_blank"><?php _e('Download PDF Invoice', 'event_espresso'); ?></a></p>
<p><?php echo $invoice_payment_settings['invoice_instructions']?></p>
<?php echo wpautop($invoice_payment_settings['payment_address'])?>


