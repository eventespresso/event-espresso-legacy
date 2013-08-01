<?php

function event_espresso_wepay_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	require_once(dirname(__FILE__) . "/Wepay.php");
	$wepay_settings = get_option('event_espresso_wepay_settings');
	if (isset($_POST['update_wepay'])) {
		$wepay_settings['wepay_client_id'] = $_POST['wepay_client_id'];
		$wepay_settings['wepay_client_secret'] = $_POST['wepay_client_secret'];
		$wepay_settings['access_token'] = $_POST['access_token'];
		$wepay_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$wepay_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$wepay_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$wepay_settings['button_url'] = $_POST['button_url'];
		$wepay_settings['account_id'] = $_POST['account_id'];
		update_option('event_espresso_wepay_settings', $wepay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('WePay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	if (empty($wepay_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/wepay/wepay-logo.png")) {
			$wepay_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/wepay/wepay-logo.png";
		} else {
			$wepay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/wepay/wepay-logo.png";
		}
		$wepay_settings['wepay_client_id'] = '';
		$wepay_settings['wepay_client_secret'] = '';
		$wepay_settings['access_token'] = '';
		$wepay_settings['use_sandbox'] = false;
		$wepay_settings['bypass_payment_page'] = 'N';
		$wepay_settings['account_id'] = '';
		$wepay_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_wepay_settings', $wepay_settings, '', 'no') == false) {
			update_option('event_espresso_wepay_settings', $wepay_settings);
		}
	}
	
	if ( ! isset( $wepay_settings['button_url'] ) || ! file_exists( $wepay_settings['button_url'] )) {
		$wepay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}	

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_wepay'])
					&& (!empty($_REQUEST['activate_wepay'])
					|| array_key_exists('wepay', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<a name="wepay" id="wepay"></a>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('WePay Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_wepay'])) {
						$active_gateways['wepay'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_wepay'])) {
						unset($active_gateways['wepay']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('wepay', $active_gateways)) {
						echo '<li id="deactivate_wepay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_wepay=true\';" class="red_alert pointer"><strong>' . __('Deactivate WePay IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_wepay_settings();
					} else {
						echo '<li id="activate_wepay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_wepay=true#wepay\';" class="green_alert pointer"><strong>' . __('Activate WePay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//wepay Settings Form
function event_espresso_display_wepay_settings() {
	$wepay_settings = get_option('event_espresso_wepay_settings');
	$uri = $_SERVER['REQUEST_URI'];
	$pos = strpos($uri, '&activate_wepay=true');
	if ($pos)
		$uri = substr("$uri", 0, $pos);
	$pos = strpos($uri, '&code');
	if ($pos)
		$uri = substr("$uri", 0, $pos);
	_e('Instructions:');
	?>
	&nbsp;<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=instructions"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
	<form method="post" action="<?php echo $uri; ?>#wepay">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="wepay_client_id">
								<?php _e('WePay Client ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="wepay_client_id" size="35" value="<?php echo $wepay_settings['wepay_client_id']; ?>" />
						</li>
						<li>
							<label for="wepay_client_secret">
								<?php _e('WePay Client Secret', 'event_espresso'); ?>
							</label>
							<input type="text" name="wepay_client_secret" size="35" value="<?php echo $wepay_settings['wepay_client_secret']; ?>" />
						</li>
						<li>
							<label for="account_id">
								<?php _e('Account ID', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=account_id"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label><br>
							<input type="text" name="account_id" size="34" value="<?php echo (($wepay_settings['account_id'] == '') ? '' : $wepay_settings['account_id'] ); ?>" />
						</li>
						<li>
							<label for="access_token">
								<?php _e('Access Token', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=access_token"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="access_token" size="100" value="<?php echo (($wepay_settings['access_token'] == '') ? '' : $wepay_settings['access_token'] ); ?>" />
						</li>
					</ul></td>
				<td valign="top">
					<ul>
						<li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $wepay_settings['bypass_payment_page']);
							?>
						</li>
						<li>
							<label for="use_sandbox">
								<?php _e('Turn on Debugging Using the', 'event_espresso'); ?> <a href="https://developer.wepay.com/devscr?cmd=_home||https://cms.wepay.com/us/cgi-bin/?&amp;cmd=_render-content&amp;content_ID=developer/howto_testing_sandbox||https://cms.wepay.com/us/cgi-bin/?&amp;cmd=_render-content&amp;content_ID=developer/howto_testing_sandbox_get_started" title="WePay Sandbox Login||Sandbox Tutorial||Getting Started with WePay Sandbox" target="_blank"><?php _e('WePay Sandbox', 'event_espresso'); ?></a> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=wepay_sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $wepay_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($wepay_settings['force_ssl_return']) && $wepay_settings['force_ssl_return'] == 1 )) { ?>
							<li>
								<label for="force_ssl_return">
									<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
									<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
								</label>
								<input name="force_ssl_return" type="checkbox" value="1" <?php echo $wepay_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
						<?php } ?>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo (($wepay_settings['button_url'] == '') ? '' : $wepay_settings['button_url'] ); ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
						</li>
						<li>
							<?php _e('Current Button Image', 'event_espresso'); ?>
							<br />
							<?php echo (($wepay_settings['button_url'] == '') ? '' : '<img src="' . $wepay_settings['button_url'] . '" />'); ?></li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_wepay" value="update_wepay">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update WePay Settings', 'event_espresso') ?>" id="save_wepay_settings" />
		</p>
	</form>
	<div id="wepay_sandbox_info" style="display:none">
		<h2><?php _e('WePay Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the WePay Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all WePay variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The WePay Sandbox is a testing environment that is a duplicate of the live WePay site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live WePay environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="instructions" style="display:none">
		<h2><?php _e('WePay Instructions', 'event_espresso'); ?></h2>
		<?php _e('To use WePay, follow these steps:'); ?>
		<ol>
			<li>
				<?php _e('At WePay.com (or stage.wepay.com for sandbox) sign up as a user.'); ?>
			</li>
			<li>
				<?php _e('Add Event Espresso as an API application in your account.'); ?>
			</li>
			<li>
				<?php _e('Copy your client id, client secret, account id, and access token from yourAPI application\'s API Keys page and paste them here.'); ?>
			</li>
		</ol>
	</div>
	<div id="account_id" style="display: none">
		<h2><?php _e('WePay Account', 'event_espresso'); ?></h2>
		<p><?php _e('The id of the account you want to use with Event Espresso. Found on the Application API page in your WePay account.', 'event_espresso'); ?></p>
	</div>
	<div id="access_token" style="display: none">
		<h2><?php _e('WePay Access Token', 'event_espresso'); ?></h2>
		<p><?php _e('The API access token from the account you want to use with Event Espresso. Found on the Application API page in your WePay account.', 'event_espresso'); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_wepay_payment_settings');
