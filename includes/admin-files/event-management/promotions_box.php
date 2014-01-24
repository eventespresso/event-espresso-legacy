<div style="display: block;" id="event-discounts" class="postbox">
	<div class="handlediv" title="Click to toggle">
		<br>
	</div>
	<h3 class="hndle">
		<span> <?php _e('Event Promotions', 'event_espresso'); ?>
		</span>
	</h3>
	<div class="inside">

		<p>
			<strong><?php _e('Early Registration Discount', 'event_espresso'); ?>
			</strong>
		</p>

		<p>
			<label><?php _e('End Date:', 'event_espresso'); ?>
			</label><input type="text" class="datepicker" size="12"
						   id="early_disc_date" name="early_disc_date"
						   value="<?php echo isset($early_disc_date) ? $early_disc_date : ''; ?>" />
		</p>
		<p class="promo-amnts">
			<span class="promo-amnt"><label><?php _e('Amount:', 'event_espresso'); ?>
				</label><input type="text" size="3" id="early_disc" name="early_disc"
							   value="<?php echo isset($early_disc) ? $early_disc : ''; ?>" />
			</span><span class="promo-pc"> <label><?php _e('Percentage:', 'event_espresso') ?>
				</label> <?php echo select_input('early_disc_percentage', $values, !isset($early_disc_percentage) ? '' : $early_disc_percentage); ?>

		</p>
		<p>
			<?php _e('(Leave blank if not applicable)', 'event_espresso'); ?>
		</p>
		<p class="disc-codes">
			<label><?php _e('Allow Promo Codes?', 'event_espresso'); ?>
			</label>
<a class="thickbox"
			   href="#TB_inline?height=300&width=400&inlineId=coupon_code_info"><img
					src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png"
					width="16" height="16" />
			</a>
			<?php
			$coupon_code_select_values = array(
				array('id' => 'N', 'text' => __("No Promo Codes", "event_espresso")),
				array('id' => 'G', 'text' => __("Global Promo Codes Only", "event_espresso")),
				array('id' => 'Y', 'text' => __("Global and Specific Promo Codes", "event_espresso")),
				array('id' => 'A', 'text' => __("All Promo Codes (even Non-Globals)", "event_espresso"))
			);
			echo select_input('use_coupon_code', $coupon_code_select_values, !isset($use_coupon_code) || $use_coupon_code == '' ? apply_filters('FHEE_default_use_coupon_code_value',isset($org_options['default_promocode_usage']) ? $org_options['default_promocode_usage'] : 'N') : $use_coupon_code);
			?>
			
		</p>

		<div id='espresso_select_promocodes_area'>
			<strong>
<?PHP _e("Promocodes in Use:", "event_espresso") ?>
			</strong>
			<div class='promocodes-in-use'>
				<?php
				global $wpdb;
				if( ! isset($event_id)){
					$event_id = null;
				}
				if (!empty($event_id)) {
					$sql = $wpdb->prepare("SELECT d.id, d.coupon_code FROM " . EVENTS_DISCOUNT_CODES_TABLE . " AS d 
						INNER JOIN " . EVENTS_DISCOUNT_REL_TABLE . " AS r ON d.id=r.discount_id WHERE apply_to_all=0 AND event_id=%d ",$event_id);



					if (function_exists('espresso_member_data') && !empty($event_id)) {
						$wpdb->get_results("SELECT wp_user FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "'");
						$wp_user = $wpdb->last_result[0]->wp_user != '' ? $wpdb->last_result[0]->wp_user : espresso_member_data('id');
						$sql .= " AND ";
						if ($wp_user == 0 || $wp_user == 1) {
							$sql .= " (wp_user = '0' OR wp_user = '1') ";
						} else {
							$sql .= " wp_user = '" . $wp_user . "' ";
						}
					}
					$promocodes_in_use = $wpdb->get_results($sql);
				}else{
					$promocodes_in_use = array();
				}
				
				?>
				<?php
				if($promocodes_in_use){
					foreach ($promocodes_in_use as $promocode) {
						echo '<p class="event-disc-code" id="event-discount-' . $promocode->id . '"><label for="in-event-discount-' . $promocode->id . '" class="selectit already-added-disc-code"><input value="' . $promocode->id . '" type="checkbox" name="event_discount[]" id="in-event-discount-' . $promocode->id . '" checked="checked" /> ' . $promocode->coupon_code . "</label></p>";
					}
				}else{
					echo "<p id='no-promocodes-in-use'>None</p>";
				}
				?>
			</div>
			<strong>
<?php _e("Add Promocodes: ", "event_espresso") ?>
			</strong>
			<div class='promocode-ajax-list'>
				<input type='hidden' name='espresso_ignore_promocode_page_start' value=0>
			</div>
			<script>
				jQuery(document).ready(function(){
					espresso_disc_code_update_showing_of_promocode_picker();
					espresso_disc_codes_paginate();
					jQuery('.already-added-disc-code').click(function(){
						jQuery(this).remove();
						espresso_disc_codes_paginate();
						return false;
					});
					jQuery('[name="use_coupon_code"]').change(function(){
						espresso_disc_code_update_showing_of_promocode_picker();
					})
				});
				function espresso_disc_code_update_showing_of_promocode_picker(){
					if( jQuery('[name="use_coupon_code"]').val() == 'Y' ){
						jQuery('#espresso_select_promocodes_area').show('slow');
					}else{
						jQuery('#espresso_select_promocodes_area').hide('slow');
					}
				}
				function espresso_disc_codes_paginate(start,count){
					
					
					//check a start and count are provided, otherwise use what's in the inputs for that
					if(start == undefined ){
						start = jQuery("[name='espresso_ignore_promocode_page_start']").val();
					}
					if(count == undefined){
						count = jQuery("[name='espresso_ignore_promocodes_per_page']").val();
					}
					
					//get promocodes to exclude (ones already in the included list)
					var excludes = new Array();
					//also determine if we should show tell the admin no promocdes are in use
					var no_promocodes_selected = true;
					jQuery('.already-added-disc-code').each(function(){
						excludes.push(jQuery('input',this).val());
						no_promocodes_selected = false;
					});
					if(no_promocodes_selected){
						jQuery('#no-promocodes-in-use').show('slow');
					}else{
						jQuery('#no-promocodes-in-use').hide('slow');
					}
					var data = {
						action: 'event_espresso_get_discount_codes',
						event_id: <?php echo $event_id ?  $event_id : 0 ?>,
						start:start,
						count:count,
						excludes:excludes
					};
					jQuery('.promocode-ajax-list').html("<div style='text-align:center;'><img src='<?php echo EVENT_ESPRESSO_PLUGINFULLURL . "images/ajax-loader.gif" ?>'> </div>");
					// since 2.8 ajaxurl is always defined in the admin header and points to wp's admin-ajax.php
					jQuery.post(ajaxurl, data, function(response) {
						jQuery('.promocode-ajax-list').html(response);
						jQuery('.add-this-disc-code input').click(function(){
							
							parent_label = jQuery(this).closest('label');
							//do an 'animate' so that we can seperate the click event
							//from when we add a different listener on the click event
							jQuery(this).animate({
								display:'none'
							},1, 'swing', function(){
								jQuery('.promocodes-in-use').append(jQuery(parent_label).parent());
								jQuery(parent_label).removeClass('add-this-disc-code');
								jQuery(parent_label).addClass('already-added-disc-code');
								jQuery(this).click(function(){
									jQuery(parent_label).remove();
									espresso_disc_codes_paginate();
								});
								espresso_disc_codes_paginate(Math.max(0,start-1),count);
							});
							
							//jQuery(this).unbind();
							
							//return false;
						});
				
					});
					return false;
				}
			</script>
			<input type='hidden' name='espresso_ignore_promocodes_per_page' value=10 />
			<p><a href="admin.php?page=discounts" target="_blank"><?php _e('Manage Promotional Codes ', 'event_espresso') ?></a></p>	
				
		</div>
	</div>
</div>
<!-- /event-discounts -->
