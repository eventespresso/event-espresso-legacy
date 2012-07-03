<?php

class Fdggutil {

	private $storename; // Replace with your Storenumber here
	private $sharedSecret; //Replace with your Shared Secret here
	private $timezone;
	private $dateTime;
	private $chargetotal;
	private $gatewayUrl;
	private $returnUrl;
	private $cancelUrl;
	private $attendee_id;
	private $registration_id;

	public function Fdggutil($storename, $sharedSecret) {
		$this->storename = $storename;
		$this->sharedSecret = $sharedSecret;
	}

	public function set_timezone($timezone) {
		$this->timezone = $timezone;
	}

	public function set_dateTime() {
		$this->dateTime = date("Y:m:d-H:i:s");
		global $wpdb;
		$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_date ='" . $this->dateTime . "' ";
		$sql .= "WHERE id='" . $this->attendee_id . "'";
		$wpdb->query($sql);
	}

	public function set_chargetotal($chargetotal) {
		$this->chargetotal = $chargetotal;
	}

	public function set_sandbox($sandbox) {
		if ($sandbox) {
			$this->gatewayUrl = "https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing";
		} else {
			$this->gatewayUrl = "https://connect.firstdataglobalgateway.com/IPGConnect/gateway/processing";
		}
	}

	public function set_returnUrl($returnUrl) {
		$this->returnUrl = $returnUrl;
	}

	public function set_cancelUrl($cancelUrl) {
		$this->cancelUrl = $cancelUrl;
	}

	public function set_attendee_id($attendee_id) {
		$this->attendee_id = $attendee_id;
	}

	public function set_registration_id($registration_id) {
		$this->registration_id = $registration_id;
	}

	private function createHash() {
		$str = $this->storename . $this->dateTime . $this->chargetotal . $this->sharedSecret;
		$hex_str = '';
		for ($i = 0; $i < strlen($str); $i++) {
			$hex_str.=dechex(ord($str[$i]));
		}
		return hash('sha256', $hex_str);
	}

	public function check_return_hash($payment_date) {
		$currency = '840';
		$str = $this->sharedSecret . $_REQUEST['approval_code'] . $_REQUEST['chargetotal'] . $currency . $payment_date . $this->storename;
		$hex_str = '';
		for ($i = 0; $i < strlen($str); $i++) {
			$hex_str.=dechex(ord($str[$i]));
		}
		return hash('sha256', $hex_str);
	}

	private function submitForm() {
		$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
		if ($firstdata_connect_2_settings['force_ssl_return']) {
			$home = str_replace("http://", "https://", home_url());
		} else {
			$home = home_url();
		}
		$out = '<input type="hidden" name="timezone" value="' . $this->timezone . '" />';
		$out .= '<input type="hidden" name="authenticateTransaction" value="false" />';
		$out .= '<input size="50" type="hidden" name="txntype" value="sale"/>';
		$out .= '<input size="50" type="hidden" name="txndatetime" value="' . $this->dateTime . '" />';
		$out .= '<input size="50" type="hidden" name="hash" value="' . $this->createHash() . '" />';
		$out .= '<input size="50" type="hidden" name="mode" value="payonly"/>';
		$out .= '<input size="50" type="hidden" name="storename" value="' . $this->storename . '"/>';
		$out .= '<input size="50" type="hidden" name="chargetotal" value="' . $this->chargetotal . '"/>';
		$out .= '<input size="50" type="hidden" name="subtotal" value="' . $this->chargetotal . '"/>';
		$out .= '<input size="50" type="hidden" name="trxOrigin" value="ECI"/>';
		$out .= '<input size="50" type="hidden" name="responseSuccessURL" value="' . $home . '/?page_id=' . $this->returnUrl . '&id=' . $this->attendee_id . '&registration_id=' . $this->registration_id . '&type=fdc2"/>';
		$out .= '<input size="50" type="hidden" name="responseFailURL" value="' . $home . '/?page_id=' . $this->cancelUrl . '&id=' . $this->attendee_id . '&registration_id=' . $this->registration_id . '"/>';
		return $out;
	}

	public function submitButton($button_url) {
		$out = '<li><form  method="post" name="payment_form" action="' . $this->gatewayUrl . '">';
		$out .= $this->submitForm();
		$out .= '<input class="espresso_payment_button_firstdata_connect_2" type="image" ';
		$out .= 'alt="Pay using firstdata_connect_2" src="' . $button_url . '" />';
		$out .= '</form></li>';
		return $out;
	}

	public function submitPayment() {
		$out = "<html>\n";
		$out .= "<head><title>Processing Payment...</title></head>\n";
		$out .= "<body onLoad=\"document.forms['gateway_form'].submit();\">\n";
		$out .= "<p style=\"text-align:center;\"><h2>Please wait, your order is being processed and you";
		$out .= " will be redirected to the payment website.</h2></p>\n";
		$out .= '<form method="post" name="gateway_form" action="' . $this->gatewayUrl . '">\n';
		$out .= $this->submitForm();
		$out .= "<p style=\"text-align:center;\"><br/><br/>If you are not automatically redirected to ";
		$out .= "the payment website within 5 seconds...<br/><br/>\n";
		$out .= "<input type=\"submit\" value=\"Click Here\"></p>\n";
		$out .= "</form>\n";
		$out .= "</body></html>\n";
		return $out;
	}

}