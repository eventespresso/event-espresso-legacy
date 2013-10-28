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

	public $gateway_version = '0.3';
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
		$this->ipnLogFile = EVENT_ESPRESSO_UPLOAD_DIR . 'logs/moneris_hpp.log';
		if ( is_writable( EVENT_ESPRESSO_UPLOAD_DIR . 'logs' ) && ! file_exists( $this->ipnLogFile )) {
			touch( $this->ipnLogFile );
		}
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
	 * moneris_hpp_log
	 *
	 * @param string	$text	string of text to append to log file
	 * @param string	$error	whether log entry is an error
	 * @return 	void
	 */
	public function moneris_hpp_log( $text, $error = FALSE ) {
		if ($this->logIpn) {
			// Timestamp
			$log_entry = "\n" . '[' . date('m/d/Y g:i A') . ']' . "\n";
			// Success or failure being logged?
			$log_entry .= $error ? "IPN Validation Errors: " . $text : $text;
			// Write to log
			file_put_contents( $this->ipnLogFile, $log_entry, FILE_APPEND ) or do_action('action_hook_espresso_log', __FILE__, __CLASS__ . '->' . __FUNCTION__, 'could not write to moneris_hpp log file');
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
		// make sure the WP_Http class is loaded
	    if( ! class_exists( 'WP_Http' )) {
			include_once( ABSPATH . WPINC. '/class-http.php' );
		}
				
		$this->ipnData = $_REQUEST;
		
		$transactionKey = isset( $this->ipnData['transactionKey'] ) && ! empty( $this->ipnData['transactionKey'] ) ? sanitize_text_field( $this->ipnData['transactionKey'] ) : FALSE;
		if ( ! $transactionKey && $this->testMode ) {
			return TRUE;
		}

		if ( $transactionKey ) {
	   		 // set the data we're sending'   
			$post_args = array(
					'method' => 'POST',
					'timeout' => 30,
					'headers' => array(
						   'Referer' =>  get_permalink( $org_options['notify_url'] )
						),
					'body' => array(
						   'ps_store_id' => $this->settings['moneris_hpp_ps_store_id'],
						   'hpp_key' => $this->settings['moneris_hpp_key'],
						   'transactionKey' => $transactionKey
						)
				);	
	
			$response = wp_remote_request( $this->_verification_url, $post_args );  
			
			if ( is_wp_error ( $response )) {  
		        $error = $response->errors['http_request_failed'][0]; 
				$this->moneris_hpp_log( $error, TRUE );
				return $error;
		    } 
			$verification = simplexml_load_string( $response['body'] );

			if ( $this->testMode && WP_DEBUG && current_user_can( 'update_core' )) {
				// super user can see debug info 
				printr( $this->ipnData, '$this->ipnData  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				printr( $verification, '$verification  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
			}

			if ( absint( $verification->response_code ) <= 50 && (float)$verification->amount == (float)$this->ipnData['charge_total'] && $verification->status == 'Valid-Approved' ) {
				return TRUE;
			} else {
				$log_entry = 'Transaction failed verification, & ';
				$log_entry .= 'order_id = ' . $verification->order_id . ', & ';
				$log_entry .= 'response_code = ' . $verification->response_code . ', & ';
				$log_entry .= 'amount = ' . $verification->amount . ', & ';
				$log_entry .= 'txn_num = ' . $verification->txn_num . ', & ';
				$log_entry .= 'transactionKey = ' . $verification->transactionKey . ', & ';
				$log_entry .= 'status = ' . $verification->status;
				$log_entry .= "\nipnData\n";
				foreach ( $this->ipnData as $key => $value ) {
					$log_entry .= $key . ' = ' . $value . ', & ';
				}
				$this->moneris_hpp_log( $log_entry, TRUE );
				return FALSE;
			}
			
		} else {
			$this->moneris_hpp_log( 'No transactionKey or an Invalid transactionKey was returned.', TRUE );
			return FALSE; // __( 'An error occurred. No Transaction Key was received from the payment gateway.' );
		}

	}





}