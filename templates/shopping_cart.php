<?php

if ( !function_exists( 'event_espresso_shopping_cart' ) ){

		function event_espresso_shopping_cart() {
			global $wpdb, $org_options;
			//session_destroy();
			//echo "<pre>", print_r( $_SESSION ), "</pre>";
			$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );
			
			if ( event_espresso_invoke_cart_error( $events_in_session )) {
				return false;
			}
				
			$events_IN = array();

			if ( count( $events_in_session ) > 0 ){
				foreach ( $events_in_session as $event ) {
					//echo '<h4>$event[id] : ' . $event['id'] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					if ( is_numeric( $event['id'] ) )
						$events_IN[] = $event['id'];
				}

			$events_IN = implode( ',', $events_IN );
			
			if ( empty( $events_IN )) {
				return FALSE;
			}

			$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
			$sql = apply_filters( 'filter_hook_espresso_shopping_cart_SQL_select', $sql );
			$sql .= " WHERE e.id in ($events_IN) ";
			$sql .= " AND e.event_status != 'D' ";
			$sql .= " ORDER BY e.start_date ";

			$result = $wpdb->get_results( $sql );
			
			$reg_page_url = add_query_arg('regevent_action', 'load_checkout_page', get_permalink($org_options['event_page_id']));

?>

<form action="<?php echo $reg_page_url ?>" method='post' id="event_espresso_shopping_cart">

<?php
		$counter = 1; //Counter that will keep track of the first events
		foreach ( $result as $r ){
			
			$r = apply_filters( 'filter_hook_espresso_shopping_cart_event', $r );
			
			//Check to see if the Members plugin is installed.
			if ( function_exists('espresso_members_installed') && espresso_members_installed() == true && !is_user_logged_in() ) {
				$member_options = get_option('events_member_settings');
				if ($r->member_only == 'Y' || $member_options['member_only_all'] == 'Y'){
					event_espresso_user_login();
					return;
				}
			}
			//If the event is still active, then show it.
			if (event_espresso_get_status($r->id) == 'ACTIVE') {
				$num_attendees = get_number_of_attendees_reg_limit( $r->id, 'num_attendees' ); //Get the number of attendees
				$available_spaces = get_number_of_attendees_reg_limit( $r->id, 'available_spaces' ); //Gets a count of the available spaces
				$number_available_spaces = get_number_of_attendees_reg_limit( $r->id, 'number_available_spaces' ); //Gets the number of available spaces
				//echo "<pre>$r->id, $num_attendees,$available_spaces,$number_available_spaces</pre>";
		?>
				<div class="multi_reg_cart_block event-display-boxes ui-widget"  id ="multi_reg_cart_block-<?php echo $r->id ?>">
		
					<h3 class="event_title ui-widget-header ui-corner-top"><?php echo stripslashes_deep( $r->event_name ) ?> <span class="remove-cart-item"> <img class="ee_delete_item_from_cart" id="cart_link_<?php echo $r->id ?>" alt="Remove this item from your cart" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/remove.gif" /> </span> </h3>
						<div class="event-data-display ui-widget-content ui-corner-bottom">
							<table id="cart-reg-details" class="event-display-tables">
								<thead>
									<tr>
										<th><?php _e( 'Date', 'event_espresso' ); ?></th>
										<th><?php _e( 'Time', 'event_espresso' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<?php echo event_date_display( $r->start_date, get_option( 'date_format' ) ) ?>
											<?php /*_e( ' to ', 'event_espresso' ); ?> <?php echo event_date_display( $r->end_date, get_option( 'date_format' ) )*/ ?>
										</td>
										<td>
											<?php echo event_espresso_time_dropdown( $r->id, 0, 1, $_SESSION['espresso_session']['events_in_session'][$r->id]['start_time_id'] ); ?>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<?php echo event_espresso_group_price_dropdown( $r->id, 0, 1, $_SESSION['espresso_session']['events_in_session'][$r->id]['price_id']); ?>
										</td>
									</tr>
								</tbody>
							</table>
		
						<input type="hidden" name="event_name[<?php echo $r->id; ?>]" value="<?php echo $r->event_name; ?>" />
						<input type="hidden" name="use_coupon[<?php echo $r->id; ?>]" value="<?php echo $r->use_coupon_code; ?>" />
						<input type="hidden" name="use_groupon[<?php echo $r->id; ?>]" value="<?php echo $r->use_groupon_code; ?>" />
						<?php do_action_ref_array( 'action_hook_espresso_add_to_multi_reg_cart_block', array( $r ) ); ?>
						
					</div><!-- / .event-data-display -->
				</div><!-- / .event-display-boxes -->
		
				<?php
				$counter++;
			}
		}
		//echo $_SESSION['espresso_session']['groupon_used'];
//		printr( $_SESSION, '$_SESSION  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		?>
		<div class="event-display-boxes ui-widget">
			<div class="mer-event-submit ui-widget-content ui-corner-all">
				<input type="hidden" name="event_name[<?php echo $r->id; ?>]" value="<?php echo stripslashes_deep( $r->event_name ); ?>" />
				<input type="hidden" name="regevent_action" value="load_checkout_page" />
					
			<?php if ( function_exists( 'event_espresso_coupon_payment_page' ) && isset($org_options['allow_mer_discounts']) && $org_options['allow_mer_discounts'] == 'Y' ) : //Discount code display ?>
			<div id="event_espresso_coupon_wrapper" class="clearfix event-data-display">
				<label class="coupon-code" for="event_espresso_coupon_code"><?php _e( 'Enter Coupon Code ', 'event_espresso' ); ?></label>
				<input type="text" 
							name="event_espresso_coupon_code" 
							id ="event_espresso_coupon_code" 
							value="<?php echo isset( $_SESSION['espresso_session']['event_espresso_coupon_code'] ) ? $_SESSION['espresso_session']['event_espresso_coupon_code'] : ''; ?>"
							onkeydown="if(event.keyCode==13) {document.getElementById('event_espresso_refresh_total').focus(); return false;}" 
						/>
			</div>
			<?php endif; ?>
			
			<?php if ( function_exists( 'event_espresso_groupon_payment_page' ) && isset($org_options['allow_mer_vouchers']) && $org_options['allow_mer_vouchers'] == 'Y' ) : //Voucher code display ?>
			<div id="event_espresso_coupon_wrapper" class="clearfix event-data-display" >
				<label class="coupon-code" for="event_espresso_groupon_code"><?php _e( 'Enter Voucher Code ', 'event_espresso' ); ?></label>
				<input type="text" 
							name="event_espresso_groupon_code" 
							id ="event_espresso_groupon_code" 
							value="<?php echo isset( $_SESSION['espresso_session']['groupon_code'] ) ? $_SESSION['espresso_session']['groupon_code'] : ''; ?>"
							onkeydown="if(event.keyCode==13) {document.getElementById('event_espresso_refresh_total').focus(); return false;}" 
						/>
			</div>
			<?php endif; ?>
			
             <div id="event_espresso_notifications" class="clearfix event-data-display" style=""></div> 			

			<div id="event_espresso_total_wrapper" class="clearfix event-data-display">					
				<?php do_action( 'action_hook_espresso_shopping_cart_before_total' ); ?>				
				<span class="event_total_price">
					<?php echo __( 'Total ', 'event_espresso' ) . $org_options['currency_symbol'];?> <span id="event_total_price"><?php echo $_SESSION['espresso_session']['grand_total'];?></span>
				</span>
				<?php do_action( 'action_hook_espresso_shopping_cart_after_total' ); ?>
				<p id="event_espresso_refresh_total">
				<a id="event_espresso_refresh_total" style="cursor:pointer;"><?php _e( 'Refresh Total', 'event_espresso' ); ?></a>
			</p>
			</div>

			
			<p id="event_espresso_submit_cart">
				<input type="submit" class="submit btn_event_form_submit ui-priority-primary ui-state-default ui-state-hover ui-state-focus ui-corner-all" name="Continue" id="event_espresso_continue_registration" value="<?php _e( 'Enter Attendee Information', 'event_espresso' ); ?>&nbsp;&raquo;" />
			</p>
			
		</div><!-- / .mer-event-submit -->
	</div><!-- / .event-display-boxes -->
</form>
<?php

			}
		}
}
