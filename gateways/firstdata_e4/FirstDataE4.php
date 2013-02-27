<?php

/**
 * E4 Class
 *
 * Author 		Seth Shoultes
 * @package		Event Espresso E4 Gateway
 * @category	Library
 */


class Espresso_E4 extends Espresso_PaymentGateway {

	public $gateway_version = '1.0';
	/**
	 * Login ID of authorize.net account
	 *
	 * @var string
	 */
	public $login;

	/**
	 * Secret key from authorize.net account
	 *
	 * @var string
	 */
	public $secret;
	/*
	 * Initialize the E4 gateway
	 *
	 * @param none
	 * @return void
	 */

	public function __construct() {
		parent::__construct();
		// Some default values of the class
		$this->gatewayUrl = 'https://checkout.globalgatewaye4.firstdata.com/payment';
		$this->ipnLogFile = 'authorize.ipn_results.log';
		// Populate $fields array with a few default
	}

	/**
	 * Enables the test mode
	 *
	 * @param none
	 * @return none
	 */
	public function enableTestMode() {
		$this->testMode = TRUE;
		$this->addField('x_Test_Request', 'TRUE'); //Used for non-dev testing
	}

	public function useTestServer() {
		$this->testMode = TRUE;
		$this->gatewayUrl = 'https://demo.globalgatewaye4.firstdata.com/payment';
	}

	/**
	 * Set login and secret key
	 *
	 * @param string user login
	 * @param string secret key
	 * @return void
	 */
	public function setUserInfo($login, $key) {
		$this->login = $login;
		$this->secret = $key;
	}

	/**
	 * Prepare a few payment information
	 *
	 * @param none
	 * @return void
	 */
	public function prepareSubmit() {
		$this->addField('x_login', $this->login);
		$this->addField('x_fp_timestamp', time());
		$data = $this->fields['x_login'] . '^' .
						$this->fields['x_fp_sequence'] . '^' .
						$this->fields['x_fp_timestamp'] . '^' .
						$this->fields['x_amount'] . '^';
		$this->addField('x_fp_hash', $this->hmac($this->secret, $data));
	}

	/**
	 * Validate the IPN notification
	 *
	 * @param none
	 * @return boolean
	 */
	public function validateIpn() {
		foreach ($_POST as $field => $value) {
			$this->ipnData["$field"] = $value;
		}
		$invoice = intval($this->ipnData['x_invoice_num']);
		$pnref = $this->ipnData['x_trans_id'];
		$amount = doubleval($this->ipnData['x_amount']);
		$result = intval($this->ipnData['x_response_code']);
		$respmsg = $this->ipnData['x_response_reason_text'];
		$md5source = $this->secret . $this->login . $this->ipnData['x_trans_id'] . $this->ipnData['x_amount'];
		$md5 = md5($md5source);
		if ($result == '1') {
			// Valid IPN transaction.
			$this->logResults(true);
			return true;
		} else if ($result != '1') {
			$this->lastError = $respmsg;
			$this->logResults(false);
			return false;
		} else if (strtoupper($md5) != $this->ipnData['x_MD5_Hash']) {
			$this->lastError = 'MD5 mismatch';
			$this->logResults(false);
			return false;
		}
	}

	/**
	 * RFC 2104 HMAC implementation for php.
	 *
	 * @author Lance Rushing
	 * @param string key
	 * @param string date
	 * @return string encoded hash
	 */
	private function hmac($key, $data) {
		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*", md5($key));
		}
		$key = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;
		return md5($k_opad . pack("H*", md5($k_ipad . $data)));
	}

}