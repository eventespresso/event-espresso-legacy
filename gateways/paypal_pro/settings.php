<?php

function event_espresso_paypal_pro_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_paypal_pro'])) {
		$paypal_pro_settings['paypal_pro_email'] = $_POST['paypal_pro_email'];
		$paypal_pro_settings['paypal_api_username'] = $_POST['paypal_api_username'];
		$paypal_pro_settings['paypal_api_password'] = $_POST['paypal_api_password'];
		$paypal_pro_settings['currency_format'] = $_POST['currency_format'];
		$paypal_pro_settings['paypal_api_signature'] = $_POST['paypal_api_signature'];
		$paypal_pro_settings['paypal_api_credit_cards'] = implode(",", empty($_POST['paypal_api_credit_cards']) ? array() : $_POST['paypal_api_credit_cards']);
		$paypal_pro_settings['paypal_pro_use_sandbox'] = empty($_POST['paypal_pro_use_sandbox']) ? false : true;
		$paypal_pro_settings['header'] = $_POST['header'];
		$paypal_pro_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$paypal_pro_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('event_espresso_paypal_pro_settings', $paypal_pro_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('PayPal Payments Pro settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$paypal_pro_settings = get_option('event_espresso_paypal_pro_settings');
	if (empty($paypal_pro_settings)) {
		$paypal_pro_settings['paypal_pro_email'] = '';
		$paypal_pro_settings['paypal_api_username'] = '';
		$paypal_pro_settings['paypal_api_password'] = '';
		$paypal_pro_settings['currency_format'] = 'USD';
		$paypal_pro_settings['paypal_api_signature'] = '';
		$paypal_pro_settings['paypal_api_credit_cards'] = '';
		$paypal_pro_settings['paypal_pro_use_sandbox'] = false;
		$paypal_pro_settings['header'] = 'Payment Transactions by PayPal Payments Pro';
		$paypal_pro_settings['force_ssl_return'] = false;
		$paypal_pro_settings['display_header'] = false;
		if (add_option('event_espresso_paypal_pro_settings', $paypal_pro_settings, '', 'no') == false) {
			update_option('event_espresso_paypal_pro_settings', $paypal_pro_settings);
		}
	}

	if ( ! isset( $paypal_pro_settings['button_url'] ) || ! file_exists( $paypal_pro_settings['button_url'] )) {
		$paypal_pro_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_paypal_pro'])
					&& (!empty($_REQUEST['activate_paypal_pro'])
					|| array_key_exists('paypal_pro', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('PayPal Payments Pro Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_paypal_pro'])) {
						$active_gateways['paypal_pro'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_paypal_pro'])) {
						unset($active_gateways['paypal_pro']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('paypal_pro', $active_gateways)) {
						echo '<li id="deactivate_paypal_pro" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_paypal_pro=true\';" class="red_alert pointer"><strong>' . __('Deactivate PayPal Payments Pro?', 'event_espresso') . '</strong></li>';
						event_espresso_display_paypal_pro_settings();
					} else {
						echo '<li id="activate_paypal_pro" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_paypal_pro=true\';" class="green_alert pointer"><strong>' . __('Activate PayPal Payments Pro?', 'event_espresso') . '</strong></li>';
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
function event_espresso_display_paypal_pro_settings() {
	$paypal_pro_settings = get_option('event_espresso_paypal_pro_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="paypal_pro_email">
								<?php _e('PayPal Payments Pro Email', 'event_espresso'); ?>
							</label>
							<input type="text" name="paypal_pro_email" size="35" value="<?php echo $paypal_pro_settings['paypal_pro_email']; ?>">
						</li>
						<li>
							<label for="paypal_api_username">
								<?php _e('PayPal API Username', 'event_espresso'); ?>
							</label>
							<input type="text" name="paypal_api_username" size="35" value="<?php echo $paypal_pro_settings['paypal_api_username']; ?>">
						</li>
						<li>
							<label for="paypal_api_password">
								<?php _e('PayPal API Password', 'event_espresso'); ?>
							</label>
							<input type="text" name="paypal_api_password" size="35" value="<?php echo $paypal_pro_settings['paypal_api_password']; ?>">
						</li>
						<li>
							<label for="paypal_api_signature">
								<?php _e('PayPal API Signature', 'event_espresso'); ?>
							</label>
							<input type="text" name="paypal_api_signature" size="35" value="<?php echo $paypal_pro_settings['paypal_api_signature']; ?>">
							<br />

						</li>
						<li>
							<label for="currency_format">
								<?php _e('Select the Currency for Your Country', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<select name="currency_format">
								<option value="<?php echo $paypal_pro_settings['currency_format']; ?>"><?php echo $paypal_pro_settings['currency_format']; ?></option>
								<option value="USD">
									<?php _e('U.S. Dollars ($)', 'event_espresso'); ?>
								</option>
								<option value="GBP">
									<?php _e('Pounds Sterling (&pound;)', 'event_espresso'); ?>
								</option>
								<option value="CAD">
									<?php _e('Canadian Dollars (C $)', 'event_espresso'); ?>
								</option>
								<option value="AUD">
									<?php _e('Australian Dollars (A $)', 'event_espresso'); ?>
								</option>
								<option value="BRL">
									<?php _e('Brazilian Real (only for Brazilian users)', 'event_espresso'); ?>
								</option>
								<option value="CHF">
									<?php _e('Swiss Franc', 'event_espresso'); ?>
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
								<option value="MYR">
									<?php _e('Malaysian Ringgits (only for Malaysian users)', 'event_espresso'); ?>
								</option>
								<option value="NOK">
									<?php _e('Norwegian Krone', 'event_espresso'); ?>
								</option>
								<option value="NZD">
									<?php _e('New Zealand Dollar ($)', 'event_espresso'); ?>
								</option>
								<option value="PHP">
									<?php _e('Philippine Pesos', 'event_espresso'); ?>
								</option>
								<option value="PLN">
									<?php _e('Polish Zloty', 'event_espresso'); ?>
								</option>
								<option value="SEK">
									<?php _e('Swedish Krona', 'event_espresso'); ?>
								</option>
								<option value="SGD">
									<?php _e('Singapore Dollar ($)', 'event_espresso'); ?>
								</option>
								<option value="THB">
									<?php _e('Thai Baht', 'event_espresso'); ?>
								</option>
								<option value="TRY">
									<?php _e('Turkish Lira (only for Turkish users)', 'event_espresso'); ?>
								</option>
								<option value="TWD">
									<?php _e('Taiwan New Dollars', 'event_espresso'); ?>
								</option>
							</select>
							 </li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label>
								<?php _e('Accepted Credit Cards', 'event_espresso'); ?>
							</label>
							<?php
							$checked = 'checked="checked"';
							$paypal_api_credit_cards = explode(",", $paypal_pro_settings['paypal_api_credit_cards']);
							?>
							<input type="checkbox" name="paypal_api_credit_cards[]" size="35" value="Visa" <?php echo in_array("Visa", $paypal_api_credit_cards) ? $checked : ''; ?> /> Visa
							<input type="checkbox" name="paypal_api_credit_cards[]" size="35" value="MasterCard" <?php echo in_array("MasterCard", $paypal_api_credit_cards) ? $checked : ''; ?> /> Master Card
							<input type="checkbox" name="paypal_api_credit_cards[]" size="35" value="Amex" <?php echo in_array("Amex", $paypal_api_credit_cards) ? $checked : ''; ?> /> Amex
							<input type="checkbox" name="paypal_api_credit_cards[]" size="35" value="Discover" <?php echo in_array("Discover", $paypal_api_credit_cards) ? $checked : ''; ?> /> Discover
						</li>
						<li>
							<label for="paypal_pro_use_sandbox">
								<?php _e('Use PayPal Payments Pro in Sandbox Mode', 'event_espresso'); ?>
							</label>
							<input name="paypal_pro_use_sandbox" type="checkbox" value="1" <?php echo $paypal_pro_settings['paypal_pro_use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('(Make sure you enter the sandbox credentials above.)', 'event_espresso'); ?>
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($paypal_pro_settings['force_ssl_return']) && $paypal_pro_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $paypal_pro_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $paypal_pro_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $paypal_pro_settings['header']; ?>">
						</li>
					</ul>
				</td>
			</tr>
		</table>
		<?php 
		if (espresso_check_ssl() == FALSE){
			espresso_ssl_required_gateway_message();
		}
		?>
		<p>
			<input type="hidden" name="update_paypal_pro" value="update_paypal_pro">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update PayPal Payments Pro Settings', 'event_espresso') ?>" id="save_paypal_settings" />
		</p>
	</form>
	<div id="paypal_pro_sandbox_info" style="display:none">
		<h2><?php _e('PayPal Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the PayPal Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all PayPal variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The PayPal Sandbox is a testing environment that is a duplicate of the live PayPal site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live PayPal environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('PayPal Currency', 'event_espresso'); ?></h2>
		<p><?php _e('PayPal uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that PayPal (currently) supports.', 'event_espresso'); ?> </p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_paypal_pro_payment_settings');
