<?php
function espresso_display_myvirtualmerchant($data) {
	extract($data);
	global $org_options;
	$myvirtualmerchant_settings = get_option('event_espresso_myvirtualmerchant_settings');
	$use_sandbox = $myvirtualmerchant_settings['myvirtualmerchant_use_sandbox'];
	wp_register_script( 'myvirtualmerchant', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/myvirtualmerchant/myvirtualmerchant.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'myvirtualmerchant' );		
	
$countries = array(
'AFG'=>'Afghanistan','LBR'=>'Liberia', 
'ALA'=>'Åland Islands', 'LBY'=>'Libya', 
'ALB'=>'Albania', 'LIE'=>'Liechtenstein', 
'DZA'=>'Algeria', 'LTU'=>'Lithuania', 
'ASM'=>'American Samoa', 'LUX'=>'Luxembourg', 
'AND'=>'Andorra', 'MAC'=>'Macao', 
'AGO '=>'Angola',
'the'=>'Macedonia,', 'MKD'=>'former Yugoslav Republic of', 
'AIA'=>'Anguilla', 'MDG'=>'Madagascar', 
'ATA'=>'Antarctica', 'MWI'=>'Malawi', 
'ATG'=>'Antigua and Bermuda', 'MYS'=>'Malaysia', 
'ARG'=>'Argentina', 'MDV'=>'Maldives', 
'ARM'=>'Armenia', 'MLI'=>'Mali', 
'ABW'=>'Aruba', 'MLT'=>'Malta', 
'AUS'=>'Australia', 'MHL'=>'Marshall Islands', 
'AUT'=>'Austria', 'MTQ'=>'Martinique', 
'AZE'=>'Azerbaijan', 'MRT'=>'Mauritania', 
'BHS'=>'Bahamas', 'MUS'=>'Mauritius', 
'BHR'=>'Bahrain', 'MYT'=>'Mayotte', 
'BGD'=>'Bangladesh', 'MEX'=>'Mexico', 
'BRB'=>'Barbados', 'FSM'=>'Micronesia, Federated States of', 
'BLR'=>'Belarus', 'MDA'=>'Moldova, Republic of', 
'BEL'=>'Belgium', 'MCO'=>'Monaco', 
'BLZ'=>'Belize', 'MNG'=>'Mongolia', 
'BEN'=>'Benin', 'MNE'=>'Montenegro', 
'BMU'=>'Bermuda', 'MSR'=>'Montserrat', 
'BTN'=>'Bhutan', 'MAR'=>'Morocco', 
'BOL'=>'Bolivia, Plurinational State of', 'MOZ'=>'Mozambique', 
'BES'=>'Bonaire, Sint Eustatius and Saba', 'MMR'=>'Myanmar',
'BIH'=>'Bosnia and Herzegovina', 'NAM'=>'Namibia', 
'BWA'=>'Botswana', 'NRU'=>'Nauru', 
'BVT'=>'Bouvet Island', 'NPL'=>'Nepal', 
'BRA'=>'Brazil', 'NLD'=>'Netherlands', 
'New'=>'British Indian Ocean Territory IOT', 'NCL'=>'Caledonia', 
'New'=>'Brunei Darussalam BRN', 'NZL'=>'Zealand', 
'BGR'=>'Bulgaria', 'NIC'=>'Nicaragua', 
'BFA'=>'Burkina Faso', 'NER'=>'Niger', 
'BDI'=>'Burundi', 'NGA'=>'Nigeria', 
'KHM'=>'Cambodia', 'NIU'=>'Niue', 
'CMR'=>'Cameroon', 'NFK'=>'Norfolk Island', 
'CAN'=>'Canada', 'MNP'=>'Northern Mariana Islands', 
'CPV'=>'Cape Verde', 'NOR'=>'Norway', 
'CYM'=>'Cayman Islands', 'OMN'=>'Oman', 
'CAF'=>'Central African Republic', 'PAK'=>'Pakistan', 
'TCD'=>'Chad', 'PLW'=>'Palau', 
'CHL'=>'Chile', 'PSE'=>'Palestinian Territory, Occupied', 
'CHN'=>'China', 'PAN'=>'Panama', 
'New'=>'Christmas Island CXR Papua', 'PNG'=>'Guinea', 
'CCK'=>'Cocos (Keeling) Islands', 'PRY'=>'Paraguay', 
'COL'=>'Colombia', 'PER'=>'Peru', 
'COM'=>'Comoros', 'PHL'=>'Philippines', 
'COG'=>'Congo', 'PCN'=>'Pitcairn', 
'COD'=>'Congo, the Democratic Republic of the', 'POL'=>'Poland', 
'COK'=>'Cook Islands', 'PRT'=>'Portugal', 
'CRI'=>'Costa Rica', 'PRI'=>'Puerto Rico', 
'CIV'=>'Côte d\'Ivoire', 'QAT'=>'Qatar', 
'HRV'=>'Croatia', 'REU'=>'Réunion', 
'CUB'=>'Cuba', 'ROU'=>'Romania', 
'CUW'=>'Curaçao', 'RUS'=>'Russian Federation', 
'CYP'=>'Cyprus', 'RWA'=>'Rwanda', 
'CZE'=>'Czech Republic', 'BLM'=>'Saint Barthélemy', 
'and'=>'Denmark DNK Saint Helena, Ascension', 'SHN'=>'Tristan da Cunha', 
'and'=>'Djibouti DJI Saint Kitts', 'KNA'=>'Nevis', 
'DMA'=>'Dominica', 'LCA'=>'Saint Lucia', 
'DOM'=>'Dominican Republic', 'MAF'=>'Saint Martin (French part)', 
'and'=>'Ecuador ECU Saint Pierre', 'SPM'=>'Miquelon', 
'the'=>'Egypt EGY Saint Vincent and', 'VCT'=>'Grenadines', 
'SLV'=>'El Salvador', 'WSM'=>'Samoa', 
'San'=>'Equatorial Guinea GNQ', 'SMR'=>'Marino', 
'and'=>'Eritrea ERI Sao Tome', 'STP'=>'Principe', 
'EST'=>'Estonia', 'SAU'=>'Saudi Arabia', 
'ETH'=>'Ethiopia', 'SEN'=>'Senegal', 
'FLK'=>'Falkland Islands (Malvinas)', 'SRB'=>'Serbia', 
'FRO'=>'Faroe Islands', 'SYC'=>'Seychelles', 
'FJI'=>'Fiji', 'SLE'=>'Sierra Leone', 
'FIN'=>'Finland', 'SGP'=>'Singapore', 
'FRA'=>'France', 'SXM'=>'Sint Maarten (Dutch part)', 
'GUF'=>'French Guiana', 'SVK'=>'Slovakia', 
'PYF'=>'French Polynesia', 'SVN'=>'Slovenia', 
'ATF'=>'French Southern Territories', 'SLB'=>'Solomon Islands', 
'GAB'=>'Gabon', 'SOM'=>'Somalia', 
'GMB'=>'Gambia', 'ZAF'=>'South Africa', 
'the'=>'Georgia GEO South Georgia and', 'SGS'=>'South Sandwich Islands', 
'DEU'=>'Germany', 'SSD'=>'South Sudan', 
'GHA'=>'Ghana', 'ESP'=>'Spain', 
'Sri'=>'Gibraltar GIB', 'LKA'=>'Lanka', 
'GRC'=>'Greece', 'SDN'=>'Sudan', 
'GRL'=>'Greenland', 'SUR'=>'Suriname', 
'Jan'=>'Grenada GRD Svalbard and', 'SJM'=>'Mayen', 
'GLP'=>'Guadeloupe', 'SWZ'=>'Swaziland', 
'GUM'=>'Guam', 'SWE'=>'Sweden', 
'GTM'=>'Guatemala', 'CHE'=>'Switzerland', 
'GGY'=>'Guernsey', 'SYR'=>'Syrian Arab Republic', 
'GIN'=>'Guinea', 'TWN'=>'Taiwan, Province of China', 
'GNB'=>'Guinea-Bissau', 'TJK'=>'Tajikistan', 
'GUY'=>'Guyana', 'TZA'=>'Tanzania, United Republic of', 
'HTI'=>'Haiti', 'THA'=>'Thailand', 
'HMD'=>'Heard Island and McDonald Islands', 'TLS'=>'Timor-Leste', 
'VAT'=>'Holy See (Vatican City State)', 'TGO'=>'Togo', 
'HND'=>'Honduras', 'TKL'=>'Tokelau', 
'HKG'=>'Hong Kong', 'TON'=>'Tonga', 
'and'=>'Hungary HUN Trinidad', 'TTO'=>'Tobago', 
'ISL'=>'Iceland', 'TUN'=>'Tunisia', 
'IND'=>'India', 'TUR'=>'Turkey', 
'IDN'=>'Indonesia', 'TKM'=>'Turkmenistan', 
'and'=>'Iran, Islamic Republic of IRN Turks', 'TCA'=>'Caicos Islands', 
'IRQ'=>'Iraq', 'TUV'=>'Tuvalu', 
'IRL'=>'Ireland', 'UGA'=>'Uganda', 
'IMN'=>'Isle of Man', 'UKR'=>'Ukraine', 
'ISR'=>'Israel', 'ARE'=>'United Arab Emirates', 
'ITA'=>'Italy', 'GBR'=>'United Kingdom', 
'JAM'=>'Jamaica', 'USA'=>'United States', 
'JPN'=>'Japan', 'UMI'=>'United States Minor Outlying Islands', 
'JEY'=>'Jersey', 'URY'=>'Uruguay', 
'JOR'=>'Jordan', 'UZB'=>'Uzbekistan', 
'KAZ'=>'Kazakhstan', 'VUT'=>'Vanuatu', 
'KEN'=>'Kenya', 'VEN'=>'Venezuela, Bolivarian Republic of', 
'KIR'=>'Kiribati', 'VNM'=>'Viet Nam', 
'PRK'=>'Korea, Democratic People\'s Republic of', 'VGB'=>'Virgin Islands, British', 
'KOR'=>'Korea, Republic of', 'VIR'=>'Virgin Islands, U.S.', 
'and'=>'Kuwait KWT Wallis', 'WLF'=>'Futuna', 
'KGZ'=>'Kyrgyzstan', 'ESH'=>'Western Sahara', 
'LAO'=>'Lao People\'s Democratic Republic', 'YEM'=>'Yemen', 
'LVA'=>'Latvia', 'ZMB'=>'Zambia', 
'LBN'=>'Lebanon', 'ZWE'=>'Zimbabwe', 
'LSO'=>'Lesotho' );
	?>
<div id="myvirtualmerchant-payment-option-dv" class="payment-option-dv">

	<a id="myvirtualmerchant-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="myvirtualmerchant-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using Credit Card" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/pay-by-credit-card.png">
	</a>	

	<div id="myvirtualmerchant-payment-option-form-dv" class="hide-if-js">	
		<div class="event-display-boxes">
			<?php
			if ($use_sandbox) {
				echo '<div id="sandbox-panel"><h2 class="section-title">' . __('MyVirtualMerchant Demo Mode', 'event_espresso') . '</h2><p>Test # 4111111111111111</p>';
				echo '<p>Exp: any date in the future</p>';
				echo '<p>CVV2: 123 </p>';
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3></div>';
			}
			if ($myvirtualmerchant_settings['force_ssl_return']) {
				$home = str_replace('http://', 'https://', home_url());
			} else {
				$home = home_url();
			}
			if ($myvirtualmerchant_settings['display_header']) {
?>
			<h3 class="payment_header"><?php echo $myvirtualmerchant_settings['header']; ?></h3><?php } ?>

			<div class = "event_espresso_form_wrapper">
				<form id="myvirtualmerchant_payment_form" name="myvirtualmerchant_payment_form" method="post" action="<?php echo $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id; ?>">
					
					<fieldset id="paypal-billing-info-dv">
						<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
						<p>
							<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
				        	<input name="first_name" type="text" id="mvm_first_name" class="required" value="<?php echo $fname ?>" />
						</p>
						<p>
					        <label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
					        <input name="last_name" type="text" id="mvm_last_name" class="required" value="<?php echo $lname ?>" />
						</p>
						<p>
					        <label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
					        <input name="email" type="text" id="mvm_email" class="required" value="<?php echo $attendee_email ?>" />
						</p>
						<p>
					        <label for="address"><?php _e('Address', 'event_espresso'); ?></label>
					        <input name="address" type="text" id="mvm_address" class="required" value="<?php echo $address ?>" />
						</p>
						<p>
					        <label for="address2"><?php _e('Address (cont\'d)', 'event_espresso'); ?></label>
					        <input name="address2" type="text" id="mvm_address2" value="<?php echo $address2 ?>" />
						</p>
						<p>
					        <label for="city"><?php _e('City', 'event_espresso'); ?></label>
					        <input name="city" type="text" id="mvm_city" class="required" value="<?php echo $city ?>" />
						</p>
						<p>
					        <label for="state"><?php _e('State', 'event_espresso'); ?></label>
					        <input name="state" type="text" id="mvm_state" class="required" value="<?php echo $state ?>" />
						</p>
						<p>
							
					        <label for="country"><?php _e('Country', 'event_espresso'); ?></label>
							<?php asort($countries); $countries = array(''=>  __("Please select...", 'event_espresso')) + $countries;?>
							<select name="country" id="mvm_country" class="required">
								<?php foreach ($countries as $iso=>$name){
									?><option value="<?php echo $iso?>" <?php echo $iso == $country || $name == $country ? 'selected="selected"':''?>><?php echo $name?></option><?php
								}?>
							</select>
						</p>
						<p>
					        <label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
					        <input name="zip" type="text" id="mvm_zip" class="required" />
						</p>
						<p>
					        <label for="phone"><?php _e('Phone', 'event_espresso'); ?></label>
					        <input name="phone" type="text" id="mvm_phone" class="required" value="<?php echo $phone ?>" />
						</p>
					</fieldset>

					<fieldset id="paypal-credit-card-info-dv">
						<h4 class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></h4>
						<p>
					        <label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
					        <input type="text" name="card_num" class="required" id="mvm_card_num" autocomplete="off" />
						</p>
						<p>
					        <label for="card-exp"><?php _e('Expiration Month', 'event_espresso'); ?></label>
					        <select id="mvm_card-exp" name ="expmonth" class="med required">
										<?php
										for ($i = 1; $i < 13; $i++)
											echo "<option value='".sprintf("%02s", $i)."'>$i</option>";
										?>
					        </select>
						</p>
						<p>
					        <label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
					        <select id="mvm_exp-year" name ="expyear" class="med required">
										<?php
										$curr_year = date("y");
										for ($i = 0; $i < 10; $i++) {
											$disp_year = $curr_year + $i;
											echo "<option value='$disp_year'>$disp_year</option>";
										}
										?>
					        </select>
						</p>
						<p>
					        <label for="cvv"><?php _e('CVV Code', 'event_espresso'); ?></label>
					        <input type="text" name="cvv" id="mvm_exp_date" autocomplete="off"  class="small required"/>
						</p>
					</fieldset>
					
					<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
					<input name="myvirtualmerchant" type="hidden" value="true" />
					<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />
					<input name='invoice' type='hidden' value='<?php echo uniqid(); ?>'/>
					<p class="event_form_submit">
						<input name="myvirtualmerchant_submit" id="myvirtualmerchant_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />		
						<div class="clear" id="processing" style="display:none"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/ajax-loader.gif"></div>
					</p>
					<span id="processing"></span>
				</form>

			</div><!-- / .event_espresso_or_wrapper -->
		</div>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="myvirtualmerchant-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
		</p>

	</div>
</div>		
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway','espresso_display_myvirtualmerchant');
