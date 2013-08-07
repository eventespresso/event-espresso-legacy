<?php

function event_espresso_securepay_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_securepay'])) {
		$securepay_settings['merchant_id'] = $_POST['merchant_id'];
		$securepay_settings['mechant_password'] = $_POST['mechant_password'];
		$securepay_settings['currency_format'] = $_POST['currency_format'];
		$securepay_settings['securepay_use_sandbox'] = empty($_POST['securepay_use_sandbox']) ? false : true;
		$securepay_settings['header'] = $_POST['header'];
		$securepay_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$securepay_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('event_espresso_securepay_settings', $securepay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('SecurePay saved.', 'event_espresso') . '</strong></p></div>';
	}
	$securepay_settings = get_option('event_espresso_securepay_settings');
	if (empty($securepay_settings)) {
		$securepay_settings['merchant_id'] = '';
		$securepay_settings['mechant_password'] = '';
		$securepay_settings['currency_format'] = 'AUD';
		$securepay_settings['securepay_use_sandbox'] = false;
		$securepay_settings['header'] = 'Payment Transactions by SecurePay Payments Pro';
		$securepay_settings['force_ssl_return'] = false;
		$securepay_settings['display_header'] = false;
		if (add_option('event_espresso_securepay_settings', $securepay_settings, '', 'no') == false) {
			update_option('event_espresso_securepay_settings', $securepay_settings);
		}
	}

	if ( ! isset( $securepay_settings['button_url'] ) || ! file_exists( $securepay_settings['button_url'] )) {
		$securepay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_securepay'])
					&& (!empty($_REQUEST['activate_securepay'])
					|| array_key_exists('securepay', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('SecurePay', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_securepay'])) {
						$active_gateways['securepay'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_securepay'])) {
						unset($active_gateways['securepay']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('securepay', $active_gateways)) {
						echo '<li id="deactivate_securepay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_securepay=true\';" class="red_alert pointer"><strong>' . __('Deactivate SecurePay Payments Pro?', 'event_espresso') . '</strong></li>';
						event_espresso_display_securepay_settings();
					} else {
						echo '<li id="activate_securepay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_securepay=true\';" class="green_alert pointer"><strong>' . __('Activate SecurePay Payments Pro?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//SecurePay Settings Form
function event_espresso_display_securepay_settings() {
	$securepay_settings = get_option('event_espresso_securepay_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="merchant_id">
								<?php _e('Merchant ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="merchant_id" size="35" value="<?php echo $securepay_settings['merchant_id']; ?>">
						</li>
						<li>
							<label for="mechant_password">
								<?php _e('Merchant Key', 'event_espresso'); ?>
							</label>
							<input type="text" name="mechant_password" size="35" value="<?php echo $securepay_settings['mechant_password']; ?>">
						</li>
						<li>
							<label for="currency_format">
								<?php _e('Select the Currency for Your Country', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php 
							
 echo select_input('currency_format', array(
	 array('id'=>'AUD','text'=>__('Australian Dollars (A $)', 'event_espresso')),
	 array('id'=>'USD','text'=>  __("U.S. Dollars ($)", "event_espresso")),
	array('id'=>'GBP','text'=>  __("Pounds Sterling (&pound;)", "event_espresso")),
	array('id'=>'CAD','text'=>  __("Canadian Dollars (C $)", "event_espresso")),
	 array('id'=>'CHF','text'=>  __("Swiss Franc", "event_espresso")),
	 array('id'=>'EUR','text'=>  __("Euros (&#8364;)", "event_espresso")),
	 array('id'=>'HKD','text'=>  __("Hong Kong Dollar ($)", "event_espresso")),
	 array('id'=>'JPY','text'=>  __("Yen (&yen;)", "event_espresso")),
	 array('id'=>'NZD','text'=>  __("New Zealand Dollar ($)", "event_espresso")),
	 array('id'=>'SGD','text'=>  __("Singapore Dollar ($)", "event_espresso"))), $securepay_settings['currency_format']); ?>
								
							 </li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						
						<li>
							<label for="securepay_use_sandbox">
								<?php _e('Use SecurePay Payments Pro in Sandbox Mode', 'event_espresso'); ?>
							</label>
							<input name="securepay_use_sandbox" type="checkbox" value="1" <?php echo $securepay_settings['securepay_use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('(Make sure you enter the sandbox credentials above.)', 'event_espresso'); ?>
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($securepay_settings['force_ssl_return']) && $securepay_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $securepay_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $securepay_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $securepay_settings['header']; ?>">
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
			<input type="hidden" name="update_securepay" value="update_securepay">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update SecurePay', 'event_espresso') ?>" id="save_securepay_settings" />
		</p>
	</form>
	<div id="securepay_sandbox_info" style="display:none">
		<h2><?php _e('SecurePay Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the SecurePay Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all SecurePay variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The SecurePay Sandbox is a testing environment that is a duplicate of the live SecurePay site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live SecurePay environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('SecurePay Currency', 'event_espresso'); ?></h2>
		<p><?php _e('SecurePay uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that SecurePay (currently) supports.', 'event_espresso'); ?> </p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_securepay_payment_settings');
