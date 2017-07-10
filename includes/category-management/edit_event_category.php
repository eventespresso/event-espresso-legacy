<?php
function edit_event_category(){
	global $wpdb;
	
	$id=$_REQUEST['id'];
	$results = $wpdb->get_results( $wpdb->prepare(
		"
			SELECT * 
			FROM ". EVENTS_CATEGORY_TABLE ." 
			WHERE id = %d 
		",
		$id
	) );
	foreach ($results as $result){
		$category_id = $result->id;
		$category_name = stripslashes($result->category_name);
		$category_identifier = stripslashes($result->category_identifier);
		$category_desc  =stripslashes($result->category_desc);
		$display_category_desc = $result->display_desc;
		
		$category_meta = unserialize($result->category_meta);
		//echo "<pre>".print_r($category_meta,true)."</pre>";
	}
	?>
<!--Add event display-->

<div class="metabox-holder" id="add-edit-categories">
	<div class="postbox">
		<h3>
			<?php _e('Edit Category:','event_espresso'); ?>
			<?php echo stripslashes($category_name) ?> </h3>
		<div class="inside">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
				<input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
				<input type="hidden" name="action" value="update">
				<p class="add-cat-name inputunder">
					<label for="category-name">
						<?php _e('Category Name','event_espresso'); ?>
					</label>
					<input type="text" id="category-name" name="category_name" size="25" value="<?php echo stripslashes($category_name);?>">
				</p>
				<p class="add-cat-id inputunder">
					<label for="category-id">
						<?php _e('Unique Category Identifier','event_espresso'); ?>
					</label>
					<input type="text" id="category-id" name="category_identifier" value="<?php echo $category_identifier;?>">
					<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=unique_id_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a></p>
				<p class="inputunder">
					<label for="display_desc">
						<?php _e('Display category description on event listings page?','event_espresso'); ?>
					</label>
					<?php 
			$values=array(					
        		array('id'=>'Y','text'=> __('Yes','event_espresso')),
        		array('id'=>'N','text'=> __('No','event_espresso'))
			);				
			echo select_input('display_desc', $values, $display_category_desc);
   		?>
				</p>
				<?php global $espresso_premium; if ($espresso_premium == true){?>
				<table class="form-table">
					<tbody>
						<tr>
							<th> <label for="espresso_use_pickers">
									<?php _e('Use Color Pickers', 'event_espresso'); ?>
								</label>
							</th>
							<td><?php echo select_input('use_pickers', $values, $category_meta['use_pickers'], 'id="espresso_use_pickers"'); ?></td>
						</tr>
						<tr class="color-picker-selections">
							<th class="color-picker-style">
								<label for="background-color">
									<?php _e('Event Background Color', 'event_espresso') ?>
								</label>
							</th>
							<td><input id="background-color"type="text" name="event_background" <?php echo (isset($category_meta['event_background']) && !empty($category_meta['event_background'])) ? 'value="' . $category_meta['event_background'] . '"' : 'value="#486D96"' ?> />
								<div id="colorpicker-1"></div></td>
						</tr>
						<tr class="color-picker-selections">
							<th class="color-picker-style"> 
								<label for="text-color">
									<?php _e('Event Text Color', 'event_espresso') ?>
								</label>
							</th>
							<td><input id="text-color" type="text" name="event_text_color" <?php echo (isset($category_meta['event_text_color']) && !empty($category_meta['event_text_color'])) ? 'value="' . $category_meta['event_text_color'] . '"' : 'value="#ebe6e8"' ?> />
								<div id="colorpicker-2"></div></td>
						</tr>
					</tbody>
				</table>
				<?php }?>
				<div id="categorydescriptiondivrich" class="postarea">
					<p class="section-heading">
						<?php _e('Category Description','event_espresso'); ?>
					</p>
					<div class="postbox">
						<?php 
							if (function_exists('wp_editor')){
								$args = array("textarea_rows" => 5, "textarea_name" => "category_desc", "editor_class" => "my_editor_custom");
								wp_editor(espresso_admin_format_content($category_desc), "category_desc", $args);
							}else{
								the_editor(espresso_admin_format_content($category_desc), $id = 'category_desc', $prev_id = 'title', $media_buttons = true, $tab_index = 3);
							}
							//the_editor(espresso_admin_format_content($category_desc), $id = 'category_desc', $prev_id = 'title', $media_buttons = true, $tab_index = 3);?>
						<table id="cat-descr-add-form" cellspacing="0">
							<tbody>
								<tr>
									<td class="aer-word-count"></td>
									<td class="autosave-info"><span> <br />
										</span></td>
								</tr>
							</tbody>
						</table>
					</div>
					<!-- /.postbox -->
					<p>
						<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update'); ?>" id="update_category" />
					</p>
				</div>
				<!-- /.postarea -->
				
			</form>
		</div>
		<!-- /.inside --> 
	</div>
	<!-- /.postbox --> 
</div>
<!-- /.metabox-holder -->

<?php 
}