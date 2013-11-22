<?php

function event_espresso_invoice_payment_settings() {
	global $espresso_premium, $active_gateways, $org_options;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_invoice_payment_settings'])) {
		$invoice_payment_settings['invoice_title'] = strip_tags($_POST['invoice_title']);
		$invoice_payment_settings['pdf_title'] = strip_tags($_POST['pdf_title']);
		$invoice_payment_settings['pdf_instructions'] = strip_tags($_POST['pdf_instructions']);
		$invoice_payment_settings['invoice_instructions'] = strip_tags($_POST['invoice_instructions']);
		$invoice_payment_settings['payable_to'] = strip_tags($_POST['payable_to']);
		$invoice_payment_settings['payment_address'] = strip_tags($_POST['payment_address']);
		$invoice_payment_settings['image_url'] = strip_tags($_POST['image_url']);
		$invoice_payment_settings['show'] = strip_tags($_POST['show']);
		update_option('event_espresso_invoice_payment_settings', $invoice_payment_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Invoice Payment settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');
	if (empty($invoice_payment_settings)) {
		$default_address = trim($org_options['organization_street1']);
		$default_address .= empty($org_options['organization_street2']) ? '' : '<br />' . trim($org_options['organization_street2']);
		$default_address .= '<br />' . trim($org_options['organization_city']);
		$default_address .= ',' . trim($org_options['organization_state']);
		$default_address .= '<br />' . trim(getCountryName($org_options['organization_country']));
		$default_address .= '<br />' . trim($org_options['organization_zip']);
		$invoice_payment_settings['invoice_title'] = __('Invoice Payments', 'event_espresso');
		$invoice_payment_settings['pdf_title'] = __('Invoice Payments', 'event_espresso');
		$invoice_payment_settings['pdf_instructions'] = __('Please send this invoice with payment attached to the address above, or use the payment link below. Payment must be received within 48 hours of event date.', 'event_espresso');
		$invoice_payment_settings['invoice_instructions'] = __('Please send Invoice to the address below. Payment must be received within 48 hours of event date.', 'event_espresso');
		$invoice_payment_settings['payable_to'] = trim($org_options['organization']);
		$invoice_payment_settings['payment_address'] = trim($default_address);
		$invoice_payment_settings['image_url'] = '';
		$invoice_payment_settings['show'] = 'Y';
		if (add_option('event_espresso_invoice_payment_settings', $invoice_payment_settings, '', 'no') == false) {
			update_option('event_espresso_invoice_payment_settings', $invoice_payment_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_invoice_payment'])
					&& (!empty($_REQUEST['activate_invoice_payment'])
					|| array_key_exists('invoice', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Invoice Payment Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_invoice_payment'])) {
						$active_gateways['invoice'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_invoice_payment'])) {
						unset($active_gateways['invoice']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('invoice', $active_gateways)) {
						echo '<li id="deactivate_invoice" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_invoice_payment=true\';" class="red_alert pointer"><strong>' . __('Deactivate Invoice Payments?', 'event_espresso') . '</strong></li>';
						event_espresso_display_invoice_payment_settings();
					} else {
						echo '<li id="activate_invoice" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_invoice_payment=true\';" class="green_alert pointer"><strong>' . __('Activate Invoice Payments?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Invoice Payments Settings Form
function event_espresso_display_invoice_payment_settings() {
	$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="invoice_title">
								<?php _e('Title', 'event_espresso'); ?>
							</label>
							<input type="text" name="invoice_title" size="30" value="<?php echo stripslashes_deep($invoice_payment_settings['invoice_title']); ?>" />
						</li>
						<li>
							<label for="invoice_instructions">
								<?php _e('Invoice Instructions', 'event_espresso'); ?>
							</label>
							<textarea name="invoice_instructions" cols="30" rows="5"><?php echo stripslashes_deep($invoice_payment_settings['invoice_instructions']); ?></textarea>
						</li>
						<li>
							<label for="payable_to">
								<?php _e('Payable To', 'event_espresso'); ?>
							</label>
							<input type="text" name="payable_to" size="30" value="<?php echo stripslashes_deep($invoice_payment_settings['payable_to']); ?>" />
						</li>
						<li>
							<label for="payment_address">
								<?php _e('Address to Send Payment', 'event_espresso'); ?>
							</label>
							<textarea name="payment_address" cols="30" rows="5"><?php echo stripslashes_deep(str_replace("<br />", ",&nbsp", $invoice_payment_settings['payment_address'])); ?></textarea>
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<h4><?php _e('PDF Settings', 'event_espresso'); ?></h4>
						</li>
						<li>
							<label for="pdf_title">
								<?php _e('PDF Title (top right of the invoice):', 'event_espresso'); ?>
							</label>
							<input type="text" name="pdf_title" size="30" value="<?php echo stripslashes_deep($invoice_payment_settings['pdf_title']); ?>" />
						</li>
						<li>
							<label for="image_url">
								<?php _e('Logo URL', 'event_espresso'); ?>
							</label>
							<input type="text" name="image_url" size="45" value="<?php echo $invoice_payment_settings['image_url']; ?>" /><br />
							<?php _e('(logo for the top left of the invoice)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="pdf_instructions">
								<?php _e('Invoice Instructions in PDF', 'event_espresso'); ?>
							</label>
							<textarea name="pdf_instructions" cols="30" rows="5"><?php echo stripslashes_deep($invoice_payment_settings['pdf_instructions']); ?></textarea>
						</li>
						<li>
							<label for="show"><?php _e('Show as an option on the payment page?', 'event_espresso'); ?></label>
							<?php
							$values = array(
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')),
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
							);
							echo select_input('show', $values, $invoice_payment_settings['show']);
							?>
						</li>
					</ul>
				</td>
			</tr>
		</table>
		<input type="hidden" name="update_invoice_payment_settings" value="update_invoice_payment_settings">
		<p>
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Invoice Payment Settings', 'event_espresso') ?>" id="save_invoice_payment_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_invoice_payment_settings');
