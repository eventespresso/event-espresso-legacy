<?php
function edit_event_email(){
	global $wpdb;
	
	$id=$_REQUEST['id'];
	$results = $wpdb->get_results("SELECT * FROM ". EVENTS_EMAIL_TABLE ." WHERE id =".$id);
	foreach ($results as $result){
		$email_id= $result->id;
		$email_name=stripslashes_deep($result->email_name);
		$email_subject=stripslashes_deep($result->email_subject);
		$email_text=stripslashes_deep($result->email_text);
	}
	?>
<!--Add event display-->
<div class="metabox-holder">
  <div class="postbox">
<h3><?php _e('Edit Email','event_espresso'); ?> </h3>
<div class="inside">
  <form id="add-edit-new-event-email" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
  <h4><?php echo stripslashes($email_name) ?></h4>
  <table class="form-table">
		<tbody>
			<tr>
				<th><label for="email_name"><?php _e('Email Name','event_espresso'); ?></label></th>
				<td><input class="regular-text" type="text" name="email_name" size="25" value="<?php echo stripslashes($email_name);?>" /></td>
			</tr>
			<tr>
				<th><label><?php _e('Email Subject Line','event_espresso'); ?></label></th>
				<td><input class="regular-text" type="text" name="email_subject" size="25" value="<?php echo stripslashes($email_subject);?>"></td>
			</tr>
			
				<tr><td colspan="2"><div id="descriptiondivrich" class="postarea">   
				<div class="postbox">   
					<?php the_editor(espresso_admin_format_content($email_text), $id = 'email_text', $prev_id = 'title', $media_buttons = true, $tab_index = 3);?>
				<table id="manage-event-email-form" cellspacing="0">
					<tbody>
						<tr>
							<td class="aer-word-count"></td>
							<td class="autosave-info">
								<span>
									<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_info"><?php _e('View Custom Email Tags', 'event_espresso'); ?></a> | <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_example"> <?php _e('Email Example','event_espresso'); ?></a>
								</span>
							</td>
						</tr>
					</tbody>
				</table>				
				
				</div>
			
			</div></td>
			</tr>
		</tbody>
	</table>
  <input type="hidden" name="email_id" value="<?php echo $email_id; ?>">
  <input type="hidden" name="action" value="update">
  
	<p><input class="button-primary" type="submit" name="Submit" value="<?php _e('Update'); ?>" id="update_email" /></p>
	<?php wp_nonce_field( 'espresso_form_check', 'update_email' ) ?>
   </li>
  </ul>
 </form>
</div>
</div>
</div>
<?php 
//espresso_tiny_mce();
}