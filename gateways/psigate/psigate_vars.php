<?php
function espresso_display_psigate($payment_data){
	global $wpdb;
	$formhtml=<<<HEREDOC
 

<!--Test Settings-->
<!--Set the StoreKey (MerchantID) variable to “psigatecapturescard001010” if you wish to capture the credit card data through a secured form provided by PSiGate.-->
<!--Set the StoreKey (MerchantID) variable to “merchantcardcapture200024” if you wish to capture the credit card data through a secured checkout form you provide.-->

<!--Production server-->
<!--<FORM ACTION='https://checkout.psigate.com/HTMLPost/HTMLMessenger' METHOD=post>-->

<!--Test server-->
<form action="https://devcheckout.psigate.com/HTMLPost/HTMLMessenger" method="post"><input type="TEXT" name="MerchantID" value="psigatecapturescard001010">MerchantID<br><input type="TEXT" name="CustomerRefNo" value="123456789">CustomerRefNo<br><input type="TEXT" name="PaymentType" value="CC">PaymentType<br><input type="TEXT" name="TestResult" value="">TestResult<br><input type="TEXT" name="OrderID" value="">OrderID<br><input type="TEXT" name="UserID" value="User1">UserID<br><input type="TEXT" name="Bname" value="John Smith">Bname<br><input type="TEXT" name="Bcompany" value="PSiGate">Bcompany<br><input type="TEXT" name="Baddress1" value="123 Main St.">Baddress1<br><input type="TEXT" name="Baddress2" value="Apt 6">Baddress2<br><input type="TEXT" name="Bcity" value="Toronto">Bcity<br><input type="TEXT" name="Bprovince" value="Ontario">Bprovince<br><input type="TEXT" name="Bpostalcode" value="L5N2B3">Bpostalcode<br><input type="TEXT" name="Bcountry" value="Canada">Bcountry<br><input type="TEXT" name="Sname" value="John Smith">Sname<br><input type="TEXT" name="Scompany" value="PSiGate">Scompany<br><input type="TEXT" name="Saddress1" value="123 Main St.">Saddress1<br><input type="TEXT" name="Saddress2" value="Apt 6">Saddress2<br><input type="TEXT" name="Scity" value="Toronto">Scity<br><input type="TEXT" name="Sprovince" value="Ontario">Sprovince<br><input type="TEXT" name="Spostalcode" value="L5N2B3">Spostalcode<br><input type="TEXT" name="Scountry" value="Canada">Scountry<br><input type="TEXT" name="Phone" value="416-555-2092">Phone<br><input type="TEXT" name="Fax" value="416-555-2091">Fax<br><input type="TEXT" name="Email" value="charles.zhu@psigate.com">Email<br><input type="TEXT" name="Comments" value="No comments today">Comments<br><input type="TEXT" name="Tax1" value="1">Tax1<br><input type="TEXT" name="Tax2" value="2">Tax2<br><input type="TEXT" name="Tax3" value="3">Tax3<br><input type="TEXT" name="Tax4" value="4">Tax4<br><input type="TEXT" name="Tax5" value="5">Tax5<br><input type="TEXT" name="ShippingTotal" value="6">ShippingTotal<br><input type="TEXT" name="SubTotal" value="8">SubTotal<br><input type="TEXT" name="CardAction" value="0">CardAction<br><input type="TEXT" name="CardNumber" value="">CardNumber<br><input type="TEXT" name="CardExpMonth" value="">CardExpMonth<br><input type="TEXT" name="CardExpYear" value="">CardExpYear<br><input type="TEXT" name="TransRefNumber" value="">TransRefNumber<br><input type="TEXT" name="CardAuthNumber" value="">CardAuthNumber<br><input type="TEXT" name="CustomerIP" value="216.220.59.201">CustomerIP<br><input type="TEXT" name="CardIDNumber" value="">CardIDNumber<br><input type="TEXT" name="CardXID" value="">CardXID<br><input type="TEXT" name="CardECI" value="">CardECI<br><input type="TEXT" name="CardCavv" value="">CardCavv<br><input type="TEXT" name="CardLevel2PO" value="">CardLevel2PO<br><input type="TEXT" name="CardLevel2Tax" value="">CardLevel2Tax<br><input type="TEXT" name="CardLevel2TaxExempt" value="">CardLevel2TaxExempt<br><input type="TEXT" name="CardLevel2ShiptoZip" value="">CardLevel2ShiptoZip<br><input type="TEXT" name="AuthorizationNumber" value="">AuthorizationNumber<br><input type="TEXT" name="CardRefNumber" value="">CardRefNumber<br><input type="TEXT" name="ItemID01" value="apple">ItemID01
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
</table>
</form>
   
HEREDOC;
	echo $formhtml;
	return $payment_data;
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_psigate');
