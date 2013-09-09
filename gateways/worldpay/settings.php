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
		$worldpay_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$worldpay_settings['bypass_payment_page'] = empty($_POST['bypass_payment_page']) ? false : true;
		$worldpay_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_worldpay_settings', $worldpay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('WorldPay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$worldpay_settings = get_option('event_espresso_worldpay_settings');
	if (empty($worldpay_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/worldpay/worldpay-logo.png")) {
			$worldpay_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/worldpay/worldpay-logo.png";
		} else {
			$worldpay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/worldpay/worldpay-logo.png";
		}
		$worldpay_settings['worldpay_id'] = '';
		$worldpay_settings['image_url'] = '';
		$worldpay_settings['currency_format'] = 'USD';
		$worldpay_settings['use_sandbox'] = FALSE;
		$worldpay_settings['force_ssl_return'] = FALSE;
		$worldpay_settings['bypass_payment_page'] = FALSE;
		if (add_option('event_espresso_worldpay_settings', $worldpay_settings, '', 'no') == false) {
			update_option('event_espresso_worldpay_settings', $worldpay_settings);
		}
	}

	if ( ! isset( $worldpay_settings['button_url'] ) || ! file_exists( $worldpay_settings['button_url'] )) {
		$worldpay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
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
						echo '<li id="deactivate_worldpay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_worldpay=true\';" class="red_alert pointer"><strong>' . __('Deactivate WorldPay IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_worldpay_settings();
					} else {
						echo '<li id="activate_worldpay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_worldpay=true\';" class="green_alert pointer"><strong>' . __('Activate WorldPay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//WorldPay Settings Form
function event_espresso_display_worldpay_settings() {
	global $org_options;
	$worldpay_settings = get_option('event_espresso_worldpay_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="worldpay_id">
								<?php _e('WorldPay Installation ID', 'event_espresso'); ?>
							</label>
							<input class="regular-text" type="text" name="worldpay_id" size="35" value="<?php echo $worldpay_settings['worldpay_id']; ?>" /><br />
							<?php _e('(Typically payment@yourdomain.com)', 'event_espresso'); ?>
						</li>

						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $worldpay_settings['button_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a></li>

						<li>
							<label for="image_url">
								<?php _e('Image URL (logo for payment page)', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=worldpay_image_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="image_url" size="35" value="<?php echo $worldpay_settings['image_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a><br />
							<?php _e('(used for your business/personal logo on the WorldPay page)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="currency_format">
								<?php _e('Select the Currency for Your Country', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=worldpay_currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<select name="currency_format" data-placeholder="Choose a currency..." class="wide">
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
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label><?php _e('Relay Response URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=wp_relay_response"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></label>
							<span class="display-path" style="background-color: rgb(255, 251, 204); border:#999 solid 1px; padding:2px;"><?php echo get_permalink($org_options['return_url']); ?></span>  </li>
						<li>
						<li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="bypass_payment_page" type="checkbox" value="1" <?php echo $worldpay_settings['bypass_payment_page'] ? 'checked="checked"' : '' ?> />
						</li>

						<li>
							<label for="use_sandbox">
								<?php _e('Turn on Debugging Using the WorldPay Sandbox?', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=worldpay_sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $worldpay_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />

						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($worldpay_settings['force_ssl_return']) && $worldpay_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $worldpay_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
	<?php echo (($worldpay_settings['button_url'] == '') ? '' : '<img src="' . $worldpay_settings['button_url'] . '" />'); ?></li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_worldpay" value="update_worldpay">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update WorldPay Settings', 'event_espresso') ?>" id="save_worldpay_settings" />
		</p>
		<?php wp_nonce_field('espresso_form_check', 'add_worldpay_settings'); ?>
	</form>

	<div id="worldpay_sandbox_info" style="display:none">
		<h2><?php _e('WorldPay Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the WorldPay Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all WorldPay variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The WorldPay Sandbox is a testing environment that is a duplicate of the live WorldPay site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live WorldPay environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>

	<div id="wp_relay_response" style="display:none">
		<h2><?php _e('Relay Response', 'event_espresso'); ?></h2>
		<p><?php _e('This shows the specific the URL to which the gateway should return the relay response for a transaction. This the page should be set in your Worldpay account.', 'event_espresso'); ?></p>
		<p><strong><?php _e('Relay Response URL:', 'event_espresso'); ?></strong> <?php echo home_url() . '/?page_id=' . $org_options['return_url'] ?><br />
			<span style="color:red;"><?php _e('Note:', 'event_espresso'); ?></span> <?php _e('This URL can be changed in the "Organization Settings" page.', 'event_espresso'); ?></p>
		<p><strong><?php _e('Enabling Payment Response', 'event_espresso'); ?></strong>
			<?php _e('As a default the payment response feature is set to OFF by default, to enable this
feature:', 'event_espresso'); ?></p>
		<ol>
			<li><?php _e('Log in to the Merchant Interface', 'event_espresso'); ?></li>
			<li><?php _e('Select Installations from the left hand navigation', 'event_espresso'); ?></li>
			<li><?php _e('Choose an installation and select the Integration Setup button for either the
TEST or PRODUCTION environment', 'event_espresso'); ?></li>
			<li><?php _e('Check the Enable Payment Response checkbox', 'event_espresso'); ?></li>
			<li><?php _e('Enter the Payment Response URL of the server-side script that is hosted on
your web server', 'event_espresso'); ?></li>
			<li><?php _e('Select the Save Changes button', 'event_espresso'); ?></li>
		</ol>
		<p>
			<span style="color:red;"><?php _e('Note:', 'event_espresso'); ?></span><?php _e('If your Payment Response URL starts with HTTPS:// you will need to
make sure that your server supports either SSL 3.0 or TLS 1.0.', 'event_espresso'); ?>
		</p>
	</div>

	<div id="worldpay_image_url_info" style="display:none">
		<h2>
			<?php _e('WorldPay Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the WorldPay checkout pages.', 'event_espresso'); ?>
		</p>
		<p>
			<?php _e('Default - Your business name, if you have a Business account, or your email address, if you have Premier or Personal account.', 'event_espresso'); ?>
		</p>
	</div>

	<div id="worldpay_currency_info" style="display:none">
		<h2><?php _e('WorldPay Currency', 'event_espresso'); ?></h2>
		<p><?php _e('WorldPay uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that WorldPay (currently) supports.', 'event_espresso'); ?> </p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_worldpay_payment_settings');
