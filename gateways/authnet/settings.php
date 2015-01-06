<?php

function event_espresso_authnet_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_authnet'])) {
		$authnet_settings['authnet_login_id'] = $_POST['authnet_login_id'];
		$authnet_settings['authnet_transaction_key'] = $_POST['authnet_transaction_key'];
		$authnet_settings['authnet_md5_value'] = $_POST['authnet_md5_value'];
		$authnet_settings['image_url'] = $_POST['image_url'];
		$authnet_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$authnet_settings['test_transactions'] = empty($_POST['test_transactions']) ? false : true;
		$authnet_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$authnet_settings['use_md5'] = empty($_POST['use_md5']) ? false : true;
		$authnet_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$authnet_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_authnet_settings', $authnet_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Authorize.net settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$authnet_settings = get_option('event_espresso_authnet_settings');
	if (empty($authnet_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/authnet-logo.png")) {
			$authnet_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/authnet/authnet-logo.png";
		} else {
			$authnet_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/authnet/authnet-logo.png";
		}
		$authnet_settings['active'] = false;
		$authnet_settings['authnet_login_id'] = '';
		$authnet_settings['authnet_transaction_key'] = '';
		$authnet_settings['authnet_md5_value'] = '';
		$authnet_settings['image_url'] = '';
		$authnet_settings['use_sandbox'] = false;
		$authnet_settings['use_md5'] = false;
		$authnet_settings['test_transactions'] = false;
		$authnet_settings['force_ssl_return'] = false;
		$authnet_settings['bypass_payment_page'] = 'N';
		if (add_option('event_espresso_authnet_settings', $authnet_settings, '', 'no') == false) {
			update_option('event_espresso_authnet_settings', $authnet_settings);
		}
	}

	if(!isset($authnet_settings['authnet_md5_value'])) {
		$authnet_settings['authnet_md5_value'] = $authnet_settings['authnet_transaction_key'];
		update_option('event_espresso_authnet_settings', $authnet_settings);
	}

	if ( ! isset( $authnet_settings['button_url'] ) || ! file_exists( $authnet_settings['button_url'] )) {
		$authnet_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_authnet'])
					&& (!empty($_REQUEST['activate_authnet'])
					|| array_key_exists('authnet', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Authorize.net SIM Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_authnet'])) {
						$active_gateways['authnet'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_authnet'])) {
						unset($active_gateways['authnet']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('authnet', $active_gateways)) {
						echo '<li id="deactivate_authnet" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_authnet=true\';" class="red_alert pointer"><strong>' . __('Deactivate Authorize.net SIM Gateway?', 'event_espresso') . '</strong></li>';
						event_espresso_display_authnet_settings();
					} else {
						echo '<li id="activate_authnet" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_authnet=true\';" class="green_alert pointer"><strong>' . __('Activate Authorize.net SIM Gateway?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Authorize.net SIM Settings Form
function event_espresso_display_authnet_settings() {
	global $org_options;
	$authnet_settings = get_option('event_espresso_authnet_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="authnet_login_id">
								<?php _e('Authorize.net Login ID', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="authnet_login_id" size="35" value="<?php echo $authnet_settings['authnet_login_id']; ?>">
						</li>
						<li>
							<label for="authnet_transaction_key">
								<?php _e('Authorize.net Transaction Key', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="authnet_transaction_key" size="35" value="<?php echo $authnet_settings['authnet_transaction_key']; ?>">
						</li>
						<li>
							<label for="image_url">
								<?php _e('Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=authnet_image_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="image_url" size="35" value="<?php echo $authnet_settings['image_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
							<br />
							<?php _e('(used for your business/personal logo on the Authorize.net SIM payment page)', 'event_espresso'); ?>
						</li>
						<li>
							<label><?php _e('Relay Response URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=relay_response"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></label> 
							<span class="display-path" style="background-color: rgb(255, 251, 204); border:#999 solid 1px; padding:2px;"><?php echo get_permalink( $org_options['return_url'] ); ?></span>
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label for="use_sandbox">
								<?php _e('Account Uses Authorize.net\'s Development Server', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=authnet_sandbox"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $authnet_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
						</li> 
						<li>
							<label for="test_transactions">
								<?php _e('Submit a Test Transaction', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=authnet_sandbox"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="test_transactions" type="checkbox" value="1" <?php echo $authnet_settings['test_transactions'] ? 'checked="checked"' : '' ?> />
						</li>
						<li>
							<label for="use_md5">
								<?php _e('Use md5 check to secure payment response', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=authnet_md5"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_md5" type="checkbox" value="1" <?php echo isset($authnet_settings['use_md5']) && $authnet_settings['use_md5'] ? 'checked="checked"' : '' ?> />
						</li>
						<li>
							<label for="authnet_md5_value">
								<?php _e('Authorize.net MD5 Hash value', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="authnet_md5_value" size="35" value="<?php echo $authnet_settings['authnet_md5_value']; ?>">
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($authnet_settings['force_ssl_return']) && $authnet_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $authnet_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> />
						</li>
						<?php }?>
						<li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')),
									array('id' => 'N', 'text' => __('No', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $authnet_settings['bypass_payment_page']);
							?>
						</li>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="35" value="<?php echo $authnet_settings['button_url']; ?>" />
							<a  class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
						</li>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							<?php echo (($authnet_settings['button_url'] == '') ? '' : '<img src="' . $authnet_settings['button_url'] . '" />'); ?>
						</li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_authnet" value="update_authnet">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Authorize.net SIM Settings', 'event_espresso') ?>" id="save_authnet_settings" />
		</p>
	</form>
	<div id="relay_response" style="display:none">
		<h2><?php _e('Relay Response', 'event_espresso'); ?></h2>
		<p><?php _e('This shows the specific the URL to which the gateway should return the relay response for a transaction. This the page should be set in your Authorize.net account. Login to Authorize.net, goto Account > Response/Receipt URLs > Add URL and enter the following URL.', 'event_espresso'); ?></p>
		<p><strong><?php _e('Relay Response URL:', 'event_espresso'); ?></strong> <?php echo get_permalink( $org_options['return_url'] ) ?><br />
			<span style="color:red;"><?php _e('Note:', 'event_espresso'); ?></span> <?php _e('This URL can be changed in the "Organization Settings" page.', 'event_espresso'); ?></p>
		<p><?php _e('For complete information on configuring relay response, please refer to', 'event_espresso'); ?> <a href="https://www.authorize.net/support/CNP/helpfiles/Account/Settings/Transaction_Format_Settings/Transaction_Response_Settings/Relay_Response.htm"><?php _e('Reference &amp; User Guides', 'event_espresso'); ?></a>.</p>
	</div>
	<div id="authnet_image_url_info" style="display:none">
		<h2>
			<?php _e('Authorize.net SIM Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the image displayed as your logo in the header of the Authorize.net checkout pages.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="authnet_sandbox" style="display:none">
		<h2><?php _e('Authorize.net Test Mode', 'event_espresso'); ?></h2>
		<p><?php _e('Test Mode allows you to submit test transactions to the payment gateway. Transactions that are submitted while Test Mode is ON are NOT actually processed. The result of a transaction depends on the card number submitted, and the invoice amount. If you want a transaction to be approved, use one of the following card numbers.', 'event_espresso'); ?></p><p>370000000000002 (<?php _e('American Express', 'event_espresso'); ?>)<br />6011000000000012 (<?php _e('Discover', 'event_espresso'); ?>)<br />5424000000000015 (<?php _e('Master Card', 'event_espresso'); ?>)<br />4007000000027 (<?php _e('Visa', 'event_espresso'); ?>)</p>
	</div>
	<div id="authnet_md5" style="display:none">
		<h2><?php _e('Authorize.net MD5 Secure Response', 'event_espresso'); ?></h2>
		<p><?php _e('Authorize.net allows you to secure the reponse from their sever to Event Espresso.', 'event_espresso'); ?></p>
		
		<p><?php _e('To configure an MD5 Hash value for your account:', 'event_espresso'); ?>
		<ol>
			<li><?php _e('Log on to the Merchant Interface at ', 'event_espresso'); ?><a href="https://secure.authorize.net">https://secure.authorize.net</a></li>
			<li><?php _e('Click Settings under Account in the main menu on the left', 'event_espresso'); ?></li>
			<li><?php _e('Click MD5-Hash in the Security Settings section', 'event_espresso'); ?></li>
			<li><?php _e('Enter any random value to use for your MD5 Hash Value. Enter the value again to confirm', 'event_espresso'); ?></li>
			<li><?php _e('Click Submit', 'event_espresso'); ?></li>
		</ol>
		</p>
		
		<p><strong><?php _e('Warning:', 'event_espresso'); ?></strong><br />
<strong><?php _e('MAY CAUSE LEGITIMATE PAYMENTS TO BE MARKED "INCOMPLETE"', 'event_espresso'); ?></strong><br />
			<?php _e('This may be a problem, such as a mismatch, between the authorize.net and Event Espresso MD5 Hash value settings.', 'event_espresso'); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_authnet_payment_settings');
