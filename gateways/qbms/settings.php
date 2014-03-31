<?php
//sometimes (upon gateway activation and some other actions I think) the init.php file
//isn't included. But we need to include it because it has constants we want
event_espresso_require_gateway('/qbms/init.php');
function event_espresso_qbms_payment_settings() {
	global $espresso_premium, $active_gateways;
	
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_qbms'])) {
		$qbms_settings['qbms_conn_ticket'] = $_POST['qbms_conn_ticket'];
		$qbms_settings['qbms_sandbox'] = array_key_exists('qbms_sandbox',$_POST) ? true : false;
		$qbms_settings['qbms_logpath'] = array_key_exists('qbms_logpath',$_POST) ? $_POST['qbms_logpath'] : '';
		$qbms_settings['qbms_log'] = array_key_exists('qbms_log',$_POST) ? $_POST['qbms_log'] : false;
		$qbms_settings['header'] = $_POST['header'];
		$qbms_settings['display_header'] = empty($_POST['display_header']) ? false : true;
		update_option('event_espresso_qbms_settings', $qbms_settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('qbms settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$qbms_settings = get_option('event_espresso_qbms_settings');
	if (empty($qbms_settings)) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/qbms/qbms-logo.gif")) {
			$qbms_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/qbms/qbms-logo.gif";
		} else {
			$qbms_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/qbms/qbms-logo.gif";
		}
		$qbms_settings['qbms_conn_ticket'] = '';
		$qbms_settings['qbms_sandbox'] = false;
		$qbms_settings['header'] = __('Payments by QuickBooks Merchant Services','event_espresso');
		$qbms_settings['display_header'] = false;
		$default_logdir = wp_upload_dir();
		$qbms_settings['qbms_logpath'] = $default_logdir['basedir'].'/espresso/gateways/qbms/';
		$qbms_settings['qbms_log'] = 'off';
		if (add_option('event_espresso_qbms_settings', $qbms_settings, '', 'no') == false) {
			update_option('event_espresso_qbms_settings', $qbms_settings);
		}
	}

	if ( ! isset( $qbms_settings['button_url'] ) || ! file_exists( $qbms_settings['button_url'] )) {
		$qbms_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_qbms'])
		&& (!empty($_REQUEST['activate_qbms'])
			|| array_key_exists('qbms', $active_gateways))) {
		$postbox_style = '';
} else {
	$postbox_style = 'closed';
}
?>

<div class="metabox-holder">
	<div class="postbox <?php echo $postbox_style; ?>">
		<div title="Click to toggle" class="handlediv"><br /></div>
		<h3 class="hndle">
			<?php _e('QuickBooks Merchant Services Settings', 'event_espresso'); ?>
		</h3>
		<div class="inside">
			<div class="padding">
				<?php
				if (!empty($_REQUEST['activate_qbms'])) {
					$active_gateways['qbms'] = dirname(__FILE__);
					update_option('event_espresso_active_gateways', $active_gateways);
				}
				if (!empty($_REQUEST['deactivate_qbms'])) {
					unset($active_gateways['qbms']);
					update_option('event_espresso_active_gateways', $active_gateways);
				}
				echo '<ul>';
				if (array_key_exists('qbms', $active_gateways)) {
					echo '<li id="deactivate_qbms" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_qbms=true\';" class="red_alert pointer"><strong>' . __('Deactivate QuickBooks Merchant Services Payment Method?', 'event_espresso') . '</strong></li>';
					event_espresso_display_qbms_settings();
				} else {
					echo '<li id="activate_qbms" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_qbms=true\';" class="green_alert pointer"><strong>' . __('Activate QuickBooks Merchant Services Payment Method?', 'event_espresso') . '</strong></li>';
				}
				echo '</ul>';
				?>
			</div>
		</div>
	</div>
</div>
<?php
}

//qbms Settings Form
function event_espresso_display_qbms_settings() {
	$qbms_settings = get_option('event_espresso_qbms_settings');
	$default_logdir = wp_upload_dir();
	$default_logdir = $default_logdir['basedir'].'/espresso/gateways/qbms/';
	$app_id = $qbms_settings['qbms_sandbox'] ? ESPRESSO_QBSM_DEV_APP_ID : ESPRESSO_QBMS_LIVE_APP_ID;
	$connection_ticket_url = $qbms_settings['qbms_sandbox'] ? "https://merchantaccount.ptc.quickbooks.com/j/sdkconnection?appid=$app_id&sessionEnabled=false":"https://merchantaccount.quickbooks.com/j/sdkconnection?appid=$app_id&sessionEnabled=false";
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for='qbms_conn_ticket'>
								<?php _e("QuickBooks Connection Ticket",'event_espresso');?> 
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=connection_ticket"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input class="input-text" type="text" name="qbms_conn_ticket" id="qbms_conn_ticket" style="min-width:50px;" value="<?php echo $qbms_settings['qbms_conn_ticket'] ?>" />
							<br/>
						</li>
					</ul>
				</td>
				<td valign="top">
					<ul>
						<li>
							<label for='qbms_sandbox'><?php _e("Use PTC (Development) Server",'event_espresso')?><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=qbms_ipc"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a></label>
							<input type='checkbox' name='qbms_sandbox' value="1" <?php if ($qbms_settings['qbms_sandbox']) echo 'checked'; ?>>
						</li>
						<li>
							<label for="display_header">
								<?php _e('Display a Form Header', 'event_espresso'); ?>
								<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=display_header"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
							</label>
							<input name="display_header" type="checkbox" value="1" <?php echo $qbms_settings['display_header'] ? 'checked="checked"' : '' ?> /></li>
						<li>
							<label for="header">
								<?php _e('Header Text', 'event_espresso'); ?>
							</label>
							<input type="text" name="header" size="35" value="<?php echo $qbms_settings['header']; ?>">
						</li>
						<?php /* ?><li>
							<label for='qbms_log'><?php _e("Technical Log",'event_espresso')?></label>
							<select name="qbms_log" id="qbms_log" style="min-width:100px;">
								<option value="off" <?php if ($qbms_settings['qbms_log'] == 'off') echo 'selected="selected"'; ?>> <?php _e('Off', 'event_espresso'); ?></option>
								<option value="e_only" <?php if ($qbms_settings['qbms_log'] == 'e_only') echo 'selected="selected"'; ?>><?php _e('Errors Only', 'event_espresso'); ?></option>
								<option value="all" <?php if ($qbms_settings['qbms_log'] == 'all') echo 'selected="selected"'; ?>><?php _e('All', 'event_espresso'); ?></option>
							</select>
						</li>
						<li>
							<label for='qbms_logpath'><?php _e("Folder where to place log file",'event_espresso')?></label>
							<input class="input-text wide-input" type="text" name="qbms_logpath" id="qbms_logpath" value="<?php echo $qbms_settings['qbms_logpath'] ?>" />
						</li>
						<?php */ ?>
						 
					</ul>
				</td>
			</tr>
		</table>
		<?php if (espresso_check_ssl() == FALSE){
			espresso_ssl_required_gateway_message();
		}?>
		<p>
			<input type="hidden" name="update_qbms" value="update_qbms">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update QBMS Settings', 'event_espresso') ?>" id="save_qbms_settings" />
		</p>
	</form>
<div id="connection_ticket" style="display:none">
		<h2><?php _e('QuickBooks Merchant Services Connection Ticket', 'event_espresso'); ?></h2>
		<p><?php printf(__('QuickBooks Merchant Services allows you to accept credit card payments and easily link to your other QuickBooks software. %s Click here to read more %s', 'event_espresso'), "<a target='_blank' href='http://payments.intuit.com/'>","</a>"); ?></p>
		<h2><?php _e("How to Configure QuickBooks Merchant Services Payment Gateway",'event_espresso')?></h2>
		<ol>
			<!-- <li>
				<?php __("This version of Event Espresso's QuickBooks Merchant Services Payment Method receive's users' credit card info directly into your website. For this reason, it is HIGHLY recommended that you enable your site to be handled over HTTPS/SSL.",'event_espresso');?>
			</li> -->
			<li>
				<?php printf(__('First signup for a %s Merchant Services Account here %s', 'event_espresso'),"<a target='_blank' href='https://merchant.intuit.com/signup/start.wsp'>","</a>"); ?>
			</li>
			<li>
				<?php printf(__('After creating the account, %s click here to link Event Espresso to your QuickBooks Merchant Services account%s','event_espresso'),"<a target='_blank' href='$connection_ticket_url'>","</a>");?>
			</li>
			<li>
				<?php printf(__('After you click on the previous link, enter your QuickBooks Merchant Services email and password.','event_espresso'))?>
			</li>
			<li>
				<?php printf(__("You will then be given a 'Connection Ticket', which you must copy and paste into the Field 'Connection Ticket' in Event Espresso's QuickBooks Merchant Services payment settings field.",'event_espresso'))?>
				<br>
				<?php _e("(Note: if you change whether you're using the PTC/Development server, you will need to re-acquire your connection ticket.)",'event_espresso')?>
			</li>
			<li>
				<?php _e("All done! Now just test that payments are working, and you're done!",'event_espresso');?>
			</li>
		</ol>
	</div>
<div id="qbms_ipc" style="display:none">
		<h2><?php _e('QuickBooks Merchant Services PTC (Development) Server', 'event_espresso'); ?></h2>
		<p><?php _e("If you wish to test this payment method, you can enable the PTC (Development) server. If this setting is enabled, payment processing messages will be sent to QuickBooks Merchant Services' IPC server, where payments are not actually processed (no money actually changes hands).",'event_espresso')?></p>
		<p><?php _e("You will probably only want to use this setting if you are modifying this payment method's code, or if you suspect there to be a bug in it and you want to try to reproduce it.",'event_espresso')?></p>
		<p><?php printf(__("Note: if you change this setting, you MUST get a new Connection ticket (%sby clicking here%s)",'event_espresso'),"<a target='_blank' href='$connection_ticket_url'>",'</a>')?></p>
		<p><?php printf(__("While using the PTC (Development) Server, you must use test credit cards, %s listed here%s",'event_espresso'),"<a target='_blank' href='https://ipp.developer.intuit.com/0085_QuickBooks_Windows_SDK/030_qbms/0060_Documentation/Testing#Testing_Credit_Card_Transactions'>",'</a>')?>
</div>

	<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_qbms_payment_settings');

