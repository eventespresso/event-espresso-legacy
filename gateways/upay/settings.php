<?php

function event_espresso_upay_payment_settings() {
	global $active_gateways;
	if (isset($_POST['update_upay'])) {
		$upay_settings['upay_site_id'] = $_POST['upay_site_id'];
		$upay_settings['upay_site_url'] = $_POST['upay_site_url'];
		$upay_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$upay_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$upay_settings['debug_mode'] = empty($_POST['debug_mode'])  ? false : true;
		$upay_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_upay_settings', $upay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('uPay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$upay_settings = get_option('event_espresso_upay_settings');
	if (empty($upay_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/upay/btn_stdCheckout2.gif")) {
			$upay_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/upay/btn_stdCheckout2.gif";
		} else {
			$upay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/upay/btn_stdCheckout2.gif";
		}
		$upay_settings['upay_site_id'] = '';
		$upay_settings['upay_site_url'] = '';
		$upay_settings['bypass_payment_page'] = 'N';
		$upay_settings['debug_mode'] = false;
		$upay_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_upay_settings', $upay_settings, '', 'no') == false) {
			update_option('event_espresso_upay_settings', $upay_settings);
		}
	}

	if ( ! isset( $upay_settings['button_url'] ) || ! file_exists( $upay_settings['button_url'] )) {
		$upay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_upay'])
					&& (!empty($_REQUEST['activate_upay'])
					|| array_key_exists('upay', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('uPay Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_upay'])) {
						$active_gateways['upay'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_upay'])) {
						unset($active_gateways['upay']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('upay', $active_gateways)) {
						echo '<li id="deactivate_upay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_upay=true\';" class="red_alert pointer"><strong>' . __('Deactivate uPay IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_upay_settings();
					} else {
						echo '<li id="activate_upay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_upay=true\';" class="green_alert pointer"><strong>' . __('Activate uPay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//uPay Settings Form
function event_espresso_display_upay_settings() {
	$upay_settings = get_option('event_espresso_upay_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="upay_site_id">
								<?php _e('uPay Site ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="upay_site_id" size="35" value="<?php echo $upay_settings['upay_site_id']; ?>">
						</li>
						<li>
							<label for="upay_site_url">
								<?php _e('uPay Site URL', 'event_espresso'); ?>
							</label>
							<input type="text" name="upay_site_url" size="35" value="<?php echo $upay_settings['upay_site_url']; ?>">
						</li>
					</ul>
				</td>	
				<td valign="top"><ul><li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $upay_settings['bypass_payment_page']);
							?>
						</li>
						<li>
								<label for="debug_mode">
									<?php _e('Debug Mode', 'event_espresso'); ?>
								</label>
								<input name="debug_mode" type="checkbox" value="1" <?php echo $upay_settings['debug_mode'] ? 'checked="checked"' : '' ?> /></li>

						<?php if (espresso_check_ssl() == TRUE || ( isset($upay_settings['force_ssl_return']) && $upay_settings['force_ssl_return'] == 1 )) { ?>
							<li>
								<label for="force_ssl_return">
									<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
									<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
								</label>
								<input name="force_ssl_return" type="checkbox" value="1" <?php echo $upay_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
						<?php } ?>

						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $upay_settings['button_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li>
						<li>
							<label><?php _e('Current Button Image:', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $upay_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		<input type="hidden" name="update_upay" value="update_upay">
		<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update uPay Settings', 'event_espresso') ?>" id="save_upay_settings" />
	</p>
	</form>
	
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_upay_payment_settings');
