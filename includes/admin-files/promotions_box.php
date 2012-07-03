<div style="display: block;" id="event-discounts" class="postbox">
      <div class="handlediv" title="Click to toggle"><br>
      </div>
      <h3 class="hndle"><span>
        <?php _e('Event Promotions','event_espresso'); ?>
        </span></h3>
      <div class="inside">
       
                <p><strong><?php _e('Early Registration Discount','event_espresso'); ?></strong></p>
                
                <p><?php _e('End Date:','event_espresso'); ?><input type="text" class="datepicker" size="12" id="early_disc_date" name="early_disc_date" value="<?php echo $early_disc_date; ?>"/></p>
                <p><?php _e('Amount:','event_espresso'); ?><input type="text" size="3" id="early_disc" name="early_disc" value="<?php echo $early_disc; ?>"/> <?php _e('Percentage:','event_espresso'); 		
		echo select_input('early_disc_percentage', $values, $early_disc_percentage);?></p>
				<p><?php _e('(Leave blank if not applicable)', 'event_espresso'); ?></p>
        <p>
          <strong><?php _e('Allow discount codes?','event_espresso');?></strong>
					
			<?php echo select_input('use_coupon_code', $values, $use_coupon_code ==''?'N':$use_coupon_code); ?>
          <a class="ev_reg-fancylink" href="#coupon_code_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a></p>
        <?php
            $event_discounts = $wpdb->get_results("SELECT * FROM ". EVENTS_DISCOUNT_CODES_TABLE);
			foreach ($event_discounts as $event_discount){
				$discount_id = $event_discount->id;
				$coupon_code = $event_discount->coupon_code;
				$discount_type_price = $event_discount->use_percentage == 'Y' ? $event_discount->coupon_code_price.'%' : $org_options['currency_symbol'].$event_discount->coupon_code_price;
				
					$in_event_discounts = $wpdb->get_results("SELECT * FROM " . EVENTS_DISCOUNT_REL_TABLE . " WHERE event_id='".$event_id."' AND discount_id='".$discount_id."'");
					foreach ($in_event_discounts as $in_discount){
						$in_event_discount = $in_discount->discount_id;
					}
					echo '<p id="event-discount-' . $discount_id . '"><label for="in-event-discount-' . $discount_id . '" class="selectit"><input value="' . $discount_id . '" type="checkbox" name="event_discount[]" id="in-event-discount-' . $discount_id . '"' . ($in_event_discount == $discount_id ? ' checked="checked"' : "" ) . '/> ' . $coupon_code. "</label></p>";
			}	
            ?>
      </div>
    </div>
    <!-- /event-discounts -->