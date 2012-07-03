<?php

if ( !function_exists( 'event_espresso_shopping_cart' ) )
{


    function event_espresso_shopping_cart() {

        global $wpdb, $org_options;
//session_destroy();
        echo "<pre>", print_r( $_SESSION ), "</pre>";

        $events_in_session = $_SESSION['events_in_session'];

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
?>

            <form action='' method='post' id="ee_multi_reg_cart">


    <?php

            foreach ( $result as $r ):

                $num_attendees = get_number_of_attendees_reg_limit( $r->id, 'num_attendees' ); //Get the number of attendees
                $available_spaces = get_number_of_attendees_reg_limit( $r->id, 'available_spaces' ); //Gets a count of the available spaces
                $number_available_spaces = get_number_of_attendees_reg_limit( $r->id, 'number_available_spaces' ); //Gets the number of available spaces
                //echo "<pre>$r->id, $num_attendees,$available_spaces,$number_available_spaces</pre>";
    ?>
                <div class="multi_reg_container" style="margin:5px; border: 1px solid #d1d1d1;padding:5px;">
                    <p><strong><?php echo $r->event_name ?></strong>
                        <span style="float:right">
                            <img class="delete_event_from_session" id="cart_link_<?php echo $r->id ?>" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/remove.gif" />
                        </span>
                    </p>
                    <div>
                        <p id="event_date-<?php echo $event_id ?>"><?php _e( 'Dates:', 'event_espresso' ); ?>
                <?php echo event_date_display( $r->start_date, get_option( 'date_format' ) ) ?> <?php _e( ' to ', 'event_espresso' ); ?> <?php echo event_date_display( $r->end_date, get_option( 'date_format' ) ) ?>
            </p>
            <div>
                <label>Time</label>
                <?php event_espresso_time_dropdown( $r->id, 0, 1, $_SESSION['events_in_session'][$r->id]['start_time_id'] ); ?>
                <label>Price</label>
                <?php event_espresso_price_dropdown( $r->id, 0, 1, $_SESSION['events_in_session'][$r->id]['price_id'] ); ?>
                <label># of Attendees</label>
                <?php event_espresso_multi_qty_dd( $r->id, $r->additional_limit + 1, $_SESSION['events_in_session'][$r->id]['attendee_quantitiy'] ); ?>
                <div class="additional_attendees">


                </div>
            </div>

        </div>
    </div>

    <?php

                endforeach;
    ?>

                <table width="90%" border="0" id="event_espresso_attendee_verify">
                    <tr>
                        <td><strong class="event_espresso_name">
                    <?php _e( 'Total (' . $org_options['currency_symbol'] . '):', 'event_espresso' ); ?>
                </strong> <input type="submit" name="Total" value="Calculate" /> </td>
            <td><span id="event_total_price">--</span></td>
        </tr>
    </table>

    

<input type="submit" name="Continue" id="ee_continue_registration" value="Continue Registration" />

<div id="ee_multi_regis_form">

    Your registration form will load here.


</div>
</form>
<input type="submit" name="event_espresso_confirm_pay" id="event_espresso_confirm_pay" value="Confirm and Pay" />

<div id="temp">123</div>

<!--<div id="espresso_add_attendee_template">
    <div class="espresso_add_attendee_container">
        <label for="x_attendee_fname"><?php _e( 'First Name', 'event_espresso' ); ?>:</label>
        <input type="text" name="x_attendee_fname[]" class='input'/>
        <label for="x_attendee_lname"><?php _e( 'Last Name', 'event_espresso' ); ?>:</label>
        <input type="text" name="x_attendee_lname[]" class='input'/>
        <label for="x_attendee_email"><?php _e( 'Email', 'event_espresso' ); ?>:</label>
        <input type="text" name="x_attendee_email[]" class='input'/>
        <label for="x_attendee_phone"><?php _e( 'Phone', 'event_espresso' ); ?>:</label>
        <input type="text" name="x_attendee_phone[]" class='input'/>
    </div>
</div>-->


<?php

                }
            }

        }
?>
