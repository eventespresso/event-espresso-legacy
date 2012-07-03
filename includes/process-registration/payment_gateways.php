<?php 
function event_espresso_agteways_mnu(){
	global $wpdb;
	
?>

<div id="event_reg_theme" class="wrap">
  <div id="icon-options-event" class="icon32"></div>
  <h2><?php echo _e('Manage Payment Gateways', 'event_espresso') ?></h2>
<?php	
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php")){
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/check/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/check/settings.php");
			event_espresso_check_payment_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/settings.php");
			event_espresso_bank_deposit_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/settings.php");
			event_espresso_invoice_payment_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/settings.php");
		}else{
			echo '<div class="metabox-holder"><div class="postbox"><h3>' . __('Authorize.net SIM Gateway','event_espresso') . '</h3><ul><li>If you already have a Merchant Account at your bank which allows you to authorize card accounts for direct payment into your bank account, then Authorize.Net would be the online equivalent of your in-store card-swipe/keypad terminal for card payment authorizations.</li>';
			if (ESPRESSO_STRENGTH == 'free'){
				_e('<li><a href="https://www.e-junkie.com/ecom/gb.php?c=cart&i=ESPRESSO-AUTHNET-SIM-GTWY&cl=113214&ejc=2" target="ej_ejc" class="ec_ejc_thkbx" onClick="javascript:return EJEJC_lc(this);"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/add-to-cart.gif" border="0" alt="Add to Cart"/></a> <a href="https://www.e-junkie.com/ecom/gb.php?c=cart&cl=113214&ejc=2" target="ej_ejc" class="ec_ejc_thkbx" onClick="javascript:return EJEJC_lc(this);"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/checkout-button.gif" border="0" alt="View Cart"/></a></li>','event_espresso');
			}else{
				_e('<li class="red_alert">It looks like your Authorize.net files are missing. If you did not recieve these files, please contact <a href="mailto:support@eventespresso.com">support@eventespresso.com</a></li>','event_espresso');
			}
			echo '</ul></div>';
		}

		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/settings.php");
			event_espresso_paypal_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/settings.php");
		}
		//End check to see if gateway files exist.
	}else{
?>		<ul><li>
        <div class="box-mid-head">
          <h2 class="fugue f-pallette">
            <?php _e("Install Payment Gateways", 'event_espresso'); ?>
          </h2>
        </div>
        <div class="box-mid-body" id="toggle6">
          <div class="padding">
<?php  if($_REQUEST['event_espresso_admin_action'] == 'copy_gateways') {
			add_action('admin_init', 'event_espresso_smartCopy');
		}?>
<?php
			  if($_SESSION['event_espresso_gateways_copied'] == true) {
					?>
					<div class="updated fade below-h2" id="message" style="background-color: rgb(255, 251, 204);">
						<p><?php _e("Your gateways have been installed."); ?></p>
					</div>
					<?php
					$_SESSION['event_espresso_gateways_copied'] = false;
			  }
			  ?>
			<?php
			if(event_espresso_count_files(EVENT_ESPRESSO_GATEWAY_DIR) > 0) {
				
					if(!is_writable(EVENT_ESPRESSO_GATEWAY_DIR)) {
					?>
						<p class="fugue f-error"><?php _e("The permissions on your templates directory are incorrect.", 'event_espresso'); ?> </p>
						<p class="fugue f-error"><?php _e("Please set the permissions to 775 on the following directory.", 'event_espresso'); ?><br /><br />
						<span class='display-path'><strong><?php _e("Path:", 'event_espresso'); ?></strong> <?php echo str_replace(ABSPATH, "", EVENT_ESPRESSO_GATEWAY_DIR); ?> </span></p>
<?php
					}
				}else{
				?>
					<p class="fugue f-warn"><?php _e("Your gateway files have not been moved.", 'event_espresso');?></p>
					<p class="updated"><?php printf(__("Click here to <a href='%s'>Move your files</a> to a safe place.", 'event_espresso'), wp_nonce_url("admin.php?event_espresso_admin_action=copy_gateways", 'copy_gateways') ); ?> </p>
				<?php
			}
			?>
          </div>
          </div>
          <div style="clear:both;"></div>
      </li></ul>
<?php
	}
?>
      
  </div>
  
<div id="button_image" style="display:none">
  <?php _e('<h2>Button Image URL</h2>
      <p>A default payment button is provided. A custom payment button may be used, choose your image or upload a new one, and just copy the "file url" here (optional.)</p>','event_espresso'); ?>
</div>
<div id="bypass_confirmation" style="display:none">
  <?php _e('<h2>By-passing the Confirmation Page</h2>
      <p>This will allow you to send your customers directly to the payment gateway of your choice.</p>','event_espresso'); ?>
</div>
<?php
}