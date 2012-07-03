<?php
function edit_event_category(){
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
	$results = $wpdb->get_results("SELECT * FROM ". get_option('events_category_detail_tbl') ." WHERE id =".$id);
	foreach ($results as $result){
		$category_id= $result->id;
		$category_name=stripslashes($result->category_name);
		$category_identifier=stripslashes($result->category_identifier);
		$category_desc=stripslashes($result->category_desc);
		$display_category_desc=$result->display_desc;
	}
	?>
<!--Add event display-->
<div class="metabox-holder">
  <div class="postbox">
<h3><?php _e('Edit Category:','event_espresso'); ?> <?php echo stripslashes($category_name) ?></h3>
<div class="inside">
  <form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
  <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
  <input type="hidden" name="action" value="update">
   <ul>
    <li><label><strong><?php _e('Category Name:','event_espresso'); ?></strong></label> <input type="text" name="category_name" size="25" value="<?php echo stripslashes($category_name);?>"></li>
   <li><label><strong><?php _e('Unique Category Identifier:','event_espresso'); ?></strong></label> <input type="text" name="category_identifier" value="<?php echo $category_identifier;?>"> <a class="ev_reg-fancylink" href="#unique_id_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a></li>
   <li><?php _e('Do you want to display the category description on the events page?','event_espresso'); ?>
<?php 
	$values=array(					
        array('id'=>'Y','text'=> __('Yes','event_espresso')),
        array('id'=>'N','text'=> __('No','event_espresso')));				
	echo select_input('display_desc', $values, $display_category_desc);
   
	?>
	</li>
   <li><strong><?php _e('Category Description:','event_espresso'); ?></strong><br />
   <textarea class="theEditor" id="category_desc_new" name="category_desc"><?php echo wpautop(html_entity_decode(stripslashes_deep($category_desc))); ?></textarea>
   </li>
   <li>
    <p>
            <input class="button-secondary" type="submit" name="Submit" value="<?php _e('Update'); ?>" id="update_category" />
            </p>
    </li>
   </ul>
     </form>
	</div>
    </div>
</div>
<?php }