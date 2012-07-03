<?php
function event_espresso_insert_group(){
	global $wpdb;
	$wpdb->show_errors();
	$group_name= $_REQUEST['group_name'];
	$group_order = $_POST['group_order'];
	$group_identifier=($_REQUEST['group_identifier'] == '') ? $group_identifier = sanitize_title_with_dashes($group_name.'-'.time()) : $group_identifier = sanitize_title_with_dashes($_REQUEST['group_identifier']);
	$group_description= $_REQUEST['group_description'];
	$show_group_name = isset($_POST['show_group_name']) && $_POST['show_group_name'] !=''?1:0;
	$show_group_description = isset($_POST['show_group_description']) && $_POST['show_group_description'] != ''?1:0;

	$sql=array('group_name'=>$group_name,
            'group_identifier'=>$group_identifier,
            'group_description'=>$group_description,
            'group_order'=>$group_order,
             'show_group_name' => $show_group_name,
             'show_group_description' => $show_group_description);

	$sql_data = array('%s','%s','%s','%d','%d','%d');
	
	if (!$wpdb->insert( EVENTS_QST_GROUP_TABLE, $sql, $sql_data)){
		$error = true;
	}
	
		$last_group_id = $wpdb->insert_id;
		
		if ($_REQUEST['question_id'] != ''){
			foreach ($_REQUEST['question_id'] as $k=>$v){
				if($v != '') {
					$sql_qst_grp="INSERT INTO " . EVENTS_QST_GROUP_REL_TABLE . " (group_id, question_id) VALUES ('".$last_group_id."', '".$v."')";
					//echo "$sql3 <br>";
					if (!$wpdb->query($sql_qst_grp)){
						$error = true;
					}
				}
			}
		}

	if ($error != true){?>
		<div id="message" class="updated fade"><p><strong><?php _e('The group has been added.','event_espresso'); ?></strong></p></div>
<?php 
	}else { ?>
		<div id="message" class="error"><p><strong><?php _e('There was an error in your submission, please try again. The group was not saved!','event_espresso'); ?><?php print $wpdb->print_error(); ?>.</strong></p></div>
<?php 
	}
}