<?php

function event_espresso_ideal_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_ideal'])) {
		$ideal_settings['ideal_mollie_partner_id'] = $_POST['ideal_mollie_partner_id'];
		$ideal_settings['ideal_mollie_use_sandbox'] = empty($_POST['ideal_mollie_use_sandbox']) ? false : true;
		update_option('event_espresso_ideal_mollie_settings', $ideal_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Ideal Mollie settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$ideal_settings = get_option('event_espresso_ideal_mollie_settings');
	if (empty($ideal_settings)) {
		$ideal_settings['ideal_mollie_partner_id'] = '';
		$ideal_settings['ideal_mollie_use_sandbox'] = false;
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
				<?php _e('iDEAL (Mollie) Settings', 'event_espresso'); ?>
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
				<td valign="top"><ul>
						<li>
							<label>
								<?php _e('iDEAL (Mollie) partner id', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="ideal_mollie_partner_id" size="35" value="<?php echo $ideal_settings['ideal_mollie_partner_id']; ?>">
							<br />
						</li>
						<li>
							<label for="ideal_mollie_use_sandbox">
								<?php _e('Use Mollie in test mode', 'event_espresso'); ?>?
							</label>
							<input name="ideal_mollie_use_sandbox" type="checkbox" value="1" <?php echo $ideal_settings['ideal_mollie_use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('(Make sure you enable test mode in your Mollie account).', 'event_espresso'); ?>
						</li>

					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_ideal" value="update_ideal">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Settings', 'event_espresso') ?>" id="save_ideal_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_ideal_payment_settings');
