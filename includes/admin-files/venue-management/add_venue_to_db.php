<?php
function add_venue_to_db(){
	global $wpdb, $current_user;
	$wpdb->show_errors();
	
	//Check if we are adding a new venue from within the event editor
	if ( isset( $_REQUEST['add_new_venue_dynamic']) && $_REQUEST['add_new_venue_dynamic'] == 'true' ){
		$venue_autocomplete = true;
	}
	
	if ( isset( $_REQUEST['action']) && $_REQUEST['action'] == 'add' || $venue_autocomplete == true ){
		//print_r($_REQUEST);
		$venue_meta['contact'] = $_REQUEST['contact'];
		$venue_meta['phone'] = $_REQUEST['phone'];
		$venue_meta['twitter'] = $_REQUEST['twitter'];
		$venue_meta['image'] = $_REQUEST['image'];
		$venue_meta['website'] = $_REQUEST['website'];
		$venue_meta['description'] = esc_html($_REQUEST['description']);
		$locale = $_REQUEST['locale'];
		$meta = serialize($venue_meta);	
		
		$identifier=uniqid($current_user->ID.'-');
		
		if (!function_exists('espresso_member_data'))
			$current_user->ID = 1;
	
		$sql=array('identifier'=>$identifier, 'name'=>$_REQUEST['venue_name'],'address'=>$_REQUEST['venue_address'], 'address2'=>$_REQUEST['venue_address2'], 'city'=>$_REQUEST['venue_city'], 'state'=>$_REQUEST['venue_state'], 'zip'=>$_REQUEST['venue_zip'], 'country'=>$_REQUEST['venue_country'],'wp_user'=>$current_user->ID, 'meta'=>$meta,); 
		
		$sql_data = array('%s','%s','%s','%s','%s','%s','%s','%s','%d','%s');
		  
		 if ($wpdb->insert( EVENTS_VENUE_TABLE, $sql, $sql_data )){
			 
			 $last_venue_id = $wpdb->insert_id;
			 
			if( !empty($locale) ){
				
				$sql_locale="INSERT INTO ".EVENTS_LOCALE_REL_TABLE." (venue_id, locale_id) VALUES ('".$last_venue_id."', '".$locale."')";
				if (!$wpdb->query($sql_locale)){
					$error = true;
				}
			}
			
			if ( $venue_autocomplete == true ){
				return $last_venue_id;
			}
?>
		<div id="message" class="updated fade">
		<p><strong>
		<?php _e('The venue  has been added.','event_espresso'); ?>
		</strong></p>
		</div>
	<?php 
			
			
		}else{//$locale is empty
	?>
			<div id="message" class="error">
			<p><strong>
			<?php _e('The venue  was not saved.','event_espresso'); ?>
			</strong></p>
			</div>
			<?php 
				/*echo 'Debug: <br />';
				print_r($sql);
				print 'Number of vars: ' . count ($sql);
				echo '<br />';
				print 'Number of cols: ' . count($sql_data);*/
			?>
		<?php
		}
	}
}
