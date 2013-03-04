<?php
function event_espresso_luottokunta_payment_settings() {
	global $active_gateways;
	if (isset($_POST['update_luottokunta'])) {
		$luottokunta_settings['luottokunta_id'] = $_POST['luottokunta_id'];
		$luottokunta_settings['luottokunta_mac_key'] = $_POST['luottokunta_mac_key'];
		
		$luottokunta_settings['payment_page_language'] = $_POST['payment_page_language'];
		//$luottokunta_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$luottokunta_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$luottokunta_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$luottokunta_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_luottokunta_settings', $luottokunta_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Luottokunta settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$luottokunta_settings = get_option('event_espresso_luottokunta_settings');
	if (empty($luottokunta_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/luottokunta/luottokunta.jpg")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_URL . "/luottokunta/luottokunta.jpg";
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL;
		}
		$luottokunta_settings['luottokunta_id'] = '';
		$luottokunta_settings['luottokunta_mac_key']='';
		$luottokunta_settings['payment_page_language'] = '978';
		//$luottokunta_settings['use_sandbox'] = false;
		$luottokunta_settings['bypass_payment_page'] = 'N';
		$luottokunta_settings['force_ssl_return'] = false;
		$luottokunta_settings['button_url'] = $button_url;
		if (add_option('event_espresso_luottokunta_settings', $luottokunta_settings, '', 'no') == false) {
			update_option('event_espresso_luottokunta_settings', $luottokunta_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_luottokunta'])
					&& (!empty($_REQUEST['activate_luottokunta'])
					|| array_key_exists('luottokunta', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Luottokunta Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_luottokunta'])) {
						$active_gateways['luottokunta'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_luottokunta'])) {
						unset($active_gateways['luottokunta']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('luottokunta', $active_gateways)) {
						echo '<li id="deactivate_luottokunta" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_luottokunta=true\';" class="red_alert pointer"><strong>' . __('Deactivate Luottokunta IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_luottokunta_settings();
					} else {
						echo '<li id="activate_luottokunta" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_luottokunta=true\';" class="green_alert pointer"><strong>' . __('Activate Luottokunta IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Luottokunta Settings Form
function event_espresso_display_luottokunta_settings() {
	$luottokunta_settings = get_option('event_espresso_luottokunta_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="luottokunta_id">
								<?php _e('Luottokunta ID', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=store_key_id"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="luottokunta_id" size="35" value="<?php echo $luottokunta_settings['luottokunta_id']; ?>">
							<br />
							<?php _e('Eg, NEWSETUPjWbtSQMxaXr400243. NOT the same as your StoreID', 'event_espresso'); ?>
						</li>
						<li>
							<label for="luottokunta_mac_key">
								<?php _e('Secret MAC key', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=store_key_id"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="luottokunta_mac_key" size="35" value="<?php echo $luottokunta_settings['luottokunta_mac_key']; ?>">
							<br />
							<?php _e('A secret key used to identify your client', 'event_espresso'); ?>
						</li>
						<li>
							<label for="payment_page_language">
								<?php _e('Luottokunta Payment Page Language', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
								<?php $languages = array(
									array('id'=>'EN','text'=>__('English','event_espresso')),
									array('id'=>'FI','text'=>__('Finnish','event_espresso')),
									array('id'=>'SE','text'=>__('Swedish','event_espresso'))
								);
								echo select_input('payment_page_language', $languages, $luottokunta_settings['payment_page_language']);
								?>
								
						</li>
						<!--<li>
							<label for="use_sandbox">
								<?php _e('Use the Development Site', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=paypal_sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $luottokunta_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
						</li>-->
					</ul></td>
				<td valign="top"><ul><li>
						<label for="bypass_payment_page">
							<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
						</label>
						<?php
						$values = array(
								array('id' => 'N', 'text' => __('No', 'event_espresso')),
								array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
						echo select_input('bypass_payment_page', $values, $luottokunta_settings['bypass_payment_page']);
						?>
						</li>
						
						
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $luottokunta_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
						
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=luottokunta_button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="button_url" size="34" value="<?php echo $luottokunta_settings['button_url']; ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li><li>
							<label><?php _e('Current Button Image:', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $luottokunta_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		</p>
			<input type="hidden" name="update_luottokunta" value="update_luottokunta">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Luottokunta Settings', 'event_espresso') ?>" id="save_luottokunta_settings" />
		</p>
	</form>
	<div id="store_key_id" style="display:none">
		<h2><?php _e('Luottokunta Store Key', 'event_espresso'); ?></h2>
		<p><?php _e("To find and setup your Luottokunta Store Key,",'event_espresso'); ?></p>
		<ol>
			<li>
				<?php echo sprintf(__("login to %s luottokunta.com %s", 'event_espresso'),"<a href='https://secure.luottokunta.com/'>","</a>"); ?>
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
		<h2><?php _e("Why is there a Canadian and US Luottokunta Store Key?",'event_espresso');?></h2>
		<p><?php _e('If you will be accepting payments only in USD, you only need to enter a "US Luottokunta Store Key". Conversely, 
			if you are only accepting payments in CAD, you need only enter a "Canadian Luottokunta Store Key".','event_espresso');?></p>
		<p><?php _e('If, however, some events will be accepting US dollars and others will be accepting Canadian dollars, you will need 2
			Luottokunta accounts: one accepting CAD and the other for USD. Enter the store keys for each into Event Espresso in the appropriate fields ("Canadian Luottokunta Store Key" and "US Luottokunta Store Key").')?></p>
		<p><?php _e("Then, select a default currency. You may then specify an event as using the other currency by adding an 'Event Meta' called 'event_currency', and give it a value of either 'USD' or 'CAD'.",'event_espresso');?></p>
		<p><?php _e("When customers go to pay for an event, if the currency is in USD, your US Store Key will be used. If the currency for the event is CAD, the Canadian Store Key will be used.",'event_espresso');?>
		
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('Luottokunta Currency', 'event_espresso'); ?></h2>
		<p><?php _e('Luottokunta uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that Luottokunta (currently) supports.', 'event_espresso'); ?> </p>
	</div>
	<div id="no_shipping" style="display:none">
		<h2><?php _e('Shipping Address', 'event_espresso'); ?></h2>
		<p><?php _e('By default, Luottokunta will display shipping address information on the Luottokunta payment screen. If you plan on shipping items to a registrant (shirts, invoices, etc) then use this option. Otherwise it should not be used, as it will require a shipping address when someone registers for an event.', 'event_espresso'); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_luottokunta_payment_settings');
