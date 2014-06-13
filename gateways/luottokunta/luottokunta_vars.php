<?php
function espresso_display_luottokunta($payment_data){
	global $org_options;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'Luottokunta';
	
	//$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	global $wpdb, $org_options;
	$luottokunta_settings = get_option('event_espresso_luottokunta_settings');
	$currency_code=978;//for now luottokuna only supports Euros, which have currency cod e978$luottokunta_settings['currency_code'];
	$merchant_number=$luottokunta_settings['luottokunta_id'];
	$mac_key=$luottokunta_settings['luottokunta_mac_key'];//it would be good to use this 
	$total_cost_in_cents=$payment_data['total_cost']*100;
	$description=$payment_data['event_name'];
	
	$bypass_payment_page = ($luottokunta_settings['bypass_payment_page'] == 'Y')?true:false;
	$button_url = $luottokunta_settings['button_url'];
	//generate a unique key which must be unique for every request to luottokunta
	$order_id=wp_generate_password(9,false);
	$base_return_url= add_query_arg(array(
		'r_id'=>$payment_data['registration_id'],
		'type'=>'luottokunta',
		'order_id'=>$order_id), 
			get_permalink($org_options['return_url'] )); 
	$success_url=add_query_arg('success',true,$base_return_url);
	$failure_url = add_query_arg('success',0,$base_return_url);
	$cancel_url = get_permalink($org_options['cancel_return']);
	if ($luottokunta_settings['force_ssl_return']) {
		$success_url = str_replace("http://", "https://", $success_url);
		$failure_url = str_replace("http://", "https://", $failure_url);
		$cancel_url = str_replace("http://", "https://", $cancel_url);
	}
	$server_url="https://dmp2.luottokunta.fi/dmp/html_payments";
	$user_ip = $_SERVER["REMOTE_ADDR"]!='::1'?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
	
	$button_url = espresso_select_button_for_display($luottokunta_settings['button_url'], "luottokunta/luottokunta.jpg");
	$submit_html="<input type='image' class='payment-option-lnk allow-leave-page' src='$button_url'/>";

	if($bypass_payment_page){
		$bypass_payment_page_js="<script>document.getElementById('luottokunta_form').submit();</script>";
	}else{
		$bypass_payment_page_js="";
	}
	$transaction_type="0";
	
	$authentication_mac_unhashed = $mac_check_in_form=implode("&",array($merchant_number,$order_id,$total_cost_in_cents,$currency_code,$transaction_type,$mac_key));
	//echo "authentication mac unhasehd:".$authentication_mac_unhashed; //note: order is essential. see luottokunta's "html form interface v1.3 section 4.2.1
	$authentication_mac = hash('sha256',$authentication_mac_unhashed);
	$external_link_img = EVENT_ESPRESSO_PLUGINFULLURL . '/images/icons/external-link.png';
	
	$formhtml=<<<HEREDOC
		 <div id="luottokunta-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
			<img class="off-site-payment-gateway-img" width="16" height="16" src="{$external_link_img}" alt="click to visit this payment gateway">
	
			<form action="{$server_url}" method="post" id='luottokunta_form'> 
				<input type="hidden" name="Merchant_Number" value="{$merchant_number}"> 
				
				<input type="hidden" name="Card_Details_Transmit" value="0">
				<input type="hidden" name="Language" value="{$luottokunta_settings['luottokunta_payment_page_language']}"> 
				<input type="hidden" name="Device_Category" value="1"> 
				<input type="hidden" name="Order_ID" value="{$order_id}"> 
				<input type="hidden" name="Customer_ID" value="{$payment_data['attendee_id']}"> 
				<input type="hidden" name="Amount" value="{$total_cost_in_cents}"> 
				<input type="hidden" name="Currency_Code" value="{$currency_code}"> 
				<input type="hidden" name="Order_Description" value="{$description}"> 
				<input type="hidden" name="Success_Url" value="{$success_url}"> 
				<input type="hidden" name="Failure_Url" value="{$failure_url}"> 
				<input type="hidden" name="Cancel_Url" value="{$cancel_url}"> 
				<input type="hidden" name="Transaction_Type" value="{$transaction_type}"> 
				<input type='hidden' name='Authentication_Mac' value='{$authentication_mac}'>

				<input type="hidden" name="Customer_IP_Address" value="{$user_ip}"> 
				{$submit_html}
				</form>
			</div>
$bypass_payment_page_js
HEREDOC;

/* other fields from demo
<input type="text" name="Authentication_Mac" value="{$mac_key}">

	<input type="text" name="Dossier_ID" value="Dossier123"> 

<input type="TEXT" name="OrderID" value="{$payment_data['registration_id']}">OrderID<br>
<input type="TEXT" name="Sname" value="John Smith">Sname<br>
<input type="TEXT" name="Saddress1" value="123 Main St.">Saddress1<br>
<input type="TEXT" name="Saddress2" value="Apt 6">Saddress2<br>
<input type="TEXT" name="Scity" value="Toronto">Scity<br>
<input type="TEXT" name="Sprovince" value="Ontario">Sprovince<br>
<input type="TEXT" name="Spostalcode" value="L5N2B3">Spostalcode<br>
<input type="TEXT" name="Scountry" value="Canada">Scountry<br>
<input type="TEXT" name="Scompany" value="Luottokunta">Scompany<br>
<input type="TEXT" name="Bcompany" value="Luottokunta">Bcompany<br>
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

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_luottokunta');
