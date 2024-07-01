<?php 
function update_event_staff(){
	global $wpdb;
	$wpdb->show_errors();
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
		
	
	$sql=array('name'=>sanitize_text_field($_REQUEST['name']),'role'=>sanitize_text_field($_REQUEST['role']),'email'=>sanitize_text_field($_REQUEST['email']), 'meta'=>$meta); 
		
		$update_id = array('id'=> (int)$_REQUEST['staff_id']);
		
		$sql_data = array('%s','%s','%s','%s');
		$wpdb->update( EVENTS_PERSONNEL_TABLE, $sql, $update_id, $sql_data, array( '%d' ) );
		/*echo 'Debug: <br />';
                    print_r($sql);
                    print 'Number of vars: ' . count ($sql);
                    echo '<br />';
                    print 'Number of cols: ' . count($sql_data)*/;
		?>
		<div id="message" class="updated fade">
			<p><strong><?php _e('The person  has been updated.','event_espresso'); ?></strong></p>
         </div>
<?php
}