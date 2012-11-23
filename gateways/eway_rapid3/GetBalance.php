<?php
// Included required files.
require_once('includes/config.php');
require_once('includes/paypal.nvp.class.php');

// Setup eway_rapid3 object
$eway_rapid3Config = array('Sandbox' => $sandbox, 'APIUsername' => $api_username, 'APIPassword' => $api_password, 'APISignature' => $api_signature);
$eway_rapid3 = new eway_rapid3($eway_rapid3Config);

// Populate data arrays with order data.
$GBFields = array(
				'returnallcurrencies' => '1'					// Whether or not to return all currencies.  0 or 1.
			);

// Wrap all data arrays into a single, "master" array which will be passed into the class function.
$eway_rapid3RequestData = array(
						   'GBFields' => $GBFields
						   );

// Pass the master array into the eway_rapid3 class function
$eway_rapid3Result = $eway_rapid3->GetBalance($eway_rapid3RequestData);

// Display results
echo '<pre />';
print_r($eway_rapid3Result);
?>