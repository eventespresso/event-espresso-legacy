<?php
function add_cat_to_db(){
	global $wpdb,$current_user;
	if ( $_REQUEST['action'] == 'add' ){
		$category_name			= isset($_REQUEST['category_name']) && !empty($_REQUEST['category_name']) ? esc_html($_REQUEST['category_name']) : '';
		$category_identifier	= isset($_REQUEST['category_identifier']) && !empty($_REQUEST['category_identifier']) ? $category_identifier = sanitize_title_with_dashes($_REQUEST['category_identifier']) :  $category_identifier = sanitize_title_with_dashes($category_name.'-'.time()) ;
		$category_desc			= isset($_REQUEST['category_desc']) && !empty($_REQUEST['category_desc']) ? wp_kses_post( $_REQUEST['category_desc'] ) : ''; 
		$display_category_desc	= isset($_REQUEST['display_desc']) && $_REQUEST['display_desc'] === 'Y' ? 'Y' : 'N';
		
		if (!function_exists('espresso_member_data'))
			$current_user->ID = 1;
		
		$category_meta['use_pickers']		= isset($_REQUEST['use_pickers']) && $_REQUEST['use_pickers'] === 'Y' ? 'Y' : 'N';
		$category_meta['event_background']	= isset($_REQUEST['event_background']) && !empty($_REQUEST['event_background']) ? sanitize_text_field($_REQUEST['event_background']) : '' ;
		$category_meta['event_text_color']	= isset($_REQUEST['event_text_color']) && !empty($_REQUEST['event_text_color']) ? sanitize_text_field($_REQUEST['event_text_color']) : '' ;
		$category_meta = serialize($category_meta);
	
		$sql		= array('category_name'=>$category_name, 'category_identifier'=>$category_identifier, 'category_desc'=>$category_desc, 'display_desc'=>$display_category_desc, 'category_meta'=>$category_meta, 'wp_user'=>$current_user->ID);
		
		$sql_data	= array('%s','%s','%s','%s','%s','%d');
	
		if ($wpdb->insert( EVENTS_CATEGORY_TABLE, $sql, $sql_data )){?>
		<div id="message" class="updated fade"><p><strong><?php _e('The category has been added.', 'event_espresso'); ?></strong></p></div>
	<?php }else { ?>
		<div id="message" class="error"><p><strong><?php _e('The category   was not saved.', 'event_espresso'); ?> </strong></p></div>

<?php
		}
	}
}
