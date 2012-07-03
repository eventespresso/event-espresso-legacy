<?
/* THIS PAGE IS DEPRECATED - DO NOT USE */

/* 

Attention!!! Attention!!!

This file is deprecated. Do not use.

Attention!!! Attention!!!

Attention!!! Attention!!!


*/

//Payment Page/PayPal Buttons - Used to display the payment options and the payment link in the email. Used with the {ESPRESSO_PAYMENTS} tag
//session_start();
//echo $HTTP_SESSION_VARS['attendee_session'];
//This is the initial PayPal button
function events_payment_page($event_id,$attendee_id){
	// Setup class
	$p = new paypal_class;// initiate an instance of the class
	$event_cost = $_REQUEST['event_cost'];
			global $wpdb;
			
			//$events_detail_tbl = get_option('events_detail_tbl');
			
	  			$org_options = get_option('events_organization_settings');
				$Organization =$org_options['organization'];
				$Organization_street1 =$org_options['organization_street1'];
				$Organization_street2=$org_options['organization_street2'];
				$Organization_city =$org_options['organization_city'];
				$Organization_state=$org_options['organization_state'];
				$Organization_zip =$org_options['organization_zip'];
				$contact =$org_options['contact_email'];
 				$registrar = $org_options['contact_email'];
				$paypal_id =$org_options['paypal_id'];
				$paypal_cur =$org_options['currency_format'];
				$events_listing_type =$org_options['events_listing_type'];
				$message =$org_options['message'];
				$return_url = $org_options['return_url'];
				$cancel_return = $org_options['cancel_return'];
				$notify_url = $org_options['notify_url'];
				$image_url = $org_options['image_url'];
				$use_sandbox = $org_options['use_sandbox'];
			
	if ($use_sandbox == 1) {
		$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; // testing paypal url
		echo "<h3 style=\"color:#ff0000;\" title=\"Payments will not be processed\">Debug Mode Is Turned On</h3>";
	}else {
		$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr'; // paypal url
	}

			//Query Database for Active event and get variable
			$sql = "SELECT * FROM ". EVENTS_DETAIL_TABLE . " WHERE id ='$event_id'";
 
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc ($result)){
						$event_id = $row['id'];
						$event_name = $row['event_name'];
						$event_desc = $row['event_desc'];
						$event_description = $row['event_desc'];
						$event_identifier = $row['event_identifier'];
						$send_mail = $row['send_mail'];
						$active = $row['is_active'];
						$conf_mail = $row['conf_mail'];
						$use_coupon_code = $row['use_coupon_code'];	
						
			}

		$events_attendee_tbl = get_option('events_attendee_tbl');
		$query  = "SELECT * FROM ". EVENTS_ATTENDEE_TABLE ." WHERE id='$attendee_id'";
		//echo $query;
	   		$result = mysql_query($query) or die('Error : ' . mysql_error());
	   		while ($row = mysql_fetch_assoc ($result)){
	  		    $attendee_id = $row['id'];
				$attendee_last = $row['lname'];
				$attendee_first = $row['fname'];
				$attendee_address = $row['address'];
				$attendee_city = $row['city'];
				$attendee_state = $row['state'];
				$attendee_zip = $row['zip'];
				$attendee_email = $row['email'];
				$phone = $row['phone'];
				$date = $row['date'];
				$num_people = $row['quantity'];
				$payment_status = $row['payment_status'];
				$txn_type = $row['txn_type'];
				$amount_pd = $row['amount_pd'];
				$payment_date = $row['payment_date'];
				$event_id = $row['event_id'];
			}
			$attendee_name = $attendee_first.' '.$attendee_last;
			
			$event_price = number_format($event_cost,2);
			$event_price_x_attendees = number_format($event_cost * $num_people,2);

			$event_cost = $event_cost * $num_people;
			
			/*//Tier pricing test
			$event_price = 100.00;
			$num_people = 7;
			$tier_min = 2;
			$tier_people = $num_people - $tier_min;
			$tdisc = 50;
			$pdisc  = $event_price / 100;
			//echo $tier_people;
			
			if ($num_people > $tier_min){
					for ($i = 1; $i <= $tier_people; $i++) {
						//echo $i;
						$event_price = $event_price - ($tdisc * $pdisc);
						echo $event_price.'<br />';
					}
			}
			//End tier pricing test*/
			
			//from db

 

class PricingDetail{
	var $arg1;
	var $arg2;
	var $item;
	
	function __construct($arg1,$arg2,$item){
		$this->$arg1 = $arg1;
		$this->$arg2 = $arg2;
		$this->$name = $item;
	}

}

class CartItem{
	
	var $itemid;
	var $price;
	var $qty;
	var $totalDiscount;


	function __construct($itemid, $price, $qty, $totalDiscount){
		$this->itemid = $itemid;
		$this->price = $price;
		$this->qty = $qty;
		$this->totalDiscount = $totalDiscount;
	}
} 

/*foreach(row in results)

                $items[row["itemid"]] = new cartitem(row["item"],row["price"], row["qty"]);

 */


$sql_items = "SELECT event_id, event_price, min_qty FROM wp_events_tier_table WHERE event_id IN (SELECT event_id FROM wp_events_detail)";

while ($row = mysql_fetch_array($sql_items)) {
	foreach ($row as $key => $val) {
		//print "$key = $val\n";
		$items[$row["itemid"]] = new CartItem(row["item"],row["event_price"], row["min_qty"]);
	}  

}

$tier_pricing = "SELECT name, arg1, arg2, item FROM custompricing c JOIN itemcustompricing ic ON ic.customid = c.customid WHERE ic.item IN (SELECT item FROM cart)";

 

foreach(row in results)

                $custompricing[row["name"]] = new PricingDetail(row["arg1"],row["arg2"],row["item"]);

 

 

foreach($item in $items.keys)

{

                $bestPrice = $items[$item].$price * $items[$item].$qty;

                foreach($pricing in $custompricing.keys)

                {

                                if($custompricing[$pricing].$item = $item            

                                {

                                                switch($pricing)

                                                {

                                                                case "Tier":

                                                                                if($items[$item].$qty > $custompricing[$pricing].$arg1)

                                                                                {

                                                                                                $discount=  ($items[$item].$qty-$custompricing[$pricing].$arg1)  * ($custompricing[$pricing].$arg2);

                                                                                                $items[$item].$totalDiscount += $discount;

                                                                                }

                                                                break;

                                                }

                                }

                }

 

}

 

$prices = ($items[index].$price * $items[index].$qty) - $items[index].$totalDiscount;
		
			
			if ($use_coupon_code == "Y"){ 
				if ($_REQUEST['coupon_code'] != ''){
					$sql = "SELECT * FROM ". EVENTS_DISCOUNT_CODES_TABLE ." WHERE coupon_code = '".$_REQUEST['coupon_code']."'";
					$result = mysql_query ($sql);
					if (mysql_num_rows($result) != 0){
					//_e($sql,'event_espresso');
					$valid_discount = true;
						while ($row = mysql_fetch_assoc ($result)){
								$discount_id= $row['id'];
								$coupon_code=$row['coupon_code'];
								$coupon_code_price=$row['coupon_code_price'];
								$coupon_code_description=$row['coupon_code_description'];
								$use_percentage=$row['use_percentage'];
						}
						$discount_type_price = $use_percentage == 'Y' ? $coupon_code_price.'%' : $org_options['currency_symbol'].$coupon_code_price;
						_e('<p><strong>You are using discount code:</strong> '.$coupon_code.' ('.$discount_type_price.' discount)</p>','event_espresso');
						if($use_percentage == 'Y'){
							$pdisc  = $coupon_code_price / 100;
							$event_cost = $event_cost - ($event_cost * $pdisc);
						}else{
							$event_cost = $event_cost - $coupon_code_price;
						}
					}else{
						_e('<p><font color="red">Sorry, that coupon code is invalid or expired.</font></p>','event_espresso');
					}
				}
			 }
			 
	if ($event_cost != "" || $event_cost != "" ){
			 			
			if ($paypal_id !="" || $paypal_id !=" "){
			?>				  
<p align="left"><strong><?php _e('Please verify your registration details:','event_espresso'); ?></strong></p>
                    <table width="95%" border="0" id="event_espresso_attendee_verify">
                      <tr>
                        <td><strong><?php _e('Event Name/Price:','event_espresso'); ?></strong></td>
                        <td><?php echo $event_name?>/<?php echo $org_options['currency_symbol']?><?php echo $event_price?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Attendee Name:','event_espresso'); ?></strong></td>
                        <td><?php echo $attendee_name?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Email Address:','event_espresso'); ?></strong></td>
                        <td><?php echo $attendee_email?></td>
                      </tr>
                      <?php if ($num_people > 1){?>
                       <tr>
                        <td><strong><?php _e('Total Registrants:','event_espresso'); ?></strong></td>
                        <td><?php echo $num_people; ?> X <?php echo $org_options['currency_symbol']?><?php echo $event_price; ?> = <?php echo $org_options['currency_symbol']?><?php echo $event_price_x_attendees; ?></td>
                      </tr>
                      <?php }?>
                    </table>
<?
				$p->add_field('business', $paypal_id);
				$p->add_field('return', get_option('siteurl').'/?page_id='.$return_url);
				$p->add_field('cancel_return', get_option('siteurl').'/?page_id='.$cancel_return);
				$p->add_field('notify_url', get_option('siteurl').'/?page_id='.$notify_url.'&id='.$attendee_id.'&event_id='.$event_id.'&attendee_action=post_payment&form_action=payment');
				$p->add_field('item_name', $event_name . ' | '.__('Reg. ID:','event_espresso').' '.$attendee_id. ' | '.__('Name:','event_espresso').' '. $attendee_name .' | '.__('Total Registrants:','event_espresso').' '.$num_people);
				$p->add_field('amount', number_format($event_cost,2));
				$p->add_field('currency_code', $paypal_cur);
				$p->add_field('image_url', $image_url);
							  
				//Post variables
				$p->add_field('first_name', $attendee_first);
				$p->add_field('last_name', $attendee_last);
				$p->add_field('email', $attendee_email);
				$p->add_field('address1', $attendee_address);
				$p->add_field('city', $attendee_city);
				$p->add_field('state', $attendee_state);
				$p->add_field('zip', $attendee_zip);
				$p->submit_paypal_post(); // submit the fields to paypal
				if ($use_sandbox == true) {
					$p->dump_fields(); // for debugging, output a table of all the fields
				}   
			}
	  }
}

//This is the alternate PayPal button used for the email 
function event_espresso_pay(){

		global $wpdb;
		$events_attendee_tbl = get_option('events_attendee_tbl');
		$events_detail_tbl = get_option('events_detail_tbl');
		$paypal_cur = get_option('paypal_cur');
		$id="";
		$id=$_GET['id'];
if ($id ==""){ _e('Please check your email for payment information.','event_espresso');}
else{
			$query  = "SELECT * FROM ". EVENTS_ATTENDEE_TABLE . " WHERE id='$id'";
	   		$result = mysql_query($query) or die('Error : ' . mysql_error());
	   		while ($row = mysql_fetch_assoc ($result))
				{
	  		    $attendee_id = $row['id'];
				$lname = $row['lname'];
				$fname = $row['fname'];
				$address = $row['address'];
				$city = $row['city'];
				$state = $row['state'];
				$zip = $row['zip'];
				$email = $row['email'];
				$phone = $row['phone'];
				$date = $row['date'];
				$payment_status = $row['payment_status'];
				$txn_type = $row['txn_type'];
				$amount_pd = $row['amount_pd'];
				$payment_date = $row['payment_date'];
				$event_id = $row['event_id'];
				$attendee_name = $fname." ".$lname;
				}

				
			$org_options = get_option('events_organization_settings');
				$Organization =$org_options['organization'];
				$Organization_street1 =$org_options['organization_street1'];
				$Organization_street2=$org_options['organization_street2'];
				$Organization_city =$org_options['organization_city'];
				$Organization_state=$org_options['organization_state'];
				$Organization_zip =$org_options['organization_zip'];
				$contact =$org_options['contact_email'];
 				$registrar = $org_options['contact_email'];
				$paypal_id =$org_options['paypal_id'];
				$paypal_cur =$org_options['currency_format'];
				$events_listing_type =$org_options['events_listing_type'];
				$message =$org_options['message'];
				$return_url = $org_options['return_url'];
				$cancel_return = $org_options['cancel_return'];
				$notify_url = $org_options['notify_url'];
				$use_sandbox = $org_options['use_sandbox'];
				$image_url = $org_options['image_url'];
				$currency_symbol = $org_options['currency_symbol'];
			


			//Query Database for event and get variable

			$sql = "SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='$event_id'";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc ($result)){
						//$event_id = $row['id'];
						$event_name = $row['event_name'];
						$event_desc = $row['event_desc'];
						$event_description = $row['event_desc'];
						$event_identifier = $row['event_identifier'];
						$active = $row['is_active'];
			}

 _e('<br><br><strong>Thank You '.$fname.' '.$lname.' for registering for '.$event_name.'</strong><br><br>','event_espresso');

if ($payment_status == "Completed"){echo "<p><font color='red' size='3'>".__('Our records indicate you have paid','event_espresso')." ".$currency_symbol.$amount_pd."</font></p>";}
if ($payment_status == "Pending"){echo "<p><font color='red' size='3'>".__('Our records indicate your payment is pending.','event_espresso')."<br />".__('Amount pending:','event_espresso')." ".$currency_symbol.$amount_pd."</font></p>";}

if ($payment_status != ("Completed" || "Pending") ){
	
if ($event_cost != "0.00" && $paypal_id !=""){
	
//Payment Selection with data hidden - forwards to paypal
		?>
<p align="left"><strong><?php _e('Payment By Credit Card, Debit Card or Pay Pal Account','event_espresso'); ?><br>
  <?php _e('(a PayPal account is not required to pay by credit card).','event_espresso'); ?></strong></p>
<p><?php _e('Payment will be in the amount of','event_espresso'); ?>  <?php echo $currency_symbol.$amount_pd;?>.</p>
<p><?php _e('PayPal Payments will be sent to:','event_espresso'); ?> <?php echo $Organization?> (<?php echo $paypal_id?>)</p>
  
  

<table width="500">
  <tr>
    <td align="center" valign="middle">&nbsp;<br />
      <strong>
      <?php echo $event_name." - ".$currency_symbol.$amount_pd;?>
      </strong>&nbsp;
      <? 
	  if ($use_sandbox == 1){ 
      		echo "<form action='https://www.sandbox.paypal.com/cgi-bin/webscr' method='post'>";
       }else{
      		echo "<form action='https://www.paypal.com/cgi-bin/webscr' method='post'>";
	   }
     /* ?>
      Additional attendees?
      <select name="quantity" style="width:70px;margin-top:4px">
        <option value="1" selected>None</option>
        <option value="2">1</option>
        <option value="3">2</option>
        <option value="4">3</option>
        <option value="5">4</option>
        <option value="6">5</option>
      </select>
      x
      <?php echo $currency_symbol." ".$event_cost; ?>
      <?php  if ($paypal_cur == "$" || $paypal_cur == ""){
				$paypal_cur ="USD";
			} */?>
      <br />
      <br />
      <input type="hidden" name="bn" value="AMPPFPWZ.301">
      <input type="hidden" name="cmd" value="_ext-enter">
      <input type="hidden" name="redirect_cmd" value="_xclick">
      <input type="hidden" name="business" value="<?php echo $paypal_id;?>" >
      <input type="hidden" name="item_name" value="<?php echo $event_name." - ".$attendee_id." - ".$attendee_name;?>">
      <input type="hidden" name="item_number" value="<?php echo $event_identifier;?>">
      <input type="hidden" name="amount" value="<?php echo $amount_pd;?>">
      <input type="hidden" name="currency_code" value="<?php echo $paypal_cur;?>">
      <input type="hidden" name="undefined_quantity" value="0">
      <input type="hidden" name="custom" value="<?php echo $attendee_id;?>">
      <input type="hidden" name="image_url" value="<?php echo $image_url;?>">
      <input type="hidden" name="email" value="<?php echo $attendee_email;?>">
      <input type="hidden" name="first_name" value="<?php echo $attendee_first;?>">
      <input type="hidden" name="last_name" value="<?php echo $attendee_last;?>">
      <input type="hidden" name="address1" value="<?php echo $attendee_address;?>">
      <input type="hidden" name="address2" value="">
      <input type="hidden" name="city" value="<?php echo $attendee_city;?>">
      <input type="hidden" name="state" value="<?php echo $attendee_state;?>">
      <input type="hidden" name="zip" value="<?php echo $attendee_zip;?>">
      <input type="hidden" name="return" value="<?php echo get_option('siteurl')?>/?page_id=<?php echo $return_url;?>">
      <input type="hidden" name="cancel_return" value="<?php echo get_option('siteurl')?>/?page_id=<?php echo $cancel_return;?>">
      <input type="hidden" name="notify_url" value="<?php echo get_option('siteurl')?>/?page_id=<?php echo $notify_url?>&id=<?php echo $attendee_id;?>&event_id=<?php echo $event_id?>&attendee_action=post_payment&form_action=payment">
      <input type="hidden" name="rm" value="2">
      <input type="hidden" name="add" value="1">
      <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" align='middle' name="submit">
      </form></td>
  </tr>
</table>
<?php		}
		}
	}
}
?>
