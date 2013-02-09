<?php

function event_espresso_alipay_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_alipay'])) {
		$alipay_settings['alipay_partner_id'] = $_POST['alipay_partner_id'];
		$alipay_settings['alipay_security_code'] = $_POST['alipay_security_code'];
		$alipay_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_alipay_settings', $alipay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Alipay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$authnet_aim_settings = get_option('event_espresso_alipay_settings');
	if (empty($authnet_aim_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/alipay/alipay-logo.png")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_URL . "/alipay/alipay-logo.png";
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/alipay/alipay-logo.png";
		}
		$alipay_settings['alipay_partner_id'] = '';
		$alipay_settings['alipay_security_code'] = '';
		$alipay_settings['button_url'] = $button_url;
		if (add_option('event_espresso_alipay_settings', $alipay_settings, '', 'no') == false) {
			update_option('event_espresso_alipay_settings', $alipay_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_alipay'])
					&& (!empty($_REQUEST['activate_alipay'])
					|| array_key_exists('alipay', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Alipay Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<?php
				if (!empty($_REQUEST['activate_alipay'])) {
					$active_gateways['alipay'] = dirname(__FILE__);
					update_option('event_espresso_active_gateways', $active_gateways);
				}
				if (!empty($_REQUEST['deactivate_alipay'])) {
					unset($active_gateways['alipay']);
					update_option('event_espresso_active_gateways', $active_gateways);
				}
				echo '<ul>';
				if (array_key_exists('alipay', $active_gateways)) {
					echo '<li id="deactivate_alipay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_alipay=true\';" class="red_alert pointer"><strong>' . __('Deactivate Alipay Gateway?', 'event_espresso') . '</strong></li>';
					event_espresso_display_alipay_settings();
				} else {
					echo '<li id="activate_alipay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_alipay=true\';" class="green_alert pointer"><strong>' . __('Activate Alipay Gateway?', 'event_espresso') . '</strong></li>';
				}
				echo '</ul>';
				?>
			</div>
		</div>
	</div>
	<?php
}

//Authorize.net Settings Form
function event_espresso_display_alipay_settings() {
	$alipay_settings = get_option('event_espresso_alipay_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="alipay_login_id">
								<?php _e('Alipay partner ID', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="alipay_partner_id" size="35" value="<?php echo $alipay_settings['alipay_partner_id']; ?>">
						</li>
						<li>
							<label for="alipay_transaction_key">
								<?php _e('Alipay security code', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="alipay_security_code" size="35" value="<?php echo $alipay_settings['alipay_security_code']; ?>">
						</li>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL: ', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="button_url" size="34" value="<?php echo $alipay_settings['button_url']; ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a></li>

					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_alipay" value="update_alipay">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Alipay Settings', 'event_espresso') ?>" id="save_alipay_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_alipay_settings');
