<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
function add_new_attendee($event_id){
	if (isset($_REQUEST['regevent_action_admin']) && $_REQUEST['regevent_action_admin']== 'post_attendee'){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."includes/functions/attendee_functions.php");
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."includes/process-registration/add_attendees_to_db.php");
		$attendee_id = event_espresso_add_attendees_to_db();

		if ( $attendee_id ) {
			// SEND CONFIRMATION EMAIL MESSAGES
			event_espresso_email_confirmations(array('attendee_id' => $attendee_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
			//echo $attendee_id;
			?>
			<div id="message" class="updated fade">
			  <p><strong>
			    <?php _e('Added Attendee to Database','event_espresso'); ?>
			    </strong></p>
			</div>
		<?php
		} else {
			global $notifications;
			$error_msg = implode( $notifications['error'], '<br />');
			?>
			<div id="message" class="error">
				<p>
					<strong><?php echo $error_msg; ?></strong>
				</p>
			</div>
			<?php
		}
	}
	wp_register_script('reCopy', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/reCopy.js"), false, '1.1.0');
        wp_print_scripts('reCopy');

	global $wpdb;
	$sql  = "SELECT * FROM " .EVENTS_DETAIL_TABLE. " WHERE is_active='Y' AND event_status != 'D' AND id = '" . $event_id . "' LIMIT 0,1";

	//Build the registration page
	if ($wpdb->get_results($sql)){
			$events = $wpdb->get_results($sql);
			//These are the variables that can be used throughout the regsitration page
			foreach ($events as $event){
					$event_id = $event->id;
					$event_name = stripslashes($event->event_name);
					$event_desc = stripslashes($event->event_desc);
					$display_desc = $event->display_desc;
					$event_address = $event->address;
					$event_city = $event->city;
					$event_state = $event->state;
					$event_zip = $event->zip;
					$event_description = stripslashes($event->event_desc);
					$event_identifier = $event->event_identifier;
					$event_cost = isset($event->event_cost) ? $event->event_cost:'';
					$member_only = isset($event->member_only) ? $event->member_only:'';
					$reg_limit = isset($event->reg_limit) ? $event->reg_limit:'';
					$allow_multiple = $event->allow_multiple;
					$start_date =  $event->start_date;
					$end_date =  $event->end_date;
					$reg_limit=$event->reg_limit;
					$additional_limit = $event->additional_limit;
					$is_active = array();
					$question_groups = unserialize($event->question_groups);
					//This function gets the status of the event.
					$is_active = event_espresso_get_is_active($event_id);

					//If the coupon code system is intalled then use it
					if (function_exists('event_espresso_coupon_registration_page')) {
						$use_coupon_code = $event->use_coupon_code;
					}

					//If the groupon code addon is installed, then use it
					if (function_exists('event_espresso_groupon_payment_page')) {
						$use_groupon_code = $event->use_groupon_code;
					}

					//Set a default value for additional limit
					if ($additional_limit == ''){
						$additional_limit = '5';
					}
			}//End foreach ($events as $event)


			//This is the start of the registration form. This is where you can start editing your display.
			$num_attendees = apply_filters('filter_hook_espresso_get_num_attendees', $event_id);//Get the number of attendees
			$available_spaces = apply_filters('filter_hook_espresso_available_spaces_text', $event_id);//Gets a count of the available spaces
			$number_available_spaces = apply_filters('filter_hook_espresso_get_num_available_spaces', $event_id);//Gets the number of available spaces

?>
<script>$jaer = jQuery.noConflict();
	jQuery(document).ready(function($jaer) {
	jQuery(function(){
		//Registration form validation
		jQuery('#espresso-admin-add-new-attendee-frm').validate();
	});
});

	</script>
<div class="metabox-holder">
  <div class="postbox">
    <div id="espresso-admin-add-new-attendee-dv">

        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']?>" onsubmit="return validateForm(this)"  id="registration_form" class="espresso_form">
			<?php wp_nonce_field('reg_nonce', 'reg_form_nonce');?>
          <h3 class="h3_event_title" id="h3_event_title-<?php echo $event_id;?>"><?php echo $event_name?></h3>
           <div  class="padding">
     	     <div  class="inside">
				<fieldset>
		 		<h4 class="reg-quest-title section-title"><?php _e('Event Dates and Times','event_espresso'); ?></h4>
					<p class="start_date">
						<span class="span_event_date_label"><?php _e('Start Date:','event_espresso'); ?></span><span class="span_event_date_value"><?php echo event_date_display($start_date)?></span>
					</p>
		          	<p class="event_time">
		            <?php
						$time_selected ='';
						//This block of code is used to display the times of an event in either a dropdown or text format.
						if (!empty($time_selected) && $time_selected == true){//If the customer is coming from a page where the time was preselected.
							echo event_espresso_display_selected_time($time_id);//Optional parameters start, end, default
						}else if ($time_selected == false){
							echo event_espresso_time_dropdown($event_id);
						}//End time selected
					?>
	          		</p>
	          		<?php
						// Added for seating chart addon
						do_action('ee_seating_chart_css');
						do_action('ee_seating_chart_js');
						do_action('ee_seating_chart_flush_expired_seats');
						do_action( 'espresso_seating_chart_select', $event_id);
			  		?>
				</fieldset>
			  <?php
						echo event_espresso_add_question_groups( $question_groups, '', null, 0, array('admin_only'=>true), 'inline' );

						
						//Coupons
						if (function_exists('event_espresso_coupon_registration_page')) {
							echo event_espresso_coupon_registration_page($use_coupon_code, $event_id);
						}//End coupons display

						//Groupons
						if (function_exists('event_espresso_groupon_registration_page')) {
							echo event_espresso_groupon_registration_page($use_groupon_code, $event_id);
						}//End groupons display


	?>


	          <p class="event_form_field">
	            <label for="event_cost" class="inline">
	              <?php _e('Amount Paid:','event_espresso'); ?>
	            </label>
	            <input tabindex="9" type="text" maxlength="10" size="15" name="event_cost" id="event_cost-<?php echo $event_id;?>" <?php echo $event_cost ? 'value="' . $event_cost . '"' : ""; ?> />
	            <input type="hidden" name="regevent_action_admin" id="regevent_action-<?php echo $event_id;?>" value="post_attendee" />
	            <input type="hidden" name="event_id" id="event_id-<?php echo $event_id;?>" value="<?php echo $event_id;?>" />
	            <input type="hidden" name="admin" value="true" />
	          </p>
			 
			<?php echo event_espresso_additional_attendees( $event_id, $additional_limit, $number_available_spaces, __('Number of Tickets', 'event_espresso'), true, 'admin', 'inline' );  ?>
			
	          <p class="event_form_submit" id="event_form_submit-<?php echo $event_id;?>">
	            <input class="btn_event_form_submit button-primary" id="event_form_field-<?php echo $event_id;?>" type="submit" name="Submit" value="<?php _e('Submit','event_espresso');?>" />
	          </p>
	      </div>
	      </div>
        </form>
      </div>
    </div>
  </div>
<?php
event_list_attendees();
	}//End Build the registration page
}
