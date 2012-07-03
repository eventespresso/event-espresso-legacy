<?php

//This is the registration form.
//This is a template file for displaying a registration form for an event on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
?>
<div id="event_espresso_registration_form">

    <h2 class="event_title" id="event_title-<?php echo $event_id; ?>"><?php echo $event_name ?> <?php echo $is_active['status'] == 'EXPIRED' ? ' - <span class="expired_event">Event Expired</span>' : ''; ?> </h2>

<?php if ( $display_desc == "Y" )
{//Show the description or not
?>
    <div class="event_description"><?php echo wpautop( $event_desc ); //Code to show the actual description. The Wordpress function "wpautop" adds formatting to your description.?></div>
    <?php

}//End display description
//print_r( event_espresso_get_is_active($event_id));
/* Displays the social media buttons */ ?>
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

                //Outputs the custom form questions. This function can be overridden using the custom files addon
                echo event_espresso_add_question_groups( $question_groups, '', $event_id, 1);

                //Outputs the shopping cart items
                if ( function_exists( 'event_espresso_add_cart_item_groups' ) )
                {
                    echo event_espresso_add_cart_item_groups( $item_groups );
                }

                //Coupons
                if ( function_exists( 'event_espresso_coupon_registration_page' ) )
                {
                    echo event_espresso_coupon_registration_page( $use_coupon_code, $event_id );
                }//End coupons display
                //Groupons
                if ( function_exists( 'event_espresso_groupon_registration_page' ) )
                {
                    echo event_espresso_groupon_registration_page( $use_groupon_code, $event_id );
                }//End groupons display
                //Multiple Attendees
                if ( $allow_multiple == "Y" )
                {

                    //This returns the additional attendee form fields. 
                    event_espresso_multi_additional_attendees( $extra['additional_attendee'], $number_available_spaces, $event_id );
                }
                else
                {
 ?>
                    <input type="hidden" name="num_people" id="num_people-<?php echo $event_id; ?>" value="1">
<?php }//End allow multiple  ?>

            <input type="hidden" name="regevent_action" id="regevent_action-<?php echo $event_id; ?>" value="post_attendee_multi">
                <input type="hidden" name="event_id" id="event_id-<?php echo $event_id; ?>" value="<?php echo $event_id; ?>">
    <?php

                //Recaptcha portion
                if ( $org_options['use_captcha'] == 'Y' && $_REQUEST['edit_details'] != 'true' )
                {
                    if ( !function_exists( 'recaptcha_get_html' ) )
                    {
                        require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/recaptchalib.php');
                    }//End require captcha library
                    # the response from reCAPTCHA
                    $resp = null;
                    # the error code from reCAPTCHA, if any
                    $error = null;
    ?>
                    <p class="event_form_field" id="captcha-<?php echo $event_id; ?>"><?php _e( 'Anti-Spam Measure: Please enter the following phrase', 'event_espresso' ); ?>
<?php echo recaptcha_get_html( $org_options['recaptcha_publickey'], $error ); ?>
                    </p>
<?php } //End use captcha  ?>

<?php

            }
            break;
    }//End Switch statement to check the status of the event
?>

</div>

