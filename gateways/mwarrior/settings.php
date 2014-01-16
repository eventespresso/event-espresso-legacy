<?php

function event_espresso_mwarrior_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_mwarrior'])) {
		$mwarrior_settings['mwarrior_id'] = $_POST['mwarrior_id'];
		$mwarrior_settings['mwarrior_apikey'] = $_POST['mwarrior_apikey'];
		$mwarrior_settings['mwarrior_passphrase'] = $_POST['mwarrior_passphrase'];
		$mwarrior_settings['image_url'] = $_POST['image_url'];
		$mwarrior_settings['currency_format'] = $_POST['currency_format'];
		$mwarrior_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$mwarrior_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$mwarrior_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$mwarrior_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_mwarrior_settings', $mwarrior_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Mwarrior settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$mwarrior_settings = get_option('event_espresso_mwarrior_settings');
	if (empty($mwarrior_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/mwarrior/mwarrior-logo.png")) {
			$mwarrior_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/mwarrior/mwarrior-logo.png";
		} else {
			$mwarrior_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/mwarrior/mwarrior-logo.png";
		}
		$mwarrior_settings['mwarrior_id'] = '';
		$mwarrior_settings['mwarrior_apikey'] = '';
		$mwarrior_settings['mwarrior_passphrase'] = '';
		$mwarrior_settings['image_url'] = '';
		$mwarrior_settings['currency_format'] = 'USD';
		$mwarrior_settings['use_sandbox'] = false;
		$mwarrior_settings['force_ssl_return'] = false;
		$mwarrior_settings['bypass_payment_page'] = '';
		if (add_option('event_espresso_mwarrior_settings', $mwarrior_settings, '', 'no') == false) {
			update_option('event_espresso_mwarrior_settings', $mwarrior_settings);
		}
	}

	if ( ! isset( $mwarrior_settings['button_url'] ) || ! file_exists( $mwarrior_settings['button_url'] )) {
		$mwarrior_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_mwarrior'])
					&& (!empty($_REQUEST['activate_mwarrior'])
					|| array_key_exists('mwarrior', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Merchant Warrior Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_mwarrior'])) {
						$active_gateways['mwarrior'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_mwarrior'])) {
						unset($active_gateways['mwarrior']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('mwarrior', $active_gateways)) {
						echo '<li id="deactivate_mwarrior" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_mwarrior=true\';" class="red_alert pointer"><strong>' . __('Deactivate Merchant Warrior IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_mwarrior_settings();
					} else {
						echo '<li id="activate_mwarrior" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_mwarrior=true\';" class="green_alert pointer"><strong>' . __('Activate Merchant Warrior IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Mwarrior Settings Form
function event_espresso_display_mwarrior_settings() {
	$mwarrior_settings = get_option('event_espresso_mwarrior_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="mwarrior_id">
								<?php _e('MW Merchant UUID', 'event_espresso'); ?>
							</label>
							<input type="text" name="mwarrior_id" size="35" value="<?php echo $mwarrior_settings['mwarrior_id']; ?>" />
						</li>
						<li>
							<label for="mwarrior_apikey">
								<?php _e('MW API Key', 'event_espresso'); ?>
							</label>
							<input type="text" name="mwarrior_apikey" size="35" value="<?php echo $mwarrior_settings['mwarrior_apikey']; ?>" />
						</li>
						<li>
							<label for="mwarrior_passphrase">
								<?php _e('MW API Passphrase', 'event_espresso'); ?>
							</label>
							<input type="text" name="mwarrior_passphrase" size="35" value="<?php echo $mwarrior_settings['mwarrior_passphrase']; ?>" />
						</li>
						<li>
							<label for="currency_format">
								<?php _e('Select the Currency for Your Country', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<select name="currency_format">
								<option value="AUD" <?php echo ($mwarrior_settings['currency_format'] == "AUD") ? "selected" : ""; ?>>
									<?php _e('Australian Dollars (A $)', 'event_espresso'); ?>
								</option>
								<option value="USD" <?php echo ($mwarrior_settings['currency_format'] == "USD") ? "selected" : ""; ?>>
									<?php _e('U.S. Dollars ($)', 'event_espresso'); ?>
								</option>
								<option value="GBP" <?php echo ($mwarrior_settings['currency_format'] == "GBP") ? "selected" : ""; ?>>
									<?php _e('Pounds Sterling (&pound;)', 'event_espresso'); ?>
								</option>
								<option value="CAD" <?php echo ($mwarrior_settings['currency_format'] == "CAD") ? "selected" : ""; ?>>
									<?php _e('Canadian Dollars (C $)', 'event_espresso'); ?>
								</option>
								<option value="EUR" <?php echo ($mwarrior_settings['currency_format'] == "EUR") ? "selected" : ""; ?>>
									<?php _e('Euros (&#8364;)', 'event_espresso'); ?>
								</option>
								<option value="JPY" <?php echo ($mwarrior_settings['currency_format'] == "JPY") ? "selected" : ""; ?>>
									<?php _e('Yen (&yen;)', 'event_espresso'); ?>
								</option>
								<option value="NZD" <?php echo ($mwarrior_settings['currency_format'] == "NZD") ? "selected" : ""; ?>>
									<?php _e('New Zealand Dollar ($)', 'event_espresso'); ?>
								</option>
								<option value="SGD" <?php echo ($mwarrior_settings['currency_format'] == "SGD") ? "selected" : ""; ?>>
									<?php _e('Singapore Dollar ($)', 'event_espresso'); ?>
								</option>
							</select>
							 </li>
						<li>
							<label for="image_url">
								<?php _e('Image URL (logo for payment page)', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=image_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="image_url" size="35" value="<?php echo $mwarrior_settings['image_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a> <br />
							<?php _e('(used for your business/personal logo on the Merchant Warrior page)', 'event_espresso'); ?>
						</li>
					</ul></td>
				<td valign="top"><ul>
						<li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $mwarrior_settings['bypass_payment_page']);
							?>
							</li>
						<li>
							<label for="use_sandbox">
								<?php _e('Use the Test Mode for Merchant Warrior', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $mwarrior_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($mwarrior_settings['force_ssl_return']) && $mwarrior_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $mwarrior_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo (isset($mwarrior_settings['button_url']) ? $mwarrior_settings['button_url'] : '' ); ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $mwarrior_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_mwarrior" value="update_mwarrior">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Merchant Warrior Settings', 'event_espresso') ?>" id="save_mwarrior_settings" />
		</p>
	</form>
	<div id="sandbox_info" style="display:none">
		<h2><?php _e('Merchant Warrior Test Mode', 'event_espresso'); ?></h2>
		<p><?php _e('Test Mode allows you to submit test transactions to the payment gateway. This allows you to test your entire integration before submitting transactions to the live Merchant Warrior environment. ', 'event_espresso'); ?></p>
	</div>
	<div id="image_url_info" style="display:none">
		<h2>
			<?php _e('Merchant Warrior Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the image displayed as your logo in the header of the Merchant Warrior checkout pages.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('Merchant Warrior Currency', 'event_espresso'); ?></h2>
		<p><?php _e('Merchant Warrior uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is Australian Dollars (AUD). If you want to accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that Merchant Warrior (currently) supports.', 'event_espresso'); ?> </p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_mwarrior_payment_settings');
