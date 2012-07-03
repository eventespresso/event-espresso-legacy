<?php


function edit_attendee_record() {
    global $wpdb;
    if ( $_REQUEST['form_action'] == 'edit_attendee' )
    {
        
        $id = $_REQUEST['id'];

        $registration_id = $_REQUEST['registration_id'];

        /*
         * find the primary attendee so we know which form to present
         * since the additional attendees will have a different form
         */

        $r = $wpdb->get_row("SELECT id from " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id ='$registration_id' ORDER BY id");

        $primary_attendee = $r->id;

        $is_additional_attendee = ($primary_attendee != $id)?true:false;

        if ( $_REQUEST['attendee_action'] == 'delete_attendee' )
        {

            $sql = " DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id ='$id'";
            $wpdb->query( $sql );
            $sql = " UPDATE " . EVENTS_ATTENDEE_TABLE . " SET quantity = IF(IS NULL quantity,NULL,quantity-1 WHERE registration_id ='$registration_id'";
            $wpdb->query( $sql );
        }

        /*
         * Update the attendee information
         */
        else if ( $_REQUEST['attendee_action'] == 'update_attendee' )
        {


            

            $fname = $_POST['fname'];
            $lname = $_POST['lname'];
            $address = $_POST['address'];
            $address2 = $_POST['address2'];
            $city = $_POST['city'];
            $state = $_POST['state'];
            $zip = $_POST['zip'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $event_id = $_POST['event_id'];
            $payment = $_POST['payment'];
            

            $sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET fname='$fname', lname='$lname', address='$address',address2='$address2', city='$city', state='$state', zip='$zip', phone='$phone', email='$email', payment='$payment' WHERE id ='$id'";
            $wpdb->query( $sql );

            //print_r($_POST);
            // Insert Extra From Post Here
            $reg_id = $id;

            $response_source = $_POST;

            //With the new

            $questions = $wpdb->get_row( "SELECT question_groups, event_meta FROM " . EVENTS_DETAIL_TABLE . "
                            WHERE id = " . $event_id . " " );

            $question_groups = unserialize( $questions->question_groups );
            $event_meta=unserialize($questions->event_meta);
            
            if($is_additional_attendee && isset($event_meta['add_attendee_question_groups'])){
                
                $question_groups = $event_meta['add_attendee_question_groups'];
            }
            
                $questions_in = '';

                foreach ( $question_groups as $g_id )
                    $questions_in .= $g_id . ',';

                $questions_in = substr( $questions_in, 0, -1 );
                $group_name = '';
                $counter = 0;

                //pull the list of questions that are relevant to this event
                $questions = $wpdb->get_results( "SELECT q.*, qg.group_name
                                                            FROM " . EVENTS_QUESTION_TABLE . " q
                                                            JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr
                                                                on q.id = qgr.question_id
                                                            JOIN " . EVENTS_QST_GROUP_TABLE . " qg
                                                                on qg.id = qgr.group_id
                                                                WHERE qgr.group_id in ( " . $questions_in
                                . ") ORDER BY qg.id, q.sequence ASC" );


                if ( $questions )
                {

                    /*
                     * find the list of answers in the answer table so we don't insert again because 
                     * when running an update query and the values don't change, the rows_affected will be 0
                     */

                     $answers = $wpdb->get_results( "
                                                                                        SELECT question_id, answer FROM " . EVENTS_ANSWER_TABLE . " at
                                                                                        WHERE at.attendee_id = '" . $id . "'
                                                        ", OBJECT_K );


                    foreach ( $questions as $question ) {
                        $sql = '';
                        switch ( $question->question_type )
                        {
                            case "TEXT" :
                            case "TEXTAREA" :
                            case "DROPDOWN" :
                                //$post_val = $_POST [ $question->question_type . '_' . $question->question_id ];
                                $post_val = ( $question->system_name != '') ? $response_source[$question->system_name] : $response_source[$question->question_type . '_' . $question->id];
                                //echo '<p> SOMETHING HERE ' . $post_val . '</p>';

                                if(array_key_exists( $question->id, $answers )){
                                  $sql = "UPDATE " . EVENTS_ANSWER_TABLE . " SET answer='$post_val' WHERE attendee_id = '$id' AND question_id ='$question->id'";
                                }else{

                                    $sql = "INSERT INTO " . EVENTS_ANSWER_TABLE . " (registration_id, answer,attendee_id,question_id) VALUES ('$registration_id','$post_val', $id,$question->id)";

                                }

                                break;
                            case "SINGLE" :
                                //$post_val = $_POST [ $question->question_type . '_' . $question->question_id ];
                                $post_val = ($question->system_name != '') ? $response_source[$question->system_name] : $response_source[$question->question_type . '_' . $question->id];
                                if(array_key_exists( $question->id, $answers )){
                                  $sql = "UPDATE " . EVENTS_ANSWER_TABLE . " SET answer='$post_val' WHERE attendee_id = '$id' AND question_id ='$question->id'";
                                }else{

                                    $sql = "INSERT INTO " . EVENTS_ANSWER_TABLE . " (registration_id, answer,attendee_id,question_id) VALUES ('$registration_id','$post_val', $id,$question->id)";

                                }
                                break;
                            case "MULTIPLE" :
                                /* $value_string = '';
                                  for ( $i = 0; $i < count( $_POST[ $question->question_type . '_' . $question->question_id ] ); $i++ ) {
                                  //$value_string = $value_string +","+ ($_POST[$question->question_type.'_'.$question->id][$i]);
                                  $value_string .= $_POST[ $question->question_type . '_' . $question->question_id ][ $i ] . ",";
                                  } */
                                //echo "Value String - " . $value_string;
                                /* $values = explode ( ",", $question->response );
                                  $value_string = '';
                                  foreach ( $values as $key => $value ) {
                                  $post_val = $_POST [$question->question_type . '_' . $question->id . '_' . $key];
                                  if ($key > 0 && ! empty ( $post_val )) $value_string .= ',';
                                  $value_string .= $post_val;
                                  } */

                                $value_string = '';
                                for ( $i = 0; $i < count( $response_source[$question->question_type . '_' . $question->id] ); $i++ ) {

                                    $value_string .= trim( $response_source[$question->question_type . '_' . $question->id][$i] ) . ",";
                                }

                                if(array_key_exists( $question->id, $answers )){
                                  $sql = "UPDATE " . EVENTS_ANSWER_TABLE . " SET answer='$value_string' WHERE attendee_id = '$id' AND question_id ='$question->id'";
                                }else{

                                    $sql = "INSERT INTO " . EVENTS_ANSWER_TABLE . " (registration_id, answer,attendee_id,question_id) VALUES ('$registration_id','$value_string', $id,$question->id)";

                                }
                               
                                break;
                        }

                        if ($sql != '')
                            $wpdb->query( $sql );

                    }
                }
            }


            $counter = 0;
            $additional_attendees = NULL;

            $WHERE = (isset( $_REQUEST['registration_id'] )) ? "registration_id ='" . $_REQUEST['registration_id'] . "'" : "id = " . $_REQUEST['id'];



            $results = $wpdb->get_results( "SELECT  t1.*, t2.event_name, t2.question_groups, t2.event_meta FROM " . EVENTS_ATTENDEE_TABLE . " t1
                 JOIN " . EVENTS_DETAIL_TABLE . " t2
                 ON t1.event_id = t2.id
                 WHERE t1.$WHERE
                 ORDER BY t1.id" );

            foreach ( $results as $result ) {


                if ( $counter == 0 )
                {

                    $id = $result->id;
                    $registration_id = $result->registration_id;
                    $lname = $result->lname;
                    $fname = $result->fname;
                    $address = $result->address;
                    $address2 = $result->address2;
                    $city = $result->city;
                    $state = $result->state;
                    $zip = $result->zip;
                    $email = $result->email;
                    $hear = $result->hear;
                    $payment = $result->payment;
                    $phone = $result->phone;
                    $date = $result->date;
                    $payment_status = $result->payment_status;
                    $txn_type = $result->txn_type;
                    $txn_id = $result->txn_id;
                    $amount_pd = $result->amount_pd;
                    $quantity = $result->quantity;
                    $payment_date = $result->payment_date;
                    $event_id = $result->event_id;
                    $event_name = $result->event_name;
                    $question_groups = unserialize( $result->question_groups );

                      $question_groups = unserialize( $result->question_groups );
                      $event_meta=unserialize($result->event_meta);

                      $is_additional_attendee = ($primary_attendee != $id)?true:false;
                      
            if($is_additional_attendee && isset($event_meta['add_attendee_question_groups'])){
                
                $question_groups = $event_meta['add_attendee_question_groups'];
            }

					/*$values=array(					
										array('id'=>'Y','text'=> __('Yes','event_espresso')),
										array('id'=>'N','text'=> __('No','event_espresso')));*/

                    $counter = 1;
                }
                else
                {
                    $additional_attendees[$result->id] = array( 'full_name' => $result->fname . ' ' . $result->lname, 'email' => $result->email, 'phone' => $result->phone );
                }
            }

            if ( $_REQUEST['status'] == 'saved' )
            {
?>
                <div id="message" class="updated fade"><p><strong><?php _e( 'Attendee details saved for ' . $fname . ' ' . $lname . '.', 'event_espresso' ); ?></strong></p></div>
<?php } ?>
            <div class="metabox-holder">
                <div class="postbox">
                    <h3><?php _e( 'Registration Id #' . $registration_id . ' | Name: ' . $fname . ' ' . $lname . ' | Registered For:', 'event_espresso' ); ?> <a href="admin.php?page=events#event-id-<?php echo $event_id ?>"><?php echo stripslashes_deep( $event_name ) ?></a></h3>
        <div class="inside">
            <table>
                <tr><td><form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class="espresso_form">
                            <fieldset>
                                <ul>
<?php /* ?> <li><label><?php _e( 'First Name:', 'event_espresso' ); ?></label> <input tabindex="1" maxlength="45" name="fname" value ="<?php echo $fname; ?>" /> </li>
                                      <li><label><?php _e( 'Last Name:', 'event_espresso' ); ?> </label><input tabindex="2" maxlength="45"name="lname" value ="<?php echo $lname; ?>" /></li>
                                      <li><label><?php _e( 'Email:', 'event_espresso' ); ?> </label><input tabindex="3" maxlength="37" size="37" name="email" value ="<?php echo $email; ?>" /></li><?php */ ?>
                                    <li><label><?php _e( 'How is attendee paying for registration?', 'event_espresso' ); ?></label>
                                        <select tabindex="10" size="1" name="payment">
                                            <option value="<?php echo $payment; ?>" selected="selected"><?php echo $payment; ?></option>
                                            <option value="Paypal"><?php _e( 'Credit Card or Paypal', 'event_espresso' ); ?></option>
                                            <option value="Cash"><?php _e( 'Cash', 'event_espresso' ); ?></option>
                                            <option value="Check"><?php _e( 'Check', 'event_espresso' ); ?></option>
                                        </select></li>
                                    <?php

                                    if ( count( $question_groups ) > 0 )
                                    {
                                        $questions_in = '';

                                        foreach ( $question_groups as $g_id )
                                            $questions_in .= $g_id . ',';

                                        $questions_in = substr( $questions_in, 0, -1 );
                                        $group_name = '';
                                        $counter = 0;

                                        //pull the list of questions that are relevant to this event
                                        $questions = $wpdb->get_results( "SELECT q.*, qg.group_name
                                                            FROM " . EVENTS_QUESTION_TABLE . " q
                                                            JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr
                                                                on q.id = qgr.question_id
                                                            JOIN " . EVENTS_QST_GROUP_TABLE . " qg
                                                                on qg.id = qgr.group_id
                                                                WHERE qgr.group_id in ( " . $questions_in
                                                        . ") ORDER BY qg.id, q.sequence ASC" );
                                        $num_rows = $wpdb->num_rows;
                                        $answers = $wpdb->get_results( "
                                                                                        SELECT question_id, answer FROM " . EVENTS_ANSWER_TABLE . " at
                                                                                        WHERE at.attendee_id = '" . $id . "'
                                                        ", OBJECT_K );



                                        if ( $num_rows > 0 )
                                        {
                                            foreach ( $questions as $question ) {

                                                //if new group, close fieldset
                                                echo ($group_name != '' && $group_name != $question->group_name) ? '</fieldset>' : '';

                                                if ( $group_name != $question->group_name )
                                                {
                                                    echo "<hr /><fieldset><legend>$question->group_name<legend>";
                                                    $group_name = $question->group_name;
                                                }

                                                $_answers = (array_key_exists( $question->id, $answers )) ? $answers[$question->id] : '';

                                                echo '<p>';
                                                event_form_build_edit( $question, ($question->system_name != '') ? ${$question->system_name} : $_answers->answer );
                                                echo "</p>";


                                                $counter++;
                                                echo $counter == $num_rows ? '</fieldset>' : '';
                                            }
                                        }//end questions display
                                    }
                                    
									
									?>
                                    <input type="hidden" name="id" value="<?php echo $id ?>" />
                                    <input type="hidden" name="registration_id" value="<?php echo $registration_id ?>" />
                                    <input type="hidden" name="event_id" value="<?php echo $event_id ?>" />
                                    <input type="hidden" name="display_action" value="view_list" />
                                    <input type="hidden" name="view_event" value="<?php echo $view_event ?>" />
                                    <input type="hidden" name="form_action" value="edit_attendee" />
                                    <input type="hidden" name="attendee_action" value="update_attendee" />

                                    <li><input type="submit" name="Submit" value="<?php _e( 'Update Record', 'event_espresso' ); ?>" /></li></ul>
                                            </fieldset>

                                        </form></td>


<?php if ( count( $additional_attendees ) > 0 ): ?>
                            <td valign="top">
                                <div style="margin:0 100px;">
                                    <p><strong><?php _e( 'Additional Attendees', 'event_espresso' ); ?></strong></p>
                            <ol>
<?php foreach ( $additional_attendees as $att => $row ): ?>
                                    <li>
                                        <a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;event_id=<?php echo $event_id; ?>&amp;id=<?php echo $att; ?>&amp;form_action=edit_attendee" title="<?php _e( 'Edit Attendee', 'event_espresso' ); ?>">
                                <?php echo $row['full_name']; ?> (<?php echo $row['email'] . ', ' . $row['phone']; ?>)</a>
                                                <a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;event_id=<?php echo $event_id; ?>&amp;registration_id=<?php echo $registration_id; ?>&amp;attendee_id=<?php echo $att; ?>&amp;form_action=edit_attendee&amp;attendee_action=delete_attendee" title="<?php _e( 'Delete Attendee', 'event_espresso' ); ?>" onclick="return confirmDelete();">
                                                    <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>images/icons/remove.gif" width="16" height="16" />
                                                </a>

                                            </li>
<?php endforeach; ?>
                                        </ol></div>

                                </td>
                            </tr><?php endif; ?>
                        </table>


                    </div>
                </div>
            </div>
<?php

                                            event_list_attendees();
                                        }
                                    }

