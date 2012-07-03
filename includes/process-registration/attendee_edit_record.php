<?php
function attendee_edit_record() {
    global $wpdb, $org_options;
   

        if ( $_REQUEST[ 'attendee_action' ] == 'delete_attendee' )
        {
            $id = $_REQUEST[ 'attendee_id' ];
            $registration_id = $_REQUEST[ 'registration_id' ];
            $sql = " DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id ='$id'";
            $wpdb->query( $sql );
            $sql = " UPDATE " . EVENTS_ATTENDEE_TABLE . " SET quantity = IF(IS NULL quantity,NULL,quantity-1 WHERE registration_id ='$registration_id'";
            $wpdb->query( $sql );
        }

        /*
         * Update the attendee information
         */
        else if ( $_REQUEST[ 'attendee_action' ] == 'update_attendee' )
        {
            $id = $_REQUEST[ 'id' ];
			$registration_id = $_REQUEST[ 'registration_id' ];
            $fname = $_POST[ 'fname' ];
            $lname = $_POST[ 'lname' ];
            $address = $_POST[ 'address' ];
            $city = $_POST[ 'city' ];
            $state = $_POST[ 'state' ];
            $zip = $_POST[ 'zip' ];
            $phone = $_POST[ 'phone' ];
            $email = $_POST[ 'email' ];
            $event_id = $_POST[ 'event_id' ];
            $payment = $_POST[ 'payment' ];

            $sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET fname='$fname', lname='$lname', address='$address', city='$city', state='$state', zip='$zip', phone='$phone', email='$email', payment='$payment' WHERE id ='$id'";
            $wpdb->query( $sql );
// Insert Extra From Post Here
            $reg_id = $id;

            //$questions = $wpdb->get_results( "SELECT * FROM " . EVENTS_QUESTION_TABLE . " WHERE event_id = '" . $event_id . "'" );

            $questions = $wpdb->get_results( "SELECT t2.*, t1.* FROM " . EVENTS_ANSWER_TABLE . " t1
                        JOIN " . EVENTS_QUESTION_TABLE . " t2
                            ON t1.question_id=t2.id
                            WHERE attendee_id = '" . $id . "' " );
            
            if ( $questions )
            {
                foreach ( $questions as $question ) {
                    switch ( $question->question_type )
                    {
                        case "TEXT" :
                        case "TEXTAREA" :
                        case "DROPDOWN" :
                            $post_val = $_POST [ $question->question_type . '_' . $question->question_id ];
                            $sql = "UPDATE " . EVENTS_ANSWER_TABLE . " SET answer='$post_val' WHERE attendee_id = '$id' AND question_id ='$question->question_id'";
                            $wpdb->query( $sql );
                            break;
                        case "SINGLE" :
                            $post_val = $_POST [ $question->question_type . '_' . $question->question_id ];
                            $sql = "UPDATE " . EVENTS_ANSWER_TABLE . " SET answer='$post_val' WHERE attendee_id = '$id' AND question_id ='$question->question_id'";
                            $wpdb->query( $sql );
                            break;
                        case "MULTIPLE" :
                            $value_string = '';
                            for ( $i = 0; $i < count( $_POST[ $question->question_type . '_' . $question->question_id ] ); $i++ ) {
//$value_string = $value_string +","+ ($_POST[$question->question_type.'_'.$question->id][$i]); 
                                $value_string .= $_POST[ $question->question_type . '_' . $question->question_id ][ $i ] . ",";
                            }
                           // echo "Value String - " . $value_string;
                            /* $values = explode ( ",", $question->response );
                              $value_string = '';
                              foreach ( $values as $key => $value ) {
                              $post_val = $_POST [$question->question_type . '_' . $question->id . '_' . $key];
                              if ($key > 0 && ! empty ( $post_val )) $value_string .= ',';
                              $value_string .= $post_val;
                              } */
                            $sql = "UPDATE " . EVENTS_ANSWER_TABLE . " SET answer='$value_string' WHERE attendee_id = '$id' AND question_id ='$question->question_id'";
                            $wpdb->query( $sql );
                            break;
                    }
                }
            }
        }


        $counter = 0;
        $additional_attendees = NULL;

        //$WHERE = (isset($_REQUEST[ 'registration_id' ]))?"registration_id ='" . $_REQUEST[ 'registration_id' ] . "'":"id = " . $_REQUEST[ 'id' ];



        $results = $wpdb->get_results( "SELECT  t1.*, t2.event_name, t2.question_groups FROM " . EVENTS_ATTENDEE_TABLE  . " t1
                 JOIN " . EVENTS_DETAIL_TABLE  . " t2
                 ON t1.event_id = t2.id
                 WHERE t1.id = " . $_REQUEST[ 'id' ] . "
                 ORDER BY t1.id" );

        foreach ( $results as $result ) {

           
            if ( $counter == 0 ){
           
           	$id = $result->id;
                $registration_id=$result->registration_id;
                $lname = $result->lname;
                $fname = $result->fname;
                $address = $result->address;
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
                  $question_groups = unserialize($result->question_groups);

            $counter = 1;
            } else {
                $additional_attendees[$result->id] = array('full_name' => $result->fname . ' ' . $result->lname, 'email' => $result->email, 'phone' => $result->phone);
            }
        }
?>       
<h3><?php _e('Registration For:', 'event_espresso' ); ?> <?php echo $event_name ?></h3>
   
  <?php  
  
  $attendees = "SELECT * FROM " .EVENTS_ATTENDEE_TABLE . " WHERE registration_id='".$registration_id."' ORDER BY id desc";
  //echo   $attendees;
   //$rsrcResult = mysql_query($strQuery);
   $data = $wpdb->get_results($attendees, ARRAY_A);
   //print_r($data);
   if ($wpdb->num_rows >1) {
	   ?>
       <div class="espresso_registration_edit">
         <form method="post" action="<?php echo get_option('siteurl')?>/?page_id=<?php echo $org_options['event_page_id']?>" class="espresso_form"> 
           
<?php _e('Select an attendee to edit:', 'event_espresso'); ?> 
<select name="id">

   <?php
	 /*** loop over the results ***/
        foreach($data as $row)
        {
            /*** create the options ***/
            echo '<option value="'.$row["id"].'"';
            if($row["id"]==$_REQUEST[ 'id' ])
            {
                echo ' selected';
            }
            echo '>'. stripslashes_deep($row["fname"]) . ' ' .stripslashes_deep($row["lname"]) . '</option>'."\n";
        }
	?>
    </select>
          <input type="hidden" name="form_action" value="edit_attendee" />
          
           <input type="hidden" name="regevent_action" value="register" />
           
           <input type="submit" name="submit" value="<?php _e( 'Edit', 'event_espresso' ); ?>" />
           </form>
       </div>
    <?php
   }else{
   }?>
	<form method="post" action="<?php echo $_SERVER[ 'REQUEST_URI' ] ?>" class="espresso_form">
	<?php
	if (count($question_groups) > 0){
		$questions_in = '';

		foreach ($question_groups as $g_id) $questions_in .= $g_id . ',';

			$questions_in = substr($questions_in,0,-1);
			$group_name = '';
			$counter = 0;

			$questions = $wpdb->get_results("SELECT q.*,at.*, qg.group_name
												FROM " . EVENTS_QUESTION_TABLE . " q
												LEFT JOIN " .  EVENTS_ANSWER_TABLE . " at
												on q.id = at.question_id
												JOIN " .  EVENTS_QST_GROUP_REL_TABLE . " qgr
												on q.id = qgr.question_id
												JOIN " . EVENTS_QST_GROUP_TABLE . " qg
												on qg.id = qgr.group_id
												WHERE qgr.group_id in ( " .   $questions_in . ") 
												AND (at.attendee_id IS NULL OR at.attendee_id = '" . $id . "') ORDER BY qg.id, q.id ASC");

			$num_rows = $wpdb->num_rows;

			if ($num_rows > 0 ){
				foreach($questions as $question){

					//if new group, close fieldset
					//echo ($group_name != '' &&  $group_name != $question->group_name) ?'</fieldset>':'';

					if ($group_name != $question->group_name){
						echo '<p><strong>'.$question->group_name.'</strong></p>';
						$group_name = $question->group_name;

					}

					echo '<p>';
					event_form_build_edit($question, is_null($question->registration_id)?${$question->system_name}:$question->answer);
					echo "</p>";


					$counter++;
					//echo $counter == $num_rows?'</fieldset>':'';

			}

		}//end questions display
	}

            
                ?>
                <input type="hidden" name="id" value="<?php echo $id ?>" />
                <input type="hidden" name="event_id" value="<?php echo $event_id ?>" />
      <input type="hidden" name="form_action" value="edit_attendee" />
      <input type="hidden" name="attendee_action" value="update_attendee" />
	<input type="hidden" name="regevent_action" value="register" />
                <p class="espresso_confirm_registration"><input type="submit" name="submit" value="<?php _e( 'Update Record', 'event_espresso' ); ?>" /></p>

</form>
               <form  method="post" action="<?php echo $_SERVER[ 'REQUEST_URI' ] ?>" class="espresso_form">
                    <p class="espresso_confirm_registration">
                    <input type="submit" name="confirm" id="confirm" value="<?php _e('Finalize Registration', 'event_espresso'); ?>" /></p>
                    <input name="confirm_registration" id="confirm_registration" type="hidden" value="true" />
                    <input type="hidden" name="registration_id" id="registration_id" value="<?php echo $registration_id ?>" />
                    <input type="hidden" name="regevent_action" id="regevent_action-<?php echo $event_id;?>" value="post_attendee">
                    <input type="hidden" name="event_id" id="event_id-<?php echo $event_id;?>" value="<?php echo $event_id;?>">
</form>

<?php
        }
