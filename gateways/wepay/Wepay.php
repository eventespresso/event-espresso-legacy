<?php

/**
 * WePay Class
 *
 * Author 		Seth Shoultes
 * @package		Event Espresso WePay Gateway
 * @category	Library
 */
class Espresso_Wepay extends Espresso_PaymentGateway {

	/**
	 * Version number - sent in user agent string
	 */
	public static $version = '0.0.9';

	/**
	 * Scope fields
	 * Passed into Wepay::getAuthorizationUri as array
	 */
	const SCOPE_MANAGE_ACCOUNTS = 'manage_accounts';	// Open and interact with accounts
	const SCOPE_VIEW_BALANCE = 'view_balance';	 // View account balances
	const SCOPE_COLLECT_PAYMENTS = 'collect_payments'; // Create and interact with checkouts
	const SCOPE_REFUND_PAYMENTS = 'refund_payments';	// Refund checkouts
	const SCOPE_VIEW_USER = 'view_user';		 // Get details about authenticated user

	/**
	 * Application's client ID
	 */
	private static $client_id;

	/**
	 * Application's client secret
	 */
	private static $client_secret;

	/**
	 * Pass Wepay::$all_scopes into getAuthorizationUri if your application desires full access
	 */
	public static $all_scopes = array(
			self::SCOPE_MANAGE_ACCOUNTS,
			self::SCOPE_VIEW_BALANCE,
			self::SCOPE_COLLECT_PAYMENTS,
			self::SCOPE_REFUND_PAYMENTS,
			self::SCOPE_VIEW_USER,
	);
	private static $production = null;

	/**
	 * cURL handle
	 */
	private $ch;

	/**
	 * Authenticated user's access token
	 */
	private $token;

	/**
	 * Generate URI used during oAuth authorization
	 * Redirect your user to this URI where they can grant your application
	 * permission to make API calls
	 * @link https://www.wepay.com/developer/reference/oauth2
	 * @param array  $scope             List of scope fields for which your appliation wants access
	 * @param string $redirect_uri      Where user goes after logging in at WePay (domain must match application settings)
	 * @param array  $options optional  user_name,user_email which will be pre-fileld on login form, state to be returned in querystring of redirect_uri
	 * @return string URI to which you must redirect your user to grant access to your application
	 */
	public static function getAuthorizationUri(array $scope, $redirect_uri, array $options = array()) {
		// This does not use Wepay::getDomain() because the user authentication
		// domain is different than the API call domain
		if (self::$production === null) {
			throw new RuntimeException('You must initialize the WePay SDK with Wepay::useStaging() or Wepay::useProduction()');
		}
		$domain = self::$production ? 'https://www.wepay.com' : 'https://stage.wepay.com';
		$uri = $domain . '/v2/oauth2/authorize?';
		$uri .= http_build_query(array(
				'client_id' => self::$client_id,
				'redirect_uri' => $redirect_uri,
				'scope' => implode(',', $scope),
				'state' => empty($options['state']) ? '' : $options['state'],
				'user_name' => empty($options['user_name']) ? '' : $options['user_name'],
				'user_email' => empty($options['user_email']) ? '' : $options['user_email']
						));
		return $uri;
	}

	private static function getDomain() {
		if (self::$production === true) {
			return 'https://wepayapi.com/v2/';
		} elseif (self::$production === false) {
			return 'https://stage.wepayapi.com/v2/';
		} else {
			throw new RuntimeException('You must initialize the WePay SDK with Wepay::useStaging() or Wepay::useProduction()');
		}
	}

	/**
	 * Exchange a temporary access code for a (semi-)permanent access token
	 * @param string $code          'code' field from query string passed to your redirect_uri page
	 * @param string $redirect_uri  Where user went after logging in at WePay (must match value from getAuthorizationUri)
	 * @return StdClass|false
	 *  user_id
	 *  access_token
	 *  token_type
	 */
	public static function getToken($code, $redirect_uri) {
		$uri = self::getDomain() . 'oauth2/token';
		$params = (array(
				'client_id' => self::$client_id,
				'client_secret' => self::$client_secret,
				'redirect_uri' => $redirect_uri,
				'code' => $code,
				'state' => '', // do not hardcode
						));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, 'Wepay v2 PHP SDK v' . self::$version);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5-second timeout, adjust to taste
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$raw = curl_exec($ch);
		if ($errno = curl_errno($ch)) {
			// Set up special handling for request timeouts
			if ($errno == CURLE_OPERATION_TIMEOUTED) {
				throw new WepayServerException;
			}
			throw new Exception('cURL error while making API call to WePay: ' . curl_error($ch), $errno);
		}
		$result = json_decode($raw);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode >= 400) {
			if ($httpCode >= 500) {
				throw new WepayServerException($result->error_description);
			}
			switch ($result->error) {
				case 'invalid_request':
					throw new WepayRequestException($result->error_description, $httpCode);
				case 'access_denied':
				default:
					throw new WepayPermissionException($result->error_description, $httpCode);
			}
		}
		return $result;
	}

	/**
	 * Configure SDK to run against WePay's production servers
	 * @param string $client_id      Your application's client id
	 * @param string $client_secret  Your application's client secret
	 * @return void
	 * @throws RuntimeException
	 */
	public static function useProduction($client_id, $client_secret) {
		if (self::$production !== null) {
			throw new RuntimeException('API mode has already been set.');
		}
		self::$production = true;
		self::$client_id = $client_id;
		self::$client_secret = $client_secret;
	}

	/**
	 * Configure SDK to run against WePay's staging servers
	 * @param string $client_id      Your application's client id
	 * @param string $client_secret  Your application's client secret
	 * @return void
	 * @throws RuntimeException
	 */
	public static function useStaging($client_id, $client_secret) {
		if (self::$production !== null) {
			throw new RuntimeException('API mode has already been set.');
		}
		self::$production = false;
		self::$client_id = $client_id;
		self::$client_secret = $client_secret;
	}

	/**
	 * Create a new API session
	 * @param string $token - access_token returned from Wepay::getToken
	 */
	public function __construct($token) {
		$this->token = $token;
	}

	/**
	 * Clean up cURL handle
	 */
	public function __destruct() {
		if ($this->ch) {
			curl_close($this->ch);
		}
	}

	/**
	 * Make API calls against authenticated user
	 * @param string $endpoint - API call to make (ex. 'user', 'account/find')
	 * @param array  $values   - Associative array of values to send in API call
	 * @return StdClass
	 * @throws WepayException on failure
	 * @throws Exception on catastrophic failure (non-Wepay-specific cURL errors)
	 */
	public function request($endpoint, array $values = array()) {
		if (!$this->ch) {
			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_USERAGENT, 'Wepay v2 PHP SDK v' . self::$version);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $this->token", "Content-Type: application/json"));
			curl_setopt($this->ch, CURLOPT_TIMEOUT, 5); // 5-second timeout, adjust to taste
			curl_setopt($this->ch, CURLOPT_POST, !empty($values)); // WePay's API is not strictly RESTful, so all requests are sent as POST unless there are no request values
		}
		$uri = self::getDomain() . $endpoint;
		curl_setopt($this->ch, CURLOPT_URL, $uri);
		if (!empty($values)) {
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($values));
		}
		$raw = curl_exec($this->ch);
		if ($errno = curl_errno($this->ch)) {
			// Set up special handling for request timeouts
			if ($errno == CURLE_OPERATION_TIMEOUTED) {
				throw new WepayServerException;
			}
			throw new Exception('cURL error while making API call to WePay: ' . curl_error($this->ch), $errno);
		}
		$result = json_decode($raw);
		$httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		if ($httpCode >= 400) {
			if ($httpCode >= 500) {
				throw new WepayServerException($result->error_description);
			}
			switch ($result->error) {
				case 'invalid_request':
					//throw new WepayRequestException($result->error_description, $httpCode);
				case 'access_denied':
				default:
					//throw new WepayPermissionException($result->error_description, $httpCode);
			}
		}
		return $result;
	}

	public function submitPayment( $uri = FALSE ) {
		echo "<html>\n";
		echo "<head><title>Processing Payment...</title></head>\n";
		echo "<body onLoad=\"document.forms['payment_form'].submit();\">\n";
		echo "<p style=\"text-align:center;\"><h2>Please wait, your order is being processed and you";
		echo " will be redirected to the payment website.</h2></p>\n";
		echo '<form method="get" name="payment_form" action="' . $uri . '">';
		echo "<input type=\"hidden\" id=\"bypass_payment_page\" name=\"bypass_payment_page\" value=\"true\"/>\n";
		echo "<p style=\"text-align:center;\"><br/><br/>If you are not automatically redirected to ";
		echo "the payment website within 5 seconds...<br/><br/>\n";
		echo "<input type=\"submit\" value=\"Click Here\"></p>\n";
		echo "</form>\n";
		echo "</body></html>\n";
	}

	public function submitButton($uri, $button_url) {
		echo '<div id="wepay-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
	<img class="off-site-payment-gateway-img" width="16" height="16" src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/icons/external-link.png" alt="click to visit this payment gateway">';
		echo '<form method="get" name="payment_form" action="' . $uri . '">';
		echo '<input class="payment-option-lnk allow-leave-page" id="wepay_submit" type="image" alt="Pay using WePay" src="' . $button_url . '" />';
		echo '</form></div>';
	}

	public function dump_fields( $fields = FALSE ) {
		echo '<table style="background: #000;" width="95%" border="1" cellpadding="2" cellspacing="0">';
		echo '<caption style="background: #000; color: #fff; font-weight: bold;">WePay debug output</caption>';
		echo '<thead>';
		echo '<tr>';
		echo '<th style="color: #fff;">' . __('Field Name', 'event_espresso') . '</th>';
		echo '<th style="color: #fff;">' . __('Value', 'event_espresso') . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		ksort($fields);
		foreach ($fields as $key => $value) {

			echo '<tr>';
			echo '<td style="color: #fff; border-right: 1px solid #ccc;">' . $key . '</td>';
			echo '<td style="color: #fff;">' . urldecode($value) . '&nbsp;</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Validate the IPN notification
	 *
	 * @param none
	 * @return boolean
	 */
	public function validateIpn() {

		if (function_exists('curl_init')) {
			//new WePay code//
			// parse the WePay URL
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
			$ch = curl_init(); // Starts the curl handler
			curl_setopt($ch, CURLOPT_URL, $url); // Sets the WePay address for curl
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

			//Old Wepay code
			// parse the Wepay URL
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
			// open the connection to Wepay
			$fp = fsockopen($urlParsed[host], "80", $errNum, $errStr, 30);
			if (!$fp) {
				// Could not open the connection, log error if enabled
				$this->lastError = "fsockopen error no. $errNum: $errStr";
				$this->logResults(false);
				return false;
			} else {
				// Post the data back to Wepay
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

	public function enableTestMode() {

	}

}

/**
 * Different problems will have different exception types so you can
 * catch and handle them differently.
 *
 * WepayServerException indicates some sort of 500-level error code and
 * was unavoidable from your perspective. You may need to re-run the
 * call, or check whether it was received (use a "find" call with your
 * reference_id and make a decision based on the response)
 *
 * WepayRequestException indicates a development error - invalid endpoint,
 * erroneous parameter, etc.
 *
 * WepayPermissionException indicates your authorization token has expired,
 * was revoked, or is lacking in scope for the call you made
 */
class WepayException extends Exception {

}

class WepayRequestException extends WepayException {

}

class WepayPermissionException extends WepayException {

}

class WepayServerException extends WepayException {

}