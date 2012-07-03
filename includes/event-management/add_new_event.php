<?php
function add_new_event(){
	global $wpdb, $org_options;
	wp_tiny_mce( false , // true makes the editor "teeny"
		array(
			"editor_selector" => "theEditor"//This is the class name of your text field
		)
	);

        if (  function_exists( 'wp_tiny_mce_preload_dialogs' )){
             add_action( 'admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30 );
        }
?>
<!--New event display-->

<div id="side-info-column" class="inner-sidebar">
  <div id="side-sortables" class="meta-box-sortables ui-sortable">
    <div id="submitdiv" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class='hndle'><span>
        <?php _e('New Event','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <div class="submitbox" id="submitpost"><!-- /minor-publishing -->
          <div id="major-publishing-actions">
            <div id="delete-action"> <a class="submitdelete deletion" href="admin.php?page=events" onclick="return confirm('<?php _e('Are you sure you want to cancel '.$event_name.'?','event_espresso'); ?>')">
              <?php _e('Cancel','event_espresso'); ?>
              </a></div>
            <div id="publishing-action">
              <input class="button-primary" type="submit" name="Submit" value="<?php _e('Submit New Event','event_espresso'); ?>" id="add_new_event" />
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
   $values=array(array('id'=>'Y','text'=> __('Yes','event_espresso')), array('id'=>'N','text'=> __('No','event_espresso')));
	
	$additional_attendee_reg_info_dd = '';
	$advanced_options ='';
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/admin-files/event-management/advanced_settings.php')){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."includes/admin-files/event-management/advanced_settings.php");
	}else{
		//Display Lite version options
		$status=array(array('id'=>'A','text'=> __('Active','event_espresso')), array('id'=>'D','text'=> __('Deleted','event_espresso')));
	  	$advanced_options = '<p><strong>' .__('Advanced Options:','event_espresso') . '</strong></p>' 
		.'<p>' .__('Is this an active event? ','event_espresso') . __( select_input('is_active', $values, $is_active)) . '</p>' 
		.'<p>' .__('Display  description? ','event_espresso') . select_input('display_desc', $values, $display_desc) . '</p>'
		.'<p>' .__('Display  registration form? ','event_espresso') . select_input('display_reg_form', $values, $display_reg_form) . '</p>';
	}//Display Lite version options - End
	
	   postbox('event-status', 'Event Options',
	   '<p>' .__('Attendee Limit','event_espresso') . ': <input name="reg_limit" size="10" type="text" value="' . $reg_limit . '"><br />' .
			'(' .__('leave blank for unlimited','event_espresso') . ')</p>' .
			'<p>' .__('Allow group registrations?','event_espresso') . ' ' . select_input('allow_multiple', $values, 'N') .
			'<p>' .__('Max Group Registrants','event_espresso') . ': <input type="text" name="additional_limit" value="' . $additional_limit . '" size="4">' .
					$additional_attendee_reg_info_dd .
					$advanced_options
	);
	
	###### Modification by wp-developers to introduce attendee pre-approval requirement ##########
	if ( $org_options['use_attendee_pre_approval'] == 'Y' ) {
?>
	<div style="display: block;" id="attendee-pre-approval-options" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class="hndle"><span>
        <?php _e('Attendee pre-approval required?','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <p>
		<?php 
			$pre_approval_values=array(array('id'=>'1','text'=> __('Yes','event_espresso')), array('id'=>'0','text'=> __('No','event_espresso')));
			echo select_input("require_pre_approval",$pre_approval_values,"0"); 
		?>
        </p>
      </div>
    </div>
<?php
    }
	
	########## END #################################
	if (get_option('events_members_active') == 'true') {
?>
    <div style="display: block;" id="member-options" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class="hndle"><span>
        <?php _e('Member Options','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <p><?php echo event_espresso_member_only($member_only); ?></p>
      </div>
    </div>
    <!-- /event-category -->
    <?php
	}
     ?>
    <div style="display: block;" id="event-discounts" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class="hndle"><span>
        <?php _e('Event Category','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <?php
		echo event_espresso_get_categories();?>
      </div>
    </div>
    <!-- /event-category -->

    
<?php if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/admin-files/promotions_box.php')){
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH."includes/admin-files/promotions_box.php");
}?>

    <div style="display: block;" id="event-questions" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class="hndle"><span>
        <?php _e('Event Questions','event_espresso'); ?>
        </span></h3>
      <div class="inside">

                <p><strong><?php _e('Question Groups','event_espresso'); ?></strong><br />
<?php _e('Add a pre-populated', 'event_espresso'); ?> <a href="admin.php?page=form_groups" target="_blank"><?php _e('group', 'event_espresso'); ?></a> <?php _e('of', 'event_espresso'); ?> <a href="admin.php?page=form_builder" target="_blank"><?php _e('questions', 'event_espresso'); ?></a> <?php _e('to your event. The personal information group is rquired for all events.', 'event_espresso'); ?>
</p>

        <?php
        global $espresso_premium;
		$g_limit =  $espresso_premium != true?'LIMIT 0,1':'';
            $q_groups = $wpdb->get_results("SELECT qg.* FROM ". EVENTS_QST_GROUP_TABLE . " qg
                    JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr
                    ON qg.id = qgr.group_id
                    GROUP BY qg.id ORDER BY group_order $g_limit
                    ");
			foreach ($q_groups as $question_group){
				$question_group_id = $question_group->id;
				$question_group_description = $question_group->group_description;
                                $group_name = $question_group->group_name;
                                $checked = $question_group->system_group == 1?' checked="checked" ':'';
                                 $visibility = $question_group->system_group == 1?'style="visibility:hidden"':'';

					echo '<p id="event-question-group-' . $group_id . '">
                                             <input value="' . $question_group_id . '" type="checkbox" ' . $checked . $visibility . ' name="question_groups[' . $question_group_id . ']" ' . $checked . ' /> <a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=' . $question_group_id . '" title="edit" target="_blank">' . $group_name. '</a></p>';
			}
			if ($espresso_premium != true)
				echo __('Need more questions?', 'event_espresso') . ' <a href="http://eventespresso.com/download/" target="_blank">'.__('Upgrade Now!', 'event_espresso').'</a>';
            ?>
      </div>
    </div>
    <!-- /event-questions -->

<?php
	if (get_option('events_groupons_active') == 'true') {
?>
    <div style="display: block;" id="groupon-options" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class="hndle"><span>
        <?php _e('Groupon Options','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <p><?php echo event_espresso_add_new_event_groupon($use_groupon_code); ?></p>
      </div>
    </div>
    <!-- /groupon-options -->
<?php
	}
?>
<?php if (USING_CALENDAR_DB == 'Y'){?>
<div style="display: block;" id="groupon-options" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
       <h3 class="hndle"><span>
        <?php _e('Calendar Options','event_espresso'); ?>
        </span></h3>
      <div class="inside">
      <p> <?php _e('It looks like you are using the "<a href="http://wordpress.org/extend/plugins/calendar/" target="_blank" title="Calendar developed and supported by Kieran O\'Shea">Calendar</a>" plugin. Would you like to add this event to the calendar?','event_regis'); ?></p>
             <p> <?php _e('Choose a <a href="admin.php?page=calendar-categories">Calendar Category</a>:','event_regis'); ?>
          <select name="calendar_category">
              <?php
		$sql = "SELECT * FROM " . WP_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_name ASC";
		$cat_details = $wpdb->get_results($sql);
		 foreach($cat_details as $cat_detail){
			 ?>
              <option value="<?php echo $cat_detail->category_id; ?>"><?php echo $cat_detail->category_name; ?></option>
              <?php }	?>
            </select></p>
           <p><?php _e('Add to Calendar:','event_espresso');
			echo select_input('add_to_calendar', $values, 'Y');?></p>
      </div>
    </div>
    <!-- /calendar-options -->
      <?php }?>
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
          <?php  echo '<a href="#" class="button" onclick="prompt(&#39;Event Shortcode:&#39;, \'[SINGLEEVENT single_event_id=&#34;\' + jQuery(\'#event_identifier\').val() + \'&#34;]\'); return false;">' . __('Get Shortcode') . '</a>'?>
          <?php  echo '<a href="#" class="button" onclick="prompt(&#39;Event URL:&#39;, \'' . home_url() . '/?page_id=' . $org_options['event_page_id'] . '&amp;regevent_action=register&amp;event_id=' . $event_id . '\'); return false;">' . __('Get URL') . '</a>'?>
        </div>
      </div>
      <!-- /edit-slug-box -->
    </div>
    <!-- /titlediv -->
    <div id="descriptiondivrich" class="postarea"><strong><?php _e('Event Description', 'event_espresso'); ?></strong>
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
	the_editor('', $id = 'event_desc', $prev_id = 'title', $media_buttons = true, $tab_index = 3); ?>
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
        <div class="handlediv" title="Click to toggle"><br />
        </div>
        <h3 class="hndle"><span>
          <?php _e('Event Date/Times','event_espresso'); ?>
          </span></h3>
        <div class="inside">

          <table width="100%" border="0" cellpadding="5">
            
            <tr valign="top">
            <td style="width:50%"><p><strong><?php _e('Registration Dates','event_espresso'); ?></strong><br /> <?php echo __('Registration Start:','event_espresso') . ' <input type="text" size="15" id="registration_start" class="datepicker" name="registration_start" value="" />'; ?><br />
<?php echo  __('Registration End:','event_espresso') . ' <input type="text" size="15" id="registration_end" class="datepicker" name="registration_end" value="" />'; ?></p>

                <p><strong><?php _e('Event Dates','event_espresso'); ?></strong><br /> <?php echo __('Event Start Date:','event_espresso') . ' <input type="text" size="15" id="start_date" class="datepicker" name="start_date" value="" />'; ?> <br />
<?php echo  __('Event End Date:','event_espresso') . ' <input type="text" size="15" id="end_date" class="datepicker" name="end_date" value="" />'; ?></p>

               <?php /*?> <p>
                    <br /> <?php echo __('Event Visible On:','event_espresso') . ' <input type="text" size="15" id="visible_on" class="datepicker" name="visible_on" value="" />'; ?> <br />
                </p><?php */?>
<?php echo get_option( 'event_espresso_re_active' ) == 1? '' :'<p><a href="http://eventespresso.com/?p=3319" target="_blank">Recurring Event Manager Now Available!</a></p>'; ?>
                </td>
              <?php // ADDED TIME REGISTRATION LIMITS ?>
			  <td>
			   <p><strong><?php _e('Registration Times','event_espresso'); ?></strong><br />
			  <?php echo event_espresso_timereg_editor();?>
			  </p>
			   <p><strong><?php _e('Event Times','event_espresso'); ?></strong><br /> 
			  <?php echo event_espresso_time_editor();?>
			  </p>
               <p><strong><?php _e('Current Time', 'event_espresso'); ?>:</strong> 
                   <?php echo date(get_option('date_format')).' '.date(get_option('time_format')); ?> <a href="options-general.php" target="_blank"><br /><?php _e('Change timezone and date format settings?', 'event_espresso'); ?></a></p>
			  </td>

            </tr>
			
          </table>
        </div>
      </div>
      <?php

        /**
         * Load the recurring events form if the add-on has been installed and activated.
         */
         if ( get_option( 'event_espresso_re_active' ) == 1 )
        {

            require_once(EVENT_ESPRESSO_RECURRENCE_FULL_PATH . "functions/re_view_functions.php");
            event_espresso_re_form( $events );
        }
?>


      <div style="display: block;" id="event-pricing" class="postbox">
        <div class="handlediv" title="Click to toggle"><br />
        </div>
        <h3 class="hndle"><span>
          <?php _e('Event Pricing','event_espresso'); ?>
          </span></h3>
        <div class="inside">
          <table width="100%" border="0" cellpadding="5">
            <tr valign="top">
              <td width="55%"><?php event_espresso_multi_price_update($event_id); //Standard pricing?></td>
              <?php
	//If the members addon is installed, define member only event settings
	if (get_option('events_members_active') == 'true'){ ?>
              <td width="50%"><?php echo event_espresso_member_only_pricing();//Show the the member only pricing options.?></td>
<?php
	}
?>
          </tr>
          </table>
        </div>
      </div>
      <h2><?php _e('Advanced Options', 'event_espresso'); ?></h2>
      <div style="display: block;" id="event-location" class="postbox">
        <div class="handlediv" title="Click to toggle"><br />
        </div>
        <h3 class="hndle"><span>
          <?php _e('Additional Event/Venue Information','event_espresso'); ?>
          </span></h3>
        <div class="inside">
          <table width="100%" border="0" cellpadding="5">
            <thead>
            </thead>
           
            <tr valign="top">
              <td>
              <p><strong><?php _e('Physical Location','event_espresso'); ?></strong></p>
                 <p>
                  <?php _e('Address:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="101"  type="text"  value="" name="address" />
                </p>
                <p>
                  <?php _e('Address 2:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="102"  type="text"  value="" name="address2" />
                </p>
                <p>
                  <?php _e('City:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="103"  type="text"  value="" name="city" />
                </p>
                 <p>
                  <?php _e('State:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="104"  type="text"  value="" name="state" />
                </p>
                 <p>
                  <?php _e('Zip/Postal Code:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="105"  type="text"  value="" name="zip" />
                </p>
                 <p>
                  <?php _e('Country:','event_espresso'); ?>
                  <br />
                  <input size="20" tabindex="106"  type="text"  value="" name="country" />
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
<input size="20"  type="text" tabindex="107" value="" name="phone" />
               </p>
               <p>
                 <?php _e('URL of Event:','event_espresso'); ?>
                 <br />
                 <textarea cols="30" rows="4" tabindex="108"  name="virtual_url"></textarea>
               </p>
               <p>
                 <?php _e('Call in Number:','event_espresso'); ?>
                 <br />
<input size="20" tabindex="109"  type="text"  value="" name="virtual_phone" />
               </p></td>
            </tr>
          </table>
        </div>
      </div>
      <!-- /event-location-->
       <div style="display: block;" id="confirmation-email" class="postbox">
        <div class="handlediv" title="Click to toggle"><br />
        </div>
        <h3 class="hndle"><span>
          <?php _e('Email Confirmation:','event_espresso')?>
          </span></h3>
        <div class="inside">
          <div id="emaildescriptiondivrich" class="postarea">
            <div style="float:left; width:400px;">
              <p><?php echo __('Send custom confirmation emails for this event?','event_espresso') . ' ' . select_input('send_mail', $values, 'N'); ?> <?php echo '<a class="ev_reg-fancylink" href="#custom_email_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a>'; ?></p>
               <p><?php _e('Use a', 'event_espresso');?> <a href="admin.php?page=event_emails">pre-existing email</a>? <?php echo espresso_db_dropdown(id, email_name, EVENTS_EMAIL_TABLE, email_name, $email_id, 'desc') . ' <a class="ev_reg-fancylink" href="#email_manager_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a>'; ?> </p>
                <p><strong>OR</strong></p>
              <p><?php _e('Create a custom email','event_espresso')?>:</p>
            </div>
            <div style="float:right; width:150px;">
              <p><a class="button toggleVisual">Visual</a> <a class="button toggleHTML">HTML</a></p>
            </div>
            <div class="clear"></div>
            <div class="postbox">
              <textarea name="conf_mail" class="theEditor" id="conf_mail" style="width:inherit; height:150px;"></textarea>
       <table id="email-confirmation-form" cellspacing="0">
        <tbody>
          <tr>
           <td class="aer-word-count"></td>
            <td class="autosave-info"><span><a class="ev_reg-fancylink" href="#custom_email_info"><?php _e('View Custom Email Tags', 'event_espresso'); ?></a>  | <a class="ev_reg-fancylink" href="#custom_email_example"> <?php _e('Email Example','event_espresso'); ?> </a></span></td>
          </tr>
        </tbody>
      </table>
            </div>
          </div>
        </div>
      </div>
      <!-- /confirmation-email-->
<?php
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/admin-files/event-management/new_event_post.php')){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."includes/admin-files/event-management/new_event_post.php");
	}
?>

     

    </div>
    <!-- /normal-sortables-->
  </div>
  <!-- /post-body-content -->
</div>
<!-- /post-body -->
<input type="hidden" name="action" value="add" />
<script type="text/javascript" charset="utf-8">

 jQuery(document).ready(function() {
        jQuery(".datepicker" ).datepicker({
			changeMonth: true,
			changeYear: true,
                        dateFormat: "yy-mm-dd",
                        showButtonPanel: true
		});
        jQuery("#start_date").change(function(){
            jQuery("#end_date").val(jQuery(this).val());
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
