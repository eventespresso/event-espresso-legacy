<?php
function event_espresso_luottokunta_payment_settings() {
	global $active_gateways;
	if (isset($_POST['update_luottokunta'])) {
		$luottokunta_settings['luottokunta_id'] = $_POST['luottokunta_id'];
		$luottokunta_settings['luottokunta_mac_key'] = $_POST['luottokunta_mac_key'];
		$luottokunta_settings['luottokunta_uses_mac_key'] = $_POST['luottokunta_uses_mac_key'];
		$luottokunta_settings['luottokunta_payment_page_language'] = $_POST['luottokunta_payment_page_language'];
		//$luottokunta_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$luottokunta_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$luottokunta_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$luottokunta_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_luottokunta_settings', $luottokunta_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Luottokunta settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$luottokunta_settings = get_option('event_espresso_luottokunta_settings');
	if (empty($luottokunta_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "luottokunta/luottokunta.jpg")) {
			$luottokunta_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "luottokunta/luottokunta.jpg";
		} else {
			$luottokunta_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/luottokunta/luottokunta.jpg";
		}
		$luottokunta_settings['luottokunta_id'] = '';
		$luottokunta_settings['luottokunta_uses_mac_key'] = 'N';
		$luottokunta_settings['luottokunta_mac_key']='';
		$luottokunta_settings['luottokunta_payment_page_language'] = '978';
		//$luottokunta_settings['use_sandbox'] = false;
		$luottokunta_settings['bypass_payment_page'] = 'N';
		$luottokunta_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_luottokunta_settings', $luottokunta_settings, '', 'no') == false) {
			update_option('event_espresso_luottokunta_settings', $luottokunta_settings);
		}
	}

	if ( ! isset( $luottokunta_settings['button_url'] ) || ! file_exists( $luottokunta_settings['button_url'] )) {
		$luottokunta_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
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
								<?php _e('Luottokunta ID / Merchant Number', 'event_espresso'); ?>
							</label>
							<input type="text" name="luottokunta_id" size="35" value="<?php echo $luottokunta_settings['luottokunta_id']; ?>">
							<br />
						</li>
						<li>
							<label for='luottokunta_uses_mac_key'>
								<?php _e("Perform MAC security Check",'event_espresso')?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=mac_security_check"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php $use_mac_options = array(
								array('id'=>'Y','text'=>__('Yes','event_espresso')),
								array('id'=>'N','text'=>__('No','event_espresso')));
							
								echo select_input('luottokunta_uses_mac_key', $use_mac_options, $luottokunta_settings['luottokunta_uses_mac_key']);?>
						</li>
						<li>
							<label for="luottokunta_mac_key">
								<?php _e('Secret MAC key', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=mac_security_check"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="luottokunta_mac_key" size="35" value="<?php echo $luottokunta_settings['luottokunta_mac_key']; ?>">
							<br />
							<?php _e('A secret key used to identify your client', 'event_espresso'); ?>
						</li>
						<li>
							<label for="luottokunta_payment_page_language">
								<?php _e('Luottokunta Payment Page Language', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=payment_page_language_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
								<?php $languages = array(
									array('id'=>'EN','text'=>__('English','event_espresso')),
									array('id'=>'FI','text'=>__('Finnish','event_espresso')),
									array('id'=>'SE','text'=>__('Swedish','event_espresso'))
								);
								echo select_input('luottokunta_payment_page_language', $languages, $luottokunta_settings['luottokunta_payment_page_language']);
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
						
						<?php if (espresso_check_ssl() == TRUE || ( isset($luottokunta_settings['force_ssl_return']) && $luottokunta_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $luottokunta_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=luottokunta_button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $luottokunta_settings['button_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li><li>
							<label><?php _e('Current Button Image:', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $luottokunta_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_luottokunta" value="update_luottokunta">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Luottokunta Settings', 'event_espresso') ?>" id="save_luottokunta_settings" />
		</p>
	</form>
	<div id="mac_security_check" style="display:none">
		<h2><?php _e('Luottokunta MAC Security Check', 'event_espresso'); ?></h2>
		<p><?php _e("Using MAC calculation (MAC = Message Authentication Code) improves the security of the card payments in online store. Use of the MAC calculation is mandatory and the merchant must implement the MAC calculation in two phases in the HTML form interface. If the merchant does not use MAC calculation, the merchant will be responsible for any security risks and damages experienced by Luottokunta.",'event_espresso'); ?></p>
		<p> <?php _e('Luottokunta sends the merchant one secret key for the MAC calculation, enclosed in the service ID codes letter for Luottokunta ePayment Service, and this key is used for actual MAC calculation.','event_espresso')?></p>
		<p> <?php _e("To Activate the Mac Security Check:",'event_espresso');?></p>
		<ol>
			<li>
				<?php _e("Log in to Transaction management section of the web interface of Luottokunta ePayment Service, using a merchant admin user or admin user ID.",'event_espresso')?>
			</li>
			<li>
				<?php _e('Go to the page "Merchant settings".','event_espresso');?>
			</li>
			<li>
				<?php _e('Enable the options "Add MAC check to HTML form" and "Add MAC check to Success_Url" (by marking them as checked).','event_espresso');?>
			</li>
			<li>
				<?php _e('Save your changes by clicking the button "Update"','event_espresso');?>
			</li>
			<li>
				<?php _e('Lastly, from within Event Espresso\'s payments page, set "Perform MAC security Check" to "Yes" and click "Update Luottokunta Settings"','event_espresso')?>
			</li>
		</ol> 
		</p>
	</div>
	<div id="payment_page_language_info" style="display:none">
		<h2><?php _e('Luottokunta Payment Page Language', 'event_espresso'); ?></h2>
		<p><?php _e('During the payment process with Luottokunta, your website clients will be taken to Luottokunta\'s secure payment page. This setting selects the language this page will be in.', 'event_espresso'); ?> </p>
	</div>
	<div id="no_shipping" style="display:none">
		<h2><?php _e('Shipping Address', 'event_espresso'); ?></h2>
		<p><?php _e('By default, Luottokunta will display shipping address information on the Luottokunta payment screen. If you plan on shipping items to a registrant (shirts, invoices, etc) then use this option. Otherwise it should not be used, as it will require a shipping address when someone registers for an event.', 'event_espresso'); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_luottokunta_payment_settings');
