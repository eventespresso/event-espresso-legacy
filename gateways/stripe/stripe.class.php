<?php
if(!class_exists("Espresso_Stripe")) {
	require_once (dirname(__FILE__).'/stripe-php-1.5.19/lib/Stripe.php');
}

class Espresso_ClsStripe
{	
	function do_transaction($amount ,$cc, $cvc, $exp_month, $exp_year, $name, $description)
	{
		$result = array();

		$stripe_settings = get_option('event_espresso_stripe_settings');
        $publishableKey = $stripe_settings['stripe_publishable_key'];
        $secretKey = $stripe_settings['stripe_secret_key'];
        $currencySymbol = $stripe_settings['stripe_currency_symbol'];
        //$transactionPrefix = $stripe_settings['stripe_transaction_prefix'];
		
		Stripe::setApiKey($secretKey);
		
		$charge = "unknown";
		try {
			$charge = Stripe_Charge::create(array(
					"amount"	=> $amount*100,
					"currency"	=> $currencySymbol,
					"card"		=> array(
					"number"	=> $cc,
					"exp_month"	=> $exp_month,
					"exp_year"	=> $exp_year,
					"cvc"		=> $cvc,
					"name"		=> $name
				),
				"description"	=> $description
				));
			
			$result["status"] = 1;
			$result["msg"] = "Transaction was completed successfully [Transaction ID# ".$charge->id."]";
			$result['txid'] = $charge->id;
		} catch (Exception $e) {
			$result["status"] = 0;
			$result["error_msg"] = "Failed to charge the card.";
		}
		
		return $result;
	}
}