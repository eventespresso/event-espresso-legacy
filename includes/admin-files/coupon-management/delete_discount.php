<?php
function delete_event_discount(){
	global $wpdb;
	if(isset($_REQUEST['delete_discount'])){
		if (is_array($_POST['checkbox'])){
			foreach($_POST['checkbox'] as $key => $value) {
				//Delete discount data
				$wpdb->delete(EVENTS_DISCOUNT_CODES_TABLE, array('id' => $key), array('%d'));
				$wpdb->delete(EVENTS_DISCOUNT_REL_TABLE, array('discount_id' => $key),array('%d'));
			}
		}
	}
	if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete_discount'){
		//Delete discount data
		$wpdb->delete(EVENTS_DISCOUNT_CODES_TABLE, array('id' => $_REQUEST['discount_id']), array('%d'));
		$wpdb->delete(EVENTS_DISCOUNT_REL_TABLE, array('discount_id' => $_REQUEST['discount_id']), array('%d'));
	}
	?>
	<div id="message" class="updated fade">
	  <p><strong>
		<?php _e('Promotional Codes have been successfully deleted from the database.','event_espresso'); ?>
		</strong></p>
	</div>
	<?php
}