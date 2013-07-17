<?php

function event_espresso_atos_payment_settings() {
	global $espresso_premium, $active_gateways;
	if (!$espresso_premium)
		return;
	if (isset($_POST['update_atos'])) {
		$settings['merchant_id'] = $_POST['merchant_id'];
		$settings['merchant_country'] = $_POST['merchant_country'];
		$settings['currency_code'] = $_POST['currency_code'];
		$settings['provider'] = $_POST['provider'];
		$settings['language'] = $_POST['language'];
		$settings['payment_means'] = $_POST['payment_means'];
		$settings['debug_mode'] = $_POST['debug_mode'];

		update_option('event_espresso_atos_settings', $settings);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Authorize.net settings saved.', 'event_espresso') . '</strong></p></div>';
	}
	$settings = get_option('event_espresso_atos_settings');
	if (empty($settings)) {
		$settings['merchant_id'] = '';
		$settings['merchant_country'] = 'fr';
		$settings['currency_code'] = '978';
		$settings['provider'] = 'default';
		$settings['language'] = 'fr';
		$settings['debug_mode'] = FALSE;
		$settings['payment_means'] = array(
				'CB' => 0,
				'VISA' => 0,
				'MASTERCARD' => 0,
				'AMEX' => 0,
				'DINERS' => 0,
				'FINAREF' => 0,
				'FNAC' => 0,
				'CYRILLUS' => 0,
				'PRINTEMPS' => 0,
				'KANGOUROU' => 0,
				'SURCOUF' => 0,
				'POCKETCARD' => 0,
				'CONFORAMA' => 0,
				'NUITEA' => 0,
				'AURORE' => 0,
				'PASS' => 0,
				'PLURIEL' => 0,
				'TOYSRUS' => 0,
				'CONNEXION' => 0,
				'HYPERMEDIA' => 0,
				'DELATOUR' => 0,
				'NORAUTO' => 0,
				'NOUVFRONT' => 0,
				'SERAP' => 0,
				'BOURBON' => 0,
				'COFINOGA' => 0,
				'COFINOGA_BHV' => 0,
				'COFINOGA_CASINOGEANT' => 0,
				'COFINOGA_DIAC' => 0,
				'COFINOGA_GL' => 0,
				'COFINOGA_GOSPORT' => 0,
				'COFINOGA_MONOPRIX' => 0,
				'COFINOGA_MRBRICOLAGE' => 0,
				'COFINOGA_SOFICARTE' => 0,
				'COFINOGA_SYGMA' => 0,
				'JCB' => 0,
				'DELTA' => 0,
				'SWITCH' => 0,
				'SOLO' => 0
		);

		if (add_option('event_espresso_atos_settings', $settings, '', 'no') == false) {
			update_option('event_espresso_atos_settings', $settings);
		}
	}


	$file = file_get_contents(dirname(__FILE__) . "/" . $settings["provider"] . "/pathfile");
	$lines = explode("\n", $file);
	$new_file = "";
	foreach ($lines as $line) {
		if (empty($line[0])) {
			continue;
		} elseif ($line[0] == "#") {
			$new_file .= $line . "\n";
			continue;
		}
		$vars = explode("!", $line);
		switch ($vars[0]) {
			case "DEBUG":
				if ($settings['debug_mode']) {
					$new_file .= "DEBUG!YES!\n";
				} else {
					$new_file .= "DEBUG!NO!\n";
				}
				break;

			case "D_LOGO":
				if (dirname(__FILE__) == EVENT_ESPRESSO_GATEWAY_DIR."atos") {
					$url = EVENT_ESPRESSO_GATEWAY_URL."atos/";
				} else {
					$url = EVENT_ESPRESSO_PLUGINFULLURL."gateways/atos/";
				}
				$new_file .= "D_LOGO!" . $url . "logos/!\n";
				break;

			case "F_DEFAULT":
				$new_file .= "F_DEFAULT!" . dirname(__FILE__) . "/" . $settings["provider"] . "/parmcom." . $settings["provider"] . "!\n";
				break;

			case "F_PARAM":
				$new_file .= "F_PARAM!" . dirname(__FILE__) . "/" . $settings["provider"] . "/parmcom!\n";
				break;

			case "F_CERTIFICATE":
				$new_file .= "F_CERTIFICATE!" . dirname(__FILE__) . "/" . $settings["provider"] . "/certif!\n";
				break;
		}
	}
	$result = file_put_contents(dirname(__FILE__) . "/" . $settings["provider"] . "/pathfile", $new_file);

	//Open or close the postbox div
	if (empty($_REQUEST['deactivate_atos'])
					&& (!empty($_REQUEST['activate_atos'])
					|| array_key_exists('atos', $active_gateways))) {
		$postbox_style = '';
	} else {
		$postbox_style = 'closed';
	}
	?>
	<div class="metabox-holder">
		<div class="postbox <?php echo $postbox_style; ?>">
			<div title="Click to toggle" class="handlediv"><br /></div>
			<h3 class="hndle">
				<?php _e('Atos SIPS Settings', 'event_espresso'); ?>
			</h3>
			<div class="inside">
				<div class="padding">
					<?php
					if (!empty($_REQUEST['activate_atos'])) {
						$active_gateways['atos'] = dirname(__FILE__);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					if (!empty($_REQUEST['deactivate_atos'])) {
						unset($active_gateways['atos']);
						update_option('event_espresso_active_gateways', $active_gateways);
					}
					echo '<ul>';
					if (array_key_exists('atos', $active_gateways)) {
						echo '<li id="deactivate_atos" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_atos=true\';" class="red_alert pointer"><strong>' . __('Deactivate Atos SIPS Gateway?', 'event_espresso') . '</strong></li>';
						event_espresso_display_atos_settings();
					} else {
						echo '<li id="activate_atos" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_atos=true\';" class="green_alert pointer"><strong>' . __('Activate Atos SIPS Gateway?', 'event_espresso') . '</strong></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

//Atos SIPS Settings Form
function event_espresso_display_atos_settings() {
	global $org_options;
	$settings = get_option('event_espresso_atos_settings');
	?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<table width="99%" border="0" cellspacing="5" cellpadding="5">
			<tr>
				<td valign="top">
					<ul>
						<li>
							<label for="merchant_id">
								<?php _e('Merchant ID number', 'event_espresso'); ?>
							</label>
							<br />
							<input type="text" name="merchant_id" size="15" value="<?php echo $settings['merchant_id']; ?>">
						</li>
						<li>
							<label for="merchant_country">
								<?php _e('Merchant Country', 'event_espresso'); ?>
							</label>
							<?php
							$values = array(
									array('id' => 'be', 'text' => __('Belgium', 'event_espresso')),
									array('id' => 'fr', 'text' => __('France', 'event_espresso')),
									array('id' => 'de', 'text' => __('Germany', 'event_espresso')),
									array('id' => 'it', 'text' => __('Italy', 'event_espresso')),
									array('id' => 'es', 'text' => __('Spain', 'event_espresso')),
									array('id' => 'en', 'text' => __('United Kingdom', 'event_espresso')));
							echo select_input('merchant_country', $values, $settings['merchant_country']);
							?>
						</li>
						<li>
							<label for="currency_code">
								<?php _e('Merchant Currency', 'event_espresso'); ?>
							</label>
							<?php
							$values = array(
									array('id' => '978', 'text' => __('Euro', 'event_espresso')),
									array('id' => '840', 'text' => __('Us Dollar', 'event_espresso')),
									array('id' => '756', 'text' => __('Swiss Franc', 'event_espresso')),
									array('id' => '826', 'text' => __('Pound Sterling', 'event_espresso')),
									array('id' => '124', 'text' => __('Canadian Dollar', 'event_espresso')),
									array('id' => '392', 'text' => __('Yen', 'event_espresso')),
									array('id' => '484', 'text' => __('Mexican Peso', 'event_espresso')),
									array('id' => '792', 'text' => __('Turkish Lira', 'event_espresso')),
									array('id' => '036', 'text' => __('Australian Dollar', 'event_espresso')),
									array('id' => '554', 'text' => __('New Zealand Dollar', 'event_espresso')),
									array('id' => '578', 'text' => __('Norwegian Krone', 'event_espresso')),
									array('id' => '986', 'text' => __('Brazilian Real', 'event_espresso')),
									array('id' => '032', 'text' => __('Argentinean Peso', 'event_espresso')),
									array('id' => '116', 'text' => __('Riel', 'event_espresso')),
									array('id' => '901', 'text' => __('Taiwan Dollar', 'event_espresso')),
									array('id' => '752', 'text' => __('Swedish Krona', 'event_espresso')),
									array('id' => '208', 'text' => __('Danish Krone', 'event_espresso')),
									array('id' => '410', 'text' => __('Won', 'event_espresso')),
									array('id' => '702', 'text' => __('Singapore Dollar', 'event_espresso')),
									array('id' => '953', 'text' => __('Polynesian Franc', 'event_espresso')),
									array('id' => '952', 'text' => __('CFA Franc', 'event_espresso')));
							echo select_input('currency_code', $values, $settings['currency_code']);
							?>
						</li>
						<li>
							<label for="language">
								<?php _e('Language', 'event_espresso'); ?>
							</label>
							<?php
							$values = array(
									array('id' => 'fr', 'text' => __('French', 'event_espresso')),
									array('id' => 'ge', 'text' => __('German', 'event_espresso')),
									array('id' => 'en', 'text' => __('English', 'event_espresso')),
									array('id' => 'sp', 'text' => __('Spanish', 'event_espresso')),
									array('id' => 'it', 'text' => __('Italian', 'event_espresso')));
							echo select_input('language', $values, $settings['language']);
							?>
						</li>
						<li>
							<label for="provider">
								<?php _e('Provider', 'event_espresso'); ?>
							</label>
							<?php
							$values = array(
									array('id' => 'default', 'text' => __('Default', 'event_espresso')),
									array('id' => 'mercanet', 'text' => __('Mercanet', 'event_espresso')));
							echo select_input('provider', $values, $settings['provider']);
							?>
						</li>
						<li>
							<label for="debug_mode">
								<?php _e('Use Debug Mode', 'event_espresso'); ?>
							</label>
							<?php
							$values = array(
									array('id' => FALSE, 'text' => __('No', 'event_espresso')),
									array('id' => TRUE, 'text' => __('Yes', 'event_espresso')));
							echo select_input('debug_mode', $values, $settings['debug_mode']);
							?>
						</li>
						<li>
							<?php
							_e('Means of Payment and Block Position', 'event_espresso');
							$values = array(
									array('id' => '0', 'text' => __('OFF', 'event_espresso')),
									array('id' => '1', 'text' => __('Choose a means of payment below:', 'event_espresso')),
									array('id' => '2', 'text' => __('You are using the standard secure SSL form; select a card below:', 'event_espresso')),
									array('id' => '4', 'text' => __('Other means of payment:', 'event_espresso')));
							?>
							<label for="payment_means[CB]">
								<?php _e('Carte Bleue', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[CB]", $values, $settings['payment_means']['CB']);
							?>
							<label for="payment_means[VISA]">
								<?php _e('VISA', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[VISA]", $values, $settings['payment_means']['VISA']);
							?>
							<label for="payment_means[MASTERCARD]">
								<?php _e('MASTERCARD', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[MASTERCARD]", $values, $settings['payment_means']['MASTERCARD']);
							?>
							<label for="payment_means[AMEX]">
								<?php _e('AMEX', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[AMEX]", $values, $settings['payment_means']['AMEX']);
							?>
							<label for="payment_means[DINERS]">
								<?php _e('DINERS', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[DINERS]", $values, $settings['payment_means']['DINERS']);
							?>
							<label for="payment_means[FINAREF]">
								<?php _e('FINAREF', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[FINAREF]", $values, $settings['payment_means']['FINAREF']);
							?>
							<label for="payment_means[FNAC]">
								<?php _e('FNAC', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[FNAC]", $values, $settings['payment_means']['FNAC']);
							?>
							<label for="payment_means[CYRILLUS]">
								<?php _e('CYRILLUS', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[CYRILLUS]", $values, $settings['payment_means']['CYRILLUS']);
							?>
							<label for="payment_means[PRINTEMPS]">
								<?php _e('PRINTEMPS', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[PRINTEMPS]", $values, $settings['payment_means']['PRINTEMPS']);
							?>
							<label for="payment_means[KANGOUROU]">
								<?php _e('KANGOUROU', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[KANGOUROU]", $values, $settings['payment_means']['KANGOUROU']);
							?>
							<label for="payment_means[SURCOUF]">
								<?php _e('SURCOUF', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[SURCOUF]", $values, $settings['payment_means']['SURCOUF']);
							?>
							<label for="payment_means[POCKETCARD]">
								<?php _e('POCKETCARD', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[POCKETCARD]", $values, $settings['payment_means']['POCKETCARD']);
							?>
							<label for="payment_means[CONFORAMA]">
								<?php _e('CONFORAMA', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[CONFORAMA]", $values, $settings['payment_means']['CONFORAMA']);
							?>
							<label for="payment_means[NUITEA]">
								<?php _e('NUITEA', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[NUITEA]", $values, $settings['payment_means']['NUITEA']);
							?>
							<label for="payment_means[AURORE]">
								<?php _e('AURORE', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[AURORE]", $values, $settings['payment_means']['AURORE']);
							?>
							<label for="payment_means[PASS]">
								<?php _e('PASS', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[PASS]", $values, $settings['payment_means']['PASS']);
							?>
							<label for="payment_means[PLURIEL]">
								<?php _e('PLURIEL', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[PLURIEL]", $values, $settings['payment_means']['PLURIEL']);
							?>
							<label for="payment_means[TOYSRUS]">
								<?php _e('TOYSRUS', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[TOYSRUS]", $values, $settings['payment_means']['TOYSRUS']);
							?>
							<label for="payment_means[CONNEXION]">
								<?php _e('CONNEXION', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[CONNEXION]", $values, $settings['payment_means']['CONNEXION']);
							?>
							<label for="payment_means[HYPERMEDIA]">
								<?php _e('HYPERMEDIA', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[HYPERMEDIA]", $values, $settings['payment_means']['HYPERMEDIA']);
							?>
							<label for="payment_means[DELATOUR]">
								<?php _e('DELATOUR', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[DELATOUR]", $values, $settings['payment_means']['DELATOUR']);
							?>
							<label for="payment_means[NORAUTO]">
								<?php _e('NORAUTO', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[NORAUTO]", $values, $settings['payment_means']['NORAUTO']);
							?>
							<label for="payment_means[NOUVFRONT]">
								<?php _e('NOUVFRONT', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[NOUVFRONT]", $values, $settings['payment_means']['NOUVFRONT']);
							?>
							<label for="payment_means[SERAP]">
								<?php _e('SERAP', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[SERAP]", $values, $settings['payment_means']['SERAP']);
							?>
							<label for="payment_means[BOURBON]">
								<?php _e('BOURBON', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[BOURBON]", $values, $settings['payment_means']['BOURBON']);
							?>
							<label for="payment_means[COFINOGA]">
								<?php _e('COFINOGA', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA]", $values, $settings['payment_means']['COFINOGA']);
							?>
							<label for="payment_means[COFINOGA_BHV]">
								<?php _e('BHV', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_BHV]", $values, $settings['payment_means']['COFINOGA_BHV']);
							?>
							<label for="payment_means[COFINOGA_CASINOGEANT]">
								<?php _e('CASINOGEANT', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_CASINOGEANT]", $values, $settings['payment_means']['COFINOGA_CASINOGEANT']);
							?>
							<label for="payment_means[COFINOGA_DIAC]">
								<?php _e('DIAC', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_DIAC]", $values, $settings['payment_means']['COFINOGA_DIAC']);
							?>
							<label for="payment_means[COFINOGA_GL]">
								<?php _e('GL', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_GL]", $values, $settings['payment_means']['COFINOGA_GL']);
							?>
							<label for="payment_means[COFINOGA_GOSPORT]">
								<?php _e('GOSPORT', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_GOSPORT]", $values, $settings['payment_means']['COFINOGA_GOSPORT']);
							?>
							<label for="payment_means[COFINOGA_MONOPRIX]">
								<?php _e('MONOPRIX', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_MONOPRIX]", $values, $settings['payment_means']['COFINOGA_MONOPRIX']);
							?>
							<label for="payment_means[COFINOGA_MRBRICOLAGE]">
								<?php _e('MRBRICOLAGE', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_MRBRICOLAGE]", $values, $settings['payment_means']['COFINOGA_MRBRICOLAGE']);
							?>
							<label for="payment_means[COFINOGA_SOFICARTE]">
								<?php _e('SOFICARTE', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_SOFICARTE]", $values, $settings['payment_means']['COFINOGA_SOFICARTE']);
							?>
							<label for="payment_means[COFINOGA_SYGMA]">
								<?php _e('SYGMA', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[COFINOGA_SYGMA]", $values, $settings['payment_means']['COFINOGA_SYGMA']);
							?>
							<label for="payment_means[JCB]">
								<?php _e('JCB', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[JCB]", $values, $settings['payment_means']['JCB']);
							?>
							<label for="payment_means[DELTA]">
								<?php _e('DELTA', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[DELTA]", $values, $settings['payment_means']['DELTA']);
							?>
							<label for="payment_means[SWITCH]">
								<?php _e('SWITCH', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[SWITCH]", $values, $settings['payment_means']['SWITCH']);
							?>
							<label for="payment_means[SOLO]">
								<?php _e('SOLO', 'event_espresso'); ?>
							</label>
							<?php
							echo select_input("payment_means[SOLO]", $values, $settings['payment_means']['SOLO']);
							?>
						</li>
					</ul>
				</td>
			</tr>
		</table>
		<p>
			<input type="hidden" name="update_atos" value="update_atos">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Atos SIPS Settings', 'event_espresso') ?>" id="save_atos_settings" />
		</p>
	</form>
	<p>
		<?php
		_e("Notes on Files and Permissions: ", "event_espresso");
		_e("In order for the Atos gateway to work properly, you will need to upload the files given to you by your provider to the folder matching your selected provider above. Eg, gateways/atos/mercanet/. The file called pathfile will need to be writable by your web server and the two files in gateways/atos/bin/ will need to be executable by your web server. The current implementation uses the Atos provided Linux executables. Please contact support if you are using a Windows server, and would like to sponsor development of a version that will run on your server. ", "event_espresso");
		_e("For more information on setting file permissions, see: ", "event_espresso"); ?>
		<a href="http://codex.wordpress.org/Changing_File_Permissions" target="blank">http://codex.wordpress.org/Changing_File_Permissions</a>
	</p>
		<?php
}

add_action('action_hook_espresso_display_gateway_settings', 'event_espresso_atos_payment_settings');
