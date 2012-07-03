<?php
//Event Registration Subpage 1 - Configure Organization
function organization_config_mnu()	{
	wp_tiny_mce( false , // true makes the editor "teeny"
		array(
			"editor_selector" => "theEditor"//This is the class name of your text field
		)
	);
	global $wpdb, $org_options;
	//print_r($timezoneTable);

	if (isset($_POST['update_org'])) {
		$org_options['organization'] = stripslashes_deep($_POST['org_name']);
		$org_options['organization_street1'] = $_POST['org_street1'];
		$org_options['organization_street2'] = $_POST['org_street2'];
		$org_options['organization_city'] = $_POST['org_city'];
		$org_options['organization_state'] = $_POST['org_state'];
		$org_options['organization_zip'] = $_POST['org_zip'];
		$org_options['organization_country'] = $_POST['org_country'];
		$org_options['organization_timezone'] = $_POST['organization_timezone'];
		$org_options['contact_email'] = $_POST['email'];
		$org_options['currency_format'] = $_POST['currency_format'];
		$org_options['currency_symbol'] = $_POST['currency_format'];
		$org_options['events_listing_type'] = $_POST['events_listing_type'];
		$org_options['expire_on_registration_end'] = $_POST['expire_on_registration_end'];
		$org_options['event_page_id'] = $_POST['event_page_id'];
		$org_options['return_url'] = $_POST['return_url'];
		$org_options['cancel_return'] = $_POST['cancel_return'];
		$org_options['notify_url'] = $_POST['notify_url'];
		$org_options['use_sandbox'] = $_POST['use_sandbox'];
		$org_options['default_mail'] = $_POST['default_mail'];
		$org_options['payment_subject'] = $_POST['payment_subject'];
		$org_options['payment_message'] = $_POST['payment_message'];
		$org_options['message'] = $_POST['success_message'];
		$org_options['email_before_payment'] = $_POST['email_before_payment'];
		$org_options['use_captcha'] = $_POST['use_captcha'];
		$org_options['recaptcha_publickey'] = $_POST['recaptcha_publickey'];
		$org_options['recaptcha_privatekey'] = $_POST['recaptcha_privatekey'];
		$org_options['recaptcha_theme'] = $_POST['recaptcha_theme'];
		$org_options['recaptcha_width'] = $_POST['recaptcha_width'];
		$org_options['recaptcha_language'] = $_POST['recaptcha_language'];
		$org_options['use_custom_post'] = $_POST['use_custom_post'];
		$org_options['espresso_dashboard_widget'] = $_POST['espresso_dashboard_widget'];
		$org_options['time_reg_limit'] = $_POST['time_reg_limit'];
		$org_options['use_custom_post_types'] = $_POST['use_custom_post_types'];
		$org_options['display_short_description_in_event_list'] = $_POST['display_short_description_in_event_list'];
		$org_options['display_address_in_event_list'] = $_POST['display_address_in_event_list'];
		$currency_format = getCountryFullData($org_options['organization_country']);
		switch ($currency_format['iso_code_3']){
			case 'USA':
			$org_options['currency_symbol'] = '$';
			break;
							
			case 'AUS':
			$org_options['currency_symbol'] = 'A $';
			break;
							
			case 'GBR':
			$org_options['currency_symbol'] = '&pound;';
			break;
			
			case 'NOR':
			$org_options['currency_symbol'] = 'NOK ';
			break;
			
			case 'BRA':
			$org_options['currency_symbol'] = 'R$';
			break;
							
			case 'CAN':
			$org_options['currency_symbol'] = 'C $';
			break;
							
			case 'JPN':
			$org_options['currency_symbol'] = '&yen;';
			break;
			
			case 'SWE':
			$org_options['currency_symbol'] = 'Kr. ';
			break;
			
			case 'DNK':
			$org_options['currency_symbol'] = 'Kr. ';
			break;
							
			default:
			$org_options['currency_symbol'] = '$';
			break;
		}
	if (getCountryZoneId($org_options['organization_country']) == '2'){
		$org_options['currency_symbol'] = '&#8364;';//Creates the symbol for the Euro
	}
	update_option( 'events_organization_settings', $org_options);
	echo '<div id="message" class="updated fade"><p><strong>'.__('Organization details saved.','event_espresso').'</strong></p></div>';

}

$org_options = get_option('events_organization_settings');
$values=array(					
		array('id'=>'Y','text'=> __('Yes','event_espresso')),
		array('id'=>'N','text'=> __('No','event_espresso')));
?>

<div id="configure_organization_form" class="wrap meta-box-sortables ui-sortable">
  <div id="icon-options-event" class="icon32"> </div>
  <h2>
    <?php _e('General Settings','event_espresso'); ?>
  </h2>
  <div id="event_espresso-col-left" style="width:70%;">
    <form class="espresso_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
      <ul id="event_espresso-sortables">
        <li>
          <div class="box-mid-head">
            <h2 class="fugue f-wrench">
              <?php _e('Organization Settings','event_espresso'); ?>
            </h2>
          </div>
          <div class="box-mid-body" id="toggle2">
            <div class="padding">
              <ul>
                <li>
                  <label for="org_name">
                    <?php _e('Organization Name:','event_espresso'); ?>
                  </label>
                  <input type="text" name="org_name" size="45" value="<?php echo stripslashes_deep($org_options['organization']);?>" />
                </li>
                <li>
                  <label for="org_street1">
                    <?php _e('Organization Street 1:','event_espresso'); ?>
                  </label>
                  <input type="text" name="org_street1" size="45" value="<?php echo $org_options['organization_street1'];?>" />
                </li>
                <li>
                  <label for="org_street2">
                    <?php _e('Organization Street 2:','event_espresso'); ?>
                  </label>
                  <input type="text" name="org_street2" size="45" value="<?php echo $org_options['organization_street2'];?>" />
                </li>
                <li>
                  <label for="org_city">
                    <?php _e('Organization City:','event_espresso'); ?>
                  </label>
                  <input type="text" name="org_city" size="45" value="<?php echo $org_options['organization_city'];?>" />
                </li>
                <li>
                  <label for="org_state">
                    <?php _e('Organization State:','event_espresso'); ?>
                  </label>
                  <input type="text" name="org_state" size="45" value="<?php echo $org_options['organization_state'];?>" />
                </li>
                <li>
                  <label for="org_zip">
                    <?php _e('Organization Zip/Postal Code:','event_espresso'); ?>
                  </label>
                  <input type="text" name="org_zip" size="10" value="<?php echo $org_options['organization_zip'];?>" />
                </li>
                <li>
                  <label for="org_country">
                    <?php _e('Organization Country:','event_espresso'); ?>
                  </label>
                  <?php printCountriesSelector("org_country", $org_options['organization_country']);?> </li>
                <li>
                  <label for="email">
                    <?php _e('Primary contact email:','event_espresso'); ?>
                  </label>
                  <input type="text" name="email" size="45" value="<?php echo $org_options['contact_email'];?>" />
                </li>
                <li><h4><?php _e('Time and Date Settings', 'event_espresso'); ?></h4></li>
				<li><strong><?php _e('Current Time', 'event_espresso'); ?>:</strong> 
                   <?php echo date(get_option('date_format')); ?> <a href="options-general.php" target="_blank"><br /><?php _e('Change timezone and date format settings?', 'event_espresso'); ?></a><br /><span class="red_text"><?php _e('Note:', 'event_espresso'); ?></span> <?php _e('Only use the timezone names. Numbers will not work.', 'event_espresso'); ?>
				</li>			
                <li>
                  <label for="expire_on_registration_end">
                    <?php _e('Events expire on registration end date?','event_espresso'); ?> <?php echo select_input('expire_on_registration_end', $values, $org_options['expire_on_registration_end']);?>
                  </label>
                   </li>
              </ul>
               <p>
        <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_1" />
      </p>
            </div>
          </div>
        </li>
        <li>
          <div class="box-mid-head">
            <h2 class="fugue f-doc-code">
              <?php _e('Page Settings','event_espresso'); ?>
            </h2>
          </div>
          <div class="box-mid-body" id="toggle3">
            <div class="padding"> <a name="page_settings" id="page_settings"></a>
               <?php 
			 	 if(($org_options['event_page_id']==('0'||'') || $org_options['return_url']==('0'||'') || $org_options['notify_url']==('0'||''))){ 
			 		 espresso_create_default_pages(); 
				 }
				?>
			  
              <p>
                <label for="event_page_id">
                  <?php _e('Main registration page:','event_espresso'); ?>
                </label>
                <select name="event_page_id">
                  <option value="0">
                  <?php _e ('Main page'); ?>
                  </option>
                  <?php parent_dropdown ($default=$org_options['event_page_id']); ?>
                </select>
                <a class="ev_reg-fancylink" href="#registration_page_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a><br />
                <font  size="-2">(
                <?php _e('This page should contain the', 'event_espresso'); ?>
                <strong>[ESPRESSO_EVENTS]</strong>
                <?php _e('shortcode. <br />
This page can be hidden from navigation if desired, <br />
but should always contain the [ESPRESSO_EVENTS] shortcode.', 'event_espresso'); ?>
                </font></p>
              <div id="registration_page_info" style="display:none">
                <?php _e('<h2>Main Events Page</h2>
						<p>This is the page that displays your events.</p>
						<p>This page should contain the <strong>[ESPRESSO_EVENTS]</strong> shortcode.</p>','event_espresso'); ?>
              </div>
              <p>
                <label for="return_url">
                  <?php _e('Auto Return URL (Thank You and Return Payment page):','event_espresso'); ?>
                </label>
                <select name="return_url">
                  <option value="0">
                  <?php _e ('Main page', 'event_espresso'); ?>
                  </option>
                  <?php parent_dropdown ($default=$org_options['return_url']); ?>
                </select>
                <a class="ev_reg-fancylink" href="#return_url_info" target="_blank"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a><br />
                <font size="-2">(
                <?php _e('This page should contain the', 'event_espresso'); ?>
                <strong>[ESPRESSO_PAYMENTS]</strong>
                <?php _e('shortcode. <br />
This page should hidden from your navigation, <br />
but still viewable to the public (not password protected.)', 'event_espresso'); ?>
                </font></p>
              <div id="return_url_info" style="display:none">
                <?php _e('<h2>Auto Return URL</h2>
						<p>The URL to which the payer"s browser is redirected after completing the payment; for example, a URL on your site that displays a "Thank you for your payment" page.</p>
						<p>This page should contain the <strong>[ESPRESSO_PAYMENTS]</strong> shortcode.</p>
						<p class="red_text"><strong>ATTENTION:</strong><br />This page should be hidden from from your navigation menu. Exclude pages by using the "Exclude Pages" plugin from http://wordpress.org/extend/plugins/exclude-pages/ or using the "exclude" parameter in your "wp_list_pages" template tag. Please refer to http://codex.wordpress.org/Template_Tags/wp_list_pages for more inforamation about excluding pages.</p>','event_espresso'); ?>
              </div>
              <p>
                <label for="cancel_return">
                  <?php _e('Cancel Return URL (used for cancelled payments):','event_espresso'); ?>
                </label>
                <select name="cancel_return">
                  <option value="0">
                  <?php _e ('Main page'); ?>
                  </option>
                  <?php parent_dropdown ($default=$org_options['cancel_return']); ?>
                </select>
                <a class="ev_reg-fancylink" href="#cancel_return_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a><br />
                <font  size="-2">(
                <?php _e('This should be a page on your website that contains a cancelled message. <br />
No short tags are needed. This page should hidden from your navigation, <br />
but still viewable to the public (not password protected.)', 'event_espresso'); ?>
                )</font></p>
              <div id="cancel_return_info" style="display:none">
                <?php _e('<h2>Cancel Return URL</h2>
						<p>A URL to which the payer\'s browser is redirected if payment is cancelled; for example, a URL on your website that displays a "Payment Canceled" page.</p>
						<p>This should be a page on your website that contains a cancelled message. No short tags are needed.</p>
						<p class="red_text"><strong>ATTENTION:</strong><br />This page should be hidden from from your navigation menu. Exclude pages by using the "Exclude Pages" plugin from http://wordpress.org/extend/plugins/exclude-pages/ or using the "exclude" parameter in your "wp_list_pages" template tag. Please refer to http://codex.wordpress.org/Template_Tags/wp_list_pages for more inforamation about excluding pages.</p>','event_espresso'); ?>
              </div>
              <p>
                <label for="notify_url">
                  <?php _e('Notify URL (used to process payments):','event_espresso'); ?>
                </label>
                <select name="notify_url">
                  <option value="0">
                  <?php _e ('Main page'); ?>
                  </option>
                  <?php parent_dropdown ($default=$org_options['notify_url']); ?>
                </select>
                <a class="ev_reg-fancylink" href="#notify_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a><br />
                <font size="-2">(
                <?php _e('This page should contain the', 'event_espresso'); ?>
                <strong>[ESPRESSO_TXN_PAGE]</strong>
                <?php _e('shortcode. <br />
This page should hidden from your navigation, <br />
but still viewable to the public (not password protected.)', 'event_espresso'); ?>
                </font></p>
              <div id="notify_url_info" style="display:none">
                <?php _e('<h2>Notify URL</h2>
						<p>The URL to which PayPal posts information about the transaction, in the form of Instant Payment Notification messages.</p>
						<p>This page should contain the <strong>[ESPRESSO_TXN_PAGE]</strong> shortcode.</p>
						<p class="red_text"><strong>ATTENTION:</strong><br />This page should be hidden from from your navigation menu. Exclude pages by using the "Exclude Pages" plugin from http://wordpress.org/extend/plugins/exclude-pages/ or using the "exclude" parameter in your "wp_list_pages" template tag. Please refer to http://codex.wordpress.org/Template_Tags/wp_list_pages for more inforamation about excluding pages.</p>','event_espresso'); ?>
              </div>
               <p>
        <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_2" />
      </p>
            </div>
          </div>
        </li>
        <li>
          <div class="box-mid-head">
            <h2 class="fugue f-footer">
              <?php _e('Email Settings','event_espresso'); ?>
            </h2>
          </div>
          <div class="box-mid-body" id="toggle5">
            <div class="padding">
              <?php
	$values=array(					
        array('id'=>'Y','text'=> __('Yes','event_espresso')),
        array('id'=>'N','text'=> __('No','event_espresso')));	
?>
              <p>
                <?php _e('Send payment confirmation emails?','event_espresso'); 
	echo select_input('default_mail', $values, $org_options['default_mail']);?>
              </p>
              <p>
                <?php _e('Send registration confirmation emails before payment is received?','event_espresso'); 
	echo select_input('email_before_payment', $values, $org_options['email_before_payment']);?>
              </p>
              <strong>
              <?php _e('Payment Confirmation Email:','event_espresso'); ?>
              </strong>
              <p>
                <label for="payment_subject">
                  <?php _e('Email Subject:','event_espresso'); ?>
                </label>
                <input name="payment_subject" size="50" type="text" value="<?php echo $org_options['payment_subject'];?>" />
              </p>
              <div class="postbox">
                <textarea class="theEditor" id="payment_message" name="payment_message"><?php echo $org_options['payment_message'];?></textarea>
                <table id="payment-confirmation-form" cellspacing="0">
                  <tbody>
                    <tr>
                      <td class="aer-word-count"></td>
                      <td class="autosave-info"><span><a class="ev_reg-fancylink" href="#custom_email_info">
                        <?php _e('View Custom Email Tags','event_espresso'); ?>
                        </a></span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <strong>
              <?php _e('Default Registration Confirmation Email:','event_espresso'); ?>
              </strong>
              <div class="postbox">
                <textarea class="theEditor" id="success_message" name="success_message"><?php echo $org_options['message'];?></textarea>
                <table id="email-confirmation-form" cellspacing="0">
                  <tbody>
                    <tr>
                      <td class="aer-word-count"></td>
                      <td class="autosave-info"><span><a class="ev_reg-fancylink" href="#custom_email_info">
                        <?php _e('View Custom Email Tags','event_espresso'); ?>
                        </a> | <a class="ev_reg-fancylink" href="#custom_email_example">
                        <?php _e('Example','event_espresso'); ?>
                        </a></span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
               <p>
        <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_5" />
      </p>
            </div>
          </div>
          <div style="clear:both;"></div>
          </li>
          <li><h3><?php _e('Advanced Features', 'event_espresso'); ?></h3></li>
<?php 
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/recaptcha_form.php') || file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/template_files.php')  || file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/optional_event_settings.php')){

		if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/optional_event_settings.php')){
			echo '<li>';
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH. 'includes/admin-files/optional_event_settings.php');
			echo '</li>';
		}
		if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/recaptcha_form.php')){
			echo '<li>';
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH. 'includes/admin-files/recaptcha_form.php');
			echo '</li>';
		}
		if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/template_files.php')){
			echo '<li>';
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/template_files.php');
			echo '</li>';
		}
	}else{
		echo '<li>' . __('Purchase a <a href="http://eventespresso.com/download/" target="_blank">support license</a> to gain access to adavanced features such as CAPTCHA and <a href="http://eventespresso.com/features/customizable-event-listings/" target="_blank">Template Customization</a>.','event_espresso') . '</li>';
	}
?>  
        
      </ul>
      <input type="hidden" name="update_org" value="update" />
     
    </form>
  </div>
</div>

              
<?php event_espresso_display_right_column ();?>

<script type="text/javascript" charset="utf-8">
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
echo event_espresso_custom_email_info();
}
?>
