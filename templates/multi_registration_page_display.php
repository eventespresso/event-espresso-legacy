<?php

//This is the registration form.
//This is a template file for displaying a registration form for an event on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
?>
<div id="event_espresso_registration_form">

    <?php

    $num_attendees = ' - ' . $_SESSION['events_in_session'][$event_id]['attendee_quantitiy'] . __(' attendees', 'event_espresso');

    ?>
    <h1 class="event_title" id="event_title-<?php echo $event_id; ?>">
        <?php echo stripslashes_deep( $event_name ) ?>
        <?php echo $is_active['status'] == 'EXPIRED' ? ' - <span class="expired_event">Event Expired</span>' : '';?>
         -
         <?php echo _e('Price Type:') . ' ' . $meta['price_type'] ?>
          -
            <?php
                printf(__ngettext('%d attendee','%d attendees',$meta['attendee_quantity'],'event_espresso'), $meta['attendee_quantity']);
            ?>
    </h1>

    <?php

    $wrapper_class = '';

   /* if ( isset( $meta['copy_link'] ) && $meta['copy_link'] > 1 && $display_reg_form == 'Y'):
    ?>

        <label>Copy from: </label>
        <select id='<?php echo "event_espresso_copy_data-$event_id"; ?>' class="event_espresso_copy_info">
            <option value=''></option>


        <?php

        $i = 1;
        foreach ( $events_in_session as $k => $v ) {
            if ( $k != $event_id )
            {
                echo "<option value='$k|$event_id'>" . "Same as Event $i: " . stripslashes_deep( $v['event_name'] ) . "</option>";
            }
            $i++;
        }
        ?>

    </select>
    <?php

        $wrapper_class = 'multi_regis_form_fields';


    endif;*/
    ?>

    <div class="multi_regis_form_fields" id="multi_regis_form_fields-<?php echo $event_id . '-' . $meta['price_id']; ?>">

        <?php

        if ( $display_desc == "Y" )
        {//Show the description or not
        ?>

            <div class="event_description"><?php echo wpautop( $event_desc ); //Code to show the actual description. The Wordpress function "wpautop" adds formatting to your description. ?></div>
        <?php

        }//End display description
//print_r( event_espresso_get_is_active($event_id));
        /* Displays the social media buttons */
        ?>
        <p><?php echo espresso_show_social_media( $event_id, 'twitter' ); ?> <?php echo espresso_show_social_media( $event_id, 'facebook' ); ?></p>
        <?php

        switch ( $is_active['status'] )
        {
            case 'EXPIRED': //only show the event description.
                _e( '<h3 class="expired_event">This event has passed.</h3>', 'event_espresso' );
                break;

            case 'REGISTRATION_CLOSED': //only show the event description.
                // if todays date is after $reg_end_date
        ?>
                <p class="event_full"><strong><?php _e( 'We are sorry but registration for this event is now closed.', 'event_espresso' ); ?></strong></p>
                <p class="event_full"><strong><?php _e( 'Please <a href="contact" title="contact us">contact us</a> if you would like to know if spaces are still available.', 'event_espresso' ); ?></strong></p>
        <?php

                break;

            case 'REGISTRATION_NOT_OPEN': //only show the event description.
                // if todays date is after $reg_end_date
                // if todays date is prior to $reg_start_date
        ?>
                <p class="event_full"><strong><?php _e( 'We are sorry but this event is not yet open for registration.', 'event_espresso' ); ?></strong></p>
                <p class="event_full"><strong><?php _e( 'You will be able to register starting ' . event_espresso_no_format_date( $reg_start_date, 'F d, Y' ), 'event_espresso' ); ?></strong></p>
        <?php

                break;

            default:
                /* Display the address and google map link if available */
                if ( $location != '' )
                {
        ?>

        <?php

                }

                /*
                 * *
                  This section shows the registration form if it is an active event
                 * *
                 */

                if ( $display_reg_form == 'Y' )
                {
        ?>


        <?php

                    //Outputs the custom form questions.
                    //This will be the main attendee
                    $meta['attendee_number'] = 1;
                    echo "<h2>" . __('Attendee ', 'event_espresso') . "1</h2>";
                    echo event_espresso_copy_dd( $event_id, $meta );
                    echo event_espresso_add_question_groups( $question_groups, $events_in_session, $event_id, 1, $meta);
                    unset($meta['attendee_number']);
                    //Outputs the shopping cart items
                    if ( function_exists( 'event_espresso_add_cart_item_groups' ) )
                    {
                        echo event_espresso_add_cart_item_groups( $item_groups );
                    }

                    //Coupons
                    if ( function_exists( 'event_espresso_coupon_registration_page' ) )
                    {
                        // echo event_espresso_coupon_registration_page( $use_coupon_code, $event_id );
                    }//End coupons display
                    //Groupons
                    if ( function_exists( 'event_espresso_groupon_registration_page' ) )
                    {
                        //echo event_espresso_groupon_registration_page( $use_groupon_code, $event_id );
                    }//End groupons display
                    //Multiple Attendees
                    if ( $allow_multiple == "Y" )
                    {

                        //This returns the additional attendee form fields.
                        //
                        if ( $meta['additional_attendee_reg_info'] > 1 && $meta['attendee_quantity'] > 1)
                        {

                            //The offset of 2 since this is attendee 2 and on
                            //adding 1 since the primary attendee is added
                            //in the above function call (c.a. line 118)
                            //Used for "Attendee #" display
                            for ( $i = 2, $cnt = $meta['attendee_quantity'] + 1; $i < $cnt; $i++ ) {

                                $meta['attendee_number'] = $i;
                                echo "<h2>" . __('Attendee ', 'event_espresso') . $i . "</h2>";
                                echo event_espresso_copy_dd( $event_id, $meta );
                                event_espresso_add_question_groups( $question_groups, $events_in_session, $event_id, 1, $meta );
                            }
                            
                        }
                    }
                    else
                    {
        ?>

<?php }//End allow multiple    ?>

<?php

                }
                break;
        }//End Switch statement to check the status of the event
?>

    </div>
</div>

