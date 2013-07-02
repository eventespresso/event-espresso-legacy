<?php

function espresso_promocodes_search($search_string,$start=0,$count=10){
	global $wpdb;
	$search_string = sanitize_text_field($search_string);
	$query = $wpdb->prepare("SELECT * FROM ".EVENTS_DISCOUNT_CODES_TABLE." WHERE coupon_code LIKE '%$search_string%' OR coupon_code_description LIKE '%$search_string%' LIMIT %d,%d",$start,$count);
	$results = $wpdb->get_results($query);
	return $results;
}

function espresso_promocodes_datatables_search(){
	//note: this is meant ot respond to an ajax request sent by jquery datatables
	//and here is an example of the $_GET parameters it contains:
//	[action] => event_espresso_get_discount_codes_for_jquery_datatables //this is just used for wp ajax integration
//    [sEcho] => 9 //this value simply needs to be echoed back to the jquery data tables
//    [iColumns] => 6
//    [sColumns] =>
//    [iDisplayStart] => 0
//    [iDisplayLength] => 10
//    [sSearch] => monkey //the search string
//    [bRegex] => false
//    [sSearch_0] => //I suppose this would be filled IF we allowed them to have different searches on different column
//    [bRegex_0] => false
//    [bSearchable_0] => true
//    [sSearch_1] =>
//    [bRegex_1] => false
//    [bSearchable_1] => true
//    [sSearch_2] =>
//    [bRegex_2] => false
//    [bSearchable_2] => true
//    [sSearch_3] =>
//    [bRegex_3] => false
//    [bSearchable_3] => true
//    [sSearch_4] =>
//    [bRegex_4] => false
//    [bSearchable_4] => true
//    [sSearch_5] =>
//    [bRegex_5] => false
//    [bSearchable_5] => true
//    [iSortingCols] => 1 //number of columns we're sorting on right now
//    [iSortCol_0] => 0 //the first column that we're sorting by... this should actually mean we're NOT sorting in this case, because 0 is not sortable
//    [sSortDir_0] => asc //sort by the 1st column in ascending order, except that we don't sort by that 1st column as indicated below
//    [bSortable_0] => false
//    [bSortable_1] => true
//    [bSortable_2] => true
//    [bSortable_3] => true
//    [bSortable_4] => true
//    [bSortable_5] => true
//    [_] => 1372206625831
	$search = isset($_GET['sSearch']) ? sanitize_text_field($_GET['sSearch']) : '';
	$jquery_datatable_column_to_sql_column_mapping = array();
	$jquery_datatable_column_to_sql_column_mapping[] = 'id';//the first column is actually the checkbox, which shouldn't be ordered by.
	$jquery_datatable_column_to_sql_column_mapping[] = 'id';//ID
	$jquery_datatable_column_to_sql_column_mapping[] = 'coupon_code';//Name
	if (function_exists('espresso_is_admin') && espresso_is_admin() == true) { 
		$jquery_datatable_column_to_sql_column_mapping[] = 'wp_user';//Author
	}
	$jquery_datatable_column_to_sql_column_mapping[] = 'coupon_code_price';//Amount
	$jquery_datatable_column_to_sql_column_mapping[] = 'use_percentage';//Percentage
	$jquery_datatable_column_to_sql_column_mapping[] = 'apply_to_all';//Global
	
	$order_column = $jquery_datatable_column_to_sql_column_mapping[ intval($_GET['iSortCol_0']) ];
	$sort_order = isset($_GET['sSortDir_0']) && $_GET['sSortDir_0'] == 'asc' ? 'asc' : 'desc';
	$start = intval($_GET['iDisplayStart']);
	$count = intval($_GET['iDisplayLength']);
	global $wpdb;
	
	$where_conditions =" WHERE coupon_code LIKE '%$search%' OR coupon_code_description LIKE '%$search%' OR coupon_code_price like '%$search%'";
	$query = "SELECT * FROM ".EVENTS_DISCOUNT_CODES_TABLE." 
		$where_conditions
		ORDER BY $order_column $sort_order LIMIT $start,$count";
	$wpdb_results = $wpdb->get_results($query);
	
	$total_result = espresso_promocodes_count_total();
	$total_filtered_result = $wpdb->get_var("SELECT count(id) FROM ".EVENTS_DISCOUNT_CODES_TABLE.$where_conditions);
	$prepared_results = espresso_promocodes_format_for_jquery_datatables($wpdb_results);
	
	$output = array( 
		'sColumns' => 'Checkbox, ID, Name, Amount, Percentage, Global', 
		'sEcho' => intval($_GET['sEcho']), 
		'iTotalRecords' => $total_result, 
		'iTotalDisplayRecords' =>$total_filtered_result, 
		'aaData' => $prepared_results ); //- See more at: http://www.koolkatwebdesigns.com/using-jquery-datatables-with-wordpress-and-ajax/#sthash.H0zsZy6z.dpuf
	echo json_encode($output);
	
	die;
}

/**
 * Gets an array of html content for each column.
 * @return array  
 */
function espresso_promocodes_initial_jquery_datatables_data(){
	global $wpdb;
	$wpdb_results = $wpdb->get_results("SELECT * FROM ".EVENTS_DISCOUNT_CODES_TABLE." LIMIT 10");
	return espresso_promocodes_format_for_jquery_datatables($wpdb_results);
}

/**
 * Count all promocodes in existence in our db
 * @global type $wpdb
 * @return int
 */
function espresso_promocodes_count_total(){
	global $wpdb;
	return $wpdb->get_var("SELECT count(id) FROM ".EVENTS_DISCOUNT_CODES_TABLE);
}

/**
 * Formats wpdb results from query to the EVENTS_DISCOUNT_CODES_TABLE for use in a jquery data table
 * @param array $wpdb_result_objects results from $wpdb->get_results($query);
 * @return array formatted for jquery datatables row entries
 */
function espresso_promocodes_format_for_jquery_datatables($wpdb_result_objects){
	$prepared_results = array();
	foreach($wpdb_result_objects as $result_object){
		$jquery_datatables_row = array();
		
		$jquery_datatables_row[] = $checkbox_html = "<input name='checkbox[{$result_object->id}]' type='checkbox'  title='Delete {$result_object->coupon_code}'>";
		$jquery_datatables_row[] = $id_html = $result_object->id;
		
		$jquery_datatables_row[] = $name_html = "<strong><a href='admin.php?page=discounts&amp;action=edit&amp;discount_id={$result_object->id}'>{$result_object->coupon_code}</a></strong>".
					"<div class='row-actions'><span class='edit'><a href='admin.php?page=discounts&action=edit&amp;discount_id={$result_object->id}'>". __('Edit', 'event_espresso') ."</a> | </span><span class='delete'><a onclick='return confirmDelete();' class='submitdelete' href='admin.php?page=discounts&action=delete_discount&discount_id={$result_object->id}'>".__("Delete", "event_espresso")."</a></span></div>";
								
										
		if (function_exists('espresso_is_admin') && espresso_is_admin() == true) { 
				$jquery_datatables_row[] =  espresso_user_meta($result_object->wp_user, 'user_firstname') != '' ? espresso_user_meta($result_object->wp_user, 'user_firstname') . ' ' . espresso_user_meta($result_object->wp_user, 'user_lastname') : espresso_user_meta($result_object->wp_user, 'display_name'); 
		}
		$jquery_datatables_row[] = $result_object->coupon_code_price;
		$jquery_datatables_row[] = $result_object->use_percentage;
		$jquery_datatables_row[] = $result_object->apply_to_all ? 'Y' : 'N';
		$prepared_results[] = $jquery_datatables_row;
	}
	return $prepared_results;
}