<?php
function event_espresso_form_group_delete(){
	global $wpdb;
	if($_POST['delete_group']){
			if (is_array($_POST['checkbox'])){
				while(list($key,$value)=each($_POST['checkbox'])){
					$del_id=$key;
                    
                    $go_delete = true;
                    
                    if ( function_exists( 'espresso_member_data' ) ) {
                        if (function_exists( 'espresso_is_admin' ) ) {  
                            // If the user doesn't have admin access get only user's own question groups 
                            if ( !espresso_is_admin() ) { 
                                $go_delete = false;
                                $sql = " SELECT * FROM " . EVENTS_QST_GROUP_TABLE ." WHERE id = '" . $del_id . "' AND wp_user = '" . espresso_member_data( 'id' ) . "' ";
                                $rs = $wpdb->get_results( $sql );
                                if( is_array( $rs ) && count( $rs ) > 0 ) {
                                    $go_delete = true;
                                }
                            }
                        }
                    }
                    
                    if ( $go_delete ) {
                        //Delete question group data
                        $sql = "DELETE FROM " . EVENTS_QST_GROUP_TABLE . " WHERE id='" . $del_id . "'";
                        $wpdb->query($sql);

                        //Delete question group rel data
                        $sql = "DELETE FROM " . EVENTS_QST_GROUP_REL_TABLE . " WHERE group_id='" . $del_id . "'";
                        $wpdb->query($sql);
                    }
				}
			}
	}
	
	if($_REQUEST['action']== 'delete_group'){
        $go_delete = true;
                    
        if ( function_exists( 'espresso_member_data' ) ) {
            if (function_exists( 'espresso_is_admin' ) ) {  
                // If the user doesn't have admin access get only user's own question groups 
                if ( !espresso_is_admin() ) { 
                    $go_delete = false;
                    $sql = " SELECT * FROM " . EVENTS_QST_GROUP_TABLE ." WHERE id = '" . $_REQUEST['group_id'] . "' AND wp_user = '" . espresso_member_data( 'id' ) . "' ";
                    $rs = $wpdb->get_results( $sql );
                    if( is_array( $rs ) && count( $rs ) > 0 ) {
                        $go_delete = true;
                    }
                }
            }
        }
        
        if ( $go_delete ) {
            //Delete question group data
            $sql = "DELETE FROM " . EVENTS_QST_GROUP_TABLE . " WHERE id='" . $_REQUEST['group_id'] . "'";
            $wpdb->query($sql);

            //Delete question group rel data
            $sql = "DELETE FROM " . EVENTS_QST_GROUP_REL_TABLE . " WHERE group_id='" . $_REQUEST['group_id'] . "'";
            $wpdb->query($sql);
        }
	}
	?>
	<div id="message" class="updated fade">
	  <p><strong>
		<?php _e('Question Groups have been successfully deleted.','event_espresso');?>
		</strong></p>
	</div>
	<?php 
}