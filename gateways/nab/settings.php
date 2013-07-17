<?php

function event_espresso_nab_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_nab'])) {
		$nab_settings['nab_merchant_id'] = $_POST['nab_merchant_id'];
		$nab_settings['nab_merchant_password'] = $_POST['nab_merchant_password'];
		$nab_settings['nab_use_sandbox'] = isset($_POST['nab_use_sandbox']) ? 1 : 0;
		update_option('event_espresso_nab_settings', $nab_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('NAB Transact Direct Post settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$nab_settings = get_option('event_espresso_nab_settings');
	if (empty($nab_settings)) {
		$nab_settings['nab_merchant_id'] = '';
		$nab_settings['nab_merchant_password'] = '';
		$nab_settings['nab_use_sandbox'] = 0;
		if (add_option('event_espresso_nab_settings', $nab_settings, '', 'no') == false) {
			update_option('event_espresso_nab_settings', $nab_settings);
		}
	}

	if ( ! isset( $nab_settings['button_url'] ) || ! file_exists( $nab_settings['button_url'] )) {
		$nab_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_nab'])
					&& (!empty($_REQUEST['activate_nab'])
					|| array_key_exists('nab', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<h3 class="hndle">
				<?php _e('NAB Transact Direct Post Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_nab'])) {
						$active_gateways['nab'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_nab'])) {
						unset($active_gateways['nab']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('nab', $active_gateways)) {
						echo '<li id="deactivate_nab" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_nab=true\';" class="red_alert pointer"><strong>' . __('Deactivate NAB Transact Direct Post?', 'event_espresso') . '</strong></li>';
						event_espresso_display_nab_settings();
					} else {
						echo '<li id="activate_nab" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_nab=true\';" class="green_alert pointer"><strong>' . __('Activate NAB Transact Direct Post?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//nab Settings Form
function event_espresso_display_nab_settings() {
	$nab_settings = get_option('event_espresso_nab_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="nab_id">
								<?php _e('NAB Merchant ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="nab_merchant_id" size="35" value="<?php echo $nab_settings['nab_merchant_id']; ?>" />
							<br />
						</li>
						<li>
							<label for="nab_id">
								<?php _e('NAB Merchant Password', 'event_espresso'); ?>
							</label>
							<input type="text" name="nab_merchant_password" size="35" value="<?php echo $nab_settings['nab_merchant_password']; ?>" />
							<br />
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label for="nab_use_sandbox">
								<?php _e('Use NAB Transact Direct Post in test mode?', 'event_espresso'); ?>
							</label>
							<input name="nab_use_sandbox" type="checkbox" value="Test Reference" <?php echo $nab_settings['nab_use_sandbox'] == "1" ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('(Make sure you enter the test credentials above.)', 'event_espresso'); ?>
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
			<input type="hidden" name="update_nab" value="update_nab" />
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update NAB Settings', 'event_espresso') ?>" id="save_nab_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_nab_settings');
