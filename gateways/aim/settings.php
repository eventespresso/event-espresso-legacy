<?php

function event_espresso_aim_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_authnet_aim'])) {
		$authnet_aim_settings['authnet_aim_login_id'] = $_POST['authnet_aim_login_id'];
		$authnet_aim_settings['authnet_aim_transaction_key'] = $_POST['authnet_aim_transaction_key'];
		$authnet_aim_settings['header'] = $_POST['header'];
		$authnet_aim_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$authnet_aim_settings['test_transactions'] = empty($_POST['test_transactions']) ? false : true;
		$authnet_aim_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$authnet_aim_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('event_espresso_authnet_aim_settings', $authnet_aim_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Authorize.net AIM settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$authnet_aim_settings = get_option('event_espresso_authnet_aim_settings');
	if (empty($authnet_aim_settings)) {
		$authnet_aim_settings['authnet_aim_login_id'] = '';
		$authnet_aim_settings['authnet_aim_transaction_key'] = '';
		$authnet_aim_settings['header'] = 'Payment Transactions by Authorize.net';
		$authnet_aim_settings['use_sandbox'] = false;
		$authnet_aim_settings['test_transactions'] = false;
		$authnet_aim_settings['force_ssl_return'] = false;
		$authnet_aim_settings['display_header'] = false;
		if (add_option('event_espresso_authnet_aim_settings', $authnet_aim_settings, '', 'no') == false) {
			update_option('event_espresso_authnet_aim_settings', $authnet_aim_settings);
		}
	}

	if ( ! isset( $authnet_aim_settings['button_url'] ) || ! file_exists( $authnet_aim_settings['button_url'] )) {
		$authnet_aim_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/aim/aim-logo.png";
	}
	
	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_authnet_aim'])
					&& (!empty($_REQUEST['activate_authnet_aim'])
					|| array_key_exists('aim', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Authorize.net AIM Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (isset($_REQUEST['activate_authnet_aim']) && $_REQUEST['activate_authnet_aim'] == 'true') {
						$active_gateways['aim'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (isset($_REQUEST['deactivate_authnet_aim']) && $_REQUEST['deactivate_authnet_aim'] == 'true') {
						unset($active_gateways['aim']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('aim', $active_gateways)) {
						echo '<li id="deactivate_aim" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_authnet_aim=true\';" class="red_alert pointer"><strong>' . __('Deactivate Authorize.net AIM Gateway?', 'event_espresso') . '</strong></li>';
							event_espresso_display_authnet_aim_settings();
					} else {
						echo '<li id="activate_aim" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_authnet_aim=true\';" class="green_alert pointer"><strong>' . __('Activate Authorize.net AIM Gateway?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Authorize.net Settings Form
function event_espresso_display_authnet_aim_settings() {
	$authnet_aim_settings = get_option('event_espresso_authnet_aim_settings');
	
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="authnet_aim_login_id">
								<?php _e('Authorize.net AIM Login ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="authnet_aim_login_id" size="35" value="<?php echo $authnet_aim_settings['authnet_aim_login_id']; ?>">
						</li>
						<li>
							<label for="authnet_aim_transaction_key">
								<?php _e('Authorize.net AIM Transaction Key', 'event_espresso'); ?>
							</label>
							<input type="text" name="authnet_aim_transaction_key" size="35" value="<?php echo $authnet_aim_settings['authnet_aim_transaction_key']; ?>">
						</li>
					
					</ul></td>
				<td valign="top">
					<ul>
						<li>
							<label for="use_sandbox">
								<?php _e('Account Uses Authorize.net development Server', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=authnet_aim_sandbox"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input id="sandbox_checkbox_aim" name="use_sandbox" type="checkbox" value="1" <?php echo $authnet_aim_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							 </li>
						<li>
							<label for="test_transactions">
								<?php _e('Submit a Test Transaction', 'event_espresso'); ?>
							</label>
							<input name="test_transactions" type="checkbox" value="1" <?php echo $authnet_aim_settings['test_transactions'] ? 'checked="checked"' : '' ?> />
						</li>
						
						<?php if (espresso_check_ssl() == TRUE || ( isset($authnet_aim_settings['force_ssl_return']) && $authnet_aim_settings['force_ssl_return'] == 1 )) {?>
							<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $authnet_aim_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
							<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $authnet_aim_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
							<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="header" size="35" value="<?php echo $authnet_aim_settings['header']; ?>">
						</li>
					</ul></td>
			</tr>
		</table>
		<?php 
		if (espresso_check_ssl() == FALSE){
			espresso_ssl_required_gateway_message();
		}
		?>
		<p>
			<input type="hidden" name="update_authnet_aim" value="update_authnet_aim">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Authorize.net AIM Settings', 'event_espresso') ?>" id="save_authnet_aim_settings" />
		</p>
	</form>

	<div id="authnet_aim_sandbox" style="display:none">
		<h2><?php _e('Authorize.net AIM Test Mode', 'event_espresso'); ?></h2>
		<p><?php _e('Test Mode allows you to submit test transactions to the payment gateway. Transactions that are submitted while Test Mode is ON are NOT actually processed. The result of a transaction depends on the card number submitted, and the invoice amount. If you want a transaction to be approved, use one of the following card numbers.', 'event_espresso'); ?></p><p>370000000000002 (<?php _e('American Express', 'event_espresso'); ?>)<br />6011000000000012 (<?php _e('Discover', 'event_espresso'); ?>)<br />5424000000000015 (<?php _e('Master Card', 'event_espresso'); ?>)<br />4007000000027 (<?php _e('Visa', 'event_espresso'); ?>)</p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_aim_payment_settings');
