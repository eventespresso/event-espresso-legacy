<?php
//This is the payment gateway settings page. 
function event_espresso_agteways_mnu(){
	global $wpdb;
	
?>

<div id="event_reg_theme" class="wrap">
  <div id="icon-options-event" class="icon32"></div>
  <h2><?php echo _e('Manage Payment Gateways', 'event_espresso') ?></h2>
<?php	
	
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/check/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/check/settings.php");
			event_espresso_check_payment_settings();
		}else{
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/check/settings.php");
			event_espresso_check_payment_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/settings.php");
			event_espresso_bank_deposit_settings();
		}else{
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/bank/settings.php");
			event_espresso_bank_deposit_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/settings.php");
			event_espresso_invoice_payment_settings();
		}else{
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/invoice/settings.php");
			event_espresso_invoice_payment_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/settings.php");
		}else{
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/authnet/settings.php");
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/settings.php");
			event_espresso_paypal_settings();
		}else{
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal/settings.php");
			event_espresso_paypal_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/plugnpay/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/plugnpay/settings.php");
			event_espresso_plugnpay_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/plugnpay/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/plugnpay/settings.php");
			event_espresso_plugnpay_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/settings.php");
		}else{
			//require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/twoco/settings.php");
		}
	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php")){
		?>
        <ul><li>
        <div class="box-mid-head">
          <h2 class="fugue f-pallette">
            <?php _e("Move Your Payment Gateways", 'event_espresso'); ?>
          </h2>
        </div>
        <div class="box-mid-body" id="toggle6">
          <div class="padding">
          <p>
                <?php _e("Modifying and adding payment gateways is easy.",'event_espresso');?>
              </p>
              <p>
                <?php _e("You just need to add/edit the appropriate files in the following location.", 'event_espresso'); ?>
              </p>
              <p> <span class="green_alert">
                <?php _e("Path:", 'event_espresso'); ?>
                <?php echo str_replace(ABSPATH, "", EVENT_ESPRESSO_GATEWAY_DIR); ?></span> </p>
        <p class="red_alert">
                  <?php _e('Remember, if updates are made or features are added to these templates in the future. You will need to make the updates to your customized templates.', 'event_espresso'); ?>
                </p>
                </div>
          </div>
          <div style="clear:both;"></div>
      </li></ul>
        <?php
	}else{
?>		<ul><li>
        <div class="box-mid-head">
          <h2 class="fugue f-pallette">
            <?php _e("Move Your Payment Gateways", 'event_espresso'); ?>
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
						<p><?php _e("Your gateways have been moved.",'event_espresso'); ?></p>
					</div>
					<?php
					$_SESSION['event_espresso_gateways_copied'] = false;
			  }
			  ?>
			<?php
			if(@event_espresso_count_files(EVENT_ESPRESSO_GATEWAY_DIR) > 0) {
				
					if(!is_writable(EVENT_ESPRESSO_GATEWAY_DIR)) {
					?>
						<p class="fugue f-error"><?php _e("The permissions on your templates directory are incorrect.", 'event_espresso'); ?> </p>
						<p class="fugue f-error"><?php _e("Please set the permissions to 775 on the following directory.", 'event_espresso'); ?><br /><br />
						<span class='display-path'><strong><?php _e("Path:", 'event_espresso'); ?></strong> <?php echo str_replace(ABSPATH, "", EVENT_ESPRESSO_GATEWAY_DIR); ?> </span></p>
<?php
					}
				}else{
				?>
                	<p><?php _e('If you plan on adding additional payment gateways. Please use the link below to move your gateway files to a safe place. Only use this option if you absolutely need to or instructed to by a represntative from Event Espresso. ', 'event_espresso'); ?></p>
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