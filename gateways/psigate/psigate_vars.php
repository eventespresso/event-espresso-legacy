<?php
function espresso_display_psigate($payment_data){
	global $wpdb;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'PSiGate';
	
	//$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	
	global $wpdb, $org_options;
	$psigate_settings = get_option('event_espresso_psigate_settings');
	$currency_format=$psigate_settings['currency_format'];
	$event_meta=$wpdb->get_var($wpdb->prepare("SELECT event_meta from {$wpdb->prefix}events_detail WHERE id=%s",$payment_data['event_id']));
	$event_meta=maybe_unserialize($event_meta);
	if(array_key_exists('event_currency',$event_meta)){
		$currency_format=$event_meta['event_currency'];
	}
	if('USD'==$currency_format){
		$storekey=$psigate_settings['psigate_id_us'];
	}else{
		$storekey=$psigate_settings['psigate_id_can'];
	}
	$bypass_payment_page = ($psigate_settings['bypass_payment_page'] == 'Y')?true:false;
	$button_url = $psigate_settings['button_url'];
	
	
	$return_url= espresso_build_gateway_url('return_url', $payment_data, 'psigate');
	$server_url=($psigate_settings['use_sandbox'])?"https://devcheckout.psigate.com/HTMLPost/HTMLMessenger":'https://checkout.psigate.com/HTMLPost/HTMLMessenger';
	/* @var $items StdClass[] array of attendees inner join with event on the current purhcase*/
	//$items=espresso_get_items_being_purchased($payment_data['attendee_id']);
	//get payment's details
	//get country of user. default to Canada, as this gateway is canadian
	$country=array_key_exists('country',$payment_data)?$payment_data['country']:'';
	$address2=array_key_exists('address2',$payment_data)?$payment_data['address2']:'';
	$user_ip = $_SERVER["REMOTE_ADDR"];
	
	$button_url = espresso_select_button_for_display($psigate_settings['button_url'], "psigate/psigate.gif");
	$submit_html="<input class='allow-leave-page payment-option-lnk' type='image' src='$button_url'/>";

	if($bypass_payment_page){
		$bypass_payment_page_js="<script>document.getElementById('psigate_form').submit();</script>";
	}else{
		$bypass_payment_page_js="";
	}
	
	$external_link_img = EVENT_ESPRESSO_PLUGINFULLURL . '/images/icons/external-link.png';
		
	$formhtml=<<<HEREDOC
		 <div id="PSiGate-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
			<img class="off-site-payment-gateway-img" width="16" height="16" src="{$external_link_img}" alt="click to visit this payment gateway">
	
			<form action="{$server_url}" id='psigate_form' method="post">
			<input type="HIDDEN" name="MerchantID" value="{$storekey}">
			<input type='HIDDEN' name='ThanksURL' value='{$return_url}'>
			<input type='HIDDEN' name='NoThanksURL' value='{$return_url}'>
			<input type="HIDDEN" name="PaymentType" value="CC">
			<input type="HIDDEN" name="Bname" value="{$payment_data['fname']} {$payment_data['lname']}">
			<input type="HIDDEN" name="Baddress1" value="{$payment_data['address']}">
			<input type="HIDDEN" name="Baddress2" value="{$address2}">
			<input type="HIDDEN" name="Bcity" value="{$payment_data['city']}">
			<input type="HIDDEN" name="Bprovince" value="{$payment_data['state']}">
			<input type="HIDDEN" name="Bpostalcode" value="{$payment_data['zip']}">
			<input type="HIDDEN" name="Bcountry" value="{$country}">

			<input type="HIDDEN" name="Phone" value="{$payment_data['phone']}">
			<input type="HIDDEN" name="Email" value="{$payment_data['attendee_email']}">

			<input type="HIDDEN" name="SubTotal" value="{$payment_data['total_cost']}">
			<input type="HIDDEN" name="CardAction" value="0">
			<input type="HIDDEN" name="CustomerIP" value="{$user_ip}">
			$submit_html
			</form>
			</div>
$bypass_payment_page_js
HEREDOC;

/* other fields from demo

<input type="TEXT" name="OrderID" value="{$payment_data['registration_id']}">OrderID<br>
<input type="TEXT" name="Sname" value="John Smith">Sname<br>
<input type="TEXT" name="Saddress1" value="123 Main St.">Saddress1<br>
<input type="TEXT" name="Saddress2" value="Apt 6">Saddress2<br>
<input type="TEXT" name="Scity" value="Toronto">Scity<br>
<input type="TEXT" name="Sprovince" value="Ontario">Sprovince<br>
<input type="TEXT" name="Spostalcode" value="L5N2B3">Spostalcode<br>
<input type="TEXT" name="Scountry" value="Canada">Scountry<br>
<input type="TEXT" name="Scompany" value="PSiGate">Scompany<br>
<input type="TEXT" name="Bcompany" value="PSiGate">Bcompany<br>
<input type="TEXT" name="CustomerRefNo" value="123456789">CustomerRefNo<br>
<input type="TEXT" name="TestResult" value="">TestResult<br>
<input type="TEXT" name="UserID" value="User1">UserID<br>
<input type="TEXT" name="Comments" value="No comments today">Comments<br>
<input type="TEXT" name="CardNumber" value="">CardNumber<br>
<input type="TEXT" name="CardExpMonth" value="">CardExpMonth<br>
<input type="TEXT" name="CardExpYear" value="">CardExpYear<br>
<input type="TEXT" name="TransRefNumber" value="">TransRefNumber<br>
<input type="TEXT" name="CardAuthNumber" value="">CardAuthNumber<br>

<input type="TEXT" name="Fax" value="416-555-2091">Fax<br>
<input type="TEXT" name="Tax1" value="1">Tax1<br>
<input type="TEXT" name="Tax2" value="2">Tax2<br>
<input type="TEXT" name="Tax3" value="3">Tax3<br>
<input type="TEXT" name="Tax4" value="4">Tax4<br>
<input type="TEXT" name="Tax5" value="5">Tax5<br>
<input type="TEXT" name="ShippingTotal" value="6">ShippingTotal<br>
<input type="TEXT" name="CardIDNumber" value="">CardIDNumber<br>
<input type="TEXT" name="CardXID" value="">CardXID<br>
<input type="TEXT" name="CardECI" value="">CardECI<br>
<input type="TEXT" name="CardCavv" value="">CardCavv<br>
<input type="TEXT" name="CardLevel2PO" value="">CardLevel2PO<br>
<input type="TEXT" name="CardLevel2Tax" value="">CardLevel2Tax<br>
<input type="TEXT" name="CardLevel2TaxExempt" value="">CardLevel2TaxExempt<br>
<input type="TEXT" name="CardLevel2ShiptoZip" value="">CardLevel2ShiptoZip<br>
<input type="TEXT" name="AuthorizationNumber" value="">AuthorizationNumber<br>
<input type="TEXT" name="CardRefNumber" value="">CardRefNumber<br><input type="TEXT" name="ItemID01" value="apple">ItemID01
<input type="TEXT" name="Description01" value="delicious apple">Description01
<input type="TEXT" name="Quantity01" value="2">Quantity01
<input type="TEXT" name="Price01" value="15">Price01<br><input type="TEXT" name="OptionName0101" value="Color0101"><input type="TEXT" name="OptionValue0101" value="Red01">Option01
<input type="TEXT" name="OptionName0102" value="Color0102"><input type="TEXT" name="OptionValue0102" value="Green01">Option02<br><input type="TEXT" name="OptionName0103" value="Color0103"><input type="TEXT" name="OptionValue0103" value="Yellow01">Option03
<input type="TEXT" name="OptionName0104" value="Color0104"><input type="TEXT" name="OptionValue0104" value="Black01">Option04<br><input type="TEXT" name="OptionName0105" value="Color0105"><input type="TEXT" name="OptionValue0105" value="White01">Option05<br><input type="TEXT" name="ItemID02" value="book">ItemID02
<input type="TEXT" name="Description02" value="good book">Description02
<input type="TEXT" name="Quantity02" value="3">Quantity02
<input type="TEXT" name="Price02" value="25">Price02<br><input type="TEXT" name="OptionName0201" value="Color0201"><input type="TEXT" name="OptionValue0201" value="Red02">Option01
<input type="TEXT" name="OptionName0202" value="Color0202"><input type="TEXT" name="OptionValue0202" value="Green02">Option02<br><input type="TEXT" name="OptionName0203" value="Color0203"><input type="TEXT" name="OptionValue0203" value="Yellow02">Option03
<input type="TEXT" name="OptionName0204" value="Color0204"><input type="TEXT" name="OptionValue0204" value="Black02">Option04<br><input type="TEXT" name="OptionName0205" value="Color0205"><input type="TEXT" name="OptionValue0205" value="White02">Option05<br><input type="TEXT" name="ItemID03" value="computer">ItemID03
<input type="TEXT" name="Description03" value="IBM computer">Description03
<input type="TEXT" name="Quantity03" value="1">Quantity03
<input type="TEXT" name="Price03" value="1200">Price03<br><input type="TEXT" name="OptionName0301" value="Color0301"><input type="TEXT" name="OptionValue0301" value="Red03">Option01
<input type="TEXT" name="OptionName0302" value="Color0302"><input type="TEXT" name="OptionValue0302" value="Green03">Option02<br><input type="TEXT" name="OptionName0303" value="Color0303"><input type="TEXT" name="OptionValue0303" value="Yellow03">Option03
<input type="TEXT" name="OptionName0304" value="Color0304"><input type="TEXT" name="OptionValue0304" value="Black03">Option04<br><input type="TEXT" name="OptionName0305" value="Color0305"><input type="TEXT" name="OptionValue0305" value="White03">Option05<br><input type="TEXT" name="ItemID04" value="">ItemID04
<input type="TEXT" name="Description04" value="">Description04
<input type="TEXT" name="Quantity04" value="">Quantity04
<input type="TEXT" name="Price04" value="">Price04<br><input type="TEXT" name="OptionName0401" value="Color0401"><input type="TEXT" name="OptionValue0401" value="Red04">Option01
<input type="TEXT" name="OptionName0402" value="Color0402"><input type="TEXT" name="OptionValue0402" value="Green04">Option02<br><input type="TEXT" name="OptionName0403" value="Color0403"><input type="TEXT" name="OptionValue0403" value="Yellow04">Option03
<input type="TEXT" name="OptionName0404" value="Color0404"><input type="TEXT" name="OptionValue0404" value="Black04">Option04<br><input type="TEXT" name="OptionName0405" value="Color0405"><input type="TEXT" name="OptionValue0405" value="White04">Option05<br><input type="TEXT" name="ItemID05" value="">ItemID05
<input type="TEXT" name="Description05" value="">Description05
<input type="TEXT" name="Quantity05" value="">Quantity05
<input type="TEXT" name="Price05" value="">Price05<br><input type="TEXT" name="OptionName0501" value="Color0501"><input type="TEXT" name="OptionValue0501" value="Red05">Option01
<input type="TEXT" name="OptionName0502" value="Color0502"><input type="TEXT" name="OptionValue0502" value="Green05">Option02<br><input type="TEXT" name="OptionName0503" value="Color0503"><input type="TEXT" name="OptionValue0503" value="Yellow05">Option03
<input type="TEXT" name="OptionName0504" value="Color0504"><input type="TEXT" name="OptionValue0504" value="Black05">Option04<br><input type="TEXT" name="OptionName0505" value="Color0505"><input type="TEXT" name="OptionValue0505" value="White05">Option05<br><input type="SUBMIT" value="Buy Now"><table>
-->*/

	echo $formhtml;
	return $payment_data;
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_psigate');

