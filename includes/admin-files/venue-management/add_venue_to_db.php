<?php
function add_venue_to_db(){
	global $wpdb;
	if ( $_REQUEST['action'] == 'add' ){
		//print_r($_REQUEST);
		$venue_meta['contact'] = $_REQUEST['contact'];
		$venue_meta['phone'] = $_REQUEST['phone'];
		$venue_meta['twitter'] = $_REQUEST['twitter'];
		$venue_meta['image'] = $_REQUEST['image'];
		$venue_meta['website'] = $_REQUEST['website'];
		$venue_meta['description'] = esc_html($_REQUEST['description']);
	
		$meta = serialize($venue_meta);	
	
		$sql=array('name'=>esc_html($_REQUEST['name']),'address'=> esc_html($_REQUEST['address']), 'address2'=> esc_html($_REQUEST['address2']), 'city'=> esc_html($_REQUEST['city']), 'state'=> esc_html($_REQUEST['state']), 'zip'=> esc_html($_REQUEST['zip']), 'country'=> esc_html($_REQUEST['country']), 'meta'=>$meta); 
		
		$sql_data = array('%s','%s','%s','%s','%s','%s','%s','%s');
	
		$wpdb->insert( EVENTS_VENUE_TABLE, $sql, $sql_data );
		
		  echo 'Debug: <br />';
          print 'Number of vars: ' . count ($sql);
          echo '<br />';
          print 'Number of cols: ' . count($sql_data); 
	}
}