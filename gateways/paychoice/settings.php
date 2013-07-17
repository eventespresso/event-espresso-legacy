<?php

function event_espresso_paychoice_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_paychoice'])) {
		$paychoice_settings['paychoice_username'] = $_POST['paychoice_username'];
		$paychoice_settings['paychoice_password'] = $_POST['paychoice_password'];
		$paychoice_settings['paychoice_currency_symbol'] = $_POST['paychoice_currency_symbol'];
		$paychoice_settings['header'] = $_POST['header'];
		$paychoice_settings['use_sandbox'] = $_POST['use_sandbox'];
		$paychoice_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$paychoice_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('event_espresso_paychoice_settings', $paychoice_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('PayChoice settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$paychoice_settings = get_option('event_espresso_paychoice_settings');
	if (empty($paychoice_settings)) {
		$paychoice_settings['paychoice_username'] = '';
		$paychoice_settings['paychoice_password'] = '';
		$paychoice_settings['paychoice_currency_symbol'] = 'AUD';
		$paychoice_settings['header'] = 'Payment Transactions by PayChoice';
		$paychoice_settings['use_sandbox'] = false;
		$paychoice_settings['force_ssl_return'] = false;
		$paychoice_settings['display_header'] = false;
		if (add_option('event_espresso_paychoice_settings', $paychoice_settings, '', 'no') == false) {
			update_option('event_espresso_paychoice_settings', $paychoice_settings);
		}
	}

	if ( ! isset( $paychoice_settings['button_url'] ) || ! file_exists( $paychoice_settings['button_url'] )) {
		$paychoice_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_paychoice'])
					&& (!empty($_REQUEST['activate_paychoice'])
					|| array_key_exists('paychoice', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('PayChoice Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_paychoice'])) {
						$active_gateways['paychoice'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_paychoice'])) {
						unset($active_gateways['paychoice']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('paychoice', $active_gateways)) {
						echo '<li id="deactivate_paychoice" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_paychoice=true\';" class="red_alert pointer"><strong>' . __('Deactivate PayChoice?', 'event_espresso') . '</strong></li>';
						event_espresso_display_paychoice_settings();
					} else {
						echo '<li id="activate_paychoice" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_paychoice=true\';" class="green_alert pointer"><strong>' . __('Activate PayChoice?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//PayChoice Settings Form
function event_espresso_display_paychoice_settings() {
	$paychoice_settings = get_option('event_espresso_paychoice_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="paychoice_password">
								<?php _e('PayChoice Username', 'event_espresso'); ?>
							</label>
							<input type="text" name="paychoice_username" size="35" value="<?php echo $paychoice_settings['paychoice_username']; ?>">
						</li>
						<li>
							<label for="paychoice_username">
								<?php _e('PayChoice Password', 'event_espresso'); ?>
							</label>
							<input type="text" name="paychoice_password" size="35" value="<?php echo $paychoice_settings['paychoice_password']; ?>">
						</li>
						<li>
							<label for="paychoice_currency_symbol">
								<?php _e('PayChoice Currency Symbol (AUD)', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=paychoice_currency_symbol"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="paychoice_currency_symbol" size="35" value="<?php echo $paychoice_settings['paychoice_currency_symbol']; ?>">
						</li>
						<li>
							<label for="use_sandbox">
								<?php _e('Use the Test Mode for PayChoice', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=paychoice_sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $paychoice_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
						</li>						
					<?php if (espresso_check_ssl() == TRUE || ( isset($paychoice_settings['force_ssl_return']) && $paychoice_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $paychoice_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
							<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $paychoice_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
							<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $paychoice_settings['header']; ?>">
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
			<input type="hidden" name="update_paychoice" value="update_paychoice">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update PayChoice Settings', 'event_espresso') ?>" id="save_paychoice_settings" />
		</p>
	</form>
	<div id="paychoice_currency_symbol" style="display:none">
		<h2>
			<?php _e('PayChoice Currency Symbol', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('PayChoice uses 3-character ISO-4217 codes for specifying currencies in fields and variables.  If you are taking purchases in US Dollars, enter <code>AUD</code> here.  PayChoice currently only takes payment in AUD, but can accept payments from any currency which will be converted to AUD at checkout.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="paychoice_sandbox_info" style="display:none">
		<h2><?php _e('PayChoice Test Mode', 'event_espresso'); ?></h2>
		<p><?php _e('Test Mode allows you to submit test transactions to the payment gateway. This allows you to test your entire integration before submitting transactions to the live PayChoice environment. ', 'event_espresso'); ?></p>
	</div>	
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_paychoice_payment_settings');