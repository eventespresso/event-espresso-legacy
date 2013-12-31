<?php
function copy_event( $recurrence_array = array() ){

	global $wpdb, $current_user;
	
	$success = array();
	$errors = array();
	
//	$event_id = array_key_exists( 'event_id', $recurrence_array )? $recurrence_array['event_id'] : absint( $_REQUEST ['event_id'] );
	$event_id = absint( $_REQUEST ['event_id'] );
	
	$SQL = "SELECT * FROM ". EVENTS_DETAIL_TABLE ." WHERE id = %d";
	if ( $event = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id ))){
	
		//printr( $event, '$event  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' ); die();
				
		$columns_and_values = array(
		
				'event_code' 					=> uniqid( $current_user->ID.'-' ), 
				'event_name'					=> $event->event_name, 
				'event_desc'					=> $event->event_desc, 
				'display_desc'					=> $event->display_desc, 
				'display_reg_form'				=> $event->display_reg_form, 
				'event_identifier'				=> substr( $event->event_identifier, 0, 13 ) . uniqid('-'), 
				'start_date'					=> $event->start_date, 
				'end_date'						=> $event->end_date, 
				'registration_start'			=> $event->registration_start, 
				'registration_end'				=> $event->registration_end, 
				'registration_startT'			=> $event->registration_startT, 
				'registration_endT'				=> $event->registration_endT, 
				'phone'							=> $event->phone, 
				'virtual_url'					=> $event->virtual_url,				
				'virtual_phone'					=> $event->virtual_phone, 
				'reg_limit'						=> $event->reg_limit, 
				'allow_multiple'				=> $event->allow_multiple, 
				'additional_limit'				=> $event->additional_limit,
				'send_mail'						=> $event->send_mail, 
				'is_active'						=> $event->is_active, 
				
				'event_status'					=> $event->event_status, 
				'conf_mail'						=> $event->conf_mail, 
				'use_coupon_code'				=> $event->use_coupon_code, 
				'use_groupon_code'				=> $event->use_groupon_code,				
				'coupon_id'						=> $event->coupon_id,
				'member_only'					=> $event->member_only,				
				'post_id' 						=> apply_filters('filter_hook_espresso_existing_post_id', $event->post_id),//IF using this filter, just use NULL or 0 for the post_id
				'post_type' 					=> $event->post_type,				 
				'externalURL' 					=> $event->externalURL, 
				'early_disc' 					=> $event->early_disc,
				
				'early_disc_date' 				=> $event->early_disc_date, 
				'early_disc_percentage' 		=> $event->early_disc_percentage,				
				'question_groups' 				=> $event->question_groups, 
				'allow_overflow' 				=> $event->allow_overflow, 
				'overflow_event_id' 			=> $event->overflow_event_id, 
				'recurrence_id'					=> $event->recurrence_id, 
				'email_id' 						=> $event->email_id, 
				'alt_email' 					=> $event->alt_email,
				'event_meta' 					=> $event->event_meta, 
				'wp_user' 						=> $current_user->ID,
				
				'require_pre_approval' 			=> $event->require_pre_approval, 
				'timezone_string' 				=> $event->timezone_string, 
				'submitted' 					=> date('Y-m-d H:i:s', time()), 
				'ticket_id' 					=> $event->ticket_id,
				
				//Legacy venue information
				'address'						=> $event->address, 
				'address2'						=> $event->address2, 
				'city'							=> $event->city, 
				'state'							=> $event->state, 
				'zip'							=> $event->zip, 
				'country'						=> $event->country, 
				'phone'							=> $event->phone,
				
				'venue_phone'					=> $event->venue_phone,
				'venue_title'					=> $event->venue_title,
				'venue_url'						=> $event->venue_url,
				'venue_image'					=> $event->venue_image,

		);
		
		$data_formats = array(
				'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 
				'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', 
				'%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', 
				'%s', '%s', '%s', '%s', '%d', '%d', '%d', 
				'%s', '%s', '%d',//wp_user
				'%d', '%s', '%s', '%d',//Ticket id
				'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',//Legacy venue
		);
		
	}

	
	if ( $wpdb->insert( EVENTS_DETAIL_TABLE, $columns_and_values, $data_formats )) {

		$new_id = $wpdb->insert_id;

		$SQL = "SELECT * FROM ". EVENTS_CATEGORY_REL_TABLE ." WHERE event_id = %d ORDER BY id";
		if ( $event_categories = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ))){
			foreach ( $event_categories as $category ) {
				if( ! empty( $category->cat_id )) {
					$columns_and_values = array( 'event_id' => $new_id, 'cat_id' => $category->cat_id );
					$data_formats = array( '%d', '%d'	);
					if ( ! $wpdb->insert( EVENTS_CATEGORY_REL_TABLE, $columns_and_values, $data_formats )) {
						$error[] = __('An error occured. Event category ID#','event_espresso') . $category->cat_id . __(' was not saved.','event_espresso');
					}
				}
			}
		}


		$SQL = "SELECT * FROM ". EVENTS_VENUE_REL_TABLE ." WHERE event_id = %d ORDER BY id";
		if ( $event_venues = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ))){
			foreach ( $event_venues as $venue ) {
				if( ! empty( $venue->venue_id )) {
					$columns_and_values = array( 'event_id' => $new_id, 'venue_id' => $venue->venue_id );
					$data_formats = array( '%d', '%d'	);
					if ( ! $wpdb->insert( EVENTS_VENUE_REL_TABLE, $columns_and_values, $data_formats )) {
						$error[] = __('An error occured. Event venue ID#','event_espresso') . $venue->venue_id . __(' was not saved.','event_espresso');
					}
				}
			}
		}


		$SQL = "SELECT * FROM ". EVENTS_PERSONNEL_REL_TABLE ." WHERE event_id = %d ORDER BY id";
		if ( $event_persons = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ))){
			foreach ( $event_persons as $person ) {
				if( ! empty( $person->person_id )) {
					$columns_and_values = array( 'event_id' => $new_id, 'person_id' => $person->person_id );
					$data_formats = array( '%d', '%d'	);
					if ( ! $wpdb->insert( EVENTS_PERSONNEL_REL_TABLE, $columns_and_values, $data_formats )) {
						$error[] = __('An error occured. Event person ID#','event_espresso') . $person->person_id . __(' was not saved.','event_espresso');
					}
				}
			}
		}


		$SQL = "SELECT * FROM ". EVENTS_DISCOUNT_REL_TABLE ." WHERE event_id = %d ORDER BY id";
		if ( $event_discounts = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ))){
			foreach ( $event_discounts as $discount ) {
				if( ! empty( $discount->discount_id )) {
					$columns_and_values = array( 'event_id' => $new_id, 'discount_id' => $discount->discount_id );
					$data_formats = array( '%d', '%d'	);
					if ( ! $wpdb->insert( EVENTS_DISCOUNT_REL_TABLE, $columns_and_values, $data_formats )) {
						$error[] = __('An error occured. Event discount ID#','event_espresso') . $discount->discount_id . __(' was not saved.','event_espresso');
					}
				}
			}
		}


		$SQL = "SELECT * FROM ". EVENTS_START_END_TABLE ." WHERE event_id = %d ORDER BY id";
		if ( $event_times = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ))){
			foreach ( $event_times as $event_time ) {
				if ( ! empty( $event_time )) {
					$columns_and_values = array( 
							'event_id' => $new_id, 
							'start_time' => $event_time->start_time, 
							'end_time' => $event_time->end_time, 
							'reg_limit' => $event_time->reg_limit 
					);
					$data_formats = array( '%d', '%s', '%s', '%d'	);
					if ( ! $wpdb->insert( EVENTS_START_END_TABLE, $columns_and_values, $data_formats )) {
						$error[] = __('An error occured. Event time details with ID#','event_espresso') . $event_time->id . __(' were not saved.','event_espresso');
					}
				}
			}
		}


		$SQL = "SELECT * FROM ". EVENTS_PRICES_TABLE ." WHERE event_id = %d ORDER BY id";
		if ( $event_prices = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ))){
			foreach ( $event_prices as $event_price ) {
				if ( ! empty( $event_price )) {
					$columns_and_values = array( 
							'event_id' => $new_id, 
							'price_type' => stripslashes_deep($event_price->price_type), 
							'event_cost' => $event_price->event_cost, 
							'surcharge' => $event_price->surcharge, 
							'surcharge_type' => $event_price->surcharge_type, 
							'member_price' => $event_price->member_price, 
							'member_price_type' => stripslashes_deep($event_price->member_price_type), 
							'max_qty' => $event_price->max_qty, 
							'max_qty_members' => $event_price->max_qty_members 
					);
					$data_formats = array( '%d', '%s', '%f', '%f', '%s', '%f', '%s', '%d', '%d' );
					if ( ! $wpdb->insert( EVENTS_PRICES_TABLE, $columns_and_values, $data_formats )) {
						$error[] = __('An error occured. Event price details with ID#','event_espresso') . $event_price->id . __(' were not saved.','event_espresso');
					}
				}
			}
		}


	} else {
		$error[] = __('An error occured. The venue  was not saved.','event_espresso'); 
	}

	if ( empty( $error )) {
		$event_url = add_query_arg( array( 'action' => 'edit', 'event_id' => $event_id ), admin_url( 'admin.php?page=events' ));
		$success[] =  __('The event','event_espresso') . ' <a href="' . $event_url . '">' . stripslashes( $event->event_name ) . '</a> ' . __('has been successfully copied. You are now editing the copy.','event_espresso');
	}

	if ( ! empty( $success )) : 
?>
	<div id="message" class="updated fade">
	<?php foreach ( $success as $msg ) { ?>
		<p><strong><?php echo $msg;?></strong></p>
	<?php } ?>
	</div>
	
<?php	
	endif;
		
	if ( ! empty( $error )) : 
?>
	<div id="message" class="error">
	<?php foreach ( $error as $msg ) { ?>
		<p><strong><?php echo $msg;?></strong></p>
	<?php } ;?>
	</div>
	
<?php	
		endif;
	return $new_id;
}