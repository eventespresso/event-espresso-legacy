<?php
function add_discount_to_db(){
	global $wpdb;
	if (isset($_POST['Submit'])){
	if ( $_REQUEST['action'] == 'add' ){
		$coupon_code= $_REQUEST['coupon_code'];
		$coupon_code_price = $_REQUEST['coupon_code_price'];
		$coupon_code_description= $_REQUEST['coupon_code_description']; 
		$use_percentage=$_REQUEST['use_percentage'];
	
		$sql="INSERT INTO " . EVENTS_DISCOUNT_CODES_TABLE . " (coupon_code, coupon_code_price, coupon_code_description, use_percentage) VALUES('$coupon_code', '$coupon_code_price', '$coupon_code_description', '$use_percentage')";

	if ($wpdb->query($sql)){ ?>
<div id="message" class="updated fade">
  <p><strong>
    <?php _e('The discount code '.$_REQUEST['coupon_code'].' has been added.','event_espresso'); ?>
    </strong></p>
</div>
<?php }else { ?>
<div id="message" class="error">
  <p><strong>
    <?php _e('The discount code '.$_REQUEST['coupon_code'].' was not saved.','event_espresso'); ?>
    <?php print mysql_error() ?>.</strong></p>
</div>
<?php
}
	}
	}
}