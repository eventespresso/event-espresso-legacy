<?php

class Espresso_Realauth {

	private $merchant_id;
	private $order_id;
	private $account;
	private $amount;
	private $currency;
	private $timestamp;
	private $auto_settle_flag;
	private $gateway_URL;
	private $attendee_id;
	private $shared_secret;
	private $reg_id;

	public function Espresso_Realauth($merchant_id, $shared_secret) {
		$this->merchant_id = $merchant_id;
		$this->shared_secret = $shared_secret;
		$this->gateway_Url = "https://epage.payandshop.com/epage.cgi";
	}

	public function set_timestamp() {
				$this->timestamp = date("YmdHis");
				global $wpdb;
				$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_date ='" . $this->timestamp . "' ";
				$sql .= "WHERE id='" . $this->attendee_id . "'";
				$wpdb->query($sql);
	}

	public function set_amount($amount) {
				$this->amount = $amount;
	}

	public function set_auto_settle_flag($auto_settle_flag) {
		if ($auto_settle_flag=='Y') {
			$this->auto_settle_flag = 1;
		} else {
			$this->auto_settle_flag = 0;
		}
	}

	public function set_currency($currency) {
		$this->currency = $currency;
	}

	public function set_sandbox($use_sandbox) {
		$this->use_sandbox = $use_sandbox;
	}

	public function set_attendee_id($attendee_id) {
		$this->attendee_id = $attendee_id;
		$this->order_id = $attendee_id;
	}

	public function set_registration_id($registration_id) {
		$this->reg_id = $registration_id;
	}

	private function createHash() {
		$str = $this->timestamp . '.' . $this->merchant_id . '.' . $this->order_id . '.' . $this->amount . '.' . $this->currency;
		$str = sha1($str)  . '.' . $this->shared_secret;
		return sha1($str);
	}

	public function check_return_hash($payment_date) {
		$currency = '840';
		$str = $this->shared_secret . $_REQUEST['approval_code'] . $_REQUEST['chargetotal'] . $currency . $payment_date . $this->storename;
		$hex_str = '';
		for ($i = 0; $i < strlen($str); $i++) {
			$hex_str.=dechex(ord($str[$i]));
		}
		return hash('md5', $hex_str);
	}

	private function submitForm() {
		$out = '<input type="hidden" name="MERCHANT_ID" value="' . $this->merchant_id . '" />';
		$out .= '<input type="hidden" name="ORDER_ID" value="' . $this->order_id . '" />';
		$out .= '<input type="hidden" name="REG_ID" value="' . $this->reg_id . '" />';
		$out .= '<input size="50" type="hidden" name="AMOUNT" value="' . $this->amount .'" />';
		$out .= '<input size="50" type="hidden" name="CURRENCY" value="' . $this->currency . '" />';
		$out .= '<input size="50" type="hidden" name="TIMESTAMP" value="' . $this->timestamp . '"/>';
		$out .= '<input size="50" type="hidden" name="SHA1HASH" value="' . $this->createHash() . '"/>';
		$out .= '<input size="50" type="hidden" name="AUTO_SETTLE_FLAG" value="' . $this->auto_settle_flag . '"/>';
		$out .= '<input size="50" type="hidden" name="type" value="realauth"/>';
		if ($this->use_sandbox) {
			$out .= '<input type="hidden" name="ACCOUNT" value="internettest" />';
		}
		return $out;
	}

	public function submitButton($button_url) {
		$out = '
<div id="realauth-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
	<img class="off-site-payment-gateway-img" width="16" height="16" src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/icons/external-link.png" alt="click to visit this payment gateway">';
		$out .= '<form  method="post" name="payment_form" action="' . $this->gateway_Url . '">';
		$out .= $this->submitForm();
		$out .= '<input id="realauth-payment-option-lnk" class="payment-option-lnk allow-leave-page" type="image" ';
		$out .= 'alt="Pay using RealAuth" src="' . $button_url . '" />';
    	$out .= '</form></div>';
		return $out;
	}

	public function submitPayment() {
		$out = "<html>\n";
	    $out .= "<head><title>Processing Payment...</title></head>\n";
	    $out .= "<body onLoad=\"document.forms['gateway_form'].submit();\">\n";
	    $out .= "<p style=\"text-align:center;\"><h2>Please wait, your order is being processed and you";
	    $out .= " will be redirected to the payment website.</h2></p>\n";
	    $out .= '<form method="post" name="gateway_form" action="' . $this->gateway_Url . '">\n';
	    $out .= $this->submitForm();
		$out .= "<input type=\"hidden\" id=\"bypass_payment_page\" name=\"bypass_payment_page\" value=\"true\"/>\n";
		$out .= "<p style=\"text-align:center;\"><br/><br/>If you are not automatically redirected to ";
	    $out .= "the payment website within 5 seconds...<br/><br/>\n";
	    $out .= "<input type=\"submit\" value=\"Click Here\"></p>\n";
	    $out .= "</form>\n";
	    $out .= "</body></html>\n";
		return $out;
	}
}