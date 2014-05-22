<?php

function event_espresso_evertec_payment_settings() {
	global $active_gateways;
	if (isset($_POST['update_evertec'])) {
		$evertec_settings['username'] = $_POST['username'];
		$evertec_settings['password'] = $_POST['password'];
		$evertec_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$evertec_settings['accepted_payment_methods'] = $_POST['accepted_payment_methods'];
		$evertec_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_evertec_settings', $evertec_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Evertec settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$evertec_settings = get_option('event_espresso_evertec_settings');
	if (empty($evertec_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/evertec/logo_evertec.png")) {
			$evertec_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/evertec/logo_evertec.png";
		} else {
			$evertec_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/evertec/logo_evertec.png";
		}
		$evertec_settings['username'] = '';
		$evertec_settings['password'] = '';
		$evertec_settings['use_sandbox'] = false;
		$evertec_settings['accepted_payment_methods'] = array('A','V','M','X','W','S','C');
		if (add_option('event_espresso_evertec_settings', $evertec_settings, '', 'no') == false) {
			update_option('event_espresso_evertec_settings', $evertec_settings);
		}
	}

	if ( ! isset( $evertec_settings['button_url'] ) || ! file_exists( $evertec_settings['button_url'] )) {
		$evertec_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_evertec'])
					&& (!empty($_REQUEST['activate_evertec'])
					|| array_key_exists('evertec', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Evertec Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_evertec'])) {
						$active_gateways['evertec'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_evertec'])) {
						unset($active_gateways['evertec']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('evertec', $active_gateways)) {
						echo '<li id="deactivate_evertec" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_evertec=true\';" class="red_alert pointer"><strong>' . __('Deactivate Evertec IPN?', 'event_espresso') . '</strong></li>';
						event_espresso_display_evertec_settings();
					} else {
						echo '<li id="activate_evertec" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_evertec=true\';" class="green_alert pointer"><strong>' . __('Activate Evertec IPN?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Evertec Settings Form
function event_espresso_display_evertec_settings() {
	$evertec_settings = get_option('event_espresso_evertec_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="username">
								<?php _e('Username', 'event_espresso'); ?>
							</label>
							<input type="text" name="username" size="35" value="<?php echo $evertec_settings['username']; ?>">
						</li>
						<li>
							<label for="password">
								<?php _e('Password', 'event_espresso'); ?>
							</label>
							<input type="text" name="password" size="35" value="<?php echo $evertec_settings['password']; ?>">
						</li>
						<li>
							<p><label for="evertec_use_sandbox">
								<input name="use_sandbox" type="checkbox" value="1" id="evertec_use_sandbox" <?php echo $evertec_settings['use_sandbox'] ? 'checked="checked"' : '' ?> /><?php _e('Use the Debugging Feature and the Evertec Sandbox', 'event_espresso'); ?>
							</label>
							</p>
						</li>
						
					</ul>
				<?php if (espresso_check_ssl() == FALSE) {
							
							espresso_ssl_required_gateway_message();
						}?></td>
				<td valign="top"><ul><li>
							<?php $card_payment_methods = array(
	'A'=> __("BPPR ATH", "event_espresso"),
	'V'=>  __("Visa", "event_espresso"),
	'M'=>  __("MasterCard", "event_espresso"),
	'X'=>  __("AMEX", "event_espresso"),
);

$bank_payment_methods = array(
	'W'=>  __("Personal Checking", "event_espresso"),
	'S'=> __("Personal Saving", "event_espresso"),
	'C'=>  __("Business Checking", "event_espresso")
);?>
							<b><?php _e("Credit Card Payment Options", "event_espresso");?></b>
					<?php foreach($card_payment_methods as $card_code => $i18n_name){
						$checked = isset($evertec_settings['accepted_payment_methods'][$card_code]) ? 'checked="checked"' : '';
						?><label for="accepted_payment_method_<?php echo $card_code?>"><input type="checkbox" <?php echo $checked?> name="accepted_payment_methods[<?php echo $card_code?>]" id="accepted_payment_method_<?php echo $card_code?>" value="<?php echo $i18n_name?>"><?php echo $i18n_name?></label><?php 
						}?><b><?php _e("Bank Account Payment Options", "event_espresso");?></b>
					<?php foreach($bank_payment_methods as $card_code => $i18n_name){
						$checked = isset($evertec_settings['accepted_payment_methods'][$card_code]) ? 'checked="checked"' : '';
						?><label for="accepted_payment_method_<?php echo $card_code?>"><input type="checkbox" <?php echo $checked?> name="accepted_payment_methods[<?php echo $card_code?>]" id="accepted_payment_method_<?php echo $card_code?>" value="<?php echo $i18n_name?>"><?php echo $i18n_name?></label><?php 
						}?>
						</li>						
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="button_url" size="34" value="<?php echo $evertec_settings['button_url']; ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li><li>
							<label><?php _e('Current Button Image:', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $evertec_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		
			<input type="hidden" name="update_evertec" value="update_evertec">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Evertec Settings', 'event_espresso') ?>" id="save_evertec_settings" />
		</p>
	</form>
	<div id="evertec_sandbox_info" style="display:none">
		<h2><?php _e('Evertec Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the Evertec Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all Evertec variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The Evertec Sandbox is a testing environment that is a duplicate of the live Evertec site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live Evertec environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="password_info" style="display:none">
		<h2><?php _e('Override Profile-Based Tax', 'event_espresso'); ?></h2>
		<p><?php _e('Overrides any sales taxes that may be applied to all of your Evertec.com payments. These settings can be managed in your Evertec.com Profile > Sales Tax (<a href="https://www.evertec.com/us/cgi-bin/webscr?cmd=_profile-sales-tax" target="_blank">https://www.evertec.com/us/cgi-bin/webscr?cmd=_profile-sales-tax</a>).', 'event_espresso'); ?></p>
		<p><?php _e('Even if you are using your Profile-based tax settings, you may want to set a special tax rate for some of your items (e.g. if it is a event/product that does not require tax).', 'event_espresso'); ?></p>
	</div>
	<?php
	
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_evertec_payment_settings');
