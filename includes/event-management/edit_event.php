<?php
function edit_event($event_id = 0){
	global $wpdb, $org_options;
	wp_tiny_mce( false , // true makes the editor "teeny"
		array(
			"editor_selector" => "theEditor"//This is the class name of your text field
		)
	);

        if (  function_exists( 'wp_tiny_mce_preload_dialogs' )){
             add_action( 'admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30 );
        }

	$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "'");
	foreach ($events as $event){

		$event_id = $event->id;
		$event_name = stripslashes_deep($event->event_name);
		$event_desc = stripslashes_deep($event->event_desc);
		$display_desc= $event->display_desc;
		$display_reg_form= $event->display_reg_form;
		$event_description = stripslashes_deep($event->event_desc);
		$member_only = $event->member_only;
		
		$phone=stripslashes_deep($event->phone);
		$externalURL=stripslashes_deep($event->externalURL);
		
		//Early discounts
		$early_disc=stripslashes_deep($event->early_disc);
		$early_disc_date=stripslashes_deep($event->early_disc_date);		
		$early_disc_percentage=stripslashes_deep($event->early_disc_percentage);
		
		$post_id=$event->post_id;
		$post_type=$event->post_type;
		
		$event_identifier = stripslashes_deep($event->event_identifier);
		
		$registration_start =$event->registration_start;
		$registration_end =$event->registration_end;
		$registration_startT = $event->registration_startT;
		$resitration_endT = $event->registration_endT;
		//$event_timezone = $event->event_timezone;
		
		$start_date =$event->start_date;
		$end_date =$event->end_date;
		
		$tax_percentage = $event->tax_percentage;
		$tax_mode =$event->tax_mode;
		
		$start_time = $event->start_time;
		$end_time = $event->end_time;
		$reg_limit = $event->reg_limit;
		$additional_limit = $event->additional_limit;
		$allow_overflow = $event->allow_overflow;
		$overflow_event_id = $event->overflow_event_id;
		$allow_multiple = $event->allow_multiple;
		$event_cost = unserialize($event->event_cost);
		$is_active = $event->is_active;
		$status = array();
		$status = event_espresso_get_is_active($event_id);
		$event_status = $event->event_status;
		$conf_mail=stripslashes_deep($event->conf_mail);
		$send_mail=stripslashes_deep($event->send_mail);
		$use_coupon_code=$event->use_coupon_code;
		if (function_exists('event_espresso_edit_event_groupon')) {
			$use_groupon_code=$event->use_groupon_code;
		}
		$alt_email = $event->alt_email;
		
		$address=stripslashes_deep($event->address);
		$address2=stripslashes_deep($event->address2);
		$city=stripslashes_deep($event->city);
		$state=stripslashes_deep($event->state);
		$zip=stripslashes_deep($event->zip);
		$country=stripslashes_deep($event->country);
		
		$venue_title=stripslashes_deep($event->venue_title);
		$venue_url=stripslashes_deep($event->venue_url);
		$venue_phone=stripslashes_deep($event->venue_phone);
		$venue_image=stripslashes_deep($event->venue_image);
		
		$email_id  = $event->email_id;
			
		$google_map_link = espresso_google_map_link(array( 'address'=>$address, 'city'=>$city, 'state'=>$state, 'zip'=>$zip, 'country'=>$country) );
		
		
		//Virtual location
		$virtual_url=stripslashes_deep($event->virtual_url);
		$virtual_phone=stripslashes_deep($event->virtual_phone);

                $question_groups = unserialize($event->question_groups);
				$item_groups = unserialize($event->item_groups);

                $event_meta = unserialize($event->event_meta);

                $recurrence_id = $event->recurrence_id;
                $visible_on = $event->visible_on;
		}
		
		$values=array(					
		array('id'=>'Y','text'=> __('Yes','event_espresso')),
		array('id'=>'N','text'=> __('No','event_espresso')));

                $additional_attendee_reg_info = array(
                    array('id'=>'1','text'=> __('No info required','event_espresso')),
                    array('id'=>'2','text'=> __('Personal Info','event_espresso')),
                    array('id'=>'3','text'=> __('Full Registration Info','event_espresso'))
                   
                );

		//If user is an event manager, then show only their events
		if (function_exists('espresso_member_data')&&espresso_member_data('role')=='espresso_event_manager'&&espresso_member_data('id')!= espresso_is_my_event($event_id))
			return;
?>
<!--Update event display-->

<div id="side-info-column" class="inner-sidebar">
  <div id="side-sortables" class="meta-box-sortables ui-sortable">
    <div id="submitdiv" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class='hndle'><span>
        <?php _e('Quick Overview','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <div class="submitbox" id="submitpost">
          <div id="minor-publishing">
            <div id="minor-publishing-actions">
              <div id="preview-action"> <a class="preview button" href="<?php echo home_url()?>/?page_id=<?php echo $org_options['event_page_id']?>&regevent_action=register&event_id=<?php echo $event_id?>&name_of_event=<?php echo $event_name?>" target="event-preview" id="event-preview" tabindex="5">
                <?php _e('View Event','event_espresso'); ?>
                </a>
                <input type="hidden" name="event-preview" id="event-preview" value="" />
              </div>
              <div id="copy-action"> <a class="preview button" href="admin.php?page=events&amp;action=copy_event&event_id=<?php echo $event_id?>" target="event-copy" id="post-copy" tabindex="4" onclick="return confirm('<?php _e('Are you sure you want to copy '.$event_name.'?','event_espresso'); ?>')">
                <?php _e('Duplicate Event','event_espresso'); ?>
                </a>
                <input  type="hidden" name="event-copy" id="event-copy" value="" />
              </div>
              <div class="clear"></div>
            </div>
            <!-- /minor-publishing-actions -->
            
            <div id="misc-publishing-actions">
              <div class="misc-pub-section curtime" id="visibility"> <span id="timestamp">
                <?php _e('Start Date','event_espresso'); ?>
                <b> <?php echo event_date_display($start_date)?> <?php echo $start_time?></b></span> </div>
              <div class="misc-pub-section">
                <label for="post_status">
                  <?php _e('Current Status:','event_espresso'); ?>
                </label>
                <span id="post-status-display"> <?php echo $status['display'];?></span> </div>
              <div class="misc-pub-section misc-pub-section-last" id="visibility"> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/icons/group.png" width="16" height="16" alt="<?php _e('View Attendees','event_espresso'); ?>" /> <?php echo $number_attendees == '0' ? __('Attendees','event_espresso') : '<a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=' . $event_id . '">' . __('Attendees','event_espresso') . '</a>';?>: <?php echo get_number_of_attendees_reg_limit($event_id, $reg_limit );?> </div>
              
              <div class="misc-pub-section misc-pub-section-last" id="visibility">
              	<a href="admin.php?page=events&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Send Event Newsletter', 'event_espresso' ); ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_go.png" width="16" height="16" alt="<?php _e( 'Newsletter', 'event_espresso' ); ?>" /></a> <a href="admin.php?page=events&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Send Event Newsletter', 'event_espresso' ); ?>"><?php _e('Send Newsletter', 'event_espresso'); ?></a>
                </div>
            </div>
            <!-- /misc-publishing-actions -->
            <div class="clear"></div>
          </div>
          <!-- /minor-publishing -->
          <div id="major-publishing-actions">
            <div id="delete-action"> <a class="submitdelete deletion" href="admin.php?page=events&amp;action=delete&event_id=<?php echo $event_id?>" onclick="return confirm('<?php _e('Are you sure you want to delete '.$event_name.'?','event_espresso'); ?>')">
              <?php _e('Delete Event','event_espresso'); ?>
              </a></div>
            <div id="publishing-action">
              <input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Event','event_espresso'); ?>" id="save_event_setting" />
            </div>
            <!-- /publishing-action -->
            <div class="clear"></div>
          </div>
          <!-- /major-publishing-actions --> 
        </div>
        <!-- /submitpost --> 
      </div>
      <!-- /inside --> 
    </div>
    <!-- /submitdiv -->
    
    <?php 
    $status=array(array('id'=>'A','text'=> __('Primary','event_espresso')), array('id'=>'S','text'=> __('Secondary','event_espresso')), array('id'=>'O','text'=> __('Ongoing','event_espresso')), array('id'=>'D','text'=> __('Deleted','event_espresso')));
    $additional_attendee_reg_info_dd = '';

    $additional_attendee_reg_info = array(
        array('id'=>'1','text'=> __('No info required','event_espresso')),
        array('id'=>'2','text'=> __('Personal Info only','event_espresso')),
        array('id'=>'3','text'=> __('Full registration info','event_espresso'))

    );

     if (get_option('event_espresso_multi_reg_active') == 1)
        $additional_attendee_reg_info_dd = '<p>' .__('Additional Attendee Registration info?','event_espresso') . ' ' . select_input('additional_attendee_reg_info', $additional_attendee_reg_info, $event_meta['additional_attendee_reg_info']) .'</p>';

   postbox('event-status', 'Event Options', 
   '<p>' .__('Attendee Limit','event_espresso') . ': <input name="reg_limit" size="10" type="text" value="' . $reg_limit . '"><br />' .
		'(' .__('leave blank for unlimited','event_espresso') . ')</p>' . 
		'<p>' .__('Allow group registrations?','event_espresso') . ' ' . select_input('allow_multiple', $values, $allow_multiple) .'</p>' . 
		'<p>' .__('Max Group Registrants','event_espresso') . ': <input type="text" name="additional_limit" value="' . $additional_limit . '" size="4">'.'</p>' .
                $additional_attendee_reg_info_dd .
   
   '<p><strong>' .__('Advanced Options:','event_espresso') . '</strong></p>' .
   
   
		'<p>' .__('Is this an active event? ','event_espresso') . __( select_input('is_active', $values, $is_active)) . '</p>' .
		'<p>' .__('Event Status: ','event_espresso') . __( select_input('event_status', $status, $event_status)) . ' <a class="ev_reg-fancylink" href="#status_types_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a></p>' . 
		'<p>' .__('Display  description? ','event_espresso') . select_input('display_desc', $values, $display_desc) . '</p>' .
		'<p>' .__('Display  registration form? ','event_espresso') . select_input('display_reg_form', $values, $display_reg_form) . '</p>' .
		($event_status != 'S' ? espresso_secondary_events_dd($overflow_event_id, $allow_overflow) :'' ) .
		'<p>' .__('Use an alternate registration page?','event_espresso') . '<br />
			<input name="externalURL" size="20" type="text" value="' . $externalURL . '"> <a class="ev_reg-fancylink" href="#external_URL_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a><br /></p>' .
		'<p>' .__('Use an alternate email address?','event_espresso') . '<br />
			<input name="alt_email" size="20" type="text" value="' . $alt_email . '"> <a class="ev_reg-fancylink" href="#alt_email_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a></p>'
	);
 
    if (get_option('events_members_active') == 'true') {
?>
    <div style="display: block;" id="member-options" class="postbox">
      <div class="handlediv" title="Click to toggle"><br>
      </div>
      <h3 class="hndle"><span>
        <?php _e('Member Options','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <p><?php echo event_espresso_member_only($member_only); ?></p>
      </div>
    </div>
    <!-- /member-options -->
    <?php
	}
     ?>
    <div style="display: block;" id="event-category" class="postbox">
      <div class="handlediv" title="Click to toggle"><br>
      </div>
      <h3 class="hndle"><span>
        <?php _e('Event Category','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <?php	
		echo event_espresso_get_categories($event_id);?>
      </div>
    </div>
    <!-- /event-category -->
    
    
    
    <div style="display: block;" id="event-discounts" class="postbox">
      <div class="handlediv" title="Click to toggle"><br>
      </div>
      <h3 class="hndle"><span>
        <?php _e('Event Promotions','event_espresso'); ?>
        </span></h3>
      <div class="inside">
       
                <p><strong><?php _e('Early Registration Discount','event_espresso'); ?></strong></p>
                
                <p><?php _e('End Date:','event_espresso'); ?><input type="text" class="datepicker" size="12" id="early_disc_date" name="early_disc_date" value="<?php echo $early_disc_date; ?>"/></p>
                <p><?php _e('Amount:','event_espresso'); ?><input type="text" size="3" id="early_disc" name="early_disc" value="<?php echo $early_disc; ?>"/> <?php _e('Percentage:','event_espresso'); 		
		echo select_input('early_disc_percentage', $values, $early_disc_percentage);?></p>
				<p><?php _e('(Leave blank if not applicable)', 'event_espresso'); ?></p>
        <p>
          <strong><?php _e('Allow discount codes?','event_espresso');?></strong>
					
			<?php echo select_input('use_coupon_code', $values, $use_coupon_code); ?>
          <a class="ev_reg-fancylink" href="#coupon_code_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a></p>
        <?php
            $event_discounts = $wpdb->get_results("SELECT * FROM ". EVENTS_DISCOUNT_CODES_TABLE);
			foreach ($event_discounts as $event_discount){
				$discount_id = $event_discount->id;
				$coupon_code = $event_discount->coupon_code;
				$discount_type_price = $event_discount->use_percentage == 'Y' ? $event_discount->coupon_code_price.'%' : $org_options['currency_symbol'].$event_discount->coupon_code_price;
				
					$in_event_discounts = $wpdb->get_results("SELECT * FROM " . EVENTS_DISCOUNT_REL_TABLE . " WHERE event_id='".$event_id."' AND discount_id='".$discount_id."'");
					foreach ($in_event_discounts as $in_discount){
						$in_event_discount = $in_discount->discount_id;
					}
					echo '<p id="event-discount-' . $discount_id . '"><label for="in-event-discount-' . $discount_id . '" class="selectit"><input value="' . $discount_id . '" type="checkbox" name="event_discount[]" id="in-event-discount-' . $discount_id . '"' . ($in_event_discount == $discount_id ? ' checked="checked"' : "" ) . '/> ' . $coupon_code. "</label></p>";
			}	
            ?>
      </div>
    </div>
    <!-- /event-discounts -->



        <!-- /event-questions -->

    <div style="display: block;" id="event-questions" class="postbox">
      <div class="handlediv" title="Click to toggle"><br>
      </div>
      <h3 class="hndle"><span>
        <?php _e('Event Questions','event_espresso'); ?>
        </span></h3>
      <div class="inside">

                <p><strong><?php _e('Question Groups','event_espresso'); ?></strong><br />
<?php _e('Add a pre-populated', 'event_espresso'); ?> <a href="admin.php?page=form_groups" target="_blank"><?php _e('group', 'event_espresso'); ?></a> <?php _e('of', 'event_espresso'); ?> <a href="admin.php?page=form_builder" target="_blank"><?php _e('questions', 'event_espresso'); ?></a> <?php _e('to your event. The personal information group is rquired for all events.', 'event_espresso'); ?></p>

        <?php
            $q_groups = $wpdb->get_results("SELECT qg.* FROM ". EVENTS_QST_GROUP_TABLE . " qg
                    JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr
                    ON qg.id = qgr.group_id
                    GROUP BY qg.id ORDER BY group_order
                    ");
			foreach ($q_groups as $question_group){
				$question_group_id = $question_group->id;
				$question_group_description = $question_group->group_description;
				$group_name = $question_group->group_name;
                                $checked = (is_array($question_groups) && array_key_exists($question_group_id, $question_groups)) || ($question_group->system_group == 1)?' checked="checked" ':'';

                                $visibility = $question_group->system_group == 1?'style="visibility:hidden"':'';

					echo '<p id="event-question-group-' . $group_id . '">
                                             <input value="' . $question_group_id . '" type="checkbox" ' . $visibility . ' name="question_groups[' . $question_group_id . ']" ' . $checked . ' /> <a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=' . $question_group_id . '" title="edit" target="_blank">'. $group_name. "</a></p>";
			}
            ?>
      </div>
    </div>
    <!-- /event-questions -->
    <?php if (is_plugin_active('espresso-cart/espresso-cart.php')) { ?>
     <!-- /cart-items -->

    <div style="display: block;" id="event-cart-items" class="postbox">
      <div class="handlediv" title="Click to toggle"><br>
      </div>
      <h3 class="hndle"><span>
        <?php _e('Cart Items','event_espresso'); ?>
        </span></h3>
      <div class="inside">

                <p><strong><?php _e('Item Groups','event_espresso'); ?></strong><br />
<?php _e('Add a pre-populated ', 'event_espresso'); ?><a href="admin.php?page=item_groups"><?php _e('group', 'event_espresso'); ?></a> <?php _e('of', 'event_espresso'); ?> <a href="admin.php?page=cart_items"><?php _e('items', 'event_espresso'); ?></a> <?php _e('to your event', 'event_espresso'); ?>.</p>

        <?php
            $i_groups = $wpdb->get_results("SELECT ig.* FROM ". EVENTS_CART_ITEMS_GROUP_TABLE . " ig
                    JOIN " . EVENTS_CART_ITEMS_GROUP_REL_TABLE . " igr
                    ON ig.id = igr.group_id
                    GROUP BY ig.id ORDER BY group_order
                    ");
			foreach ($i_groups as $item_group){
				$item_group_id = $item_group->id;
				$item_group_description = $item_group->group_description;
				$group_name = $item_group->group_name;
				$checked = (is_array($item_groups) && array_key_exists($item_group_id, $item_groups)) || ($item_group->system_group == 1)?' checked="checked" ':'';

                                //$visibility = $item_group->system_group == 1?'style="visibility:hidden"':'';

					echo '<p id="event-cart-group-' . $group_id . '">
                                             <input value="' . $item_group_id . '" type="checkbox" ' . $visibility . ' name="item_groups[' . $item_group_id . ']" ' . $checked . ' /> <a href="admin.php?page=item_groups&amp;action=edit_group&amp;group_id=' . $item_group_id . '" title="edit">'. $group_name. "</a></p>";
			}
            ?>
      </div>
    </div>
    <!-- /cart-items -->
<?php }?>
<?php
	if (get_option('events_groupons_active') == 'true') {
?>
    <div style="display: block;" id="groupon-options" class="postbox">
      <div class="handlediv" title="Click to toggle"><br>
      </div>
      <h3 class="hndle"><span>
        <?php _e('Groupon Options','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <p><?php echo event_espresso_edit_event_groupon($use_groupon_code); ?></p>
      </div>
    </div>
    <!-- /groupon-options -->
<?php
	}
?>
  </div>
  <!-- /side-sortables --> 
</div>
<!-- /side-info-column --> 

<!-- Left Column-->
<div id="post-body">
  <div id="post-body-content">
    <div id="titlediv">
    <strong><?php _e('Event Title','event_espresso'); ?></strong>
      <div id="titlewrap">
        <label class="screen-reader-text" for="title">
          <?php _e('Event Title','event_espresso'); ?>
        </label>
        <input type="text" name="event" size="30" tabindex="1" value="<?php echo $event_name;?>" id="title" autocomplete="off" />
      </div>
      <!-- /titlewrap -->
      <div class="inside">
        <div id="edit-slug-box"> <strong>
          <?php _e('Unique Event Identifier:','event_espresso'); ?>
          </strong>
          <input type="text" size="30" tabindex="2" name="event_identifier" id="event_identifier" value ="<?php echo $event_identifier;?>" />
          <?php  echo '<a href="#" class="button" onclick="prompt(&#39;Event Shortcode:&#39;, \'[SINGLEEVENT single_event_id=&#34;\' + jQuery(\'#event_identifier\').val() + \'&#34;]\'); return false;">' . __('Shortcode') . '</a>'?>
          <?php  echo '<a href="#" class="button" onclick="prompt(&#39;Event Short URL:&#39;, \'' . home_url() . '/?ee=' . $event_id . '\'); return false;">' . __('Short URL') . '</a>'?>
          <?php  echo '<a href="#" class="button" onclick="prompt(&#39;Event Full URL:&#39;, \'' . home_url() . '/?page_id=' . $org_options['event_page_id'] . '&amp;regevent_action=register&amp;event_id=' . $event_id . '\'); return false;">' . __('Full URL') . '</a>'?>
        </div>
      </div>
      <!-- /edit-slug-box --> 
    </div>
    <!-- /titlediv -->
    <div id="descriptiondivrich" class="postarea">
   <strong><?php _e('Event Description', 'event_espresso'); ?></strong>
      <?php 
	/*
	This is the editor used by WordPress. It is very very hard to find documentation for this thing, so I pasted everything I could find below.
	param: string $content Textarea content.
	param: string $id Optional, default is 'content'. HTML ID attribute value.
	param: string $prev_id Optional, default is 'title'. HTML ID name for switching back and forth between visual editors.
	param: bool $media_buttons Optional, default is true. Whether to display media buttons.
	param: int $tab_index Optional, default is 2. Tabindex for textarea element.
	*/
	//the_editor($content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2)
	the_editor(html_entity_decode($event_desc), $id = 'event_desc', $prev_id = 'title', $media_buttons = true, $tab_index = 3); ?>
      <table id="post-status-info" cellspacing="0">
        <tbody>
          <tr>
            <td id="wp-word-count"></td>
            <td class="autosave-info"><span id="autosave">&nbsp;</span></td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- /postdivrich -->
    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
      <div style="display: block;" id="event-date-time" class="postbox">
        <div class="handlediv" title="Click to toggle"><br>
        </div>
        <h3 class="hndle"><span>
          <?php _e('Event Date/Times','event_espresso'); ?>
          </span></h3>
        <div class="inside">
        <table width="100%" border="0" cellpadding="5">

            <tr valign="top">
            <td><p><strong><?php _e('Registration Dates','event_espresso'); ?></strong><br /> <?php echo __('Registration Start:','event_espresso') . ' <input type="text" class="datepicker" size="15" id="registration_start" name="registration_start"  value="' . $registration_start . '" />'; ?><br />
<?php echo  __('Registration End:','event_espresso') . ' <input type="text" class="datepicker" size="15" id="registration_end" name="registration_end"  value="' . $registration_end . '" />'; ?></p>
                
                <p><strong><?php _e('Event Dates','event_espresso'); ?></strong><br /> <?php echo __('Event Start Date','event_espresso') . ' <input type="text" class="datepicker" size="15" id="start_date" name="start_date" value="' . $start_date . '" />'; ?> <br />
<?php echo  __('Event End Date','event_espresso') . ' <input type="text" class="datepicker" size="15" id="end_date" name="end_date" value="' . $end_date . '" />'; ?></p>
              </td>
 		 	<?php // ADD TIME REGISTRATION ?>
			  <td>
			   <p><strong><?php _e('Registration Times','event_espresso'); ?></strong><br />
			  <?php echo event_espresso_timereg_editor($event_id);?>
			  </p>
			   <p><strong><?php _e('Event Times','event_espresso'); ?></strong><br /> 
			  <?php echo event_espresso_time_editor($event_id);?>
			  </p>
              <p><strong><?php _e('Current Time', 'event_espresso'); ?>:</strong> 
                   <?php echo date(get_option('date_format')). ' ' .date(get_option('time_format')); ?> <a href="options-general.php" target="_blank"><br /><?php _e('Change timezone and date format settings?', 'event_espresso'); ?></a></p>
			  </td>                
            </tr>
          </table>
        </div>
      </div>

<?php
        /**
         * Load the recurring events form if the add-on has been installed.	*
         */
        if ( get_option( 'event_espresso_re_active' ) == 1 )
        {
            require_once(EVENT_ESPRESSO_RECURRENCE_FULL_PATH . "functions/re_view_functions.php");
            //For now, only the recurring events will show the form
            if ( $recurrence_id > 0)
                event_espresso_re_form( $recurrence_id );
        }
?>
      <div style="display: block;" id="event-pricing" class="postbox">
        <div class="handlediv" title="Click to toggle"><br>
        </div>
        <h3 class="hndle"><span>
          <?php _e('Event Pricing','event_espresso'); ?>
          </span></h3>
        <div class="inside">
          <table width="100%" border="0" cellpadding="5">
            <tr valign="top">
              <td width="50%"><?php event_espresso_multi_price_update($event_id); //Standard pricing?></td>
<?php 
	//If the members addon is installed, define member only event settings
	if (get_option('events_members_active') == 'true'){ ?>
              <td width="50%"><?php echo event_espresso_member_only_pricing($event_id);//Show the the member only pricing options.?></td>
<?php	
	}
?>
          </table>
        </div>
      </div>
      <h2><?php _e('Advanced Options', 'event_espresso'); ?></h2>
      <div style="display: block;" id="event-location" class="postbox">
        <div class="handlediv" title="Click to toggle"><br>
        </div>
        <h3 class="hndle"><span>
         <?php _e('Additional Event/Venue Information','event_espresso'); ?>
          </span></h3>
        <div class="inside">
          <table width="100%" border="0" cellpadding="5">
            <tr valign="top">
              <td>
               <p><strong><?php _e('Physical Location','event_espresso'); ?></strong></p>
                <p>
                  <?php _e('Address:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="100"  type="text"  value="<?php echo $address ?>" name="address" />
                </p>
                <p>
                  <?php _e('Address 2:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="101"  type="text"  value="<?php echo $address2 ?>" name="address2" />
                </p>
                <p>
                  <?php _e('City:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="102"  type="text"  value="<?php echo $city ?>" name="city" />
                </p>
                 <p>
                  <?php _e('State:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="103"  type="text"  value="<?php echo $state ?>" name="state" />
                </p>
                 <p>
                  <?php _e('Zip/Postal Code:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="104"  type="text"  value="<?php echo $zip ?>" name="zip" />
                </p>
                 <p>
                  <?php _e('Country:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="105"  type="text"  value="<?php echo $country ?>" name="country" />
                </p>
                 <p>
                  <?php _e('Google Map Link (for email):','event_espresso');?>
                  <br />
                  <?php  echo $google_map_link;  ?>
                </p>
              </td>
              <td><p><strong>
                <?php _e('Venue Information','event_espresso'); ?>
              </strong></p>
                <p>
                  <?php _e('Title:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="106"  type="text"  value="<?php echo $venue_title ?>" name="venue_title" />
                </p>
                <p>
                  <?php _e('Website:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="107"  type="text"  value="<?php echo $venue_url ?>" name="venue_url" />
                </p>
                <p>
                  <?php _e('Phone:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="108"  type="text"  value="<?php echo $venue_phone ?>" name="venue_phone" />
                </p>
                <p>
                  <?php _e('Image:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="110"  type="text"  value="<?php echo $venue_image ?>" name="venue_image" />
              </p></td>
              <td>
              <p><strong>
                <?php _e('Virtual Location','event_espresso'); ?>
              </strong></p>
              <p>
                <?php _e('Phone:','event_espresso'); ?>
                <br />
<input size="20"  type="text" tabindex="111" value="<?php echo $phone ?>" name="phone" />
              </p>
              <p>
                <?php _e('URL of Event:','event_espresso'); ?>
                <br />
                <textarea cols="30" rows="4" tabindex="112"  name="virtual_url"><?php echo $virtual_url ?></textarea>
              </p>
              <p>
                <?php _e('Call in Number:','event_espresso'); ?>
                <br />
<input size="20" tabindex="113"  type="text"  value="<?php echo $virtual_phone ?>" name="virtual_phone" />
              </p></td>
            </tr>
          </table>
        </div>
      </div>
      <!-- /event-location-->
      
            <div style="display: block;" id="confirmation-email" class="postbox">
        <div class="handlediv" title="Click to toggle"><br>
        </div>
        <h3 class="hndle"><span>
          <?php _e('Email Confirmation:','event_espresso')?>
          </span></h3>
        <div class="inside">
        
          <div id="emaildescriptiondivrich" class="postarea">
            <div style="float:left; width:400px;">
              <p><?php echo __('Send custom confirmation emails for this event?','event_espresso') . ' ' . select_input('send_mail', $values, $send_mail); ?> <?php echo '<a class="ev_reg-fancylink" href="#custom_email_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a>'; ?></p>
              <p><?php _e('Use a ', 'event_espresso');?> <a href="admin.php?page=event_emails"><?php _e('pre-existing email', 'event_espresso'); ?></a>?  <?php echo espresso_db_dropdown(id, email_name, EVENTS_EMAIL_TABLE, email_name, $email_id, 'desc') . ' <a class="ev_reg-fancylink" href="#email_manager_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a>'; ?> </p>
              <p><strong>OR</strong></p>
              <p><?php _e('Create a custom email','event_espresso')?>:</p>
            </div>
           
            <div class="clear"></div>
            <div class="postbox">
              <textarea name="conf_mail" class="theEditor" id="conf_mail" style="width:inherit; height:150px;"><?php echo wpautop($conf_mail); ?></textarea>
       <table id="email-confirmation-form" cellspacing="0">
          <tr>
           <td class="aer-word-count"></td>
            <td class="autosave-info"><span><a class="ev_reg-fancylink" href="#custom_email_info"><?php _e('View Custom Email Tags', 'event_espresso'); ?></a>  | <a class="ev_reg-fancylink" href="#custom_email_example"> <?php _e('Email Example','event_espresso'); ?></a></span></td>
          </tr>
      </table>
            </div>
          </div>
        </div>
      </div>
      <!-- /confirmation-email-->
      <div style="display: block;" id="event-post" class="postbox">
      <div class="handlediv" title="Click to toggle"><br>
      </div>
      <h3 class="hndle"><span>
        <?php _e('Create a Post','event_espresso'); ?>
        </span></h3>
      <div class="inside">
	<?php
	if (strlen($post_id)>1) {
		$create_post = 'Y';//If a post was created previously, default to yes on the update post.
	}else{
		$create_post = 'N';//If a post was NOT created previously, default to no so we do not create a post on accident.
	}
	global $current_user;
	get_currentuserinfo();
	$authors = get_editable_user_ids( $current_user->ID, true, 'post' );
	
	?>
<p>
    <?php echo __('Add/Update post for this event?','event_espresso') . ' ' . select_input('create_post', $values, $create_post); ?> <?php if (strlen($post_id)>1) {_e('If no, delete current post?', 'event_espresso'); ?> <input name="delete_post" type="checkbox" value="Y" /><?php }?></p>
	<input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
	<?php /*?><p><?php _e('Category:', 'event_espresso'); ?> <?php wp_dropdown_categories(array('orderby'=> 'name','order' => 'ASC', 'selected' => $category, 'hide_empty' => 0 )); ?></p><?php */?>
	
    <?php // Get existing post data, if it exists
		$post_data=get_post($post_id);
		
		//$org_options['use_custom_post_types']='Y';	
		
		$custom_post_array = array(array('id'=>'espresso_event','text'=> __('Espresso Event','event_espresso')));
		$post_page_array = array(array('id'=>'post','text'=> __('Post','event_espresso')), array('id'=>'page','text'=> __('Page','event_espresso')));
		$post_page_array = $org_options['use_custom_post_types']=='Y'? array_merge($custom_post_array, $post_page_array) : $post_page_array;
		//print_r($post_page_array);
		
		$post_types=$post_page_array;
		$author = $post_data->post_author;
		$authors = get_editable_user_ids( $author, true, 'post' );
		//$category=get_the_category($post_id);
		//$category=$category[0]->cat_ID;
		//echo $author;
		$tags=get_the_tags($post_id);
		if ($tags) {
			foreach ($tags as $k => $v) {
				$tag[$k] = $v->name;
			}
			$tags=join(', ',$tag);
		}?>
    <p><?php _e('Author:', 'event_espresso'); ?> <?php wp_dropdown_users(array('include' => $authors, 'selected' => $author, 'show' => 'display_name')); ?></p>
    <p><?php _e('Post Type', 'event_espresso'); ?>: <?php echo select_input('post_type', $post_types, $post_type) ?><br />
<?php _e('Post templates are stored in the "templates" directory of the plugin.', 'event_espresso'); ?></p>
	<p><?php _e('Tags:', 'event_espresso'); ?> <input  type="text" id="post_tags" name="post_tags" size="20" value="<?php echo $tags ?>"></p>
     <p><?php _e('Post Categories:', 'event_espresso'); ?> </p>
	<?php require_once( 'includes/meta-boxes.php');
		post_categories_meta_box($post_data, $box);
		//post_tags_meta_box($post, $box);?>
	
	<!-- if post templates installed, post template -->

      </div>
    </div>
    <!-- /event-post -->

    </div>
    <!-- /normal-sortables--> 
  </div>
  <!-- /post-body-content --> 
</div>

<!-- /post-body -->
<input type="hidden" name="edit_action" value="update">
<input type="hidden" name="recurrence_id" value="<?php echo $recurrence_id;?>">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="event_id" value="<?php echo $event_id?>">
<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function() {
        jQuery(".datepicker" ).datepicker({
			changeMonth: true,
			changeYear: true,
                        dateFormat: "yy-mm-dd",
                        showButtonPanel: true
		});
 });
function toggleEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
	}
	jQuery(document).ready(function($) {
		
                

		var id = 'conf_mail';
		$('a.toggleVisual').click(
			function() {
				tinyMCE.execCommand('mceAddControl', false, id);
			}
		);

		$('a.toggleHTML').click(
			function() {
				tinyMCE.execCommand('mceRemoveControl', false, id);
			}
		);
});
</script>
<?php
}
