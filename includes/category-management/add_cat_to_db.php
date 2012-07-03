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
		<div id="message" class="updated fade"><p><strong>The category <?php echo htmlentities2($_REQUEST['category_name']);?> has been added.</strong></p></div>
	<?php }else { ?>
		<div id="message" class="error"><p><strong>The category <?php echo htmlentities2($_REQUEST['category_name']);?> was not saved. <?php print mysql_error() ?>.</strong></p></div>

<?php
		}
	}
}