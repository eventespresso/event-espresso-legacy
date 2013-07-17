<?php

function event_espresso_2checkout_payment_settings() {
	global $espresso_premium, $active_gateways;
	;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_2checkout'])) {
		$twocheckout_settings['2checkout_id'] = $_POST['2checkout_id'];
		$twocheckout_settings['2checkout_username'] = $_POST['2checkout_username'];
		$twocheckout_settings['currency_format'] = $_POST['currency_format'];
		$twocheckout_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? 0 : $_POST['use_sandbox'];
		$twocheckout_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$twocheckout_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$twocheckout_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_2checkout_settings', $twocheckout_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('2Checkout settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$twocheckout_settings = get_option('event_espresso_2checkout_settings');
	if (empty($twocheckout_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/2checkout/logo.png")) {
			$twocheckout_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/2checkout/logo.png";
		} else {
			$twocheckout_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/2checkout/logo.png";
		}
		$twocheckout_settings['2checkout_id'] = '';
		$twocheckout_settings['2checkout_username'] = '';
		$twocheckout_settings['currency_format'] = 'USD';
		$twocheckout_settings['use_sandbox'] = 'N';
		$twocheckout_settings['bypass_payment_page'] = 'N';
		$twocheckout_settings['force_ssl_return'] = false;
		$twocheckout_settings['button_url'] = $twocheckout_settings['button_url'];
		if (add_option('event_espresso_2checkout_settings', $twocheckout_settings, '', 'no') == false) {
			update_option('event_espresso_2checkout_settings', $twocheckout_settings);
		}
	}

	if ( ! isset( $twocheckout_settings['button_url'] ) || ! file_exists( $twocheckout_settings['button_url'] )) {
		$twocheckout_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/2checkout/logo.png";
	}
	
	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_2checkout'])
					&& (!empty($_REQUEST['activate_2checkout'])
					|| array_key_exists('2checkout', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div id="2copostbox" class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
	<?php _e('2Checkout Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_2checkout'])) {
						$active_gateways['2checkout'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_2checkout'])) {
						unset($active_gateways['2checkout']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('2checkout', $active_gateways)) {
						echo '<li id="deactivate_2co" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_2checkout=true\';" class="red_alert pointer"><strong>' . __('Deactivate 2Checkout IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_2checkout_settings();
					} else {
						echo '<li id="activate_2co" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_2checkout=true\';" class="green_alert pointer"><strong>' . __('Activate 2Checkout IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
<?php

//2Checkout Settings Form
function event_espresso_display_2checkout_settings() {
	$twocheckout_settings = get_option('event_espresso_2checkout_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="2checkout_id">
	<?php _e('2Checkout ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="2checkout_id" size="35" value="<?php echo $twocheckout_settings['2checkout_id']; ?>"><br />
	<?php _e('(Typically 87654321)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="2checkout_username">
	<?php _e('2Checkout Username', 'event_espresso'); ?>
							</label>
							<input type="text" name="2checkout_username" size="35" value="<?php echo $twocheckout_settings['2checkout_username']; ?>"><br />
	<?php _e('(Typically TestAccount)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="currency_format">
	<?php _e('Country Currency', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<select name="currency_format">
								<option value="<?php echo $twocheckout_settings['currency_format']; ?>"><?php echo $twocheckout_settings['currency_format']; ?>
								</option>
								<option value="ARS">
	<?php _e('Argentina Peso', 'event_espresso'); ?>
								</option>
								<option value="AUD">
	<?php _e('Australian Dollars (A $)', 'event_espresso'); ?>
								</option>
								<option value="BRL">
	<?php _e('Brazilian Real', 'event_espresso'); ?>
								</option>
								<option value="GBP">
	<?php _e('British Pound', 'event_espresso'); ?>
								</option>
								<option value="CAD">
	<?php _e('Canadian Dollar', 'event_espresso'); ?>
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
								<option value="INR">
	<?php _e('Indian Rupee (Rs.)', 'event_espresso'); ?>
								</option>
								<option value="ILS">
	<?php _e('Israeli New Shekel', 'event_espresso'); ?>
								</option>
								<option value="JPY">
	<?php _e('Yen (&yen;)', 'event_espresso'); ?>
								</option>
								<option value="LTL">
	<?php _e('Lithuanian Litas', 'event_espresso'); ?>
								</option>
								<option value="MYR">
	<?php _e('Malaysian Ringgit', 'event_espresso'); ?>
								</option>
								<option value="MXN">
	<?php _e('Mexican Peso', 'event_espresso'); ?>
								</option>
								<option value="NZD">
	<?php _e('New Zealand Dollar', 'event_espresso'); ?>
								</option>
								<option value="NOK">
	<?php _e('Norwegian Krone', 'event_espresso'); ?>
								</option>
								<option value="PHP">
	<?php _e('Philippine Peso', 'event_espresso'); ?>
								</option>
								<option value="RON">
	<?php _e('Romanian New Leu', 'event_espresso'); ?>
								</option>
								<option value="RUB">
	<?php _e('Russian Ruble', 'event_espresso'); ?>
								</option>
								<option value="SGD">
	<?php _e('Singapore Dollar', 'event_espresso'); ?>
								</option>
								<option value="ZAR">
	<?php _e('South African Rand', 'event_espresso'); ?>
								</option>
								<option value="SEK">
	<?php _e('Swedish Krona', 'event_espresso'); ?>
								</option>
								<option value="CHF">
	<?php _e('Swiss Franc', 'event_espresso'); ?>
								</option>
								<option value="TRY">
	<?php _e('Turkish Lira', 'event_espresso'); ?>
								</option>
								<option value="USD">
	<?php _e('U.S. Dollar', 'event_espresso'); ?>
								</option>
								<option value="AED">
	<?php _e('United Arab Emirates Dirham ', 'event_espresso'); ?>
								</option>
							</select>
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label for="bypass_payment_page">
							<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $twocheckout_settings['bypass_payment_page']);
							?>
						
						</li>
						<li>
							<label for="use_sandbox">
	<?php _e('Turn on Debugging Using the', 'event_espresso'); ?> <a href="https://www.2checkout.com/va/signup/create_activation" target="_blank"><?php _e('2Checkout Sandbox', 'event_espresso'); ?></a> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input id ="sandbox_checkbox_2co" name="use_sandbox" type="checkbox" value="1" <?php echo $twocheckout_settings['use_sandbox'] == "1" ? 'checked="checked"' : '' ?> />
							
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($twocheckout_settings['force_ssl_return']) && $twocheckout_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
	<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $twocheckout_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label for="button_url">
	<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $twocheckout_settings['button_url']; ?>" />
							<a  class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							
	<?php echo '<img src="' . $twocheckout_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_2checkout" value="update_2checkout">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update 2Checkout Settings', 'event_espresso') ?>" id="save_2checkout_settings" />
		</p>
	</form>
	<div id="sandbox_info" style="display:none">
		<h2><?php _e('2Checkout Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the 2Checkout Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all 2Checkout variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The 2Checkout Sandbox is a testing environment that is a duplicate of the live 2Checkout site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live 2Checkout environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('2Checkout Currency', 'event_espresso'); ?></h2>
		<p><?php _e('2Checkout uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that 2Checkout (currently) supports.', 'event_espresso'); ?> </p>
	</div>

	
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_2checkout_payment_settings');
