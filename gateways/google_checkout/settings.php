<?php

function event_espresso_google_checkout_payment_settings() {
	global $active_gateways;
	if (isset($_POST['update_google_checkout'])) {
		$google_checkout_settings['google_checkout_id'] = $_POST['google_checkout_id'];
		$google_checkout_settings['google_checkout_key'] = $_POST['google_checkout_key'];
		$google_checkout_settings['image_url'] = $_POST['image_url'];
		$google_checkout_settings['currency_format'] = $_POST['currency_format'];
		$google_checkout_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		//$google_checkout_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$google_checkout_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$google_checkout_settings['no_shipping'] = $_POST['no_shipping'];
		$google_checkout_settings['button_url'] = $_POST['button_url'];
		$google_checkout_settings['default_payment_status']=$_POST['default_payment_status'];
		update_option('event_espresso_google_checkout_settings', $google_checkout_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Google Checkout settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$google_checkout_settings = get_option('event_espresso_google_checkout_settings');
	if (empty($google_checkout_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/google_checkout/btn_stdCheckout2.gif")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_URL . "/google_checkout/btn_stdCheckout2.gif";
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/google_checkout/btn_stdCheckout2.gif";
		}
		$google_checkout_settings['google_checkout_id'] = '';
		$google_checkout_settings['google_checkout_key'] = '';
		$google_checkout_settings['image_url'] = '';
		$google_checkout_settings['currency_format'] = 'USD';
		$google_checkout_settings['use_sandbox'] = false;
		//note: how this is displayed will be internationalized, but this value is used internally
		$google_checkout_settings['default_payment_status'] = 'Pending';
		$google_checkout_settings['force_ssl_return'] = false;
		$google_checkout_settings['button_url'] = $button_url;
		if (add_option('event_espresso_google_checkout_settings', $google_checkout_settings, '', 'no') == false) {
			update_option('event_espresso_google_checkout_settings', $google_checkout_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_google_checkout'])
					&& (!empty($_REQUEST['activate_google_checkout'])
					|| array_key_exists('google_checkout', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Google Wallet (Checkout) Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_google_checkout'])) {
						$active_gateways['google_checkout'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_google_checkout'])) {
						unset($active_gateways['google_checkout']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('google_checkout', $active_gateways)) {
						echo '<li id="deactivate_google_checkout" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_google_checkout=true\';" class="red_alert pointer"><strong>' . __('Deactivate Google Wallet IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_google_checkout_settings();
					} else {
						echo '<li id="activate_google_checkout" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_google_checkout=true\';" class="green_alert pointer"><strong>' . __('Activate Google Wallet IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Google Checkout Settings Form
function event_espresso_display_google_checkout_settings() {
	$google_checkout_settings = get_option('event_espresso_google_checkout_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<a class='thickbox' href='#TB_inline?height=300&width=400&inlineId=google_wallet_settings'><?php _e("IMPORTANT: Please read these instructions on configuring Google Wallet","event_espresso")?></a>
						</li>
						<li>
							<label for="google_checkout_id">
								<?php _e('Google Merchant ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="google_checkout_id" size="35" value="<?php echo $google_checkout_settings['google_checkout_id']; ?>">
							<br />
						</li>
						<li>
							<label for="google_checkout_key">
								<?php _e('Google Merchant Key', 'event_espresso'); ?>
							</label>
							<input type="text" name="google_checkout_key" size="35" value="<?php echo $google_checkout_settings['google_checkout_key']; ?>">
							<br />
						</li>
						<li>
							
							<label for="currency_format">
								<?php _e('Select the Currency for Your Country', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<select name="currency_format">
								<option value="<?php echo $google_checkout_settings['currency_format']; ?>"><?php echo $google_checkout_settings['currency_format']; ?></option>
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
					</ul></td>
				<td valign="top"><ul>
						<li>
							<label for="use_sandbox">
								<?php _e('Use the Debugging Feature and the', 'event_espresso'); ?> <?php _e('Google Wallet Sandbox', 'event_espresso'); ?>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $google_checkout_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($google_checkout_settings['force_ssl_return']) && $google_checkout_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $google_checkout_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						
						<li>
							<label for="default_payment_status">
								<?php _e("Default Payment Status on Receipt of 'New Order Notification'", 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=default_payment_status"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a><br/>
								<?php $defaultPaymentStatuses=array(
									array('id'=>'Pending','text'=>__("Pending","event_espresso")),
									array('id'=>'Complete','text'=>__("Complete","event_espresso")));?>
								
								<?php echo select_input('default_payment_status', $defaultPaymentStatuses, $google_checkout_settings['default_payment_status']);?>
							</label>
						<li>
						</li>
						
					</ul></td>
			</tr>
		</table>
		
		<p>
			<input type="hidden" name="update_google_checkout" value="update_google_checkout">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Google Checkout Settings', 'event_espresso') ?>" id="save_google_checkout_settings" />
		</p>
	</form>
	<div id="google_checkout_sandbox_info" style="display:none">
		<h2><?php _e('Google Checkout Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the Google Checkout Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all Google Checkout variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The Google Checkout Sandbox is a testing environment that is a duplicate of the live Google Checkout site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live Google Checkout environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>

	<div id="currency_info" style="display:none">
		<h2><?php _e('Google Wallet Currency', 'event_espresso'); ?></h2>
		<p><?php _e('Google Wallet uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that Google Wallet (currently) supports.', 'event_espresso'); ?> </p>
	</div>
<div id='google_wallet_settings' style='display:none'>
	<h2><?php _e("Google Wallet Settings Instructions","event_espresso");?></h2>
	<p>
		<?php _e("Setup your Google Wallet merchant acccount <a href='http://checkout.google.com/sell'>here</a>.")?>
	</p>
	<p>
		<?php _e("You will find your 'Merchant ID' and 'Merchant Key' in Google Wallet's management area, 
			by clicking \"Settings\", then \"Integration\". You must enter them into Event Espresso's 'Google Wallet (Checkout) payment settings.","event_espresso");?>
	</p>
	<p>
		<?php printf(__("On the Google Wallet (Checkout) Settings Integration page, set the 'API callback URL' in Google Checkout to the following:%s (a page on your website with an added GET query parameter of 'type=google_checkout'). Leave the \"Callback Contents\" as \"Notification Serial Number\".","event_espresso"),home_url()."?type=google_checkout");?>
		
	</p>
	<p>
		<?php _e("Set the API version to 2.5, and check the box under \"Notiication Filtering\" stating \"   	I am integrating using the Order Processing Tutorial documentation and will receive and handle only the following notifications: new-order-notification, authorization-amount-notification, order-state-change-notification.\".","event_espresso")?>
	</p>
	<p>
		<?php _e("Lastly, don't forget to save your settings in both Google Wallet (Checkout) AND Event Espresso's payment settings page.","event_espresso")?>
	</p>
</div>
<div id='default_payment_status' style='display:none'>
	<h2><?php _e("Default Payment Status on 'New Order Notificaiton'","event_espresso");?></h2>
	<p>
		<?php _e(sprintf("First, some background information: When a registrant completes the payment process in Google Wallet (Checkout), we receive a 'New Order Notification.' This notification
			does not mean the payment has yet been approved. Google Wallet (Checkout) will send us another notification (a 'Authorization Amount Notification') (usually within 15 minutes), 
			indicating the payment has been approved. See <a target='_blank' href='%s'>this google documentation</a> for more info.","https://developers.google.com/checkout/developer/Google_Checkout_Custom_Processing_How_To#store_db"),"event_espresso");?>
		
	</p>
	<p>
		<?php _e("Before the receipt of that second notification (indicating we can now charge the purchase to their account), you may decide
			to either mark their transaction as 'pending' or 'complete' in Event Espresso. 'Pending' is more technically correct (because Google has not yet sent the 
			'Authorization Amount Notification', and thus the payment could still be declined), but may confuse your customers into attempting to pay again. 'Complete'
			will probably produce more happier customers, except if their payment is rejected (in which you'll need to explain this all to them).","event_espresso")?>
	</p>
</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_google_checkout_payment_settings');
