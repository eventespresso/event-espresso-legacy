<?php

function event_espresso_ideal_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_ideal'])) {
		$ideal_settings['ideal_mollie_partner_id'] = $_POST['ideal_mollie_partner_id'];
		$ideal_settings['ideal_mollie_use_sandbox'] = empty($_POST['ideal_mollie_use_sandbox']) ? false : true;
		$ideal_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$ideal_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_ideal_mollie_settings', $ideal_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Ideal Mollie settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$ideal_settings = get_option('event_espresso_ideal_mollie_settings');
	if (empty($ideal_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/ideal/ideal-mollie-logo.png")) {
			$ideal_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/ideal/ideal-mollie-logo.png";
		} else {
			$ideal_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/ideal/ideal-mollie-logo.png";
		}
		$ideal_settings['ideal_mollie_partner_id'] = '';
		$ideal_settings['ideal_mollie_use_sandbox'] = false;
		$ideal_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_ideal_mollie_settings', $ideal_settings, '', 'no') == false) {
			update_option('event_espresso_ideal_mollie_settings', $ideal_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_ideal'])
					&& (!empty($_REQUEST['activate_ideal'])
					|| array_key_exists('ideal', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('iDEAL by Mollie Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_ideal'])) {
						$active_gateways['ideal'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_ideal'])) {
						unset($active_gateways['ideal']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('ideal', $active_gateways)) {
						echo '<li id="deactivate_ideal" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_ideal=true\';" class="red_alert pointer"><strong>' . __('Deactivate iDEAL (Mollie)?', 'event_espresso') . '</strong></li>';
						event_espresso_display_ideal_settings();
					} else {
						echo '<li id="activate_ideal" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_ideal=true\';" class="green_alert pointer"><strong>' . __('Activate iDEAL (Mollie)?', 'event_espresso') . '</strong></li>';
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
function event_espresso_display_ideal_settings() {
	$ideal_settings = get_option('event_espresso_ideal_mollie_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label>
								<?php _e('Mollie Partner ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="ideal_mollie_partner_id" size="35" value="<?php echo $ideal_settings['ideal_mollie_partner_id']; ?>">
							<br />
						</li>
						<li>
							<label for="ideal_mollie_use_sandbox">
								<?php _e('Use iDEAL in test mode?', 'event_espresso'); ?>
							</label>
							<input name="ideal_mollie_use_sandbox" type="checkbox" value="1" <?php echo $ideal_settings['ideal_mollie_use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('(Make sure you enable test mode in your Mollie account).', 'event_espresso'); ?>
						</li>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="35" value="<?php echo (isset($ideal_settings['button_url']) ? $ideal_settings['button_url'] : '' ); ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
						</li>
						<?php if ( ! empty($ideal_settings['button_url'])) { ?>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							<?php echo (($ideal_settings['button_url'] == '') ? '' : '<img src="' . $ideal_settings['button_url'] . '" />'); ?>
						</li>
						<?php } ?>
					<?php if (espresso_check_ssl() == TRUE || ( isset($ideal_settings['force_ssl_return']) && $ideal_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $ideal_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_ideal" value="update_ideal">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update iDEAL Settings', 'event_espresso') ?>" id="save_ideal_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_ideal_payment_settings');
