<?php

function event_espresso_firstdata_e4_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_firstdata_e4'])) {
		$firstdata_e4_settings['firstdata_e4_login_id'] = $_POST['firstdata_e4_login_id'];
		$firstdata_e4_settings['firstdata_e4_transaction_key'] = $_POST['firstdata_e4_transaction_key'];
		$firstdata_e4_settings['image_url'] = $_POST['image_url'];
		$firstdata_e4_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
		$firstdata_e4_settings['test_transactions'] = empty($_POST['test_transactions']) ? false : true;
		$firstdata_e4_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
		$firstdata_e4_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$firstdata_e4_settings['button_url'] = $_POST['button_url'];
		update_option('event_espresso_firstdata_e4_settings', $firstdata_e4_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('FirstData E4 settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$firstdata_e4_settings = get_option('event_espresso_firstdata_e4_settings');
	if (empty($firstdata_e4_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata_e4/firstdata-logo.png")) {
			$firstdata_e4_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/firstdata_e4/firstdata-logo.png";
		} else {
			$firstdata_e4_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/firstdata_e4/firstdata-logo.png";
		}
		$firstdata_e4_settings['firstdata_e4_login_id'] = '';
		$firstdata_e4_settings['firstdata_e4_transaction_key'] = '';
		$firstdata_e4_settings['image_url'] = '';
		$firstdata_e4_settings['use_sandbox'] = false;
		$firstdata_e4_settings['test_transactions'] = false;
		$firstdata_e4_settings['bypass_payment_page'] = 'N';
		$firstdata_e4_settings['force_ssl_return'] = false;
		if (add_option('event_espresso_firstdata_e4_settings', $firstdata_e4_settings, '', 'no') == false) {
			update_option('event_espresso_firstdata_e4_settings', $firstdata_e4_settings);
		}
	}

	if ( ! isset( $firstdata_e4_settings['button_url'] ) || ! file_exists( $firstdata_e4_settings['button_url'] )) {
		$firstdata_e4_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_firstdata_e4'])
					&& (!empty($_REQUEST['activate_firstdata_e4'])
					|| array_key_exists('firstdata_e4', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('FirstData E4 Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_firstdata_e4'])) {
						$active_gateways['firstdata_e4'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_firstdata_e4'])) {
						unset($active_gateways['firstdata_e4']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('firstdata_e4', $active_gateways)) {
						echo '<li id="deactivate_firstdata_e4" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_firstdata_e4=true\';" class="red_alert pointer"><strong>' . __('Deactivate FirstData E4 Gateway?', 'event_espresso') . '</strong></li>';
							event_espresso_display_firstdata_e4_settings();
					} else {
						echo '<li id="activate_firstdata_e4" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_firstdata_e4=true\';" class="green_alert pointer"><strong>' . __('Activate FirstData E4 Gateway?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//FirstData E4 Settings Form
function event_espresso_display_firstdata_e4_settings() {
	$firstdata_e4_settings = get_option('event_espresso_firstdata_e4_settings');
	$org_options = get_option('events_organization_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top"><ul>
						<li>
							<label for="firstdata_e4_login_id">
								<?php _e('FirstData E4 Payment Page ID', 'event_espresso'); ?>
							</label>
							<input type="text" name="firstdata_e4_login_id" size="35" value="<?php echo $firstdata_e4_settings['firstdata_e4_login_id']; ?>">
						</li>
						<li>
							<label for="firstdata_e4_transaction_key">
								<?php _e('FirstData E4 Transaction Key', 'event_espresso'); ?>
							</label>
							<input type="text" name="firstdata_e4_transaction_key" size="35" value="<?php echo $firstdata_e4_settings['firstdata_e4_transaction_key']; ?>">
						</li>
						<li>
							<label for="button_url">
								<?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="button_url" size="35" value="<?php echo (isset($firstdata_e4_settings['button_url']) ? $firstdata_e4_settings['button_url'] : '' ); ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
							</li>
						<li>
							<label for="image_url">
								<?php _e('Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=firstdata_e4_image_url_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="upload_url_input" type="text" name="image_url" size="35" value="<?php echo $firstdata_e4_settings['image_url']; ?>" />
							<a class="upload_image_button" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>
							<br />
							<?php _e('(used for your business/personal logo on the FirstData E4 payment page)', 'event_espresso'); ?>
						</li>
					</ul></td>
				<td valign="top"><ul>
						<li>
							<label><?php _e('Relay Response URL', 'event_espresso'); ?></label>
							<span class="display-path" style="background-color: rgb(255, 251, 204); border:#999 solid 1px; padding:2px;"><?php
							if($firstdata_e4_settings['force_ssl_return']) {
								echo str_replace("http://", "https://", home_url() . '/?type=firstdata_e4&page_id=' . $org_options['return_url']);
							} else {
								echo home_url() . '/?type=firstdata_e4&page_id=' . $org_options['return_url'];
							}
?></span> &nbsp;<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=first_data_e4_relay_response"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a> </li>
						<li>
							<label for="use_sandbox">
								<?php _e('Account Uses FirstData E4.com\'s Demo Server', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=firstdata_e4_sandbox"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="use_sandbox" type="checkbox" value="1" <?php echo $firstdata_e4_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
							</li>
						<li>
							<label for="test_transactions">
								<?php _e('Payment Page in Test Mode', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=firstdata_e4_sandbox"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="test_transactions" type="checkbox" value="1" <?php echo $firstdata_e4_settings['test_transactions'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="bypass_payment_page">
								<?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<?php
							$values = array(
									array('id' => 'Y', 'text' => __('Yes', 'event_espresso')),
									array('id' => 'N', 'text' => __('No', 'event_espresso')));
							echo select_input('bypass_payment_page', $values, $firstdata_e4_settings['bypass_payment_page']);
							?>
							 </li>
						<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $firstdata_e4_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label><?php _e('Current Button Image', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $firstdata_e4_settings['button_url'] . '" />'; ?></li>
					</ul></td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_firstdata_e4" value="update_firstdata_e4">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update FirstData E4 Settings', 'event_espresso') ?>" id="save_firstdata_e4_settings" />
		</p>
	</form>
	<div id="first_data_e4_relay_response" style="display:none">
		<h2><?php _e('Relay Response', 'event_espresso'); ?></h2>
		<p><?php _e('This shows the specific the URL to which the gateway should return the relay response for a transaction. This the page should be set in your FirstData E4 account. Login to FirstData E4, goto Account > Response/Receipt URLs > Add URL and enter the following URL.', 'event_espresso'); ?></p>
		<p><strong><?php _e('Relay Response URL:', 'event_espresso'); ?></strong> <?php echo home_url() . '/?page_id=' . $org_options['return_url'] ?><br />
			<span style="color:red;"><?php _e('Note:', 'event_espresso'); ?></span> <?php _e('This URL can be changed in the "Organization Settings" page.', 'event_espresso'); ?></p>
		<p><?php _e('For complete information on configuring relay response, please refer to', 'event_espresso'); ?> <a href="https://firstdata.zendesk.com/entries/407673-Where-do-I-enter-my-Relay-Response-URL-"><?php _e('Reference &amp; User Guides', 'event_espresso'); ?></a>.</p>
	</div>
	<div id="firstdata_e4_image_url_info" style="display:none">
		<h2>
			<?php _e('FirstData E4 Image URL (logo for payment page)', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('The URL of the image displayed as your logo in the header of the Authorize.net checkout pages.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="firstdata_e4_sandbox" style="display:none">
		<h2><?php _e('FirstData E4 Test Mode', 'event_espresso'); ?></h2>
		<p><?php _e("There are two options for testing in First Data e4:","event_espresso");?></p>
		<ol>
			<li><a href='https://firstdata.zendesk.com/entries/21510561-global-gateway-e4sm-demo-accounts' target='_blank'><?php _e("using the Demo site, (option 1)","event_espresso")?></a></li>
			<li><a href='https://firstdata.zendesk.com/entries/407522#4.2' target='_blank'><?php _e("using the Live site but setting the Payment Page to Test mode, (option 2)","event_espresso")?></a></li>
		</ol>
		<p><?php _e("If you login at demo.globalgatewaye4.firstdata.com, then your account is on the Demo site (option 1). If, however, you login at globalgatewaye4.firstdata.com, then your account is on the Live site, and you will want to test with option 2.","event_espresso");?></p>
		<p><?php _e("If you are using the Demo site (option 1), then check 'Account Uses FirstData E4.com's Demo Server', and leave 'Payment Page in Test Mode' <b>unchecked</b>.",'event_espresso');?></p>
		<p><?php _e("If you using the Live site (option 2, most common), then leave 'Account Uses Firstdata E3.com's Demo Server' <b>unchecked</b>, and check 'Payment Page in Test Mode'.",'event_espresso');?></p>
		<p><?php _e('Test Mode allows you to submit test transactions to the payment gateway. Transactions that are submitted while Test Mode is ON are NOT actually processed. The result of a transaction depends on the card number submitted, and the invoice amount. If you want a transaction to be approved, use one of the following card numbers.', 'event_espresso'); ?></p><p>370000000000002 (<?php _e('American Express', 'event_espresso'); ?>)<br />6011000000000012 (<?php _e('Discover', 'event_espresso'); ?>)<br />5424000000000015 (<?php _e('Master Card', 'event_espresso'); ?>)<br />4007000000027 (<?php _e('Visa', 'event_espresso'); ?>)</p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_firstdata_e4_payment_settings');
