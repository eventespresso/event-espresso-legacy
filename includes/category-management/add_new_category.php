<?php

function add_new_event_category() {
   
    ?>
<!--Add event display-->

<div id="add-edit-categories" class="metabox-holder">
	<div class="postbox">
		<h3>
			<?php _e('Add a Category', 'event_espresso'); ?>
		</h3>
		<div class="inside">
			<form id="add-new-cat" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
				<input type="hidden" name="action" value="add">
				<p class="add-cat-name inputunder">
					<label for="category_name">
						<?php _e('Category Name', 'event_espresso'); ?>
						<em title="<?php _e('This field is required', 'event_espresso') ?>"> *</em></label>
					<input id="category_name" type="text" name="category_name" size="25" />
				</p>
				<p class="add-cat-id inputunder">
					<label for="cat_id">
						<?php _e('Unique ID For Category', 'event_espresso'); ?>
					</label>
					<input id="cat_id"  type="text" name="category_identifier" />
					<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=unique_id_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a> </p>
				<p class="section-quest">
					<?php _e('Display category description on event listings Page?', 'event_espresso'); ?>
				</p>
				<?php 
										$values=array(					
											array('id'=>'Y','text'=> __('Yes','event_espresso')),
											array('id'=>'N','text'=> __('No','event_espresso'))
											);				
										echo select_input('display_desc', $values, 'N');
										?>
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
							<td><input id="background-color"type="text" name="event_background" value="#486D96" />
								<div id="colorpicker-1"></div></td>
						</tr>
						<tr class="color-picker-selections">
							<th class="color-picker-style"> 
								<label for="text-color">
									<?php _e('Event Text Color', 'event_espresso') ?>
								</label>
							</th>
							<td><input id="text-color" type="text" name="event_text_color" value="#ebe6e8" />
								<div id="colorpicker-2"></div></td>
						</tr>
					</tbody>
				</table>
				<?php }?>
				<div id="categorydescriptiondivrich" class="postarea">
					<p id="add-category-desc" class="section-heading">
						<?php _e('Category Description', 'event_espresso'); ?>
					</p>
					<div class="postbox">
						<?php 
												
												if (function_exists('wp_editor')){
													$args = array("textarea_rows" => 5, "textarea_name" => "category_desc", "editor_class" => "my_editor_custom");
													wp_editor("My category content", "category_desc", $args);
												}else{
													the_editor('', $id = 'category_desc', $prev_id = 'title', $media_buttons = true, $tab_index = 3);
												}
												//the_editor('', $id = 'category_desc', $prev_id = 'title', $media_buttons = true, $tab_index = 3);?>
						<table id="cat-descr-add-form" cellspacing="0">
							<tbody>
								<tr>
									<td class="aer-word-count"></td>
									<td class="autosave-info"><span>
										<p></p>
										</span></td>
								</tr>
							</tbody>
						</table>
					</div>
					<!-- /.postbox -->
					<p>
						<input class="button-secondary" type="submit" name="Submit" value="<?php _e('Submit'); ?>" id="add_new_category" />
					</p>
				</div>
				<!-- /.postarea -->
			</form>
		</div>
		<!-- /.inside --> 
	</div>
	<!-- /.postbox --> 
</div>
<!-- metabox-holder -->
<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
<?php
//espresso_tiny_mce();
}



