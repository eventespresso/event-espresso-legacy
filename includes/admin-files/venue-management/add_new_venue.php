<?php
function add_new_event_venue(){
	wp_tiny_mce( false , // true makes the editor "teeny"
		array(
			"editor_selector" => "theEditor"//This is the class name of your text field
		)
	);

        if (  function_exists( 'wp_tiny_mce_preload_dialogs' )){
             add_action( 'admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30 );
        }
	?>
<!--Add event display-->

<div class="metabox-holder">
  <div class="postbox">
    <h3>
      <?php _e('Add a Venue','event_espresso'); ?>
    </h3>
    <div class="inside">
      <form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
        <input type="hidden" name="action" value="add">
        <table width="100%" border="0">
          <tr>
            <td align="left" valign="top"><ul>
                <li>
                  <label>
                    <?php _e('Name','event_espresso'); ?>
                  </label>
                  <input type="text" name="name" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Address','event_espresso'); ?>
                  </label>
                  <input type="text" name="address" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Address 2','event_espresso'); ?>
                  </label>
                  <input type="text" name="address2" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('City','event_espresso'); ?>
                  </label>
                  <input type="text" name="city" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('State','event_espresso'); ?>
                  </label>
                  <input type="text" name="state" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Zip','event_espresso'); ?>
                  </label>
                  <input type="text" name="zip" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Country','event_espresso'); ?>
                  </label>
                  <input type="text" name="country" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Locale','event_espresso'); ?> 
                  </label>
                  <input type="text" name="locale" size="25"> <a class="ev_reg-fancylink" href="#venue_locale"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a>
                </li>
              </ul></td>
            <td align="left" valign="top"><ul>
            <li>
                  <label>
                    <?php _e('Contact','event_espresso'); ?>
                  </label>
                  <input type="text" name="contact" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Phone','event_espresso'); ?>
                  </label>
                  <input type="text" name="phone" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Twitter','event_espresso'); ?>
                  </label>
                  <input type="text" name="twitter" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Website','event_espresso'); ?>
                  </label>
                  <input type="text" name="website" size="25">
                </li>
                <li>
                  <label>
                    <?php _e('Image/Logo URL','event_espresso'); ?>
                  </label>
                  <input type="text" name="image" size="25">
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
            <textarea class="theEditor" id="description" name="description"></textarea>
            <br />
          </li>
          <li>
            <p>
              <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Venue'); ?>" id="add_new_venue" />
            </p>
          </li>
        </ul>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" charset="utf-8">

function toggleEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
	}
	jQuery(document).ready(function($) {
		
                

		var id = 'description';
		$('a.toggleVisual').click(
			function() {
				tinyMCE.execCommand('mceAddControl', false, id);
			}
		);

		$('a.toggleHTML').click(
			function() {
				tinyMCE.execCommand('mceRemoveControl', false, id);
			}
		);
});
</script>
<?php } 
