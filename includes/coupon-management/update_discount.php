<?php
function update_event_discount() {
	
		$discount_id= $_REQUEST['discount_id'];
		$coupon_code= $_REQUEST['coupon_code'];
		$coupon_code_price = $_REQUEST['coupon_code_price'];
		$coupon_code_description= $_REQUEST['coupon_code_description']; 
		$use_percentage=$_REQUEST['use_percentage'];
		global $wpdb;
		//Post the new event into the database
		$sql="UPDATE ".EVENTS_DISCOUNT_CODES_TABLE." SET coupon_code='$coupon_code', coupon_code_price='$coupon_code_price', coupon_code_description='$coupon_code_description',   use_percentage='$use_percentage' WHERE id = $discount_id";
		
		if ($wpdb->query($sql)){ ?>
            <div id="message" class="updated fade">
              <p><strong>
                <?php _e('The discount '.$_REQUEST['coupon_code'].' has been updated.','event_espresso'); ?>
                </strong></p>
            </div>
            <?php }else { ?>
            <div id="message" class="error">
              <p><strong>
                <?php _e('The discount code '.$_REQUEST['coupon_code'].' was not updated.','event_espresso'); ?>
                <?php print mysql_error() ?>.</strong></p>
            </div>
<?php
		}
}