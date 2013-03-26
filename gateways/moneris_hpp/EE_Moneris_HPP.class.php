<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
//echo '<h3>'. basename( __FILE__ ) . ' LOADED <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';
/**
* Moneris Hosted Pay Page Class
*
* @package	Event Espresso Gateways
* @category	Library
* @author		Brent Christensen
*/
class EE_Moneris_HPP extends Espresso_PaymentGateway {

	public $gateway_version = '0.1';
	public $settings;
	public $logIpn;
	
	private $_verification_url = '';

	



	/**
	 * Initialize the Moneris Hosted Pay Page gateway
	 *
	 * 	@return 	void
	 */
	public function __construct() {

		parent::__construct();
		$this->settings = get_option( 'event_espresso_moneris_hpp_settings' );
		// Some default values of the class
		$this->ipnLogFile = EVENT_ESPRESSO_UPLOAD_DIR . 'logs/moneris_hpp_error.log';
		$this->gatewayUrl = 'https://www3.moneris.com/HPPDP/index.php';
		$this->_verification_url = 'https://www3.moneris.com/HPPDP/verifyTxn.php';
		echo '<!--Event Espresso Moneris Hosted Pay Page Gateway Version ' . $this->gateway_version . '-->';

	}





	/**
	 * Enables the test mode
	 *
	 * @return void
	 */
	public function enableTestMode() {
		$this->testMode = TRUE;
		$this->gatewayUrl = 'https://esqa.moneris.com/HPPDP/index.php';
		$this->_verification_url = 'https://esqa.moneris.com/HPPDP/verifyTxn.php';
	}





	/**
	 * logErrors
	 *
	 * @param string	$errors
	 * @return 	void
	 */
	public function logErrors($errors) {
		if ($this->logIpn) {
			// Timestamp
			$text = '[' . date('m/d/Y g:i A') . '] - ';
			// Success or failure being logged?
			$text .= "Errors from IPN Validation:\n";
			$text .= $errors;
			// Write to log
			file_put_contents( $this->ipnLogFile, $text, FILE_APPEND ) or do_action('action_hook_espresso_log', __FILE__, __CLASS__ . '->' . __FUNCTION__, 'could not write to moneris_hpp log file');
		}
	}





	/**
	 * Validate the IPN notification
	 *
	 * @return boolean
	 */
	public function validateIpn() {
	
		global $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		// make sure the WP_Http clkass is loaded
	    if( ! class_exists( 'WP_Http' )) {
			include_once( ABSPATH . WPINC. '/class-http.php' );
		}
				
		$this->ipnData = $_REQUEST;
		
		$transactionKey = isset( $_POST['transactionKey'] ) && ! empty( $_POST['transactionKey'] ) ? $_POST['transactionKey'] : FALSE;
		if ( ! $transactionKey && $this->testMode ) {
			return TRUE;
		}

		if ( $transactionKey ) {
	   		 // set the data we're sending'   
			$post_args = array(
					'method' => 'POST',
					'timeout' => 30,
					'body' => array(
						   'ps_store_id' => $this->settings['moneris_hpp_ps_store_id'],
						   'hpp_key' => $this->settings['moneris_hpp_key'],
						   'transactionKey' => $transactionKey
						)
				);	
	
			$response = wp_remote_request( $this->_verification_url, $post_args );  
			
			if ( is_wp_error ( $response )) {  
		        return $response->errors['http_request_failed'][0];  
		    } 
			$verification = simplexml_load_string( $response['body'] );
			
//			printr( $this->ipnData, '$this->ipnData  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//			printr( $verification, '$verification  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

			if ( absint( $verification->response_code ) <= 50 && (float)$verification->amount == (float)$this->ipnData['charge_total'] && $verification->status == 'Valid-Approved' ) {
				return TRUE;
			} elseif ( $this->testMode ) {
				return TRUE;
			} else {
				return FALSE;
			}
			
		} else {
			return FALSE; // __( 'An error occurred. No Transaction Key was received from the payment gateway.' );
		}

	}





}