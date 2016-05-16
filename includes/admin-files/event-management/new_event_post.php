<?php
if (function_exists('espresso_member_data')) {
	global $espresso_manager;
	$is_admin = (espresso_member_data('role') == "administrator" || espresso_member_data('role') =='espresso_event_admin')? true:false;
	if ($espresso_manager['event_manager_create_post'] == 'N' && $is_admin == false){
		return;	
	}
}
?>

<div style="display: block;" id="event-post" class="postbox">
    <div class="handlediv" title="Click to toggle"><br />
    </div>
    <h3 class="hndle"><span>
            <?php _e('Create a Post', 'event_espresso'); ?>
        </span></h3>
    <div class="inside">
        <p>
          <label>  <?php _e('Create a post for this event?', 'event_espresso'); ?></label>

            <?php 
                $create_post = apply_filters('filter_hook_espresso_default_create_post_option', 'N');
                echo select_input('create_post', $values, $create_post); 
            ?> 
					</p>
        <input type="hidden" name="post_id" value="<?php echo isset($post_id) ? $post_id : ''; ?>" />
        <?php
        $current_user = wp_get_current_user();

        if (isset($post_id)) {
            $post_data = get_post($post_id);
            $tags = get_the_tags($post_id);
            if ($tags) {
                foreach ($tags as $k => $v) {
                    $tag[$k] = $v->name;
                }
                $tags = join(', ', $tag);
            }
        } else {
            $post_data = new stdClass();
            $post_data->ID = 0;
            $tags = '';
        }
        $box = array();

        $custom_post_array = array(array('id' => 'espresso_event', 'text' => __('Espresso Event', 'event_espresso')));
        $post_page_array = array(array('id' => 'post', 'text' => __('Post', 'event_espresso')));
		$org_options['use_custom_post_types'] = isset( $org_options['use_custom_post_types'] ) ? $org_options['use_custom_post_types'] : 'N'; 
        $post_page_array = $org_options['use_custom_post_types'] == 'Y' ? array_merge($custom_post_array, $post_page_array) : $post_page_array;

        $post_types = $post_page_array;
        require_once( 'includes/meta-boxes.php');
        ?>
        <p class="create-post"><label><?php _e('Author:', 'event_espresso'); ?></label> <span><?php wp_dropdown_users(array('who' => 'authors', 'selected' => $current_user->ID, 'show' => 'display_name')); ?></span></p>
        <?php if ( $org_options['use_custom_post_types'] == 'Y' ) { ?>
        <p class="create-post"><label><?php _e('Post Type', 'event_espresso'); ?>:</label><span> <?php echo select_input('espresso_post_type', $post_types, $post_type) ?></span></p>
        <p><?php _e('Post templates are stored in the "templates" directory of the plugin.', 'event_espresso'); ?></p>
        <?php } ?>
        <p class="create-post post-tags"><label><?php _e('Tags', 'event_espresso'); ?>:</label> <span><input id="post_tags" name="post_tags" size="20" type="text" value="<?php echo $tags; ?>" /></span></p>
       	<p class="section-heading"><?php _e('Post Categories:', 'event_espresso'); ?> </p>
        <?php post_categories_meta_box($post_data, $box); ?>
        <input type="hidden" name="espresso_fb" value="true" />
        <!-- if post templates installed, post template -->

    </div>
</div>
<!-- /event-post -->