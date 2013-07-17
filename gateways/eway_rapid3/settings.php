<?php

function event_espresso_eway_rapid3_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_eway_rapid3'])) {
		$eway_rapid3_settings['eway_rapid3_api_key'] = $_POST['eway_rapid3_api_key'];
		$eway_rapid3_settings['eway_rapid3_api_username'] = $_POST['eway_rapid3_api_username'];
		$eway_rapid3_settings['eway_rapid3_api_password'] = $_POST['eway_rapid3_api_password'];
		//$eway_rapid3_settings['eway_rapid3_api_signature'] = $_POST['eway_rapid3_api_signature'];
		$eway_rapid3_settings['eway_rapid3_api_credit_cards'] = implode(",", empty($_POST['eway_rapid3_api_credit_cards']) ? array() : $_POST['eway_rapid3_api_credit_cards']);
		$eway_rapid3_settings['eway_rapid3_use_sandbox'] = empty($_POST['eway_rapid3_use_sandbox']) ? false : true;
		$eway_rapid3_settings['region'] = $_POST['region'];
		$eway_rapid3_settings['header'] = $_POST['header'];
		$eway_rapid3_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$eway_rapid3_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		switch ($_POST['region']) {
			case 'UK':
				$eway_rapid3_settings['currency_format'] = 'GBP';
				break;
			case 'AU':
				$eway_rapid3_settings['currency_format'] = 'AUD';
				break;
			case 'NZ':
				$eway_rapid3_settings['currency_format'] = 'NZD';
				break;
		}
		update_option('event_espresso_eway_rapid3_settings', $eway_rapid3_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Eway Rapid 3.0 Settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$eway_rapid3_settings = get_option('event_espresso_eway_rapid3_settings');
	if (empty($eway_rapid3_settings)) {
		$eway_rapid3_settings['eway_rapid3_api_key'] = '';
		$eway_rapid3_settings['eway_rapid3_api_username'] = '';
		$eway_rapid3_settings['eway_rapid3_api_password'] = '';
		$eway_rapid3_settings['currency_format'] = 'AUD';
		//$eway_rapid3_settings['eway_rapid3_api_signature'] = '';
		$eway_rapid3_settings['eway_rapid3_api_credit_cards'] = '';
		$eway_rapid3_settings['eway_rapid3_use_sandbox'] = false;
		$eway_rapid3_settings['header'] = 'Payment Transactions by Eway Rapid 3.0 Payments Pro';
		$eway_rapid3_settings['force_ssl_return'] = false;
		$eway_rapid3_settings['display_header'] = false;
		if (add_option('event_espresso_eway_rapid3_settings', $eway_rapid3_settings, '', 'no') == false) {
			update_option('event_espresso_eway_rapid3_settings', $eway_rapid3_settings);
		}
	}

	if ( ! isset( $eway_rapid3_settings['button_url'] ) || ! file_exists( $eway_rapid3_settings['button_url'] )) {
		$eway_rapid3_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_eway_rapid3'])
					&& (!empty($_REQUEST['activate_eway_rapid3'])
					|| array_key_exists('eway_rapid3', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Eway Rapid 3.0 Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_eway_rapid3'])) {
						$active_gateways['eway_rapid3'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_eway_rapid3'])) {
						unset($active_gateways['eway_rapid3']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('eway_rapid3', $active_gateways)) {
						echo '<li id="deactivate_eway_rapid3" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_eway_rapid3=true\';" class="red_alert pointer"><strong>' . __('Deactivate Eway Rapid 3.0 Payments Pro?', 'event_espresso') . '</strong></li>';
						event_espresso_display_eway_rapid3_settings();
					} else {
						echo '<li id="activate_eway_rapid3" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_eway_rapid3=true\';" class="green_alert pointer"><strong>' . __('Activate Eway Rapid 3.0 Payments Pro?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Eway Rapid 3.0 Settings Form
function event_espresso_display_eway_rapid3_settings() {
	$eway_rapid3_settings = get_option('event_espresso_eway_rapid3_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="eway_rapid3_api_key">
								<?php _e("Eway Rapid 3.0 API Key (viewable in MyEway Business Centre under 'Manage Users')", 'event_espresso'); ?>
							</label>
							<input type="text" name="eway_rapid3_api_key" size="35" value="<?php echo $eway_rapid3_settings['eway_rapid3_api_key']; ?>">
						</li>
						<li>
							<label for="eway_rapid3_api_username">
								<?php _e('Eway Rapid 3.0 API Username', 'event_espresso'); ?>
							</label>
							<input type="text" name="eway_rapid3_api_username" size="35" value="<?php echo $eway_rapid3_settings['eway_rapid3_api_username']; ?>">
						</li>
						<li>
							<label for="eway_rapid3_api_password">
								<?php _e('Eway Rapid 3.0 API Password', 'event_espresso'); ?>
							</label>
							<input type="password" name="eway_rapid3_api_password" size="35" value="<?php echo $eway_rapid3_settings['eway_rapid3_api_password']; ?>">
						</li>
						<!--<li>
							<label for="eway_rapid3_api_signature">
								<?php _e('Eway Rapid 3.0 API Signature', 'event_espresso'); ?>
							</label>
							<input type="text" name="eway_rapid3_api_signature" size="35" value="<?php echo $eway_rapid3_settings['eway_rapid3_api_signature']; ?>">
							<br />

						</li>-->
						<li>
							<label for="region">
								<?php _e('Choose Your Region', 'event_espresso'); ?>
							</label>
							<select name="region">
								<?php $regionOptions=array('AU'=>'Australia','NZ'=>'New Zealand','UK'=>'United Kingdom');
								foreach($regionOptions as $regionAbbreviation=>$regionName){?>
								<option value='<?php echo $regionAbbreviation?>' <?php if(array_key_exists('region',$eway_rapid3_settings) && $regionAbbreviation==$eway_rapid3_settings['region']) echo 'selected'?>>
									<?php _e($regionName,'event_espresso')?>
								</option>
								<?php
								}?>
							
							</select>
						</li>
						<li>
							<label for="currency_format">
								<?php _e('The currency set for your region is', 'event_espresso'); ?>
							</label>
							<span class="display-path" style="background-color: rgb(255, 251, 204); border:#999 solid 1px; padding:2px;"><?php echo $eway_rapid3_settings['currency_format']; ?></span>
							<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a> </li>
						
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label for="eway_rapid3_use_sandbox">
								<?php _e('Use Eway Rapid 3.0in Sandbox Mode', 'event_espresso'); ?>
							</label>
							<input name="eway_rapid3_use_sandbox" type="checkbox" value="1" <?php echo $eway_rapid3_settings['eway_rapid3_use_sandbox'] ? 'checked="checked"' : '' ?> />
							<br />
							<?php _e('Note: Sandbox mode only works for AUD as currency. If you are using Sandbox mode, ensure you are using Sandbox credentials.', 'event_espresso'); ?>
						</li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($eway_rapid3_settings['force_ssl_return']) && $eway_rapid3_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $eway_rapid3_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> />
						</li>
						<?php }?>
						<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $eway_rapid3_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $eway_rapid3_settings['header']; ?>">
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
			<input type="hidden" name="update_eway_rapid3" value="update_eway_rapid3">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Eway Rapid 3.0 Settings', 'event_espresso') ?>" id="save_eway_rapid3_settings" />
		</p>
	</form>
	<div id="eway_rapid3_sandbox_info" style="display:none">
		<h2><?php _e('Eway Rapid 3.0 Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the Eway Rapid 3.0 Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all Eway Rapid 3.0 variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The Eway Rapid 3.0 Sandbox is a testing environment that is a duplicate of the live Eway Rapid 3.0 site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live Eway Rapid 3.0 environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="currency_info" style="display:none">
		<h2><?php _e('Eway Rapid 3.0 Currency', 'event_espresso'); ?></h2>
		<p><?php _e('There are currently 3 currencies accepted by Eway Rapid 3.0: GBP (United Kingdom), AUD (Australia), NZD (New Zealand). To change the corrency, change the region in your Eway Rapid 3.0 settings.', 'event_espresso'); ?> </p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_eway_rapid3_payment_settings');
