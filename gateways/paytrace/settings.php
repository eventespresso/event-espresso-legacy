<?php

function event_espresso_paytrace_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_paytrace'])) {
		$paytrace_settings['paytrace_user_id'] = $_POST['paytrace_user_id'];
		$paytrace_settings['paytrace_user_pass'] = $_POST['paytrace_user_pass'];
		$paytrace_settings['header'] = $_POST['header'];
		$paytrace_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$paytrace_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('event_espresso_paytrace_settings', $paytrace_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('PayTrace settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$paytrace_settings = get_option('event_espresso_paytrace_settings');
	if (empty($paytrace_settings)) {
		$paytrace_settings['paytrace_user_id'] = '';
		$paytrace_settings['paytrace_user_pass'] = '';
		$paytrace_settings['header'] = 'Payment Transactions by PayTrace';
		$paytrace_settings['force_ssl_return'] = false;
		$paytrace_settings['display_header'] = false;
		if (add_option('event_espresso_paytrace_settings', $paytrace_settings, '', 'no') == false) {
			update_option('event_espresso_paytrace_settings', $paytrace_settings);
		}
	}

	if ( ! isset( $paytrace_settings['button_url'] ) || ! file_exists( $paytrace_settings['button_url'] )) {
		$paytrace_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_paytrace'])
					&& (!empty($_REQUEST['activate_paytrace'])
					|| array_key_exists('paytrace', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('PayTrace Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_paytrace'])) {
						$active_gateways['paytrace'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_paytrace'])) {
						unset($active_gateways['paytrace']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('paytrace', $active_gateways)) {
						echo '<li id="deactivate_paytrace" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_paytrace=true\';" class="red_alert pointer"><strong>' . __('Deactivate PayTrace?', 'event_espresso') . '</strong></li>';
						event_espresso_display_paytrace_settings();
					} else {
						echo '<li id="activate_paytrace" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_paytrace=true\';" class="green_alert pointer"><strong>' . __('Activate PayTrace?', 'event_espresso') . '</strong></li>';
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
function event_espresso_display_paytrace_settings() {
	$paytrace_settings = get_option('event_espresso_paytrace_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="paytrace_user_id">
								<?php _e('PayTrace User ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="paytrace_user_id" size="35" value="<?php echo $paytrace_settings['paytrace_user_id']; ?>">
						</li>
						<li>
							<label for="paytrace_user_pass">
								<?php _e('PayTrace User Password', 'event_espresso'); ?>
							</label>
							<input type="text" name="paytrace_user_pass" size="35" value="<?php echo $paytrace_settings['paytrace_user_pass']; ?>">
						</li>

					</ul>
				</td>
				<td>
						<ul>
						<?php if (espresso_check_ssl() == TRUE || ( isset($paytrace_settings['force_ssl_return']) && $paytrace_settings['force_ssl_return'] == 1 )) {?>
							<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $paytrace_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
							<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $paytrace_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
							<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $paytrace_settings['header']; ?>">
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
			<input type="hidden" name="update_paytrace" value="update_paytrace">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update PayTrace Settings', 'event_espresso') ?>" id="save_paytrace_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_paytrace_payment_settings');
