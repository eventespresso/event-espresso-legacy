<?php

function event_espresso_wepay_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	require_once(dirname(__FILE__) . "/Wepay.php");
	$wepay_settings = get_option('event_espresso_wepay_settings');
	$need_to_reauthorize = false;
	if(!array_key_exists('access_token',$wepay_settings) || empty($wepay_settings['access_token'])){
		$need_to_reauthorize = true;
	}
	if (isset($_POST['update_wepay'])) {
		if ($wepay_settings['wepay_client_id'] != $_POST['wepay_client_id']
						|| $wepay_settings['wepay_client_secret'] != $_POST['wepay_client_secret']) {
			$wepay_settings['wepay_client_id'] = $_POST['wepay_client_id'];
			$wepay_settings['wepay_client_secret'] = $_POST['wepay_client_secret'];
			$wepay_settings['access_token']='';
			$need_to_reauthorize=true;
			
		}
		$wepay_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$wepay_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$wepay_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$wepay_settings['button_url'] = $_POST['button_url'];
		$wepay_settings['account_id'] = $_POST['account_id'];
		update_option('event_espresso_wepay_settings', $wepay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('WePay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	if (isset($_GET['code'])) {
		if ($wepay_settings['use_sandbox']) {
			Espresso_Wepay::useStaging($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
		} else {
			Espresso_Wepay::useProduction($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
		}
		$info = Espresso_Wepay::getToken($_GET['code'], get_transient('espresso_wepay_redirect_uri'));
		if ($info) {
			// Normally you'd integrate this into your existing auth system
			$wepay_settings['access_token'] = $info->access_token;
			$wepay_settings['user_id'] = $info->user_id;
			try {
				$wepay = new Espresso_Wepay($info->access_token);
				$accounts = $wepay->request('account/find');
				foreach ($accounts as $account) {
					$available_accounts[] = array('id' => $account->account_id, 'text' => $account->name);
				}
				$wepay_settings['available_accounts'] = $available_accounts;
				$wepay_settings['account_id'] = $available_accounts[0]['id'];
				$need_to_reauthorize=false;
			} catch (WepayException $e) {
				// Something went wrong - normally you would log
				// this and give your user a more informative message
				echo $e->getMessage();
			}
			update_option('event_espresso_wepay_settings', $wepay_settings);
			echo '<div id="message" class="updated fade"><p><strong>' . __('WePay Access Token saved.', 'event_espresso') . '</strong></p></div>';
		} else {
			// Unable to obtain access token
			echo 'Unable to obtain access token from WePay.';
		}
	}
	if (empty($wepay_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/wepay/wepay-logo.png")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_URL . "/wepay/wepay-logo.png";
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/wepay/wepay-logo.png";
		}
		$wepay_settings['wepay_client_id'] = '';
		$wepay_settings['wepay_client_secret'] = '';
		$wepay_settings['use_sandbox'] = false;
		$wepay_settings['bypass_payment_page'] = 'N';
		$wepay_settings['button_url'] = $button_url;
		$wepay_settings['available_accounts'] = array();
		$wepay_settings['account_id'] = '';
		$wepay_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_wepay_settings', $wepay_settings, '', 'no') == false) {
			update_option('event_espresso_wepay_settings', $wepay_settings);
		}
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
						event_espresso_display_wepay_settings($need_to_reauthorize);
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
function event_espresso_display_wepay_settings($need_to_reauthorize) {
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
							<label for="button_url">
	<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="button_url" size="34" value="<?php echo (($wepay_settings['button_url'] == '') ? '' : $wepay_settings['button_url'] ); ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a> </li>
						<li>
							<label for="account_id">
							<?php _e('Select the Account to Use', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=select_account"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							echo select_input('account_id', $wepay_settings['available_accounts'], $wepay_settings['account_id']);
							?>
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
						<?php if (espresso_check_ssl() == TRUE || ( isset($wepay_settings['force_ssl_return']) && $wepay_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
	<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $wepay_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<?php _e('Current Button Image', 'event_espresso'); ?>
							<br />
	<?php echo (($wepay_settings['button_url'] == '') ? '<img src="' . $button_url . '" />' : '<img src="' . $wepay_settings['button_url'] . '" />'); ?></li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_wepay" value="update_wepay">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update WePay Settings', 'event_espresso') ?>" id="save_wepay_settings" />
		</p>
	</form>
	<?php
	if ($need_to_reauthorize) {
		if ($wepay_settings['use_sandbox']) {
			Espresso_Wepay::useStaging($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
		} else {
			Espresso_Wepay::useProduction($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
		}
		$scope = Espresso_Wepay::$all_scopes;
		$redirect_uri = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		set_transient('espresso_wepay_redirect_uri',$redirect_uri,60*60);
		$uri = Espresso_Wepay::getAuthorizationUri($scope, $redirect_uri);
		?>
			<a class="button-primary" href='<?php echo $uri?>'><?php _e('Authorize Application', 'event_espresso') ?></a>
	<?php } ?>
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
	<?php _e('At WePay.com (or stage.wepay.com for sandbox) sign up as a user and add an account to your user.'); ?>
			</li>
			<li>
	<?php _e('Register your instance of Event Espresso as an application in your WePay user profile.'); ?>
			</li>
			<li>
	<?php _e('Copy your client id and client secret from your application profile and paste them here.'); ?>
			</li>
			<li>
	<?php _e('Anytime you change your id and secret on this page and update your WePay settings, you will see a button to authorize your application.'); ?>
			</li>
			<li>
	<?php _e('Once your application is authorized, you will be able to select from your available accounts, and update your WePay settings.'); ?>
			</li>
		</ol>
	</div>
	<div id="select_account" style="display: none">
		<h2><?php _e('WePay Account','event_espresso'); ?></h2>
		<p><?php _e('The name of the account you want to use with Event Espresso.', 'event_espresso'); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_wepay_payment_settings');
