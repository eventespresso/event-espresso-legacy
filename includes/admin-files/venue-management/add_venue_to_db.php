<?php
if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	

function add_venue_to_db(){
	global $wpdb, $current_user;
	//$wpdb->show_errors();
	
	$success = array();
	$errors = array();
	
	//Set front end event manager to false
	$use_fem = FALSE;
		
	//If using the Espresso Event Manager
	if ( isset($_REQUEST['ee_fem_action']) && $_REQUEST['ee_fem_action'] == 'ee_fem_add'){
		//Security check using nonce
		if ( empty($_POST['nonce_verify_insert_event']) || !wp_verify_nonce($_POST['nonce_verify_insert_event'],'espresso_verify_insert_event_nonce') ){
			print '<h3 class="error">'.__('Sorry, there was a security error and your event was not saved.', 'event_espresso').'</h3>';
			return;
		}
		$use_fem = TRUE;
	}

    //Don't show sql errors if using the front-end event manager
	if ( $use_fem == FALSE )
		$wpdb->show_errors();
	
	//Check if we are adding a new venue from within the event editor
	$venue_autocomplete = isset( $_REQUEST['add_new_venue_dynamic'] ) && $_REQUEST['add_new_venue_dynamic'] == 'true' ? TRUE : FALSE;
	
	if ( isset( $_REQUEST['action']) && $_REQUEST['action'] == 'add' || $venue_autocomplete == true ){

		$venue_name = isset( $_REQUEST['venue_name'] ) ? sanitize_text_field( $_REQUEST['venue_name'] ) : '';
		
		if ( empty( $venue_name )) {
		
			$error = urlencode( __( 'An error occured. The venue name is a required field.','event_espresso' )); 
			$redirect = add_query_arg( array( 'action' => 'add_new_venue', 'form_error' => $error ), admin_url( 'admin.php?page=event_venues' ));
			if ( $use_fem == TRUE ){
				return;
			}else{
				wp_safe_redirect( $redirect );
			}
			
		} else {

			$venue_meta = array();
			$venue_meta['contact']		= isset( $_REQUEST['contact'] ) ? sanitize_text_field( $_REQUEST['contact'] ) : '';
			$venue_meta['phone']		= isset( $_REQUEST['phone'] ) ? sanitize_text_field( $_REQUEST['phone'] ) : '';
			$venue_meta['twitter']		= isset( $_REQUEST['twitter'] ) ? sanitize_text_field( $_REQUEST['twitter'] ) : '';
			$venue_meta['image']		= isset( $_REQUEST['image'] ) ? sanitize_text_field( $_REQUEST['image'] ) : '';
			$venue_meta['website']		= isset( $_REQUEST['website'] ) ? sanitize_text_field( $_REQUEST['website'] ) : '';
			$venue_meta['description']	= isset( $_REQUEST['description'] ) ? wp_kses_post( $_REQUEST['description'] ) : '';
			$locale						= isset( $_REQUEST['locale'] ) ? sanitize_text_field( sanitize_text_field($_REQUEST['locale']) ) : '';
			
			if ( ! function_exists('espresso_member_data')) {
				$current_user->ID = 1;
			}			
		
			$cols_and_values = array(
				'identifier' 	=> uniqid($current_user->ID.'-'), 
				'name'			=> $venue_name,
				'address'		=> isset( $_REQUEST['venue_address'] ) ? sanitize_text_field( $_REQUEST['venue_address'] ) : '', 
				'address2'		=> isset( $_REQUEST['venue_address2'] ) ? sanitize_text_field( $_REQUEST['venue_address2'] ) : '', 
				'city'			=> isset( $_REQUEST['venue_city'] ) ? sanitize_text_field( $_REQUEST['venue_city'] ) : '', 
				'state'			=> isset( $_REQUEST['venue_state'] ) ? sanitize_text_field( $_REQUEST['venue_state'] ) : '', 
				'zip'			=> isset( $_REQUEST['venue_zip'] ) ? sanitize_text_field( $_REQUEST['venue_zip'] ) : '', 
				'country'		=> isset( $_REQUEST['venue_country'] ) ? sanitize_text_field( $_REQUEST['venue_country'] ) : '',
				'wp_user'		=> (int)$current_user->ID, 
				'meta'			=> serialize($venue_meta) 
			); 
			
			$data_format = array('%s','%s','%s','%s','%s','%s','%s','%s','%d','%s');
			  
			 if ( $wpdb->insert( EVENTS_VENUE_TABLE, $cols_and_values, $data_format )){
				$success[] =  __('The venue  has been added.','event_espresso');
				$last_venue_id = $wpdb->insert_id;			 
				if( !empty($locale) ){				
					$cols_and_values =array ( 'venue_id' => $last_venue_id, 'locale_id' => $locale );
					$data_format = array('%d','%d');
					if ( $wpdb->insert( EVENTS_LOCALE_REL_TABLE, $cols_and_values, $data_format )) {
						$success[] =  __('The locale has been added.','event_espresso');
					} else {
						$error[] = __('An error occured. The locale was not saved.','event_espresso'); 
					}
				}
				
				if ( $venue_autocomplete == true ){
					return $last_venue_id;
				}
				
			} else {
				//$locale is empty
				$error[] = __('An error occured. The venue  was not saved.','event_espresso'); 
			}
			
		}


		if ( ! empty( $success )) : 
?>
		<div id="message" class="updated fade">
		<?php foreach ( $success as $msg ) { ?>
			<p><strong><?php echo $msg;?></strong></p>
		<?php } ?>
		</div>
		
<?php	
		endif;
			
		if ( ! empty( $error )) : 
?>
		<div id="message" class="error">
		<?php foreach ( $error as $msg ) { ?>
			<p><strong><?php echo $msg;?></strong></p>
		<?php } ;?>
		</div>
		
<?php	
		endif;

	}
}
