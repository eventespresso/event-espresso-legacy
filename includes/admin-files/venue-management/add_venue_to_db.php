<?php
function add_venue_to_db(){
	global $wpdb, $current_user;
	$wpdb->show_errors();
	if ( $_REQUEST['action'] == 'add' ){
		//print_r($_REQUEST);
		$venue_meta['contact'] = $_REQUEST['contact'];
		$venue_meta['phone'] = $_REQUEST['phone'];
		$venue_meta['twitter'] = $_REQUEST['twitter'];
		$venue_meta['image'] = $_REQUEST['image'];
		$venue_meta['website'] = $_REQUEST['website'];
		$venue_meta['description'] = esc_html($_REQUEST['description']);
	
		$meta = serialize($venue_meta);	
		
		$identifier=uniqid($current_user->ID.'-');
	
		$sql=array('identifier'=>$identifier, 'name'=>$_REQUEST['name'],'address'=>$_REQUEST['address'], 'address2'=>$_REQUEST['address2'], 'city'=>$_REQUEST['city'], 'state'=>$_REQUEST['state'], 'zip'=>$_REQUEST['zip'], 'country'=>$_REQUEST['country'],'wp_user'=>$current_user->ID, 'meta'=>$meta, 'locale'=>$_REQUEST['locale']); 
		
		$sql_data = array('%s','%s','%s','%s','%s','%s','%s','%s','%d','%s','%s');		
		  
		 if ($wpdb->insert( EVENTS_VENUE_TABLE, $sql, $sql_data )){?>
				<div id="message" class="updated fade">
				  <p><strong>
					<?php _e('The venue  has been added.','event_espresso'); ?>
					</strong></p>
				</div>
	<?php 
			}else{ ?>
				<div id="message" class="error">
				  <p><strong>
					<?php _e('The venue  was not saved.','event_espresso'); ?>
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