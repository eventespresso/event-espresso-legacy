<?php

function event_espresso_stripe_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_stripe'])) {
		$stripe_settings['stripe_publishable_key'] = $_POST['stripe_publishable_key'];
		$stripe_settings['stripe_secret_key'] = $_POST['stripe_secret_key'];
		$stripe_settings['stripe_currency_symbol'] = $_POST['stripe_currency_symbol'];
		$stripe_settings['header'] = $_POST['header'];
		$stripe_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
		$stripe_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		$stripe_settings['stripe_collect_billing_address'] =  empty($_POST['stripe_collect_billing_address']) ? false : true;
		update_option('event_espresso_stripe_settings', $stripe_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Stripe settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$stripe_settings = get_option('event_espresso_stripe_settings');
	if (empty($stripe_settings)) {
		$stripe_settings['stripe_publishable_key'] = '';
		$stripe_settings['stripe_secret_key'] = '';
		$stripe_settings['stripe_currency_symbol'] = 'usd';
		$stripe_settings['header'] = 'Payment Transactions by Stripe';
		$stripe_settings['force_ssl_return'] = false;
		$stripe_settings['display_header'] = false;
		$stripe_settings['stripe_collect_billing_address'] = false;
		if (add_option('event_espresso_stripe_settings', $stripe_settings, '', 'no') == false) {
			update_option('event_espresso_stripe_settings', $stripe_settings);
		}
	}

	if ( ! isset( $stripe_settings['button_url'] ) || ! file_exists( $stripe_settings['button_url'] )) {
		$stripe_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}
		
	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_stripe'])
					&& (!empty($_REQUEST['activate_stripe'])
					|| array_key_exists('stripe', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Stripe Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_stripe'])) {
						$active_gateways['stripe'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_stripe'])) {
						unset($active_gateways['stripe']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('stripe', $active_gateways)) {
						echo '<li id="deactivate_stripe" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_stripe=true\';" class="red_alert pointer"><strong>' . __('Deactivate Stripe?', 'event_espresso') . '</strong></li>';
						event_espresso_display_stripe_settings();
					} else {
						echo '<li id="activate_stripe" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_stripe=true\';" class="green_alert pointer"><strong>' . __('Activate Stripe?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Stripe Settings Form
function event_espresso_display_stripe_settings() {
	$stripe_settings = get_option('event_espresso_stripe_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="stripe_secret_key">
								<?php _e('Stripe Secret Key', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=stripe_secret_key"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="stripe_secret_key" size="35" value="<?php echo $stripe_settings['stripe_secret_key']; ?>">
						</li>
						<li>
							<label for="stripe_publishable_key">
								<?php _e('Stripe Publishable Key', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=stripe_publishable_key"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="stripe_publishable_key" size="35" value="<?php echo $stripe_settings['stripe_publishable_key']; ?>">
						</li>
						<li>
							<label for="stripe_currency_symbol">
								<?php _e('Stripe Currency Symbol (USD)', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=stripe_currency_symbol"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input type="text" name="stripe_currency_symbol" size="35" value="<?php echo $stripe_settings['stripe_currency_symbol']; ?>">
						</li>
						<?php /* Commenting this out, as this is not required or even used, really
						  <li>
						  <label for="stripe_transaction_prefix">
						  <?php _e('Stripe Transaction Prefix (Terminal):', 'event_espresso'); ?>
						  </label>
						  <br />
						  <input type="text" name="stripe_transaction_prefix" size="35" value="<?php echo $stripe_settings['stripe_transaction_prefix']; ?>">
						  </li>
						 */ ?>
					</ul>
				</td>
				<td>
						<ul>
						<?php if (espresso_check_ssl() == TRUE || ( isset($quickpay_settings['force_ssl_return']) && $quickpay_settings['force_ssl_return'] == 1 )) {?>
							<li>
							<label for="force_ssl_return">
								<?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="force_ssl_return" type="checkbox" value="1" <?php echo $stripe_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
							<?php }?>
							<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $stripe_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
							<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $stripe_settings['header']; ?>">
							<li>
							<label for="stripe_collect_billing_address">
								<?php _e('Collect billing address?', 'event_espresso'); ?>
							</label>
							<input name="stripe_collect_billing_address" type="checkbox" value="1" <?php echo $stripe_settings['stripe_collect_billing_address'] ? 'checked="checked"' : '' ?> /></li>
							<li>
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
			<input type="hidden" name="update_stripe" value="update_stripe">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Stripe Settings', 'event_espresso') ?>" id="save_stripe_settings" />
		</p>
	</form>
	<div id="stripe_currency_symbol" style="display:none">
		<h2>
			<?php _e('Stripe Currency Symbol', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('Stripe uses 3-character ISO-4217 codes for specifying currencies in fields and variables.  If you are taking purchases in US Dollars, enter <code>usd</code> here.  Stripe currently only takes payment in USD, but can accept payments from any currency which will be converted to USD at checkout.', 'event_espresso'); ?>
		</p>
	</div>
	<div id="stripe_secret_key" style="display:none">
		<h2>
			<?php _e('Stripe Secret Key', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('Enter your <a href="https://manage.stripe.com/#account/apikeys" target="_blank">Secret Key</a> here.  If you are testing the Stripe gateway, use your Test Secret Key, otherwise use your Live Secret Key.', 'event_espresso'); ?>
		</p>
		<p>
			<?php _e('<a href="https://stripe.com/docs/api#authentication" target="_blank">Learn more about API authentication.</a>', 'event_espresso'); ?>
		</p>
	</div>
	<div id="stripe_publishable_key" style="display:none">
		<h2>
			<?php _e('Stripe Publishable Key', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('Enter your <a href="https://manage.stripe.com/#account/apikeys" target="_blank">Publishable Key</a> here.  If you are testing the Stripe gateway, use your Test Publishable Key, otherwise use your Live Publishable Key.', 'event_espresso'); ?>
		</p>
		<p>
			<?php _e('<a href="https://stripe.com/docs/api#authentication" target="_blank">Learn more about API authentication.</a>', 'event_espresso'); ?>
		</p>
	</div>
	<?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_stripe_payment_settings');
