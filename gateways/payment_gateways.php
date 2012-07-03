<?php
//This is the payment gateway settings page. 
function event_espresso_agteways_mnu(){
	global $wpdb;

?>

<div id="event_reg_theme" class="wrap">
  <div id="icon-options-event" class="icon32"></div>
  <h2><?php echo _e('Manage Payment Gateways', 'event_espresso') ?></h2>
  <div id="poststuff" class="metabox-holder has-right-sidebar">
  <?php event_espresso_display_right_column ();?>
  <div id="post-body">
<div id="post-body-content">
<?php	
	
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/check/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/check/settings.php");
			event_espresso_check_payment_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/check/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/check/settings.php");
			event_espresso_check_payment_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/settings.php");
			event_espresso_bank_deposit_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/bank/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/bank/settings.php");
			event_espresso_bank_deposit_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/settings.php");
			event_espresso_invoice_payment_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/invoice/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/invoice/settings.php");
			event_espresso_invoice_payment_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/settings.php");
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/authnet/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/authnet/settings.php");
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/aim/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/aim/settings.php");
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/aim/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/aim/settings.php");
		}

                if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata/settings.php");
			event_espresso_firstdata_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/firstdata/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/firstdata/settings.php");
			event_espresso_firstdata_settings();
		}
                if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/ideal/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/ideal/settings.php");
			event_espresso_ideal_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/ideal/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/ideal/settings.php");
			event_espresso_ideal_settings();
		}
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/settings.php");
			event_espresso_paypal_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal/settings.php");
			event_espresso_paypal_settings();
		}

                if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal-pro/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal-pro/settings.php");
			event_espresso_paypal_pro_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal-pro/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal-pro/settings.php");
			event_espresso_paypal_pro_settings();
		}

		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/plugnpay/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/plugnpay/settings.php");
			event_espresso_plugnpay_settings();
		}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/plugnpay/settings.php")){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/plugnpay/settings.php");
			event_espresso_plugnpay_settings();
		}
                //requires and empty alipay_active.php file in the gateways/alipay OR
                //if you have moved the gateway files, place it in uploads/espresso/gateways
                if(file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/alipay/alipay_active.php") || file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/alipay/alipay_active.php")){
                    if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/alipay/settings.php")){
                            require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/alipay/settings.php");
                            event_espresso_alipay_settings();
                    }elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/alipay/settings.php")){
                            require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/alipay/settings.php");
                            event_espresso_alipay_settings();
                    }
                }
		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/settings.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/settings.php");
		}else{
			//require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/twoco/settings.php");
		}
		if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/admin-files/gateway_developer.php')){
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/admin-files/gateway_developer.php');
		}
		global $espresso_premium; if ($espresso_premium != true)
				echo '<h2>'.__('Need more gateway options?', 'event_espresso') . ' <a href="http://eventespresso.com/download/" target="_blank">'.__('Upgrade Now!', 'event_espresso').'</a></h2>';
?>

  </div>
  </div>
  </div>
  </div>
  
  
<div id="button_image" style="display:none">
 <h2><?php _e('Button Image URL', 'event_espresso'); ?></h2>
      <p><?php _e('A default payment button is provided. A custom payment button may be used, choose your image or upload a new one, and just copy the "file url" here (optional.)', 'event_espresso'); ?></p>
</div>
<div id="bypass_confirmation" style="display:none">
  <h2><?php _e('By-passing the Confirmation Page', 'event_espresso'); ?></h2>
      <p><?php _e('This will allow you to send your customers directly to the payment gateway of your choice.', 'event_espresso'); ?></p>
</div>
<?php
}