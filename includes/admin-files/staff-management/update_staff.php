<?php 
function update_event_staff(){
	global $wpdb;
	//print_r($_REQUEST);
	$staff_meta['phone'] = $_REQUEST['phone'];
	$staff_meta['twitter'] = $_REQUEST['twitter'];
	$staff_meta['image'] = $_REQUEST['image'];
	$staff_meta['website'] = $_REQUEST['website'];
	$staff_meta['description'] = esc_html($_REQUEST['description']);

    $meta = serialize($staff_meta);
		
	
	$sql=array('name'=>esc_html($_REQUEST['name']),'email'=>esc_html($_REQUEST['email']), 'meta'=>$meta); 
		
		$update_id = array('id'=> $_REQUEST['staff_id']);
		
		$sql_data = array('%s','%s');
	
	$wpdb->update( EVENTS_PERSONNEL_TABLE, $sql, $update_id, $sql_data, array( '%d' ) );
	  	/*echo 'Debug: <br />';
        print 'Number of vars: ' . count ($sql);
        echo '<br />';
        print 'Number of cols: ' . count($sql_data);
		echo '<br />';
		echo 'Table: ' .EVENTS_PERSONNEL_TABLE;
		echo '<br />';
		print_r($sql);*/
}