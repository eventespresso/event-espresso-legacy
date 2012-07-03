<?php
function add_cat_to_db(){
	global $wpdb;
	if ( $_REQUEST['action'] == 'add' ){
		$category_name= esc_html($_REQUEST['category_name']);
		$category_identifier = ($_REQUEST['category_identifier'] == '') ? $category_identifier = sanitize_title_with_dashes($category_name.'-'.time()) : $category_identifier = sanitize_title_with_dashes($_REQUEST['category_identifier']);
		$category_desc= esc_html($_REQUEST['category_desc']); 
		$display_category_desc=$_REQUEST['display_desc'];
	
	
		$sql=array('category_name'=>$category_name, 'category_identifier'=>$category_identifier, 'category_desc'=>$category_desc, 'display_desc'=>$display_category_desc); 
		
		$sql_data = array('%s','%s','%s','%s');
	
		if ($wpdb->insert( get_option('events_category_detail_tbl'), $sql, $sql_data )){?>
		<div id="message" class="updated fade"><p><strong><?php _e('The category has been added.', 'event_espresso'); ?></strong></p></div>
	<?php }else { ?>
		<div id="message" class="error"><p><strong><?php _e('The category   was not saved.', 'event_espresso'); ?> </strong></p></div>

<?php
		}
	}
}