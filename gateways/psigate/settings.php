<?php
function event_espresso_psigate_payment_settings() {
	global $active_gateways;
	if (isset($_POST['update_psigate'])) {
		$psigate_settings['psigate_id_can'] = $_POST['psigate_id_can'];
		$psigate_settings['psigate_id_us'] = $_POST['psigate_id_us'];
		$psigate_settings['currency_format'] = $_POST['currency_format'];
		$psigate_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$psigate_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$psigate_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$psigate_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_psigate_settings', $psigate_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('PSiGate settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$psigate_settings = get_option('event_espresso_psigate_settings');
	if (empty($psigate_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/psigate/psigate.gif")) {
			$psigate_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/psigate/psigate.gif";
		} else {
			$psigate_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/psigate/psigate.gif";
		}
		$psigate_settings['psigate_id_can'] = '';
		$psigate_settings['psigate_id_us'] = '';
		$psigate_settings['currency_format'] = 'USD';
		$psigate_settings['use_sandbox'] = false;
		$psigate_settings['bypass_payment_page'] = 'N';
		$psigate_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_psigate_settings', $psigate_settings, '', 'no') == false) {
			update_option('event_espresso_psigate_settings', $psigate_settings);
		}
	}

	if ( ! isset( $psigate_settings['button_url'] ) || ! file_exists( $psigate_settings['button_url'] )) {
		$psigate_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_psigate'])
					&& (!empty($_REQUEST['activate_psigate'])
					|| array_key_exists('psigate', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('PSiGate / MerchantAccount.ca Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_psigate'])) {
						$active_gateways['psigate'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_psigate'])) {
						unset($active_gateways['psigate']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('psigate', $active_gateways)) {
						echo '<li id="deactivate_psigate" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_psigate=true\';" class="red_alert pointer"><strong>' . __('Deactivate PSiGate IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_psigate_settings();
					} else {
						echo '<li id="activate_psigate" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_psigate=true\';" class="green_alert pointer"><strong>' . __('Activate PSiGate IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//PSiGate Settings Form
function event_espresso_display_psigate_settings() {
	$psigate_settings = get_option('event_espresso_psigate_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="psigate_id_can">
								<?php _e('Canadian PSiGate Store Key (Merchant ID)', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=store_key_id"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="psigate_id_can" size="35" value="<?php echo $psigate_settings['psigate_id_can']; ?>">
							<br />
							<?php _e('Eg, NEWSETUPjWbtSQMxaXr400243. NOT the same as your StoreID', 'event_espresso'); ?>
						</li>
						<li>
							<label for="psigate_id_us">
								<?php _e('US PSiGate Store Key (Merchant ID)', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=store_key_id"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="psigate_id_us" size="35" value="<?php echo $psigate_settings['psigate_id_us']; ?>">
							<br />
						</li>
						<li>
							<label for="currency_format">
								<?php _e('Select the Currency for Your Country', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<select name="currency_format">
								<option value="<?php echo $psigate_settings['currency_format']; ?>"><?php echo $psigate_settings['currency_format']; ?></option>
								<option value="USD" >
									<?php _e('U.S. Dollars ($)', 'event_espresso'); ?>
								</option>
								<option value="CAD">
									<?php _e('Canadian Dollars (C $)', 'event_espresso'); ?>
								</option>
							</select>
						</li>
						<li>
							<label for="use_sandbox">
								<?php _e('Use the Development Site', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=paypal_sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $psigate_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
						</li>
					</ul></td>
				<td valign="top"><ul><li>
						<label for="bypass_payment_page">
							<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
						</label>
						<?php
						$values = array(
								array('id' => 'N', 'text' => __('No', 'event_espresso')),
								array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
						echo select_input('bypass_payment_page', $values, $psigate_settings['bypass_payment_page']);
						?>
						</li>
						
						<?php if (espresso_check_ssl() == TRUE || ( isset($psigate_settings['force_ssl_return']) && $psigate_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $psigate_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=psigate_button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $psigate_settings['button_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li><li>
							<label><?php _e('Current Button Image:', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $psigate_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_psigate" value="update_psigate">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update PSiGate Settings', 'event_espresso') ?>" id="save_psigate_settings" />
		</p>
	</form>
	<div id="store_key_id" style="display:none">
		<h2><?php _e('PSiGate Store Key', 'event_espresso'); ?></h2>
		<p><?php _e("To find and setup your PsiGate Store Key,",'event_espresso'); ?></p>
		<ol>
			<li>
				<?php echo sprintf(__("login to %s psigate.com %s", 'event_espresso'),"<a href='https://secure.psigate.com/'>","</a>"); ?>
			</li>
			<li>
				<?php _e("Click on 'View Reports' for your Store",'event_espresso');?>
			</li>
			<li>
				<?php _e("Click 'HTML Capture Settings'","event_espresso");?>
			</li>
			<li>
				<?php _e("Set 'Enable HTML Messenger' to 'Yes' and click 'Save'",'event_espresso');?>
			</li>
			<li>
				<?php _e("After enabling the HTML Messenger, and saving your settings, copy the Store Key and paste it into your Event Espresso payment settings",'event_espresso');?>
			</li>
		</ol>
		<h2><?php _e("Why is there a Canadian and US PSiGate Store Key?",'event_espresso');?></h2>
		<p><?php _e('If you will be accepting payments only in USD, you only need to enter a "US PSiGate Store Key". Conversely, 
			if you are only accepting payments in CAD, you need only enter a "Canadian PsiGate Store Key".','event_espresso');?></p>
		<p><?php _e('If, however, some events will be accepting US dollars and others will be accepting Canadian dollars, you will need 2
			PSiGate accounts: one accepting CAD and the other for USD. Enter the store keys for each into Event Espresso in the appropriate fields ("Canadian PSiGate Store Key" and "US PSiGate Store Key").')?></p>
		<p><?php _e("Then, select a default currency. You may then specify an event as using the other currency by adding an 'Event Meta' called 'event_currency', and give it a value of either 'USD' or 'CAD'.",'event_espresso');?></p>
		<p><?php _e("When customers go to pay for an event, if the currency is in USD, your US Store Key will be used. If the currency for the event is CAD, the Canadian Store Key will be used.",'event_espresso');?>
		
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('PSiGate Currency', 'event_espresso'); ?></h2>
		<p><?php _e('PSiGate uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that PSiGate (currently) supports.', 'event_espresso'); ?> </p>
	</div>
	<div id="no_shipping" style="display:none">
		<h2><?php _e('Shipping Address', 'event_espresso'); ?></h2>
		<p><?php _e('By default, PSiGate will display shipping address information on the PSiGate payment screen. If you plan on shipping items to a registrant (shirts, invoices, etc) then use this option. Otherwise it should not be used, as it will require a shipping address when someone registers for an event.', 'event_espresso'); ?></p>
	</div>
	<div id="psigate_button_image" style="display:none">
		<h2><?php _e('Button Image URL', 'event_espresso'); ?></h2>
		<p><?php echo sprintf(__('You may specify the URL of any image you want to be displayed to users when selecting their payment gateway.
			By default, the PSiGate icon is selected. We also have a merchant accounts image available at %s', 'event_espresso'), EVENT_ESPRESSO_PLUGINFULLURL . "gateways/psigate/merchant-accounts-logo.gif"); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_psigate_payment_settings');
