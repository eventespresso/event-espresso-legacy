<?php
function add_new_event(){
	global $wpdb, $org_options;
	wp_tiny_mce( false , // true makes the editor "teeny"
		array(
			"editor_selector" => "theEditor"//This is the class name of your text field
		)
	);
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

    $status=array(array('id'=>'A','text'=> __('Primary','event_espresso')), array('id'=>'S','text'=> __('Secondary','event_espresso')), array('id'=>'O','text'=> __('Ongoing','event_espresso')), array('id'=>'D','text'=> __('Deleted','event_espresso')));

   postbox('event-status', 'Event Options',
   '<p>' .__('Attendee Limit','event_espresso') . ': <input name="reg_limit" size="10" type="text" value="' . $reg_limit . '"><br />' .
		'(' .__('leave blank for unlimited','event_espresso') . ')</p>' .
		'<p>' .__('Allow group registrations?','event_espresso') . ' ' . select_input('allow_multiple', $values, 'N') .
		'<p>' .__('Max Group Registrants','event_espresso') . ': <input type="text" name="additional_limit" value="' . $additional_limit . '" size="4">' .
		
		'<p><strong>' .__('Advanced Options:','event_espresso') . '</strong></p>' .
		
		'<p>' .__('Is this an active event? ','event_espresso') . __( select_input('is_active', $values, 'Y')) . '</p>' .
		'<p>' .__('Event Status: ','event_espresso') . __( select_input('event_status', $status, $event_status)) . ' <a class="ev_reg-fancylink" href="#status_types_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a></p>' .
		'<p>' .__('Display  description? ','event_espresso') . select_input('display_desc', $values, 'Y') . '</p>' .
		'<p>' .__('Display  registration form? ','event_espresso') . select_input('display_reg_form', $values, 'Y') . '</p>' .
		
		'<p>' .__('Use an alternate registration page?','event_espresso') . '<br />
			<input name="externalURL" size="20" type="text" value="' . $externalURL . '">  <a class="ev_reg-fancylink" href="#external_URL_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a></p>'.
		'<p>' .__('Use an alternate email address?','event_espresso') . '<br />
			<input name="alt_email" size="20" type="text" value="' . $alt_email . '"> <a class="ev_reg-fancylink" href="#alt_email_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a><br /></p>'
	);

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

    

    <div style="display: block;" id="event-discounts" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class="hndle"><span>
        <?php _e('Event Promotions','event_espresso'); ?>
        </span></h3>
      <div class="inside">
      <p><strong><?php _e('Early Registration Discount','event_espresso'); ?></strong></p>

                <p><?php _e('End Date:','event_espresso'); ?><input type="text" size="12" id="early_disc_date" name="early_disc_date" value="<?php echo $early_disc_date; ?>"/></p>
                <p><?php _e('Amount:','event_espresso'); ?><input type="text" size="3" id="early_disc" name="early_disc" value="<?php echo $early_disc; ?>"/>
				<?php _e('Percentage:','event_espresso');
					echo select_input('early_disc_percentage', $values, 'Y');?></p>
				<p><?php _e('(Leave blank if not applicable)', 'event_espresso'); ?></p>
        <p>
          <?php _e('Allow discount codes? ','event_espresso');
			echo select_input('use_coupon_code', $values, 'N'); ?>
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
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class="hndle"><span>
        <?php _e('Event Questions','event_espresso'); ?>
        </span></h3>
      <div class="inside">

                <p><strong><?php _e('Question Groups','event_espresso'); ?></strong><br />
<?php _e('Add a pre-populated <a href="admin.php?page=form_groups" target="_blank">group</a> of <a href="admin.php?page=form_builder" target="_blank">questions</a> to your event. The personal information group is rquired for all events.', 'event_espresso'); ?>
</p>

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
                                $checked = $question_group->system_group == 1?' checked="checked" ':'';
                                 $visibility = $question_group->system_group == 1?'style="visibility:hidden"':'';

					echo '<p id="event-question-group-' . $group_id . '">
                                             <input value="' . $question_group_id . '" type="checkbox" ' . $checked . $visibility . ' name="question_groups[' . $question_group_id . ']" ' . $checked . ' /> <a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=' . $question_group_id . '" title="edit" target="_blank">' . $group_name. '</a></p>';
			}
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
          <?php  echo '<a href="#" class="button" onclick="prompt(&#39;Event URL:&#39;, \'' . get_option('siteurl') . '/?page_id=' . $org_options['event_page_id'] . '&amp;regevent_action=register&amp;event_id=' . $event_id . '\'); return false;">' . __('Get URL') . '</a>'?>
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
                   <?php echo date('l jS \of F Y h:i:s A'); ?> <a href="options-general.php" target="_blank"><br /><?php _e('Change timezone and date format settings?', 'event_espresso'); ?></a></p>
			  </td>

            </tr>
			<?php /*?><tr>
			<td>
			<?php echo eventespresso_ddtimezone(); ?>
			</td>
			</tr><?php */?>
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
          <?php _e('Event Location','event_espresso'); ?>
          </span></h3>
        <div class="inside">
          <table width="100%" border="0" cellpadding="5">
            <thead>
            </thead>
            <tr>
              <th align="left"><?php _e('Physical Location','event_espresso'); ?></th>
              <th align="left"><?php _e('Virtual Location','event_espresso'); ?></th>
                <td></thead></td>
            </tr>
            <tr valign="top">
              <td>
              
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
              <td>
              <p>
                  <?php _e('Phone:','event_espresso'); ?>
                  <input size="20"  type="text" tabindex="107" value="" name="phone" />
                </p>
               <p>
                  <?php _e('URL of Event:','event_espresso'); ?>
                  <br />
                  <textarea cols="30" rows="4" tabindex="108"  name="virtual_url"></textarea>
                </p>
                <p>
                  <?php _e('Call in Number:','event_espresso'); ?>
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
               <p><?php _e('Use a <a href="admin.php?page=event_emails">pre-existing email</a>? ', 'event_espresso'); echo espresso_db_dropdown(id, email_name, EVENTS_EMAIL_TABLE, email_name, $email_id, 'desc') . ' <a class="ev_reg-fancylink" href="#email_manager_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a>'; ?> </p>
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
      <div style="display: block;" id="event-post" class="postbox">
      <div class="handlediv" title="Click to toggle"><br />
      </div>
      <h3 class="hndle"><span>
        <?php _e('Create a Post','event_espresso'); ?>
        </span></h3>
      <div class="inside">
        <p>
             <?php _e('Create a post for this event?','event_espresso'); ?>

        <?php echo select_input('create_post', $values, 'N'); ?> </p>
                     <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
						 <?php
						 	global $current_user;
							get_currentuserinfo();
							$authors = get_editable_user_ids( $current_user->ID, true, 'post' );
							//echo $current_user->ID;
							?>
<?php /*?><p><?php _e('Category', 'event_espresso'); ?>: <?php wp_dropdown_categories(array('orderby'=> 'name','order' => 'ASC', 'selected' => $category, 'hide_empty' => 0)); ?></p><?php */?>
						 
		<?php $post_data=get_post($post_id);
		//post_tags_meta_box($post, $box);
		$author = $post_data->post_author;
		$authors = get_editable_user_ids( $author, true, 'post' );
		//$category=get_the_category($post_id);
		//$category=$category[0]->cat_ID;
		$post_types=array(array('id'=>'espresso_event','text'=> __('Espresso Event','event_espresso')), array('id'=>'post','text'=> __('Post','event_espresso')), array('id'=>'page','text'=> __('Page','event_espresso')));
		$tags=get_the_tags($post_id);
		if ($tags) {
			foreach ($tags as $k => $v) {
				$tag[$k] = $v->name;
			}
			$tags=join(', ',$tag);
		}?>
        <p><?php _e('Author', 'event_espresso'); ?>: <?php wp_dropdown_users(array('include' => $authors, 'selected' => $current_user->ID)); ?></p>
        <p><?php _e('Post Type', 'event_espresso'); ?>: <?php echo select_input('post_type', $post_types, 'espresso_event') ?></p>
		<p><?php _e('Tags', 'event_espresso'); ?>: <input id="post_tags" name="post_tags" size="20" type="text" /></p>
       	<p><?php _e('Post Categories:', 'event_espresso'); ?> </p>  
		<?php // Get existing post data, if it exists
		require_once( 'includes/meta-boxes.php');
		post_categories_meta_box($post_data, $box);?>
		
		<p><?php _e('Post templates are stored in the "templates" directory of the plugin.', 'event_espresso'); ?></p>
						 <!-- if post templates installed, post template -->
                          <?php  echo espresso_create_meta_box();
	echo espresso_event_display_meta_box($post_id);?>
      </div>
    </div>
    <!-- /event-post -->

     

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

$tablename 		= EVENTS_DETAIL_TABLE;
$next_increment 	= 0;
$qShowStatus 		= "SHOW TABLE STATUS LIKE '$tablename'";
$qShowStatusResult 	= mysql_query($qShowStatus) or die ( "Query failed: " . mysql_error() . "<br/>" . $qShowStatus );

$row = mysql_fetch_assoc($qShowStatusResult);
$next_increment = $row['Auto_increment'];

//echo '<p class="clear_both">next increment number: [' .$next_increment . ']</p>';

}
