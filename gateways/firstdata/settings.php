<?php

function event_espresso_firstdata_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_firstdata'])) {
		$firstdata_settings['firstdata_store_id'] = $_POST['firstdata_store_id'];
		$firstdata_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$firstdata_settings['firstdata_credit_cards'] = implode(",", empty($_POST['firstdata_credit_cards']) ? array() : $_POST['firstdata_credit_cards']);
		update_option('event_espresso_firstdata_settings', $firstdata_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('First Data settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$firstdata_settings = get_option('event_espresso_firstdata_settings');
	if (empty($firstdata_settings)) {
		$firstdata_settings['firstdata_store_id'] = '';
		$firstdata_settings['use_sandbox'] = false;
		$firstdata_settings['firstdata_credit_cards'] = '';
		if (add_option('event_espresso_firstdata_settings', $firstdata_settings, '', 'no') == false) {
			update_option('event_espresso_firstdata_settings', $firstdata_settings);
		}
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_firstdata'])
					&& (!empty($_REQUEST['activate_firstdata'])
					|| array_key_exists('firstdata', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('First Data Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_firstdata'])) {
						$active_gateways['firstdata'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_firstdata']) == 'true') {
						unset($active_gateways['firstdata']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('firstdata', $active_gateways)) {
						echo '<li id="deactivate_firstdata" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_firstdata=true\';" class="red_alert pointer"><strong>' . __('Deactivate First Data?', 'event_espresso') . '</strong></li>';
						event_espresso_display_firstdata_settings();
					} else {
						echo '<li id="activate_firstdata" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_firstdata=true\';" class="green_alert pointer"><strong>' . __('Activate First Data?', 'event_espresso') . '</strong></li>';
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
function event_espresso_display_firstdata_settings() {
	$firstdata_settings = get_option('event_espresso_firstdata_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label>
								<?php _e('First Data Store Number', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="firstdata_store_id" size="35" value="<?php echo $firstdata_settings['firstdata_store_id']; ?>">
							<br />
						</li>
						<li>
							<label>
								<?php _e('Accepted Credit Cards', 'event_espresso'); ?>
							</label>
							<br />
							<?php
							$checked = 'checked="checked"';
							$firstdata_credit_cards = explode(",", $firstdata_settings['firstdata_credit_cards']);
							?>
							<input type="checkbox" name="firstdata_credit_cards[]" size="35" value="Visa" <?php echo in_array("Visa", $firstdata_credit_cards) ? $checked : ''; ?> /> Visa
							<input type="checkbox" name="firstdata_credit_cards[]" size="35" value="MasterCard" <?php echo in_array("MasterCard", $firstdata_credit_cards) ? $checked : ''; ?> /> Master Card
							<input type="checkbox" name="firstdata_credit_cards[]" size="35" value="Amex" <?php echo in_array("Amex", $firstdata_credit_cards) ? $checked : ''; ?> /> Amex
							<input type="checkbox" name="firstdata_credit_cards[]" size="35" value="Discover" <?php echo in_array("Discover", $firstdata_credit_cards) ? $checked : ''; ?> /> Discover

							<br />

						</li>
						<li>
							<label for="use_sandbox">
								<?php _e('Use the debugging feature and the ', 'event_espresso'); ?><?php _e('FirstData Sandbox', 'event_espresso'); ?>?
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $firstdata_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							&nbsp;<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=sandbox_info_firstdata"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a><br />
						</li>


					</ul></td>
				<td  valign="top">
					<span style="color:red"><?php echo __("Place the .pem file in the following folder.  Make sure the .pem file has the same name as your store number.", 'event_espresso') . ":<br /> " . dirname(__FILE__); ?></span><br /><br />

				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_firstdata" value="update_firstdata">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update First Data Settings', 'event_espresso') ?>" id="save_paypal_settings" />
		</p>
	</form>
	<div id="sandbox_info_firstdata" style="display:none">
		<h2><?php _e('First Data Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the First Data Sandbox fetaure. The debugging feature will also output the form varibales to the payment page, send an email to the admin that contains the all First Data variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The First Data Sandbox is a testing environment that is a duplicate of the live First Data site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_firstdata_payment_settings');
