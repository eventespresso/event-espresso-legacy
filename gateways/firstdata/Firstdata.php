<?php

/**********************************************************************\

	SALE_MININFO.php -  Minimum Required Fields for a Credit Card SALE


	Copyright 2003 LinkPoint International, Inc. All Rights Reserved.

	This software is the proprietary information of LinkPoint International, Inc.
	Use is subject to license terms.

\**********************************************************************/

//Find the correct amount so that unsavory characters don't change it in the previous form

$registration_id = espresso_registration_id( $_POST['id'] );

$sql = "SELECT ea.amount_pd, ed.event_name FROM " . EVENTS_ATTENDEE_TABLE . " ea ";
$sql .= "JOIN " . EVENTS_DETAIL_TABLE . " ed ";
$sql .= "ON ed.id = ea.event_id ";
$sql .= " WHERE registration_id = '" . $registration_id . "' ";
$sql .= " ORDER BY ea.id ASC LIMIT 1";

$r = $wpdb->get_row($sql);

if (!$r || $wpdb->num_rows == 0){

    exit("Looks like something went wrong.  Please try again or notify the website administrator.");

}


$firstdata_settings = get_option( 'event_espresso_firstdata_settings' );

$pem_file = EVENT_ESPRESSO_PLUGINFULLPATH."gateways/firstdata/" . $firstdata_settings['firstdata_store_id'] . ".pem";

	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata/" . $firstdata_settings['firstdata_store_id'] . ".pem")){

            $pem_file = EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata/" . $firstdata_settings['firstdata_store_id'] . ".pem";

	}


	include"lphp.php";
	$mylphp=new lphp;

	$myorder["host"]       = "secure.linkpt.net";
	$myorder["port"]       = "1129";
	$myorder["keyfile"]    = $pem_file; # Change this to the name and location of your certificate file
	$myorder["configfile"] = $firstdata_settings['firstdata_store_id'];        # Change this to your store number

	$myorder["ordertype"]    = "SALE";
	$myorder["result"]       = "LIVE"; # For a test, set result to GOOD, DECLINE, or DUPLICATE
	$myorder["cardnumber"]   = $_POST['card_num'];
	$myorder["cardexpmonth"] = $_POST['expmonth'];
	$myorder["cardexpyear"]  = $_POST['expyear'];
	$myorder["chargetotal"]  = $r->amount_pd;

        $myorder["name"]     = $_POST['first_name'] . ' ' . $_POST['last_name'];
        $myorder["address1"] = $_POST['address'];
	$myorder["city"]     = $_POST["city"];
	$myorder["state"]    = $_POST["state"];
	$myorder["phone"]    = $_POST["phone"];
	$myorder["email"]    = $_POST["email"];
        
        /*
         * It looks like firstdata requires addrnum, the beginning 
         * number of the address.  On their test forms, they have a specific 
         * field for this.  I am just going to grab the address, split it and grab 
         * index 0.  Will see how this goes before adding a new field.  If can't split the 
         * address, will pass it full.
         */
        
        $addrnum = $_POST['address'];
        
        $temp_address = explode(" ", $_POST['address']);
        
        if (count($temp_address > 0))
             $addrnum = $temp_address[0];
        
	$myorder["addrnum"]  = $addrnum;
	$myorder["zip"]      = $_POST["zip"];


	//$myorder["debugging"] = "true";  # for development only - not intended for production use

	# ITEMS AND OPTIONS
	/*$items = array (
		'id' 			=> $registration_id,
		'description' 	=> stripslashes_deep( $r->event_name ),
		'quantity' 		=> '1',
		'price'			=>  $r->amount_pd
		);

	$myorder["items"][0] = $items; # put array of items into hash
*/

	# MISC
	//$myorder["comments"] = "Repeat customer. Ship immediately.";
	//$myorder["referred"] = "Saw ad on Web site.";
        //$myorder["debugging"] = "true";  # for development only - not intended for production use



  # Send transaction. Use one of two possible methods  #
//	$result = $mylphp->process($myorder);       # use shared library model
	$result = $mylphp->curl_process($myorder);  # use curl methods


	if ($result["r_approved"] != "APPROVED")	// transaction failed, print the reason
	{
		print "<br />Status: $result[r_approved]\n";
		print "<br />Error: $result[r_error]\n";
	}
	else
	{	// success
            $payment_status = 'Completed';

            $sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET
                payment_status = '$payment_status',
                txn_id = '". $result["r_ordernum"] ."',
                txn_type = '$txn_type',
                amount_pd = '". $r->amount_pd . "',
                payment_date ='". $result["r_tdate"]."'
                WHERE registration_id ='" . $registration_id . "' ";

            $wpdb->query( $sql );


	}

/*
	# Look at returned hash & use the elements you need  #
	while (list($key, $value) = each($result))
	{
		echo "$key = $value\n";

	# (if you're in web space, look at response like this):
		 echo htmlspecialchars($key) . " = " . htmlspecialchars($value) . "<BR>\n";
	}
*/
?>
