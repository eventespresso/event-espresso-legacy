<?php

function event_espresso_exact_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_exact'])) {
		$exact_settings['exact_login_id'] = stripslashes_deep($_POST['exact_login_id']);
		$exact_settings['exact_transaction_key'] = stripslashes_deep($_POST['exact_transaction_key']);
		$exact_settings['image_url'] = $_POST['image_url'];
		$exact_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$exact_settings['test_transactions'] = empty($_POST['test_transactions']) ? false : true;
		$exact_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$exact_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$exact_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_exact_settings', $exact_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('E-xact settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$exact_settings = get_option('event_espresso_exact_settings');
	if (empty($exact_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/exact/exact-logo.png")) {
			$exact_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/exact/exact-logo.png";
		} else {
			$exact_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/exact/exact-logo.png";
		}
		$exact_settings['exact_login_id'] = '';
		$exact_settings['exact_transaction_key'] = '';
		$exact_settings['image_url'] = '';
		$exact_settings['use_sandbox'] = false;
		$exact_settings['test_transactions'] = false;
		$exact_settings['bypass_payment_page'] = 'N';
		$exact_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_exact_settings', $exact_settings, '', 'no') == false) {
			update_option('event_espresso_exact_settings', $exact_settings);
		}
	}

	if ( ! isset( $exact_settings['button_url'] ) || ! file_exists( $exact_settings['button_url'] )) {
		$exact_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_exact'])
					&& (!empty($_REQUEST['activate_exact'])
					|| array_key_exists('exact', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('E-xact Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_exact'])) {
						$active_gateways['exact'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_exact'])) {
						unset($active_gateways['exact']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('exact', $active_gateways)) {
						echo '<li id="deactivate_exact" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_exact=true\';" class="red_alert pointer"><strong>' . __('Deactivate E-xact Gateway?', 'event_espresso') . '</strong></li>';
							event_espresso_display_exact_settings();
					} else {
						echo '<li id="activate_exact" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_exact=true\';" class="green_alert pointer"><strong>' . __('Activate E-xact Gateway?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//E-xact Settings Form
function event_espresso_display_exact_settings() {
	$exact_settings = get_option('event_espresso_exact_settings');
	$org_options = get_option('events_organization_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="exact_login_id">
								<?php _e('E-xact Login ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="exact_login_id" size="35" value="<?php echo $exact_settings['exact_login_id']; ?>">
						</li>
						<li>
							<label for="exact_transaction_key">
								<?php _e('E-xact Transaction Key', 'event_espresso'); ?>
							</label>
							<input type="text" name="exact_transaction_key" size="35" value="<?php echo $exact_settings['exact_transaction_key']; ?>">
						</li>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="35" value="<?php echo (isset($exact_settings['button_url']) ? $exact_settings['button_url'] : ''  ); ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
							</li>
						<li>
							<label for="image_url">
								<?php _e('Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=exact_image_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="image_url" size="35" value="<?php echo $exact_settings['image_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
							<br />
							<?php _e('(used for your business/personal logo on the E-xact payment page)', 'event_espresso'); ?>
						</li>
					</ul></td>
				<td valign="top"><ul>
						<li>
							<label><?php _e('Relay Response URL', 'event_espresso'); ?></label>
							<span class="display-path" style="background-color: rgb(255, 251, 204); border:#999 solid 1px; padding:2px;"><?php
							if($exact_settings['force_ssl_return']) {
								echo str_replace("http://", "https://", home_url() . '/?type=exact&page_id=' . $org_options['return_url']);
							} else {
								echo home_url() . '/?type=exact&page_id=' . $org_options['return_url'];
							}
?></span> &nbsp;<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=exact_relay_response"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a> </li>
						<li>
							<label for="use_sandbox">
								<?php _e('Account Uses E-xact.com\'s Development Server', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=exact_sandbox"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $exact_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							</li>
						<li>
							<label for="test_transactions">
								<?php _e('Submit a Test Transaction', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=exact_sandbox"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="test_transactions" type="checkbox" value="1" <?php echo $exact_settings['test_transactions'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')),
									array('id' => 'N', 'text' => __('No', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $exact_settings['bypass_payment_page']);
							?>
							 </li>
						<?php if (espresso_check_ssl() == TRUE || ( isset($exact_settings['force_ssl_return']) && $exact_settings['force_ssl_return'] == 1 )) {?>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $exact_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $exact_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_exact" value="update_exact">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update E-xact Settings', 'event_espresso') ?>" id="save_exact_settings" />
		</p>
	</form>
	<div id="exact_relay_response" style="display:none">
		<h2><?php _e('Relay Response', 'event_espresso'); ?></h2>
		<p><?php _e('This shows the specific the URL to which the gateway should return the relay response for a transaction. This the page should be set in your E-xact account. Login to E-xact, goto Account > Response/Receipt URLs > Add URL and enter the following URL.', 'event_espresso'); ?></p>
		<p><strong><?php _e('Relay Response URL:', 'event_espresso'); ?></strong> <?php echo home_url() . '/?page_id=' . $org_options['return_url'] ?><br />
			<span style="color:red;"><?php _e('Note:', 'event_espresso'); ?></span> <?php _e('This URL can be changed in the "Organization Settings" page.', 'event_espresso'); ?></p>
		<p><?php _e('For complete information on configuring relay response, please refer to', 'event_espresso'); ?> <a href="https://hostedcheckout.zendesk.com/entries/234989-Where-do-I-enter-my-Relay-Response-URL-"><?php _e('Reference &amp; User Guides', 'event_espresso'); ?></a>.</p>
	</div>
	<div id="exact_image_url_info" style="display:none">
		<h2>
			<?php _e('E-xact Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the image displayed as your logo in the header of the Authorize.net checkout pages.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="exact_sandbox" style="display:none">
		<h2><?php _e('E-xact Test Mode', 'event_espresso'); ?></h2>
		<p><?php _e('Test Mode allows you to submit test transactions to the payment gateway. Transactions that are submitted while Test Mode is ON are NOT actually processed. The result of a transaction depends on the card number submitted, and the invoice amount. If you want a transaction to be approved, use one of the following card numbers.', 'event_espresso'); ?></p><p>370000000000002 (<?php _e('American Express', 'event_espresso'); ?>)<br />6011000000000012 (<?php _e('Discover', 'event_espresso'); ?>)<br />5424000000000015 (<?php _e('Master Card', 'event_espresso'); ?>)<br />4007000000027 (<?php _e('Visa', 'event_espresso'); ?>)</p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_exact_payment_settings');
