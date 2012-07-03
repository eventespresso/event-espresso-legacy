<?php
function edit_event_venue(){
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
	$results = $wpdb->get_results("SELECT * FROM ". EVENTS_VENUE_TABLE ." WHERE id =".$id);
	foreach ($results as $result){
		$venue_id= $result->id;
		$name=stripslashes_deep($result->name);
		$address=stripslashes_deep($result->address);
		$address2=stripslashes_deep($result->address2);
		$city=stripslashes_deep($result->city);
		$state=stripslashes_deep($result->state);
		$zip=stripslashes_deep($result->zip);
		$country=stripslashes_deep($result->country);
		$meta = unserialize($result->meta);
	}
	?>
<!--Add event display-->

<div class="metabox-holder">
  <div class="postbox">
    <h3>
      <?php _e('Edit Venue:','event_espresso'); ?>
      <?php echo stripslashes($name) ?></h3>
    <div class="inside">
      <form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
        <input type="hidden" name="venue_id" value="<?php echo $venue_id; ?>">
        <input type="hidden" name="action" value="update">
        <table width="100%" border="0">
          <tr>
            <td align="left" valign="top"><ul>
                <li>
                  <label>
                    <?php _e('Name','event_espresso'); ?>
                  </label>
                  <input type="text" name="name" size="25" value="<?php echo $name;?>">
                </li>
                <li>
                  <label>
                    <?php _e('Address','event_espresso'); ?>
                  </label>
                  <input type="text" name="address" size="25" value="<?php echo $address;?>">
                </li>
                <li>
                  <label>
                    <?php _e('Address 2','event_espresso'); ?>
                  </label>
                  <input type="text" name="address2" size="25" value="<?php echo $address2;?>">
                </li>
                <li>
                  <label>
                    <?php _e('City','event_espresso'); ?>
                  </label>
                  <input type="text" name="city" size="25" value="<?php echo $city;?>">
                </li>
                <li>
                  <label>
                    <?php _e('State','event_espresso'); ?>
                  </label>
                  <input type="text" name="state" size="25" value="<?php echo $state;?>">
                </li>
                <li>
                  <label>
                    <?php _e('Zip','event_espresso'); ?>
                  </label>
                  <input type="text" name="zip" size="25" value="<?php echo $zip;?>">
                </li>
                <li>
                  <label>
                    <?php _e('Country','event_espresso'); ?>
                  </label>
                  <input type="text" name="country" size="25" value="<?php echo $country;?>">
                </li>
              </ul></td>
            <td align="left" valign="top"><ul>
                <li>
                  <label>
                    <?php _e('Contact','event_espresso'); ?>
                  </label>
                  <input type="text" name="contact" size="25" value="<?php echo stripslashes_deep($meta['contact']);?>">
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
              </ul></td>
          </tr>
        </table>
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
              <input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Venue'); ?>" id="update_venue" />
            </p>
          </li>
        </ul>
      </form>
    </div>
  </div>
</div>
<?php }
