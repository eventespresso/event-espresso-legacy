<?php
function event_espresso_form_builder_delete(){
	global $wpdb;
	if(!empty($_REQUEST['delete_question']) && !empty($_POST['checkbox'])){
			if (is_array($_POST['checkbox'])){
				while(list($key,$value)=each($_POST['checkbox'])){
					$del_id=$key;
                    
                    $go_delete = true;
                    
                    if ( function_exists( 'espresso_member_data' ) ) {
                        if (function_exists( 'espresso_is_admin' ) ) {  
                            // If the user doesn't have admin access get only user's own question groups 
                            if ( !espresso_is_admin() ) { 
                                $go_delete = false;
                                $sql = " SELECT * FROM " . EVENTS_QUESTION_TABLE ." WHERE id = '" . $del_id . "' AND wp_user = '" . espresso_member_data( 'id' ) . "' ";
                                $rs = $wpdb->get_results( $sql );
                                if( is_array( $rs ) && count( $rs ) > 0 ) {
                                    $go_delete = true;
                                }
                            }
                        }
                    }
                    
                    if ( $go_delete ) {
                        //Delete question data
                        $sql = "DELETE FROM " . EVENTS_QUESTION_TABLE . " WHERE id='" . $del_id . "'";
                        $wpdb->query($sql);

                        //Delete question group rel data
                        $sql = "DELETE FROM " . EVENTS_QST_GROUP_REL_TABLE . " WHERE question_id='" . $del_id . "'";
                        $wpdb->query($sql);
                    }
				}
			}
	}

	if(!empty($_REQUEST['question_id']) && $_REQUEST['action']== 'delete_question'){
        
        $go_delete = true;
                    
        if ( function_exists( 'espresso_member_data' ) ) {
            if (function_exists( 'espresso_is_admin' ) ) {  
                // If the user doesn't have admin access get only user's own question groups 
                if ( !espresso_is_admin() ) { 
                    $go_delete = false;
                    $sql = " SELECT * FROM " . EVENTS_QUESTION_TABLE ." WHERE id = '" . $_REQUEST['question_id'] . "' AND wp_user = '" . espresso_member_data( 'id' ) . "' ";
                    $rs = $wpdb->get_results( $sql );
                    if( is_array( $rs ) && count( $rs ) > 0 ) {
                        $go_delete = true;
                    }
                }
            }
        }
        
        if ( $go_delete ) {
            //Delete question group data
            $sql = "DELETE FROM " . EVENTS_QUESTION_TABLE . " WHERE id='" . $_REQUEST['question_id'] . "'";
            $wpdb->query($sql);

            //Delete question group rel data
            $sql = "DELETE FROM " . EVENTS_QST_GROUP_REL_TABLE . " WHERE question_id='" . $_REQUEST['question_id'] . "'";
            $wpdb->query($sql);
        }
	}
	?>
	<div id="message" class="updated fade">
	  <p><strong>
		<?php _e('Questions have been successfully deleted.','event_espresso');?>
		</strong></p>
	</div>
	<?php
}