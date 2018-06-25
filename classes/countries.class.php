<?php
class SunshineCountries extends SunshineSingleton {

	public static $countries;
	public static $states;

	function __construct() {

		self::$countries = apply_filters( 'sunshine_countries', array(
				'AF' => __( 'Afghanistan', 'sunshine' ),
				'AX' => __( '&#197;land Islands', 'sunshine' ),
				'AL' => __( 'Albania', 'sunshine' ),
				'DZ' => __( 'Algeria', 'sunshine' ),
				'AS' => __( 'American Samoa', 'sunshine' ),
				'AD' => __( 'Andorra', 'sunshine' ),
				'AO' => __( 'Angola', 'sunshine' ),
				'AI' => __( 'Anguilla', 'sunshine' ),
				'AQ' => __( 'Antarctica', 'sunshine' ),
				'AG' => __( 'Antigua and Barbuda', 'sunshine' ),
				'AR' => __( 'Argentina', 'sunshine' ),
				'AM' => __( 'Armenia', 'sunshine' ),
				'AW' => __( 'Aruba', 'sunshine' ),
				'AU' => __( 'Australia', 'sunshine' ),
				'AT' => __( 'Austria', 'sunshine' ),
				'AZ' => __( 'Azerbaijan', 'sunshine' ),
				'BS' => __( 'Bahamas', 'sunshine' ),
				'BH' => __( 'Bahrain', 'sunshine' ),
				'BD' => __( 'Bangladesh', 'sunshine' ),
				'BB' => __( 'Barbados', 'sunshine' ),
				'BY' => __( 'Belarus', 'sunshine' ),
				'BE' => __( 'Belgium', 'sunshine' ),
				'BZ' => __( 'Belize', 'sunshine' ),
				'BJ' => __( 'Benin', 'sunshine' ),
				'BM' => __( 'Bermuda', 'sunshine' ),
				'BT' => __( 'Bhutan', 'sunshine' ),
				'BO' => __( 'Bolivia', 'sunshine' ),
				'BA' => __( 'Bosnia and Herzegovina', 'sunshine' ),
				'BW' => __( 'Botswana', 'sunshine' ),
				'BR' => __( 'Brazil', 'sunshine' ),
				'IO' => __( 'British Indian Ocean Territory', 'sunshine' ),
				'VG' => __( 'British Virgin Islands', 'sunshine' ),
				'BN' => __( 'Brunei', 'sunshine' ),
				'BG' => __( 'Bulgaria', 'sunshine' ),
				'BF' => __( 'Burkina Faso', 'sunshine' ),
				'BI' => __( 'Burundi', 'sunshine' ),
				'KH' => __( 'Cambodia', 'sunshine' ),
				'CM' => __( 'Cameroon', 'sunshine' ),
				'CA' => __( 'Canada', 'sunshine' ),
				'CV' => __( 'Cape Verde', 'sunshine' ),
				'KY' => __( 'Cayman Islands', 'sunshine' ),
				'CF' => __( 'Central African Republic', 'sunshine' ),
				'TD' => __( 'Chad', 'sunshine' ),
				'CL' => __( 'Chile', 'sunshine' ),
				'CN' => __( 'China', 'sunshine' ),
				'CX' => __( 'Christmas Island', 'sunshine' ),
				'CC' => __( 'Cocos (Keeling) Islands', 'sunshine' ),
				'CO' => __( 'Colombia', 'sunshine' ),
				'KM' => __( 'Comoros', 'sunshine' ),
				'CG' => __( 'Congo (Brazzaville)', 'sunshine' ),
				'CD' => __( 'Congo (Kinshasa)', 'sunshine' ),
				'CK' => __( 'Cook Islands', 'sunshine' ),
				'CR' => __( 'Costa Rica', 'sunshine' ),
				'HR' => __( 'Croatia', 'sunshine' ),
				'CU' => __( 'Cuba', 'sunshine' ),
				'CY' => __( 'Cyprus', 'sunshine' ),
				'CZ' => __( 'Czech Republic', 'sunshine' ),
				'DK' => __( 'Denmark', 'sunshine' ),
				'DJ' => __( 'Djibouti', 'sunshine' ),
				'DM' => __( 'Dominica', 'sunshine' ),
				'DO' => __( 'Dominican Republic', 'sunshine' ),
				'EC' => __( 'Ecuador', 'sunshine' ),
				'EG' => __( 'Egypt', 'sunshine' ),
				'SV' => __( 'El Salvador', 'sunshine' ),
				'GQ' => __( 'Equatorial Guinea', 'sunshine' ),
				'ER' => __( 'Eritrea', 'sunshine' ),
				'EE' => __( 'Estonia', 'sunshine' ),
				'ET' => __( 'Ethiopia', 'sunshine' ),
				'FK' => __( 'Falkland Islands', 'sunshine' ),
				'FO' => __( 'Faroe Islands', 'sunshine' ),
				'FJ' => __( 'Fiji', 'sunshine' ),
				'FI' => __( 'Finland', 'sunshine' ),
				'FR' => __( 'France', 'sunshine' ),
				'GF' => __( 'French Guiana', 'sunshine' ),
				'PF' => __( 'French Polynesia', 'sunshine' ),
				'TF' => __( 'French Southern Territories', 'sunshine' ),
				'GA' => __( 'Gabon', 'sunshine' ),
				'GM' => __( 'Gambia', 'sunshine' ),
				'GE' => __( 'Georgia', 'sunshine' ),
				'DE' => __( 'Germany', 'sunshine' ),
				'GH' => __( 'Ghana', 'sunshine' ),
				'GI' => __( 'Gibraltar', 'sunshine' ),
				'GR' => __( 'Greece', 'sunshine' ),
				'GL' => __( 'Greenland', 'sunshine' ),
				'GD' => __( 'Grenada', 'sunshine' ),
				'GP' => __( 'Guadeloupe', 'sunshine' ),
				'GU' => __( 'Guam', 'sunshine' ),
				'GT' => __( 'Guatemala', 'sunshine' ),
				'GG' => __( 'Guernsey', 'sunshine' ),
				'GN' => __( 'Guinea', 'sunshine' ),
				'GW' => __( 'Guinea-Bissau', 'sunshine' ),
				'GY' => __( 'Guyana', 'sunshine' ),
				'HT' => __( 'Haiti', 'sunshine' ),
				'HN' => __( 'Honduras', 'sunshine' ),
				'HK' => __( 'Hong Kong', 'sunshine' ),
				'HU' => __( 'Hungary', 'sunshine' ),
				'IS' => __( 'Iceland', 'sunshine' ),
				'IN' => __( 'India', 'sunshine' ),
				'ID' => __( 'Indonesia', 'sunshine' ),
				'IR' => __( 'Iran', 'sunshine' ),
				'IQ' => __( 'Iraq', 'sunshine' ),
				'IE' => __( 'Republic of Ireland', 'sunshine' ),
				'IM' => __( 'Isle of Man', 'sunshine' ),
				'IL' => __( 'Israel', 'sunshine' ),
				'IT' => __( 'Italy', 'sunshine' ),
				'CI' => __( 'Ivory Coast', 'sunshine' ),
				'JM' => __( 'Jamaica', 'sunshine' ),
				'JP' => __( 'Japan', 'sunshine' ),
				'JE' => __( 'Jersey', 'sunshine' ),
				'JO' => __( 'Jordan', 'sunshine' ),
				'KZ' => __( 'Kazakhstan', 'sunshine' ),
				'KE' => __( 'Kenya', 'sunshine' ),
				'KI' => __( 'Kiribati', 'sunshine' ),
				'KW' => __( 'Kuwait', 'sunshine' ),
				'KG' => __( 'Kyrgyzstan', 'sunshine' ),
				'LA' => __( 'Laos', 'sunshine' ),
				'LV' => __( 'Latvia', 'sunshine' ),
				'LB' => __( 'Lebanon', 'sunshine' ),
				'LS' => __( 'Lesotho', 'sunshine' ),
				'LR' => __( 'Liberia', 'sunshine' ),
				'LY' => __( 'Libya', 'sunshine' ),
				'LI' => __( 'Liechtenstein', 'sunshine' ),
				'LT' => __( 'Lithuania', 'sunshine' ),
				'LU' => __( 'Luxembourg', 'sunshine' ),
				'MO' => __( 'Macao S.A.R., China', 'sunshine' ),
				'MK' => __( 'Macedonia', 'sunshine' ),
				'MG' => __( 'Madagascar', 'sunshine' ),
				'MW' => __( 'Malawi', 'sunshine' ),
				'MY' => __( 'Malaysia', 'sunshine' ),
				'MV' => __( 'Maldives', 'sunshine' ),
				'ML' => __( 'Mali', 'sunshine' ),
				'MT' => __( 'Malta', 'sunshine' ),
				'MH' => __( 'Marshall Islands', 'sunshine' ),
				'MQ' => __( 'Martinique', 'sunshine' ),
				'MR' => __( 'Mauritania', 'sunshine' ),
				'MU' => __( 'Mauritius', 'sunshine' ),
				'YT' => __( 'Mayotte', 'sunshine' ),
				'MX' => __( 'Mexico', 'sunshine' ),
				'FM' => __( 'Micronesia', 'sunshine' ),
				'MD' => __( 'Moldova', 'sunshine' ),
				'MC' => __( 'Monaco', 'sunshine' ),
				'MN' => __( 'Mongolia', 'sunshine' ),
				'ME' => __( 'Montenegro', 'sunshine' ),
				'MS' => __( 'Montserrat', 'sunshine' ),
				'MA' => __( 'Morocco', 'sunshine' ),
				'MZ' => __( 'Mozambique', 'sunshine' ),
				'MM' => __( 'Myanmar', 'sunshine' ),
				'NA' => __( 'Namibia', 'sunshine' ),
				'NR' => __( 'Nauru', 'sunshine' ),
				'NP' => __( 'Nepal', 'sunshine' ),
				'NL' => __( 'Netherlands', 'sunshine' ),
				'AN' => __( 'Netherlands Antilles', 'sunshine' ),
				'NC' => __( 'New Caledonia', 'sunshine' ),
				'NZ' => __( 'New Zealand', 'sunshine' ),
				'NI' => __( 'Nicaragua', 'sunshine' ),
				'NE' => __( 'Niger', 'sunshine' ),
				'NG' => __( 'Nigeria', 'sunshine' ),
				'NU' => __( 'Niue', 'sunshine' ),
				'NF' => __( 'Norfolk Island', 'sunshine' ),
				'KP' => __( 'North Korea', 'sunshine' ),
				'MP' => __( 'Northern Mariana Islands', 'sunshine' ),
				'NO' => __( 'Norway', 'sunshine' ),
				'OM' => __( 'Oman', 'sunshine' ),
				'PK' => __( 'Pakistan', 'sunshine' ),
				'PW' => __( 'Palau', 'sunshine' ),
				'PS' => __( 'Palestinian Territory', 'sunshine' ),
				'PA' => __( 'Panama', 'sunshine' ),
				'PG' => __( 'Papua New Guinea', 'sunshine' ),
				'PY' => __( 'Paraguay', 'sunshine' ),
				'PE' => __( 'Peru', 'sunshine' ),
				'PH' => __( 'Philippines', 'sunshine' ),
				'PN' => __( 'Pitcairn', 'sunshine' ),
				'PL' => __( 'Poland', 'sunshine' ),
				'PT' => __( 'Portugal', 'sunshine' ),
				'PR' => __( 'Puerto Rico', 'sunshine' ),
				'QA' => __( 'Qatar', 'sunshine' ),
				'RE' => __( 'Reunion', 'sunshine' ),
				'RO' => __( 'Romania', 'sunshine' ),
				'RU' => __( 'Russia', 'sunshine' ),
				'RW' => __( 'Rwanda', 'sunshine' ),
				'BL' => __( 'Saint Barth&eacute;lemy', 'sunshine' ),
				'SH' => __( 'Saint Helena', 'sunshine' ),
				'KN' => __( 'Saint Kitts and Nevis', 'sunshine' ),
				'LC' => __( 'Saint Lucia', 'sunshine' ),
				'MF' => __( 'Saint Martin (French part)', 'sunshine' ),
				'PM' => __( 'Saint Pierre and Miquelon', 'sunshine' ),
				'VC' => __( 'Saint Vincent and the Grenadines', 'sunshine' ),
				'WS' => __( 'Samoa', 'sunshine' ),
				'SM' => __( 'San Marino', 'sunshine' ),
				'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'sunshine' ),
				'SA' => __( 'Saudi Arabia', 'sunshine' ),
				'SN' => __( 'Senegal', 'sunshine' ),
				'RS' => __( 'Serbia', 'sunshine' ),
				'SC' => __( 'Seychelles', 'sunshine' ),
				'SL' => __( 'Sierra Leone', 'sunshine' ),
				'SG' => __( 'Singapore', 'sunshine' ),
				'SK' => __( 'Slovakia', 'sunshine' ),
				'SI' => __( 'Slovenia', 'sunshine' ),
				'SB' => __( 'Solomon Islands', 'sunshine' ),
				'SO' => __( 'Somalia', 'sunshine' ),
				'ZA' => __( 'South Africa', 'sunshine' ),
				'GS' => __( 'South Georgia/Sandwich Islands', 'sunshine' ),
				'KR' => __( 'South Korea', 'sunshine' ),
				'ES' => __( 'Spain', 'sunshine' ),
				'LK' => __( 'Sri Lanka', 'sunshine' ),
				'SD' => __( 'Sudan', 'sunshine' ),
				'SR' => __( 'Suriname', 'sunshine' ),
				'SJ' => __( 'Svalbard and Jan Mayen', 'sunshine' ),
				'SZ' => __( 'Swaziland', 'sunshine' ),
				'SE' => __( 'Sweden', 'sunshine' ),
				'CH' => __( 'Switzerland', 'sunshine' ),
				'SY' => __( 'Syria', 'sunshine' ),
				'TW' => __( 'Taiwan', 'sunshine' ),
				'TJ' => __( 'Tajikistan', 'sunshine' ),
				'TZ' => __( 'Tanzania', 'sunshine' ),
				'TH' => __( 'Thailand', 'sunshine' ),
				'TL' => __( 'Timor-Leste', 'sunshine' ),
				'TG' => __( 'Togo', 'sunshine' ),
				'TK' => __( 'Tokelau', 'sunshine' ),
				'TO' => __( 'Tonga', 'sunshine' ),
				'TT' => __( 'Trinidad and Tobago', 'sunshine' ),
				'TN' => __( 'Tunisia', 'sunshine' ),
				'TR' => __( 'Turkey', 'sunshine' ),
				'TM' => __( 'Turkmenistan', 'sunshine' ),
				'TC' => __( 'Turks and Caicos Islands', 'sunshine' ),
				'TV' => __( 'Tuvalu', 'sunshine' ),
				'VI' => __( 'U.S. Virgin Islands', 'sunshine' ),
				'USAF' => __( 'US Armed Forces', 'sunshine' ),
				'UM' => __( 'US Minor Outlying Islands', 'sunshine' ),
				'UG' => __( 'Uganda', 'sunshine' ),
				'UA' => __( 'Ukraine', 'sunshine' ),
				'AE' => __( 'United Arab Emirates', 'sunshine' ),
				'GB' => __( 'United Kingdom', 'sunshine' ),
				'US' => __( 'United States', 'sunshine' ),
				'UY' => __( 'Uruguay', 'sunshine' ),
				'UZ' => __( 'Uzbekistan', 'sunshine' ),
				'VU' => __( 'Vanuatu', 'sunshine' ),
				'VA' => __( 'Vatican', 'sunshine' ),
				'VE' => __( 'Venezuela', 'sunshine' ),
				'VN' => __( 'Vietnam', 'sunshine' ),
				'WF' => __( 'Wallis and Futuna', 'sunshine' ),
				'EH' => __( 'Western Sahara', 'sunshine' ),
				'YE' => __( 'Yemen', 'sunshine' ),
				'ZM' => __( 'Zambia', 'sunshine' ),
				'ZW' => __( 'Zimbabwe', 'sunshine' )
			) );

		self::$states = apply_filters( 'sunshine_states', array(
				'AU' => array(
					'ACT' => __( 'Australian Capital Territory', 'sunshine' ) ,
					'NSW' => __( 'New South Wales', 'sunshine' ) ,
					'NT' => __( 'Northern Territory', 'sunshine' ) ,
					'QLD' => __( 'Queensland', 'sunshine' ) ,
					'SA' => __( 'South Australia', 'sunshine' ) ,
					'TAS' => __( 'Tasmania', 'sunshine' ) ,
					'VIC' => __( 'Victoria', 'sunshine' ) ,
					'WA' => __( 'Western Australia', 'sunshine' )
				),
				'BR' => array(
					'AM' => __( 'Amazonas', 'sunshine' ),
					'AC' => __( 'Acre', 'sunshine' ),
					'AL' => __( 'Alagoas', 'sunshine' ),
					'AP' => __( 'Amap&aacute;', 'sunshine' ),
					'CE' => __( 'Cear&aacute;', 'sunshine' ),
					'DF' => __( 'Distrito Federal', 'sunshine' ),
					'ES' => __( 'Esp&iacute;rito Santo', 'sunshine' ),
					'MA' => __( 'Maranh&atilde;o', 'sunshine' ),
					'PR' => __( 'Paran&aacute;', 'sunshine' ),
					'PE' => __( 'Pernambuco', 'sunshine' ),
					'PI' => __( 'Piau&iacute;', 'sunshine' ),
					'RN' => __( 'Rio Grande do Norte', 'sunshine' ),
					'RS' => __( 'Rio Grande do Sul', 'sunshine' ),
					'RO' => __( 'Rond&ocirc;nia', 'sunshine' ),
					'RR' => __( 'Roraima', 'sunshine' ),
					'SC' => __( 'Santa Catarina', 'sunshine' ),
					'SE' => __( 'Sergipe', 'sunshine' ),
					'TO' => __( 'Tocantins', 'sunshine' ),
					'PA' => __( 'Par&aacute;', 'sunshine' ),
					'BH' => __( 'Bahia', 'sunshine' ),
					'GO' => __( 'Goi&aacute;s', 'sunshine' ),
					'MT' => __( 'Mato Grosso', 'sunshine' ),
					'MS' => __( 'Mato Grosso do Sul', 'sunshine' ),
					'RJ' => __( 'Rio de Janeiro', 'sunshine' ),
					'SP' => __( 'S&atilde;o Paulo', 'sunshine' ),
					'RS' => __( 'Rio Grande do Sul', 'sunshine' ),
					'MG' => __( 'Minas Gerais', 'sunshine' ),
					'PB' => __( 'Para&iacute;ba', 'sunshine' ),
				),
				'CA' => array(
					'AB' => __( 'Alberta', 'sunshine' ) ,
					'BC' => __( 'British Columbia', 'sunshine' ) ,
					'MB' => __( 'Manitoba', 'sunshine' ) ,
					'NB' => __( 'New Brunswick', 'sunshine' ) ,
					'NF' => __( 'Newfoundland', 'sunshine' ) ,
					'NT' => __( 'Northwest Territories', 'sunshine' ) ,
					'NS' => __( 'Nova Scotia', 'sunshine' ) ,
					'NU' => __( 'Nunavut', 'sunshine' ) ,
					'ON' => __( 'Ontario', 'sunshine' ) ,
					'PE' => __( 'Prince Edward Island', 'sunshine' ) ,
					'QC' => __( 'Quebec', 'sunshine' ) ,
					'SK' => __( 'Saskatchewan', 'sunshine' ) ,
					'YT' => __( 'Yukon Territory', 'sunshine' )
				),
				'HK' => array(
					'HONG KONG' => __( 'Hong Kong Island', 'sunshine' ),
					'KOWLOON' => __( 'Kowloon', 'sunshine' ),
					'NEW TERRITORIES' => __( 'New Territories', 'sunshine' )
				),
				'NL' => array(
					'DR' => __( 'Drenthe', 'sunshine' ) ,
					'FL' => __( 'Flevoland', 'sunshine' ) ,
					'FR' => __( 'Friesland', 'sunshine' ) ,
					'GLD' => __( 'Gelderland', 'sunshine' ) ,
					'GRN' => __( 'Groningen', 'sunshine' ) ,
					'LB' => __( 'Limburg', 'sunshine' ) ,
					'NB' => __( 'Noord-Brabant', 'sunshine' ) ,
					'NH' => __( 'Noord-Holland', 'sunshine' ) ,
					'OV' => __( 'Overijssel', 'sunshine' ) ,
					'UT' => __( 'Utrecht', 'sunshine' ) ,
					'ZLD' => __( 'Zeeland', 'sunshine' ) ,
					'ZH' => __( 'Zuid-Holland', 'sunshine' ) ,
				),
				'NZ' => array(
					'NL' => __( 'Northland', 'sunshine' ) ,
					'AK' => __( 'Auckland', 'sunshine' ) ,
					'WA' => __( 'Waikato', 'sunshine' ) ,
					'BP' => __( 'Bay of Plenty', 'sunshine' ) ,
					'TK' => __( 'Taranaki', 'sunshine' ) ,
					'HB' => __( 'Hawke&rsquo;s Bay', 'sunshine' ) ,
					'MW' => __( 'Manawatu-Wanganui', 'sunshine' ) ,
					'WE' => __( 'Wellington', 'sunshine' ) ,
					'NS' => __( 'Nelson', 'sunshine' ) ,
					'MB' => __( 'Marlborough', 'sunshine' ) ,
					'TM' => __( 'Tasman', 'sunshine' ) ,
					'WC' => __( 'West Coast', 'sunshine' ) ,
					'CT' => __( 'Canterbury', 'sunshine' ) ,
					'OT' => __( 'Otago', 'sunshine' ) ,
					'SL' => __( 'Southland', 'sunshine' ) ,
				),
				'US' => array(
					'AL' => __( 'Alabama', 'sunshine' ) ,
					'AK' => __( 'Alaska', 'sunshine' ) ,
					'AZ' => __( 'Arizona', 'sunshine' ) ,
					'AR' => __( 'Arkansas', 'sunshine' ) ,
					'CA' => __( 'California', 'sunshine' ) ,
					'CO' => __( 'Colorado', 'sunshine' ) ,
					'CT' => __( 'Connecticut', 'sunshine' ) ,
					'DE' => __( 'Delaware', 'sunshine' ) ,
					'DC' => __( 'District Of Columbia', 'sunshine' ) ,
					'FL' => __( 'Florida', 'sunshine' ) ,
					'GA' => __( 'Georgia', 'sunshine' ) ,
					'HI' => __( 'Hawaii', 'sunshine' ) ,
					'ID' => __( 'Idaho', 'sunshine' ) ,
					'IL' => __( 'Illinois', 'sunshine' ) ,
					'IN' => __( 'Indiana', 'sunshine' ) ,
					'IA' => __( 'Iowa', 'sunshine' ) ,
					'KS' => __( 'Kansas', 'sunshine' ) ,
					'KY' => __( 'Kentucky', 'sunshine' ) ,
					'LA' => __( 'Louisiana', 'sunshine' ) ,
					'ME' => __( 'Maine', 'sunshine' ) ,
					'MD' => __( 'Maryland', 'sunshine' ) ,
					'MA' => __( 'Massachusetts', 'sunshine' ) ,
					'MI' => __( 'Michigan', 'sunshine' ) ,
					'MN' => __( 'Minnesota', 'sunshine' ) ,
					'MS' => __( 'Mississippi', 'sunshine' ) ,
					'MO' => __( 'Missouri', 'sunshine' ) ,
					'MT' => __( 'Montana', 'sunshine' ) ,
					'NE' => __( 'Nebraska', 'sunshine' ) ,
					'NV' => __( 'Nevada', 'sunshine' ) ,
					'NH' => __( 'New Hampshire', 'sunshine' ) ,
					'NJ' => __( 'New Jersey', 'sunshine' ) ,
					'NM' => __( 'New Mexico', 'sunshine' ) ,
					'NY' => __( 'New York', 'sunshine' ) ,
					'NC' => __( 'North Carolina', 'sunshine' ) ,
					'ND' => __( 'North Dakota', 'sunshine' ) ,
					'OH' => __( 'Ohio', 'sunshine' ) ,
					'OK' => __( 'Oklahoma', 'sunshine' ) ,
					'OR' => __( 'Oregon', 'sunshine' ) ,
					'PA' => __( 'Pennsylvania', 'sunshine' ) ,
					'RI' => __( 'Rhode Island', 'sunshine' ) ,
					'SC' => __( 'South Carolina', 'sunshine' ) ,
					'SD' => __( 'South Dakota', 'sunshine' ) ,
					'TN' => __( 'Tennessee', 'sunshine' ) ,
					'TX' => __( 'Texas', 'sunshine' ) ,
					'UT' => __( 'Utah', 'sunshine' ) ,
					'VT' => __( 'Vermont', 'sunshine' ) ,
					'VA' => __( 'Virginia', 'sunshine' ) ,
					'WA' => __( 'Washington', 'sunshine' ) ,
					'WV' => __( 'West Virginia', 'sunshine' ) ,
					'WI' => __( 'Wisconsin', 'sunshine' ) ,
					'WY' => __( 'Wyoming', 'sunshine' )
				),
				'USAF' => array(
					'AA' => __( 'Americas', 'sunshine' ) ,
					'AE' => __( 'Europe', 'sunshine' ) ,
					'AP' => __( 'Pacific', 'sunshine' )
				)
			) );
	}

	public static function get_states( $country ) {
		if ( isset( self::$states[$country] ) )
			return self::$states[$country];
	}

	public static function get_allowed_countries() {
		global $sunshine;
		if ( empty( $sunshine->options['allowed_countries'] ) || !is_array( $sunshine->options['allowed_countries'] ) || in_array( 'all', $sunshine->options['allowed_countries'] ) ) {
			return;
		}
		$allowed_countries = array();
		foreach ( $sunshine->options['allowed_countries'] as $code ) {
			$allowed_countries[ $code ] = self::$countries[ $code ];
		}
		return $allowed_countries;
	}

	public static function country_only_dropdown( $name = 'country', $selected = '' ) {
		global $sunshine;
		$countries = self::get_allowed_countries();
		if ( empty( $countries ) )
			$countries = self::$countries;
		if ( $selected == '' )
			$selected = $sunshine->options['country'];
		if ( !empty( $countries ) ) {
			asort( $countries );
			echo '<select name="'.$name.'"><option value="">'.__( 'Select country', 'sunshine' ).'</option>';
			foreach ( $countries as $key => $value )
				echo '<option value="'.$key.'" '.selected( $key, $selected, 0 ).'>'.$value.'</option>';
			echo '</select>';
		}
	}

	public static function countries_dropdown( $name = 'state', $selected = '' ) {
		if ( !empty( self::$countries ) ) :
			asort( self::$countries );
			foreach ( self::$countries as $key=>$value ) :
				if ( $states =  self::get_states( $key ) ) :
					echo '<optgroup label="'.$value.'">';
				foreach ( $states as $state_key=>$state_value ) :
					echo '<option value="'.$key.'|'.$state_key.'" '.selected( $key.'|'.$state_key, $selected, 0 ).'>'.$value.' &mdash; '.$state_value.'</option>';
				endforeach;
			echo '</optgroup>';
		else :
			echo '<option value="'.$key.'" '.selected( $state_key, $selected, 0 ).'>'. ( $escape ? esc_js( $value ) : $value ) .'</option>';
		endif;
		endforeach;
		endif;

	}

	public static function state_dropdown( $country, $name = 'state', $selected = '' ) {
		global $sunshine;
		$states = array();
		if ( $country == '' )
			$country = $sunshine->options['country'];
		if ( isset( SunshineCountries::$states[$country] ) )
			$states = SunshineCountries::$states[$country];
		if ( $selected == '' )
			$selected = SunshineUser::get_user_meta( $name );
		if ( $states ) {
			echo '<select name="'.$name.'">';
			echo '<option value="">'.__( 'Select state','sunshine' ).'</option>';
			foreach ( $states as $code => $name ) {
				echo '<option value="'.$code.'" '.selected( $selected, $code, 0 ).'>'.$name.'</option>';
			}
			echo '</select>';
		} else
			echo '<input type="text" name="'.$name.'" value="'.$selected.'" />';
	}

}

SunshineCountries::instance();
