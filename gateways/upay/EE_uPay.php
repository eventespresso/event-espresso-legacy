<?php

if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author			Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link					http://www.eventespresso.com
 * @ version		 	4.3
 *
 * ------------------------------------------------------------------------
 *
 * EE_uPay
 *
 * @package			Event Espresso
 * @subpackage		
 * @author				Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class EE_uPay extends Espresso_PaymentGateway{
	public $gateway_version = '1.0';
	public function enableTestMode() {
		$this->testMode = TRUE;
		//dont change the url we send to or anything, because there is no 'sandbox' site
		//just sites in test mode
	}

	protected function validateIpn() {
		
	}
}

// End of file EE_uPay.php