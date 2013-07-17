<?php

function espresso_usaepay_offsite_payment_settings() {
	global $espresso_premium, $active_gateways;

	if (!$espresso_premium)
		return;
	if (isset($_POST['update_usaepay_offsite'])) {
		$settings['key'] = $_POST['key'];
		$settings['button_url'] = $_POST['button_url'];
		update_option('espresso_usaepay_offsite_settings', $settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('USAePay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$settings = get_option('espresso_usaepay_offsite_settings');
	if (empty($settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/usaepay_offsite/usaepay-logo.png")) {
			$settings['button_url'] = EVENT_ESPRESSO_GATEWAY_DIR . "/usaepay_offsite/usaepay-logo.png";
		} else {
			$settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/usaepay_offsite/usaepay-logo.png";
		}
		$settings['key'] = '';

		if (add_option('espresso_usaepay_offsite_settings', $settings, '', 'no') == false) {
			update_option('espresso_usaepay_offsite_settings', $settings);
		}
	}

	if ( ! isset( $settings['button_url'] ) || ! file_exists( $settings['button_url'] )) {
		$settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	if (empty($_REQUEST['deactivate_usaepay_offsite'])
					&& (!empty($_REQUEST['activate_usaepay_offsite'])
					|| array_key_exists('usaepay_offsite', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div id="usaepayoffsitepostbox" class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('USAePay Offsite Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_usaepay_offsite'])) {
						$active_gateways['usaepay_offsite'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_usaepay_offsite'])) {
						unset($active_gateways['usaepay_offsite']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('usaepay_offsite', $active_gateways)) {
						echo '<li id="deactivate_usaepay_offsite" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_usaepay_offsite=true\';" class="red_alert pointer"><strong>' . __('Deactivate USAePay Offsite IPN?', 'event_espresso') . '</strong></li>';
						espresso_display_usaepay_offsite_settings();
					} else {
						echo '<li id="activate_usaepay_offsite" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_usaepay_offsite=true\';" class="green_alert pointer"><strong>' . __('Activate USAePay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function espresso_display_usaepay_offsite_settings() {
	$settings = get_option('espresso_usaepay_offsite_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="key">
								<?php _e('Key', 'event_espresso'); ?>
							</label>
							<input type="text" name="key" size="35" value="<?php echo $settings['key']; ?>">
						</li>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $settings['button_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  
						</li>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $settings['button_url'] . '" />'; ?>
						</li>
					</ul>
				</td>
			</tr>
		</table>
		
		<p>
			<input type="hidden" name="update_usaepay_offsite" value="update_usaepay_offsite">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update USAePay Offsite Settings', 'event_espresso') ?>" id="save_usaepay_offsite_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'espresso_usaepay_offsite_payment_settings');
