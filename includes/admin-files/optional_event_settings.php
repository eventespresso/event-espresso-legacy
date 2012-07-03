<?php
//Displays reCAPTCHA form
$values=array(					
	array('id'=>'N','text'=> __('No','event_espresso')),
	array('id'=>'Y','text'=> __('Yes','event_espresso'))
);
?>
          <div class="metabox-holder">
			<div class="postbox">
  			<h3>
              <?php _e('Optional Event Settings','event_espresso'); ?>
            </h3>
         
            <div class="padding">
              <ul>
                <li>
                <label for="surcharge">
                    
              <?php _e(' Default Surcharge (this value will be automatically filled in for each price type when creating an event): ','event_espresso'); ?>
                </label>
                <input type="text" name="surcharge" size="2" value="<?php echo (!is_numeric($org_options['surcharge']))?'0.00':$org_options['surcharge'];?>" />
                <select name="surcharge_type">
                        <option value = "flat_rate" <?php selected($org_options['surcharge_type'], 'flat_rate') ?>>Flat Rate</option>
                        <option value = "pct" <?php selected($org_options['surcharge_type'], 'pct') ?>>Percent</option>
                    </select>
              </li>
  
              <li>
                  <label for="espresso_dashboard_widget">
                    <?php _e('Show the Upcoming Events widget in the dashboard?','event_espresso'); ?></label>
                  <?php	echo select_input('espresso_dashboard_widget', $values, $org_options['espresso_dashboard_widget']);?>
                	<?php _e('Show the next', 'event_espresso'); ?> <input name="events_in_dasboard" size="5" style="width:50px;" type="text" value="<?php echo $org_options['events_in_dasboard'] == ''? '30':stripslashes_deep($org_options['events_in_dasboard']);?>" /> <?php _e('days of events in the dashboard.', 'event_espresso'); ?>
                </li>
                <li>
                  <label for="time_reg_limit">
                    <?php _e('Use registration limits on time slots?<br />
							<span style="color:red">(This function is experimental and may not function as expected. You should adjust your attendee limit accordingly.)</span>','event_espresso'); ?></label>
                  <?php	echo select_input('time_reg_limit', $values, $org_options['time_reg_limit']);?>
                </li>
                <li>
                  <label>
                    <?php _e('Use a custom time zone for each event?','event_espresso'); ?></label>
                  <?php echo select_input('use_event_timezones', $values, $org_options['use_event_timezones']); ?> <br />

                </li>
                <li>
                  <label for="display_short_description_in_event_list">
                    <?php _e('Display short descriptions in the event listings? (Be sure to use the "More..." tag in your event description)','event_espresso'); ?></label> 
                  <?php echo select_input('display_short_description_in_event_list', $values, $org_options['display_short_description_in_event_list']); ?>
                </li>
                <li>
                  <label for="display_address_in_event_list">
                    <?php _e('Display adresses in the event listings?','event_espresso'); ?></label>
                  <?php echo select_input('display_address_in_event_list', $values, $org_options['display_address_in_event_list']); ?>
                </li>
               	<li>
                  <label for="use_custom_post_types">
                    <?php _e('Use the custom post types feature?','event_espresso'); ?></label>
                  <?php echo select_input('use_custom_post_types', $values, $org_options['use_custom_post_types']); ?>
                </li>
                <li>
                  <label for="use_attendee_pre_approval">
                    <?php _e('Enable attendee pre-approval feature?','event_espresso'); ?></label>
                  <?php echo select_input('use_attendee_pre_approval', $values, $org_options['use_attendee_pre_approval']); ?>
                </li>
                <li>
                  <label>
                    <?php _e('Enable default style sheet?','event_espresso'); ?></label>
                  <?php echo select_input('enable_default_style', $values, $org_options['enable_default_style']); ?> <br />

                </li>
                <li>
                  <label>
                    <?php _e('Show payment options for "Pending Payments" on the Payment Overview page?','event_espresso'); ?></label>
                  <?php echo select_input('show_pending_payment_options', $values, $org_options['show_pending_payment_options']); ?> <br />

                </li>
                <li>
                  <label>
                    <?php _e('Use the Venue Manager?','event_espresso'); ?></label>
                  <?php echo select_input('use_venue_manager', $values, $org_options['use_venue_manager']); ?> <br />

                </li>
                <li>
                  <label>
                    <?php _e('Use the Staff Manager?','event_espresso'); ?></label>
                  <?php echo select_input('use_personnel_manager', $values, $org_options['use_personnel_manager']); ?> <br />

                </li>
              </ul>
             
              
               <p>
        <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_setting_3" />
      </p>
            </div>
          </div>
          </div>
