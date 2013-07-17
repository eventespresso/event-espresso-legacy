<?php

function event_espresso_megasoft_payment_settings() {
	global $espresso_premium, $active_gateways;;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_megasoft'])) {
		$megasoft_settings['megasoft_login_id'] = $_POST['megasoft_login_id'];
		$megasoft_settings['header'] = $_POST['header'];
		$megasoft_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$megasoft_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		$megasoft_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_megasoft_settings', $megasoft_settings);
		
		echo '<div id="message" class="updated fade"><p><strong>' . __('Megasoft settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$megasoft_settings = get_option('event_espresso_megasoft_settings');
	if (empty($megasoft_settings)) {
		$megasoft_settings['megasoft_login_id'] = '';
		$megasoft_settings['header'] = 'Payment Transactions by Megasoft';
		$megasoft_settings['use_sandbox'] = false;
		$megasoft_settings['display_header'] = false;
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/megasoft/megasoft-logo.gif")) {
			$megasoft_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/megasoft/megasoft-logo.gif";
		} else {
			$megasoft_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/megasoft/megasoft-logo.gif";
		}
		if (add_option('event_espresso_megasoft_settings', $megasoft_settings, '', 'no') == false) {
			update_option('event_espresso_megasoft_settings', $megasoft_settings);
		}
	}

	if ( ! isset( $megasoft_settings['button_url'] ) || ! file_exists( $megasoft_settings['button_url'] )) {
		$megasoft_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_megasoft'])
					&& (!empty($_REQUEST['activate_megasoft'])
					|| array_key_exists('megasoft', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Megasoft Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (isset($_REQUEST['activate_megasoft']) && $_REQUEST['activate_megasoft'] == 'true') {
						$active_gateways['megasoft'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (isset($_REQUEST['deactivate_megasoft']) && $_REQUEST['deactivate_megasoft'] == 'true') {
						unset($active_gateways['megasoft']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('megasoft', $active_gateways)) {
						echo '<li id="deactivate_megasoft" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_megasoft=true\';" class="red_alert pointer"><strong>' . __('Deactivate Megasoft Gateway?', 'event_espresso') . '</strong></li>';
							event_espresso_display_megasoft_settings();
					} else {
						echo '<li id="activate_megasoft" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_megasoft=true\';" class="green_alert pointer"><strong>' . __('Activate Megasoft Gateway?', 'event_espresso') . '</strong></li>';
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
function event_espresso_display_megasoft_settings() {
	$megasoft_settings = get_option('event_espresso_megasoft_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="megasoft_login_id">
								<?php _e('Affiliation ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="megasoft_login_id" size="35" value="<?php echo $megasoft_settings['megasoft_login_id']; ?>">
						</li>
						<li>
								<label for="button_url">
	<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php $buttonUrl=((!array_key_exists('button_url',$megasoft_settings) || empty($megasoft_settings['button_url'])) ? '' : $megasoft_settings['button_url'] );?>
							<input class="upload_url_input" type="text" name="button_url" id='button_url' size="34" value="<?php echo  $buttonUrl?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a> </li>
						<?php _e('Current Button Image', 'event_espresso'); ?>
						<br />
						<?php// if(!empty($buttonUrl)){?>
							<img src='<?php echo $buttonUrl?>'/>
						<?php //}?>
						</li>
					</ul></td>
				<td valign="top">
					<ul>
						<li>
							<label for="use_sandbox">
								<?php _e('Account Uses Megasoft\'s development Server', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=megasoft_sandbox"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input id="sandbox_checkbox_aim" name="use_sandbox" type="checkbox" value="1" <?php echo $megasoft_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							 </li>
							<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $megasoft_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
							<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="header" size="35" value="<?php echo $megasoft_settings['header']; ?>">
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
			<input type="hidden" name="update_megasoft" value="update_megasoft">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Megasoft Settings', 'event_espresso') ?>" id="save_megasoft_settings" />
		</p>
	</form>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_megasoft_payment_settings');
