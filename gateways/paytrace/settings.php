<?php

function event_espresso_paytrace_payment_settings() {
	if (isset($_POST['update_paytrace']) && check_admin_referer('espresso_form_check', 'add_paytrace_settings')) {
		
		$paytrace_settings = array();#get_option('event_espresso_paytrace_settings');
		$paytrace_settings['paytrace_user_id'] = $_POST['paytrace_user_id'];
		$paytrace_settings['paytrace_user_pass'] = $_POST['paytrace_user_pass'];
		update_option('event_espresso_paytrace_settings', $paytrace_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Paytrace settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	?>

<div class="metabox-holder">
	<div class="postbox">
		<div title="Click to toggle" class="handlediv"><br />
		</div>
		<h3 class="hndle">
			<?php _e('Paytrace Settings', 'event_espresso'); ?>
		</h3>
		<div class="inside">
			<div class="padding">
				<?php
				if (isset($_REQUEST['activate_paytrace'])&&$_REQUEST['activate_paytrace'] == 'true') {
					add_option("events_paytrace_active", 'true', '', 'yes');
					add_option("event_espresso_paytrace_settings", '', '', 'yes');
				}
				if (isset($_REQUEST['reactivate_paytrace'])&&$_REQUEST['reactivate_paytrace'] == 'true') {
					update_option('events_paytrace_active', 'true');
				}
				if (isset($_REQUEST['deactivate_paytrace'])&&$_REQUEST['deactivate_paytrace'] == 'true') {
					update_option('events_paytrace_active', 'false');
				}
				echo '<ul>';
				switch (get_option('events_paytrace_active')) {
					case 'false':
						echo '<li>Paytrace Gateway is installed.</li>';
						echo '<li style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&reactivate_paytrace=true\';" class="green_alert pointer"><strong>' . __('Activate Paytrace?', 'event_espresso') . '</strong></li>';
						break;
					case 'true':
						echo '<li style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_paytrace=true\';" class="red_alert pointer"><strong>' . __('Deactivate Paytrace?', 'event_espresso') . '</strong></li>';
						event_espresso_display_paytrace_settings();

						break;
					default:
						echo '<li style="width:50%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_paytrace=true\';" class="yellow_alert pointer"><strong>' . __('The Paytrace addon is installed. Would you like to activate it?', 'event_espresso') . '</strong></li>';
						break;
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
	<table width="99%" border="0" >
		<tr>
			<td valign="top"><ul>
					<li>
						<label for="paytrace_user_id">
							<?php _e('Paytrace User ID:', 'event_espresso'); ?>
						</label>
						<br />
						<input class="regular-text" type="text" name="paytrace_user_id" size="35" value="<?php echo $paytrace_settings['paytrace_user_id']; ?>">
					</li>
					<li>
						<label for="paytrace_user_pass">
							<?php _e('Paytrace User Password:', 'event_espresso'); ?>
						</label>
						<br />
						<input class="regular-text" type="text" name="paytrace_user_pass" size="35" value="<?php echo $paytrace_settings['paytrace_user_pass']; ?>">
					</li>
				</ul></td>
		</tr>
	</table>
	<p>
		<input type="hidden" name="update_paytrace" value="update_paytrace">
		<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Paytrace Settings', 'event_espresso') ?>" id="save_paytrace_settings" />
	</p>
	<?php wp_nonce_field( 'espresso_form_check', 'add_paytrace_settings' ); ?>
</form>
<?php
}
