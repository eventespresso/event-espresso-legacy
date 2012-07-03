<?php

function event_espresso_worldpay_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_worldpay']) && check_admin_referer('espresso_form_check', 'add_worldpay_settings')) {
		$worldpay_settings['worldpay_id'] = $_POST['worldpay_id'];
		$worldpay_settings['image_url'] = $_POST['image_url'];
		$worldpay_settings['currency_format'] = $_POST['currency_format'];
		$worldpay_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$worldpay_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$worldpay_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_worldpay_settings', $worldpay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('worldpay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$worldpay_settings = get_option('event_espresso_worldpay_settings');
	if (empty($worldpay_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/worldpay/WorldPaylogoBluetrans.png")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_URL . "/worldpay/WorldPaylogoBluetrans.png";
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/worldpay/WorldPaylogoBluetrans.png";
		}
		$worldpay_settings['worldpay_id'] = '';
		$worldpay_settings['image_url'] = '';
		$worldpay_settings['currency_format'] = 'USD';
		$worldpay_settings['use_sandbox'] = false;
		$worldpay_settings['bypass_payment_page'] = 'N';
		$worldpay_settings['button_url'] = $button_url;
		if (add_option('event_espresso_worldpay_settings', $worldpay_settings, '', 'no') == false) {
			update_option('event_espresso_worldpay_settings', $worldpay_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_worldpay'])
					&& (!empty($_REQUEST['activate_worldpay'])
					|| array_key_exists('worldpay', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<h3 class="hndle">
				<?php _e('Worldpay Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_worldpay'])) {
						$active_gateways['worldpay'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_worldpay'])) {
						unset($active_gateways['worldpay']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('worldpay', $active_gateways)) {
						echo '<li id="deactivate_worldpay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_worldpay=true\';" class="red_alert pointer"><strong>' . __('Deactivate worldpay IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_worldpay_settings();
					} else {
						echo '<li id="activate_worldpay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_worldpay=true\';" class="green_alert pointer"><strong>' . __('Activate worldpay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//worldpay Settings Form
function event_espresso_display_worldpay_settings() {
	$worldpay_settings = get_option('event_espresso_worldpay_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="worldpay_id">
								<?php _e('worldpay installation I.D.', 'event_espresso'); ?>
							</label>
							<input class="regular-text" type="text" name="worldpay_id" size="35" value="<?php echo $worldpay_settings['worldpay_id']; ?>" />
							<p><?php _e('(Typically payment@yourdomain.com)', 'event_espresso'); ?></p>
						</li>

						<li>
							<label for="button_url">
								<?php _e('Button Image URL: ', 'event_espresso'); ?> <?php echo apply_filters('espresso_help', 'button_image'); ?>
							</label>
							<input class="regular-text" type="text" name="button_url" size="34" value="<?php echo $worldpay_settings['button_url']; ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a> <?php echo '<img src="' . $worldpay_settings['button_url'] . '" />'; ?></li>

						<li>
							<label for="image_url">
								<?php _e('Image URL (logo for payment page):', 'event_espresso'); ?> <?php echo apply_filters('espresso_help', 'worldpay_image_url_info'); ?>
							</label>
							<input class="regular-text" type="text" name="image_url" size="35" value="<?php echo $worldpay_settings['image_url']; ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=image_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a><br />
							<?php _e('(used for your business/personal logo on the worldpay page)', 'event_espresso'); ?>
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label for="currency_format">
								<?php _e('Select the currency for your country:', 'event_espresso'); ?> <?php echo apply_filters('espresso_help', 'worldpay_currency_info'); ?>
							</label>
							<select name="currency_format" data-placeholder="Choose a currency..." class="chzn-select wide">
								<option value="<?php echo $worldpay_settings['currency_format']; ?>"><?php echo $worldpay_settings['currency_format']; ?></option>
								<option value="USD">
									<?php _e('U.S. Dollars ($)', 'event_espresso'); ?>
								</option>
								<option value="AUD">
									<?php _e('Australian Dollars (A $)', 'event_espresso'); ?>
								</option>
								<option value="GBP">
									<?php _e('Pounds Sterling (&pound;)', 'event_espresso'); ?>
								</option>
								<option value="CAD">
									<?php _e('Canadian Dollars (C $)', 'event_espresso'); ?>
								</option>
								<option value="CZK">
									<?php _e('Czech Koruna', 'event_espresso'); ?>
								</option>
								<option value="DKK">
									<?php _e('Danish Krone', 'event_espresso'); ?>
								</option>
								<option value="EUR">
									<?php _e('Euros (&#8364;)', 'event_espresso'); ?>
								</option>
								<option value="CHF">
									<?php _e('Swiss Franc', 'event_espresso'); ?>
								</option>
								<option value="HKD">
									<?php _e('Hong Kong Dollar ($)', 'event_espresso'); ?>
								</option>
								<option value="HUF">
									<?php _e('Hungarian Forint', 'event_espresso'); ?>
								</option>
								<option value="ILS">
									<?php _e('Israeli Shekel', 'event_espresso'); ?>
								</option>
								<option value="JPY">
									<?php _e('Yen (&yen;)', 'event_espresso'); ?>
								</option>
								<option value="MXN">
									<?php _e('Mexican Peso', 'event_espresso'); ?>
								</option>
								<option value="NZD">
									<?php _e('New Zealand Dollar ($)', 'event_espresso'); ?>
								</option>
								<option value="NOK">
									<?php _e('Norwegian Krone', 'event_espresso'); ?>
								</option>
								<option value="PLN">
									<?php _e('Polish Zloty', 'event_espresso'); ?>
								</option>
								<option value="SGD">
									<?php _e('Singapore Dollar ($)', 'event_espresso'); ?>
								</option>
								<option value="SEK">
									<?php _e('Swedish Krona', 'event_espresso'); ?>
								</option>
								<option value="BRL">
									<?php _e('Brazilian Real (only for Brazilian users)', 'event_espresso'); ?>
								</option>
								<option value="MYR">
									<?php _e('Malaysian Ringgits (only for Malaysian users)', 'event_espresso'); ?>
								</option>
								<option value="PHP">
									<?php _e('Philippine Pesos', 'event_espresso'); ?>
								</option>
								<option value="TWD">
									<?php _e('Taiwan New Dollars', 'event_espresso'); ?>
								</option>
								<option value="THB">
									<?php _e('Thai Baht', 'event_espresso'); ?>
								</option>
							</select>

						<li><label for="bypass_payment_page">
								<?php _e('By-pass the payment confirmation page?', 'event_espresso'); ?> <?php echo apply_filters('espresso_help', 'worldpay_bypass_confirmation'); ?>
							</label>
							<?php
							$values = array(
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $worldpay_settings['bypass_payment_page']);
							?></li>

						<li>
							<label for="use_sandbox">
								<?php _e('Use the debugging feature and the worldpay Sandbox?', 'event_espresso'); ?> <?php echo apply_filters('espresso_help', 'worldpay_sandbox_info'); ?>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $worldpay_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />

						</li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_worldpay" value="update_worldpay">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update worldpay Settings', 'event_espresso') ?>" id="save_worldpay_settings" />
		</p>
		<?php wp_nonce_field('espresso_form_check', 'add_worldpay_settings'); ?>
	</form>

	<div id="worldpay_sandbox_info" style="display:none">
		<h2><?php _e('worldpay Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the worldpay Sandbox fetaure. The debugging feature will also output the form varibales to the payment page, send an email to the admin that contains the all worldpay variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The worldpay Sandbox is a testing environment that is a duplicate of the live worldpay site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live worldpay environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>

	<div id="worldpay_image_url_info" style="display:none">
		<h2>
			<?php _e('worldpay Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the worldpay checkout pages.', 'event_espresso'); ?>
		</p>
		<p>
			<?php _e('Default - Your business name, if you have a Business account, or your email address, if you have Premier or Personal account.', 'event_espresso'); ?>
		</p>
	</div>

	<div id="worldpay_currency_info" style="display:none">
		<h2><?php _e('worldpay Currency', 'event_espresso'); ?></h2>
		<p><?php _e('worldpay uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that worldpay (currently) supports.', 'event_espresso'); ?> </p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_worldpay_payment_settings');
