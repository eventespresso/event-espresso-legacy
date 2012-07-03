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
			<td><?php echo $payment_status ?></td>
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
			echo apply_filters('filter_hook_espresso_display_add_to_calendar_by_attendee_id', $attendee_id);
			?>
		</tr>
	</table>
	</div>
</div><!-- / .event-display-boxes -->
