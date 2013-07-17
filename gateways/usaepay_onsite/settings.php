<?php

function espresso_usaepay_onsite_payment_settings() {
	global $espresso_premium, $active_gateways;

	if (!$espresso_premium)
		return;
	if (isset($_POST['update_usaepay_onsite'])) {
		$settings['key'] = $_POST['key'];
		$settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$settings['testmode'] = empty($_POST['testmode']) ? false : true;
		$settings['header'] = $_POST['header'];
		$settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('espresso_usaepay_onsite_settings', $settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('USAePay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$settings = get_option('espresso_usaepay_onsite_settings');
	if (empty($settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/usaepay_onsite/usaepay-logo.png")) {
			$settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/usaepay_onsite/usaepay-logo.png";
		} else {
			$settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/usaepay_onsite/usaepay-logo.png";
		}
		$settings['key'] = '';
		$settings['use_sandbox'] = false;
		$settings['testmode'] = false;
		$settings['header'] = 'Payment Transactions by USAePay';
		$settings['force_ssl_return'] = false;
		$settings['display_header'] = false;
		if (add_option('espresso_usaepay_onsite_settings', $settings, '', 'no') == false) {
			update_option('espresso_usaepay_onsite_settings', $settings);
		}
	}

	if ( ! isset( $settings['button_url'] ) || ! file_exists( $settings['button_url'] )) {
		$settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	if ( empty( $_REQUEST['deactivate_usaepay_onsite'] ) && ( ! empty( $_REQUEST['activate_usaepay_onsite'] ) || array_key_exists( 'usaepay_onsite', $active_gateways ))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div id="usaepayonsitepostbox" class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('USAePay Onsite Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_usaepay_onsite'])) {
						$active_gateways['usaepay_onsite'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_usaepay_onsite'])) {
						unset($active_gateways['usaepay_onsite']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('usaepay_onsite', $active_gateways)) {
						echo '<li id="deactivate_usaepay_onsite" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_usaepay_onsite=true\';" class="red_alert pointer"><strong>' . __('Deactivate USAePay Offsite IPN?', 'event_espresso') . '</strong></li>';
						espresso_display_usaepay_onsite_settings();
					} else {
						echo '<li id="activate_usaepay_onsite" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_usaepay_onsite=true\';" class="green_alert pointer"><strong>' . __('Activate USAePay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function espresso_display_usaepay_onsite_settings() {
	$settings = get_option('espresso_usaepay_onsite_settings');
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
							<label for="usaepay_onsite_use_sandbox">
								<?php _e('Use USAePay\' Development Server?', 'event_espresso'); ?>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('(Make sure you enter the development server credentials above.)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="usaepay_onsite_testmode">
								<?php _e('Submit a test transaction?', 'event_espresso'); ?>
							</label>
							<input name="testmode" type="checkbox" value="1" <?php echo $settings['testmode'] ? 'checked="checked"' : '' ?> />
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($settings['force_ssl_return']) && $settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $settings['header']; ?>">
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
			<input type="hidden" name="update_usaepay_onsite" value="update_usaepay_onsite">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update USAePay Onsite Settings', 'event_espresso') ?>" id="save_usaepay_onsite_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'espresso_usaepay_onsite_payment_settings');
