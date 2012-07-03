<?php

if ( !function_exists( 'event_espresso_multi_reg_css' ) )
{


    function event_espresso_multi_reg_css() {

        echo '<link type="text/css" rel="stylesheet" href="' . EVENT_ESPRESSO_PLUGINFULLURL . '/css/multi_reg_style.css" />' . "\n";
    }

}

if ( !function_exists( 'event_espresso_multi_reg_js_functions' ) )
{


    function event_espresso_multi_reg_js_functions() {
        //Have to do it here to get access to ee global definitions
?>
        <script>

            jQuery(document).ready(function() {


                jQuery('.add_event_to_session').click(function(){

                    //alert(jQuery(this).attr('href')) ;
                    /*
                     * 1. send the url as a post
                     * 2. replace the parent with the response.
                     */
                    var par = jQuery(this).parent();
                    show_loader(par);
                    var data = {
                        action: 'event_espresso_add_event_to_session',
                        regevent_action: "event_espresso_add_to_session",
                        event_id: jQuery(this).attr('id')
                    };

                    ee_do_ajax( data, par, '' );

                    return false;


                });

                jQuery('.delete_event_from_session').click(function(){

                    //alert(jQuery(this).attr('href')) ;
                    /*
                     * 1. send the url as a post
                     * 2. replace the parent with the response.
                     */
                    var par = jQuery(this).parents('.multi_reg_container');
                    show_loader(jQuery(this).parent());
                    var data = {
                        action: 'event_espresso_delete_event_from_session',
                        regevent_action: "event_espresso_delete_event_from_session",
                        event_id: jQuery(this).attr('id')
                    };

                    ee_do_ajax( data, par, 'remove' );

                    return false;


                });

                jQuery('#ee_multi_reg_cart').submit(function(){

                    var data = "action=event_espresso_calculate_total&" + jQuery(this).serialize()

                    show_loader(jQuery('#event_total_price'));
                    ee_do_ajax(data,jQuery('#event_total_price'),'');
                    return false;
                });

                jQuery('#ee_multi_reg_cart').trigger('submit');

                jQuery('#ee_continue_registration').click(function(){

                    var data = "action=event_espresso_load_regis_form&" + jQuery('#ee_multi_reg_cart').serialize()

                    show_loader(jQuery('#ee_multi_regis_form'));
                    ee_do_ajax(data,jQuery('#ee_multi_regis_form'),'');
                    return false;
                });

                jQuery('#event_espresso_confirm_pay').click(function(){

                    var data = "action=event_espresso_confirm_and_pay&" + jQuery('#ee_multi_reg_cart').serialize()


                   show_loader(jQuery('#temp'));
                   ee_do_ajax(data,jQuery('#temp'),'');
                    return false;
                });


                jQuery('.attendee_quantitiy').live('change', function(){

                    var me = jQuery(this);

                    // add_attendee_fields(me.next('.additional_attendees'), me.attr('id'), me.val());


                });




                /**
                 * add additional attendee fields (NOT YET in v1)
                 */
                function add_attendee_fields(container, event_id, count_of_attendees ) {

                    //if 1, slide up and remove
                    //if >1, find if the form of additional attendees is currently visible
                    //if new number is greater than the form rows available, add row difference
                    //if new number is less, delete extra rows

                    if (count_of_attendees >1){
                        var cl = jQuery('#espresso_add_attendee_template').html();

                        var kids =  $(container).children();

                        show_loader(jQuery(container));

                        container.slideDown('fast', function(){
                            container.html(cl);
                        })


                    }
                    else
                    {
                        container.slideUp('fast', function(){
                            container.html('');
                        })
                    }

                }
                function ee_do_ajax(data, response_container, act){
                    ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';

                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: data,
                        dataType: "html",
                        success: function(resp){

                            if (act=='remove'){
                                jQuery(response_container).slideUp('fast', function(){ jQuery(response_container).remove(); });
                            }
                            if (act=='alert'){
                                alert(resp);
                            }else if(response_container != ''){

                                jQuery( response_container.html(resp));
                            } else
                            {
                                jQuery( response_container.html(resp));
                            }


                        },
                        error: function(resp) {
                            alert("Error.");
                        }
                    });

                }



                function show_loader(container){

                    jQuery(container).html('<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/ajax-loader.gif">');

                }

            })
        </script>

<?php

    }

}

if ( !function_exists( 'event_espresso_multi_qty_dd' ) )
{


    function event_espresso_multi_qty_dd( $event_id, $qty, $value = '' ) {
        $counter = 0;
?>

        <select name="attendee_quantitiy[<?php echo $event_id; ?>]" id="attendee_quantitiy-<?php echo $event_id; ?>" class="attendee_quantitiy">
    <?php

        for ( $i = 1; $i <= $qty; $i++ ):
            $selected = $i == $value ? ' selected="selected" ' : '';
    ?>

            <option <?php echo $selected; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
<?php endfor; ?>

        </select>

<?php

        }

    }


    if ( !function_exists( 'event_espresso_add_event_to_session' ) )
    {


        function event_espresso_add_event_to_session() {
            global $wpdb;

            $events_in_session = $_SESSION['events_in_session'];

            /*
             * added the cart_link_# to the page to prevent element id conflicts on the html page
             *
             */
            $event_id = $_POST['event_id'];
            $event_id = str_replace( 'cart_link_', '', $event_id );

            $events_in_session[$event_id] = array(
                'id' => $event_id,
                'attendee_quantitiy' => 1,
                'start_time_id' => '',
                'price_id' => '',
                'cost' => 0,
            );

            $_SESSION['events_in_session'] = $events_in_session;

            $response['is_error'] = '0';
            $response['error_text'] = '';
            $response['html'] = '<a href="' . site_url() . '/events/?regevent_action=show_shopping_cart">' . __( 'View Cart', 'event_espresso' ) . '</a>';

            echo '<a href="' . site_url() . '/events/?regevent_action=show_shopping_cart">' . __( 'View Cart', 'event_espresso' ) . '</a>';

            die();
        }

    }

    if ( !function_exists( 'event_espresso_update_event_in_session' ) )
    {


        function event_espresso_update_event_in_session() {
            global $wpdb;

            $events_in_session = $_SESSION['events_in_session'];

            $updated_events_in_session = array( );

            $event_cost = 0;
            foreach ( $events_in_session as $k => $v ) {

                $event_id = $k;
                $updated_events_in_session[$event_id]['id'] = $event_id;
                /*
                 * if the array key exists, update that array key with the value form post
                 */

                $start_time_id = '';
                if ( array_key_exists( 'start_time_id', $_POST ) && array_key_exists( $event_id, $_POST['start_time_id'] ) )
                {

                    $updated_events_in_session[$event_id]['start_time_id'] = $wpdb->escape( $_POST['start_time_id'][$event_id] );
                }

                $attendee_quantitiy = 1;
                if ( array_key_exists( 'attendee_quantitiy', $_POST ) && array_key_exists( $event_id, $_POST['attendee_quantitiy'] ) )
                {
                    $attendee_quantitiy = $wpdb->escape( $_POST['attendee_quantitiy'][$event_id] );
                    $updated_events_in_session[$event_id]['attendee_quantitiy'] = $attendee_quantitiy;
                }

                $price_id = null;
                if ( array_key_exists( 'price_option', $_POST ) && array_key_exists( $event_id, $_POST['price_option'] ) )
                {

                    $price_options = explode( '|', $_POST['price_option'][$event_id], 2 );
                    $price_id = $price_options[0];
                    $price_type = $price_options[1];

                    $updated_events_in_session[$event_id]['price_id'] = $wpdb->escape( $price_id );
                }

                //echo $_POST['price_option'][$event_id] . $attendee_quantitiy ."<br />";
                $event_cost = !is_null( $price_id ) ? event_espresso_get_final_price( $price_id, $event_id ) : 0;
                $event_individual_cost[$event_id] = number_format( $event_cost * $attendee_quantitiy, 2, '.', '' );

                $updated_events_in_session[$event_id]['cost'] = $event_individual_cost[$event_id];
            }

            // $response['html'] = number_format( $event_total_cost, 2, '.', '' );

            $_SESSION['events_in_session'] = $updated_events_in_session;

            die();
        }

    }

    if ( !function_exists( 'event_espresso_delete_event_from_session' ) )
    {


        function event_espresso_delete_event_from_session() {
            global $wpdb;

            $events_in_session = $_SESSION['events_in_session'];

            /*
             * added the cart_link_# to the page to prevent element id conflicts on the html page
             *
             */
            $event_id = $_POST['event_id'];
            $event_id = str_replace( 'cart_link_', '', $event_id );

            unset( $events_in_session[$event_id] );

            $_SESSION['events_in_session'] = $events_in_session;


            $response['is_error'] = '0';
            $response['error_text'] = '';
            $response['html'] = '';

            echo json_encode( $response );

            die();
        }

    }

    if ( !function_exists( 'event_espresso_calculate_total' ) )
    {


        function event_espresso_calculate_total() {
            //print_r($_POST);

            $events_in_session = $_SESSION['events_in_session'];

//         if ($_POST)        {
            $event_cost = 0;
            foreach ( $events_in_session as $k => $v ) {

                $event_id = $k;


                $start_time_id = '';
                if ( array_key_exists( 'start_time_id', $_POST ) && array_key_exists( $event_id, $_POST['start_time_id'] ) )
                {

                    $start_time_id = $_POST['start_time_id'][$event_id];
                }

                $attendee_quantitiy = 1;
                if ( array_key_exists( 'attendee_quantitiy', $_POST ) && array_key_exists( $event_id, $_POST['attendee_quantitiy'] ) )
                {

                    $attendee_quantitiy = $_POST['attendee_quantitiy'][$event_id];
                }

                $price_id = null;
                if ( array_key_exists( 'price_option', $_POST ) && array_key_exists( $event_id, $_POST['price_option'] ) )
                {

                    $price_options = explode( '|', $_POST['price_option'][$event_id], 2 );
                    $price_id = $price_options[0];
                    $price_type = $price_options[1];
                }

                //echo $_POST['price_option'][$event_id] . $attendee_quantitiy ."<br />";
                $event_cost = !is_null( $price_id ) ? event_espresso_get_final_price( $price_id, $event_id ) : 0;
                $event_individual_cost[$event_id] = number_format( $event_cost * $attendee_quantitiy, 2, '.', '' );

                $event_total_cost += $event_individual_cost[$event_id];
            }

            // $response['html'] = number_format( $event_total_cost, 2, '.', '' );



            echo number_format( $event_total_cost, 2, '.', '' );

            event_espresso_update_event_in_session();

            /* print_r($event_individual_cost);
              echo number_format($event_total_cost,2, '.', ''); */

            //}

            die();
        }

    }

    if ( !function_exists( 'event_espresso_load_regis_form' ) )
    {


        function event_espresso_load_regis_form() {
            global $wpdb;

            if ( file_exists( EVENT_ESPRESSO_TEMPLATE_DIR . "multi_registration_page.php" ) )
            {
                require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "multi_registration_page.php"); //This is the path to the template file if available
            }
            else
            {
                require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/multi_registration_page.php");
            }
            $events_in_session = $_SESSION['events_in_session'];
            $response['html'] = '';

            if ( count( $events_in_session ) > 0 )
            {
                foreach ( $events_in_session as $event ) {
                    // echo $event['id'];

                    $events_IN[] = $event['id'];
                }

                $events_IN = implode( ',', $events_IN );


                $sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
                $sql .= " WHERE e.id in ($events_IN) ";

                $result = $wpdb->get_results( $sql );
            }

            $extra = array( );
//print_r($_POST);
            foreach ( $result as $r ) {

                $event_id = $r->id;
                $additional_attendee = 0;
                if ( array_key_exists( 'attendee_quantitiy', $_POST ) && array_key_exists( $event_id, $_POST['attendee_quantitiy'] ) )
                {

                    $extra['additional_attendee'] = $_POST['attendee_quantitiy'][$event_id] - 1;
                }
                //echo "<br>  $r->id";
                multi_register_attendees( null, $event_id, $extra );
                // $response['html'] .=  "<br> . $r->event_name";
            }



            //echo json_encode( $response );
            die();
        }

    }


    if ( !function_exists( 'event_espresso_confirm_and_pay' ) )
    {


        function event_espresso_confirm_and_pay() {
            global $wpdb;

            echo "<pre>" , print_r($_POST) , "</pre>";

            die();

        }

    }

//Function to display additional attendee fields.
    if ( !function_exists( 'event_espresso_multi_additional_attendees' ) )
    {


        function event_espresso_multi_additional_attendees( $additional_limit, $available_spaces, $event_id = null ) {
            if ( $additional_limit == 0 )
                return;
            while ( ($i < $additional_limit) && ($i < $available_spaces) ) {
                $i++;


?>
                <p class="event_form_field additional_header" id="additional_header">
                    Additional Attendee
                </p>
                <div id="additional_attendees">
                    <div class="clone espresso_add_attendee">
                        <p>
                            <label for="x_attendee_fname"><?php _e( 'First Name', 'event_espresso' ); ?>:</label>
                            <input type="text" name="x_attendee_fname[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required input'/>
                        </p>
                        <p>
                            <label for="x_attendee_lname"><?php _e( 'Last Name', 'event_espresso' ); ?>:</label>
                            <input type="text" name="x_attendee_lname[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required input'/>
                        </p>
                        <p>
                            <label for="x_attendee_email"><?php _e( 'Email', 'event_espresso' ); ?>:</label>
                            <input type="text" name="x_attendee_email[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required email input'/>
                        </p>
                    </div>
                </div>
<?php

            }
            $i = $i - 1;
        }

    }




