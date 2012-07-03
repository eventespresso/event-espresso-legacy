<?php
function add_staff_to_db(){
	global $wpdb;
	if ( $_REQUEST['action'] == 'add' ){
		//print_r($_REQUEST);
		$staff_meta['phone'] = $_REQUEST['phone'];
		$staff_meta['twitter'] = $_REQUEST['twitter'];
		$staff_meta['image'] = $_REQUEST['image'];
		$staff_meta['website'] = $_REQUEST['website'];
		$staff_meta['description'] = esc_html($_REQUEST['description']);
	
		$meta = serialize($staff_meta);	
	
		$sql=array('name'=>esc_html($_REQUEST['name']),'email'=>esc_html($_REQUEST['email']),'meta'=>$meta); 
		
		$sql_data = array('%s','%s');
	
		$wpdb->insert( EVENTS_PERSONNEL_TABLE, $sql, $sql_data );
		
		  echo 'Debug: <br />';
          print 'Number of vars: ' . count ($sql);
          echo '<br />';
          print 'Number of cols: ' . count($sql_data); 
	}
}