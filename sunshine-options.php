<?php
global $sunshine;
if ( is_array( $sunshine->options ) ):

	$options = array();

/* General Options */
$options[] = array( 'name' => __( 'General','sunshine' ), 'type' => 'heading' );

$options[] = array( 'name' => __( 'Localization', 'sunshine' ), 'type' => 'title', 'desc' => '' );
$options[] = array(
	'name' => __( 'Default Country', 'sunshine' ),
	'id'   => 'country',
	'type' => 'select',
	'select2' => true,
	'options' => SunshineCountries::$countries
);

$options[] = array( 'name' => __( 'Taxes', 'sunshine' ), 'type' => 'title', 'desc' => '' );
foreach ( SunshineCountries::$countries as $key => $country ) {
	$states = SunshineCountries::get_states( $key );
	if ( $states ) {
		$tax_options[$key] = $country;
		foreach ( $states as $state_key => $state )
			$tax_options["$key|$state_key"] = $country.' &mdash; '.$state;
	} else
		$tax_options[$key] = $country;
}
asort( $tax_options );
$tax_options = array_merge( array( '' => __( 'Do not use taxes', 'sunshine' ) ), $tax_options );
$options[] = array(
	'name' => __( 'Country / State', 'sunshine' ),
	'desc' => __( 'What country or state should have taxes applied','sunshine' ),
	'id'   => 'tax_location',
	'type' => 'select',
	'select2' => true,
	'options' => $tax_options
);
$options[] = array(
	'name' => __( 'Tax rate (%)', 'sunshine' ),
	'desc' => __( 'Number only', 'sunshine' ),
	'id'   => 'tax_rate',
	'type' => 'text',
	'css' => 'width: 50px;'
);
/*
$options[] = array(
	'name' => __( 'Show all prices with tax included', 'sunshine' ),
	'id'   => 'show_price_including_tax',
	'type' => 'checkbox',
	'tip' => __( 'All prices will have the tax % automatically added to each item price','sunshine' ),
);
*/

$options[] = array( 'name' => __( 'Currency Formatting', 'sunshine' ), 'type' => 'title', 'desc' => '' );

$currencies = apply_filters( 'sunshine_currencies',
	array(
		'AED' => __( 'United Arab Emirates Dirham', 'sunshine' ),
		'AUD' => __( 'Australian Dollars', 'sunshine' ),
		'BDT' => __( 'Bangladeshi Taka', 'sunshine' ),
		'BRL' => __( 'Brazilian Real', 'sunshine' ),
		'BGN' => __( 'Bulgarian Lev', 'sunshine' ),
		'CAD' => __( 'Canadian Dollars', 'sunshine' ),
		'CLP' => __( 'Chilean Peso', 'sunshine' ),
		'CNY' => __( 'Chinese Yuan', 'sunshine' ),
		'COP' => __( 'Colombian Peso', 'sunshine' ),
		'CZK' => __( 'Czech Koruna', 'sunshine' ),
		'DKK' => __( 'Danish Krone', 'sunshine' ),
		'DOP' => __( 'Dominican Peso', 'sunshine' ),
		'EUR' => __( 'Euros', 'sunshine' ),
		'HKD' => __( 'Hong Kong Dollar', 'sunshine' ),
		'HRK' => __( 'Croatia kuna', 'sunshine' ),
		'HUF' => __( 'Hungarian Forint', 'sunshine' ),
		'ISK' => __( 'Icelandic krona', 'sunshine' ),
		'IDR' => __( 'Indonesia Rupiah', 'sunshine' ),
		'INR' => __( 'Indian Rupee', 'sunshine' ),
		'NPR' => __( 'Nepali Rupee', 'sunshine' ),
		'ILS' => __( 'Israeli Shekel', 'sunshine' ),
		'JPY' => __( 'Japanese Yen', 'sunshine' ),
		'KIP' => __( 'Lao Kip', 'sunshine' ),
		'KRW' => __( 'South Korean Won', 'sunshine' ),
		'MYR' => __( 'Malaysian Ringgits', 'sunshine' ),
		'MXN' => __( 'Mexican Peso', 'sunshine' ),
		'NGN' => __( 'Nigerian Naira', 'sunshine' ),
		'NOK' => __( 'Norwegian Krone', 'sunshine' ),
		'NZD' => __( 'New Zealand Dollar', 'sunshine' ),
		'PYG' => __( 'Paraguayan Guaraní', 'sunshine' ),
		'PHP' => __( 'Philippine Pesos', 'sunshine' ),
		'PLN' => __( 'Polish Zloty', 'sunshine' ),
		'GBP' => __( 'Pounds Sterling', 'sunshine' ),
		'QAR' => __( 'Qatari Riyal', 'sunshine' ),
		'RON' => __( 'Romanian Leu', 'sunshine' ),
		'RUB' => __( 'Russian Ruble', 'sunshine' ),
		'SCR' => __( 'Seychelles Rupee', 'sunshine' ),
		'SGD' => __( 'Singapore Dollar', 'sunshine' ),
		'ZAR' => __( 'South African rand', 'sunshine' ),
		'SEK' => __( 'Swedish Krona', 'sunshine' ),
		'CHF' => __( 'Swiss Franc', 'sunshine' ),
		'TWD' => __( 'Taiwan New Dollars', 'sunshine' ),
		'THB' => __( 'Thai Baht', 'sunshine' ),
		'TRY' => __( 'Turkish Lira', 'sunshine' ),
		'UAH' => __( 'Ukrainian Hryvnia', 'sunshine' ),
		'USD' => __( 'US Dollars', 'sunshine' ),
		'VND' => __( 'Vietnamese Dong', 'sunshine' ),
		'EGP' => __( 'Egyptian Pound', 'sunshine' ),
	)
);

$options[] = array(
	'name' => __( 'Currency', 'sunshine' ),
	'id'   => 'currency',
	'type' => 'select',
	'select2' => true,
	'options' => $currencies
);
$options[] = array(
	'name' => __( 'Currency symbol position', 'sunshine' ),
	'id'   => 'currency_symbol_position',
	'type' => 'select',
	'options' => array( 'left' => __( 'Left', 'sunshine' ), 'right' => __( 'Right', 'sunshine' ), 'left_space' => __( 'Left space', 'sunshine' ), 'right_space' => __( 'Right space', 'sunshine' ) )
);
$options[] = array(
	'name' => __( 'Thousands separator', 'sunshine' ),
	'id'   => 'currency_thousands_separator',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Decimal separator', 'sunshine' ),
	'id'   => 'currency_decimal_separator',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Number of decimals', 'sunshine' ),
	'id'   => 'currency_decimals',
	'type' => 'text',
	'css' => 'width: 50px;'
);

$options[] = array( 'name' => __( 'Cart, Checkout and Accounts', 'sunshine' ), 'type' => 'title', 'desc' => '' );
$options[] = array(
	'name' => __( 'Require account to see products', 'sunshine' ),
	'id'   => 'add_to_cart_require_account',
	'type' => 'checkbox',
	'tip' => __( 'Enabling this option means users cannot see products or add them to cart unless they have created an account and are logged in.','sunshine' ),
	'options' => array( 1 => 'Require account' )
);

$options[] = array( 'name' => __( 'URLs', 'sunshine' ), 'type' => 'title', 'desc' => '' );
$options[] = array(
	'name' => __( 'Gallery Endpoint', 'sunshine' ),
	'id'   => 'endpoint_gallery',
	'type' => 'text',
	'desc' => 'Current gallery URL example: <pre style="display: inline;">'.get_permalink( $sunshine->options['page'] ).'<strong>'.$sunshine->options['endpoint_gallery'].'</strong>/gallery-slug</pre>'
);
$options[] = array(
	'name' => __( 'Image Endpoint', 'sunshine' ),
	'id'   => 'endpoint_image',
	'type' => 'text',
	'desc' => 'Current image URL example: <pre style="display: inline;">'.get_permalink( $sunshine->options['page'] ).'<strong>'.$sunshine->options['endpoint_image'].'</strong>/image-slug</pre>'
);
$options[] = array(
	'name' => __( 'Order Endpoint', 'sunshine' ),
	'id'   => 'endpoint_order',
	'type' => 'text',
	'desc' => 'Current order URL example: <pre style="display: inline;">'.get_permalink( $sunshine->options['page'] ).'<strong>'.$sunshine->options['endpoint_order'].'</strong>/42</pre>'
);

$options = apply_filters( 'sunshine_options_general', $options );

/* Pages */
$options[] = array( 'name' => __( 'Pages', 'sunshine' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Page options', 'sunshine' ), 'type' => 'title', 'desc' => __( 'The following pages need selecting so that Sunshine knows where they are. These pages should have been created upon installation, if not you will need to create them.', 'sunshine' ) );

$options[] = array(
	'name' => __( 'Sunshine page', 'sunshine' ),
	'desc' => __( 'Choose which page Sunshine will be displayed on','sunshine' ),
	'id'   => 'page',
	'select2' => true,
	'type' => 'single_select_page'
);
$options[] = array(
	'name' => __( 'Cart', 'sunshine' ),
	'id'   => 'page_cart',
	'select2' => true,
	'type' => 'single_select_page'
);
$options[] = array(
	'name' => __( 'Checkout', 'sunshine' ),
	'id'   => 'page_checkout',
	'select2' => true,
	'type' => 'single_select_page'
);
$options[] = array(
	'name' => __( 'Account', 'sunshine' ),
	'id'   => 'page_account',
	'select2' => true,
	'type' => 'single_select_page'
);
$options = apply_filters( 'sunshine_options_pages', $options );

/* Galleries */
$options[] = array( 'name' => __( 'Galleries', 'sunshine' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Administration Options', 'sunshine' ), 'type' => 'title', 'desc' => '' );

$options[] = array(
	'name' => __( 'Delete Media Library images', 'sunshine' ),
	'id'   => 'delete_images',
	'type' => 'checkbox',
	'tip' => __( 'This will remove all images from the Media Library AND the actual image files from your server when a gallery is permanently deleted','sunshine' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Delete FTP folder', 'sunshine' ),
	'id'   => 'delete_images_folder',
	'type' => 'checkbox',
	'tip' => __( 'This will remove the folder and images added via FTP, if this was used to create the gallery','sunshine' ),
	'options' => array( 1 )
);


$options[] = array( 'name' => __( 'Display Options', 'sunshine' ), 'type' => 'title', 'desc' => '' );
$options[] = array(
	'name' => __( 'Image Order', 'sunshine' ),
	'id'   => 'image_order',
	'type' => 'select',
	'options' => array( 
		'menu_order' => __( 'Custom ordering', 'sunshine' ), 
		'shoot_order' => __( 'Order images shot (based on timestamp from camera)', 'sunshine' ), 
		'date_new_old' => __( 'Image Upload Date (New to Old)', 'sunshine' ), 
		'date_old_new' => __( 'Image Upload Date (Old to New)', 'sunshine' ), 
		'title' => __( 'Alphabetical', 'sunshine' ) 
	)
);

$options[] = array(
	'name' => __( 'Columns', 'sunshine' ),
	'id'   => 'columns',
	'type' => 'select',
	'options' => array( 2 => 2, 3 => 3, 4 => 4, 5 => 5 )
);
$options[] = array(
	'name' => __( 'Rows', 'sunshine' ),
	'id'   => 'rows',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Image Theft Prevention', 'sunshine' ),
	'id'   => 'disable_right_click',
	'type' => 'checkbox',
	'tip' => __( 'Enabling this option will disable the right click menu and also not allow images to be dragged/dropped to the desktop. NOT a 100% effective method, but should stop most people.','sunshine' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Proofing Only', 'sunshine' ),
	'id'   => 'proofing',
	'type' => 'checkbox',
	'tip' => __( 'This will remove all aspects of purchasing abilities throughout the site, leaving just image viewing and adding to favorites','sunshine' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Thumbnail Width', 'sunshine' ),
	'id'   => 'thumbnail_width',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Thumbnail Height', 'sunshine' ),
	'id'   => 'thumbnail_height',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Crop', 'sunshine' ),
	'id'   => 'thumbnail_crop',
	'desc' => sprintf( __( 'Enabling this option will not affect already uploaded images. <a href="%s" target="_blank">Please see this help article</a>','sunshine' ), 'http://www.sunshinephotocart.com/docs/thumbnails-not-cropping/' ),
	'tip' => __( 'Should images be cropped to the exact dimensions of your thumbnail width / height','sunshine' ),
	'type' => 'checkbox',
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Show Image Names', 'sunshine' ),
	'id'   => 'show_image_names',
	'tip' => __( 'Show the file name of the image under the thumbnail','sunshine' ),
	'type' => 'checkbox',
);

$options = apply_filters( 'sunshine_options_galleries', $options );

/* Payment Methods */
$options[] = array( 'name' => __( 'Payments', 'sunshine' ), 'type' => 'heading' );
$options = apply_filters( 'sunshine_options_payment_methods', $options );

/* Shipping */
$options[] = array( 'name' => __( 'Shipping', 'sunshine' ), 'type' => 'heading' );
$options = apply_filters( 'sunshine_options_shipping_methods', $options );

/* Templates */
$options[] = array( 'name' => __( 'Design', 'sunshine' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Design Elements','sunshine' ), 'type' => 'title', 'desc' => '' );
$options[] = array(
	'name' => __( 'Theme', 'sunshine' ),
	'id'   => 'theme',
	'type' => 'select',
	'options' => array(
		'theme' => __( 'My WordPress Theme', 'sunshine' ),
		'default' => __( 'Default Sunshine Theme', 'sunshine' ),
		'2013' => __( 'Sunshine 2013 Theme', 'sunshine' )
	)
);
$attachments = get_posts( array( 'post_type' => 'attachment', 'post_parent' => 0, 'posts_per_page' => 250 ) );
$media[0] = __( 'No image', 'sunshine' );
foreach ( $attachments as $attachment ) {
	$media[$attachment->ID] = $attachment->post_title;
}
$options[] = array(
	'name' => __( 'Logo', 'sunshine' ),
	'id'   => 'template_logo',
	'type' => 'select',
	'options' => $media,
	'select2' => true,
	'desc' => __( 'Logo should be no more than 320px wide transparent PNG. Upload a file to your <a href="upload.php">Media gallery</a>, then select it here','sunshine' )
);
$options = apply_filters( 'sunshine_options_templates', $options );

/* Email Settings */
$options[] = array( 'name' => __( 'Email', 'sunshine' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Order Notifications', 'sunshine' ), 'type' => 'title', 'desc' => '' );

$options[] = array(
	'name' => __( 'Email(s)', 'sunshine' ),
	'desc' => __( 'Email address(es) to receive order notifications. Separate multiple emails with a comma.','sunshine' ),
	'id'   => 'order_notifications',
	'type' => 'text',
);

$options[] = array( 'name' => __( 'Email From', 'sunshine' ), 'type' => 'title', 'desc' => '' );

$options[] = array(
	'name' => __( 'From Name', 'sunshine' ),
	'desc' => __( 'When emails are sent to customers, what name should they come from','sunshine' ),
	'id'   => 'from_name',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'From Email', 'sunshine' ),
	'desc' => __( 'When emails are sent to customers, what email address should they come from','sunshine' ),
	'id'   => 'from_email',
	'type' => 'text',
);

/* Email Subjects */
$options[] = array( 'name' => __( 'Email Subjects', 'sunshine' ), 'type' => 'title', 'desc' => __( 'Allowed template variables are:','sunshine' ).' [sitename], [order_id]' );
$options[] = array(
	'name' => __( 'Register','sunshine' ),
	'id'   => 'email_subject_register',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'Order Receipt','sunshine' ),
	'id'   => 'email_subject_order_receipt',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'Order Status','sunshine' ),
	'id'   => 'email_subject_order_status',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'Order Comment','sunshine' ),
	'id'   => 'email_subject_order_comment',
	'type' => 'text',
);

/* Extra Email Content */
$options[] = array( 'name' => __( 'Email Text', 'sunshine' ), 'type' => 'title', 'desc' => '' );
$options[] = array(
	'name' => __( 'Email Signature','sunshine' ),
	'desc' => __( 'Appears at the end of every email message','sunshine' ),
	'id'   => 'email_signature',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);
$options[] = array(
	'name' => __( 'Receipt', 'sunshine' ),
	'desc' => __( 'Message at the top of email receipts','sunshine' ),
	'id'   => 'email_receipt',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);
$options[] = array(
	'name' => __( 'Registration', 'sunshine' ),
	'desc' => __( 'Message at top of new user registration email','sunshine' ),
	'id'   => 'email_register',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);
$options[] = array(
	'name' => __( 'Order Status', 'sunshine' ),
	'desc' => __( 'Message added to bottom order status change email','sunshine' ),
	'id'   => 'email_order_status',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);
$options = apply_filters( 'sunshine_options_email', $options );

$license_options = apply_filters( 'sunshine_options_licenses', array() );
if ( !empty( $license_options ) ) {
	$options[] = array( 'name' => __( 'Licenses','sunshine' ), 'type' => 'heading', 'desc' => __( 'Manage licenses for your Sunshine add-ons here','sunshine' ) );
	$options = array_merge( $options, $license_options );
}

/*
$options[] = array( 'name' => 'Language', 'type' => 'heading' );
$options[] = array( 'name' => 'Order Status', 'type' => 'title', 'desc' => '' );
*/
/*
$options[] = array( 'name' => 'Samples', 'type' => 'heading' );
$options[] = array(
	'name' => __( 'Age', 'geczy' ),
	'desc' => __( 'What\'s your age, buddy?.', 'geczy' ),
	'tip'  => __( 'It\'s simple, just enter your age!', 'geczy'),
	'id'   => 'number_sample',
	'css' => 'width:70px;',
	'type' => 'number',
	'restrict' => array(
		'min' => 0,
		'max' => 100
	)
);

$options[] = array(
	'name' => __( 'Describe yourself', 'geczy' ),
	'desc' => __( 'Which word describes you best?.', 'geczy' ),
	'tip'  => __( 'If you can\'t choose, I\'ve defaulted an option for you.', 'geczy'),
	'std'  => 'gorgeous',
	'id'   => 'radio_sample',
	'type' => 'radio',
	'options' => array(
		'gorgeous' => 'Gorgeous',
		'pretty' => 'Pretty'
	)
);

$options[] = array(
	'name' => __( 'Biography', 'geczy' ),
	'desc' => __( 'So tell me about yourself.', 'geczy' ),
	'id'   => 'textarea_sample',
	'type' => 'textarea',
);

$options[] = array(
	'name' => __( 'Wordpress page', 'geczy' ),
	'desc' => __( 'Pick your favorite page!', 'geczy' ),
	'tip'  => __( 'Or maybe you don\'t have a favorite?', 'geczy'),
	'id'   => 'single_select_page_sample',
	'type' => 'single_select_page',
);

$options[] = array(
	'name' => __( 'Would you rather have', 'geczy' ),
	'desc' => __( 'Which would you rather have?.', 'geczy' ),
	'id'   => 'select_sample',
	'type' => 'select',
	'options' => array(
		'tenbucks' => 'Ten dollars',
		'redhead' => 'A readheaded girlfriend',
		'tofly' => 'Flying powers',
		'lolwhat' => 'Three hearts',
	)
);

$options[] = array(
	'name' => __( 'Terms', 'geczy' ),
	'desc' => __( 'Agree to my terms...Or else.', 'geczy' ),
	'id'   => 'checkbox_sample',
	'type' => 'checkbox',
);


$options[] = array(
	'name' => __( 'Awesome', 'geczy' ),
	'desc' => __( 'Is this awesome or what?', 'geczy' ),
	'id'   => 'checkbox_sample2',
	'type' => 'checkbox',
);
*/

$options = apply_filters( 'sunshine_options_extra', $options );

endif;