<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
//echo '<h3>'. basename( __FILE__ ) . ' LOADED <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';

/**
 *
 * Payment Gateway Class
 * This class builds the payment buttons/forms
 *
 * */
if (!class_exists('Espresso_PaymentGateway')) {

    abstract class Espresso_PaymentGateway {

        /**
         * Holds the last error encountered
         *
         * @var string
         */
        public $lastError;
        /**
         * Do we need to log IPN results ?
         *
         * @var boolean
         */
        public $logIpn;
        /**
         * File to log IPN results
         *
         * @var string
         */
        public $ipnLogFile;
        /**
         * Payment gateway IPN response
         *
         * @var string
         */
        public $ipnResponse;
        /**
         * Are we in test mode ?
         *
         * @var boolean
         */
        public $testMode;
        /**
         * Field array to submit to gateway
         *
         * @var array
         */
        public $fields = array();
        /**
         * IPN post values as array
         *
         * @var array
         */
        public $ipnData = array();
        /**
         * Payment gateway URL
         *
         * @var string
         */
        public $gatewayUrl;

        /**
         * Initialization constructor
         *
         * @param none
         * @return void
         */
        public function __construct() {
            // Some default values of the class
            $this->lastError = '';
            $this->logIpn = TRUE;
            $this->ipnResponse = '';
            $this->testMode = FALSE;
        }

        /**
         * Adds a key=>value pair to the fields array
         *
         * @param string key of field
         * @param string value of field
         * @return
         */
        public function addField($field, $value) {
            $this->fields["$field"] = $value;
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
            echo '
		 <div id="' . $gateway . '-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
			<img class="off-site-payment-gateway-img" width="16" height="16" src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/icons/external-link.png" alt="click to visit this payment gateway">
			<form  method="post" name="payment_form" action="' . $this->gatewayUrl . '">';
            foreach ($this->fields as $name => $value) {
                echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
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
        public function submitPayment( $fields = FALSE ) {
            $this->prepareSubmit();
//            echo "<html>\n";
//            echo "<head><title>Processing Payment...</title></head>\n";
//            echo "<body>\n"; // onLoad=\"document.forms['gateway_form'].submit();\"
            echo "<div id=\"bypass_payment_page-dv\">\n"; // onLoad=\"document.forms['gateway_form'].submit();\"
            echo "<p style=\"text-align:center;\"><h2>Please wait, your order is being processed and you will be redirected to the payment website.</h2></p>\n";
            echo "<form id=\"bypass_payment_page_gateway_form\" method=\"POST\" name=\"gateway_form\" ";
            echo "action=\"" . $this->gatewayUrl . "\">\n";
            foreach ($this->fields as $name => $value) {
                echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
                //echo 'Field name: ' . $name . ' Field value : ' . $value . '<br>';
            }
             echo "<input type=\"hidden\" id=\"bypass_payment_page\" name=\"bypass_payment_page\" value=\"true\"/>\n";
            echo "<p style=\"text-align:center;\"><br/><br/>If you are not automatically redirected to  the payment website within 5 seconds...<br/><br/>\n";
            echo "<input type=\"submit\" value=\"Click Here\"></p>\n";
            echo "</form>\n";
            echo "</body></html>\n";
//            echo "</body></html>\n";
        }

        /** 
         * Perform any pre-posting actions
         *
         * @param none
         * @return none
         */
        protected function prepareSubmit() {
            // Fill if needed
        }

        /**
         * Enables the test mode
         *
         * @param none
         * @return none
         */
        abstract protected function enableTestMode();

        /**
         * Validate the IPN notification
         *
         * @param none
         * @return boolean
         */
        abstract protected function validateIpn();

        /**
         * Logs the IPN results
         *
         * @param boolean IPN result
         * @return void
         */
        public function logResults($success) {
            if (!$this->logIpn)
                return;
            // Timestamp

            $text = '[' . date('m/d/Y g:i A') . '] - ';
            // Success or failure being logged?

            $text .= ( $success) ? "SUCCESS!\n" : 'FAIL: ' . $this->lastError . "\n";
            // Log the POST variables

            $text .= "IPN POST Vars from gateway:\n";

            foreach ($this->ipnData as $key => $value) {
                $text .= "$key=$value, ";
            }
            // Log the response from the paypal server

            $text .= "\nIPN Response from gateway Server:\n " . $this->ipnResponse;
            // Write to log
						if (is_writable($this->ipnLogFile)) {
            $fp = @fopen($this->ipnLogFile, 'a');
            @fwrite($fp, $text . "\n\n");
            @fclose($fp);
						}
        }

        public function dump_fields( $gateway_name = '' ) {
            // Used for debugging, this function will output all the field/value pairs
            // that are currently defined in the instance of the class using the
            // add_field() function.
           	echo  '<h5>' . $gateway_name . ' Debug output</h5>';
			echo '<div class="espresso-gateway-debug-dump" style="padding:5px; margin:0 0 3em; border:1px solid #D7482A; border-radius:3px;">';
            echo '<table width="70%" class="debug-table" border="1" cellpadding="2" cellspacing="0" style="margin:0;background:#e8e8e8; border:1px solid #ddd; color:#444;">';
			echo '<thead>';
			echo '<tr>';
			echo '<th>' . __('Field Name', 'event_espresso') . '</th>';
			echo '<th>' . __('Value', 'event_espresso') . '</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
            ksort($this->fields);
            foreach ($this->fields as $key => $value) {
				echo '<tr>';
				echo '<td style="border-right: 1px solid #ccc;">' . $key . '</td>';
				echo '<td style="max-width: 500px; overflow: auto; font-family: monospace; white-space: pre-wrap; word-wrap: break-word; ">' . htmlspecialchars($value) .'&nbsp;</td>';
				echo '</tr>';
            }
			echo '</tbody>';
            echo '</table>';
            echo '</div>';
        }

    }

}
