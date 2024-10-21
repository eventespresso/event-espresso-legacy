<?php
$transaction_page_url = 'YOUR TRANSACTION PAGE URL HERE';
if(isset($_POST['transStatus']) && $_POST['transStatus'] != "Y"){
header("location: " . $transaction_page_url); //<-- go to this page but since no post data is set it will show error
exit();
}else{

# clear a variable to hold the POSTed data
$datafields = "";
foreach($_POST as $name => $value) {
	if ( $name == "name" ) {
		$name="wpnm";
	}
	$datafields.=$name."=".$value."&";
}
$ch = curl_init($transaction_page_url);
curl_setopt ($ch, CURLOPT_POST, 1);
curl_setopt ($ch, CURLOPT_POSTFIELDS, $datafields);
curl_exec ($ch);
curl_close ($ch);
}
?>