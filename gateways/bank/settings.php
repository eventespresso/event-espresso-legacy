<?php

function event_espresso_bank_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_bank_deposit_settings'])) {
		$allowable_tags = '<br /><br><a>';
		$bank_deposit_settings['account_name'] = strip_tags($_POST['account_name'], $allowable_tags);
		$bank_deposit_settings['bank_title'] = strip_tags($_POST['bank_title'], $allowable_tags);
		$bank_deposit_settings['bank_instructions'] = strip_tags($_POST['bank_instructions'], $allowable_tags);
		$bank_deposit_settings['bank_name'] = strip_tags($_POST['bank_name'], $allowable_tags);
		$bank_deposit_settings['bank_account'] = strip_tags($_POST['bank_account'], $allowable_tags);
		$bank_deposit_settings['bank_address'] = strip_tags($_POST['bank_address'], $allowable_tags);
		update_option('event_espresso_bank_deposit_settings', $bank_deposit_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Electronic Funds Transfer settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$bank_deposit_settings = get_option('event_espresso_bank_deposit_settings');
	if (empty($bank_deposit_settings)) {
		$bank_deposit_settings['account_name'] = '';
		$bank_deposit_settings['bank_title'] = __('Electronic Funds Transfers', 'event_espresso');
		$bank_deposit_settings['bank_instructions'] = __('Please initiate an electronic payment using the bank information below. Payment must be received within 48 hours of event date.', 'event_espresso');
		$bank_deposit_settings['bank_name'] = '';
		$bank_deposit_settings['bank_account'] = '';
		$bank_deposit_settings['bank_address'] = '';
		if (add_option('event_espresso_bank_deposit_settings', $bank_deposit_settings, '', 'no') == false) {
			update_option('event_espresso_bank_deposit_settings', $bank_deposit_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_bank_payment'])
					&& (!empty($_REQUEST['activate_bank_payment'])
					|| array_key_exists('bank', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Electronic Funds Transfer Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_bank_payment'])) {
						$active_gateways['bank'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_bank_payment'])) {
						unset($active_gateways['bank']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('bank', $active_gateways)) {
						echo '<li id="deactivate_bank" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_bank_payment=true\';" class="red_alert pointer"><strong>' . __('Deactivate Electronic Funds Transfers?', 'event_espresso') . '</strong></li>';
						event_espresso_display_bank_deposit_settings();
					} else {
						echo '<li id="activate_bank" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_bank_payment=true\';" class="green_alert pointer"><strong>' . __('Activate Electronic Funds Transfers?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
<?php

//Electronic Funds Transfers Settings Form
function event_espresso_display_bank_deposit_settings() {
	$bank_deposit_settings = get_option('event_espresso_bank_deposit_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="bank_title">
								<?php _e('Title', 'event_espresso'); ?>
							</label>
							<input type="text" name="bank_title" size="30" value="<?php echo $bank_deposit_settings['bank_title']; ?>" />
						</li>
						<li>
							<label for="bank_instructions">
								<?php _e('Payment Instructions', 'event_espresso'); ?>
							</label>
							<textarea name="bank_instructions" cols="30" rows="5"><?php echo $bank_deposit_settings['bank_instructions']; ?></textarea>
						</li>
					</ul></td>
				<td valign="top"><ul>
						<li>
							<label for="account_name">
								<?php _e('Name on Account', 'event_espresso'); ?>
							</label>
							<input type="text" name="account_name" size="30" value="<?php echo trim($bank_deposit_settings['account_name']) ?>" />
						</li>
						<li>
							<label for="bank_account">
								<?php _e('Bank Account #', 'event_espresso'); ?>
							</label>
							<input type="text" name="bank_account" size="30" value="<?php echo trim($bank_deposit_settings['bank_account']) ?>" />
						</li>
						<li>
							<label for="bank_name">
								<?php _e('Bank Name', 'event_espresso'); ?>
							</label>
							<input type="text" name="bank_name" size="30" value="<?php echo trim($bank_deposit_settings['bank_name']) ?>" />
						</li>
						<li>
							<label for="bank_address">
								<?php _e('Bank Address', 'event_espresso'); ?>
							</label>
							<textarea name="bank_address" cols="30" rows="5"><?php echo $bank_deposit_settings['bank_address']; ?></textarea>
						</li>
					</ul></td>
			</tr>
		</table>
		<input type="hidden" name="update_bank_deposit_settings" value="update_bank_deposit_settings">
		<p>
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Electronic Funds Transfer Settings', 'event_espresso') ?>" id="save_bank_deposit_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_bank_payment_settings');
