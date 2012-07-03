<?php

/**
 * Event Espresso Multi Event Registration Functions
 *
 *
 * @package		Event Espresso
 * @subpackage          Multi Event Registration and shopping cart functions
 * @author		Abel Sekepyan
 * @link		http://eventespresso.com/support/
 */
/**
 * Add event or item (planned for shopping cart) to the session
 *
 * @param $_POST
 *
 * @return JSON object
 */
if ( !function_exists( 'event_espresso_add_item_to_session' ) )
{


    function event_espresso_add_item_to_session() {
        global $wpdb;
        // echo "<pre>", print_r( $_POST ), "</pre>";
        $events_in_session = $_SESSION['events_in_session'];

        /*
         * added the cart_link_# to the page to prevent element id conflicts on the html page
         *
         */
        $id = $_POST['id'];
        //One link, multiple events
        if ( strpos( $id, "-" ) )
        {

            $event_group = explode( "-", $id );


            foreach ( $event_group as $event ) {

                $id = str_replace( 'cart_link_', '', $event );

                event_espresso_add_event_process( $id, $_POST['event_name'] );
            }
        }
        else //one event per click
        {

            $id = str_replace( 'cart_link_', '', $id );

            event_espresso_add_event_process( $id, $_POST['event_name'] );
        }

        $r = event_espresso_cart_link( array( 'event_id' => $id, 'view_cart' => TRUE, 'event_page_id' => $_POST['event_page_id'] ) );

        echo event_espresso_json_response( array( 'html' => $r, 'code' => 1 ) );
        //echo '<a href="' . site_url() . '/events/?regevent_action=show_shopping_cart">' . __( 'View Cart', 'event_espresso' ) . '</a>';

        die();
    }

}


/**
 * Processor function for adding items to the session
 *
 * @param event_id
 * @param event_name
 *
 * @return true
 */
if ( !function_exists( 'event_espresso_add_event_process' ) )
{


    function event_espresso_add_event_process( $event_id, $event_name ) {

        $events_in_session = $_SESSION['events_in_session'];

        $events_in_session[$event_id] = array(
            'id' => $event_id,
            'event_name' => stripslashes_deep( $event_name ),
            'attendee_quantitiy' => 1,
            'start_time_id' => '',
            'price_id' => '',
            'cost' => 0,
            'event_attendees' => array( )
        );


        $_SESSION['events_in_session'] = $events_in_session;


        return true;
    }

}

/**
 * Convert passed array to json object
 *
 * @param array
 *
 * @return JSON object
 */
if ( !function_exists( 'event_espresso_json_response' ) )
{


    function event_espresso_json_response( $params = array( ) ) {

        $params['code'] = 1;

        return json_encode( $params );
    }

}

/**
 * Return an individual Session variable
 *
 * @param key
 *
 * @return value of session key
 */
if ( !function_exists( 'event_espresso_return_session_var' ) )
{


    function event_espresso_return_session_var( $k = null ) {

        if ( is_null( $k ) )
            return;


        return array_key_exists( $k, $_SESSION ) ? $_SESSION[$k] : null;
    }

}

/**
 * Updates item information in the session
 *
 * @param $_POST
 *
 * @return true
 */
if ( !function_exists( 'event_espresso_update_item_in_session' ) )
{


    function event_espresso_update_item_in_session( $update_section = null ) {
        global $wpdb;

        /*
         * - grab the event sessions
         * - loop through the events and for each one
         * -- update the pricing, time options
         * -- update the attendee information
         */

//echo "$event_details_only YES";
        $events_in_session = $_SESSION['events_in_session'];

        //holds the updated infromation
        $updated_events_in_session = $events_in_session;
        //$updated_events_in_session = array( );

        if ( $update_section == 'details')
        {
            $event_cost = 0;
            foreach ( $events_in_session as $k => $v ) {

                $event_id = $k;
                $updated_events_in_session[$event_id]['id'] = $event_id;
                /*
                 * if the array key exists, update that array key with the value from post
                 */


                //Start time selection
                $start_time_id = '';
                if ( array_key_exists( 'start_time_id', $_POST ) && array_key_exists( $event_id, $_POST['start_time_id'] ) )
                {

                    $updated_events_in_session[$event_id]['start_time_id'] = $wpdb->escape( $_POST['start_time_id'][$event_id] );

                    //unset the post key so it doesn't get added below
                    unset( $_POST['start_time_id'][$event_id] );
                }

                //Attendee selection
                $attendee_quantitiy = 1;
                if ( array_key_exists( 'attendee_quantitiy', $_POST ) && array_key_exists( $event_id, $_POST['attendee_quantitiy'] ) )
                {
                    $attendee_quantitiy = $wpdb->escape( $_POST['attendee_quantitiy'][$event_id] );
                    $updated_events_in_session[$event_id]['attendee_quantitiy'] = $attendee_quantitiy;

                    unset( $_POST['attendee_quantitiy'][$event_id] );
                }

                //Pricing selection
                $price_id = null;
                if ( array_key_exists( 'price_option', $_POST ) && array_key_exists( $event_id, $_POST['price_option'] ) )
                {

                    $price_options = explode( '|', $_POST['price_option'][$event_id], 2 );
                    $price_id = $price_options[0];
                    $price_type = $price_options[1];

                    $updated_events_in_session[$event_id]['price_id'] = $wpdb->escape( $price_id );
                    $updated_events_in_session[$event_id]['price_type'] = $wpdb->escape( $price_type );

                    unset( $_POST['price_option'][$event_id] );
                }
                elseif ( array_key_exists( 'price_id', $_POST ) && array_key_exists( $event_id, $_POST['price_id'] ) )
                {

                    $price_id = $_POST['price_id'][$event_id];
                    $updated_events_in_session[$event_id]['price_id'] = $wpdb->escape( $price_id );
                    unset( $_POST['price_id'][$event_id] );
                }

                //Get Cost of each event
                $event_cost = !is_null( $price_id ) ? event_espresso_get_final_price( $price_id, $event_id ) : 0;
                $event_individual_cost[$event_id] = number_format( $event_cost * $attendee_quantitiy, 2, '.', '' );

                $updated_events_in_session[$event_id]['cost'] = $event_individual_cost[$event_id];
                $updated_events_in_session[$event_id]['event_name'] = $wpdb->escape( $_POST['event_name'][$event_id] );

                if ( isset( $_POST['event_espresso_coupon_code'] ) )
                {
                    $_SESSION['event_espresso_coupon_code'] = $wpdb->escape( $_POST['event_espresso_coupon_code'] );
                }
            }
        }


        $attendee_information = array( );
        if ( $update_section == 'attendees' )
        {

            if ( event_espresso_invoke_cart_error( $events_in_session ) )
                return false;

            //echo "<pre>", print_r( $_POST ), "</pre>";
            foreach ( $events_in_session as $k => $v ) {
                unset( $updated_events_in_session[$k]['event_attendees'] );
                foreach ( $_POST as $field_name => $field_value ) {

                    if ( is_array( $field_value ) && array_key_exists( $k, $field_value ) )
                    {

                        if ( is_multi( $field_value ) )
                        {

                            //$multi_key= $field_value[$k];
                            foreach ( $field_value[$k] as $mkey => $mval ) {
                                $updated_events_in_session[$k]['event_attendees'][$mkey][$field_name] = $mval;
                                //echo "multi $k > $field_name >" . $mkey . " > " . $mval . "<br />";
                            }
                        }
                        else
                        {
                            $updated_events_in_session[$k]['event_attendees'][$field_name] = $field_value[$k];
                            //echo "$k > $field_name >" . $field_value[$k] . "<br />";
                        }
                    }
                    else
                    {
                        //unset()
                    }
                }
            }
        }

        $_SESSION['events_in_session'] = $updated_events_in_session;
        //echo "<pre>", print_r($updated_events_in_session), "</pre>";


        return true;

        die();
    }

}


/**
 * Calculates total of the items in the session
 *
 * @param $_POST
 *
 * @return JSON (grand total)
 */
if ( !function_exists( 'event_espresso_calculate_total' ) )
{


    function event_espresso_calculate_total( $update_section = null ) {
        //print_r($_POST);

        $events_in_session = $_SESSION['events_in_session'];

        if ( count( $events_in_session ) == 0 )
            die( event_espresso_json_response( array( 'grand_total' => 0.00 ) ) );

        $event_cost = 0;
        $event_total_cost = 0;
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
            elseif ( array_key_exists( 'price_id', $_POST ) && array_key_exists( $event_id, $_POST['price_id'] ) )
            {

                $price_id = $_POST['price_id'][$event_id];
            }


            //echo $_POST['price_option'][$event_id] . $attendee_quantitiy ."<br />";
            $event_cost = !is_null( $price_id ) ? event_espresso_get_final_price( $price_id, $event_id ) : 0;
            $event_individual_cost[$event_id] = number_format( $event_cost * $attendee_quantitiy, 2, '.', '' );

            $event_total_cost += $event_individual_cost[$event_id];
        }

        $_SESSION['event_espresso_pre_discount_total'] = number_format( $event_total_cost, 2, '.', '' );

        // $response['html'] = number_format( $event_total_cost, 2, '.', '' );

        if ( isset( $_POST['event_espresso_coupon_code'] ) )
        {

            $event_total_cost = event_espresso_coupon_payment_page( 'Y', NULL, $event_total_cost, NULL );
        }

        $grand_total = number_format( $event_total_cost, 2, '.', '' );

        $_SESSION['event_espresso_grand_total'] = $grand_total;

        
        event_espresso_update_item_in_session( $update_section );

        /* print_r($event_individual_cost);
          echo number_format($event_total_cost,2, '.', ''); */

        //}
        if ($update_section == null){
            echo event_espresso_json_response( array( 'grand_total' => $grand_total ) );
            die();
        }
    }

}


/**
 * Delete and item from the session
 *
 * @param $_POST
 *
 * @return JSON 0 or 1
 */
if ( !function_exists( 'event_espresso_delete_item_from_session' ) )
{


    function event_espresso_delete_item_from_session() {
        global $wpdb;

        $events_in_session = $_SESSION['events_in_session'];

        /*
         * added the cart_link_# to the page to prevent element id conflicts on the html page
         *
         */
        $id = $_POST['id'];
        $id = str_replace( 'cart_link_', '', $id );

        unset( $events_in_session[$id] );

        if ( count( $events_in_session ) == 0 )
        {

            unset( $_SESSION['event_espresso_coupon_code'] );
            unset( $_SESSION['events_in_session'] );
            unset( $_SESSION['event_espresso_grand_total'] );
        } else
            $_SESSION['events_in_session'] = $events_in_session;


        echo event_espresso_json_response();

        die();
    }

}

/**
 * Loads the registration form based on information in the session
 *
 * @return HTML form
 */
if ( !function_exists( 'event_espresso_load_checkout_page' ) )
{


    function event_espresso_load_checkout_page() {
        global $wpdb, $org_options;

        $events_in_session = $_SESSION['events_in_session'];

        if ( event_espresso_invoke_cart_error( $events_in_session ) )
            return false;

        //echo "<pre>", print_r( $_SESSION ), "</pre>";
        if ( file_exists( EVENT_ESPRESSO_TEMPLATE_DIR . "multi_registration_page.php" ) )
        {
            require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "multi_registration_page.php"); //This is the path to the template file if available
        }
        else
        {
            require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/multi_registration_page.php");
        }

        $response['html'] = '';

        if ( count( $events_in_session ) > 0 )
        {
            foreach ( $events_in_session as $event ) {
                // echo $event['id'];
                if ( is_numeric( $event['id'] ) )
                    $events_IN[] = $event['id'];
            }

            $events_IN = implode( ',', $events_IN );


            $sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
            $sql .= " WHERE e.id in ($events_IN) ";
            $sql .= " ORDER BY e.start_date ";

            $result = $wpdb->get_results( $sql );


            $extra = array( );
//print_r($_POST);
?>
            <form autocomplete="off" id="event_espresso_checkout_form" method="post" action="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=post_multi_attendee">
    <?php

            $counter = 1;
            foreach ( $result as $r ) {

                $event_id = $r->id;
                $event_meta = unserialize( $r->event_meta );

                $extra['additional_attendee_reg_info'] = (is_array($event_meta) && array_key_exists( 'additional_attendee_reg_info', $event_meta )) ? $event_meta['additional_attendee_reg_info'] : 2;

                $extra['additional_attendee'] = 0;

                if ( (array_key_exists( 'attendee_quantitiy', $_POST ) && array_key_exists( $event_id, $_POST['attendee_quantitiy'] )) || isset( $events_in_session[$event_id]['attendee_quantitiy'] ) )
                {

                    $extra['additional_attendee'] = $_POST ? $_POST['attendee_quantitiy'][$event_id] - 1 : $events_in_session[$event_id]['attendee_quantitiy'] - 1;
                }

                $extra['copy_link'] = $counter; //event_espresso_copy_link($event_id,$events_in_session):'';


                multi_register_attendees( null, $event_id, $extra );
                // $response['html'] .=  "<br> . $r->event_name";


                $counter++;
            }
    ?>
            <input type="submit" class="submit" name="payment_page" value="<?php _e( 'Confirm and go to payment page', 'event_espresso' ); ?>" />

    <?php _e( ' or ', 'event_espresso' ); ?>

            <a href="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=show_shopping_cart">  <?php _e( 'Edit Cart', 'event_espresso' ); ?> </a>

        </form>

        <script>
            jQuery(function(){

                //Registration form validation
                jQuery('#event_espresso_checkout_form').validate();


            });
        </script>
<?php

        }


        //echo json_encode( $response );
        //die();
    }

}
?>
<?php


/**
 * NOT USED.  Returns the "Copy from " dropdown.
 */
function event_espresso_copy_link( $event_id, $events_in_session ) {

    $count_of_events = count( $events_in_session );

    if ( $count_of_events < 2 )
        return;

    $count_of_events =
            $var = "<select id='event_espresso_copy_data[$event_id]'>";
    $var .= "<option value=''></option>";
    foreach ( $events_in_session as $k => $v ) {

        $var .= $k != $event_id ? "<option>$event_id (" . $v['start_time_id'] . ")</option>" : '';
    }

    $var .= "</select>";

    return $var;

    return "<a href='#' class='event_espresso_copy_link' id='event_espresso_copy_link-$event_id'> Copy from above</a>";
}

/**
 * Add event or item (planned for shopping cart) to the session
 *
 * @param $_POST
 *
 * @return JSON object
 */
if ( !function_exists( 'event_espresso_confirm_and_pay' ) )
{


    function event_espresso_confirm_and_pay() {
        global $wpdb;

        $events_in_session = $_SESSION['events_in_session'];


        foreach ( $events_in_session as $k => $v ) {

            foreach ( $_POST as $field_name => $field_value ) {

                if ( is_array( $field_value ) && array_key_exists( $events_in_session, $field_value ) )
                {

                    if ( is_multi( $field_value ) )
                    {

                        //$multi_key= $field_value[$k];
                        foreach ( $field_value[$k] as $mkey => $mval ) {

                            echo "multi $k > $field_name >" . $mkey . " > " . $mval . "<br />";
                        }
                    }
                    else
                    {

                        echo "$k > $field_name >" . $field_value[$k] . "<br />";
                    }
                }
            }
            echo "<hr />";
        }
        //echo "<pre>" , print_r($_POST) , "</pre>";

        die();
    }

}


/**
 * Creates the # of Attendees dropdown in the shopping cart page
 *
 * @param $event_id
 * @param $qty - of attendees
 * @param $value - previously selected value
 *
 * @return JSON object
 */
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


    /**
     * Additional attendees grid
     *
     * @param $additional_limit -limit of attendees
     * @param $available_spaces -available spaces
     * @param $event_id
     *
     * @return JSON object
     */
    if ( !function_exists( 'event_espresso_multi_additional_attendees' ) )
    {


        function event_espresso_multi_additional_attendees( $additional_limit, $available_spaces, $event_id = null ) {
            if ( $additional_limit == 0 )
                return;
            $events_in_session = $_SESSION['events_in_session'];
?>
            <div class="event_espresso_add_attendee_wrapper-<?php echo $event_id; ?>">
    <?php

            while ( ($i < $additional_limit) && ($i < $available_spaces) ) {
                $i++;
    ?>

                <div class="additional_attendees-<?php echo $event_id . '-' . $i; ?>">
                    <p class="event_form_field additional_header" id="">
                        Additional Attendee <?php echo $i; ?>
                    </p>
                    <div class="clone espresso_add_attendee">
                        <p>
                            <label for="x_attendee_fname"><?php _e( 'First Name', 'event_espresso' ); ?><em>*</em></label>
                            <input type="text" name="x_attendee_fname[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required input' value="<?php echo $events_in_session[$event_id]['event_attendees']['x_attendee_fname'][$i] ?>" />
                        </p>
                        <p>
                            <label for="x_attendee_lname"><?php _e( 'Last Name', 'event_espresso' ); ?><em>*</em></label>
                            <input type="text" name="x_attendee_lname[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required input' value="<?php echo $events_in_session[$event_id]['event_attendees']['x_attendee_lname'][$i] ?>" />
                        </p>
                        <p>
                            <label for="x_attendee_email"><?php _e( 'Email', 'event_espresso' ); ?><em>*</em></label>
                            <input type="text" name="x_attendee_email[<?php echo $event_id; ?>][<?php echo $i; ?>]" class='required email input' value="<?php echo $events_in_session[$event_id]['event_attendees']['x_attendee_email'][$i] ?>" />
                        </p>
                    </div>
                </div>
    <?php

            }
            $i = $i - 1;
    ?>
        </div>
<?php

        }

    }


    /**
     * Creates add to cart link or view cart
     *
     * @param $array
     *
     * @return JSON object
     */
    if ( !function_exists( 'event_espresso_cart_link' ) )
    {


        function event_espresso_cart_link( $atts ) {
            $events_in_session = $_SESSION['events_in_session'];
            //print_r($atts);
            extract( shortcode_atts(
                            array(
                                'event_id' => NULL,
                                'anchor' => NULL,
                                'event_name' => NULL,
                                'separator' => NULL,
                                'view_cart' => FALSE,
                                'event_page_id' => NULL
                            ), $atts ) );

            $registration_cart_class = '';
            ob_start();

            if ( $view_cart || (is_array( $events_in_session ) && array_key_exists( $event_id, $events_in_session )) )
            {
                $registration_cart_url = get_option( 'siteurl' ) . '/?page_id=' . $event_page_id . '&regevent_action=show_shopping_cart';
                $registration_cart_anchor = __( "View Cart", 'event_espresso' );
            }
            else
            {
                $registration_cart_url = $externalURL != '' ? $externalURL : get_option( 'siteurl' ) . '/?page_id=' . $event_page_id . '&regevent_action=add_event_to_cart&event_id=' . $event_id . '&name_of_event=' . stripslashes_deep( $event_name );
                $registration_cart_anchor = $anchor;
                $registration_cart_class = 'ee_add_item_to_cart';
            }

            echo $separator . ' <a class="' . $registration_cart_class . '" id="cart_link_' . $event_id . '" href="' . $registration_cart_url . '" title="' . stripslashes_deep( $event_name ) . '">' . $registration_cart_anchor . '</a>';

            $buffer = ob_get_contents();
            ob_end_clean();
            return $buffer;
        }

    }
    add_shortcode( 'ESPRESSO_CART_LINK', 'event_espresso_cart_link' );

    if ( !function_exists( 'event_espresso_invoke_cart_error' ) )
    {


        function event_espresso_invoke_cart_error( $events_in_session ) {
            if ( !is_array( $events_in_session ))
            {

               echo  __( 'It looks like you are attempting to refresh a page after completing your registration.  Please go to the events page and try again.', 'event_espresso' ) . "<br />";
                return true;
            }
            return false;
        }

    }


    if ( !function_exists( 'event_espresso_clear_session' ) )
    {


        function event_espresso_clear_session() {

            $_SESSION['espresso_session_id'] = '';
            $_SESSION['events_in_session'] = '';
            $_SESSION['event_espresso_pre_discount_total'] = 0;
            $_SESSION['event_espresso_grand_total'] = 0;
            $_SESSION['event_espresso_coupon_code'] = '';
        }

    }
?>
