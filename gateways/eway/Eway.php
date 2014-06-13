<?php

/**
 * eWay Class
 *
 * Author 		Seth Shoultes
 * @package		Event Espresso eWay Gateway
 * @category	Library
 */
$eway_gateway_version = '1.0';

class Espresso_Eway extends Espresso_PaymentGateway {

	var $eway_settings = NULL;
	/**
	 * array of arguuments for sending along with redirect
	 * @var array
	 */
	var $gatewayUrlArgs = array();
    /**
     * Initialize the eWay gateway
     *
     * @param none
     * @return void
     */
    public function __construct() {
        parent::__construct();
        // Some default values of the class
        $this->eway_settings = get_option('event_espresso_eway_settings');
        switch ($this->eway_settings['region']) {
            case 'NZ':
                $this->gatewayUrl = 'https://nz.ewaygateway.com/Request/';
                break;
            case 'AU':
                $this->gatewayUrl = 'https://au.ewaygateway.com/Request/';
                break;
            case 'UK':
                $this->gatewayUrl = 'https://payment.ewaygateway.com/Request/';
                break;
        }
        // Populate $fields array with a few default
    }

    public function enableTestMode() {
        $this->testMode = TRUE;
    }

    protected function prepareSubmit() {
		$ewayurl = "?";
		foreach ($this->fields as $name => $value) {
			$ewayurl .= $name . '=' . str_replace(array('#','&'),array('%23','%26'),htmlspecialchars_decode  ($value,ENT_QUOTES )) . '&';
		}
		$ewayurl = rtrim($ewayurl, "&");
        $spacereplace = str_replace(" ", "%20", $ewayurl);
        $posturl = $this->gatewayUrl . $spacereplace;

        $ch = curl_init();
        $error["set_host"] = curl_setopt($ch, CURLOPT_URL, $posturl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($ch);
		$error["result"] = curl_error($ch);
        function fetch_data($string, $start_tag, $end_tag) {
            $position = stripos($string, $start_tag);
            $str = substr($string, $position);
            $str_second = substr($str, strlen($start_tag));
            $second_positon = stripos($str_second, $end_tag);
            $str_third = substr($str_second, 0, $second_positon);
            $fetch_data = trim($str_third);
            return $fetch_data;
        }

        $responsemode = fetch_data($response, '<result>', '</result>');
        $responseurl = fetch_data($response, '<uri>', '</uri>');

  
      if ($responsemode == "True") {
			//we COULD just use the responseurl and redirect the user to it.
			//but we kinda prefer to make a form with GET method and submit it
			//instead. So let's find the URL and the args to add to the form...
			$url_parts = explode("?", $responseurl,2);
            $this->gatewayUrl =  $url_parts[0];
			parse_str($url_parts[1],$this->gatewayUrlArgs );
        } elseif ( $this->eway_settings['use_sandbox'] != '' ) {
            echo "ERROR\n";
						echo $error["set_host"] ? "Success" : "Failure";
            echo " Setting host: " . $posturl . "\n";
						echo $error["result"] . "\n";
						echo $response . "\n";
        }
    }

		public function submitPayment( $fields = FALSE ) {
            $this->prepareSubmit();
            echo "<html>\n";
            echo "<head><title>Processing Payment...</title></head>\n";
            echo "<body onLoad=\"document.forms['payment_form'].submit();\">\n";
            echo "<p style=\"text-align:center;\"><h2>Please wait, your order is being processed and you";
            echo " will be redirected to the payment website.</h2></p>\n";
            echo '<form method="get" name="payment_form" action="' . $this->gatewayUrl . '">';
			$this->echoArgs();
            echo "<input type=\"hidden\" id=\"bypass_payment_page\" name=\"bypass_payment_page\" value=\"true\"/>\n";
            echo "<p style=\"text-align:center;\"><br/><br/>If you are not automatically redirected to ";
            echo "the payment website within 5 seconds...<br/><br/>\n";
            echo "<input class=\"allow-leave-page\" type=\"submit\" value=\"Click Here\"></p>\n";
            echo "</form>\n";
            echo "</body></html>\n";
        }

    public function submitButton($button_url, $gateway) {
        $this->prepareSubmit();
        echo '<form method="get" name="payment_form" action="' . $this->gatewayUrl . '">';
		$this->echoArgs();
       echo '<input id="eway-payment-option-lnk" class="payment-option-lnk allow-leave-page" type="image" alt="Pay using eWay" src="' . $button_url . '" />';
        echo '</form>';
    }
	protected function echoArgs(){
		foreach($this->gatewayUrlArgs as $argname=>$argvalue){
			echo '<input type="hidden" value="' . $argvalue . '" name="'.$argname.'">';
		}
	}

    /**
     * Validate the IPN notification
     *
     * @param none
     * @return boolean
     */
    public function validateIpn() {

        if (function_exists('curl_init')) {
            //new eWay code//
            // parse the eWay URL
            $urlParsed = parse_url($this->gatewayUrl);

            // generate the post string from the _POST vars
            $req = '';

            // Run through the posted array
            foreach ($_POST as $key => $value) {
                $this->ipnData["$key"] = $value;
                $value = urlencode(stripslashes($value));
                $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value); // IPN fix
                $req .= $key . '=' . $value . '&';
            }
            $req .= 'cmd=_notify-validate';
            $url = $this->gatewayUrl;
            $ch = curl_init();    // Starts the curl handler
            curl_setopt($ch, CURLOPT_URL, $url); // Sets the eWay address for curl
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns result to a variable instead of echoing
            curl_setopt($ch, CURLOPT_TIMEOUT, 45); // Sets a time limit for curl in seconds (do not set too low)
            curl_setopt($ch, CURLOPT_POST, 1); // Set curl to send data using post
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req); // Add the request parameters to the post
            $result = curl_exec($ch); // run the curl process (and return the result to $result
            curl_close($ch);

            if (strcmp($result, "VERIFIED") == 0) { // It may seem strange but this function returns 0 if the result matches the string So you MUST check it is 0 and not just do strcmp ($result, "VERIFIED") (the if will fail as it will equate the result as false)
                // Do some checks to ensure that the payment has been sent to the correct person
                // Check and ensure currency and amount are correct
                // Check that the transaction has not been processed before
                // Ensure the payment is complete
                // Valid IPN transaction.
                $this->logResults(true);
                return true;
            } else {
                // Log an invalid request to look into
                // Invalid IPN transaction.  Check the log for details.
                $this->lastError = "IPN Validation Failed . $urlParsed[path] : $urlParsed[host]";
                $this->logResults(false);
                return false;
            }
        } else {

            //Old eWay code
            // parse the eWay URL
            $urlParsed = parse_url($this->gatewayUrl);
            // generate the post string from the _POST vars
            $postString = '';
            foreach ($_POST as $field => $value) {
                $this->ipnData["$key"] = $value;
                $value = urlencode(stripslashes($value));
                $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value); // IPN fix
                $req .= $key . '=' . $value . '&';
            }
            $postString .="cmd=_notify-validate"; // append ipn command
            // open the connection to eWay
            $fp = fsockopen($urlParsed[host], "80", $errNum, $errStr, 30);
            if (!$fp) {
                // Could not open the connection, log error if enabled
                $this->lastError = "fsockopen error no. $errNum: $errStr";
                $this->logResults(false);
                return false;
            } else {
                // Post the data back to eWay
                fputs($fp, "POST $urlParsed[path] HTTP/1.1\r\n");
                fputs($fp, "Host: $urlParsed[host]\r\n");
                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: " . strlen($postString) . "\r\n");
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $postString . "\r\n\r\n");
                // loop through the response from the server and append to variable
                while (!feof($fp)) {
                    $this->ipnResponse .= fgets($fp, 1024);
                }
                fclose($fp); // close connection
            }
            if (eregi("VERIFIED", $this->ipnResponse)) {
                // Valid IPN transaction.
                $this->logResults(true);
                return true;
            } else {
                // Invalid IPN transaction.  Check the log for details.
                $this->lastError = "IPN Validation Failed . $urlParsed[path] : $urlParsed[host]";
                $this->logResults(false);
                return false;
            }
        }
    }

}