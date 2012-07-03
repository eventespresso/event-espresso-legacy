<?php
if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php")){
					echo '<table id="payment_butons" width="95%">';
					echo '<tr>';
					if (get_option('events_paypal_active') == 'true'){
						echo '<td>';
						require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/paypal_vars.php"); //Load PayPal vars
						echo '</td>';
					}
					if (get_option('events_authnet_active') == 'true'){
						echo '<td>';
						require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/authnet_vars.php"); //Load Authorize.net vars
						echo '</td>';
					}
					if (get_option('events_twoco_active') == 'true'){
						echo '<td>';
						require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/twoco/twoco_vars.php"); //Load 2CO vars
						echo '</td>';
					}
					echo '</tr>';
					echo '</table>';
					
					if (get_option('events_invoice_payment_active') == 'true'){
						require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/invoice_vars.php"); //Load Invoice vars
					}
					
					if (get_option('events_check_payment_active') == 'true'){
						require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/check/check_payment_vars.php"); //Load Check Payment vars
					}
					
					if (get_option('events_bank_payment_active') == 'true'){
						require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/bank/bank_payment_vars.php"); //Load Bank Payment vars
					}
					
				}else{
					_e('<h3>No payment gateways installed. Please install at least one payment gateway.</h3>','event_espresso');
				}