<?php
//Displays reCAPTCHA form
$values=array(
	array('id'=>'N','text'=> __('No','event_espresso')),
	array('id'=>'Y','text'=> __('Yes','event_espresso'))
);
?>

<div class="metabox-holder">
  <div class="postbox">
    <div title="Click to toggle" class="handlediv"><br />
    </div>
    <h3 class="hndle">
      <?php _e('Optional Event Settings','event_espresso'); ?>
    </h3>
    <div class="inside">
      <div class="padding">
        <ul>
          <li>
            <label for="surcharge">
              <?php _e(' Default Surcharge (this value will be automatically filled in for each price type when creating an event): ','event_espresso'); ?>
            </label>
            <input type="text" name="surcharge" size="2" value="<?php echo (!is_numeric($org_options['surcharge']))?'0.00':$org_options['surcharge'];?>" />
			<?php $org_options['surcharge_type'] = isset( $org_options['surcharge_type'] ) ? $org_options['surcharge_type'] : 'flat_rate'; ?>
            <select name="surcharge_type">
              <option value = "flat_rate" <?php selected($org_options['surcharge_type'], 'flat_rate') ?>>
              <?php _e('Flat Rate', 'event_espresso'); ?>
              </option>
              <option value = "pct" <?php selected($org_options['surcharge_type'], 'pct') ?>>
              <?php _e('Percent', 'event_espresso'); ?>
              </option>
            </select>
            <label for="surcharge_text">
              <?php _e('Display text (eg. Surcharge or Service Fee):','event_espresso'); ?>
            </label>
            <input type="text" name="surcharge_text" value="<?php echo isset($org_options['surcharge_text'])? $org_options['surcharge_text']:__('Surcharge', 'event_espresso');?>" />
          </li>
          <li>
            <label for="default_payment_status">
              <?php
					  $default_payment_status = array(
							array('id'=>'Incomplete','text'=> 'Incomplete (default)'),
							array('id' => 'Pending', 'text' => 'Pending'),
							//array('id' => 'Completed', 'text' => 'Completed')
						);
 _e(' Default Payment Status (this value will be automatically filled in for each person\'s payment status, until payment is made, for each event): ','event_espresso'); ?>
            </label>
            <?php echo select_input('default_payment_status', $default_payment_status, $org_options['default_payment_status']); ?> 
		  </li>
		  <li>
            <label for="default_promocode_usage">
              <?php
					  $default_promocode_usage = array(
							array('id'=>'N', 'text'=>  __("No Promo Codes", "event_espresso")),
							array('id'=>'G', 'text' => __("Global Promo Codes Only", "event_espresso")),
							array('id'=>'Y', 'text'=>  __("Global and Specific Promo Codes", "event_espresso")),
							array('id'=>'A', 'text'=>  __("All Promo Codes (even Non-Globals)", "event_espresso"))
						);
 _e(' Default Promocode Usage on New Events: ','event_espresso'); ?>
            </label>
            <?php echo select_input('default_promocode_usage', $default_promocode_usage, isset($org_options['default_promocode_usage']) ? $org_options['default_promocode_usage'] : 'N'); ?> 
		  </li>
		  <li>
			   <label for="ticket_reservation_time">
              <?php
 _e('Ticket Reservation Time (number of minutes registrants have to complete their registration before others can register in their place. Longer times (eg, 60 minutes) are good because they reduce the likelyhood of accidental overbooking, but shorter times (eg, 15 minutes) reduce wait time for registrants who are waiting on an abandoned registration)  ','event_espresso'); ?>
            </label>
			  <input type="text" id='ticket_reservation_time' name="ticket_reservation_time" size="2" value="<?php echo (isset($org_options['ticket_reservation_time']))?$org_options['ticket_reservation_time'] : 30;?>" /> <?php _e("minutes", "event_espresso");?>
		  </li>
          <li>
            <label for="espresso_dashboard_widget">
              <?php _e('Show the Upcoming Events widget in the dashboard?','event_espresso'); ?>
            </label>
            <?php	echo select_input('espresso_dashboard_widget', $values, isset($org_options['espresso_dashboard_widget']) ? $org_options['espresso_dashboard_widget'] : '');?>
            <?php _e('Show the next', 'event_espresso'); ?>
            <input name="events_in_dasboard" size="5" style="width:50px;" type="text" value="<?php echo !isset($org_options['events_in_dasboard']) || $org_options['events_in_dasboard'] == ''? '30':stripslashes_deep($org_options['events_in_dasboard']);?>" />
            <?php _e('days of events in the dashboard.', 'event_espresso'); ?>
          </li>
          <li>
            <label for="time_reg_limit">
              <?php _e('Use registration limits on time slots?<br />
							<em class="important">(This function is experimental and may not function as expected. You should adjust your attendee limit accordingly.)</em>','event_espresso'); ?>
              <br><em class="important">
                <?php _e('It should not be used for events where group registrations are enabled.', 'event_espresso'); ?>
              </em>
            </label>
            <?php	echo select_input('time_reg_limit', $values, isset($org_options['time_reg_limit']) ? $org_options['time_reg_limit'] : '');?>
          </li>
          <li>
            <label>
              <?php _e('Use a custom time zone for each event?','event_espresso'); ?>
            </label>
            <?php echo select_input('use_event_timezones', $values, isset($org_options['use_event_timezones']) ? $org_options['use_event_timezones'] : ''); ?> <br />
          </li>
          <li>
            <label for="skip_confirmation_page">
              <?php _e('Skip Confirmation Page during Registration Process?','event_espresso'); ?>
            </label>
            <?php echo select_input('skip_confirmation_page', $values, isset($org_options['skip_confirmation_page']) ? $org_options['skip_confirmation_page'] : ''); ?> 
		 </li>
         <li>
            <label for="use_attendee_pre_approval">
              <?php _e('Enable attendee pre-approval feature?','event_espresso'); ?>
            </label>
            <?php echo select_input('use_attendee_pre_approval', $values, isset($org_options['use_attendee_pre_approval']) ? $org_options['use_attendee_pre_approval'] : ''); ?> 
		 </li>
          <li>
            <label>
              <?php _e('Show payment options for "Pending Payments" on the Payment Overview page?','event_espresso'); ?>
            </label>
            <?php echo select_input('show_pending_payment_options', $values, isset($org_options['show_pending_payment_options']) ? $org_options['show_pending_payment_options'] : ''); ?> <br />
          </li>
		  <?php if ( function_exists( 'event_espresso_coupon_payment_page' ) ) : ?>
		  <li>
            <label>
              <?php _e('Allow discounts in the shopping cart?','event_espresso'); ?>
            </label>
            <?php echo select_input('allow_mer_discounts', $values, isset($org_options['allow_mer_discounts']) ? $org_options['allow_mer_discounts'] : ''); ?> <br />
          </li>
		  <?php endif; ?>
		  <?php if ( function_exists( 'event_espresso_groupon_payment_page' ) ) : ?>
		   <li>
            <label>
              <?php _e('Allow voucher codes in the shopping cart?','event_espresso'); ?>
            </label>
            <?php echo select_input('allow_mer_vouchers', $values, isset($org_options['allow_mer_vouchers']) ? $org_options['allow_mer_vouchers'] : ''); ?> <br />
          </li>
		  <?php endif; ?>
          <li>
            <label>
              <?php _e('Use the Venue Manager?','event_espresso'); ?>
            </label>
            <?php echo select_input('use_venue_manager', $values, isset($org_options['use_venue_manager']) ? $org_options['use_venue_manager'] : ''); ?> <br />
          </li>
          <li>
            <label>
              <?php _e('Use the Staff Manager?','event_espresso'); ?>
            </label>
            <?php echo select_input('use_personnel_manager', $values, isset($org_options['use_personnel_manager']) ? $org_options['use_personnel_manager'] : ''); ?> <br />
          </li>
          <li>
            <label>
              <?php _e('Use full logging?','event_espresso'); ?>
            </label>
            <?php echo select_input('full_logging', $values, isset($org_options['full_logging']) ? $org_options['full_logging'] : ''); ?> <br />
          </li>
          <li>
            <label>
              <?php _e('Show a link to Event Espresso in your event pages?','event_espresso'); ?>
            </label>
            <?php echo select_input('show_reg_footer', $values, isset($org_options['show_reg_footer'])?$org_options['show_reg_footer']:''); ?>
            <?php _e('Affiliate ID:', 'event_espresso'); ?>
            <input name="affiliate_id" size="10" style="width:70px;" type="text" value="<?php echo isset($org_options['affiliate_id'])&&$org_options['affiliate_id'] != ''? stripslashes_deep($org_options['affiliate_id']):'0';?>" />
            <?php _e('(optional)', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=affiliate_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
            <div id="affiliate_info" class="pop-help" style="display:none">
                          <h2>
                            <?php _e('Affiliate Details', 'event_espresso'); ?>
                          </h2>
                          <p>
                            <?php _e('Promote Event Espresso and earn cash!', 'event_espresso'); ?>
                          </p>
                          <p>Get paid by helping other event managers understand the power of Event Espresso by becoming an affiliate.</p><ol><li>Go to the <a href="http://eventespresso.com/wp-content/plugins/wp-affiliate-platform/affiliates/register.php?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Promote+Event+Espresso<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=general_settings_tab" target="_blank">affiliate sign up page</a> to get your affiliate link.</li><li>All affiliates earn 20% from each sale.</li><li>Payments are made only through PayPal.</li><li>Payments are sent at the beginning of each month for the sales of the previous month.</li><li>Payments will be made regardless of the sales volume. There is no  minimum.</li><li>You can create your own banner or use the ones below:</li></ol>
                        <p>
                            <a href="http://eventespresso.com/affiliates/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Banners+and+More+Info<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=general_settings_tab" target="_blank"><?php _e('Banners and More Info >>', 'event_espresso'); ?></a>
                          </p>
                        </div>
          </li>
        </ul>
        <p>
          <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_setting_3" />
        </p>
      </div>
    </div>
  </div>
</div>
