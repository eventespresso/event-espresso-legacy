<?php
function add_staff_to_db(){
	global $wpdb, $current_user;
	$wpdb->show_errors();
	if ( $_REQUEST['action'] == 'add' ){
		//print_r($_REQUEST);
		$staff_meta['phone'] = sanitize_text_field($_REQUEST['phone']);
		$staff_meta['twitter'] = sanitize_text_field($_REQUEST['twitter']);
		$staff_meta['image'] = esc_url_raw($_REQUEST['image']);
		$staff_meta['website'] = esc_url_raw($_REQUEST['website']);
		$staff_meta['description'] = wp_kses_post( $_REQUEST['description'] );
	
		$staff_meta['organization'] = sanitize_text_field($_REQUEST['organization']);
		$staff_meta['title'] = sanitize_text_field($_REQUEST['title']);
		$staff_meta['industry'] = sanitize_text_field($_REQUEST['industry']);
		$staff_meta['city'] = sanitize_text_field($_REQUEST['city']);
		$staff_meta['country'] = sanitize_text_field($_REQUEST['country']);

		$meta = serialize($staff_meta);
		
		$identifier=uniqid($current_user->ID.'-');
		
		if (!function_exists('espresso_member_data'))
			$current_user->ID = 1;
	
		$sql=array('identifier'=>$identifier, 'role'=>sanitize_text_field($_REQUEST['role']), 'name'=>sanitize_text_field($_REQUEST['name']),'email'=>sanitize_text_field($_REQUEST['email']),'wp_user'=>$current_user->ID,'meta'=>$meta); 
		
		$sql_data = array('%s', '%s', '%s','%s','%d','%s');
			
		if ($wpdb->insert( EVENTS_PERSONNEL_TABLE, $sql, $sql_data )){?>
				<div id="message" class="updated fade">
				  <p><strong>
					<?php _e('The person  has been added.','event_espresso'); ?>
					</strong></p>
				</div>
	<?php 
			}else{ ?>
				<div id="message" class="error">
				  <p><strong>
					<?php _e('The person  was not saved.','event_espresso'); ?>
					</strong></p>
                    <?php echo 'Debug: <br />';
					  print_r($sql);
					  print 'Number of vars: ' . count ($sql);
					  echo '<br />';
					  print 'Number of cols: ' . count($sql_data);?>
				</div>
		<?php
			}
	}
}