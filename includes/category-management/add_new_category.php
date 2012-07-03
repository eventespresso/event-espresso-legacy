<?php
function add_new_event_category(){
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
<h3><?php _e('Add a Category','event_espresso'); ?></h3>
<div class="inside">
  <form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
  <input type="hidden" name="action" value="add">
   <ul>
    <li><label><?php _e('Category Name','event_espresso'); ?></label> <input type="text" name="category_name" size="25"></li>
   <li><label><?php _e('Unique ID For Category', 'event_espresso'); ?></label> <input type="text" name="category_identifier"> <a class="ev_reg-fancylink" href="#unique_id_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a></li>
   <li><?php _e('Do you want to display the category description on the events page?','event_espresso'); ?>
   <?php if ($display_desc ==""){
			echo "<input type='radio' name='display_desc' checked value='Y'>".__('Yes', 'event_espresso');
			echo "<input type='radio' name='display_desc' value='N'>".__('No', 'event_espresso');}
		if ($display_desc =="Y"){
			echo "<input type='radio' name='display_desc' checked value='Y'>".__('Yes', 'event_espresso');
			echo "<input type='radio' name='display_desc' value='N'>".__('No', 'event_espresso');}
		if ($display_desc =="N"){
			echo "<input type='radio' name='display_desc' value='Y'>".__('Yes', 'event_espresso');
			echo "<input type='radio' name='display_desc' checked value='N'>".__('No', 'event_espresso');
		}
	?>
	</li>
   <li><?php _e('Category Description','event_espresso'); ?><br />
   <textarea class="theEditor" id="category_desc_new" name="category_desc"></textarea>
      <br />
   </li>
   <li>
    <p>
            <input class="button-secondary" type="submit" name="Submit" value="<?php _e('Submit'); ?>" id="add_new_category" />
            </p>
    </li>
   </ul>
     </form>
	</div>
    </div>
</div>
<?php } 