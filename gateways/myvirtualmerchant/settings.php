<?php

function event_espresso_myvirtualmerchant_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_myvirtualmerchant'])) {
		$myvirtualmerchant_settings['ssl_merchant_id'] = $_POST['ssl_merchant_id'];
		$myvirtualmerchant_settings['ssl_user_id'] = $_POST['ssl_user_id'];
		$myvirtualmerchant_settings['ssl_pin'] = $_POST['ssl_pin'];
		$myvirtualmerchant_settings['use_custom_currency'] = empty($_POST['use_custom_currency']) ? false : true;
		$myvirtualmerchant_settings['currency_format'] = $_POST['currency_format'];
		$myvirtualmerchant_settings['myvirtualmerchant_use_sandbox'] = empty($_POST['myvirtualmerchant_use_sandbox']) ? false : true;
		$myvirtualmerchant_settings['header'] = $_POST['header'];
		$myvirtualmerchant_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('event_espresso_myvirtualmerchant_settings', $myvirtualmerchant_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('MyVirtualMerchant settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$myvirtualmerchant_settings = get_option('event_espresso_myvirtualmerchant_settings');
	if (empty($myvirtualmerchant_settings)) {
		$myvirtualmerchant_settings['ssl_merchant_id'] = '';
		$myvirtualmerchant_settings['ssl_user_id'] = '';
		$myvirtualmerchant_settings['ssl_pin'] = '';
		$myvirtualmerchant_settings['use_custom_currency'] = false;
		$myvirtualmerchant_settings['currency_format'] = 'USD';
		$myvirtualmerchant_settings['myvirtualmerchant_use_sandbox'] = false;
		$myvirtualmerchant_settings['header'] = 'Payment Transactions by MyVirtualMerchant';
		$myvirtualmerchant_settings['display_header'] = false;
		if (add_option('event_espresso_myvirtualmerchant_settings', $myvirtualmerchant_settings, '', 'no') == false) {
			update_option('event_espresso_myvirtualmerchant_settings', $myvirtualmerchant_settings);
		}
	}

	if ( ! isset( $myvirtualmerchant_settings['button_url'] ) || ! file_exists( $myvirtualmerchant_settings['button_url'] )) {
		$myvirtualmerchant_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_myvirtualmerchant'])
					&& (!empty($_REQUEST['activate_myvirtualmerchant'])
					|| array_key_exists('myvirtualmerchant', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('MyVirtualMerchant Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_myvirtualmerchant'])) {
						$active_gateways['myvirtualmerchant'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_myvirtualmerchant'])) {
						unset($active_gateways['myvirtualmerchant']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('myvirtualmerchant', $active_gateways)) {
						echo '<li id="deactivate_myvirtualmerchant" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_myvirtualmerchant=true\';" class="red_alert pointer"><strong>' . __('Deactivate MyVirtualMerchant?', 'event_espresso') . '</strong></li>';
						event_espresso_display_myvirtualmerchant_settings();
					} else {
						echo '<li id="activate_myvirtualmerchant" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_myvirtualmerchant=true\';" class="green_alert pointer"><strong>' . __('Activate MyVirtualMerchant?', 'event_espresso') . '</strong></li>';
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
function event_espresso_display_myvirtualmerchant_settings() {
	$myvirtualmerchant_settings = get_option('event_espresso_myvirtualmerchant_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="ssl_merchant_id">
								<?php _e('SSL Merchant ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="ssl_merchant_id" size="35" value="<?php echo $myvirtualmerchant_settings['ssl_merchant_id']; ?>">
						</li>
						<li>
							<label for="ssl_user_id">
								<?php _e('SSL User ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="ssl_user_id" size="35" value="<?php echo $myvirtualmerchant_settings['ssl_user_id']; ?>">
						</li>
						<li>
							<label for="ssl_pin">
								<?php _e('PIN', 'event_espresso'); ?>
							</label>
							<input type="text" name="ssl_pin" size="35" value="<?php echo $myvirtualmerchant_settings['ssl_pin']; ?>">
						</li>
						
						
						
						<li>
							<label for="use_custom_currency">
								<?php _e('I have enabled Multi-Currency in My Virtual Merchant', 'event_espresso'); ?>
							</label>
							<input name="use_custom_currency" type="checkbox" value="1" <?php echo $myvirtualmerchant_settings['use_custom_currency'] ? 'checked="checked"' : '' ?> />
						</li>
						<li>
							<label for="currency_format">
								<?php _e('Select the Currency for Your Country', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=myvirtualmerchant_currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<select name="currency_format">
								<option value="<?php echo $myvirtualmerchant_settings['currency_format']; ?>"><?php echo $myvirtualmerchant_settings['currency_format']; ?></option>
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
							<label for="myvirtualmerchant_use_sandbox">
								<?php _e('Use MyVirtualMerchant in Demo Mode', 'event_espresso'); ?>
							</label>
							<input name="myvirtualmerchant_use_sandbox" type="checkbox" value="1" <?php echo $myvirtualmerchant_settings['myvirtualmerchant_use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('(Make sure you enter the sandbox credentials above.)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $myvirtualmerchant_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $myvirtualmerchant_settings['header']; ?>">
						</li>
						<li><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=myvirtualmerchant_fields"><?php	_e("Want Event Names and Registration IDs to appear in your Virtual Terminal? Read this", 'event_espresso');?><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></li>
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
			<input type="hidden" name="update_myvirtualmerchant" value="update_myvirtualmerchant">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update MyVirtualMerchant Settings', 'event_espresso') ?>" id="save_paypal_settings" />
		</p>
	</form>
	<div id="myvirtualmerchant_sandbox_info" style="display:none">
		<h2><?php _e('My Virtual Merchant Demo Site', 'event_espresso'); ?></h2>
		<p><?php _e('If you request access from My Virtual Merchant, you can have temporary access to the demo site, in order to test your integration between Event Espresso and My Virtual Merchant. If you are using a Demo account, set this switch to use the Demo Site.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The My Virtual Merchant demo site is a testing environment that is a duplicate of the live site, except that no real money changes hands. The Demo Site allows you to test your entire integration before submitting transactions to the live environment.', 'event_espresso'); ?></p>
	</div>
	<div id="myvirtualmerchant_currency_info" style="display:none">
		<h2><?php _e('My Virtual Merchant Currency', 'event_espresso'); ?></h2>
		<p><?php _e('My Virtual Merchant uses 3-character ISO-4217 codes for specifying currencies in fields and variables. The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, you first NEED TO ENABLE MULTI-CURRENCY in your My Virtual Merchant terminal, then select the currency you wish to use in Event espresso.', 'event_espresso'); ?> </p>
	</div>
<div id="myvirtualmerchant_fields" style="display:none">
	<h2><?php		_e("My Virtual Merchant Custom Fields", 'event_espresso');?></h2>
	<p><?php		printf(__("When a payment is made using the My Virtual Merchant gateway, Event Espresso sends the event's name and registration ID along with the payment information to My Virtual Merchant as part of the description, and as seperate custom fields. However, My Virtual Merchant's terminal, by default, doesn't show the description, or record the seperate custom fields. To enable these features, login to %s My Virtual Merchant Terminal%s, and navigate to the %s Payment Fields section%s and do the following...", 'event_espresso'),"<a href='https://www.myvirtualmerchant.com/VirtualMerchant/'>","</a>","<a href='https://docs.google.com/a/eventespresso.com/file/d/0B5P8GXTvZgfMZ1ZZUjE2UmFnQ00/edit?usp=drivesdk'>","</a>");?></p>
	<h3><?php		_e("To Show the Description in your Virtual Terminal...", 'event_espresso');?></h3>
	<p><?php printf(__("From the Payment Fields page, %s click on 'Description'%s, then %s set the Description field to 'Show in Virtual Terminal'%s (and possibly make it a required field so you can search by it your Virtual Terminal). ", "event_espresso"),"<a href='https://docs.google.com/a/eventespresso.com/file/d/0B5P8GXTvZgfMeVFZcmZVZXd3YlU/edit?usp=drivesdk'>","</a>","<a href='https://docs.google.com/a/eventespresso.com/file/d/0B5P8GXTvZgfMMjQ4cFJGRG5Fa1k/edit?usp=drivesdk'>","</a>");	?></p>
	<h3><?php		_e("To Show the Event Name and Registration ID in your Virtual Terminal...", 'event_espresso');?></h3>
	<p><?php		printf(__("From the Payment Fields page, %sadd a new field named 'event_name'%s, and then %sadd a new field named 'registration_id'%s (and have them show in your Virtual Terminal; all other fields are optional).", "event_espresso"),"<a href='https://docs.google.com/a/eventespresso.com/file/d/0B5P8GXTvZgfMS3cxZHhaS0hSV1k/edit?usp=drivesdk'>","</a>","<a href='https://docs.google.com/a/eventespresso.com/file/d/0B5P8GXTvZgfMdzlDUGlxZXY4Slk/edit?usp=drivesdk'>","</a>");?></p>
	<br/>
	<p><?php		printf(__("After you are done both the above steps, all new transactions will %srecord the event name and registration id, and show the description%s", 'event_espresso'),"<a href='https://docs.google.com/a/eventespresso.com/file/d/0B5P8GXTvZgfMckVDaXFiRkl1ME0/edit?usp=drivesdk'>","</a>");?></p>
</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_myvirtualmerchant_payment_settings');
