<?php
if(session_id()=="")
	session_start();
/*
 * Class for sending requests to Eway's Rapid 3, according to documentation at http://www.eway.com.au/docs/api-documentation/rapid3-0documentation.pdf?sfvrsn=2.
 * By default, sends requests in JSON.
 * This class does not, of course, take care of creating the payment form which gets directly submitted to Eway, but takes care of
 * getting an access code, storing it, providing it upon request (eg: in the POST form which is displayed on your website
 * and then sent to Eway), and retrieving payment details from Eway after payment has been processed.
 * 
 */

/**
 * 
 * @author mnelson4
 */
class Espresso_EWayRapid3Client{
	private $useSandbox=false;
	private $apiKey;
	private $apiPassword;
	
	const BASE_URL='https://api.ewaypayments.com/';
	const BASE_SANDBOX_URL='https://api.sandbox.ewaypayments.com/';
	
	
	/**
	 * gets Authenticatiuon string which is to be sent in api requests in the Authorization http header, 
	 * as documented in http://www.eway.com.au/docs/api-documentation/rapid3-0documentation.pdf?sfvrsn=2 
	 * under the section 'Authorization'.
	 * Example usage: 
	 *		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Basic '.$this->getAuthorizationString(),
				'OtherHeader1: whateveer' ));
	 * @return string 
	 */
	private function getAuthorizationString(){
		$unenecoded=$this->apiKey.":".$this->apiPassword;
		$encoded=base64_encode($unenecoded);
		return $encoded;
	}
	
	/**
	 * get URL endpoint for a particular method in an api call
	 * @param string $path, like 'CreateAccessCode' or 'GetAcccessCodeResult'. 
	 * (see documentation at http://www.eway.com.au/docs/api-documentation/rapid3-0documentation.pdf?sfvrsn=2,
	 * sections on Implementation, "Step 1: Create an Access Code" and "Step 3: Request the Results")
	 * @return string 
	 */
	private function getEndpoint($path='CreateAccessCode'){
		if($this->useSandbox){
			return self::BASE_SANDBOX_URL.$path.'.json';
		}
		else{
			return self::BASE_URL.$path.'.json';
		}
	}
	function __construct($initVariables=array()){
		if(empty($initVariables['apiKey']) || empty($initVariables['apiPassword']))
			throw  new Exception(__("Missing apiKey or apiPassword in initialization of Eway_Rapid_3.0_Client","event_espresso"));
		$this->apiKey=$initVariables['apiKey'];
		$this->apiPassword=$initVariables['apiPassword'];
		if(isset($initVariables['useSandbox']) && $initVariables['useSandbox'])
			$this->useSandbox=$initVariables['useSandbox'];
	}
	/**
	 * for getting an accessCode and PostSubmitURL, as documented in http://www.eway.com.au/docs/api-documentation/rapid3-0documentation.pdf?sfvrsn=2
	 * the in the section Implementation->Step 1: Create an Access Code
	 * @param array $customerDetailsArray
	 * @param string $redirectUrl
	 * @param string $method 
	 */
	function createAccessCode($customerDetailsArray,$redirectUrl,$method='ProcessPayment'){
		$endpoint=$this->getEndpoint('CreateAccessCode');
		$customerDetailsArray['RedirectUrl']=$redirectUrl;
		$customerDetailsArray['Method']=$method;
		$customerDetailsArray['CustomerIP']=array_key_exists('REMOTE_ADDR',$_REQUEST)?$_REQUEST['REMOTE_ADDR']:'';
		$result=$this->curlRequest($endpoint, $customerDetailsArray);
		//echo "result";
		//		var_dump($result);
		return $result;//array($result->AccessCode,$result->FormActionURL);
	}
	function getAccessCodeResult($accessCode=null){
		if(empty($accessCode)){
			$accessCode=$_GET['AccessCode'];
			if(empty($accessCode))
				throw new EwayRapid3Exception(__("If you call getAccesCodeResult from a page whcih deson't have 'AccessCode' as a \$_GET parameter, you must explicitly provide the accessCode as a parameter"));
		}
		$endpoint=$this->getEndpoint('GetAccessCodeResult');
		$postBodyArray['AccessCode']=$accessCode;
		$result=$this->curlRequest($endpoint, $postBodyArray);
		return $result;
	}
	
	protected function curlRequest($endpoint,$unencodedPostArray){
		$jsonDetails=json_encode($unencodedPostArray);
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$endpoint);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); # Set to 1 to verify Host's SSL Cert
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_HTTPHEADER,
				array(
					"Authorization: Basic ".$this->getAuthorizationString(),
					"Content-Type: text/json",
					"Content-Length:".strlen($jsonDetails)
					));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonDetails);	
		return json_decode(curl_exec($ch));
	}
	
}

class EwayRapid3Exception extends Exception{
	
}
class EwayRapid3PaymentProblem{
	
}