<?php 
function update_event_venue(){
	global $wpdb;
	$wpdb->show_errors();
    $venue_updated = false;
    if ( isset( $_REQUEST[ 'venue_id' ] ) ) {
        
        $venue_id = $_REQUEST[ 'venue_id' ];
        
        if ( function_exists( 'espresso_user_has_venue_permission' ) ) {
            if ( !espresso_user_has_venue_permission( $venue_id ) ) {
                echo '<h2>' . __('Sorry, you do not have permission to edit this Venue.', 'event_espresso') . '</h2>'; 
                return;
            }
        }
        
        //print_r($_REQUEST);
        $venue_meta['contact']      = isset( $_REQUEST['contact'] ) ? $_REQUEST['contact'] : '';
        $venue_meta['phone']        = isset( $_REQUEST['phone'] ) ? $_REQUEST['phone'] : '';
        $venue_meta['twitter']      = isset( $_REQUEST['twitter'] ) ? $_REQUEST['twitter'] : '';
        $venue_meta['image']        = isset( $_REQUEST['image'] ) ? $_REQUEST['image'] : '';
        $venue_meta['website']      = isset( $_REQUEST['website'] ) ? $_REQUEST['website'] : '';
        $venue_meta['description']  = isset( $_REQUEST['description'] ) ? esc_html( $_REQUEST['description'] ) : '';
        $locale                     = isset( $_REQUEST['locale'] ) ? $_REQUEST['locale'] : '';
        $meta                       = serialize($venue_meta);


        $sql = array( 
                'name'      => isset( $_REQUEST[ 'name' ] ) ? $_REQUEST['name'] : '',
                'address'   => isset( $_REQUEST[ 'address' ] ) ? $_REQUEST['address'] : '',
                'address2'  => isset( $_REQUEST[ 'address2' ] ) ? $_REQUEST['address2'] : '',
                'city'      => isset( $_REQUEST[ 'city' ] ) ? $_REQUEST['city'] : '',
                'state'     => isset( $_REQUEST[ 'state' ] ) ? $_REQUEST['state'] : '',
                'zip'       => isset( $_REQUEST[ 'zip' ] ) ? $_REQUEST['zip'] : '',
                'country'   => isset( $_REQUEST[ 'country' ] ) ? $_REQUEST['country'] : '',
                'meta'      => $meta
            ); 

        $update_id = array( 'id'=> $venue_id );
        /*echo 'Debug: <br />';
        print_r($sql);
        print 'Number of vars: ' . count ($sql);
        echo '<br />';
        print 'Number of cols: ' . count($sql_data);*/

        $sql_data = array( '%s','%s','%s','%s','%s','%s','%s','%s' );
        $wpdb->update( EVENTS_VENUE_TABLE, $sql, $update_id, $sql_data, array( '%d' ) ); 
        #if ( $wpdb->rows_affected > 0 ) {
        $venue_updated = true; 
        if( !empty( $locale ) ){ 
            $wpdb->query( "DELETE FROM " . EVENTS_LOCALE_REL_TABLE . " WHERE venue_id='" . $venue_id . "'" );
            $sql_locale = "INSERT INTO ".EVENTS_LOCALE_REL_TABLE." ( venue_id, locale_id) VALUES ( '" . $venue_id . "', '" . $locale . "')";
            if ( !$wpdb->query( $sql_locale ) ) {
                $error = true;
            }
        }
        #}
        
    }
    
    if ( $venue_updated ) {
?>	
	<div id="message" class="updated fade">
			<p><strong><?php _e('The venue  has been updated.','event_espresso'); ?></strong></p>
         </div>	
         
<?php
    }
}