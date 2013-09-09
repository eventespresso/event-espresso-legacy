<?php

function event_espresso_eway_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_eway'])) {
		$eway_settings['eway_id'] = $_POST['eway_id'];
		$eway_settings['eway_username'] = $_POST['eway_username'];
		$eway_settings['image_url'] = $_POST['image_url'];
		$eway_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$eway_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$eway_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$eway_settings['button_url'] = $_POST['button_url'];
		$eway_settings['region'] = $_POST['region'];
		switch ($_POST['region']) {
			case 'UK':
				$eway_settings['currency_format'] = 'GBP';
				break;
			case 'AU':
				$eway_settings['currency_format'] = 'AUD';
				break;
			case 'NZ':
				$eway_settings['currency_format'] = 'NZD';
				break;
		}
		update_option('event_espresso_eway_settings', $eway_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('eWay settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$eway_settings = get_option('event_espresso_eway_settings');
	if (empty($eway_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/eway/eway-logo.png")) {
			$eway_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/eway/eway-logo.png";
		} else {
			$eway_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/eway/eway-logo.png";
		}
		$eway_settings['eway_id'] = '';
		$eway_settings['eway_username'] = '';
		$eway_settings['image_url'] = '';
		$eway_settings['currency_format'] = 'GBP';
		$eway_settings['use_sandbox'] = false;
		$eway_settings['bypass_payment_page'] = 'N';
		$eway_settings['force_ssl_return'] = false;
		$eway_settings['region'] = 'UK';
		if (add_option('event_espresso_eway_settings', $eway_settings, '', 'no') == false) {
			update_option('event_espresso_eway_settings', $eway_settings);
		}
	}

	if ( ! isset( $eway_settings['button_url'] ) || ! file_exists( $eway_settings['button_url'] )) {
		$eway_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_eway'])
					&& (!empty($_REQUEST['activate_eway'])
					|| array_key_exists('eway', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Eway Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_eway'])) {
						$active_gateways['eway'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_eway'])) {
						unset($active_gateways['eway']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('eway', $active_gateways)) {
						echo '<li id="deactivate_eway" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_eway=true\';" class="red_alert pointer"><strong>' . __('Deactivate eWay IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_eway_settings();
					} else {
						echo '<li id="activate_eway" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_eway=true\';" class="green_alert pointer"><strong>' . __('Activate eWay IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//eWay Settings Form
function event_espresso_display_eway_settings() {
	$eway_settings = get_option('event_espresso_eway_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="eway_id">
								<?php _e('eWay ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="eway_id" size="35" value="<?php echo $eway_settings['eway_id']; ?>">
							<br />
							<?php _e('(Typically 87654321)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="eway_username">
								<?php _e('eWay username', 'event_espresso'); ?>
							</label>
							<input type="text" name="eway_username" size="35" value="<?php echo $eway_settings['eway_username']; ?>">
							<br />
							<?php _e('(Typically TestAccount)', 'event_espresso'); ?>
						</li>
						<li>
							<label for="region">
								<?php _e('Choose Your Region', 'event_espresso'); ?>
							</label>
							<select name="region">
								<option value="<?php echo $eway_settings['region']; ?>"><?php echo $eway_settings['region']; ?></option>
								<option value="UK">
									<?php _e('United Kingdom', 'event_espresso'); ?>
								</option>
								<option value="AU">
									<?php _e('Australia', 'event_espresso'); ?>
								</option>
								<option value="NZ">
									<?php _e('New Zealand', 'event_espresso'); ?>
								</option>
							</select>
						</li>
						<li>
							<label for="currency_format">
								<?php _e('The currency set for your region is', 'event_espresso'); ?>
							</label>
							<span class="display-path" style="background-color: rgb(255, 251, 204); border:#999 solid 1px; padding:2px;"><?php echo $eway_settings['currency_format']; ?></span>
							<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a> </li>
						<li>
							<label for="image_url">
								<?php _e('Image URL (logo for payment page)', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=image_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="image_url" size="35" value="<?php echo $eway_settings['image_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a><br />
							<?php _e('(used for your business/personal logo on the eWay page)', 'event_espresso'); ?>
						</li>
					</ul></td>
				<td valign="top">
					<ul>
						<li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'N', 'text' => __('No', 'event_espresso')),
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $eway_settings['bypass_payment_page']);
							?>
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($eway_settings['force_ssl_return']) && $eway_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $eway_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label for="use_sandbox">
								<?php _e('Turn on Debugging Using the', 'event_espresso'); ?> <a href="http://www.eway.com.au/Developer/Testing/" title="eWay Sandbox Login" target="_blank"><?php _e('eWay Sandbox', 'event_espresso'); ?></a> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=eway_sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $eway_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
						</li>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo (($eway_settings['button_url'] == '') ? '' : $eway_settings['button_url'] ); ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							<?php echo (($eway_settings['button_url'] == '') ? '' : '<img src="' . $eway_settings['button_url'] . '" />'); ?></li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_eway" value="update_eway">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update eWay Settings', 'event_espresso') ?>" id="save_eway_settings" />
		</p>
	</form>
	<div id="eway_sandbox_info" style="display:none">
		<h2><?php _e('eWay Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the eWay Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all eWay variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The eWay Sandbox is a testing environment that is a duplicate of the live eWay site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live eWay environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="image_url_info" style="display:none">
		<h2>
			<?php _e('eWay Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the eWay checkout pages.', 'event_espresso'); ?>
		</p>
		<p>
			<?php _e('Default - Your business name, if you have a Business account, or your email address, if you have Premier or Personal account.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('eWay Currency', 'event_espresso'); ?></h2>
		<p><?php _e('eWay uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is British Pounds (GBP). The currency must match the region where you are using eway, so changing the region will automatically change the currency.', 'event_espresso'); ?> </p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_eway_payment_settings');
