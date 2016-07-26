<?php

function event_espresso_realauth_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_realauth'])) {
		$realauth_settings['merchant_id'] = $_POST['merchant_id'];
		$realauth_settings['shared_secret'] = $_POST['shared_secret'];
		$realauth_settings['currency_format'] = $_POST['currency_format'];
		$realauth_settings['auto_settle'] = $_POST['auto_settle'];
		$realauth_settings['button_url'] = $_POST['button_url'];
		$realauth_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$realauth_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		if (update_option('event_espresso_realauth_settings', $realauth_settings) == true) {
			echo '<div id="message" class="updated fade"><p><strong>' . __('RealAuth settings saved.', 'event_espresso') . '</strong></p></div>';
		}
	}
	$realauth_settings = get_option('event_espresso_realauth_settings');
	if (empty($realauth_settings['button_url'])) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/realauth/realauth-logo.png")) {
			$realauth_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/realauth/realauth-logo.png";
		} else {
			$realauth_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/realauth/realauth-logo.png";
		}
		$realauth_settings['merchant_id'] = '';
		$realauth_settings['shared_secret'] = '';
		$realauth_settings['currency_format'] = 'USD';
		$realauth_settings['auto_settle'] = 'Y';
		$realauth_settings['use_sandbox'] = false;
		$realauth_settings['bypass_payment_page'] = 'N';
		if (add_option('event_espresso_realauth_settings', $realauth_settings, '', 'no') == false) {
			update_option('event_espresso_realauth_settings', $realauth_settings);
		}
	}

	if ( ! isset( $realauth_settings['button_url'] ) || ! file_exists( $realauth_settings['button_url'] )) {
		$realauth_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_realauth'])
					&& (!empty($_REQUEST['activate_realauth'])
					|| array_key_exists('realauth', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<a name="realauth" id="realauth"></a>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br />
			</div>
			<h3 class="hndle">
				<?php _e('Realex RealAuth Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_realauth'])) {
						$active_gateways['realauth'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_realauth'])) {
						unset($active_gateways['realauth']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('realauth', $active_gateways)) {
						echo '<li id="deactivate_realauth" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_realauth=true\';" class="red_alert pointer"><strong>' . __('Deactivate RealAuth Payments?', 'event_espresso') . '</strong></li>';
						event_espresso_display_realauth_settings();
					} else {
						echo '<li id="activate_realauth" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_realauth=true#realauth\';" class="green_alert pointer"><strong>' . __('Activate RealAuth Payments?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//RealAuth Settings Form
function event_espresso_display_realauth_settings() {
	$realauth_settings = get_option('event_espresso_realauth_settings');

	$values = array(
			array('id' => 'Y', 'text' => __('Yes', 'event_espresso')),
			array('id' => 'N', 'text' => __('No', 'event_espresso')),
	);
	$uri = $_SERVER['REQUEST_URI'];
	$uri = substr("$uri", 0, strpos($uri, '&activate_realauth=true'));
	?>
	<form method="post" action="<?php echo $uri; ?>#realauth">
		<table width="99%" border="0" cellspacing="5" cellpadding="5" class="form-table">
			<tbody>
				<tr>
					<td valign="top">
						<ul>
							<li>
								<label for="merchant_id"><?php _e('Merchant ID', 'event_espresso'); ?></label>
								<input class="regular-text" type="text" name="merchant_id" id="merchant_id" size="35" value="<?php echo $realauth_settings['merchant_id']; ?>">
							</li>
							<li>
								<label for="shared_secret"><?php _e('Shared Secret', 'event_espresso'); ?></label>
								<input class="regular-text" type="text" name="shared_secret" id="shared_secret" size="35" value="<?php echo $realauth_settings['shared_secret']; ?>">
							</li>
							<li>
								<label for="currency_format"><?php _e('Country Currency', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=realauth_currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></label>
								<select name="currency_format" data-placeholder="Choose a currency..." class="wide">
									<option value="<?php echo $realauth_settings['currency_format']; ?>"><?php echo $realauth_settings['currency_format']; ?></option>									<option value="EUR"><?php _e('Euro', 'event_espresso'); ?></option>
									<option value="GBP"><?php _e('Pound Sterling', 'event_espresso'); ?></option>
									<option value="USD"><?php _e('U.S. Dollar', 'event_espresso'); ?></option>
									<option value="SEK"><?php _e('Swedish Krona', 'event_espresso'); ?></option>
									<option value="CHF"><?php _e('Swiss Franc', 'event_espresso'); ?></option>
									<option value="HKD"><?php _e('Hong Kong Dollar', 'event_espresso'); ?></option>
									<option value="JPY"><?php _e('Japanese Yen', 'event_espresso'); ?></option>
								</select>
							</li>
							<li>
								<label for="button_url"><?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></label> 
								<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $realauth_settings['button_url']; ?>" />
								<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
							</li>
						</ul>
					</td>
					<td valign="top">
						<ul>
							<li>
								<label for="use_sandbox"><?php _e('Use the debugging feature'); ?></label>
								<input name="use_sandbox" type="checkbox" value="1" <?php echo $realauth_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							</li>
							<li>
								<label for="bypass_payment_page"><?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></label>
								<?php echo select_input('bypass_payment_page', $values, $realauth_settings['bypass_payment_page']); ?>
							</li>
							<li>
								<label for="auto_settle"><?php _e('Auto settle transactions', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=auto_settle_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></label>
								<?php echo select_input('auto_settle', $values, $realauth_settings['auto_settle']); ?>
							</li>
							<li>
						<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
						<?php echo '<img src="' . $realauth_settings['button_url'] . '" />'; ?>
							</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<input type="hidden" name="update_realauth" value="update_realauth">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update RealAuth Settings', 'event_espresso') ?>" id="save_realauth_settings" />
		</p>
		<?php wp_nonce_field('espresso_form_check', 'add_realauth_settings'); ?>
	</form>
	<div id="auto_settle_info" style="display:none">
		<h2>
			<?php _e('RealAuth Auto Settle', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('Used to signify whether or not you wish the transaction to be captured in the next batch or not. If set to “Y” and assuming the transaction is authorised then it will automatically be settled in the next batch. If set to “N” then the merchant must use the realcontrol application to manually settle the transaction. This option can be used if a merchant wishes to delay the payment until after the goods have been shipped. Transactions can be settled for up to 115% of the original amount.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="realauth_currency_info" style="display:none">
		<h2>
			<?php _e('RealAuth Currency', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('RealAuth uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that RealAuth (currently) supports.', 'event_espresso'); ?>
		</p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_realauth_payment_settings');
