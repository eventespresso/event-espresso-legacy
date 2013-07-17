<?php
function event_espresso_anz_payment_settings() {
	global $active_gateways;
	if (isset($_POST['update_anz'])) {
		$anz_settings['anz_id'] = $_POST['anz_id'];
		$anz_settings['anz_access_code'] = $_POST['anz_access_code'];
		$anz_settings['anz_secure_secret'] = $_POST['anz_secure_secret'];
		$anz_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$anz_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$anz_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_anz_settings', $anz_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('ANZ settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$anz_settings = get_option('event_espresso_anz_settings');
	if (empty($anz_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/anz/anz_egate-logo.png")) {
			$anz_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/anz/anz_egate-logo.png";
		} else {
			$anz_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/anz/anz_egate-logo.png";
		}
		$anz_settings['anz_id'] = '';
		$anz_settings['anz_access_code'] ='';
		$anz_settings['anz_secure_secret'] = '';
		$anz_settings['bypass_payment_page'] = 'N';
		$anz_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_anz_settings', $anz_settings, '', 'no') == false) {
			update_option('event_espresso_anz_settings', $anz_settings);
		}
	}

	if ( basename( $anz_settings['button_url'] ) == 'ANZLogo_eGate.gif' || ! file_exists( $anz_settings['button_url'] )) {
		$anz_settings['button_url']  = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/anz/anz_egate-logo.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_anz'])
					&& (!empty($_REQUEST['activate_anz'])
					|| array_key_exists('anz', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('ANZ Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_anz'])) {
						$active_gateways['anz'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_anz'])) {
						unset($active_gateways['anz']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('anz', $active_gateways)) {
						echo '<li id="deactivate_anz" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_anz=true\';" class="red_alert pointer"><strong>' . __('Deactivate ANZ IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_anz_settings();
					} else {
						echo '<li id="activate_anz" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_anz=true\';" class="green_alert pointer"><strong>' . __('Activate ANZ IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//ANZ Settings Form
function event_espresso_display_anz_settings() {
	$anz_settings = get_option('event_espresso_anz_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="anz_id">
								<?php _e('Merchant ID', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=anz_creds"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="anz_id" size="35" value="<?php echo $anz_settings['anz_id']; ?>">
							<br />
							<?php _e('Eg, 1234567', 'event_espresso'); ?>
						</li>
						<li>
							<label for="anz_access_code">
								<?php _e('Access Code', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=anz_creds"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="anz_access_code" size="35" value="<?php echo $anz_settings['anz_access_code']; ?>">
							<br />
							<?php _e('Eg, 1234567', 'event_espresso'); ?>
						</li>	
						<li>
							<label for="anz_secure_secret">
								<?php _e('Secure Secret', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=anz_creds"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="anz_secure_secret" size="35" value="<?php echo $anz_settings['anz_secure_secret']; ?>">
							<br />
							<?php _e('Eg, 1234567', 'event_espresso'); ?>
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
						echo select_input('bypass_payment_page', $values, $anz_settings['bypass_payment_page']);
						?>
						</li>
						
						
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $anz_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
						
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=anz_button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $anz_settings['button_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li><li>
							<label><?php _e('Current Button Image:', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $anz_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		</p>
			<input type="hidden" name="update_anz" value="update_anz">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update ANZ Settings', 'event_espresso') ?>" id="save_anz_settings" />
		</p>
	</form>
<div id='anz_creds' style='display:none'>
	<h2><?php _e("ANZ eGate Credentials",'event_espresso')?></h2>
	<p><?php _e("The Merchant ID, Access Code, and Secure Secrets are provided from ANZ upon registration with them. Note: if you want to test your account, use the test credentials (the test Merchant ID always starts with the characters 'TEST'. e.g., if your normal account has a Merchant ID of 'ANZKANGAROO', your test merchant ID should be 'TESTANZKANGAROO'.)",'event_espresso')?></p>
	<p><?php _e("Note, ANZ will provide you wish two Secure Secrets. You may use either one.",'event_espresso');?></p>
		
</div>
	<div id="anz_button_image" style="display:none">
		<h2><?php _e('Button Image URL', 'event_espresso'); ?></h2>
		<p><?php echo sprintf(__('You may specify the URL of any image you want to be displayed to users when selecting their payment gateway.
			By default, the ANZ icon is selected. We also have a merchant accounts image available at %s', 'event_espresso'), EVENT_ESPRESSO_GATEWAY_URL . "anz/ANZLogo_eGate.gif"); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_anz_payment_settings');
