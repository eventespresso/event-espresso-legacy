<?php

/**
 * Authorize.net Class
 *
 * Author 		Seth Shoultes
 * @package		Event Espresso Authorize.net SIM Gateway
 * @category	Library
 */
class EE_Authorize extends PaymentGateway {

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
	 * Initialize the Authorize.net gateway
	 *
	 * @param none
	 * @return void
	 */
	
	private $use_md5 = false;
	private $md5_value = '';

	public function __construct() {
		parent::__construct();
		// Some default values of the class
		$this->gatewayUrl = 'https://secure.authorize.net/gateway/transact.dll';
		$this->ipnLogFile = 'authorize.ipn_results.log';
		// Populate $fields array with a few default
		$this->addField('x_Version', '3.0');
		$this->addField('x_Show_Form', 'PAYMENT_FORM');
		$this->addField('x_Relay_Response', 'TRUE');
	}
	
	public function enableUseMD5() {
		$this->use_md5 = true;
	}

	public function setMD5Value($md5_value) {
		$this->md5_value = $md5_value;
	}
	/**
	 * Enables the test mode
	 *
	 * @param none
	 * @return none
	 */
	public function enableTestMode() {
		$this->testMode = TRUE;
		$this->addField('x_Test_Request', 'TRUE');
	}

	public function useTestServer() {
		$this->testMode = TRUE;
		$this->gatewayUrl = 'https://test.authorize.net/gateway/transact.dll';
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
		$this->addField('x_Login', $this->login);
		$this->addField('x_fp_sequence', $this->fields['x_Invoice_num']);
		$this->addField('x_fp_timestamp', time());
		$data = $this->fields['x_Login'] . '^' .
						$this->fields['x_Invoice_num'] . '^' .
						$this->fields['x_fp_timestamp'] . '^' .
						$this->fields['x_Amount'] . '^';
		if (phpversion() >='5.1.2') {
			$fingerprint = hash_hmac("md5", $data, $this->secret);
		} else {
			$fingerprint = bin2hex(mhash(MHASH_MD5, $data, $this->secret));
		}
		$this->addField('x_fp_hash', $fingerprint);
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
		if ($this->use_md5) {
			$md5source = $this->md5_value . $this->login . $this->ipnData['x_trans_id'] . $this->ipnData['x_amount'];
			$md5 = md5($md5source);
			if (strtoupper($md5) != $this->ipnData['x_MD5_Hash']) {
				return false;
			}
		}
		return true;
	}
}
