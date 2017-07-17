<?php

function event_espresso_beanstream_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_beanstream'])) {
		$beanstream_settings['merchant_id'] = $_POST['merchant_id'];
		$beanstream_settings['beanstream_url']  = $_POST['beanstream_url'];
		$beanstream_settings['beanstream_use_sandbox'] = empty($_POST['beanstream_use_sandbox']) ? false : true;
		$beanstream_settings['header'] = $_POST['header'];
		$beanstream_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$beanstream_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('event_espresso_beanstream_settings', $beanstream_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Beanstream settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$beanstream_settings = get_option('event_espresso_beanstream_settings');
	if (empty($beanstream_settings)) {
		$beanstream_settings['merchant_id'] = '';
		$beanstream_settings['beanstream_url'] = 'https://web.na.bambora.com/scripts/process_transaction.asp';
		$beanstream_settings['beanstream_use_sandbox'] = false;
		$beanstream_settings['header'] = 'Payment Transactions by Beanstream';
		$beanstream_settings['force_ssl_return'] = false;
		$beanstream_settings['display_header'] = false;
		if (add_option('event_espresso_beanstream_settings', $beanstream_settings, '', 'no') == false) {
			update_option('event_espresso_beanstream_settings', $beanstream_settings);
		}
	}

	if ( ! isset( $beanstream_settings['button_url'] ) || ! file_exists( $beanstream_settings['button_url'] )) {
		$beanstream_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	if ( empty( $beanstream_settings['beanstream_url'] ) ) {
		$beanstream_settings['beanstream_url'] = 'https://web.na.bambora.com/scripts/process_transaction.asp';
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_beanstream'])
					&& (!empty($_REQUEST['activate_beanstream'])
					|| array_key_exists('beanstream', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Beanstream/Bambora Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_beanstream'])) {
						$active_gateways['beanstream'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_beanstream'])) {
						unset($active_gateways['beanstream']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('beanstream', $active_gateways)) {
						echo '<li id="deactivate_beanstream" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_beanstream=true\';" class="red_alert pointer"><strong>' . __('Deactivate Beanstream?', 'event_espresso') . '</strong></li>';
						event_espresso_display_beanstream_settings();
					} else {
						echo '<li id="activate_beanstream" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_beanstream=true\';" class="green_alert pointer"><strong>' . __('Activate Beanstream?', 'event_espresso') . '</strong></li>';
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
function event_espresso_display_beanstream_settings() {
	$beanstream_settings = get_option('event_espresso_beanstream_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="merchant_id">
								<?php _e('Beanstream Merchant ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="merchant_id" size="35" value="<?php echo $beanstream_settings['merchant_id']; ?>">
						</li>
						<li>
							<label for="beanstream_url">
								<?php _e('Gateway Server', 'event_espresso'); ?>
							</label>
							<?php 
								$beanstream_urls = array(
									'Bambora' => 'https://web.na.bambora.com/scripts/process_transaction.asp',
									'Beanstream (Deprecated)' => 'https://www.beanstream.com/scripts/process_transaction.asp'
								);

								if( empty($beanstream_settings['beanstream_url']) ) {
									$beanstream_settings['beanstream_url'] = $beanstream_urls['Bambora'];
								}
							?>
							<select name="beanstream_url">
								<?php foreach( $beanstream_urls as $key => $value ) {
									$selected = $beanstream_settings["beanstream_url"] === $value ? ' selected' : '';
									echo '<option value="'. $value .'"' . $selected . '>' . $key . '</option>';
								} ?>
							</select>
							<br />
							<?php _e('(The Gateway Server where payment requests will be sent)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="beanstream_use_sandbox">
								<?php _e('Use Beanstream in Sandbox Mode', 'event_espresso'); ?>
							</label>
							<input name="beanstream_use_sandbox" type="checkbox" value="1" <?php echo $beanstream_settings['beanstream_use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('(Make sure you enter the sandbox credentials above.)', 'event_espresso'); ?>
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($beanstream_settings['force_ssl_return']) && $beanstream_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $beanstream_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $beanstream_settings['display_header'] ? 'checked="checked"' : '' ?> />
						</li>
						<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $beanstream_settings['header']; ?>">
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
			<input type="hidden" name="update_beanstream" value="update_beanstream">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Beanstream Settings', 'event_espresso') ?>" id="save_paypal_settings" />
		</p>
	</form>
<?php
}
add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_beanstream_payment_settings');