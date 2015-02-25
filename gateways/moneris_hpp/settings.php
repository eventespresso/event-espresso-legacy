<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }

function event_espresso_moneris_hpp_payment_settings() {

	global $active_gateways;
	
	if ( ! empty( $_REQUEST['activate_moneris_hpp'] )) {
		$active_gateways['moneris_hpp'] = dirname(__FILE__);
		update_option( 'event_espresso_active_gateways', $active_gateways );
	}
	
	if ( ! empty( $_REQUEST['deactivate_moneris_hpp'] )) {
		unset( $active_gateways['moneris_hpp'] );
		update_option( 'event_espresso_active_gateways', $active_gateways );
	}
		
	if ( isset( $_POST['update_moneris_hpp'] )) {
	
		$moneris_hpp_settings['moneris_hpp_ps_store_id'] = isset( $_POST['moneris_hpp_ps_store_id'] ) ? sanitize_text_field( $_POST['moneris_hpp_ps_store_id'] ) : '';
		$moneris_hpp_settings['moneris_hpp_key'] = isset( $_POST['moneris_hpp_key'] ) ? sanitize_text_field( $_POST['moneris_hpp_key'] ) : '';
		$moneris_hpp_settings['moneris_hpp_lang'] = isset( $_POST['moneris_hpp_lang'] ) ? sanitize_text_field( $_POST['moneris_hpp_lang'] ) : '';
		$moneris_hpp_settings['moneris_hpp_country'] = isset( $_POST['moneris_hpp_country'] ) ? sanitize_text_field( $_POST['moneris_hpp_country'] ) : '';
		$moneris_hpp_settings['moneris_hpp_txn_mode'] = isset( $_POST['moneris_hpp_txn_mode'] ) ? sanitize_text_field( $_POST['moneris_hpp_txn_mode'] ) : '';
		$moneris_hpp_settings['moneris_hpp_txn_notes'] = isset( $_POST['moneris_hpp_txn_notes'] ) ? sanitize_text_field( $_POST['moneris_hpp_txn_notes'] ) : '';
		// add credit card options
//		$moneris_hpp_settings['moneris_hpp_credit_cards'] = array();
//		$cards = array( 'mstrcrd', 'visa', 'amex', 'discover', 'sears', 'diners' );
//		if ( isset( $_POST['moneris_hpp_credit_cards'] )) {
//			foreach( $_POST['moneris_hpp_credit_cards'] as $cc => $on ) {
//				if ( in_array( $cc, $cards )) {
//					$moneris_hpp_settings['moneris_hpp_credit_cards'][ $cc ] = 1;
//				}
//			}	
//		}

		$moneris_hpp_settings['image_url'] = isset( $_POST['image_url'] ) ? esc_url_raw( $_POST['image_url'] ) : '';
		$moneris_hpp_settings['button_url'] = isset( $_POST['button_url'] ) ? esc_url_raw( $_POST['button_url'] ) : '';
		
		if ( update_option( 'event_espresso_moneris_hpp_settings', $moneris_hpp_settings )) {
			echo '<div id="message" class="updated fade"><p><strong>' . __('The Moneris Hosted Pay Page settings were successfully saved.', 'event_espresso') . '</strong></p></div>';
		}
	}

	if ( ! isset( $moneris_hpp_settings['button_url'] ) || ! file_exists( $moneris_hpp_settings['button_url'] )) {
		$moneris_hpp_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/pay-by-credit-card.png";
	}

	//Open or close the postbox div
	$postbox_style = empty( $_REQUEST['deactivate_moneris_hpp'] ) && ( ! empty( $_REQUEST['activate_moneris_hpp'] ) || array_key_exists( 'moneris_hpp', $active_gateways )) ? '' : 'closed';

	?>
	<a id="moneris_hpp"></a>

	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">		
			<div title="Click to toggle" class="handlediv"><br /></div>
			
			<h3 class="hndle"><?php _e('Moneris Hosted Pay Page Settings', 'event_espresso'); ?></h3>
			
			<div class="inside">
				<div class="padding">				
					<ul>
					
					<?php if ( array_key_exists( 'moneris_hpp', $active_gateways )) { ?>
					
						<li 
							id="deactivate_moneris_hpp" 
							class="red_alert pointer"
							style="width:30%;" 
							onclick="location.href='<?php echo get_bloginfo('wpurl');?>/wp-admin/admin.php?page=payment_gateways&deactivate_moneris_hpp=true'" 
							>
							<strong><?php _e('Deactivate Moneris Hosted Pay Page IPN?', 'event_espresso');?></strong>
						</li>
						<li>
						<?php event_espresso_display_moneris_hpp_settings();?>
						
					<?php } else { ?>
					
						</li> 
						<li 
							id="activate_moneris_hpp" 
							class="green_alert pointer"
							style="width:30%;" 
							onclick="location.href='<?php echo get_bloginfo('wpurl');?>/wp-admin/admin.php?page=payment_gateways&activate_moneris_hpp=true'" 
							>
							<strong><?php _e('Activate Moneris Hosted Pay Page IPN?', 'event_espresso');?></strong>
						</li>
						
					<?php } ?>
					
					</ul>
				</div>
			</div>
			
		</div>
	</div>
	<?php
}
add_action('action_hook_espresso_display_gateway_settings','event_espresso_moneris_hpp_payment_settings');



function event_espresso_get_moneris_hpp_settings() {

	$moneris_hpp_settings = get_option('event_espresso_moneris_hpp_settings');
	if ( empty( $moneris_hpp_settings ) || isset( $_REQUEST['reset-hpp'] )) {	

		$moneris_hpp_settings['moneris_hpp_ps_store_id'] = '';
		$moneris_hpp_settings['moneris_hpp_key'] = '';
		$moneris_hpp_settings['moneris_hpp_lang'] = 'en-ca';
		$moneris_hpp_settings['moneris_hpp_country'] = 'ca';
		$moneris_hpp_settings['moneris_hpp_txn_mode'] = 'dev';
//		$moneris_hpp_settings['moneris_hpp_credit_cards'] = array( 'mstrcrd' => 1, 'visa' => 1, 'amex' => 0, 'discover' => 0, 'sears' => 0, 'diners' => 0 );
		$moneris_hpp_settings['moneris_hpp_txn_notes'] = '';
		$moneris_hpp_settings['image_url'] = '';
		$button_url = file_exists( dirname( __FILE__ ) . 'moneris-logo.png' ) ? EVENT_ESPRESSO_GATEWAY_URL . '/moneris_hpp/moneris-logo.png' : EVENT_ESPRESSO_PLUGINFULLURL . "gateways/moneris_hpp/moneris-logo.png";
		$moneris_hpp_settings['button_url'] = $button_url;
		
		if ( add_option( 'event_espresso_moneris_hpp_settings', $moneris_hpp_settings, '', 'no' ) == FALSE ) {
			update_option('event_espresso_moneris_hpp_settings', $moneris_hpp_settings);
		}
	}	
	
	return $moneris_hpp_settings;
}


//Moneris Hosted Pay Page Settings Form
function event_espresso_display_moneris_hpp_settings() {

	$moneris_hpp_settings = event_espresso_get_moneris_hpp_settings();
	//printr( $moneris_hpp_settings, '$moneris_hpp_settings  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
?>
	<form method="post" action="<?php echo admin_url( 'admin.php?page=payment_gateways#moneris_hpp' ); ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>			
				<td valign="top">
				
					<ul class="espresso-payment-gateway-options-ul">					
						<li>
							<label><?php _e('Please Note:', 'event_espresso'); ?></label>
							<?php _e('In order for the Moneris Hosted Pay Page to communicate with Event Espresso, the "Response Method" must be set to POST. This is done through the Hosted Paypage Configuration Tool.', 'event_espresso'); ?>
						</li>
						
						<li>
							<label for="moneris_hpp_ps_store_id"><?php _e('Moneris Hosted Pay Page Store ID (ps_store_id)', 'event_espresso'); ?></label>
							<input type="text" name="moneris_hpp_ps_store_id" size="24" value="<?php echo $moneris_hpp_settings['moneris_hpp_ps_store_id']; ?>"><br/>
							<span class="description">
								<?php _e('This can be found in the Moneris Eselect Plus Merchant Resource Centre --> Admin --> Hosted Config.', 'event_espresso'); ?>
							</span>
						</li>
						
						<li>
							<label for="moneris_hpp_key"><?php _e('Moneris Hosted Pay Page Key (hpp_key)', 'event_espresso'); ?></label>
							<input type="text" name="moneris_hpp_key" size="24" value="<?php echo $moneris_hpp_settings['moneris_hpp_key']; ?>"><br/>
							<span class="description">
								<?php _e('This can be found in the Moneris Eselect Plus Merchant Resource Centre --> Admin --> Hosted Config.', 'event_espresso'); ?>
							</span>
						</li>
						
						<li>
							<label for="moneris_hpp_txn_mode"><?php _e( 'Transaction Mode', 'event_espresso' ); ?></label>
							<?php 
								$values = array( 
									array( 'id' => 'dev', 'text' => __( 'Development', 'event_espresso' )),
									array( 'id' => 'debug', 'text' => __( 'Dev + Debug', 'event_espresso' )),
									array( 'id' => 'prod', 'text' => __( 'Production', 'event_espresso' ))									
								); 
								echo select_input( 'moneris_hpp_txn_mode', $values, $moneris_hpp_settings['moneris_hpp_txn_mode'] );
							?>
							<span class="description">
								<?php _e('This defines the testing status for the Hosted Paypage.', 'event_espresso'); ?>
							</span><br/>
							<span class="description">
								<?php _e('Set to "Development" to use Moneris\'s test servers and "Production" to process payments on the actual live site. Select "Dev + Debug" to add on-screen reporting while in development mode.', 'event_espresso'); ?>
							</span>
						</li>

					</ul>
				</td>
				
				<td valign="top">				
					<ul class="espresso-payment-gateway-options-ul">					

						<li>
							<label for="moneris_hpp_country"><?php _e( 'Moneris Canada or USA?', 'event_espresso' ); ?></label>
							<?php 
								$values = array( 
									array( 'id' => 'ca', 'text' => __( 'Canada', 'event_espresso' )), 
									array( 'id' => 'us', 'text' => __( 'United States', 'event_espresso' ))
								); 
								echo select_input( 'moneris_hpp_country', $values, $moneris_hpp_settings['moneris_hpp_country'] );
							?>
							<span class="description">
								<?php _e('This defines what country the Hosted Paypage is setup within.', 'event_espresso'); ?>
							</span>
						</li>

						<li>
							<label for="moneris_hpp_lang"><?php _e( 'Language', 'event_espresso' ); ?></label>
							<?php 
								$values = array( 
									array( 'id' => 'en-ca', 'text' => __( 'English', 'event_espresso' )),
									array( 'id' => 'fr-ca', 'text' => __( 'French', 'event_espresso' ))
								); 
								echo select_input( 'moneris_hpp_lang', $values, $moneris_hpp_settings['moneris_hpp_lang'] );
							?>
							<span class="description">
								<?php _e('The language the Hosted Paypage and the receipts will be in. Defaults to English.', 'event_espresso'); ?>
							</span>
						</li>
						
						<li>
							<label for="moneris_hpp_txn_notes"><?php _e( 'Invoice Notes', 'event_espresso' ); ?></label>
							<textarea name="moneris_hpp_txn_notes" cols="50" rows="3" ><?php echo $moneris_hpp_settings['moneris_hpp_txn_notes']; ?></textarea><br/>
							<span class="description">
								<?php _e('This is for adding any additional notes or instructions that you wish to appear on the invoice.', 'event_espresso'); ?>
							</span>
						</li>
						
<?php /*
						<li>
							<label><?php _e('Credit Cards Accepted', 'event_espresso'); ?></label>
							<label class="gateway-checkbox-options" style="display:inline-block; min-width:30%; margin:.25em 4% .5em 0; vertical-align: middle;">
								<?php $checked = isset( $moneris_hpp_settings['moneris_hpp_credit_cards']['mstrcrd'] ) ? ' checked="checked"' : '' ?>
								<input name="moneris_hpp_credit_cards[mstrcrd]" type="checkbox" value="1" style="position: relative; top:-2px;"<?php echo $checked;?>/>
								<?php _e('Mastercard', 'event_espresso'); ?>
							</label>
							<label class="gateway-checkbox-options" style="display:inline-block; min-width:30%; margin:.25em 4% .5em 0; vertical-align: middle;">
								<?php $checked = isset( $moneris_hpp_settings['moneris_hpp_credit_cards']['visa'] ) ? ' checked="checked"' : '' ?>
								<input name="moneris_hpp_credit_cards[visa]" type="checkbox" value="1" style="position: relative; top:-2px;"<?php echo $checked;?>/>
								<?php _e('Visa', 'event_espresso'); ?>
							</label>
							<label class="gateway-checkbox-options" style="display:inline-block; min-width:30%; margin:.25em 4% .5em 0; vertical-align: middle;">
								<?php $checked = isset( $moneris_hpp_settings['moneris_hpp_credit_cards']['amex'] ) ? ' checked="checked"' : '' ?>
								<input name="moneris_hpp_credit_cards[amex]" type="checkbox" value="1" style="position: relative; top:-2px;"<?php echo $checked;?>/>
								<?php _e('American Express', 'event_espresso'); ?>
							</label>
							<label class="gateway-checkbox-options" style="display:inline-block; min-width:30%; margin:.25em 4% .5em 0; vertical-align: middle;">
								<?php $checked = isset( $moneris_hpp_settings['moneris_hpp_credit_cards']['discover'] ) ? ' checked="checked"' : '' ?>
								<input name="moneris_hpp_credit_cards[discover]" type="checkbox" value="1" style="position: relative; top:-2px;"<?php echo $checked;?>/>
								<?php _e('Discover / Novus', 'event_espresso'); ?>
							</label>
							<label class="gateway-checkbox-options" style="display:inline-block; min-width:30%; margin:.25em 4% .5em 0; vertical-align: middle;">
								<?php $checked = isset( $moneris_hpp_settings['moneris_hpp_credit_cards']['sears'] ) ? ' checked="checked"' : '' ?>
								<input name="moneris_hpp_credit_cards[sears]" type="checkbox" value="1" style="position: relative; top:-2px;"<?php echo $checked;?>/>
								<?php _e('Sears', 'event_espresso'); ?>
							</label>
							<label class="gateway-checkbox-options" style="display:inline-block; min-width:30%; margin:.25em 4% .5em 0; vertical-align: middle;">
								<?php $checked = isset( $moneris_hpp_settings['moneris_hpp_credit_cards']['diners'] ) ? ' checked="checked"' : '' ?>
								<input name="moneris_hpp_credit_cards[diners]" type="checkbox" value="1" style="position: relative; top:-2px;"<?php echo $checked;?>/>
								<?php _e('Diners Card', 'event_espresso'); ?>
							</label><br/>
							<span class="description">
								<?php _e('This defines which credit card logos you wish to display on the registration checkout page.', 'event_espresso'); ?>
							</span>
						</li>
*/?>
					
						<li>
							<label for="button_url"><?php _e('Button Image URL', 'event_espresso'); ?></label>
							<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image">
								<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" />
							</a>							
							<input class="upload_url_input" type="text" name="button_url" size="34" value="<?php echo $moneris_hpp_settings['button_url']; ?>" />
							<a class="upload_image_button" title="Add an Image">
								<img src="images/media-button-image.gif" alt="Add an Image">
							</a>
						</li>
						
						<li>
							<label><?php _e('Current Button Image:', 'event_espresso'); ?></label>
							<?php echo '<img src="' . $moneris_hpp_settings['button_url'] . '" />'; ?>
						</li>
						
					</ul>
				</td>
				
			</tr>
		</table>
		
		<p>
			<input type="hidden" name="update_moneris_hpp" value="update_moneris_hpp">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Moneris Hosted Pay Page Settings', 'event_espresso') ?>" id="save_moneris_hpp_settings" />
		</p>
		
	</form>

	<?php
}


