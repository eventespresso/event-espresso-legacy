<?php

function event_espresso_evertec_payment_settings() {
	global $active_gateways;
	if (isset($_POST['update_evertec'])) {
		$evertec_settings['username'] = $_POST['username'];
		$evertec_settings['password'] = $_POST['password'];
		$evertec_settings['evertec_pages_language'] = $_POST['evertec_pages_language'];
		$evertec_settings['image_url'] = $_POST['image_url'];
		$evertec_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$evertec_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$evertec_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$evertec_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_evertec_settings', $evertec_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Evertec settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$evertec_settings = get_option('event_espresso_evertec_settings');
	if (empty($evertec_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/evertec/btn_stdCheckout2.gif")) {
			$evertec_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/evertec/btn_stdCheckout2.gif";
		} else {
			$evertec_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/evertec/btn_stdCheckout2.gif";
		}
		$evertec_settings['username'] = '';
		$evertec_settings['password'] = '';
		$evertec_settings['evertec_pages_language'] = 'es';
		$evertec_settings['image_url'] = '';
		$evertec_settings['use_sandbox'] = false;
		$evertec_settings['bypass_payment_page'] = 'N';
		$evertec_settings['force_ssl_return'] = false;
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
							<label for="evertec_pages_language">
								<?php _e("Evertec Payment Pages' Language", 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=evertec_pages_language"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php 
							$language_options = array(array('id'=>'es','text'=>  __("Spanish", "event_espresso")),array('id'=>'en','text'=>  __("English", "event_espresso")));
							echo select_input('evertec_pages_language', $language_options, $evertec_settings['evertec_pages_language']); ?>
						</li>
						
						<li>
							<label for="image_url">
								<?php _e('Image URL (logo for payment page)', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=image_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="image_url" size="35" value="<?php echo $evertec_settings['image_url']; ?>" />
							<a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=image_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a><br />
							<?php _e('(used for your business/personal logo on the Evertec page)', 'event_espresso'); ?>
						</li>
						
						
					</ul></td>
				<td valign="top"><ul><li>
						<label for="bypass_payment_page">
							<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
						</label>
						<?php
						$values = array(
								array('id' => 'N', 'text' => __('No', 'event_espresso')),
								array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
						echo select_input('bypass_payment_page', $values, $evertec_settings['bypass_payment_page']);
						?>
						</li>
						
						<?php if (espresso_check_ssl() == TRUE || ( isset($evertec_settings['force_ssl_return']) && $evertec_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $evertec_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						
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
<!--		<p><label for="use_sandbox">
								<?php _e('Use the Debugging Feature and the', 'event_espresso'); ?> <a href="https://developer.evertec.com/devscr?cmd=_home||https://cms.evertec.com/us/cgi-bin/?&amp;cmd=_render-content&amp;content_ID=developer/howto_testing_sandbox||https://cms.evertec.com/us/cgi-bin/?&amp;cmd=_render-content&amp;content_ID=developer/howto_testing_sandbox_get_started" title="Evertec Sandbox Login||Sandbox Tutorial||Getting Started with Evertec Sandbox" target="_blank"><?php _e('Evertec Sandbox', 'event_espresso'); ?></a><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=evertec_sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $evertec_settings['use_sandbox'] ? 'checked="checked"' : '' ?> /></p>-->
	</form>
	<div id="evertec_sandbox_info" style="display:none">
		<h2><?php _e('Evertec Sandbox', 'event_espresso'); ?></h2>
		<p><?php _e('In addition to using the Evertec Sandbox feature. The debugging feature will also output the form variables to the payment page, send an email to the admin that contains the all Evertec variables.', 'event_espresso'); ?></p>
		<hr />
		<p><?php _e('The Evertec Sandbox is a testing environment that is a duplicate of the live Evertec site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live Evertec environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
	</div>
	<div id="image_url_info" style="display:none">
		<h2>
			<?php _e('Evertec Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the Evertec checkout pages.', 'event_espresso'); ?>
		</p>
		<p>
			<?php _e('Default - Your business name, if you have a Business account, or your email address, if you have Premier or Personal account.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="evertec_pages_language" style="display:none">
		<h2><?php _e("Evertec Pages Language", "event_espresso"); ?></h2>
		<p><?php _e("When users are directed to Evertec's payment pages, these pages may be displayed in either Spanish or English.", 'event_espresso'); ?> </p>
	</div>
	<div id="password_info" style="display:none">
		<h2><?php _e('Override Profile-Based Tax', 'event_espresso'); ?></h2>
		<p><?php _e('Overrides any sales taxes that may be applied to all of your Evertec.com payments. These settings can be managed in your Evertec.com Profile > Sales Tax (<a href="https://www.evertec.com/us/cgi-bin/webscr?cmd=_profile-sales-tax" target="_blank">https://www.evertec.com/us/cgi-bin/webscr?cmd=_profile-sales-tax</a>).', 'event_espresso'); ?></p>
		<p><?php _e('Even if you are using your Profile-based tax settings, you may want to set a special tax rate for some of your items (e.g. if it is a event/product that does not require tax).', 'event_espresso'); ?></p>
	</div>
	<div id="evertec_pages_language_info" style="display:none">
		<h2><?php _e('Override Profile-Based Shipping', 'event_espresso'); ?></h2>
		<p><?php _e('Overrides any shipping charges that may be applied to all of your Evertec.com payments. These settings can be managed in your Evertec.com Profile > Shipping Calculations  (<a href="https://www.evertec.com/cgi-bin/customerprofileweb?cmd=_profile-shipping" target="_blank">https://www.evertec.com/cgi-bin/customerprofileweb?cmd=_profile-shipping</a>).', 'event_espresso'); ?></p>
	</div>
	<?php
	
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_evertec_payment_settings');
