<?php
//Displays reCAPTCHA form
$values=array(					
	array('id'=>'N','text'=> __('No','event_espresso')),
	array('id'=>'Y','text'=> __('Yes','event_espresso'))
);
?>
          <div class="box-mid-head">
            <h2 class="fugue f-footer">
              <?php _e('Optional Event Settings','event_espresso'); ?>
            </h2>
          </div>
          <div class="box-mid-body" id="toggle5">
            <div class="padding">
              <ul>
              <li>
                  <label for="espresso_dashboard_widget">
                    <?php _e('Show the Upcoming Events widget in the dashboard?','event_espresso'); ?>
                  <?php	echo select_input('espresso_dashboard_widget', $values, $org_options['espresso_dashboard_widget']);?>
                	<?php _e('Show the next', 'event_espresso'); ?> <input name="events_in_dasboard" size="5" style="width:50px;" type="text" value="<?php echo $org_options['events_in_dasboard'] == ''? '30':stripslashes_deep($org_options['events_in_dasboard']);?>" /> <?php _e('days of events in the dashboard.', 'event_espresso'); ?>
                </li>
                <li>
                  <label for="time_reg_limit">
                    <?php _e('Use registration limits on time slots?','event_espresso'); ?>
                  <?php	echo select_input('time_reg_limit', $values, $org_options['time_reg_limit']);?>
                </li>
                <li>
                  <label for="display_short_description_in_event_list">
                    <?php _e('Display short descriptions in the event listings? (Be sure to use the "More..." tag in your event description)','event_espresso'); ?></label> 
                  <?php echo select_input('display_short_description_in_event_list', $values, $org_options['display_short_description_in_event_list']); ?>
                </li>
                <li>
                  <label for="display_address_in_event_list">
                    <?php _e('Display adresses in the event listings?','event_espresso'); ?>
                  <?php echo select_input('display_address_in_event_list', $values, $org_options['display_address_in_event_list']); ?>
                </li>
               	<li>
                  <label for="use_custom_post_types">
                    <?php _e('Use the custom post types feature?','event_espresso'); ?>
                  <?php echo select_input('use_custom_post_types', $values, $org_options['use_custom_post_types']); ?>
                </li>
              </ul>
              <div id="recaptcha_info" style="display:none">
               <h2><?php _e('reCAPTCHA Information', 'event_espresso'); ?></h2>
						<p><?php _e('reCAPTCHA helps prevent automated abuse of your site (such as comment spam or bogus registrations) by using a <a href="http://recaptcha.net/captcha.html">CAPTCHA</a> to ensure that only humans perform certain actions.', 'event_espresso'); ?></p>
			<p><?php _e('You must sign up for a', 'event_espresso'); ?> <a href="https://admin.recaptcha.net/accounts/signup/?next=%2Frecaptcha%2Fsites%2F" target="_blank"><?php _e('free reCAPTCHA', 'event_espresso'); ?></a> <?php _e('account to use it with this plugin. If you already have a reCAPTCHA account enter your "Public" and "Private" keys on this page.', 'event_espresso'); ?></p>
              </div>
              
               <p>
        <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_setting_3" />
      </p>
            </div>
          </div>