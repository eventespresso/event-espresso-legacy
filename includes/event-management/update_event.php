<?php
if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	

// TODO find out why $post_content is only added to the first post in case of a recurring event

function update_event($recurrence_arr = array()) {
    //print_r($_REQUEST);
    global $wpdb, $org_options, $current_user, $espresso_premium;

	//Security check using nonce
	if ( empty($_POST['nonce_verify_update_event']) || !wp_verify_nonce($_POST['nonce_verify_update_event'],'espresso_verify_update_event_nonce') ){
		if ($recurrence_arr['bypass_nonce'] == FALSE){
			print '<h3 class="error">'.__('Sorry, there was a security error and your event was not updated.', 'event_espresso').'</h3>';
			return;
		}
	}
	
    $wpdb->show_errors();
    /*
     * Begin Recurrence handling
     *
     * Will clean up in V 1.2.0
     *
     */
    if (defined('EVENT_ESPRESSO_RECURRENCE_TABLE')) {
        require_once(EVENT_ESPRESSO_RECURRENCE_FULL_PATH . "functions/re_functions.php");

        if ($_POST['recurrence_id'] > 0) {
            /*
             * If the array is empty, then find the recurring dates
             */
            if (count($recurrence_arr) == 0) {

                // Prepare the parameters array for use with various RE functions
                $re_params = array(
                    'start_date'					=> !empty($_POST['recurrence_start_date']) ? sanitize_text_field($_POST['recurrence_start_date']) :'',
                    'event_end_date'				=> !empty($_POST['recurrence_event_end_date']) ? sanitize_text_field($_POST['recurrence_event_end_date']) : '',
                    'end_date'						=> !empty($_POST['recurrence_end_date']) ? sanitize_text_field($_POST['recurrence_end_date']) : '',
                    'registration_start'			=> !empty($_POST['recurrence_regis_start_date']) ? sanitize_text_field($_POST['recurrence_regis_start_date']) : '',
                    'registration_end'				=> !empty($_POST['recurrence_regis_end_date']) ? sanitize_text_field($_POST['recurrence_regis_end_date']) : '',
                    'frequency'						=> !empty($_POST['recurrence_frequency']) ? sanitize_text_field($_POST['recurrence_frequency']) : '',
                    'interval'						=> !empty($_POST['recurrence_interval']) ? sanitize_text_field($_POST['recurrence_interval']) : '',
                    'recurrence_type'				=> !empty($_POST['recurrence_type']) ? sanitize_text_field($_POST['recurrence_type']) : '',
                    'weekdays'						=> !empty($_POST['recurrence_weekday']) ? $_POST['recurrence_weekday'] : '',
                    'repeat_by'						=> !empty($_POST['recurrence_repeat_by']) ? $_POST['recurrence_repeat_by'] : '',
                    'recurrence_manual_dates'		=> !empty($_POST['recurrence_manual_dates']) ? $_POST['recurrence_manual_dates'] : '',
                    'recurrence_manual_end_dates'	=> !empty($_POST['recurrence_manual_end_dates']) ? $_POST['recurrence_manual_end_dates'] : '',
                    'recurrence_id'					=> !empty($_POST['recurrence_id']) ? $_POST['recurrence_id'] : '',
					'recurrence_regis_date_increment' => !empty($_POST['recurrence_regis_date_increment']) ? $_POST['recurrence_regis_date_increment'] : '',
                );

                //$re_params['adding_to_db'] = 'Y';
                //Has the form been modified
                $recurrence_form_modified = recurrence_form_modified($re_params);

                //echo ($recurrence_form_modified) ? "Yes" : 'No';


                if ($_POST['recurrence_apply_changes_to'] == 2) {
                    //Update all events in the series based on recurrence id
                    $recurrence_dates = ($_POST['recurrence_type'] == 'm') ? find_recurrence_manual_dates($re_params) : find_recurrence_dates($re_params);

                     $UPDATE_SQL = "SELECT id,start_date,event_identifier FROM " . EVENTS_DETAIL_TABLE . " WHERE recurrence_id = %d AND NOT event_status = 'D'";
                } else {
                    //Update this and upcoming events based on recurrence id and start_date >=start_date
                    $re_params['start_date'] = sanitize_text_field($_POST['start_date']);
                    $recurrence_dates = find_recurrence_dates($re_params);
                    $UPDATE_SQL = "SELECT id,start_date,event_identifier FROM " . EVENTS_DETAIL_TABLE . " WHERE start_date >='" . sanitize_text_field($_POST['start_date']) . "' AND recurrence_id = %d and NOT event_status = 'D' ";
                }
				

                //Recurrence Form modified and changes need to apply to all
                if ($recurrence_form_modified && $_POST['recurrence_apply_changes_to'] > 1) {

                    //Update the recurrence table record with the new RE selections
                    update_recurrence_master_record();

                    /*
                     * Delete the records that don't belong in the formula
                     */

                    if (count($recurrence_dates) > 0) {
                        $delete_in = '';
                        foreach ($recurrence_dates as $k => $v) {
                            $delete_in .= "'" . $k . "',";
                        }
                        $delete_in = substr($delete_in, 0, -1);
                    }


                    if ($_POST['recurrence_apply_changes_to'] == 2) {
                        //Update all events in the series based on recurrence id
                        //$DEL_SQL = 'UPDATE ' . EVENTS_DETAIL_TABLE . " SET event_status = 'D' WHERE start_date NOT IN (" . $delete_in . ") AND recurrence_id = " . $_POST['recurrence_id'];
                        $DEL_SQL = 'DELETE EDT, EAT FROM ' . EVENTS_DETAIL_TABLE . " EDT
                            LEFT JOIN " . EVENTS_ATTENDEE_TABLE . " EAT
                                ON EDT.id = EAT.event_id
                            WHERE EAT.id IS NULL
                            AND EDT.start_date NOT IN (" . $delete_in . ")
                            AND recurrence_id = " . sanitize_text_field($_POST['recurrence_id']);

                        $UPDATE_SQL = "SELECT id,start_date,event_identifier FROM " . EVENTS_DETAIL_TABLE . " WHERE recurrence_id = %d and NOT event_status = 'D' ORDER BY start_date";
                    } else {
                       $DEL_SQL = 'DELETE EDT, EAT FROM ' . EVENTS_DETAIL_TABLE . " EDT
                            LEFT JOIN " . EVENTS_ATTENDEE_TABLE . " EAT
                                ON EDT.id = EAT.event_id
                            WHERE EAT.id IS NULL
                            AND EDT.start_date >='" . esc_sql(sanitize_text_field($_POST['start_date'])) . "'
                            AND EDT.start_date NOT IN (" . $delete_in . ")
                            AND recurrence_id = " . $_POST['recurrence_id'];
                        $UPDATE_SQL = "SELECT id,start_date,event_identifier FROM " . EVENTS_DETAIL_TABLE . " WHERE start_date >='" . sanitize_text_field($_POST['start_date']) . "' AND recurrence_id = %d AND NOT event_status = 'D'  ORDER BY start_date";
                    }

                    if ($delete_in != '')
                        $wpdb->query($wpdb->prepare($DEL_SQL, NULL));

                    /*
                     * Add the new records based on the new formula
                     * The $recurrence_dates array will contain the new dates
                     */
                    if (!function_exists('add_event_to_db')) {
                        require_once ('insert_event.php');
                    }

                    foreach ($recurrence_dates as $k => $v) {
                        $result = $wpdb->get_row($wpdb->prepare("SELECT ID FROM " . EVENTS_DETAIL_TABLE . " WHERE recurrence_id = %d and start_date = %s and NOT event_status = 'D'", array($_POST['recurrence_id'], $k)));

                        if ($wpdb->num_rows == 0) {
                            add_event_to_db(array(
                                'recurrence_id' => sanitize_text_field($_POST['recurrence_id']),
                                'recurrence_start_date' => $v['start_date'],
                                'recurrence_event_end_date' => $v['event_end_date'],
                                'recurrence_end_date' => $v['start_date'],
                                'registration_start' => $v['registration_start'],
                                'registration_end' => $v['registration_end'],
								'bypass_nonce'				=> TRUE,
								
								));
                        } else {

                        }
                    }

                    /*
                     * Find all the event ids in the series and feed into the $recurrence_dates array
                     * This array will be used at the end of this document to invoke the recursion of update_event function so all the events in the series
                     * can be updated with the information.
                     */
                }

                $result = $wpdb->get_results($wpdb->prepare($UPDATE_SQL, array(sanitize_text_field($_POST['recurrence_id']))));				
                foreach ($result as $row) {
                    if ($row->start_date != '') {
                        $recurrence_dates[$row->start_date]['event_id'] = $row->id;
                        $recurrence_dates[$row->start_date]['event_identifier'] = $row->event_identifier;
                    }
                }
            }
        }
    }

    //  echo_f('rd',$recurrence_dates);


    if (defined('EVENT_ESPRESSO_RECURRENCE_MODULE_ACTIVE') &&
        !empty($_POST['recurrence']) && $_POST['recurrence'] == 'Y' &&
        count($recurrence_arr) == 0 && $_POST['recurrence_apply_changes_to'] > 1) {
//skip the first update
    } else {
		
		//Filters the event description based on user level
		$user_access = apply_filters( 'filter_hook_espresso_event_unfiltered_description', current_user_can('administrator') );
		$_REQUEST['event_desc'] = is_admin() || $user_access ? $_REQUEST['event_desc'] : apply_filters( 'filter_hook_espresso_event_wp_kses_post_description', wp_kses_post( $_REQUEST['event_desc'] ) );
		
        $event_meta = array(); //will be used to hold event meta data
        $event_id						= array_key_exists('event_id', $recurrence_arr) ? $recurrence_arr['event_id'] : (int)$_REQUEST['event_id'];
        $event_name						= sanitize_text_field($_REQUEST['event']);
        $event_desc						= !empty($_REQUEST['event_desc']) ? $_REQUEST['event_desc'] : '';
        $display_desc					= sanitize_text_field($_REQUEST['display_desc']);
        $display_reg_form				= sanitize_text_field($_REQUEST['display_reg_form']);
		$externalURL					= !empty($_REQUEST['externalURL']) ? esc_html($_REQUEST['externalURL']):'';
        $reg_limit						= (int)$_REQUEST['reg_limit'];
        $allow_multiple					= $_REQUEST['allow_multiple'];
        $overflow_event_id				= (empty($_REQUEST['overflow_event_id'])) ? '0' : (int)$_REQUEST['overflow_event_id'];
        $allow_overflow					= empty($_REQUEST['allow_overflow']) ? 'N' : sanitize_text_field($_REQUEST['allow_overflow']);
        $additional_limit				= !empty($_REQUEST['additional_limit']) && $_REQUEST['additional_limit'] > 0 ? (int)$_REQUEST['additional_limit'] : '5';
        $member_only					= empty($_REQUEST['member_only']) ? 'N' : sanitize_text_field($_REQUEST['member_only']);
		$is_active						= !empty($_REQUEST['is_active']) ? sanitize_text_field($_REQUEST['is_active']) : 'Y';
        $event_status					= !empty($_REQUEST['event_status']) ? sanitize_text_field($_REQUEST['event_status']) : 'A';
       
	    //Get the first instance of the start and end times
        $start_time						= sanitize_text_field($_REQUEST['start_time'][0]);
        $end_time						= sanitize_text_field($_REQUEST['end_time'][0]);

        // Add registration times
        $registration_startT			= event_date_display(sanitize_text_field($_REQUEST['registration_startT']), 'H:i');
        $registration_endT				= event_date_display(sanitize_text_field($_REQUEST['registration_endT']), 'H:i');

        //Add timezone
        $timezone_string				= empty($_REQUEST['timezone_string']) ? '' : sanitize_text_field($_REQUEST['timezone_string']);

        //Early discounts
        $early_disc						= !empty($_REQUEST['early_disc']) ? sanitize_text_field($_REQUEST['early_disc']) : '';
        $early_disc_date				= !empty($_REQUEST['early_disc_date']) ? sanitize_text_field($_REQUEST['early_disc_date']) : '';
        $early_disc_percentage			= !empty($_REQUEST['early_disc_percentage']) ? sanitize_text_field($_REQUEST['early_disc_percentage']) : '';

        $conf_mail						= esc_html($_REQUEST['conf_mail']);
        $use_coupon_code				= !empty($_REQUEST['use_coupon_code']) ? sanitize_text_field($_REQUEST['use_coupon_code']) : '';
        $alt_email						= isset($_REQUEST['alt_email']) && !empty($_REQUEST['alt_email']) ? sanitize_text_field($_REQUEST['alt_email']) : '';

        $send_mail						= sanitize_text_field($_REQUEST['send_mail']);
        $email_id						= isset($_REQUEST['email_name']) ? (int)$_REQUEST['email_name'] : '0';
		
		$ticket_id						= isset($_REQUEST['ticket_id']) ? (int)$_REQUEST['ticket_id'] : '0';
		

        $event_category = empty($_REQUEST['event_category']) ? '' : serialize(sanitize_text_field($_REQUEST['event_category']));
        $event_discount = empty($_REQUEST['event_discount']) ? '' : serialize(sanitize_text_field($_REQUEST['event_discount']));

        $registration_start				= array_key_exists('registration_start', $recurrence_arr) ? $recurrence_arr['registration_start'] : sanitize_text_field($_REQUEST['registration_start']);
        $registration_end				= array_key_exists('registration_end', $recurrence_arr) ? $recurrence_arr['registration_end'] : sanitize_text_field($_REQUEST['registration_end']);

        $start_date						= array_key_exists('recurrence_start_date', $recurrence_arr) ? $recurrence_arr['recurrence_start_date'] : (empty($_REQUEST['start_date']) ? $_REQUEST['recurrence_start_date'] : sanitize_text_field($_REQUEST['start_date']));
        $end_date						= array_key_exists('recurrence_event_end_date', $recurrence_arr) ? $recurrence_arr['recurrence_event_end_date'] : (empty($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['recurrence_start_date']) : sanitize_text_field($_REQUEST['end_date']));
		
		$question_groups				= serialize($_REQUEST['question_groups']);
        $add_attendee_question_groups	= empty($_REQUEST['add_attendee_question_groups']) ? '' : $_REQUEST['add_attendee_question_groups'];
				
        //Venue Information
        $venue_title = isset($_REQUEST['venue_title']) ? sanitize_text_field($_REQUEST['venue_title']):'';
        $venue_url = isset($_REQUEST['venue_url']) ? sanitize_text_field($_REQUEST['venue_url']):'';
        $venue_phone = isset($_REQUEST['venue_phone']) ? sanitize_text_field($_REQUEST['venue_phone']):'';
        $venue_image = isset($_REQUEST['venue_image']) ? sanitize_text_field($_REQUEST['venue_image']):'';
		
		//Virtual location
        $virtual_url = isset($_REQUEST['virtual_url']) ? sanitize_text_field($_REQUEST['virtual_url']):'';
        $virtual_phone = isset($_REQUEST['virtual_phone']) ? sanitize_text_field($_REQUEST['virtual_phone']):'';

		//Address/venue information
		$address = !empty($_REQUEST['address']) ? sanitize_text_field($_REQUEST['address']):'';
        $address2 = !empty($_REQUEST['address2']) ? sanitize_text_field($_REQUEST['address2']):'';
        $city = !empty($_REQUEST['city']) ? sanitize_text_field($_REQUEST['city']):'';
        $state = !empty($_REQUEST['state']) ? sanitize_text_field($_REQUEST['state']):'';
        $zip = !empty($_REQUEST['zip']) ? sanitize_text_field($_REQUEST['zip']):'';
        $country = !empty($_REQUEST['country']) ? sanitize_text_field($_REQUEST['country']):'';
        $phone = !empty($_REQUEST['phone']) ? sanitize_text_field($_REQUEST['phone']):'';
		
		$event_location		= '';
		if ( !empty($address) )
			$event_location	.= $address . ' ';
		
		if ( !empty($address2) )
			$event_location .= '<br />' . $address2;
		
		if ( !empty($city) )
			$event_location .= '<br />' . $city;
			
		if ( !empty($state) )
			$event_location .= ', ' . $state;
		
		if ( !empty($zip) )
			$event_location .= ', ' . $state;
			
		if ( !empty($country) )
			$event_location .= '<br />' . $country;
        

        if (isset($reg_limit) && empty($reg_limit)) {
            $reg_limit = 999999;
        }

        

        $event_meta['default_payment_status'] = !empty($_REQUEST['default_payment_status']) ? sanitize_text_field($_REQUEST['default_payment_status']) : '';
        $event_meta['venue_id'] = empty($_REQUEST['venue_id']) ? '' : (int)$_REQUEST['venue_id'][0];
        $event_meta['additional_attendee_reg_info'] = !empty($_REQUEST['additional_attendee_reg_info']) ? sanitize_text_field($_REQUEST['additional_attendee_reg_info']) : '';
		$event_meta['add_attendee_question_groups'] = $add_attendee_question_groups;
        $event_meta['date_submitted'] = sanitize_text_field($_REQUEST['date_submitted']);
		
		//Added for seating chart addon
		if ( isset($_REQUEST['seating_chart_id']) ){
			$cls_seating_chart = new seating_chart();
			$seating_chart_result = $cls_seating_chart->associate_event_seating_chart((int)$_REQUEST['seating_chart_id'],$event_id);
			$tmp_seating_chart_id = (int)$_REQUEST['seating_chart_id'];
			if ( $tmp_seating_chart_id > 0 ){
				if ( $seating_chart_result === false ){
					$tmp_seating_chart_row = $wpdb->get_row($wpdb->prepare("select seating_chart_id from ".EVENTS_SEATING_CHART_EVENT_TABLE." where event_id = $event_id", NULL));
					if ( $tmp_seating_chart_row !== NULL ){
						$tmp_seating_chart_id = $tmp_seating_chart_row->seating_chart_id;
					}else{
						$tmp_seating_chart_id = 0;
					}
					
				}
				
				if ( $_REQUEST['allow_multiple'] == 'Y' && isset($_REQUEST['seating_chart_id']) && $tmp_seating_chart_id > 0 ){
					$event_meta['additional_attendee_reg_info'] = 3;
				}
			}
		}

		//Process thumbnail image
		$event_thumbnail_url = '';
		if (isset($_REQUEST['upload_image']) && !empty($_REQUEST['upload_image']) ){
			 $event_meta['event_thumbnail_url'] = sanitize_text_field($_REQUEST['upload_image']);
			 $event_thumbnail_url = sanitize_text_field($event_meta['event_thumbnail_url']);
		}
			
        if (!empty($_REQUEST['emeta'])) {
            foreach ($_REQUEST['emeta'] as $k => $v) {
                $event_meta[$v] = sanitize_text_field($_REQUEST['emetad'][$k]);
            }
        }
		
		//Filter to update the event meta as needed
		$event_meta = apply_filters('filter_hook_espresso_update_event_update_meta', $event_meta, $event_id);
		
		//print_r($_REQUEST['emeta'] );
        $event_meta = serialize($event_meta);
        ############ Added by wp-developers ######################
        $require_pre_approval = 0;
        if (isset($_REQUEST['require_pre_approval'])) {
            $require_pre_approval = sanitize_text_field($_REQUEST['require_pre_approval']);
        }

        ################# END #################
        //When adding colums to the following arrays, be sure both arrays have equal values.
        $sql = array(
			'event_name'				=> $event_name,
			'event_desc'				=> $event_desc,
			'display_desc'				=> $display_desc,
			'display_reg_form'			=> $display_reg_form,
            'address'					=> $address,
			'address2'					=> $address2,
			'city'						=> $city,
			'state'						=> $state,
			'zip'						=> $zip,
			'country'					=> $country,
			'phone'						=> $phone,
			'virtual_url'				=> $virtual_url,
            'virtual_phone'				=> $virtual_phone,
			'venue_title'				=> $venue_title,
			'venue_url'					=> $venue_url,
			'venue_phone'				=> $venue_phone,
			'venue_image'				=> $venue_image,
            'registration_start'		=> $registration_start,
			'registration_end'			=> $registration_end,
			'start_date'				=> $start_date,
			'end_date'					=> $end_date,
            'allow_multiple'			=> $allow_multiple,
			'send_mail'					=> $send_mail,
			'is_active'					=> $is_active,
			'event_status'				=> $event_status,
            'conf_mail'					=> $conf_mail,
			'use_coupon_code'			=> $use_coupon_code,
			'member_only'				=> $member_only,
			'externalURL'				=> $externalURL,
            'early_disc'				=> $early_disc,
			'early_disc_date'			=> $early_disc_date,
			'early_disc_percentage'		=> $early_disc_percentage,
			'alt_email'					=> $alt_email,
            'question_groups'			=> $question_groups,
			'allow_overflow'			=> $allow_overflow,
            'overflow_event_id'			=> $overflow_event_id,
			'additional_limit'			=> $additional_limit,
            'reg_limit'					=> $reg_limit,
			'email_id'					=> $email_id,
			'registration_startT'		=> $registration_startT,
			'registration_endT'			=> $registration_endT,
			'event_meta'				=> $event_meta,
			'require_pre_approval'		=> $require_pre_approval,
			'timezone_string'			=> $timezone_string,
			'ticket_id'					=> $ticket_id
		);

        $sql_data = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d');

        $update_id = array('id' => $event_id);

        /* echo 'Debug: <br />';
          print_r($sql);
          echo '<br />';
          print 'Number of vars: ' . count ($sql);
          echo '<br />';
          print 'Number of cols: ' . count($sql_data); */


        if (function_exists('event_espresso_add_event_to_db_groupon')) {
            $sql = event_espresso_add_event_to_db_groupon($sql, $_REQUEST['use_groupon_code']);
            ///print count ($sql);
            $sql_data = array_merge((array) $sql_data, (array) '%s');
            //print count($sql_data);
            $wpdb->update(EVENTS_DETAIL_TABLE, $sql, $update_id, $sql_data, array('%d'));
            /* echo 'Debug: <br />';
              print 'Number of vars: ' . count ($sql);
              echo '<br />';
              print 'Number of cols: ' . count($sql_data); */
        } else {
            $wpdb->update(EVENTS_DETAIL_TABLE, $sql, $update_id, $sql_data, array('%d'));
            /* echo 'Debug: <br />';
              print 'Number of vars: ' . count ($sql);
              echo '<br />';
              print 'Number of cols: ' . count($sql_data); */
        }
        //print $wpdb->print_error();
 		
		//BEGIN CATEGORY MODIFICATION
        //We first delete the previous entry then we get the category id's of the event and put them in events_detail_table.category_id as a well-formatted string (id,n id)
        $del_cats = "DELETE FROM " . EVENTS_CATEGORY_REL_TABLE . " WHERE event_id = '" . $event_id . "'";
        $wpdb->query($wpdb->prepare($del_cats, NULL));
        $update_event_detail_category_id = "UPDATE ".EVENTS_DETAIL_TABLE." SET category_id = NULL WHERE id='" . $event_id . "'";
        $wpdb->query($wpdb->prepare($update_event_detail_category_id, NULL));
		$string_cat = '';

        if (!empty($_REQUEST['event_category'])) {
            foreach ($_REQUEST['event_category'] as $k => $v) {
                if (!empty($v)) {
                    $sql_cat = "INSERT INTO " . EVENTS_CATEGORY_REL_TABLE . " (event_id, cat_id) VALUES ('" . $event_id . "', '" . (int)$v . "')";
					$wpdb->query($wpdb->prepare($sql_cat, array()) );
                    $string_cat.=sanitize_text_field($v).",";
                }
            }
            if(!empty($string_cat) && $string_cat != ","){
            $cleaned_string_cat = substr($string_cat, 0, -1);
            $tmp=explode(",",$cleaned_string_cat);
            sort($tmp);
            $cleaned_string_cat=implode(",", $tmp);
            trim($cleaned_string_cat);

            $sql_update_event_detail_category_id="UPDATE ".EVENTS_DETAIL_TABLE." SET category_id = '".$cleaned_string_cat."' WHERE id='" . $event_id . "'";
            $wpdb->query($wpdb->prepare($sql_update_event_detail_category_id, NULL));
            }
        }
        //END CATEGORY MODIFICATION
		
		//Staff
		$update_all_staff = FALSE;
		if (isset($_POST['rem_apply_to_all_staff']) && $_POST['recurrence_apply_changes_to'] == 2){ 
			$update_all_staff = TRUE;
		}
		
		if ($_POST['event_id'] == $event_id || $update_all_staff == TRUE){
			$del_ppl = "DELETE FROM " . EVENTS_PERSONNEL_REL_TABLE . " WHERE event_id = '" . $event_id . "'";
			$wpdb->query($wpdb->prepare($del_ppl, NULL));
			
			if (!empty($_REQUEST['event_person'])) {
				foreach ($_REQUEST['event_person'] as $k => $v) {
					if (!empty($v)) {
						$sql_ppl = "INSERT INTO " . EVENTS_PERSONNEL_REL_TABLE . " (event_id, person_id) VALUES ('" . $event_id . "', '" . (int)$v . "')";
						$wpdb->query($wpdb->prepare($sql_ppl, array()) );
					}
				}
			}
		}
		
		//Venues
        $del_venues = "DELETE FROM " . EVENTS_VENUE_REL_TABLE . " WHERE event_id = '" . $event_id . "'";
        $wpdb->query($wpdb->prepare($del_venues, NULL));

        if (!empty($_REQUEST['venue_id'])) {
            foreach ($_REQUEST['venue_id'] as $k => $v) {
                if (!empty($v) && $v != 0) {
                    $sql_venues = "INSERT INTO " . EVENTS_VENUE_REL_TABLE . " (event_id, venue_id) VALUES ('" . $event_id . "', '" . (int)$v . "')";
					$wpdb->query($wpdb->prepare($sql_venues, array()) );
                }
            }
        }
		
		//Discounts
        $del_discounts = "DELETE FROM " . EVENTS_DISCOUNT_REL_TABLE . " WHERE event_id = '" . $event_id . "'";
        $wpdb->query($wpdb->prepare($del_discounts, NULL));

		if (!empty($_REQUEST['event_discount']) && $_REQUEST['use_coupon_code'] == 'Y') {
			//only re-add the coupon codes if they've specified to use all global coupon codes
			//and 'specific' coupon codes
			foreach ($_REQUEST['event_discount'] as $k => $v) {
				if (!empty($v)) {
					$sql_discount = "INSERT INTO " . EVENTS_DISCOUNT_REL_TABLE . " (event_id, discount_id) VALUES ('" . $event_id . "', '" . (int)$v . "')";
					$wpdb->query($wpdb->prepare($sql_discount, array()) );
				}
			}
		}

        $del_times = "DELETE FROM " . EVENTS_START_END_TABLE . " WHERE event_id = '" . $event_id . "'";
        $wpdb->query($wpdb->prepare($del_times, NULL));

        if (!empty($_REQUEST['start_time'])) {
            foreach ($_REQUEST['start_time'] as $k => $v) {
                if (!empty($v)) {
                    $time_qty = empty($_REQUEST['time_qty'][$k]) ? '0' : "'" . (int)$_REQUEST['time_qty'][$k] . "'";
                    $sql_times = "INSERT INTO " . EVENTS_START_END_TABLE . " (event_id, start_time, end_time, reg_limit) VALUES ('" . $event_id . "', '" . event_date_display(sanitize_text_field($v), 'H:i') . "', '" . event_date_display(sanitize_text_field($_REQUEST['end_time'][$k]), 'H:i') . "', " . $time_qty . ")";
					$wpdb->query($wpdb->prepare($sql_times, array()) );
                }
            }
        }

        $del_prices = "DELETE FROM " . EVENTS_PRICES_TABLE . " WHERE event_id = '" . $event_id . "'";
        $wpdb->query($wpdb->prepare($del_prices, NULL));

        if (!empty($_REQUEST['event_cost'])) {
            foreach ($_REQUEST['event_cost'] as $k => $v) {
                if (!empty($v)) {
					$v = (float)preg_replace('/[^0-9\.]/ui','',$v);//Removes non-integer characters
                    $price_type = $_REQUEST['price_type'][$k] != '' ? sanitize_text_field(stripslashes_deep($_REQUEST['price_type'][$k])) : __('General Admission', 'event_espresso');
                    $member_price_type = !empty($_REQUEST['member_price_type'][$k]) ? sanitize_text_field(stripslashes_deep($_REQUEST['member_price_type'][$k])) : __('Members Admission', 'event_espresso');
                    $member_price = !empty($_REQUEST['member_price'][$k]) ? (float)$_REQUEST['member_price'][$k] : $v;
					$sql_price = array(
						'event_id'			=> $event_id,
						'event_cost'		=> $v,
						'surcharge'			=> sanitize_text_field($_REQUEST['surcharge'][$k]),
						'surcharge_type'	=> sanitize_text_field($_REQUEST['surcharge_type'][$k]),
						'price_type'		=> $price_type,
						'member_price'		=> $member_price,
						'member_price_type' => $member_price_type
					);
					$sql_price_data = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');
					
					if ( !$wpdb->insert(EVENTS_PRICES_TABLE, $sql_price, $sql_price_data) ) {
                        $error = true;
                    }
                }
            }
        } else {
            $sql_price = "INSERT INTO " . EVENTS_PRICES_TABLE . " (event_id, event_cost, surcharge, price_type, member_price, member_price_type) VALUES ('" . $event_id . "', '0.00', '0.00', '" . __('Free', 'event_espresso') . "', '0.00', '" . __('Free', 'event_espresso') . "')";
            if ( !$wpdb->query($wpdb->prepare($sql_price, array()) ) ) {
                $error = true;
            }
        }

        ############# MailChimp Integration ###############
        if (defined('EVENTS_MAILCHIMP_ATTENDEE_REL_TABLE') && $espresso_premium == true) {
            MailChimpController::update_event_list_rel($event_id);
        }
      

        // Create Event Post Code Here
        if ( isset( $_REQUEST[ 'create_post' ] ) ) {
            switch ( $_REQUEST[ 'create_post' ] ) {
                case 'N':
                    $sql = " SELECT * FROM " . EVENTS_DETAIL_TABLE;
                    $sql .= " WHERE id = '" . $event_id . "' ";
                    $wpdb->get_results($wpdb->prepare($sql, NULL));
                    $post_id = $wpdb->last_result[0]->post_id;
                    if ($wpdb->num_rows > 0 && !empty($_REQUEST['delete_post']) && $_REQUEST['delete_post'] == 'Y') {
                        $sql = array('post_id' => '', 'post_type' => '');
                        $sql_data = array('%d', '%s');
                        $update_id = array('id' => $event_id);
                       	$wpdb->update(EVENTS_DETAIL_TABLE, $sql, $update_id, $sql_data, array('%d'));
                        wp_delete_post($post_id, 'true');
                    }
                    break;

                case 'Y':
                    $post_type = $_REQUEST['espresso_post_type'];
                    if ($post_type == 'post') {
                        if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "event_post.php") || file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/event_post.php")) {
                            // Load message from template into message post variable
                            ob_start();
                            if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "event_post.php")) {
                                require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "event_post.php");
                            } else {
                                require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/event_post.php");
                            }
                            $post_content = ob_get_contents();
                            ob_end_clean();
                        } else {
                            _e('There was error finding a post template. Please verify your post templates are available.', 'event_espresso');
                        }
                    } elseif ($post_type == 'espresso_event') {
                        ob_start();
                        echo $event_desc;
                        $post_content = ob_get_contents();
                        ob_end_clean();
                        // if there's a cart link shortcode in the post, replace the shortcode with one that includes the event_id
                        if (preg_match("/ESPRESSO_CART_LINK/", $post_content)) {
							$post_content = preg_replace('/ESPRESSO_CART_LINK/', 'ESPRESSO_CART_LINK event_id=' . $event_id, $post_content); 
						}
                    }

                    $my_post = array();

                    $sql = " SELECT * FROM " . EVENTS_DETAIL_TABLE;
                    $sql .= " WHERE id = '" . $event_id . "' ";
                    $wpdb->get_results($wpdb->prepare($sql, NULL));
                    $post_id = $wpdb->last_result[0]->post_id;


                    $post_type = $_REQUEST['espresso_post_type'];

                    if ($post_id > 0)
                        $my_post['ID'] = $post_id;

                    $my_post['post_title']		= sanitize_text_field($_REQUEST['event']);
                    $my_post['post_content']	= $post_content;
                    $my_post['post_status']		= 'publish';
					$my_post['post_author']		= !empty($_REQUEST['user']) ? (int)$_REQUEST['user'] : '';
          			$my_post['post_category']	= !empty($_REQUEST['post_category']) ? $_REQUEST['post_category'] : '';
            		$my_post['tags_input']		= !empty($_REQUEST['post_tags']) ? $_REQUEST['post_tags'] : '';
           			$my_post['post_type']		= !empty($post_type) ? $post_type : 'post';
					
					
                    //print_r($my_post);
                    // Insert the post into the database


                    if ($post_id > 0) {
                        $post_id = wp_update_post($my_post);
                        update_post_meta($post_id, 'event_id', $event_id);
						update_post_meta($post_id, 'event_meta', $event_meta);
                        update_post_meta($post_id, 'event_identifier', $event_identifier);
                        update_post_meta($post_id, 'event_start_date', $start_date);
                        update_post_meta($post_id, 'event_end_date', $end_date);
                        update_post_meta($post_id, 'event_location', $event_location);
						update_post_meta($post_id, 'event_thumbnail_url', $event_thumbnail_url);
                        update_post_meta($post_id, 'virtual_url', $virtual_url);
                        update_post_meta($post_id, 'virtual_phone', $virtual_phone);
                        //
                        update_post_meta($post_id, 'event_address', $address);
                        update_post_meta($post_id, 'event_address2', $address2);
                        update_post_meta($post_id, 'event_city', $city);
                        update_post_meta($post_id, 'event_state', $state);
                        update_post_meta($post_id, 'event_country', $country);
                        update_post_meta($post_id, 'event_phone', $phone);
                        update_post_meta($post_id, 'venue_title', $venue_title);
                        update_post_meta($post_id, 'venue_url', $venue_url);
                        update_post_meta($post_id, 'venue_phone', $venue_phone);
                        update_post_meta($post_id, 'venue_image', $venue_image);
                        update_post_meta($post_id, 'event_externalURL', $externalURL);
                        update_post_meta($post_id, 'event_reg_limit', $reg_limit);
                        update_post_meta($post_id, 'event_start_time', time_to_24hr($start_time));
                        update_post_meta($post_id, 'event_end_time', time_to_24hr($end_time));
                        update_post_meta($post_id, 'event_registration_start', $registration_start);
                        update_post_meta($post_id, 'event_registration_end', $registration_end);
                        update_post_meta($post_id, 'event_registration_startT', $registration_startT);
                        update_post_meta($post_id, 'event_registration_endT', $registration_endT);
                    } else {
                        $post_id = wp_insert_post($my_post);
                        add_post_meta($post_id, 'event_id', $event_id);
						add_post_meta($post_id, 'event_meta', $event_meta);
                        add_post_meta($post_id, 'event_identifier', $event_identifier);
                        add_post_meta($post_id, 'event_start_date', $start_date);
                        add_post_meta($post_id, 'event_end_date', $end_date);
                        add_post_meta($post_id, 'event_location', $event_location);
						add_post_meta($post_id, 'event_thumbnail_url', $event_thumbnail_url);
                        add_post_meta($post_id, 'virtual_url', $virtual_url);
                        add_post_meta($post_id, 'virtual_phone', $virtual_phone);
                        //
                        add_post_meta($post_id, 'event_address', $address);
                        add_post_meta($post_id, 'event_address2', $address2);
                        add_post_meta($post_id, 'event_city', $city);
                        add_post_meta($post_id, 'event_state', $state);
                        add_post_meta($post_id, 'event_country', $country);
                        add_post_meta($post_id, 'event_phone', $phone);
                        add_post_meta($post_id, 'venue_title', $venue_title);
                        add_post_meta($post_id, 'venue_url', $venue_url);
                        add_post_meta($post_id, 'venue_phone', $venue_phone);
                        add_post_meta($post_id, 'venue_image', $venue_image);
                        add_post_meta($post_id, 'event_externalURL', $externalURL);
                        add_post_meta($post_id, 'event_reg_limit', $reg_limit);
                        add_post_meta($post_id, 'event_start_time', time_to_24hr($start_time));
                        add_post_meta($post_id, 'event_end_time', time_to_24hr($end_time));
                        add_post_meta($post_id, 'event_registration_start', $registration_start);
                        add_post_meta($post_id, 'event_registration_end', $registration_end);
                        add_post_meta($post_id, 'event_registration_startT', $registration_startT);
                        add_post_meta($post_id, 'event_registration_endT', $registration_endT);
                    }

                    // Store the POST ID so it can be displayed on the edit page
                    $sql = array('post_id' => $post_id, 'post_type' => $post_type);
                    $sql_data = array('%d', '%s');
                    $update_id = array('id' => $event_id);
                    $wpdb->update(EVENTS_DETAIL_TABLE, $sql, $update_id, $sql_data, array('%d'));

                    break;
            }
        }
        ?>
        <div id="message" class="updated fade"><p><strong><?php _e('Event details updated for', 'event_espresso'); ?> <a href="<?php echo espresso_reg_url($event_id); ?>" target="_blank">
		<?php echo htmlentities( stripslashes( sanitize_text_field( $_REQUEST['event'] )), ENT_QUOTES, 'UTF-8' ) ?> for <?php echo date("m/d/Y", strtotime($start_date)); ?></a>.</strong></p></div>
        
        <?php
			/*
			 * Added for seating chart addon
			 */
			if ( isset($seating_chart_result) && $seating_chart_result === false ){
				echo '<p>Failed to associate new seating chart with this event. (Seats from current seating chart might have been used by some attendees)</p>';
			}
    }

    /*
     * With the recursion of this function, additional recurring events will be updated
     */
    if (isset($recurrence_dates) && count($recurrence_dates) > 0 && $_POST['recurrence_apply_changes_to'] > 1) {
        //$recurrence_dates = array_shift($recurrence_dates); //Remove the first item from the array since it will be added after this recursion
        foreach ($recurrence_dates as $r_d) {

            if ($r_d['event_id'] != '' && count($r_d) > 2) {
                update_event(
					array(
						'event_id'					=> $r_d['event_id'],
						'event_identifier'			=> $r_d['event_identifier'],
						'recurrence_id'				=> $r_d['recurrence_id'],
						'recurrence_start_date'		=> $r_d['start_date'],
						'recurrence_event_end_date' => $r_d['event_end_date'],
						'registration_start'		=> $r_d['registration_start'],
						'registration_end'			=> $r_d['registration_end'],
						'visible_on'				=> $r_d['visible_on'],
						'bypass_nonce'				=> TRUE,
                ));
            }
        }
    }
    /*
     * End recursion, as part of recurring events.
     */
	 
	do_action('action_hook_espresso_update_event_success',$_REQUEST);
}
