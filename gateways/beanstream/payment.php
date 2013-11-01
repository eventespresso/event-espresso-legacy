<?php

function espresso_display_beanstream($data) {
	extract($data);
	$states = array(
		array('id' => 'AB', 'text' => __('Alberta', 'event_espresso')),
		array('id' => 'AK', 'text' => __('Alaska', 'event_espresso')),
		array('id' => 'AL', 'text' => __('Alabama', 'event_espresso')),
		array('id' => 'AS', 'text' => __('American Somoa', 'event_espresso')),
		array('id' => 'AR', 'text' => __('Arkansas', 'event_espresso')),
		array('id' => 'AZ', 'text' => __('Arizona', 'event_espresso')),
		array('id' => 'BC', 'text' => __('British Columbia', 'event_espresso')),
		array('id' => 'CA', 'text' => __('California', 'event_espresso')),
		array('id' => 'CO', 'text' => __('Colorado', 'event_espresso')),
		array('id' => 'CT', 'text' => __('Connecticut', 'event_espresso')),
		array('id' => 'DC', 'text' => __('District of Columbia', 'event_espresso')),
		array('id' => 'DE', 'text' => __('Delaware', 'event_espresso')),
		array('id' => 'FL', 'text' => __('Florida', 'event_espresso')),
		array('id' => 'GA', 'text' => __('Georgia', 'event_espresso')),
		array('id' => 'GU', 'text' => __('Guam', 'event_espresso')),
		array('id' => 'HI', 'text' => __('Hawaii', 'event_espresso')),
		array('id' => 'IA', 'text' => __('Iowa', 'event_espresso')),
		array('id' => 'ID', 'text' => __('Idaho', 'event_espresso')),
		array('id' => 'IL', 'text' => __('Illinois', 'event_espresso')),
		array('id' => 'IN', 'text' => __('Indiana', 'event_espresso')),
		array('id' => 'KS', 'text' => __('Kansas', 'event_espresso')),
		array('id' => 'KY', 'text' => __('Kentucky', 'event_espresso')),
		array('id' => 'LA', 'text' => __('Louisiana', 'event_espresso')),
		array('id' => 'MA', 'text' => __('Massachusetts', 'event_espresso')),
		array('id' => 'MB', 'text' => __('Manitoba', 'event_espresso')),
		array('id' => 'MD', 'text' => __('Maryland', 'event_espresso')),
		array('id' => 'ME', 'text' => __('Maine', 'event_espresso')),
		array('id' => 'MI', 'text' => __('Michigan', 'event_espresso')),
		array('id' => 'FM', 'text' => __('Micronesia', 'event_espresso')),
		array('id' => 'MN', 'text' => __('Minnesota', 'event_espresso')),
		array('id' => 'MO', 'text' => __('Missouri', 'event_espresso')),
		array('id' => 'MS', 'text' => __('Mississippi', 'event_espresso')),
		array('id' => 'MT', 'text' => __('Montana', 'event_espresso')),
		array('id' => 'NB', 'text' => __('New Brunswick', 'event_espresso')),
		array('id' => 'NC', 'text' => __('North Carolina', 'event_espresso')),
		array('id' => 'ND', 'text' => __('North Dakota', 'event_espresso')),
		array('id' => 'NE', 'text' => __('Nebraska', 'event_espresso')),
		array('id' => 'NL', 'text' => __('Newfoundland/Labrador', 'event_espresso')),
		array('id' => 'NH', 'text' => __('New Hampshire', 'event_espresso')),
		array('id' => 'NJ', 'text' => __('New Jersey', 'event_espresso')),
		array('id' => 'NM', 'text' => __('New Mexico', 'event_espresso')),
		array('id' => 'NS', 'text' => __('Nova Scotia', 'event_espresso')),
		array('id' => 'NT', 'text' => __('Northwest Territories', 'event_espresso')),
		array('id' => 'NU', 'text' => __('Nunavut', 'event_espresso')),
		array('id' => 'NV', 'text' => __('Nevada', 'event_espresso')),
		array('id' => 'NY', 'text' => __('New York', 'event_espresso')),
		array('id' => 'OH', 'text' => __('Ohio', 'event_espresso')),
		array('id' => 'OK', 'text' => __('Oklahoma', 'event_espresso')),
		array('id' => 'ON', 'text' => __('Ontario', 'event_espresso')),
		array('id' => 'OR', 'text' => __('Oregon', 'event_espresso')),
		array('id' => 'PA', 'text' => __('Pennsylvania', 'event_espresso')),
		array('id' => 'PE', 'text' => __('Prince Edward Island', 'event_espresso')),
		array('id' => 'PR', 'text' => __('Puerto Rico', 'event_espresso')),
		array('id' => 'QC', 'text' => __('Quebec', 'event_espresso')),
		array('id' => 'RI', 'text' => __('Rhode Island', 'event_espresso')),
		array('id' => 'SC', 'text' => __('South Carolina', 'event_espresso')),
		array('id' => 'SD', 'text' => __('South Dakota', 'event_espresso')),
		array('id' => 'SK', 'text' => __('Saskatchewan', 'event_espresso')),
		array('id' => 'TN', 'text' => __('Tennessee', 'event_espresso')),
		array('id' => 'TX', 'text' => __('Texas', 'event_espresso')),
		array('id' => 'UT', 'text' => __('Utah', 'event_espresso')),
		array('id' => 'VA', 'text' => __('Virginia', 'event_espresso')),
		array('id' => 'VI', 'text' => __('Virgin Islands', 'event_espresso')),
		array('id' => 'VT', 'text' => __('Vermont', 'event_espresso')),
		array('id' => 'WA', 'text' => __('Washington', 'event_espresso')),
		array('id' => 'WI', 'text' => __('Wisconsin', 'event_espresso')),
		array('id' => 'WV', 'text' => __('West Virginia', 'event_espresso')),
		array('id' => 'WY', 'text' => __('Wyoming', 'event_espresso')),
		array('id' => 'YT', 'text' => __('Yukon', 'event_espresso')),
		array('id' => '--', 'text' => __('Outside U.S./Canada', 'event_espresso')));
	$countries = array(
		array('id' => 'AF', 'text' => __('Afghanistan', 'event_espresso')),
		array('id' => 'AR', 'text' => __('Argentina', 'event_espresso')),
		array('id' => 'AX', 'text' => __('land Islands', 'event_espresso')),
		array('id' => 'AL', 'text' => __('Albania', 'event_espresso')),
		array('id' => 'DZ', 'text' => __('Algeria', 'event_espresso')),
		array('id' => 'AS', 'text' => __('American Samoa', 'event_espresso')),
		array('id' => 'AD', 'text' => __('Andorra', 'event_espresso')),
		array('id' => 'AO', 'text' => __('Angola', 'event_espresso')),
		array('id' => 'AI', 'text' => __('Anguilla', 'event_espresso')),
		array('id' => 'AQ', 'text' => __('Antarctica', 'event_espresso')),
		array('id' => 'AG', 'text' => __('Antigua and Barbuda', 'event_espresso')),
		array('id' => 'AM', 'text' => __('Armenia', 'event_espresso')),
		array('id' => 'AW', 'text' => __('Aruba', 'event_espresso')),
		array('id' => 'AU', 'text' => __('Australia', 'event_espresso')),
		array('id' => 'AT', 'text' => __('Austria', 'event_espresso')),
		array('id' => 'AZ', 'text' => __('Azerbaijan', 'event_espresso')),
		array('id' => 'BS', 'text' => __('Bahamas', 'event_espresso')),
		array('id' => 'BH', 'text' => __('Bahrain', 'event_espresso')),
		array('id' => 'BD', 'text' => __('Bangladesh', 'event_espresso')),
		array('id' => 'BB', 'text' => __('Barbados', 'event_espresso')),
		array('id' => 'BY', 'text' => __('Belarus', 'event_espresso')),
		array('id' => 'BE', 'text' => __('Belgium', 'event_espresso')),
		array('id' => 'BZ', 'text' => __('Belize IR', 'event_espresso')),
		array('id' => 'BJ', 'text' => __('Benin', 'event_espresso')),
		array('id' => 'BM', 'text' => __('Bermuda', 'event_espresso')),
		array('id' => 'BT', 'text' => __('Bhutan', 'event_espresso')),
		array('id' => 'BO', 'text' => __('Bolivia', 'event_espresso')),
		array('id' => 'BA', 'text' => __('Bosnia and Herzegovina', 'event_espresso')),
		array('id' => 'BW', 'text' => __('Botswana', 'event_espresso')),
		array('id' => 'BV', 'text' => __('Bouvet Island', 'event_espresso')),
		array('id' => 'BR', 'text' => __('Brazil', 'event_espresso')),
		array('id' => 'IO', 'text' => __('British Indian Ocean Territory', 'event_espresso')),
		array('id' => 'BN', 'text' => __('Brunei Darussalam', 'event_espresso')),
		array('id' => 'BG', 'text' => __('Bulgaria', 'event_espresso')),
		array('id' => 'BF', 'text' => __('Burkina Faso', 'event_espresso')),
		array('id' => 'BI', 'text' => __('Burundi', 'event_espresso')),
		array('id' => 'KH', 'text' => __('Cambodia', 'event_espresso')),
		array('id' => 'CM', 'text' => __('Cameroon', 'event_espresso')),
		array('id' => 'CA', 'text' => __('Canada', 'event_espresso')),
		array('id' => 'CV', 'text' => __('Cape Verde', 'event_espresso')),
		array('id' => 'KY', 'text' => __('Cayman Islands', 'event_espresso')),
		array('id' => 'CF', 'text' => __('Central African Republic', 'event_espresso')),
		array('id' => 'TD', 'text' => __('Chad', 'event_espresso')),
		array('id' => 'CL', 'text' => __('Chile', 'event_espresso')),
		array('id' => 'CN', 'text' => __('China', 'event_espresso')),
		array('id' => 'CX', 'text' => __('Christmas Island', 'event_espresso')),
		array('id' => 'CC', 'text' => __(' Cocos (Keeling) Islands', 'event_espresso')),
		array('id' => 'CO', 'text' => __('Columbia', 'event_espresso')),
		array('id' => 'KM', 'text' => __('Comoros', 'event_espresso')),
		array('id' => 'CG', 'text' => __('Congo', 'event_espresso')),
		array('id' => 'CD', 'text' => __('Congo, The Democratic Republic of the', 'event_espresso')),
		array('id' => 'CK', 'text' => __('Cook Islands', 'event_espresso')),
		array('id' => 'CR', 'text' => __('Costa Rica', 'event_espresso')),
		array('id' => 'CI', 'text' => __('Cote dÕIvoire ÐIvory Coast', 'event_espresso')),
		array('id' => 'HR', 'text' => __('Croatia', 'event_espresso')),
		array('id' => 'CU', 'text' => __('Cuba', 'event_espresso')),
		array('id' => 'CY', 'text' => __('Cyprus', 'event_espresso')),
		array('id' => 'CZ', 'text' => __('Czech Republic', 'event_espresso')),
		array('id' => 'DK', 'text' => __('Denmark', 'event_espresso')),
		array('id' => 'DJ', 'text' => __('Djibouti', 'event_espresso')),
		array('id' => 'DM', 'text' => __('Dominica', 'event_espresso')),
		array('id' => 'DO', 'text' => __('Dominican Republic', 'event_espresso')),
		array('id' => 'TL', 'text' => __('East Timor', 'event_espresso')),
		array('id' => 'EC', 'text' => __('Ecuador', 'event_espresso')),
		array('id' => 'EG', 'text' => __('Egypt', 'event_espresso')),
		array('id' => 'SV', 'text' => __('El Salvador', 'event_espresso')),
		array('id' => 'GQ', 'text' => __('Equatorial Guinea', 'event_espresso')),
		array('id' => 'ER', 'text' => __('Eritrea', 'event_espresso')),
		array('id' => 'EE', 'text' => __('Estonia', 'event_espresso')),
		array('id' => 'ET', 'text' => __('Ethiopia', 'event_espresso')),
		array('id' => 'FK', 'text' => __('Falkland Islands (Malvinas)', 'event_espresso')),
		array('id' => 'FO', 'text' => __('Faroe Islands', 'event_espresso')),
		array('id' => 'FJ', 'text' => __('Fiji', 'event_espresso')),
		array('id' => 'FI', 'text' => __('Finland', 'event_espresso')),
		array('id' => 'FR', 'text' => __('France', 'event_espresso')),
		array('id' => 'GF', 'text' => __('French Guiana', 'event_espresso')),
		array('id' => 'PF', 'text' => __('French Polynesia', 'event_espresso')),
		array('id' => 'TF', 'text' => __('French Southern Territories', 'event_espresso')),
		array('id' => 'GA', 'text' => __('Gabon', 'event_espresso')),
		array('id' => 'GM', 'text' => __('Gambia', 'event_espresso')),
		array('id' => 'GE', 'text' => __('Georgia', 'event_espresso')),
		array('id' => 'DE', 'text' => __('Germany', 'event_espresso')),
		array('id' => 'GH', 'text' => __('Ghana', 'event_espresso')),
		array('id' => 'GI', 'text' => __('Gibraltar', 'event_espresso')),
		array('id' => 'GB', 'text' => __('Great Britain', 'event_espresso')),
		array('id' => 'GR', 'text' => __('Greece', 'event_espresso')),
		array('id' => 'GL', 'text' => __('Greenland', 'event_espresso')),
		array('id' => 'GD', 'text' => __('Grenada', 'event_espresso')),
		array('id' => 'GP', 'text' => __('Guadeloupe', 'event_espresso')),
		array('id' => 'GU', 'text' => __('Guam', 'event_espresso')),
		array('id' => 'GT', 'text' => __('Guatemala', 'event_espresso')),
		array('id' => 'GN', 'text' => __('Guinea', 'event_espresso')),
		array('id' => 'GW', 'text' => __('Guinea Bissau', 'event_espresso')),
		array('id' => 'GY', 'text' => __('Guyana', 'event_espresso')),
		array('id' => 'HT', 'text' => __('Haiti', 'event_espresso')),
		array('id' => 'HM', 'text' => __('Heard and McDonald Islands', 'event_espresso')),
		array('id' => 'HN', 'text' => __('Honduras', 'event_espresso')),
		array('id' => 'HK', 'text' => __('Hong Kong', 'event_espresso')),
		array('id' => 'HU', 'text' => __('Hungary', 'event_espresso')),
		array('id' => 'IS', 'text' => __('Iceland', 'event_espresso')),
		array('id' => 'IN', 'text' => __('India', 'event_espresso')),
		array('id' => 'ID', 'text' => __('Indonesia', 'event_espresso')),
		array('id' => 'IR', 'text' => __('Iran, Islamic Republic of', 'event_espresso')),
		array('id' => 'IQ', 'text' => __('Iraq', 'event_espresso')),
		array('id' => 'IE', 'text' => __('Ireland', 'event_espresso')),
		array('id' => 'IL', 'text' => __('Israel', 'event_espresso')),
		array('id' => 'IT', 'text' => __('Italy', 'event_espresso')),
		array('id' => 'JM', 'text' => __('Jamaica', 'event_espresso')),
		array('id' => 'JP', 'text' => __('Japan', 'event_espresso')),
		array('id' => 'JO', 'text' => __('Jordan', 'event_espresso')),
		array('id' => 'KZ', 'text' => __('Kazakhstan', 'event_espresso')),
		array('id' => 'KE', 'text' => __('Kenya', 'event_espresso')),
		array('id' => 'KI', 'text' => __('Kiribati', 'event_espresso')),
		array('id' => 'KP', 'text' => __('Korea, Democratic PeopleÕs Republic', 'event_espresso')),
		array('id' => 'KR', 'text' => __('Korea, Republic of', 'event_espresso')),
		array('id' => 'KW', 'text' => __('Kuwait', 'event_espresso')),
		array('id' => 'KG', 'text' => __('Kyrgyzstan', 'event_espresso')),
		array('id' => 'LA', 'text' => __('Lao PeopleÕs Democratic Republic', 'event_espresso')),
		array('id' => 'LV', 'text' => __('Latvia', 'event_espresso')),
		array('id' => 'LB', 'text' => __('Lebanon', 'event_espresso')),
		array('id' => 'LI', 'text' => __('Liechtenstein', 'event_espresso')),
		array('id' => 'LS', 'text' => __('Lesotho', 'event_espresso')),
		array('id' => 'LR', 'text' => __('Liberia', 'event_espresso')),
		array('id' => 'LY', 'text' => __('Libyan Arab Jamahiriya', 'event_espresso')),
		array('id' => 'LT', 'text' => __('Lithuania', 'event_espresso')),
		array('id' => 'LU', 'text' => __('Luxembourg', 'event_espresso')),
		array('id' => 'MO', 'text' => __('Macau', 'event_espresso')),
		array('id' => 'MK', 'text' => __('Macedonia, Former Yugoslav Republic of', 'event_espresso')),
		array('id' => 'MG', 'text' => __('Madagascar', 'event_espresso')),
		array('id' => 'MW', 'text' => __('Malawi', 'event_espresso')),
		array('id' => 'MY', 'text' => __('Malaysia', 'event_espresso')),
		array('id' => 'MV', 'text' => __('Maldives', 'event_espresso')),
		array('id' => 'ML', 'text' => __('Mali', 'event_espresso')),
		array('id' => 'MT', 'text' => __('Malta', 'event_espresso')),
		array('id' => 'MH', 'text' => __('Marshall Islands', 'event_espresso')),
		array('id' => 'MQ', 'text' => __('Martinique', 'event_espresso')),
		array('id' => 'MR', 'text' => __('Mauritania', 'event_espresso')),
		array('id' => 'MU', 'text' => __('Mauritius', 'event_espresso')),
		array('id' => 'YT', 'text' => __('Mayotte', 'event_espresso')),
		array('id' => 'MX', 'text' => __('Mexico', 'event_espresso')),
		array('id' => 'FM', 'text' => __('Micronesia, Federated States of', 'event_espresso')),
		array('id' => 'MD', 'text' => __('Moldova, Republic of', 'event_espresso')),
		array('id' => 'MC', 'text' => __('Monaco', 'event_espresso')),
		array('id' => 'MN', 'text' => __('Mongolia', 'event_espresso')),
		array('id' => 'MS', 'text' => __('Montserrat', 'event_espresso')),
		array('id' => 'MA', 'text' => __('Morocco', 'event_espresso')),
		array('id' => 'MZ', 'text' => __('Mozambique', 'event_espresso')),
		array('id' => 'MM', 'text' => __('Myanmar', 'event_espresso')),
		array('id' => 'NA', 'text' => __('Namibia', 'event_espresso')),
		array('id' => 'NR', 'text' => __('Nauru', 'event_espresso')),
		array('id' => 'NP', 'text' => __('Nepal', 'event_espresso')),
		array('id' => 'NL', 'text' => __('Netherlands', 'event_espresso')),
		array('id' => 'AN', 'text' => __('Netherlands Antilles', 'event_espresso')),
		array('id' => 'NC', 'text' => __('New Caledonia', 'event_espresso')),
		array('id' => 'NZ', 'text' => __('New Zealand', 'event_espresso')),
		array('id' => 'NI', 'text' => __('Nicaragua', 'event_espresso')),
		array('id' => 'NE', 'text' => __('Niger', 'event_espresso')),
		array('id' => 'NG', 'text' => __('Nigeria', 'event_espresso')),
		array('id' => 'NU', 'text' => __('Niue', 'event_espresso')),
		array('id' => 'NF', 'text' => __('Norfolk Island', 'event_espresso')),
		array('id' => 'MP', 'text' => __('Northern Mariana Islands', 'event_espresso')),
		array('id' => 'NO', 'text' => __('Norway', 'event_espresso')),
		array('id' => 'OM', 'text' => __('Oman', 'event_espresso')),
		array('id' => 'PK', 'text' => __('Pakistan', 'event_espresso')),
		array('id' => 'PW', 'text' => __('Palau', 'event_espresso')),
		array('id' => 'PS', 'text' => __('Palestinian Territory, Occupied', 'event_espresso')),
		array('id' => 'PA', 'text' => __('Panama', 'event_espresso')),
		array('id' => 'PG', 'text' => __('Papua New Guinea', 'event_espresso')),
		array('id' => 'PY', 'text' => __('Paraguay', 'event_espresso')),
		array('id' => 'PE', 'text' => __('Peru', 'event_espresso')),
		array('id' => 'PH', 'text' => __('Philippines', 'event_espresso')),
		array('id' => 'PN', 'text' => __('Pitcairn', 'event_espresso')),
		array('id' => 'PL', 'text' => __('Poland', 'event_espresso')),
		array('id' => 'PT', 'text' => __('Portugal', 'event_espresso')),
		array('id' => 'PR', 'text' => __('Puerto Rico', 'event_espresso')),
		array('id' => 'QA', 'text' => __('Qatar', 'event_espresso')),
		array('id' => 'RE', 'text' => __('Reunion', 'event_espresso')),
		array('id' => 'RO', 'text' => __('Romania', 'event_espresso')),
		array('id' => 'RU', 'text' => __('Russian Federation', 'event_espresso')),
		array('id' => 'RW', 'text' => __('Rwanda', 'event_espresso')),
		array('id' => 'KN', 'text' => __('Saint Kitts and Nevis', 'event_espresso')),
		array('id' => 'LC', 'text' => __('Saint Lucia', 'event_espresso')),
		array('id' => 'VC', 'text' => __('Saint Vincent and the Grenadines', 'event_espresso')),
		array('id' => 'WX', 'text' => __('Samoa', 'event_espresso')),
		array('id' => 'SM', 'text' => __('San Marino', 'event_espresso')),
		array('id' => 'ST', 'text' => __('Sao Tome and Principe', 'event_espresso')),
		array('id' => 'SA', 'text' => __('Saudi Arabia', 'event_espresso')),
		array('id' => 'SN', 'text' => __('Senegal', 'event_espresso')),
		array('id' => 'CS', 'text' => __('Serbia and Montenegro', 'event_espresso')),
		array('id' => 'SC', 'text' => __('Seychelles', 'event_espresso')),
		array('id' => 'SL', 'text' => __('Sierra Leone', 'event_espresso')),
		array('id' => 'SG', 'text' => __('Singapore', 'event_espresso')),
		array('id' => 'SK', 'text' => __('Slovakia', 'event_espresso')),
		array('id' => 'SI', 'text' => __('Slovenia', 'event_espresso')),
		array('id' => 'SB', 'text' => __('Solomon Islands', 'event_espresso')),
		array('id' => 'SO', 'text' => __('Somalia', 'event_espresso')),
		array('id' => 'ZA', 'text' => __('South Africa', 'event_espresso')),
		array('id' => 'GS', 'text' => __('South Georgia Ð South Sandwich Islands', 'event_espresso')),
		array('id' => 'ES', 'text' => __('Spain', 'event_espresso')),
		array('id' => 'LK', 'text' => __('Sri Lanka', 'event_espresso')),
		array('id' => 'SH', 'text' => __('St. Helena', 'event_espresso')),
		array('id' => 'PM', 'text' => __('St. Pierre and Miquelon', 'event_espresso')),
		array('id' => 'SD', 'text' => __('Sudan', 'event_espresso')),
		array('id' => 'SR', 'text' => __('Suriname', 'event_espresso')),
		array('id' => 'SJ', 'text' => __('Svalbard and Jan Mayen SZ Swaziland', 'event_espresso')),
		array('id' => 'SE', 'text' => __('Sweden', 'event_espresso')),
		array('id' => 'CH', 'text' => __('Switzerland', 'event_espresso')),
		array('id' => 'SY', 'text' => __('Syrian Arab Republic TW Taiwan', 'event_espresso')),
		array('id' => 'TJ', 'text' => __('Tajikistan', 'event_espresso')),
		array('id' => 'TZ', 'text' => __('Tanzania, United Republic of TH Thailand', 'event_espresso')),
		array('id' => 'TG', 'text' => __('Togo', 'event_espresso')),
		array('id' => 'TK', 'text' => __('Tokelau', 'event_espresso')),
		array('id' => 'TO', 'text' => __('Tonga', 'event_espresso')),
		array('id' => 'TT', 'text' => __('Trinidad and Tobago', 'event_espresso')),
		array('id' => 'TN', 'text' => __('Tunisia', 'event_espresso')),
		array('id' => 'TR', 'text' => __('Turkey', 'event_espresso')),
		array('id' => 'TM', 'text' => __('Turkmenistan', 'event_espresso')),
		array('id' => 'TC', 'text' => __('Turks and Caicos Islands', 'event_espresso')),
		array('id' => 'TV', 'text' => __('Tuvalu', 'event_espresso')),
		array('id' => 'UG', 'text' => __('Uganda', 'event_espresso')),
		array('id' => 'UA', 'text' => __('Ukraine', 'event_espresso')),
		array('id' => 'AE', 'text' => __('United Arab Emirates', 'event_espresso')),
		array('id' => 'US', 'text' => __('United States', 'event_espresso')),
		array('id' => 'UM', 'text' => __('United States Minor Outlying Islands', 'event_espresso')),
		array('id' => 'UY', 'text' => __('Uruguay', 'event_espresso')),
		array('id' => 'UZ', 'text' => __('Uzbekistan', 'event_espresso')),
		array('id' => 'VU', 'text' => __('Vanuatu', 'event_espresso')),
		array('id' => 'VA', 'text' => __('Vatican City state', 'event_espresso')),
		array('id' => 'VE', 'text' => __('Venezuela', 'event_espresso')),
		array('id' => 'VN', 'text' => __('Viet Nam', 'event_espresso')),
		array('id' => 'VG', 'text' => __('Virgin Islands (British)', 'event_espresso')),
		array('id' => 'VI', 'text' => __('Virgin Islands (US)', 'event_espresso')),
		array('id' => 'WF', 'text' => __('Wallis and Futuna', 'event_espresso')),
		array('id' => 'EH', 'text' => __('Western Sahara', 'event_espresso')),
		array('id' => 'YE', 'text' => __('Yemen', 'event_espresso')),
		array('id' => 'ZM', 'text' => __('Zambia', 'event_espresso')),
		array('id' => 'ZW', 'text' => __('Zimbabwe', 'event_espresso')));
	global $org_options;
	$beanstream_settings = get_option('event_espresso_beanstream_settings');
	$use_sandbox = $beanstream_settings['beanstream_use_sandbox'];

	wp_register_script( 'beanstream', EVENT_ESPRESSO_PLUGINFULLURL . 'gateways/beanstream/beanstream.js', array( 'jquery.validate.js' ), '1.0', TRUE );
	wp_enqueue_script( 'beanstream' );	
	
	?>
	<div id="beanstream-payment-option-dv" class="payment-option-dv">

		<a id="beanstream-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="beanstream-payment-option-form" style="cursor:pointer;">
			<img alt="Pay using Beanstream" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>gateways/beanstream/beanstream-logo.png">
		</a>	
	
		<div id="beanstream-payment-option-form-dv" class="hide-if-js">
			<div class="event-display-boxes">
			<?php if ($use_sandbox) { ?>
				<div id="sandbox-panel">
					<h2 class="section-title"><?php _e('Beanstream Sandbox Mode', 'event_espresso'); ?></h2>
					<p>Test Master Card # 5100000010001004</p>
					<p>Exp: 10/2012</p>
					<p>CVV2: 123 </p>
					<h3 style="color:#ff0000;" title="Payments will not be processed"><?php _e('Debug Mode Is Turned On', 'event_espresso'); ?></h3>
				</div>
			<?php } ?>
			<?php if ($beanstream_settings['display_header']) { ?>
				<h3 class="payment_header"><?php echo $beanstream_settings['header']; ?></h3>
			<?php }	?>
			
				<div class = "event_espresso_form_wrapper">
					<form id="beanstream_payment_form" name="beanstream_payment_form" method="post" action="<?php echo add_query_arg(array('r_id'=>$registration_id), get_permalink($org_options['return_url'])); ?>">
					
						<fieldset id="beanstream-billing-info-dv">
							<h4 class="section-title"><?php _e('Billing Information', 'event_espresso') ?></h4>
							<p>
								<label for="first_name"><?php _e('First Name', 'event_espresso'); ?></label>
								<input name="first_name" type="text" id="ppp_first_name" class="required" value="<?php echo $fname ?>" />
							</p>
							<p>
								<label for="last_name"><?php _e('Last Name', 'event_espresso'); ?></label>
								<input name="last_name" type="text" id="ppp_last_name" class="required" value="<?php echo $lname ?>" />
							</p>
							<p>
								<label for="email"><?php _e('Email Address', 'event_espresso'); ?></label>
								<input name="email" type="text" id="ppp_email" class="required" value="<?php echo $attendee_email ?>" />
							</p>
							<p>
								<label for="address"><?php _e('Address', 'event_espresso'); ?></label>
								<input name="address" type="text" id="ppp_address" class="required" value="<?php echo $address ?>" />
							</p>
							<p>
								<label for="city"><?php _e('City', 'event_espresso'); ?></label>
								<input name="city" type="text" id="ppp_city" class="required" value="<?php echo $city ?>" />
							</p>
							<p>
								<label for="state">
								<?php _e('State / Province', 'event_espresso'); ?>
								</label>
								<?php
								echo select_input('state', $states, 'AB');
							?>
							</p>
							<p>
								<label for="country">
								<?php _e('Country', 'event_espresso'); ?>
								</label>
								<?php
								$current_country = getCountryFullData($org_options['organization_country']);
								echo select_input('country', $countries, $current_country['iso_code_2']);
								?>
							</p>
							<p>
								<label for="zip"><?php _e('Zip', 'event_espresso'); ?></label>
								<input name="zip" type="text" id="ppp_zip" class="required" value="<?php echo $zip ?>" />
							</p>
							<p>
								<label for="phone"><?php _e('Phone', 'event_espresso'); ?></label>
								<input name="phone" type="text" id="ppp_phone" class="required" value="<?php echo $phone ?>" />
							</p>
						</fieldset>
	
						<fieldset id="beanstream-credit-card-info-dv">
							<p class="section-title"><?php _e('Credit Card Information', 'event_espresso'); ?></p>
							<p>
								<label for="card_num"><?php _e('Card Number', 'event_espresso'); ?></label>
								<input type="text" name="card_num" class="required" id="ppp_card_num" autocomplete="off" />
							</p>
			
			
							<p>				
									<?php 
							$currentMonth=date('m');
							$months=array();
							for($i=0;$i<12;$i++){
								$months[$i]['id']=sprintf("%02s",$i+1);
								$months[$i]['text']=$months[$i]['id'];
							}
						
						?>
						<label for="exp_date"><?php _e('Expiration Month', 'event_espresso'); ?></label>
						<?php echo select_input('expmonth',$months,$currentMonth, 'class="med"' );?>
								
								
								
								
								
								
			
							</p>
			
							<p>
								<label for="exp-year"><?php _e('Expiration Year', 'event_espresso'); ?></label>
								<select id="ppp_exp-year" name ="expyear" class="med required">
			
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
								<input type="text" name="cvv" id="ppp_exp_date" autocomplete="off" class="small" />
							</p>
						</fieldset>
						<input name="amount" type="hidden" value="<?php echo number_format($event_cost, 2) ?>" />
						<input name="beanstream" type="hidden" value="true" />
						<input name="id" type="hidden" value="<?php echo $attendee_id ?>" />
		
						<input name="beanstream_submit" id="beanstream_submit" class="submit-payment-btn allow-leave-page btn_event_form_submit payment-submit" type="submit" value="<?php _e('Complete Purchase', 'event_espresso'); ?>" />
						<span id="processing"></span>
					</form>
				</div><!-- / .event_espresso_or_wrapper -->
			</div>
			
			<br/>
			<p class="choose-diff-pay-option-pg">
				<a class="hide-the-displayed" rel="beanstream-payment-option-form" style="cursor:pointer;"><?php _e('Choose a different payment option', 'event_espresso'); ?></a>
			</p>

		</div>
	</div>
	<?php
}

add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_beanstream');
