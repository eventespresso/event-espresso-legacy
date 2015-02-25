<?php

/**
 * Authorize.net Class
 *
 * Author 		Seth Shoultes
 * @package		Event Espresso Authorize.net SIM Gateway
 * @category	Library
 */
class Espresso_Authorize extends Espresso_PaymentGateway {

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
		$this->ipnLogFile = EVENT_ESPRESSO_UPLOAD_DIR . 'logs/authorize.ipn_results.log';
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
		if (phpversion() >= '5.1.2') {
			$fingerprint = hash_hmac("md5", $data, $this->secret);
		} else {
			$fingerprint = bin2hex(mhash(MHASH_MD5, $data, $this->secret));
		}
		$this->addField('x_fp_hash', $fingerprint);
	}

	/**
	 * Add a line item.
	 *
	 * @param string $item_id
	 * @param string $item_name
	 * @param string $item_description
	 * @param string $item_quantity
	 * @param string $item_unit_price
	 * @param string $item_taxable
	 */
	public function addLineItem($item_id, $item_name, $item_description, $item_quantity, $item_unit_price, $item_taxable) {
		$line_item = "";
		$delimiter = "";
		foreach (func_get_args() as $key => $value) {
			$line_item .= $delimiter . $value;
			$delimiter = "<|>";
		}
		$this->_additional_line_items[] = $line_item;
	}

	/**
	 * Submit Payment Button
	 *
	 * Generates a form with hidden elements from the fields array
	 * and displays the payment button that goes to the payment form.
	 *
	 * @param string value of button url
	 * @param string type of gateway
	 * @return void
	 */
	public function submitButton($button_url, $gateway) {
		$this->prepareSubmit();
		global $gateway_name;
		echo ' <div id="' . $gateway . '-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
			<img class="off-site-payment-gateway-img" width="16" height="16" src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/icons/external-link.png" alt="click to visit this payment gateway">
			<form  method="post" name="payment_form" action="' . $this->gatewayUrl . '">';
		foreach ($this->fields as $name => $value) {
			echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
		}
		foreach ($this->_additional_line_items as $value) {
			echo "<input type=\"hidden\" name=\"x_line_item\" value=\"$value\"/>\n";
		}
		echo '<input id="' . $gateway . '-payment-option-lnk" class="payment-option-lnk allow-leave-page" type="image" alt="Pay using ' . $gateway_name . '" src="' . $button_url . '" />';
            echo '
		 	</form>
		</div>';
	}

	/**
	 * Submit Payment Request (redirect)
	 *
	 * Generates a form with hidden elements from the fields array
	 * and submits it to the payment gateway URL. The user is presented
	 * a redirecting message along with a button to click.
	 *
	 * @param string value of buttn text
	 * @return void
	 */
	public function submitPayment() {
		$this->prepareSubmit();
		echo "<html>\n";
		echo "<head><title>Processing Payment...</title></head>\n";
		echo "<body onLoad=\"document.forms['gateway_form'].submit();\">\n";
		echo "<p style=\"text-align:center;\"><h2>Please wait, your order is being processed and you";
		echo " will be redirected to the payment website.</h2></p>\n";
		echo "<form method=\"POST\" name=\"gateway_form\" ";
		echo "action=\"" . $this->gatewayUrl . "\">\n";
		foreach ($this->fields as $name => $value) {
			echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
			//echo 'Field name: ' . $name . ' Field value : ' . $value . '<br>';
		}
		foreach ($this->_additional_line_items as $value) {
			echo "<input type=\"hidden\" name=\"x_line_item\" value=\"$value\"/>\n";
		}
           echo "<input type=\"hidden\" id=\"bypass_payment_page\" name=\"bypass_payment_page\" value=\"true\"/>\n";
		echo "<p style=\"text-align:center;\"><br/><br/>If you are not automatically redirected to ";
		echo "the payment website within 5 seconds...<br/><br/>\n";
		echo "<input type=\"submit\" value=\"Click Here\"></p>\n";
		echo "</form>\n";
		echo "</body></html>\n";
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
