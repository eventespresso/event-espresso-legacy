<?php

function event_espresso_eway_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_eway'])) {
		$eway_settings['eway_id'] = $_POST['eway_id'];
		$eway_settings['eway_username'] = $_POST['eway_username'];
		$eway_settings['image_url'] = $_POST['image_url'];
		$eway_settings['currency_format'] = $_POST['currency_format'];
		$eway_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false: true;
		$eway_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$eway_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$eway_settings['button_url'] = $_POST['button_url'];
		$eway_settings['region'] = $_POST['region'];
		update_option('event_espresso_eway_settings', $eway_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('eway settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$eway_settings = get_option('event_espresso_eway_settings');
	if (empty($eway_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/eway/eway_logo.png")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_URL . "/eway/eway_logo.png";
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/eway/eway_logo.png";
		}
		$eway_settings['eway_id'] = '';
		$eway_settings['eway_username'] = '';
		$eway_settings['image_url'] = '';
		$eway_settings['currency_format'] = 'GBP';
		$eway_settings['use_sandbox'] = false;
		$eway_settings['bypass_payment_page'] = 'N';
		$eway_settings['force_ssl_return'] = false;
		$eway_settings['button_url'] = $button_url;
		$eway_settings['region'] = 'UK';
		if (add_option('event_espresso_eway_settings', $eway_settings, '', 'no') == false) {
			update_option('event_espresso_eway_settings', $eway_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_eway'])
					&& (!empty($_REQUEST['activate_eway'])
					|| array_key_exists('eway', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Eway Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_eway'])) {
						$active_gateways['eway'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_eway'])) {
						unset($active_gateways['eway']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('eway', $active_gateways)) {
						echo '<li id="deactivate_eway" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_eway=true\';" class="red_alert pointer"><strong>' . __('Deactivate eway IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_eway_settings();
					} else {
						echo '<li id="activate_eway" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_eway=true\';" class="green_alert pointer"><strong>' . __('Activate eway IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//eway Settings Form
function event_espresso_display_eway_settings() {
	$eway_settings = get_option('event_espresso_eway_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="eway_id">
								<?php _e('eway I.D.', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="eway_id" size="35" value="<?php echo $eway_settings['eway_id']; ?>">
							<br />
							<?php _e('(Typically 87654321)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="eway_username">
								<?php _e('eway username', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="eway_username" size="35" value="<?php echo $eway_settings['eway_username']; ?>">
							<br />
							<?php _e('(Typically TestAccount)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="currency_format">
								<?php _e('Select the currency for your country:', 'event_espresso'); ?>
							</label>
							<br />
							<select name="currency_format">
								<option value="<?php echo $eway_settings['currency_format']; ?>"><?php echo $eway_settings['currency_format']; ?></option>
								<option value="AUD">
									<?php _e('Australian Dollars (A $)', 'event_espresso'); ?>
								</option>
								<option value="GBP">
									<?php _e('Pounds Sterling (&pound;)', 'event_espresso'); ?>
								</option>
								<option value="NZD">
									<?php _e('New Zealand Dollar ($)', 'event_espresso'); ?>
								</option>
							</select>
							<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a> </li>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL: ', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="button_url" size="34" value="<?php echo (($eway_settings['button_url'] == '') ? '' : $eway_settings['button_url'] ); ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a> </li>
						<li>
							<label for="image_url">
								<?php _e('Image URL (logo for payment page):', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="image_url" size="35" value="<?php echo $eway_settings['image_url']; ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=image_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=image_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a><br />
							<?php _e('(used for your business/personal logo on the eway page)', 'event_espresso'); ?>
						</li>
					</ul></td>
				<td valign="top">
					<ul>
						<li>
							<label for="bypass_payment_page">
								<?php _e('By-pass the payment confirmation page?', 'event_espresso'); ?>
							</label>
							<?php
							$values = array(
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $eway_settings['bypass_payment_page']);
							?>
							&nbsp;<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></li>
						<li>
							<label for="force_ssl_return">
								<?php _e('Do you want to force the return url to be https? ', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $eway_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="region">
								<?php _e('Select the region where you want to use eWay:', 'event_espresso'); ?>
							</label>
							<br />
							<select name="region">
								<option value="<?php echo $eway_settings['region']; ?>"><?php echo $eway_settings['region']; ?></option>
								<option value="UK">
									<?php _e('United Kingdom', 'event_espresso'); ?>
								</option>
								<option value="AU">
									<?php _e('Australia', 'event_espresso'); ?>
								</option>
								<option value="NZ">
									<?php _e('New Zealand', 'event_espresso'); ?>
								</option>
							</select>
						</li>
						<li>
							<label for="use_sandbox">
								<?php _e('Use the debugging feature and the', 'event_espresso'); ?> <a href="https://developer.eway.com/devscr?cmd=_home||https://cms.eway.com/us/cgi-bin/?&amp;cmd=_render-content&amp;content_ID=developer/howto_testing_sandbox||https://cms.eway.com/us/cgi-bin/?&amp;cmd=_render-content&amp;content_ID=developer/howto_testing_sandbox_get_started" title="eway Sandbox Login||Sandbox Tutorial||Getting Started with eway Sandbox" target="_blank"><?php _e('eway Sandbox', 'event_espresso'); ?></a>?
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $eway_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							&nbsp;<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a><br />
						</li>
						<li>
							<?php _e('Current Button Image:', 'event_espresso'); ?>
							<br />
							<?php echo (($eway_settings['button_url'] == '') ? '<img src="' . $button_url . '" />' : '<img src="' . $eway_settings['button_url'] . '" />'); ?></li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_eway" value="update_eway">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update eway Settings', 'event_espresso') ?>" id="save_eway_settings" />
		</p>
	</form>
	<div id="sandbox_info" style="display:none">
		<h2><?php _e('eway Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the eway Sandbox fetaure. The debugging feature will also output the form varibales to the payment page, send an email to the admin that contains the all eway variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The eway Sandbox is a testing environment that is a duplicate of the live eway site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live eway environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="image_url_info" style="display:none">
		<h2>
			<?php _e('eway Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the eway checkout pages.', 'event_espresso'); ?>
		</p>
		<p>
			<?php _e('Default - Your business name, if you have a Business account, or your email address, if you have Premier or Personal account.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('eway Currency', 'event_espresso'); ?></h2>
		<p><?php _e('eway uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is British Pounds (GBP). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that eway (currently) supports.', 'event_espresso'); ?> </p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_eway_payment_settings');
