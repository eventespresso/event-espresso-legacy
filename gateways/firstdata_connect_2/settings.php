<?php

function event_espresso_firstdata_connect_2_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_firstdata_connect_2'])) {
		$firstdata_connect_2_settings['storename'] = $_POST['storename'];
		$firstdata_connect_2_settings['sharedSecret'] = $_POST['sharedSecret'];
		$firstdata_connect_2_settings['timezone'] = $_POST['timezone'];
		$firstdata_connect_2_settings['sandbox'] = empty($_POST['sandbox']) ? false : true;
		$firstdata_connect_2_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$firstdata_connect_2_settings['button_url'] = $_POST['button_url'];
		$firstdata_connect_2_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		update_option('event_espresso_firstdata_connect_2_settings', $firstdata_connect_2_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('First Data connect 2 settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
	if (empty($firstdata_connect_2_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata_connect_2/firstdata-logo.png")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_URL . "/firstdata_connect_2/firstdata-logo.png";
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/firstdata_connect_2/firstdata-logo.png";
		}
		$firstdata_connect_2_settings['storename'] = '';
		$firstdata_connect_2_settings['sharedSecret'] = '';
		$firstdata_connect_2_settings['timezone'] = '';
		$firstdata_connect_2_settings['sandbox'] = false;
		$firstdata_connect_2_settings['force_ssl_return'] = false;
		$firstdata_connect_2_settings['button_url'] = $button_url;
		$firstdata_connect_2_settings['bypass_payment_page'] = '';
		if (add_option('event_espresso_firstdata_connect_2_settings', $firstdata_connect_2_settings, '', 'no') == false) {
			update_option('event_espresso_firstdata_connect_2_settings', $firstdata_connect_2_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_firstdata_connect_2'])
					&& (!empty($_REQUEST['activate_firstdata_connect_2'])
					|| array_key_exists('firstdata_connect_2', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('First Data Connect 2 Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_firstdata_connect_2'])) {
						$active_gateways['firstdata_connect_2'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_firstdata_connect_2'])) {
						unset($active_gateways['firstdata_connect_2']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('firstdata_connect_2', $active_gateways)) {
						echo '<li id="deactivate_firstdata_connect_2" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_firstdata_connect_2=true\';" class="red_alert pointer"><strong>' . __('Deactivate First Data Connect 2?', 'event_espresso') . '</strong></li>';
						event_espresso_display_firstdata_connect_2_settings();
					} else {
						echo '<li id="activate_firstdata_connect_2" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_firstdata_connect_2=true\';" class="green_alert pointer"><strong>' . __('Activate First Data Connect 2?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//PayPal Settings Form
function event_espresso_display_firstdata_connect_2_settings() {
	$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label>
								<?php _e('First Data Storename', 'event_espresso'); ?>
							</label>
							<input type="text" name="storename" size="35" value="<?php echo $firstdata_connect_2_settings['storename']; ?>">
						</li>

						<li>
							<label>
								<?php _e('First Data Shared Secret', 'event_espresso'); ?>
							</label>
							<input type="text" name="sharedSecret" size="35" value="<?php echo $firstdata_connect_2_settings['sharedSecret']; ?>">
						</li>

						<li>
							<label for="use_sandbox">
								<?php _e('Turn on Debugging Using the', 'event_espresso'); ?> <?php _e('First Data Connect 2 Sandbox? ', 'event_espresso'); ?><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=sandbox_info_firstdata_connect_2"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="sandbox" type="checkbox" value="1" <?php echo $firstdata_connect_2_settings['sandbox'] ? 'checked="checked"' : '' ?> />
						</li>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $firstdata_connect_2_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> />
						</li>
						<li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page ', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $firstdata_connect_2_settings['bypass_payment_page']);
							?>
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="button_url" size="34" value="<?php echo $firstdata_connect_2_settings['button_url']; ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a> 
						</li>

						<li>
							<label for="timezone">
								<?php _e('Choose a timezone for the transaction? ', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=timezone"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'GMT', 'text' => __('GMT', 'event_espresso')),
									array('id' => 'EST', 'text' => __('EST', 'event_espresso')),
									array('id' => 'CST', 'text' => __('CST', 'event_espresso')),
									array('id' => 'MST', 'text' => __('MST', 'event_espresso')),
									array('id' => 'PST', 'text' => __('PST', 'event_espresso')));
							echo select_input('timezone', $values, $firstdata_connect_2_settings['timezone']);
							?>
						</li>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $firstdata_connect_2_settings['button_url'] . '" />'; ?>
						</li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_firstdata_connect_2" value="update_firstdata_connect_2">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update First Data Connect 2 Settings', 'event_espresso') ?>" id="save_first_data_connect_2_settings" />
		</p>
	</form>
	<div id="sandbox_info_firstdata_connect_2" style="display:none">
		<h2><?php _e('First Data Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the First Data Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all First Data variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The First Data Sandbox is a testing environment that is a duplicate of the live First Data site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="timezone" style="display:none">
		<h2><?php _e('Time Zone'); ?></h2>
		<p><?php _e('Time zone of the transaction. Valid values are: GMT, EST, CST, MST, PST'); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_firstdata_connect_2_payment_settings');
