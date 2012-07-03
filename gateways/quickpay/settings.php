<?php

function event_espresso_quickpay_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_quickpay'])) {
		$quickpay_settings['quickpay_merchantid'] = $_POST['quickpay_merchantid'];
		$quickpay_settings['quickpay_md5secret'] = $_POST['quickpay_md5secret'];
		$quickpay_settings['quickpay_language'] = $_POST['quickpay_language'];
		$quickpay_settings['quickpay_autocapture'] = $_POST['quickpay_autocapture'];
		$quickpay_settings['quickpay_currency'] = $_POST['quickpay_currency'];
		$quickpay_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$quickpay_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$quickpay_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_quickpay_settings', $quickpay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('quickpay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$quickpay_settings = get_option('event_espresso_quickpay_settings');
	if (empty($quickpay_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/btn_stdCheckout2.gif")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_URL . "/paypal/btn_stdCheckout2.gif";
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/paypal/btn_stdCheckout2.gif";
		}
		$quickpay_settings['button_url'] = $button_url;
		$quickpay_settings['quickpay_merchantid'] = '';
		$quickpay_settings['quickpay_md5secret'] = '';
		$quickpay_settings['quickpay_language'] = 'en';
		$quickpay_settings['quickpay_autocapture'] = '1';
		$quickpay_settings['quickpay_currency'] = 'USD';
		$quickpay_settings['use_sandbox'] = false;
		$quickpay_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_quickpay_settings', $quickpay_settings, '', 'no') == false) {
			update_option('event_espresso_quickpay_settings', $quickpay_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_quickpay'])
					&& (!empty($_REQUEST['activate_quickpay'])
					|| array_key_exists('quickpay', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Quickpay Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_quickpay'])) {
						$active_gateways['quickpay'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_quickpay'])) {
						unset($active_gateways['quickpay']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('quickpay', $active_gateways)) {
						echo '<li id="deactivate_quickpay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_quickpay=true\';" class="red_alert pointer"><strong>' . __('Deactivate quickpay IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_quickpay_settings();
					} else {
						echo '<li id="activate_quickpay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_quickpay=true\';" class="green_alert pointer"><strong>' . __('Activate quickpay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//quickpay Settings Form
function event_espresso_display_quickpay_settings() {
	$quickpay_settings = get_option('event_espresso_quickpay_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="quickpay_merchantid">
								<?php _e('quickpay I.D.', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="quickpay_merchantid" size="35" value="<?php echo $quickpay_settings['quickpay_merchantid']; ?>">
						</li>
						<li>
							<label for="quickpay_md5secret">
								<?php _e('quickpay md5 secret:', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="quickpay_md5secret" size="35" value="<?php echo $quickpay_settings['quickpay_md5secret']; ?>">
						</li>
						<li>
							<label for="quickpay_language">Payment Window Language</label>
							<select name='quickpay_language'>
								<option value="<?php echo $quickpay_settings['quickpay_language']; ?>" selected='selected'" ><?php echo $quickpay_settings['quickpay_language']; ?>
							<option value='da'>da - Danish</option>
							<option value='de'>de - German</option>
							<option value='en'>en - English</option>
							<option value='fr'>fr - French</option>
							<option value='it'>it - Italian</option>
							<option value='no'>no - Norwegian</option>
							<option value='nl'>nl - Dutch</option>
							<option value='pl'>pl - Polish</option>
							<option value='se'>se - Swedish</option>
						</select>
						<p><small>Choose which language the transaction window will use.</small></p>
					</li>
					<li>
						<label for="quickpay_autocapture">Automatic capture</label>
						<?php if ($quickpay_settings['quickpay_autocapture'] == '0') { ?>
							<input name="quickpay_autocapture" value="0" checked="checked" type="RADIO">Off<br>
						<?php } else { ?>
							<input name="quickpay_autocapture" value="0" type="RADIO">Off<br>
						<?php
						}
						if ($quickpay_settings['quickpay_autocapture'] == '1') {
							?>
							<input name="quickpay_autocapture" value="1" checked="checked" type="RADIO">On<br>
						<?php } else { ?>
							<input name="quickpay_autocapture" value="1" type="RADIO">On<br>
	<?php } ?>
						<p><small>Automatic Capture means you will automatically deduct the amount from the customer.</small></p>
					</li>
					<li>
						<label for="quickpay_currency">Currency</label>
						<input name="quickpay_currency" value="EUR" <?php if ($quickpay_settings['quickpay_currency'] == 'EUR') { ?>checked="checked"<?php } ?> type="RADIO">EUR<br>
						<input name="quickpay_currency" value="DKK" <?php if ($quickpay_settings['quickpay_currency'] == 'DKK') { ?>checked="checked"<?php } ?> type="RADIO">DKK<br>
						<input name="quickpay_currency" value="USD" <?php if ($quickpay_settings['quickpay_currency'] == 'USD') { ?>checked="checked"<?php } ?> type="RADIO">USD<br>
					</li>
				</ul></td>
			<td>
		<li>
			<label for="use_sandbox">
	<?php _e('Use the debugging feature and the sandbox', 'event_espresso'); ?>
			</label>
			<input name="use_sandbox" type="checkbox" value="1" <?php echo $quickpay_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
		</li>
		<li>
			<label for="force_ssl_return">
	<?php _e('Do you want to force the return url to be https? ', 'event_espresso'); ?>
				<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
			</label>
			<input name="force_ssl_return" type="checkbox" value="1" <?php echo $quickpay_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
		<li>
			<label for="button_url">
	<?php _e('Button Image URL: ', 'event_espresso'); ?>
			</label>
			<br />
			<input type="text" name="button_url" size="34" value="<?php echo $quickpay_settings['button_url']; ?>" />
			<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a> </li>
		<li>
			<?php _e('Current Button Image:', 'event_espresso'); ?>
			<br />
	<?php echo '<img src="' . $quickpay_settings['button_url'] . '" />'; ?></li>
		</td>
		</tr>
	</table>
	<p>
		<input type="hidden" name="update_quickpay" value="update_quickpay">
		<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update quickpay Settings', 'event_espresso') ?>" id="save_quickpay_settings" />
	</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_quickpay_payment_settings');
