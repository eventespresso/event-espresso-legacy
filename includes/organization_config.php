<?php

//Event Registration Subpage 1 - Configure Organization
function organization_config_mnu() {
	global $org_options, $espresso_premium, $espresso_check_for_updates;
	if (isset($_POST['update_org'])) {
		$org_options['organization'] = isset($_POST['org_name']) && !empty($_POST['org_name']) ? stripslashes_deep($_POST['org_name']) : '';
		$org_options['organization_street1'] = isset($_POST['org_street1']) && !empty($_POST['org_street1']) ? stripslashes_deep($_POST['org_street1']) : '';
		$org_options['organization_street2'] = isset($_POST['org_street2']) && !empty($_POST['org_street2']) ? stripslashes_deep($_POST['org_street2']) : '';
		$org_options['organization_city'] = isset($_POST['org_city']) && !empty($_POST['org_city']) ? stripslashes_deep($_POST['org_city']) : '';
		$org_options['organization_state'] = isset($_POST['org_state']) && !empty($_POST['org_state']) ? stripslashes_deep($_POST['org_state']) : '';
		$org_options['organization_zip'] = isset($_POST['org_zip']) && !empty($_POST['org_zip']) ? stripslashes_deep($_POST['org_zip']) : '';
		$org_options['organization_country'] = isset($_POST['org_country']) && !empty($_POST['org_country']) ? stripslashes_deep($_POST['org_country']) : '';
		$org_options['organization_country'] = isset($_POST['org_country']) && !empty($_POST['org_country']) ? $_POST['org_country'] : '';
		$org_options['contact_email'] = isset($_POST['email']) && !empty($_POST['email']) ? $_POST['email'] : '';
		$org_options['expire_on_registration_end'] = isset($_POST['expire_on_registration_end']) && !empty($_POST['expire_on_registration_end']) ? $_POST['expire_on_registration_end'] : '';
		$org_options['event_page_id'] = isset($_POST['event_page_id']) && !empty($_POST['event_page_id']) ? $_POST['event_page_id'] : '';
		$org_options['return_url'] = isset($_POST['return_url']) && !empty($_POST['return_url']) ? $_POST['return_url'] : '';
		$org_options['cancel_return'] = isset($_POST['cancel_return']) && !empty($_POST['cancel_return']) ? $_POST['cancel_return'] : '';
		$org_options['notify_url'] = isset($_POST['notify_url']) && !empty($_POST['notify_url']) ? $_POST['notify_url'] : '';
		$org_options['events_in_dasboard'] = isset($_POST['events_in_dasboard']) && !empty($_POST['events_in_dasboard']) ? $_POST['events_in_dasboard'] : '';
		$org_options['default_mail'] = isset($_POST['default_mail']) && !empty($_POST['default_mail']) ? $_POST['default_mail'] : '';
		$org_options['payment_subject'] = isset($_POST['payment_subject']) && !empty($_POST['payment_subject']) ? $_POST['payment_subject'] : '';
		$org_options['payment_message'] = isset($_POST['payment_message']) && !empty($_POST['payment_message']) ? esc_html($_POST['payment_message']) : '';
		$org_options['message'] = isset($_POST['success_message']) && !empty($_POST['success_message']) ? esc_html($_POST['success_message']) : '';
		$org_options['email_before_payment'] = isset($_POST['email_before_payment']) && !empty($_POST['email_before_payment']) ? $_POST['email_before_payment'] : '';
		$org_options['email_fancy_headers'] = isset($_POST['email_fancy_headers']) && !empty($_POST['email_fancy_headers']) ? $_POST['email_fancy_headers'] : '';
		$org_options['use_captcha'] = isset($_POST['use_captcha']) && !empty($_POST['use_captcha']) ? $_POST['use_captcha'] : '';
		$org_options['recaptcha_publickey'] = isset($_POST['recaptcha_publickey']) && !empty($_POST['recaptcha_publickey']) ? $_POST['recaptcha_publickey'] : '';
		$org_options['recaptcha_privatekey'] = isset($_POST['recaptcha_privatekey']) && !empty($_POST['recaptcha_privatekey']) ? $_POST['recaptcha_privatekey'] : '';
		$org_options['recaptcha_theme'] = isset($_POST['recaptcha_theme']) && !empty($_POST['recaptcha_theme']) ? $_POST['recaptcha_theme'] : '';
		$org_options['recaptcha_width'] = isset($_POST['recaptcha_width']) && !empty($_POST['recaptcha_width']) ? $_POST['recaptcha_width'] : '';
		$org_options['recaptcha_language'] = isset($_POST['recaptcha_language']) && !empty($_POST['recaptcha_language']) ? $_POST['recaptcha_language'] : '';
		$org_options['google_maps_api_key'] = isset($_POST['google_maps_api_key']) && !empty($_POST['google_maps_api_key']) ? $_POST['google_maps_api_key'] : '';
		$org_options['espresso_dashboard_widget'] = isset($_POST['espresso_dashboard_widget']) && !empty($_POST['espresso_dashboard_widget']) ? $_POST['espresso_dashboard_widget'] : '';
		$org_options['time_reg_limit'] = isset($_POST['time_reg_limit']) && !empty($_POST['time_reg_limit']) ? $_POST['time_reg_limit'] : '';
		$org_options['skip_confirmation_page'] = isset($_POST['skip_confirmation_page']) ? $_POST['skip_confirmation_page'] : 'N';
		$org_options['allow_mer_discounts'] = isset($_POST['allow_mer_discounts']) ? $_POST['allow_mer_discounts'] : 'N';
		$org_options['allow_mer_vouchers'] = isset($_POST['allow_mer_vouchers']) ? $_POST['allow_mer_vouchers'] : 'N';
		$org_options['use_attendee_pre_approval'] = isset($_POST['use_attendee_pre_approval']) && !empty($_POST['use_attendee_pre_approval']) ? $_POST['use_attendee_pre_approval'] : '';
		if (!empty($_POST['event_ssl_active']))
			$org_options['event_ssl_active'] = isset($_POST['event_ssl_active']) && !empty($_POST['event_ssl_active']) ? $_POST['event_ssl_active'] : '';
		$org_options['show_pending_payment_options'] = isset($_POST['show_pending_payment_options']) && !empty($_POST['show_pending_payment_options']) ? $_POST['show_pending_payment_options'] : '';
		$org_options['use_venue_manager'] = isset($_POST['use_venue_manager']) && !empty($_POST['use_venue_manager']) ? $_POST['use_venue_manager'] : '';
		$org_options['use_personnel_manager'] = isset($_POST['use_personnel_manager']) && !empty($_POST['use_personnel_manager']) ? $_POST['use_personnel_manager'] : '';
		$org_options['use_event_timezones'] = isset($_POST['use_event_timezones']) && !empty($_POST['use_event_timezones']) ? $_POST['use_event_timezones'] : '';
		$org_options['full_logging'] = isset($_POST['full_logging']) && !empty($_POST['full_logging']) ? $_POST['full_logging'] : '';
		$org_options['surcharge'] = isset($_POST['surcharge']) && !empty($_POST['surcharge']) ? $_POST['surcharge'] : '';
		$org_options['surcharge_type'] = isset($_POST['surcharge_type']) && !empty($_POST['surcharge_type']) ? $_POST['surcharge_type'] : '';
		$org_options['surcharge_text'] = isset($_POST['surcharge_text']) && !empty($_POST['surcharge_text']) ? $_POST['surcharge_text'] : '';
		$org_options['show_reg_footer'] = isset($_POST['show_reg_footer']) && !empty($_POST['show_reg_footer']) ? $_POST['show_reg_footer'] : '';
		$org_options['affiliate_id'] = isset($_POST['affiliate_id']) && !empty($_POST['affiliate_id']) ? $_POST['affiliate_id'] : '';
		$org_options['site_license_key'] = isset($_POST['site_license_key']) && !empty($_POST['site_license_key']) ? trim($_POST['site_license_key']) : '';
		$org_options['default_payment_status'] = isset($_POST['default_payment_status']) && !empty($_POST['default_payment_status']) ? $_POST['default_payment_status'] : '';
		$org_options['default_promocode_usage'] = isset($_POST['default_promocode_usage']) && !empty($_POST['default_promocode_usage']) ? $_POST['default_promocode_usage'] : 'N';
		$org_options['ticket_reservation_time'] = isset($_POST['ticket_reservation_time']) && !empty($_POST['ticket_reservation_time']) ? $_POST['ticket_reservation_time'] : '30';
		$ueip_optin = isset($_POST['ueip_optin']) && !empty($_POST['ueip_optin']) ? $_POST['ueip_optin'] : 'yes';

		$org_options['default_logo_url'] = isset($_REQUEST['upload_image']) && !empty($_REQUEST['upload_image']) ? $_REQUEST['upload_image'] : '';

		$currency_format = getCountryFullData($org_options['organization_country']);
		switch ($currency_format['iso_code_3']) {
			case 'USA': $org_options['currency_symbol'] = '$'; // US Dollar
				break;
			case 'CHE': $org_options['currency_symbol'] = 'Fr.'; // Swiss Franc
				break;
			case 'AUS': $org_options['currency_symbol'] = 'A$'; // Australian Dollar
				break;
			case 'GBR': $org_options['currency_symbol'] = '&pound;'; // British Pound
				break;
			case 'NOR': $org_options['currency_symbol'] = 'kr'; // Norwegian Krone
				break;
			case 'BRA': $org_options['currency_symbol'] = 'R$'; // Brazillian Real
				break;
			case 'CAN': $org_options['currency_symbol'] = 'C$'; // Canadian Dollar
				break;
			case 'JPN': $org_options['currency_symbol'] = '&yen;'; // Japanese Yen
				break;
			case 'SWE': $org_options['currency_symbol'] = 'kr'; // Swedish Krona
				break;
			case 'DNK': $org_options['currency_symbol'] = 'kr'; // Danish Krone
				break;
			case 'ZAF': $org_options['currency_symbol'] = 'R'; // South African Rand
				break;
			case 'IND': $org_options['currency_symbol'] = 'Rs'; // Indian Rupee
				break;
			case 'TUR' : $org_options['currency_symbol'] = 'TL'; // Turkish Lira
				break;
			case 'NZL' : $org_options['currency_symbol'] = 'NZ$'; // New Zealand Dollar
				break;
			case 'HKG' : $org_options['currency_symbol'] = 'HK$'; // Hong Kong Dollar
				break;
			case 'SGP' : $org_options['currency_symbol'] = 'S$'; // Singapore Dollar
				break;
			case 'POL' : $org_options['currency_symbol'] = 'zl'; // Polish Zloty (hex code: z&#x0142;)
				break;
			case 'HUN' : $org_options['currency_symbol'] = 'Ft'; // Hungarian Forint
				break;
			case 'CZE' : $org_options['currency_symbol'] = 'Kc'; // Czech Koruna (hex code: K&#x10D;)
				break;
			case 'ISR' : $org_options['currency_symbol'] = 'ILS'; // Israeli Shekel (hex code: &#8362;)
				break;
			case 'MEX' : $org_options['currency_symbol'] = 'Mex$'; // Mexican Peso
				break;
			case 'MYS' : $org_options['currency_symbol'] = 'RM'; // Malaysian Ringgit
				break;
			case 'PHL' : $org_options['currency_symbol'] = 'PhP'; // Phillipine Peso (hex code: &#x20b1;)
				break;
			case 'TWN' : $org_options['currency_symbol'] = 'NT$'; // New Taiwan Dollar
				break;
			case 'THA' : $org_options['currency_symbol'] = 'THB'; // Thai Baht (hex code: &#xe3f;)
				break;
			case 'VEN' : $org_options['currency_symbol'] = 'BsF'; //venezuelan bolivar, although technically its symbol should be VEF
				break;
			case 'LTU' : $org_options['currency_symbol'] = 'LT'; // Lithuanian Litas (LTL)
				break;
			case 'ARE' : $org_options['currency_symbol'] = 'AED';
				break;
			case 'AUT' : case 'BEL' : case 'CYP' : case 'EST' : case 'FIN' : case 'FRA' : case 'DEU' : case 'GRC' : case 'IRL' : case 'ITA' : case 'LUX' : case 'LVA' : case 'MLT' : case 'NLD' : case 'PRT' : case 'SVK' : case 'SVN' : case 'ESP' : case 'AND' : case 'MCO' : case 'SMR' : case 'VAT' | 'MYT' : case 'MNE' : case 'XKV' : case 'SPM' : $org_options['currency_symbol'] = 'EUR'; // use the Euro for all eurozone countries
				break;
			default: $org_options['currency_symbol'] = '$';
				break;
		}
		/* if (getCountryZoneId($org_options['organization_country']) == '2') {
		  $org_options['currency_symbol'] = 'Euro: '; //Creates the symbol for the Euro
		  } */
		update_option('events_organization_settings', $org_options);
		update_option('ee_ueip_optin', $ueip_optin);
		update_option( 'ee_ueip_has_notified', TRUE );
		remove_action( 'admin_notices', 'espresso_data_collection_optin_notice', 10 );
		echo '<div id="message" class="updated fade"><p><strong>' . __('Organization details saved.', 'event_espresso') . '</strong></p></div>';
	}

	$org_options = get_option('events_organization_settings');
	$ueip_optin = get_option('ee_ueip_optin');
	$plugin_basename = EVENT_ESPRESSO_WPPLUGINPATH;

	$verify_fail = get_option( 'pue_verification_error_' . $plugin_basename );
	$site_license_key_verified = $verify_fail || !empty( $verify_fail ) || ( empty( $org_options['site_license_key'] ) && empty( $verify_fail ) ) ? '<span class="pue-sl-not-verified"> </span>' : '<span class="pue-sl-verified"> </span>';/**/
	$values = array(
			array('id' => 'Y', 'text' => __('Yes', 'event_espresso')),
			array('id' => 'N', 'text' => __('No', 'event_espresso')));
	?>
	<div class="wrap columns-2">
		<div id="icon-options-event" class="icon32"> </div>
		<h2>
			<?php _e('General Settings', 'event_espresso'); ?>
		</h2>
		<?php ob_start(); ?>
		<div class="meta-box-sortables ui-sortable">
			<form class="espresso_form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<ul id="event_espresso-sortables">
					<li>
						<div class="metabox-holder">
							<div class="postbox">
								<div title="Click to toggle" class="handlediv"><br />
								</div>
								<h3 class="hndle">
									<?php _e('Organization Settings', 'event_espresso'); ?>
								</h3>
								<div class="inside">
									<div class="padding">
									<h4>
											<?php _e('Company Logo', 'event_espresso'); ?>
										</h4>
											<ul>
												<li><label for="upload_image">
															<?php _e('Add a Default Logo', 'event_espresso'); ?>
															<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=espresso_default_logo_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></label>
															<div id="default-logo-image">
															<?php $org_options['default_logo_url'] = isset( $org_options['default_logo_url'] ) ? $org_options['default_logo_url'] : ''; ?>
															<input id="upload_image" type="hidden" size="36" name="upload_image" value="<?php echo $org_options['default_logo_url'] ?>" />
															<input id="upload_image_button" type="button" value="<?php _e( 'Upload Image', 'event_espresso' ); ?>" />
															<?php if ( $org_options['default_logo_url'] != '') { ?>
																<p class="default-logo-thumb"><img src="<?php echo $org_options['default_logo_url'] ?>" alt="" /><br />
																<a id="remove-image" href="#" title="Remove this image" onclick="return false;"><?php _e('Remove Image', 'event_espresso'); ?></a></p>
															<?php } ?>
														</div>
														<div id="espresso_default_logo_info" class="pop-help" style="display:none">
															<h2>
																<?php _e('Default Logo', 'event_espresso'); ?>
															</h2>
															<p><?php echo __('The default logo will be used in your custom invoice, ticketing, certificates, and payment templates.', 'event_espresso'); ?></p>
														</div>
														</li>
														<li><h4><?php _e('Contact Information', 'event_espresso'); ?></h4></li>
											<li>
												<label for="org_name">
													<?php _e('Organization Name:', 'event_espresso'); ?>
												</label>
												<input type="text" name="org_name" size="45" value="<?php echo stripslashes_deep($org_options['organization']); ?>" />
											</li>
											<li>
												<label for="org_street1">
													<?php _e('Organization Street 1:', 'event_espresso'); ?>
												</label>
												<input type="text" name="org_street1" size="45" value="<?php echo stripslashes_deep($org_options['organization_street1']); ?>" />
											</li>
											<li>
												<label for="org_street2">
													<?php _e('Organization Street 2:', 'event_espresso'); ?>
												</label>
												<input type="text" name="org_street2" size="45" value="<?php echo stripslashes_deep($org_options['organization_street2']); ?>" />
											</li>
											<li>
												<label for="org_city">
													<?php _e('Organization City:', 'event_espresso'); ?>
												</label>
												<input type="text" name="org_city" size="45" value="<?php echo stripslashes_deep($org_options['organization_city']); ?>" />
											</li>
											<li>
												<label for="org_state">
													<?php _e('Organization State:', 'event_espresso'); ?>
												</label>
												<input type="text" name="org_state" size="45" value="<?php echo stripslashes_deep($org_options['organization_state']); ?>" />
											</li>
											<li>
												<label for="org_zip">
													<?php _e('Organization Zip/Postal Code:', 'event_espresso'); ?>
												</label>
												<input type="text" name="org_zip" size="10" value="<?php echo stripslashes_deep($org_options['organization_zip']); ?>" />
											</li>
											<li>
												<label for="org_country">
													<?php _e('Organization Country:', 'event_espresso'); ?>
												</label>
												<?php printCountriesSelector("org_country", $org_options['organization_country']); ?> (<?php echo $org_options['currency_symbol']; ?>)</li>
											<li>
												<label for="email">
													<?php _e('Primary contact email:', 'event_espresso'); ?>
												</label>
												<input type="text" name="email" size="45" value="<?php echo $org_options['contact_email']; ?>" />
											</li>
											<li>
												<h4>
													<?php _e('Time and Date Settings', 'event_espresso'); ?>
												</h4>
											</li >
											<li class="time-date">
												<p> <span class="run-in">
														<?php _e('Current Time: ', 'event_espresso'); ?>
													</span><span class="current-date"> <?php echo date(get_option('date_format') . ' ' . get_option('time_format')); ?> </span><a class="change-date-time" href="options-general.php" target="_blank">
														<?php _e('Change timezone and date format settings?', 'event_espresso'); ?>
													</a> </p>
												<p> <span class="important">
														<?php _e('Note:', 'event_espresso'); ?>
													</span>
													<?php _e('You must set the time zone for your city, or the city closest to you. UTC time will not work.', 'event_espresso'); ?>
													<a href="http://ee-updates.s3.amazonaws.com/images/time-zone-settings-example.jpg" class="thickbox">View an example?</a> </p>
											</li>

										</ul>

										<p>
											<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_1" />
										</p>
									</div>
								</div>
							</div>
						</div>
					</li>
					<li>
						<div class="metabox-holder">
							<div class="postbox">
								<div title="Click to toggle" class="handlediv"><br />
								</div>
								<h3 class="hndle">
									<?php _e('Page Settings', 'event_espresso'); ?>
								</h3>
								<div class="inside">
									<div class="padding"> <a name="page_settings" id="page_settings"></a>
										<?php
										if (empty($org_options['event_page_id'])
														|| empty($org_options['return_url'])
														|| empty($org_options['notify_url'])
														|| empty($org_options['cancel_return'])) {
											espresso_create_default_pages();
										}



										//Check to see if we are using the deprecated SSL option. If we are, recommend updating to WordPress HTTPS (SSL).
										if (!empty($org_options['event_ssl_active'])
														&& $espresso_premium == true
														&& $org_options['event_ssl_active'] == 'Y') {
											echo '<div id="ssl-reg" style="background-color: #ffffe0; border: #e6db55 1px solid; padding:4px;">';
											echo '<p><strong>' . __('Attention!', 'event_espresso') . '</strong><br />' . __('The Secure Payment System has been removed.', 'event_espresso') . '</p>';
											echo '<p>' . __('If your site uses SSL to handle secure transactions. Please install the <a href="http://ee-updates.s3.amazonaws.com/espresso-https.1.0.zip" title="Download Now">Event Espresso SSL/HTTPS</a> plugin now.', 'event_espresso') . ' ' . __('<a href="http://eventespresso.com/forums/2011/09/use-wordpress-https-for-ssl-encryption-on-your-event-espresso-site/" target="_blank">More information here</a>.', 'event_espresso') . '</p>';
											$ssl_values = array(
													array('id' => 'N', 'text' => __('Yes', 'event_espresso')), //This turns the message off by changing the option to 'N'
													array('id' => 'Y', 'text' => __('No', 'event_espresso'))//This leaves the message on incase they are not ready to proceed
											);
											?>
											<label for="event_ssl_active">
												<?php _e('Turn off this message?', 'event_espresso'); ?>
											</label>
											<br />
											<?php
											echo select_input('event_ssl_active', $ssl_values, $org_options['event_ssl_active']);
											echo '</div>';
										}
										?>
										<p>
											<?php _e('The following shortcodes and page settings are required for Event Espresso to function properly. These shortcodes should not be replaced with any other shortcodes. Please view <a href="admin.php?page=support#shortcodes">this page</a> for a list of optional shortcodes.', 'event_espresso'); ?>
										</p>
										<p>
											<label for="event_page_id">
												<?php _e('Main registration page:', 'event_espresso'); ?>
											</label>
											<select name="event_page_id">
												<option value="0">
													<?php _e('Main page', 'event_espresso'); ?>
												</option>
												<?php parent_dropdown($default = $org_options['event_page_id']); ?>
											</select>
											<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=registration_page_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a><br />
											<span class="messages"><?php echo sprintf(__("This page should contain the %s shortcode. <br />This page can be hidden from navigation if desired, <br />but should always contain the %s shortcode.", 'event_espresso'), '<span class="highlight">[ESPRESSO_EVENTS]</span>', '[ESPRESSO_EVENTS]'); ?>)</span></p>
										<?php ###### Popup help box #######    ?>
										<div id="registration_page_info" class="pop-help" style="display:none">
											<h2>
												<?php _e('Main Events Page', 'event_espresso'); ?>
											</h2>
											<p><?php echo sprintf(__('This is the page that displays your events and doubles as your registration page. It is very important that this page always contains the %s shortcode.', 'event_espresso'), '<strong>[ESPRESSO_EVENTS]</strong>'); ?></p>
											<p><?php echo sprintf(__("This page should ALWAYS contain the %s shortcode.", 'event_espresso'), '<strong>[ESPRESSO_EVENTS]</strong>'); ?></p>
										</div>
										<?php ###### close popup help box ######    ?>
										<p>
											<label for="return_url">
												<?php _e('Auto Return URL (Thank You and Return Payment page):', 'event_espresso'); ?>
											</label>
											<select name="return_url">
												<option value="0">
													<?php _e('Main page', 'event_espresso'); ?>
												</option>
												<?php parent_dropdown($default = $org_options['return_url']); ?>
											</select>
											<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=return_url_info" target="_blank"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a><br />
											<span class="messages">(<?php echo sprintf(__("This page should contain the %s shortcode.<br />This page should hidden from your navigation,<br />but still viewable to the public (not password protected.)", 'event_espresso'), '<span class="highlight">[ESPRESSO_PAYMENTS]</span>'); ?> </span></p>
										<?php ##### Popup help box #####    ?>
										<div id="return_url_info" class="pop-help" style="display:none">
											<h2>
												<?php _e('Auto Return URL', 'event_espresso'); ?>
											</h2>
											<p>
												<?php _e('The URL to which the payer\'s browser is redirected after completing the payment; for example, a URL on your site that displays a "Thank you for your payment" page.', 'event_espresso'); ?>
											</p>
											<p><?php echo sprintf(__("This page should contain the %s shortcode.", 'event_espresso'), '<strong>[ESPRESSO_PAYMENTS]</strong>'); ?></p>
											<p><em class="important"><b>
														<?php _e('ATTENTION:', 'event_espresso'); ?>
													</b><br />
													<?php _e('This page should be hidden from from your navigation menu. Exclude pages by using the "Exclude Pages" plugin from http://wordpress.org/extend/plugins/exclude-pages/ or using the "exclude" parameter in your "wp_list_pages" template tag. Please refer to http://codex.wordpress.org/Template_Tags/wp_list_pages for more information about excluding pages.', 'event_espresso'); ?>
												</em> </p>
										</div>
										<?php ##### close popup help #####    ?>
										<p>
											<label for="cancel_return">
												<?php _e('Cancel Return URL (used for cancelled payments):', 'event_espresso'); ?>
											</label>
											<select name="cancel_return">
												<option value="0">
													<?php _e('Main page', 'event_espresso'); ?>
												</option>
												<?php parent_dropdown($default = $org_options['cancel_return']); ?>
											</select>
											<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=cancel_return_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a><br />
											<span class="messages">(
												<?php echo sprintf(__("This should be a page on your website that contains a cancelled message %s and the %s shortcode. This page should hidden %s from your navigation, but still viewable to the public (not password protected.)", 'event_espresso'), '<br />', '<span class="highlight">[ESPRESSO_CANCELLED]</span>', '<br />'); ?>
												)</span></p>
										<?php ##### popup help box #####    ?>
										<div id="cancel_return_info" class="pop-help" style="display:none">
											<h2>
												<?php _e('Cancel Return URL', 'event_espresso'); ?>
											</h2>
											<p>
												<?php _e('A URL to which the payer\'s browser is redirected if payment is cancelled; for example, a URL on your website that displays a "Payment Canceled" page.', 'event_espresso'); ?>
											</p>
											<p>
												<?php echo sprintf(__("This should be a page on your website that contains a cancelled message and the %s shortcode.", 'event_espresso'), '<strong>[ESPRESSO_CANCELLED]</strong>'); ?>
											</p>
											<p><em class="important"><b>
														<?php _e('ATTENTION:', 'event_espresso'); ?>
													</b><br />
													<?php _e('This page should be hidden from from your navigation menu. Exclude pages by using the "Exclude Pages" plugin from http://wordpress.org/extend/plugins/exclude-pages/ or using the "exclude" parameter in your "wp_list_pages" template tag. Please refer to http://codex.wordpress.org/Template_Tags/wp_list_pages for more information about excluding pages.', 'event_espresso'); ?>
												</em></p>
										</div>
										<?php ##### close popup help box #####    ?>
										<p>
											<label for="notify_url">
												<?php _e('Notify URL (used to process payments):', 'event_espresso'); ?>
											</label>
											<select name="notify_url">
												<option value="0">
													<?php _e('Main page', 'event_espresso'); ?>
												</option>
												<?php parent_dropdown($default = $org_options['notify_url']); ?>
											</select>
											<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=notify_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a><br />
											<span class="messages">(<?php echo sprintf(__("This page should contain the %s shortcode.<br />This page should hidden from your navigation, <br />but still viewable to the public (not password protected.)", 'event_espresso'), '<span class="highlight">[ESPRESSO_TXN_PAGE]</span>'); ?></span></p>
										<?php ##### popup help box #####    ?>
										<div id="notify_url_info" class="pop-help" style="display:none">
											<h2>
												<?php _e('Notify URL', 'event_espresso'); ?>
											</h2>
											<p>
												<?php _e('The URL to which PayPal posts information about the transaction, in the form of Instant Payment Notification messages.', 'event_espresso'); ?>
											</p>
											<p> <?php echo sprintf(__('This page should contain the %s shortcode.', 'event_espresso'), '<strong>[ESPRESSO_TXN_PAGE]</strong>'); ?> </p>
											<p><em class="important"><b>
														<?php _e('ATTENTION:', 'event_espresso'); ?>
													</b><br />
													<?php _e('This page should be hidden from from your navigation menu. Exclude pages by using the "Exclude Pages" plugin from http://wordpress.org/extend/plugins/exclude-pages/ or using the "exclude" parameter in your "wp_list_pages" template tag. Please refer to http://codex.wordpress.org/Template_Tags/wp_list_pages for more information about excluding pages.', 'event_espresso'); ?>
												</em> </p>
										</div>
										<p>
											<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_2" />
										</p>
									</div>
								</div>
							</div>
						</div>
					</li>
					<li>
						<div class="metabox-holder">
							<div class="postbox">
								<div title="Click to toggle" class="handlediv"><br />
								</div>
								<h3 class="hndle">
									<?php _e('Email Settings', 'event_espresso'); ?>
								</h3>
								<div class="inside">
									<div class="padding"><a name="email-settings" id="email-settings"></a>
										<?php
										$values = array(
												array('id' => 'Y', 'text' => __('Yes', 'event_espresso')),
												array('id' => 'N', 'text' => __('No', 'event_espresso')));
										?>
										<p>
											<?php _e('Send payment confirmation emails?', 'event_espresso');
											echo select_input('default_mail', $values, $org_options['default_mail']);
											?>
										</p>
										<p>
											<?php _e('Send registration confirmation emails before payment is received?', 'event_espresso');
											echo select_input('email_before_payment', $values, $org_options['email_before_payment']);
											?>
										</p>
										<p>
	<?php _e('Use fancy email headers?', 'event_espresso');
	echo select_input('email_fancy_headers', $values, $org_options['email_fancy_headers']);
	?>
											<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=fancyemailheaders"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
										</p>
												<?php ###### Popup help box #######    ?>
										<div id="fancyemailheaders" class="pop-help" style="display:none">
											<h2>
	<?php _e('Fancy Email Headers', 'event_espresso'); ?>
											</h2>
											<p><?php echo sprintf(__("This option enables the use of the email header format %s From: name %s %s Reply-to: name %s %s.", 'event_espresso'), '<br />', '&lt;email@address.com&gt;', '<br />', '&lt;email@address.com&gt;', '<br />'); ?></p>
											<p><?php _e("You should only use this if you know it will not cause email delivery problems. Some servers will not send emails that use this format.", 'event_espresso'); ?></p>
										</div>
										<h4>
												<?php _e('Payment Confirmation Email:', 'event_espresso'); ?>
										</h4>
										<p>
											<label for="payment_subject">
	<?php _e('Email Subject:', 'event_espresso'); ?>
											</label>
											<input id="payment_subject" name="payment_subject" size="50" type="text" value="<?php echo stripslashes_deep($org_options['payment_subject']); ?>" />
										</p>

										<div id="payment-conf-email" class="postbox">
											<?php
											if (function_exists('wp_editor')) {
												$args = array("textarea_rows" => 5, "textarea_name" => "payment_message", "editor_class" => "my_editor_custom");
												wp_editor(espresso_admin_format_content($org_options['payment_message']), "payment_message", $args);
											} else {
												echo '<textarea class="theEditor std-textarea" id="payment_message" name="payment_message">' . espresso_admin_format_content($org_options['payment_message']) . '</textarea>';
											}
											?>
	<?php /* ?><textarea class="theEditor std-textarea" id="payment_message" name="payment_message"><?php echo espresso_admin_format_content($org_options['payment_message']); ?></textarea><?php */ ?>
											<table id="payment-confirmation-form" cellspacing="0">
												<tbody>
													<tr>
														<td class="aer-word-count"></td>
														<td class="autosave-info"><span><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_info">
	<?php _e('View Custom Email Tags', 'event_espresso'); ?>
																</a></span></td>
													</tr>
												</tbody>
											</table>
										</div>
										<h4>
											<?php _e('Default Registration Confirmation Email:', 'event_espresso'); ?>
										</h4>

										<div id="reg-conf-email" class="postbox">
											<?php
											if (function_exists('wp_editor')) {
												$args = array("textarea_rows" => 5, "textarea_name" => "success_message", "editor_class" => "my_editor_custom");
												wp_editor(espresso_admin_format_content($org_options['message']), "success_message", $args);
											} else {
												echo '<textarea class="theEditor std-textarea" id="success_message" name="success_message">' . espresso_admin_format_content($org_options['message']) . '</textarea>';
											}
											?>
	<?php /* ?><textarea class="theEditor std-textarea"  id="reg-conf-email-mce" name="success_message"><?php echo espresso_admin_format_content($org_options['message']); ?></textarea><?php */ ?>
											<table id="email-confirmation-form" cellspacing="0">
												<tbody>
													<tr>
														<td class="aer-word-count"></td>
														<td class="autosave-info"><span><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_info">
	<?php _e('View Custom Email Tags', 'event_espresso'); ?>
																</a> | <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_example">
	<?php _e('Example', 'event_espresso'); ?>
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
							</div>
						</div>
						<div style="clear:both;"></div>
					</li>
					<li>
						<h2>
					<?php _e('Advanced Features', 'event_espresso'); ?>
						</h2>
						<hr />
					</li>
					<?php
					if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/recaptcha_form.php') || file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/optional_event_settings.php')) {

						if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/optional_event_settings.php')) {
							echo '<li>';
							require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/optional_event_settings.php');
							echo '</li>';
						}
						if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/recaptcha_form.php')) {
							echo '<li>';
							require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/recaptcha_form.php');
							echo '</li>';
						}
					} else {
						?>
						<li>
							<div class="metabox-holder">
								<div class="postbox">
									<div title="Click to toggle" class="handlediv"><br />
									</div>
									<h3 class="hndle">
		<?php _e('Optional Event Settings', 'event_espresso'); ?>
									</h3>
									<div class="inside">
										<div class="padding">
											<p><?php echo __('Please purchase a', 'event_espresso') ?> <a href="http://eventespresso.com/pricing/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=purchase+a+support+license<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=organization_config_tab" target="_blank"><?php echo __('support license', 'event_espresso') ?></a> <?php echo __('to gain access to these features.', 'event_espresso') ?></p>
											<p>
													<?php _e('Additional features include:', 'event_espresso'); ?>
											</p>
											<ol>
												<li>
													<?php _e('Upcoming events widget in the admin dashboard', 'event_espresso'); ?>
												</li>
												<li>
													<?php _e('Registration limits on time slots', 'event_espresso'); ?>
												</li>
												<li>
													<?php _e('Ability to display short descriptions in the event listings', 'event_espresso'); ?>
												</li>
												<li>
													<?php _e('Custom post types for events', 'event_espresso'); ?>
												</li>
												<li>
													<?php _e('Attendee pre-approval feature', 'event_espresso'); ?>
												</li>
												<li>
													<?php _e('Event Venue/Staff Manager', 'event_espresso'); ?>
												</li>
												<li>
		<?php _e('Graphical Reports', 'event_espresso'); ?>
												</li>
											</ol>
										</div>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="metabox-holder">
								<div class="postbox">
									<div title="Click to toggle" class="handlediv"><br />
									</div>
									<h3 class="hndle">
		<?php _e('reCAPTCHA Settings', 'event_espresso'); ?>
									</h3>
									<div class="inside">
										<div class="padding">
											<p><?php echo __('Please purchase a', 'event_espresso') ?> <a href="http://eventespresso.com/pricing/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=purchase+a+support+license<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=organization_config_tab" target="_blank"><?php echo __('support license', 'event_espresso') ?></a> <?php echo __('to gain access to this feature.', 'event_espresso') ?></p>
											<p> <?php echo sprintf(__('reCAPTCHA helps prevent automated abuse of your site (such as comment spam or bogus registrations) by using a %s to ensure that only humans perform certain actions.', 'event_espresso'), '<a href="http://recaptcha.net/captcha.html">reCAPTCHA</a>'); ?> </p>
										</div>
									</div>
								</div>
							</div>
						</li>
					<?php 
						} //End Premium file check
					?>

					<li>
						<div class="metabox-holder">
							<div class="postbox">
								<div title="Click to toggle" class="handlediv"><br />
								</div>
								<h3 class="hndle">
									<?php _e('Google Maps Settings', 'event_espresso'); ?>
								</h3>
								<div class="inside">
									<ul>
						                <li>
						                  <label for="google_maps_api_key">
						                    <?php _e('Maps API Key:','event_espresso'); ?>
						                  </label>
						                  <input type="text" name="google_maps_api_key" size="45" value="<?php if(isset($org_options['google_maps_api_key'])) echo $org_options['google_maps_api_key'];?>" />
						                </li>
						            </ul>
						            <p class="description">
				                        <?php
				                            printf(
				                                __('An API key is now required to use the Google Maps API: %1$sclick here to get an API key%2$s', 'event_espresso'),
				                                '<a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,static_maps_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank">',
				                                '</a>'
				                            );
				                        ?>
				                    </p>
						            <p>
										<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_7" />
									</p>
								</div>
							</div>
						</div>
					</li>

					<?php
					if ($espresso_check_for_updates == true && $espresso_premium == true) {
					?>

						<li><a name="license_key" id="license_key"></a>
							<div class="metabox-holder">
								<div class="postbox">
									<div title="Click to toggle" class="handlediv"><br />
									</div>
									<h3 class="hndle">
		<?php _e('Support License', 'event_espresso'); ?>
									</h3>
									<div class="inside">
										<div class="padding">
											<ul>
												<li>
													<label for="site_license_key">
		<?php _e('Support License Key:', 'event_espresso'); ?>
													</label>
													<input type="text" name="site_license_key" size="45" value="<?php echo isset( $org_options['site_license_key'] ) ? stripslashes_deep($org_options['site_license_key']) : ''; ?>" />
													<?php echo $site_license_key_verified; ?>
												</li>

											</ul>
											<p class="description">
												<?php _e('Adding a valid Support License Key will enable automatic update notifications and backend updates for Event Espresso Core and any installed addons.'); ?>
											</p>
											<p>
												<?php _e('If this is a development or test site, please <strong>DO NOT</strong> enter your Support License Key. Save it for the live production site, otherwise you will unnecessarily run into issues with needing to have your Key reset.', 'event_espresso'); ?>
											</p>
											<p>
												<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_5" />
											</p>
										</div>
									</div>
								</div>
							</div>
						</li>
						<li><a name="ueip_optin" id="ueip_optin"></a>
							<div class="metabox-holder">
								<div class="postbox">
									<div title="Click to toggle" class="handlediv"><br />
									</div>
									<h3 class="hndle">
		<?php _e('UXIP Settings', 'event_espresso'); ?>
									</h3>
									<div class="inside">
										<div class="padding">
											<p>
												<?php echo espresso_data_collection_optin_text(); ?>
											</p>
											<ul>
												<li>
													<label for="ueip_optin">
		<?php _e('Yes! I\'m In:', 'event_espresso'); ?>
													</label>
													<?php
													$values=array(
													array('id'=>'yes','text'=> __('Yes','event_espresso')),
													array('id'=>'no','text'=> __('No','event_espresso'))
												);
													echo select_input('ueip_optin', $values, !empty($ueip_optin) ? $ueip_optin : 'yes');
													?>
												</li>

											</ul>
											<p>
												<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_6" />
											</p>
										</div>
									</div>
								</div>
							</div>
						</li>
	<?php } ?>
				</ul>
				<input type="hidden" name="update_org" value="update" />
			</form>
		</div>
		<?php
		$post_content = ob_get_clean();
		espresso_choose_layout($post_content, event_espresso_display_right_column());
		?>
	</div>
	<script type="text/javascript" charset="utf-8">
		//<![CDATA[
		jQuery(document).ready(function() {
			postboxes.add_postbox_toggles('event_espresso');

			//Logo uploader
			var header_clicked = false;
			jQuery('#upload_image_button').click(function() {
				formfield = jQuery('#upload_image').attr('name');
				tb_show('', 'media-upload.php?type=image&amp;TB_iframe=1');
				jQuery('p.default-logo-thumb').addClass('old');
				header_clicked = true;
				return false;
			});
			window.original_send_to_editor = window.send_to_editor;

			window.send_to_editor = function(html) {
				if(header_clicked) {
					//Remove old image
					jQuery("#upload_image").val('');
					jQuery("p.default-logo-thumb").remove();
					jQuery("p#image-display").remove();
					jQuery('#remove-image').remove();

					//Add new image
					imgurl = jQuery('img',html).attr('src');
					jQuery('#' + formfield).val(imgurl);
					jQuery('#default-logo-image').append("<p id='image-display'><img src='"+imgurl+"' alt='' /></p>");
					header_clicked = false;
					tb_remove();
				} else {
					window.original_send_to_editor(html);
				}
			}

			// process the remove link in the metabox
			jQuery('#remove-image').click(function(){
				var answer = confirm("<?php _e('Do you really want to delete this image? Please remember to save your settings to complete the removal.', 'event_espresso'); ?>");
				if (answer){
					jQuery("#upload_image").val('');
					jQuery("p.default-logo-thumb").remove();
					jQuery("p#image-display").remove();
					jQuery('#remove-image').remove();
				}
				return false;
			});

		});
		//]]>
	</script>
	<?php
	echo event_espresso_custom_email_info();
	if (!function_exists('wp_editor')) {
		espresso_tiny_mce();
	}
}
