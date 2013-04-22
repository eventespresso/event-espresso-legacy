<?php

function event_espresso_infusionsoft_payment_settings() {
	global $espresso_premium, $active_gateways;;
	if (!$espresso_premium)
		return;

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_infusionsoft_payment'])
					&& (!empty($_REQUEST['activate_infusionsoft_payment'])
					|| array_key_exists('infusionsoft_payment', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Infusionsoft Payment Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (isset($_REQUEST['activate_infusionsoft_payment']) && $_REQUEST['activate_infusionsoft_payment'] == 'true') {
						$active_gateways['infusionsoft_payment'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (isset($_REQUEST['deactivate_infusionsoft_payment']) && $_REQUEST['deactivate_infusionsoft_payment'] == 'true') {
						unset($active_gateways['infusionsoft_payment']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('infusionsoft_payment', $active_gateways)) {
						echo '<li id="deactivate_infusionsoft_payment" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_infusionsoft_payment=true\';" class="red_alert pointer"><strong>' . __('Deactivate Infusionsoft Payment Gateway?', 'event_espresso') . '</strong></li>';
							event_espresso_display_infusionsoft_payment_settings();
					} else {
						echo '<li id="activate_infusionsoft_payment" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_infusionsoft_payment=true\';" class="green_alert pointer"><strong>' . __('Activate Infusionsoft Payment Gateway?', 'event_espresso') . '</strong></li>';
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
function event_espresso_display_infusionsoft_payment_settings() {
	if (function_exists('espresso_infusionsoft_version')){	
	?>
	Please go here to set manage your Infusionsoft Settings...
	<?php
	}
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_infusionsoft_payment_settings');
