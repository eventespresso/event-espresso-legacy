<?php

function event_espresso_quickpay_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_quickpay'])) {
		$quickpay_settings['quickpay_merchantid'] = $_POST['quickpay_merchantid'];
		$quickpay_settings['quickpay_md5secret'] = $_POST['quickpay_md5secret'];
		$quickpay_settings['quickpay_language'] = $_POST['quickpay_language'];
		$quickpay_settings['quickpay_autocapture'] = $_POST['quickpay_autocapture'];
		$quickpay_settings['quickpay_currency'] = $_POST['quickpay_currency'];
		$quickpay_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$quickpay_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$quickpay_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_quickpay_settings', $quickpay_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('QuickPay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$quickpay_settings = get_option('event_espresso_quickpay_settings');
	if (empty($quickpay_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/quickpay/quickpay-logo.png")) {
			$quickpay_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/quickpay/quickpay-logo.png";
		} else {
			$quickpay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/quickpay/quickpay-logo.png";
		}
		$quickpay_settings['quickpay_merchantid'] = '';
		$quickpay_settings['quickpay_md5secret'] = '';
		$quickpay_settings['quickpay_language'] = 'en';
		$quickpay_settings['quickpay_autocapture'] = '1';
		$quickpay_settings['quickpay_currency'] = 'USD';
		$quickpay_settings['use_sandbox'] = false;
		$quickpay_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_quickpay_settings', $quickpay_settings, '', 'no') == false) {
			update_option('event_espresso_quickpay_settings', $quickpay_settings);
		}
	}

	if ( ! isset( $quickpay_settings['button_url'] ) || ! file_exists( $quickpay_settings['button_url'] )) {
		$quickpay_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_quickpay'])
					&& (!empty($_REQUEST['activate_quickpay'])
					|| array_key_exists('quickpay', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('QuickPay Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_quickpay'])) {
						$active_gateways['quickpay'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_quickpay'])) {
						unset($active_gateways['quickpay']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('quickpay', $active_gateways)) {
						echo '<li id="deactivate_quickpay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_quickpay=true\';" class="red_alert pointer"><strong>' . __('Deactivate QuickPay IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_quickpay_settings();
					} else {
						echo '<li id="activate_quickpay" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_quickpay=true\';" class="green_alert pointer"><strong>' . __('Activate QuickPay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//QuickPay Settings Form
function event_espresso_display_quickpay_settings() {
	$quickpay_settings = get_option('event_espresso_quickpay_settings');
	$qp_settings = array( 
		'quickpay_merchantid',
		'quickpay_md5secret',
		'quickpay_language',
		'quickpay_autocapture',
		'quickpay_currency',
		'use_sandbox',
		'force_ssl_return',
		'button_url'
	);
	
	foreach ( $qp_settings as $qp_setting ){
		$quickpay_settings[ $qp_setting ] = isset( $quickpay_settings[ $qp_setting ] ) ? $quickpay_settings[ $qp_setting ] : '';
	}
	
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class="espresso_form">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="quickpay_merchantid">
								<?php _e('QuickPay ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="quickpay_merchantid" size="35" value="<?php echo $quickpay_settings['quickpay_merchantid']; ?>">
						</li>
						<li>
							<label for="quickpay_md5secret">
								<?php _e('QuickPay md5 Secret', 'event_espresso'); ?>
							</label>
							<input type="text" name="quickpay_md5secret" size="35" value="<?php echo $quickpay_settings['quickpay_md5secret']; ?>">
						</li>
						<li>
							<label for="quickpay_language"><?php _e('Payment Window Language', 'event_espresso'); ?></label>
							<select name='quickpay_language'>
								<option value="<?php echo $quickpay_settings['quickpay_language']; ?>" selected="selected" ><?php echo $quickpay_settings['quickpay_language']; ?></option>
								<option value='da'>da - Danish</option>
								<option value='de'>de - German</option>
								<option value='en'>en - English</option>
								<option value='fr'>fr - French</option>
								<option value='it'>it - Italian</option>
								<option value='no'>no - Norwegian</option>
								<option value='nl'>nl - Dutch</option>
								<option value='pl'>pl - Polish</option>
								<option value='se'>se - Swedish</option>
							</select><br />
							<?php _e('(Choose which language the transaction window will use.)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="quickpay_autocapture">Automatic capture</label>
							<ul>
								<li>
									<label class="radio-btn-lbl">										
										<input name="quickpay_autocapture" value="0"<?php if( $quickpay_settings['quickpay_autocapture'] == '0' ){ ?> checked="checked"<?php } ?> type="RADIO">
										<span><?php _e('Off', 'event_espresso'); ?></span>										
									</label>
								</li>
								<li>
									<label class="radio-btn-lbl">
										<input name="quickpay_autocapture" value="1"<?php if( $quickpay_settings['quickpay_autocapture'] == '1' ){ ?> checked="checked"<?php } ?> type="RADIO">
										<span><?php _e('On', 'event_espresso'); ?></span>										
									</label>
								</li>
							</ul>

							<?php _e('(Automatic Capture means you will automatically deduct the amount from the customer.)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="quickpay_currency"><?php _e('Currency', 'event_espresso'); ?></label>
							<ul>
								<li>
									<label class="radio-btn-lbl">
										<input name="quickpay_currency" value="EUR" <?php if ($quickpay_settings['quickpay_currency'] == 'EUR') { ?>checked="checked"<?php } ?> type="RADIO">
										<span>EUR</span>										
									</label>
								</li>
								<li>
									<label class="radio-btn-lbl">
										<input name="quickpay_currency" value="DKK" <?php if ($quickpay_settings['quickpay_currency'] == 'DKK') { ?>checked="checked"<?php } ?> type="RADIO">
										<span>DKK</span>										
									</label>
								</li>
								<li>
									<label class="radio-btn-lbl">
										<input name="quickpay_currency" value="USD" <?php if ($quickpay_settings['quickpay_currency'] == 'USD') { ?>checked="checked"<?php } ?> type="RADIO">
										<span>USD</span>										
									</label>
								</li>
							</ul>
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label for="use_sandbox">
								<?php _e('Turn on Debugging Using the Sandbox', 'event_espresso'); ?>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $quickpay_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($quickpay_settings['force_ssl_return']) && $quickpay_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $quickpay_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> 
							</label>
							<?php $quickpay_settings['button_url'] = $quickpay_settings['button_url'] == '' ? EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/pay-by-credit-card.png' : $quickpay_settings['button_url']; ?>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $quickpay_settings['button_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a></li>
						<li>
							<?php _e('Current Button Image', 'event_espresso'); ?>
							<br />
							<img src="<?php echo $quickpay_settings['button_url']; ?>" />
						</li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_quickpay" value="update_quickpay">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update QuickPay Settings', 'event_espresso') ?>" id="save_quickpay_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_quickpay_payment_settings');
