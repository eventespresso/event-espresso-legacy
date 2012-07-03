<?php
function edit_event_staff(){
	global $wpdb;
	wp_tiny_mce( false , // true makes the editor "teeny"
		array(
			"editor_selector" => "theEditor"//This is the class name of your text field
		)
	);

        if (  function_exists( 'wp_tiny_mce_preload_dialogs' )){
             add_action( 'admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30 );
        }

	$id=$_REQUEST['id'];
	$results = $wpdb->get_results("SELECT * FROM ". EVENTS_PERSONNEL_TABLE ." WHERE id =".$id);
	foreach ($results as $result){
		$staff_id= $result->id;
		$name=stripslashes_deep($result->name);
		$email=stripslashes_deep($result->email);
		$meta = unserialize($result->meta);
	}
	?>
<!--Add event display-->

<div class="metabox-holder">
  <div class="postbox">
    <h3>
      <?php _e('Edit Staff Member:','event_espresso'); ?>
      <?php echo stripslashes($name) ?></h3>
    <div class="inside">
      <form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
        <input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>">
        <input type="hidden" name="action" value="update">
       <ul>
                <li>
                  <label>
                    <?php _e('Name','event_espresso'); ?>
                  </label>
                  <input type="text" name="name" size="25" value="<?php echo $name;?>">
                </li>
                
             
                <li>
                  <label>
                    <?php _e('Email Address','event_espresso'); ?>
                  </label>
                  <input type="text" name="email" size="25" value="<?php echo $email;?>">
                </li>
                <li>
                  <label>
                    <?php _e('Phone','event_espresso'); ?>
                  </label>
                  <input type="text" name="phone" size="25" value="<?php echo stripslashes_deep($meta['phone']);?>">
                </li>
                <li>
                  <label>
                    <?php _e('Twitter','event_espresso'); ?>
                  </label>
                  <input type="text" name="twitter" size="25" value="<?php echo stripslashes_deep($meta['twitter']);?>">
                </li>
                <li>
                  <label>
                    <?php _e('Website','event_espresso'); ?>
                  </label>
                  <input type="text" name="website" size="25" value="<?php echo stripslashes_deep($meta['website']);?>">
                </li>
                <li>
                  <label>
                    <?php _e('Image/Logo URL','event_espresso'); ?>
                  </label>
                  <input type="text" name="image" size="25" value="<?php echo stripslashes_deep($meta['image']);?>">
                </li>
              </ul>
        <ul>
          <li>
            <label>
              <?php _e('Description','event_espresso'); ?>
            </label>
            <br />
            <textarea class="theEditor" id="description" name="description"><?php echo wpautop(html_entity_decode(stripslashes_deep($meta['description']))); ?></textarea>
          </li>
          <li>
            <p>
              <input class="button-secondary" type="submit" name="Submit" value="<?php _e('Update'); ?>" id="update_staff" />
            </p>
          </li>
        </ul>
      </form>
    </div>
  </div>
</div>
<?php }
