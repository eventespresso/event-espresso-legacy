<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	?>
<div class="espresso_payment_overview event-display-boxes ui-widget" >
  <h3 class="section-heading ui-widget-header ui-corner-top">
		<?php _e('Payment Overview', 'event_espresso'); ?>
  </h3>
	<div class="event-data-display ui-widget-content ui-corner-bottom" >
  <table>
		<tr>
			<td><?php _e('Class/Event:', 'event_espresso'); ?></td>
			<td><?php echo stripslashes_deep($event_link) ?></td>
		</tr>
		<tr>
			<td><?php _e('Primary Registrant:', 'event_espresso'); ?></td>
			<td><?php echo stripslashes_deep($fname . ' ' . $lname) ?></td>
		</tr>
		<tr>
			<?php echo $txn_type == '' ? '' : '<td>' . __('Payment Type:', 'event_espresso') . '</td> <td>' . espresso_payment_type($txn_type) . '</td>'; ?> <?php echo ($payment_date == '' || ($payment_status == 'Pending' && (espresso_payment_type($txn_type) == 'Invoice' || espresso_payment_type($txn_type) == 'Offline payment'))) ? '' : '<tr><td>' . __('Payment Date:', 'event_espresso') . '</td> <td>' . event_date_display($payment_date) . '</td></tr>'; ?>
		</tr>
		<tr>
			<td><?php _e('Amount Paid/Owed:', 'event_espresso'); ?></td>
			<td><?php echo $org_options['currency_symbol'] ?><?php echo $total_cost ?>
				<?php event_espresso_paid_status_icon($payment_status) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Payment Status:', 'event_espresso'); ?>
			</td>
			<?php // localize all the things!
				switch( $payment_status ) {
					case 'Pending':
						$payment_status = __( 'Pending', 'event_espresso' );
						break;
					case 'Incomplete':
						$payment_status = __( 'Incomplete', 'event_espresso' );
						break;
					case 'Completed':
						$payment_status = __( 'Completed', 'event_espresso' );
						break;
					case 'Payment Declined':
						$payment_status = __( 'Payment Declined', 'event_espresso' );
						break;
				}
			?>
			<td><?php echo $payment_status; ?></td>
    </tr>
		<tr>
			<td>
				<?php _e('Registration ID:', 'event_espresso'); ?>
			</td>
			<td><?php echo $registration_id ?></td>
		</tr>
		<tr>
			<?php
			echo $txn_id == '' ? '' : '<td>' . __('Transaction ID:', 'event_espresso') . '</td> <td>' . $txn_id . '</td>';
			?>
		</tr>
	</table>
	</div>
</div><!-- / .event-display-boxes -->
