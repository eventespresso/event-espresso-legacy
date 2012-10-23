<?php
/**
 * PayChoice PHP Merchant API
 * 
 * Please note, this module REQUIRES the following:
 *   - PHP 5+
 *   - CURL (http://au.php.net/curl)
 *   - SimpleXML (http://au.php.net/simplexml)
 * 
 * @see http://www.paychoice.com.au
 * @author PayChoice Pty Ltd (support@paychoice.com.au)
 * @copyright 2012 PayChoice Pty Ltd
 */
  
 class PayChoice 
 {
 
	/**** <Please set these variables to your account details> ****/
	private $useSandbox = true;
	private $apiUserName = "";
	private $apiPassword = "";
	/**** </Please set these variables to your account details> ****/
	
	private $debug = false;
	private $availableCardTypes = array("Visa", "MasterCard", "DinersClub", "AmericanExpress");
	private $validCardTypes = array("Visa", "MasterCard", "DinersClub", "AmericanExpress", "Token");
	
	function setCredentials($userName, $password, $useSandbox)
	{
		$this->apiUserName=$userName;
		$this->apiPassword=$password;
		$this->useSandbox=$useSandbox;
	}
	
	function storeCard($cardName, $cardType, $cardNumber, $cardCsc, $expiryMonth, $expiryYear)
	{
		$cardNumber = $this->formatCardNumber($cardNumber);
		$cardCSC = str_replace(' ', '', $cardCsc);
		if (strlen($expiryYear) > 2) $expiryYear = substr($expiryYear, 2);
	
		$this->validateCardType($cardType);

		$requestXml = "<CreditCard xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\"><CardName>{$cardName}</CardName><CardNumber>{$cardNumber}</CardNumber><CreditCardType>{$cardType}</CreditCardType><Cvv>{$cardCSC}</Cvv><ExpiryMonth>{$expiryMonth}</ExpiryMonth><ExpiryYear>{$expiryYear}</ExpiryYear></CreditCard>";	
		$response = $this->sendRequest(trim($requestXml), "CreditCardStore.svc/", "put");
		
		// Parse the XML
		$xml = simplexml_load_string($response);
		// Convert the result from a SimpleXMLObject into an array
		$xml = (array)$xml;		
		
		$storedCreditCard = new PayChoiceStoreCreditCardResponse();
		
		$storedCreditCard->creditCardUuid = $xml["CreditCardGuid"];
		$storedCreditCard->creditCardToken = $xml["CreditCardToken"];
		
		return $storedCreditCard;
	}
	
	function chargeStoredCard($invoiceNumber, $creditCardGuid, $amountInCents, $currency)
	{
		$amountInCents = $amountInCents * 100;
		$amount = number_format($amountInCents,2, '.', '');

		$requestXml = "
<CreditCardStorePayment>
	<Amount>{$amount}</Amount>
	<CurrencyCode>{$currency}</CurrencyCode>
	<MerchantReferenceNumber>{$invoiceNumber}</MerchantReferenceNumber>
	<CreditCardGuid>{$creditCardGuid}</CreditCardGuid>
</CreditCardStorePayment>";

		$response = $this->sendRequest(trim($requestXml), "PaymentService.svc/ProcessPayment/StoredCreditCard");		
		
		return $this->parseChargeResponse($response);
	}
	
	function charge($invoiceNumber, $cardName, $cardType, $cardNumber, $cardCsc, $expiryMonth, $expiryYear, $amountInCents, $currency)
	{
		$amountInCents = $amountInCents * 100;
		$amount = number_format($amountInCents,2, '.', '');
		$cardNumber = $this->formatCardNumber($cardNumber);
		$cardCSC = str_replace(' ', '', $cardCsc);
		if (strlen($expiryYear) > 2) $expiryYear = substr($expiryYear, 2);
	
		$this->validateCardType($cardType);
				
		$requestXml = "
<CreditCardPayment>
	<Amount>{$amount}</Amount>
	<CurrencyCode>{$currency}</CurrencyCode>
	<MerchantReferenceNumber>{$invoiceNumber}</MerchantReferenceNumber>
	<CreditCard>
		<CardName>{$cardName}</CardName>
		<CardNumber>{$cardNumber}</CardNumber>
		<CreditCardType>{$cardType}</CreditCardType>
		<Cvv>{$cardCSC}</Cvv>
		<ExpiryMonth>{$expiryMonth}</ExpiryMonth>
		<ExpiryYear>{$expiryYear}</ExpiryYear>
	</CreditCard>
</CreditCardPayment>";

		$response = $this->sendRequest(trim($requestXml), "PaymentService.svc/ProcessPayment/CreditCard");		
		
		return $this->parseChargeResponse($response);
	}
	
	private function parseChargeResponse($response)
	{
		// Parse the XML
		$xml = simplexml_load_string($response);
		// Convert the result from a SimpleXMLObject into an array
		$xml = (array)$xml;			
	
		$chargeResponse = new PayChoiceChargeResponse();
		$chargeResponse->transactionGuid = $xml["TransactionGuid"];
		$chargeResponse->errorCode = (array)$xml["ErrorCode"];
		$chargeResponse->errorDescription = $xml["ErrorDescription"];
		$chargeResponse->approved = $xml["StatusCode"] == "0" ? true : false;
		$chargeResponse->status = $xml["Status"];
		$chargeResponse->rawResponse = $response;
		
		return $chargeResponse;
	}
	
	private function sendRequest($requestData, $serviceAddress, $method = "post")
	{	
		$environment = $this->useSandbox == true ? "sandbox" : "secure";
		$endPoint = "https://{$environment}.paychoice.com.au/services/v2/rest/{$serviceAddress}";
		$assignedMethod = "!!!!unassigned HTTP method!!!!";
		
		// Initialise CURL and set base options
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		
		if ($method == "post")
		{
			$assignedMethod = "post";
			curl_setopt($curl, CURLOPT_POST, true);
		}
		else if ($method == "put")
		{
			$assignedMethod = "put";
			curl_setopt($curl, CURLOPT_PUT, true);
		}
		else if ($method == "delete")
		{
			$assignedMethod = "delete";
			curl_setopt($curl, CURLOPT_DELETE, true);
		}
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml; charset=utf-8'));
		
		// Setup CURL params for this request
		curl_setopt($curl, CURLOPT_URL, $endPoint);
		curl_setopt($curl, CURLOPT_USERPWD, $this->apiUserName.':'.$this->apiPassword);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData);
		
		// Run CURL
		$response = curl_exec($curl);
		$error = curl_error($curl);
		$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);		
		
		if (isset($this->debug) && $this->debug == true)
		{	
			echo "<div style=\"border:2px solid black; clear: both;\">";
			echo "<code><pre>{$assignedMethod} {$endPoint}<br />" . htmlentities($requestData) . "<pre></code><hr />";
			echo "<code><pre>{$responseCode} - {$error}<br />" . htmlentities($response) . "<pre></code>";
			echo "</div>";
		}
		
		// Check for CURL errors
		if (isset($error) && strlen($error))
		{
			throw new PayChoiceException("Could not successfully communicate with payment processor. Error: {$error}.");
		}
		else if (isset($responseCode) && strlen($responseCode) && $responseCode != '200')
		{
			throw new PayChoiceException("Could not successfully communicate with payment processor. HTTP response code {$responseCode}.");
		}
		
		return $response;
	}
	
	private function validateCardType($cardType)
	{
		if (!in_array($cardType, $this->validCardTypes))
		{
			throw new PayChoiceException("Invalid card type \"{$cardType}\", please use one of the following values: " . implode(", ", $this->validCardTypes));
		}
	}	
	
	private function formatCardNumber($cardNumber)
	{
		return str_replace(array('-',' '), '', $cardNumber);
	}
 }
 
 class PayChoiceChargeResponse
 {
	public $transactionGuid;
	public $errorCode;
	public $errorDescription;
	public $approved;
	public $status;
	public $rawResponse;
 }
 
 class PayChoiceStoreCreditCardResponse
 {
	public $creditCardUuid;
	public $creditCardToken; 
 }
 
 class PayChoiceException extends Exception {}