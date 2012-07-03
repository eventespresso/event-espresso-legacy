<?php 
function update_event_venue(){
	global $wpdb;
	$wpdb->show_errors();
	//print_r($_REQUEST);
	$venue_meta['contact'] = $_REQUEST['contact'];
	$venue_meta['phone'] = $_REQUEST['phone'];
	$venue_meta['twitter'] = $_REQUEST['twitter'];
	$venue_meta['image'] = $_REQUEST['image'];
	$venue_meta['website'] = $_REQUEST['website'];
	$venue_meta['description'] = esc_html($_REQUEST['description']);

    $meta = serialize($venue_meta);
		
	
	$sql=array('name'=>$_REQUEST['name'],'address'=>$_REQUEST['address'], 'address2'=>$_REQUEST['address2'], 'city'=>$_REQUEST['city'], 'state'=>$_REQUEST['state'], 'zip'=>$_REQUEST['zip'], 'country'=>$_REQUEST['country'], 'locale'=>$_REQUEST['locale'], 'meta'=>$meta); 
		
	$update_id = array('id'=> $_REQUEST['venue_id']);
		
	$sql_data = array('%s','%s','%s','%s','%s','%s','%s','%s','%s');
		
	if ($wpdb->update( EVENTS_VENUE_TABLE, $sql, $update_id, $sql_data, array( '%d' ) )){ ?>
		<div id="message" class="updated fade">
			<p><strong><?php _e('The venue  has been updated.','event_espresso'); ?></strong></p>
         </div>
<?php }else { ?>
		<div id="message" class="error">
			<p><strong><?php _e('The venue was not updated.','event_espresso'); ?></strong></p>
              <?php echo 'Debug: <br />';
                    print_r($sql);
                    print 'Number of vars: ' . count ($sql);
                    echo '<br />';
                    print 'Number of cols: ' . count($sql_data);?> </div>
<?php
	}
}