<?php

function recaptcha_get_html($pubkey) {
	global	$org_options;

	//Use the ReCaptcha language settings.
	$lang = !empty($org_options['recaptcha_language']) ? '?hl=' . $org_options['recaptcha_language'] : '';

	//Enqueue the google recaptcha script.
	wp_enqueue_script( 'google_recaptcha', 'https://www.google.com/recaptcha/api.js' . $lang );

	//ReCaptcha V2 only has 2 options, users may have set different options for V1.
	//So if the user has set the 'Dark' theme, use that, otherwise for any other option use the light theme (default for v2)
	if (!empty($org_options['recaptcha_theme'])) {
		if( $org_options['recaptcha_theme'] == 'dark') {
			$theme = 'dark';
		} else {
			$theme ='light';
		}
	}

    return '<div class="g-recaptcha" data-sitekey="' . $pubkey . '" data-theme="'. $theme . '"></div>';
}

function recaptcha_check_answer($privkey, $remoteip, $gRecaptchaResponse) {
	//Auto load the ReCaptcha library.
	require('recaptcha/autoload.php');

	//Check if allow_url_fopen has been disabled on the server, use sockets if so.
	$request_method = ! ini_get( 'allow_url_fopen' ) ? new \ReCaptcha\RequestMethod\SocketPost() : null;

	//Instantiate Recaptcha
	$recaptcha = new \ReCaptcha\ReCaptcha($privkey, $request_method);

    //Vaildate the ReCaptcha response and return.
    return $recaptcha->verify($gRecaptchaResponse, $remoteip);

}