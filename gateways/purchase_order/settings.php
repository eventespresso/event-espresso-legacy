<?php

function event_espresso_purchase_order_payment_settings() {
	global $espresso_premium, $active_gateways, $org_options;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_purchase_order_payment_settings'])) {
		$allowable_tags = '<br /><br><a>';
		$purchase_order_payment_settings['purchase_order_title'] = strip_tags($_POST['purchase_order_title'], $allowable_tags);
		$purchase_order_payment_settings['purchase_order_instructions'] = strip_tags($_POST['purchase_order_instructions'], $allowable_tags);
		$purchase_order_payment_settings['payable_to'] = strip_tags($_POST['payable_to'], $allowable_tags);
		$purchase_order_payment_settings['payment_address'] = strip_tags($_POST['payment_address'], $allowable_tags);
		update_option('event_espresso_purchase_order_payment_settings', $purchase_order_payment_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Purchase Order Payment settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$purchase_order_payment_settings = get_option('event_espresso_purchase_order_payment_settings');
	if (empty($purchase_order_payment_settings)) {
		$default_address = $org_options['organization_street1'] != '' ? $org_options['organization_street1'] . '<br />' : '';
		$default_address .= $org_options['organization_street2'] != '' ? $org_options['organization_street2'] . '<br />' : '';
		$default_address .= $org_options['organization_city'] != '' ? $org_options['organization_city'] : '';
		$default_address .= ($org_options['organization_city'] != '' && $org_options['organization_state'] != '') ? ', ' : '<br />';
		$default_address .= $org_options['organization_state'] != '' ? $org_options['organization_state'] . '<br />' : '';
		$default_address .= $org_options['organization_country'] != '' ? getCountryName($org_options['organization_country']) . '<br />' : '';
		$default_address .= $org_options['organization_zip'] != '' ? $org_options['organization_zip'] : '';
		$purchase_order_payment_settings['purchase_order_title'] = __('Purchase Order Payments', 'event_espresso');
		$purchase_order_payment_settings['purchase_order_instructions'] = __('Please send purchase_order/Money Order to the address below. Payment must be received within 48 hours of event date.', 'event_espresso');
		$purchase_order_payment_settings['payable_to'] = $org_options['organization'];
		$purchase_order_payment_settings['payment_address'] = $default_address;
		if (add_option('event_espresso_purchase_order_payment_settings', $purchase_order_payment_settings, '', 'no') == false) {
			update_option('event_espresso_purchase_order_payment_settings', $purchase_order_payment_settings);
		}
	}


	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_purchase_order_payment'])
					&& (!empty($_REQUEST['activate_purchase_order_payment'])
					|| array_key_exists('purchase_order', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Purchase Order Payment Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_purchase_order_payment'])) {
						$active_gateways['purchase_order'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_purchase_order_payment'])) {
						unset($active_gateways['purchase_order']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('purchase_order', $active_gateways)) {
						echo '<li id="deactivate_purchase_order" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_purchase_order_payment=true\';" class="red_alert pointer"><strong>' . __('Deactivate Purchase Order Payments?', 'event_espresso') . '</strong></li>';
						event_espresso_display_purchase_order_payment_settings();
					} else {
						echo '<li id="activate_purchase_order" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_purchase_order_payment=true\';" class="green_alert pointer"><strong>' . __('Activate Purchase Order Payments?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Purchase Order Payments Settings Form
function event_espresso_display_purchase_order_payment_settings() {
	$purchase_order_payment_settings = get_option('event_espresso_purchase_order_payment_settings');
	?>

	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul><li><label for="purchase_order_title"><?php _e('Title', 'event_espresso'); ?></label>
							<input type="text" name="purchase_order_title" size="30" value="<?php echo stripslashes_deep($purchase_order_payment_settings['purchase_order_title']); ?>" />
						</li>
						<li><label for="purchase_order_instructions"><?php _e('Payment Instructions', 'event_espresso'); ?></label>
							<textarea name="purchase_order_instructions" cols="30" rows="5"><?php echo stripslashes_deep($purchase_order_payment_settings['purchase_order_instructions']); ?></textarea>
						</li></ul></td>
				<td valign="top">
					<ul>
						<li><label for="payable_to"><?php _e('Payable To', 'event_espresso'); ?></label>
							<input type="text" name="payable_to" size="30" value="<?php echo stripslashes_deep($purchase_order_payment_settings['payable_to']); ?>" />
						</li>
						<li><label for="payment_address"><?php _e('Address to Send Payment', 'event_espresso'); ?></label>
							<textarea name="payment_address" cols="30" rows="5"><?php echo $purchase_order_payment_settings['payment_address']; ?></textarea>
						</li>
					</ul>
				</td>
			</tr>
		</table>
		<input type="hidden" name="update_purchase_order_payment_settings" value="update_purchase_order_payment_settings">
		<p><input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Purchase Order Payment Settings', 'event_espresso') ?>" id="save_purchase_order_payment_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_purchase_order_payment_settings');
