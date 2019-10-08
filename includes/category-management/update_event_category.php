<?php 
function update_event_category(){
	global $wpdb;
	$category_id= $_REQUEST['category_id'];
	$category_name = esc_html($_REQUEST['category_name']);
	$category_identifier = ($_REQUEST['category_identifier'] == '') ? $category_identifier = sanitize_title_with_dashes($category_name.'-'.time()) : $category_identifier = sanitize_title_with_dashes($_REQUEST['category_identifier']);
	$category_desc = wp_kses_post( $_REQUEST['category_desc'] ); 
	$display_category_desc = $_REQUEST['display_desc'];
	
	$category_meta['use_pickers'] = isset($_REQUEST['use_pickers']) && $_REQUEST['use_pickers'] === 'Y' ? 'Y' : 'N';
	$category_meta['event_background'] = isset($_REQUEST['event_background']) && !empty($_REQUEST['event_background']) ? sanitize_text_field($_REQUEST['event_background']) : '' ;
	$category_meta['event_text_color'] = isset($_REQUEST['event_text_color']) && !empty($_REQUEST['event_text_color']) ? sanitize_text_field($_REQUEST['event_text_color']) : '' ;
	//echo "<pre>".print_r($_POST,true)."</pre>";
	$category_meta = serialize($category_meta);
	
	$sql=array('category_name'=>$category_name, 'category_identifier'=>$category_identifier, 'category_desc'=>$category_desc, 'display_desc'=>$display_category_desc, 'category_meta'=>$category_meta); 
		
		$update_id = array('id'=> $category_id);
		
		$sql_data = array('%s','%s','%s','%s','%s');
	
	if ($wpdb->update( EVENTS_CATEGORY_TABLE, $sql, $update_id, $sql_data, array( '%d' ) )){?>
	<div id="message" class="updated fade"><p><strong><?php _e('The category has been updated.', 'event_espresso'); ?> </strong></p></div>
<?php }else { ?>
	<div id="message" class="error"><p><strong><?php _e('The category was not updated.', 'event_espresso'); ?></strong></p></div>

<?php
	}
}