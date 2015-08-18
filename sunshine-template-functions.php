<?php
/******************************
	GETTING URLS
******************************/
function sunshine_url( $page='home', $args = array() ) {
	global $sunshine;
	$url = '';
	if ( $page == 'home' ) {
		$url = get_permalink( $sunshine->options['page'] );
	} elseif ( array_key_exists( $page, $sunshine->pages ) ) {
		$url = get_permalink( $sunshine->pages[$page] );
	}
	if ( isset( $args['query_vars'] ) ) {
		foreach ( $args['query_vars'] as $name => $value ) {
			$param_pairs[] = $name.'='.$value;
		}
	} else
		$param_pairs = '';
	if ( !empty( $param_pairs ) )
		$url .= '?'.join( '&amp;',$param_pairs );
	$url = apply_filters( 'sunshine_url', $url, $args );
	return $url;
}

function sunshine_current_url( $echo = 1 ) {
	$url = $_SERVER["REQUEST_URI"];
	$url = apply_filters( 'sunshine_current_url', $url );
	if ( $echo )
		echo $url;
	else
		return $url;
}



/******************************
	GENERAL
******************************/
/*
* 	Format a number with currency signs
*
*	@params 	$value = Number to be formatted
*				$echo = Display the result or not
*	@return Number formatted with currency (optional)
*/
function sunshine_money_format( $value, $echo=true ) {
	global $sunshine;
	if ( !$value )
		$value = 0;
	/*
	if ($sunshine->options['show_price_including_tax'])
		$value = $value + max(0, $value * ($sunshine->options['tax_rate']/100));
	*/
	if ( is_numeric( $value ) ) {
		$formatted_value = number_format( $value, stripslashes( $sunshine->options['currency_decimals'] ), stripslashes( $sunshine->options['currency_decimal_separator'] ), stripslashes( $sunshine->options['currency_thousands_separator'] ) );

		$currency_symbol = sunshine_currency_symbol();

		switch ( stripslashes( $sunshine->options['currency_symbol_position'] ) ) {
		case 'left' :
			$format = '%1$s%2$s';
			break;
		case 'right' :
			$format = '%2$s%1$s';
			break;
		case 'left_space' :
			$format = '%1$s&nbsp;%2$s';
			break;
		case 'right_space' :
			$format = '%2$s&nbsp;%1$s';
			break;
		}

		$formatted_value = sprintf( $format, $currency_symbol, $formatted_value );

		if ( $echo )
			echo $formatted_value;
		else
			return $formatted_value;
	}
	return $value;
}

function sunshine_currency_symbol() {
	global $sunshine;
	switch ( $sunshine->options['currency'] ) {
	case 'BRL' :
		$currency_symbol = '&#82;&#36;';
		break;
	case 'AUD' :
	case 'CAD' :
	case 'MXN' :
	case 'NZD' :
	case 'HKD' :
	case 'SGD' :
	case 'USD' :
		$currency_symbol = '&#36;';
		break;
	case 'EUR' :
		$currency_symbol = '&euro;';
		break;
	case 'CNY' :
	case 'RMB' :
	case 'JPY' :
		$currency_symbol = '&yen;';
		break;
	case 'TRY' : $currency_symbol = '&#84;&#76;'; break;
	case 'NOK' : $currency_symbol = '&#107;&#114;'; break;
	case 'ZAR' : $currency_symbol = '&#82;'; break;
	case 'CZK' : $currency_symbol = '&#75;&#269;'; break;
	case 'MYR' : $currency_symbol = '&#82;&#77;'; break;
	case 'DKK' : $currency_symbol = '&#107;&#114;'; break;
	case 'HUF' : $currency_symbol = '&#70;&#116;'; break;
	case 'IDR' : $currency_symbol = 'Rp'; break;
	case 'SCR' : $currency_symbol = '&#8360;'; break;
	case 'ILS' : $currency_symbol = '&#8362;'; break;
	case 'PHP' : $currency_symbol = '&#8369;'; break;
	case 'PLN' : $currency_symbol = '&#122;&#322;'; break;
	case 'SEK' : $currency_symbol = '&#107;&#114;'; break;
	case 'CHF' : $currency_symbol = '&#67;&#72;&#70;'; break;
	case 'TWD' : $currency_symbol = '&#78;&#84;&#36;'; break;
	case 'THB' : $currency_symbol = '&#3647;'; break;
	case 'GBP' : $currency_symbol = '&pound;'; break;
	case 'RON' : $currency_symbol = 'lei'; break;
	default    : $currency_symbol = ''; break;
	}
	return $currency_symbol;
}

function sunshine_currency_symbol_format() {
	global $sunshine;
	switch ( stripslashes( $sunshine->options['currency_symbol_position'] ) ) {
	case 'left' :
		$format = '%1$s%2$s';
		break;
	case 'right' :
		$format = '%2$s%1$s';
		break;
	case 'left_space' :
		$format = '%1$s&nbsp;%2$s';
		break;
	case 'right_space' :
		$format = '%2$s&nbsp;%1$s';
		break;
	}
	return $format;
}


/******************************
	CART / CHECKOUT
******************************/

/*
* 	Subtotal of the current cart
*	(Total value of line items before tax, discounts & shipping)
*
*	@return Cart subtotal
*/
function sunshine_subtotal( $echo=true, $raw=false ) {
	global $sunshine;
	$subtotal = $sunshine->cart->subtotal;
	if ( $raw )
		return sunshine_money_format( $subtotal, false );
	if ( $echo )
		sunshine_money_format( $subtotal );
}

/*
* 	Total cart taxes
*
*	@return Cart taxes
*/
function sunshine_tax_total( $echo=true, $raw=false ) {
	global $sunshine;
	$tax = $sunshine->cart->tax;
	if ( $raw )
		return sunshine_money_format( $tax, false );
	if ( $echo )
		sunshine_money_format( $tax );
}

/*
* 	Selected shipping method
*
*	@return String with selected shipping method name and cost
*	@return OR, an array with the shipping method name and cost
*/
function sunshine_shipping_method( $echo=true, $raw=false ) {
	global $sunshine;
	if ( $raw ) {
		$shipping['name'] = $sunshine->cart->shipping_method['title'];
		$shipping['cost'] = $sunshine->cart->shipping_method['cost'];
		return $shipping;
	} else {
		if ( isset( $sunshine->cart->shipping_method['id'] ) )
			$shipping_method = sunshine_money_format( $sunshine->cart->shipping_method['cost'], false );
		else {
			if ( is_page( $sunshine->options['page_checkout'] ) )
				$shipping_method = __( 'Select shipping method above','sunshine' );
			else
				$shipping_method = __( 'Select on checkout page','sunshine' );
		}
	}
	if ( $echo )
		echo $shipping_method;
	else
		return $shipping_method;
}


/*
* 	Total cart discounts
*
*	@return Cart discounts
*/
function sunshine_discount_total( $echo=true, $raw=false ) {
	global $sunshine;
	$discount_total = $sunshine->cart->discount_total;
	if ( $raw )
		return '-'.sunshine_money_format( $discount_total, false );
	if ( $echo ) {
		echo '-';
		sunshine_money_format( $discount_total );
	}
}

/*
* 	Total of the current cart
*	(Total value of line items, tax, discounts & shipping)
*
*	@return Cart subtotal
*/
function sunshine_total( $echo=true, $raw=false ) {
	global $sunshine;
	$total = $sunshine->cart->total;
	if ( !$raw )
		$total = sunshine_money_format( $total, false );
	if ( $echo )
		echo $total;
	else
		return $total;
}


/*
* 	Can the user add coupons to cart
*
*	@return True/False
*/
function sunshine_can_add_discount() {
	global $sunshine;
	$can_add = $sunshine->cart->can_add_discount();
	do_action( 'sunshine_can_add_discount', $can_add );
	return $can_add;
}

/*
* 	Get applied discounts to current cart
*
*	@return Object(s) of discount(s)
*/
function sunshine_get_applied_discounts() {
	global $sunshine;
	$discount_items = $sunshine->cart->discount_items;
	$discount_items = apply_filters( 'sunshine_get_applied_discounts', $discount_items );
	return $discount_items;
}

/*
* 	Determine if any discounts have been applied
*
*	@return True/False
*/
function sunshine_has_discounts_applied() {
	global $sunshine;
	$has_discounts = false;
	if( $sunshine->cart->discount_items )
		$has_discounts = true;
	do_action( 'sunshine_has_discounts_applied', $has_discounts );
	return $has_discounts;
}

function sunshine_usable_credits() {
	global $sunshine;
	echo '-'.sunshine_money_format( $sunshine->cart->useable_credits,false );
}

function sunshine_action_menu() {
	if ( isset( SunshineFrontend::$current_gallery ) && post_password_required( SunshineFrontend::$current_gallery->ID ) )
		return;
	do_action( 'sunshine_before_action_menu' );
	$menu = array();
	$menu = apply_filters( 'sunshine_action_menu', $menu );
	if ( $menu ) {
		ksort( $menu );
		$menu_html = '<ul class="sunshine-action-menu sunshine-clearfix">';
		foreach ( $menu as $item ) {
			$attributes = '';
			if ( isset( $item['attr'] ) ) {
				foreach ( $item['attr'] as $attr => $value )
					$attributes .= ' '.$attr.'="'.$value.'"';
			}
			$menu_html .=  '<li';
			if ( isset( $item['class'] ) )
				$menu_html .= ' class="'.$item['class'].'"';
			$menu_html .= '>';
			if ( isset( $item['before_a'] ) )
				$menu_html .= $item['before_a'];
			if ( isset( $item['url'] ) ) {
				$menu_html .= '<a href="'.$item['url'].'"';
				if ( isset( $item['a_class'] ) )
					$menu_html .= ' class="'.$item['a_class'].'" ';
				$menu_html .= $attributes;
				if ( isset( $item['target'] ) )
					$menu_html .= ' target="'.$item['target'].'" ';
				$menu_html .= '>';
			}
			if ( isset( $item['icon'] ) )
				$menu_html .= '<i class="fa fa-'.$item['icon'].'"></i> ';
			$menu_html .=  '<span class="sunshine-menu-name">'.$item['name'].'</span>';
			if ( isset( $item['url'] ) )
				$menu_html .=  '</a>';
			if ( isset( $item['after_a'] ) )
				$menu_html .=  $item['after_a'].'</li>';
		}
		$menu_html .= '</ul>';
		echo $menu_html;
	}
	do_action( 'sunshine_after_action_menu', $menu );
}

function sunshine_get_galleries() {
	global $current_user, $sunshine;
	if ( $sunshine->options['gallery_order'] == 'date_new_old' ) {
		$order = 'date';
		$orderby = 'DESC';
	} elseif ( $sunshine->options['gallery_order'] == 'date_old_new' ) {
		$order = 'date';
		$orderby = 'ASC';
	} elseif ( $sunshine->options['gallery_order'] == 'title' ) {
		$order = 'title';
		$orderby = 'ASC';
	} else {
		$order = 'menu_order';
		$orderby = 'ASC';
	}
	$args = array(
		'post_type' => 'sunshine-gallery',
		'post_parent' => 0,
		'orderby' => $order,
		'order' => $orderby,
		'nopaging' => true
	);
	if ( is_user_logged_in() && !current_user_can( 'manage_options' ) ) {
		$args['post_status'] = array( 'publish', 'private' );
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key' => 'sunshine_gallery_private_user',
				'value' => $current_user->ID
			),
			array(
				'key' => 'sunshine_gallery_private_user',
				'value' => '0'
			),
		);
	}
	if ( current_user_can( 'manage_options' ) )
		unset( $args['post_status'] );

	$galleries = new WP_Query( $args );
	return $galleries;
}

function sunshine_gallery_class() {
	global $post;
	$classes = array();
	if ( post_password_required( $post->ID ) )
		$classes[] = 'password-required';
	echo join( ' ', $classes );
}

function sunshine_image_class( $image_id, $classes = array() ) {
	global $sunshine;
	if ( is_array( $sunshine->cart->content ) ) {
		foreach ( $sunshine->cart->content as $item ) {
			if ( isset( $item['image_id'] ) && $item['image_id'] == $image_id ) {
				$classes[] = 'sunshine-in-cart';
				break;
			}
		}
	}
	$comments = get_comments( array( 'post_id' => $image_id ) );
	if ( $comments )
		$classes[] = 'sunshine-has-comments';
	echo join( ' ', apply_filters( 'sunshine_image_class', $image_id, $classes ) );
}

function sunshine_classes() {
	global $sunshine;
	$classes = '';
	if ( isset( SunshineFrontend::$current_image ) ) {
		$classes .= ' sunshine-image '.SunshineFrontend::$current_image->post_name;
		$disable_products = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_disable_products', true );
		$hide_add_to_cart = false;
		if ( $sunshine->options['add_to_cart_require_account'] && !is_user_logged_in() )
			$classes .= ' hide-add-to-cart';
	} elseif ( isset( SunshineFrontend::$current_gallery ) && !isset( SunshineFrontend::$current_image ) ) {
		$classes .= ' sunshine-gallery '.SunshineFrontend::$current_gallery->post_name;
	} elseif ( isset( SunshineFrontend::$current_order ) )
		$classes .= ' sunshine-order';

	if ( isset( $sunshine->options['proofing'] ) && $sunshine->options['proofing'] == 1 )
		$classes .= ' proofing';

	echo apply_filters( 'sunshine_classes', $classes );
}

function sunshine_featured_image( $size="sunshine-thumbnail", $echo=1 ) {
	global $post;
	if ( has_post_thumbnail() )
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
	else if ( $images = get_children( array(
					'post_parent' => get_the_ID(),
					'post_type' => 'attachment',
					'numberposts' => 1,
					'post_mime_type' => 'image',
					'orderby' => 'menu_order ID',
					'order' => 'ASC' ) ) ) {
			foreach( $images as $image )
				$src = wp_get_attachment_image_src( $image->ID, $size );
		}
	if ( isset( $src ) ) {
		if ( $echo )
			echo '<img src="'.$src[0].'" alt="'.htmlspecialchars( $post->post_title ).'" />';
		else
			return $src[0];
	}
}

function sunshine_gallery_image_count() {
	global $post;
	$attachments = get_children( array( 'post_parent' => $post->ID, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
	echo count( $attachments );
}

function sunshine_get_child_galleries() {
	global $current_user;
	$args = array(
		'post_type' => 'sunshine-gallery',
		'post_parent' => SunshineFrontend::$current_gallery->ID,
		'orderby' => 'menu_order ID',
		'order' => 'ASC',
		'nopaging' => true
	);
	if ( is_user_logged_in() && !current_user_can( 'manage_options' ) ) {
		$args['post_status'] = array( 'publish', 'private' );
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key' => 'sunshine_gallery_private_user',
				'value' => $current_user->ID
			),
			array(
				'key' => 'sunshine_gallery_private_user',
				'value' => '0'
			),
		);
	}
	if ( current_user_can( 'manage_options' ) )
		unset( $args['post_status'] );

	$child_galleries = new WP_Query( $args );
	wp_reset_query();
	return $child_galleries;
}

function sunshine_is_gallery_expired() {
	if ( current_user_can( 'manage_options' ) ) return;
	$end_date = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_end_date', true );
	if ( $end_date == '' || $end_date >= current_time( 'timestamp' ) ) // Don't let people see expired galleries
		$expired = false;
	else
		$expired = true;
	return $expired;
}

function sunshine_gallery_columns() {
	global $sunshine;
	return $sunshine->options['columns'];
}

function sunshine_gallery_rows() {
	global $sunshine;
	$sunshine->options['rows'];
}

function sunshine_gallery_images_per_page() {
	global $sunshine;
	return $sunshine->options['columns']*$sunshine->options['rows'];
}

function sunshine_get_search_images() {
	global $sunshine;

	if ( !empty( $_GET['sunshine_search'] ) ) {

		$galleries = sunshine_get_galleries();
		while ( $galleries->have_posts() ) : $galleries->the_post();
		if ( !post_password_required( get_the_ID() ) )
			$searchable_galleries[] = get_the_ID();
		endwhile; wp_reset_postdata();

		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'any',
			'nopaging' => true,
			'post_parent__in' => $searchable_galleries,
			'orderby' => 'menu_order ID',
			'order' => 'ASC',
			's' => sanitize_text_field( $_GET['sunshine_search'] )
		);
		$sunshine_search_query = new WP_Query( $args );
		while ( $sunshine_search_query->have_posts() ) : $sunshine_search_query->the_post();
		$images[] = $sunshine_search_query->post;
		endwhile; wp_reset_postdata();
	}
	return $images;
}

function sunshine_get_gallery_images() {
	global $sunshine;
	$args = array(
		'post_type' => 'attachment',
		'post_parent' => SunshineFrontend::$current_gallery->ID,
		'posts_per_page' => $sunshine->options['rows'] * $sunshine->options['columns'],
	);
	
	if ( $sunshine->options['image_order'] == 'shoot_order' ) {
		$args['orderby'] = 'meta_key';
		$args['order'] = 'DESC';
		$args['meta_key'] = 'created_timestamp';
	} elseif ( $sunshine->options['image_order'] == 'date_new_old' ) {
		$args['orderby'] = 'date';
		$args['order'] = 'DESC';
	} elseif ( $sunshine->options['image_order'] == 'date_old_new' ) {
		$args['orderby'] = 'date';
		$args['order'] = 'ASC';
	} elseif ( $sunshine->options['image_order'] == 'title' ) {
		$args['orderby'] = 'title';
		$args['order'] = 'ASC';
	} else {
		$args['orderby'] = 'menu_order ID';
		$args['order'] = 'ASC';
	}
	
	if ( isset( $_GET['pagination'] ) && $_GET['pagination'] > 1 ) {
		$args['offset'] = ( $_GET['pagination'] - 1 ) * ( $sunshine->options['columns'] * $sunshine->options['rows'] );
	}
	$images = get_posts( $args );
	return $images;
}


function sunshine_pagination( $class="sunshine-pagination" ) {
	global $wp_query, $sunshine;
	$total_images = get_children( array( 'post_parent'=>SunshineFrontend::$current_gallery->ID ) );
	$image_count = count( $total_images );
	if ( $image_count > ( $sunshine->options['columns'] * $sunshine->options['rows'] ) ) {
		$page_number = ( isset( $_GET['pagination'] ) ) ? $_GET['pagination'] : 1;
		$current_gallery_page = array( SunshineFrontend::$current_gallery->ID, $page_number );
		SunshineSession::instance()->current_gallery_page = $current_gallery_page;

		$pages = ceil( $image_count / ( $sunshine->options['columns'] * $sunshine->options['rows'] ) );
		echo '<div class="'.$class.'">';
		if ( $page_number > 1 ) {
			$prev_page = $page_number - 1;
			echo '<a href="'.get_permalink( SunshineFrontend::$current_gallery->ID ).'?pagination='.$prev_page.'">&laquo; '.__( 'Previous','sunshine' ).'</a> ';
		}
		for ( $i=1;$i<=$pages;$i++ ) {
			$class = ( $page_number == $i || ( $page_number == 0 && $i == 1 ) ) ? 'current' : '';
			echo '<a href="'.get_permalink( SunshineFrontend::$current_gallery->ID ).'?pagination='.$i.'" class="'.$class.'">'.$i.'</a> ';
		}
		if ( $page_number < $pages ) {
			$next_page = $page_number + 1;
			echo ' <a href="'.get_permalink( SunshineFrontend::$current_gallery->ID ).'?pagination='.$next_page.'">'.__( 'Next','sunshine' ).'  &raquo;</a>';
		}

		echo '</div>';
	}
}

function sunshine_image() {
	$image = wp_get_attachment_image_src( SunshineFrontend::$current_image->ID, 'full' );
	do_action( 'sunshine_before_image', $image );
	echo '<img src="'.$image[0].'" alt="" />';
	do_action( 'sunshine_after_image', $image );
}

function sunshine_add_to_cart_form() {
	global $post, $sunshine;
	do_action( 'sunshine_before_add_to_cart_form' );
	$disable_products = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_disable_products', true );
	$hide_add_to_cart = false;
	if ( $sunshine->options['add_to_cart_require_account'] && !is_user_logged_in() )
		$hide_add_to_cart = true;
	if ( !$disable_products && !$hide_add_to_cart && !$sunshine->options['proofing'] ) {
?>
		<form method="post" action="">
		<ul id="sunshine-add-to-cart">
			<?php
		$price_level = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_price_level', true );
		$product_categories = get_terms( 'sunshine-product-category', 'orderby=slug&order=ASC' );
		foreach ( $product_categories as $product_category ) {
			$args = array(
				'post_type' => 'sunshine-product',
				'orderby' => 'menu_order',
				//'meta_value' => 'sunshine_product_price_'.$price_level,
				//'orderby' => 'meta_value_num',
				'order' => 'ASC',
				'nopaging' => true,
				'tax_query' => array( array(
						'taxonomy' => 'sunshine-product-category',
						'field' => 'id',
						'terms' => array( $product_category->term_id )
					) ),
				'meta_query' => array(
					array(
						'key' => 'sunshine_product_price_'.$price_level,
						'value' => '',
						'compare' => '!='
					)
				)
			);
			$args = apply_filters( 'sunshine_add_product_query_args', $args );
			$category_products = new WP_Query( $args );
			if ( $category_products->post_count > 0 ) {
				$available_products[$product_category->term_id] = $category_products;
				$available_categories[] = $product_category;
			}
		}
		if ( count( $available_categories ) > 1 ) {
?>
			<li id="sunshine-product-category-select">
				<h2><?php _e( 'Select Product Type', 'sunshine' ); ?></h2>
				<?php do_action( 'sunshine_before_product_category_select' ); ?>
				<ul>
				<?php
			foreach ( $available_categories as $product_category ) {
				do_action( 'sunshine_before_product_category', $product_category );
?>
					<li><label><input type="radio" name="sunshine_product_category" value="<?php echo $product_category->term_id; ?>" /> <?php echo $product_category->name; ?></label> <?php if ( $product_category->description ) { ?><div class="sunshine-product-category-select-desc"><?php echo $product_category->description; ?></div><?php } ?></li>
				<?php
				do_action( 'sunshine_after_product_category', $product_category, $price_level );
			}
?>
				</ul>
				<?php do_action( 'sunshine_after_product_category_select', $price_level ); ?>
			</li>
			<?php } ?>
			<?php if ( !empty( $available_products ) ) { ?>
			<li id="sunshine-product-select" style="display: <?php echo ( count( $available_products ) > 1 ) ? 'none' : 'block'; ?>">
				<h2><?php _e( 'Select Product', 'sunshine' ); ?></h2>
				<?php
			foreach ( $available_products as $category_id => $products ) {
?>
					<div id="sunshine-product-category-<?php echo $category_id; ?>" class="sunshine-product-select" style="display: <?php echo ( count( $available_products ) > 1 ) ? 'none' : 'block'; ?>;">
						<?php if ( $products->have_posts() ) { ?>
							<ul>
							<?php while ( $products->have_posts() ) : $products->the_post(); ?>
								<li class="<?php sunshine_product_class(); ?>">
									<?php do_action( 'sunshine_before_product', $post ); ?>
									<label id="sunshine-product-<?php the_ID(); ?>">
										<input type="radio" name="sunshine_product" value="<?php the_ID(); ?>">
										<span class="sunshine-product-name"><?php the_title(); ?></span>
										<span class="sunshine-product-divider">-</span>
										<span class="sunshine-product-price"><?php echo $sunshine->cart->get_product_price( get_the_ID(), $price_level ); ?></span>
									</label>
									<?php if ( $post->post_content ) { ?><span class="sunshine-product-desc-link"><a href="#sunshine-product-<?php the_ID(); ?>-desc" onclick="jQuery('#sunshine-product-<?php the_ID(); ?>-desc').toggle(); return false;">details</a></span><?php } ?>
									<div class="sunshine-product-desc" id="sunshine-product-<?php the_ID(); ?>-desc">
										<?php echo $post->post_content; ?>
									</div>
									<?php do_action( 'sunshine_after_product', $post, $price_level ); ?>
								</li>
							<?php endwhile; wp_reset_postdata(); ?>
							</ul>
						<?php } else {
					echo '<p>'.__( 'No products for this category','sunshine' ).'</p>';
				} ?>
					</div>
				<?php
			} // End foreach
			do_action( 'sunshine_after_product_select', $price_level );
?>
			</li>
			<?php } ?>
			<li id="sunshine-add-qty" style="display: none;">
				<h2><?php _e( 'Quantity', 'sunshine' ); ?></h2>
				<input type="number" name="sunshine_qty" class="sunshine-qty" value="1" size="4" />
			</li>
			<li id="sunshine-add-comments" style="display: none;">
				<script>
				function limitText(limitField, limitCount, limitNum) {
					if (limitField.value.length > limitNum) {
						limitField.value = limitField.value.substring(0, limitNum);
					} else {
						limitCount.value = limitNum - limitField.value.length;
					}
				}
				</script>
				<h2><?php _e( 'Comments', 'sunshine' ); ?></h2>
				<textarea name="sunshine_comments" rows="5" cols="20" onKeyDown="limitText(this.form.sunshine_comments,this.form.countdown,200);" onKeyUp="limitText(this.form.sunshine_comments,this.form.countdown,200);"></textarea>
				<br /><?php echo sprintf( __( 'You have %s characters left','sunshine' ), '<input readonly type="text" class="sunshine-countdown" name="countdown" size="3" value="200" />' ); ?>
			</li>
			<li id="sunshine-add-button" style="display: none;">
				<input type="submit" value="<?php _e( 'Add to Cart','sunshine' ); ?>" class="sunshine-button" />
				<input type="hidden" name="sunshine_add_to_cart" value="1" />
				<input type="hidden" name="sunshine_image" value="<?php echo SunshineFrontend::$current_image->ID; ?>" />
			</li>
		</ul>
		</form>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('input[name="sunshine_product_category"]').change(function() {
				var product_category = jQuery(this).val();
				jQuery('.sunshine-product-select').hide();
				jQuery('#sunshine-product-select, #sunshine-product-category-'+product_category).show();
				jQuery('#sunshine-add-qty, #sunshine-add-comments, #sunshine-add-button').hide();
			});
			jQuery('input[name="sunshine_product"]').change(function() {
				var product_id = jQuery(this).val();
				jQuery('#sunshine-product-variations-'+product_id).toggle();
				jQuery('#sunshine-add-qty, #sunshine-add-comments, #sunshine-add-button').show();
			});
		});
		</script>

	<?php } elseif ( $disable_products ) {

		// Do nothing

	}
	elseif ( $hide_add_to_cart ) {

		echo '<p>'.sprintf( __( 'You must first <a href="%s">login</a> or <a href="%s">register</a> before you can add pictures to your cart. This allows us to track your favorites and items in your cart when you return.','sunshine' ), wp_login_url( sunshine_current_url( false ) ), wp_registration_url().'&redirect_to='.sunshine_current_url( false ) ).'</p>';

	}

	do_action( 'sunshine_after_add_to_cart_form' );

}

function sunshine_product_class( $product_id = '' ) {
	if ( !$product_id )
		$product_id = get_the_ID();
	$classes[] = 'sunshine-product';
	$classes[] = 'sunshine-product-'.get_the_ID();
	$classes = apply_filters( 'sunshine_product_class', $classes, $product_id );
	echo join( ' ', $classes );
}

function sunshine_main_menu( $echo=true ) {
	$menu = array();
	$menu = apply_filters( 'sunshine_main_menu', $menu );
	if ( $menu ) {
		ksort( $menu );
		//$menu_html = '<div>';
		$menu_html =  '<ul class="sunshine-main-menu sunshine-clearfix">';
		foreach ( $menu as $item ) {
			$attributes = '';
			if ( isset( $item['attr'] ) ) {
				foreach ( $item['attr'] as $attr => $value )
					$attributes .= ' '.$attr.'="'.$value.'"';
			}
			$menu_html .=  '<li';
			if ( isset( $item['class'] ) )
				$menu_html .= ' class="'.$item['class'].'"';
			$menu_html .= '>';
			if ( isset( $item['before_a'] ) )
				$menu_html .= $item['before_a'];
			if ( isset( $item['url'] ) ) {
				$menu_html .= '<a href="'.$item['url'].'"';
				if ( isset( $item['a_class'] ) )
					$menu_html .= ' class="'.$item['a_class'].'" ';
				$menu_html .= $attributes;
				if ( isset( $item['target'] ) )
					$menu_html .= ' target="'.$item['target'].'" ';
				$menu_html .= '>';
			}
			if ( isset( $item['icon'] ) )
				$menu_html .= '<i class="fa fa-'.$item['icon'].'"></i> ';
			if ( isset( $item['name'] ) )
				$menu_html .=  '<span class="sunshine-menu-name">'.$item['name'].'</span>';
			if ( isset( $item['url'] ) )
				$menu_html .=  '</a>';
			if ( isset( $item['after_a'] ) )
				$menu_html .=  $item['after_a'];
			$menu_html .= '</li>';
		}
		$menu_html .=  '</ul>';
		//$menu_html .=  '</div>';
	}
	if ( $echo )
		echo $menu_html;
	else
		return $menu_html;
}

function sunshine_image_menu( $image ) {
	if ( !is_array( $image ) )
		$image = get_post( $image );
	$menu = array();
	$menu = apply_filters( 'sunshine_image_menu', $menu, $image );
	if ( $menu ) {
		ksort( $menu );
		$menu_html = '<ul class="sunshine-image-menu sunshine-clearfix">';
		foreach ( $menu as $item ) {
			$attributes = '';
			if ( isset( $item['attr'] ) ) {
				foreach ( $item['attr'] as $attr => $value )
					$attributes .= ' '.$attr.'="'.$value.'"';
			}
			$menu_html .=  '<li';
			if ( isset( $item['class'] ) )
				$menu_html .= ' class="'.$item['class'].'"';
			$menu_html .= '>';
			if ( isset( $item['before_a'] ) )
				$menu_html .= $item['before_a'];
			if ( isset( $item['url'] ) ) {
				$menu_html .= '<a href="'.$item['url'].'"';
				if ( isset( $item['a_class'] ) )
					$menu_html .= ' class="'.$item['a_class'].'" ';
				$menu_html .= $attributes;
				if ( isset( $item['target'] ) )
					$menu_html .= ' target="'.$item['target'].'" ';
				$menu_html .= '>';
			}
			if ( isset( $item['icon'] ) )
				$menu_html .= '<i class="fa fa-'.$item['icon'].'"></i> ';
			if ( isset( $item['name'] ) )
				$menu_html .=  '<span class="sunshine-menu-name">'.$item['name'].'</span>';
			if ( isset( $item['url'] ) )
				$menu_html .=  '</a>';
			if ( isset( $item['after_a'] ) )
				$menu_html .=  $item['after_a'];
			$menu_html .= '</li>';
		}
		$menu_html .= '</ul>';
		echo $menu_html;
	}
}

function sunshine_cart_items() {
	global $sunshine;
	return $sunshine->cart->get_cart();
}

function sunshine_head() {
	do_action( 'sunshine_head' );
}

function sunshine_cart_totals() {
	global $sunshine;
?>
	<table>
	<tr class="sunshine-subtotal">
		<th><?php _e( 'Subtotal', 'sunshine' ); ?></th>
		<td><?php sunshine_subtotal(); ?></td>
	</tr>
	<?php if ( $sunshine->options['tax_location'] && $sunshine->options['tax_rate'] ) { ?>
	<tr class="sunshine-tax">
		<th><?php _e( 'Tax', 'sunshine' ); ?></th>
		<td><?php sunshine_tax_total(); ?></td>
	</tr>
	<?php } ?>
	<tr class="sunshine-shipping">
		<th><?php _e( 'Shipping', 'sunshine' ); ?></th>
		<td><?php sunshine_shipping_method(); ?></td>
	</tr>
	<?php if ( $sunshine->cart->discount_total > 0 ) { ?>
	<tr class="sunshine-discount">
		<th><?php _e( 'Discounts', 'sunshine' ); ?></th>
		<td><?php sunshine_discount_total(); ?></td>
	</tr>
	<?php } ?>
	<?php if ( $sunshine->cart->use_credits ) { ?>
	<tr class="sunshine-credits">
		<th><?php _e( 'Credits', 'sunshine' ); ?></th>
		<td><?php sunshine_usable_credits(); ?></td>
	</tr>
	<?php } ?>
	<tr class="sunshine-total">
		<th><?php _e( 'Total', 'sunshine' ); ?></th>
		<td><?php sunshine_total(); ?></td>
	</tr>
	</table>
<?php
}

function sunshine_checkout_contact_fields() {
?>
	<fieldset>
	<h2><?php _e( 'Account Information', 'sunshine' ); ?></h2>
		<div class="field field-left required"><label><?php _e( 'Email', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="email" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'email' ) ); ?>" /></label></div>
<?php if ( !is_user_logged_in() ) { ?>
	<div class="field field-right required"><label><?php _e( 'Password', 'sunshine' ); ?><span class="required">*</span> <input type="password" name="password" value="" /></label><span class="field-desc"><?php _e( 'For added security and accessing any downloads', 'sunshine' ); ?></span></div>
<?php }
	do_action( 'sunshine_checkout_contact_fields' );
	echo '</fieldset>';
}

function sunshine_checkout_billing_fields() {
?>
	<fieldset id="sunshine-billing-fields">
	<h2><?php _e( 'Billing Information', 'sunshine' ); ?></h2>
	<div class="field field-left required" id="sunshine-billing-country"><label><?php _e( 'Country', 'sunshine' ); ?><span class="required">*</span> <?php SunshineCountries::country_only_dropdown( 'country', SunshineUser::get_user_meta( 'country' ) ); ?></label></div>
	<div class="field field-left required" id="sunshine-billing-first-name"><label><?php _e( 'First Name', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="first_name" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'first_name' ) ); ?>" /></label></div>
	<div class="field field-right required" id="sunshine-billing-last-name"><label><?php _e( 'Last Name', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="last_name" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'last_name' ) ); ?>" /></label></div>
	<div class="field field-left required" id="sunshine-billing-address"><label><?php _e( 'Address', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="address" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'address' ) ); ?>" /></label></div>
	<div class="field field-right" id="sunshine-billing-address2"><label><?php _e( 'Address 2', 'sunshine' ); ?> <input type="text" name="address2" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'address2' ) ); ?>" /></label></div>
	<div class="field field-left required" id="sunshine-billing-city"><label><?php _e( 'City', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="city" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'city' ) ); ?>" /></label></div>
	<div class="field field-right required" id="sunshine-billing-state"><label><?php _e( 'State / Province', 'sunshine' ); ?><span class="required">*</span> <?php SunshineCountries::state_dropdown( SunshineUser::get_user_meta( 'country' ), 'state', SunshineUser::get_user_meta( 'state' ) ); ?></label></div>
	<div class="field field-left required" id="sunshine-billing-zip"><label><?php _e( 'Zip / Postcode', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="zip" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'zip' ) ); ?>" /></label></div>
	<div class="field field-right required"><label><?php _e( 'Phone', 'sunshine' ); ?><span class="required">*</span> <input type="tel" name="phone" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'phone' ) ); ?>" /></label></div>
	<script type="text/javascript">
	jQuery(document).ready(function(){

		// Changing state selection
		jQuery('select[name="country"]').change(function() {
			var country = jQuery(this).val();
			setTimeout(function () {
				jQuery.ajax({
				  	type: 'POST',
				  	url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				  	data: {
				  		action: 'sunshine_checkout_update_state',
						country: country
					},
				  	success: function(data, textStatus, XMLHttpRequest) {
						var obj = jQuery.parseJSON(data);
						if (obj.state_options)
							jQuery('#sunshine-billing-state').html('<label><?php _e( 'State / Province','sunshine' ); ?> '+obj.state_options+'</label>');
				  	},
				  	error: function(MLHttpRequest, textStatus, errorThrown) {
						alert('Sorry, there was an error with your request');
				  	}
				});
			}, 1000);
			return false;
		});

	});
	</script>
<?php
	do_action( 'sunshine_checkout_billing_fields' );
	echo '</fieldset>';
}

function sunshine_checkout_shipping_fields() {
?>
	<fieldset id="sunshine-shipping-fields">
		<h2><?php _e( 'Shipping Information', 'sunshine' ); ?></h2>
		<div class="field field-full"><label><input type="checkbox" name="billing_as_shipping" id="sunshine-shipping-toggle" value="1" <?php checked( SunshineUser::get_user_meta( 'billing_as_shipping' ), 1 ); ?> /> <?php _e( 'Shipping same as billing', 'sunshine' ); ?></label></div>
		<div id="sunshine-shipping-fields-use">
			<div class="field field-left required"><label><?php _e( 'Country', 'sunshine' ); ?><span class="required">*</span> <?php SunshineCountries::country_only_dropdown( 'shipping_country', SunshineUser::get_user_meta( 'shipping_country' ) ); ?></label></div>
			<div class="field field-left required"><label><?php _e( 'First Name', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="shipping_first_name" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'shipping_first_name' ) ); ?>" /></label></div>
			<div class="field field-right required"><label><?php _e( 'Last Name', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="shipping_last_name" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'shipping_last_name' ) ); ?>" /></label></div>
			<div class="field field-left required"><label><?php _e( 'Address', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="shipping_address" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'shipping_address' ) ); ?>" /></label></div>
			<div class="field field-right"><label><?php _e( 'Address 2', 'sunshine' ); ?> <input type="text" name="shipping_address2" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'shipping_address2' ) ); ?>" /></label></div>
			<div class="field field-left required"><label><?php _e( 'City', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="shipping_city" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'shipping_city' ) ); ?>" /></label></div>
			<div class="field field-right required" id="sunshine-shipping-state"><label><?php _e( 'State / Province', 'sunshine' ); ?><span class="required">*</span> <?php SunshineCountries::state_dropdown( SunshineUser::get_user_meta( 'shipping_country' ), 'shipping_state', SunshineUser::get_user_meta( 'shipping_state' ) ); ?></label></div>
			<div class="field field-left required"><label><?php _e( 'Zip / Postcode', 'sunshine' ); ?><span class="required">*</span> <input type="text" name="shipping_zip" value="<?php echo esc_attr( SunshineUser::get_user_meta( 'shipping_zip' ) ); ?>" /></label></div>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			// Toggle Billing/Shipping
			jQuery('#sunshine-shipping-toggle').change(function(){
				jQuery('#sunshine-shipping-fields-use').hide();
				if (!jQuery(this).is(':checked')) {
					jQuery('#sunshine-shipping-fields-use').show();
				}
			}).change();

			// Changing shipping state selection
			jQuery('select[name="shipping_country"]').change(function() {
				var shipping_country = jQuery(this).val();
				setTimeout(function () {
					jQuery.ajax({
					  	type: 'POST',
					  	url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
					  	data: {
					  		action: 'sunshine_checkout_update_shipping_state',
							shipping_country: shipping_country
						},
					  	success: function(data, textStatus, XMLHttpRequest) {
							var obj = jQuery.parseJSON(data);
							if (obj.state_options)
								jQuery('#sunshine-shipping-state').html('<label>State / Province '+obj.state_options+'</label>');
					  	},
					  	error: function(MLHttpRequest, textStatus, errorThrown) {
							alert('Sorry, there was an error with your request');
					  	}
					});
				}, 1000);
				return false;
			});
		});
		</script>
<?php
	do_action( 'sunshine_checkout_shipping_fields' );
	echo '</fieldset>';
}

function sunshine_checkout_shipping_methods() {
	global $sunshine;
?>
	<fieldset id="sunshine-shipping-methods">
		<h2><?php _e( 'Shipping Methods', 'sunshine' ); ?></h2>
		<ul>
<?php
	$shipping_methods = $sunshine->shipping->get_shipping_methods();
	foreach ( $shipping_methods as $id => $method ) {
		if ( $id == 'flat_rate' )
			$method['cost'] += $sunshine->cart->shipping_extra;
		echo '<li><label><input type="radio" name="shipping_method" value="'.$id.'" '.checked( $id, ( isset( $sunshine->cart->shipping_method['id'] ) ) ? $sunshine->cart->shipping_method['id'] : '', 0 ).'> '.$method['title'];
		echo ' - '.sunshine_money_format( $method['cost'], false );
		echo '</label></li>';
	}
?>
	</ul>
<?php
	do_action( 'sunshine_checkout_shipping_methods' );
	echo '</fieldset>';
}

function sunshine_checkout_shipping_methods_dropdown() {
	global $sunshine;

	$dropdown = '<select name="shipping_method"><option value="">'.__( 'Select shipping method','sunshine' ).'</option>';
	$shipping_methods = $sunshine->shipping->get_shipping_methods();
	foreach ( $shipping_methods as $id => $method ) {
		if ( $id == 'flat_rate' )
			$method['cost'] += $sunshine->cart->shipping_extra;
		$dropdown .= '<option value="'.$id.'" '.selected( $id, ( isset( $sunshine->cart->shipping_method['id'] ) ) ? $sunshine->cart->shipping_method['id'] : '', 0 ).'> '.$method['title'];
		$dropdown .= ' - '.sunshine_money_format( $method['cost'], false );
		$dropdown .= '</option>';
	}
	$dropdown .= '</select>';
	return $dropdown;
}


function sunshine_checkout_payment_methods() {
	global $sunshine;
?>
	<fieldset id="sunshine-payment-methods">
		<h2><?php _e( 'Payment Methods', 'sunshine' ); ?></h2>
		<?php if ( $sunshine->cart->credits > 0 ) { ?>
			<div id="sunshine-payment-credit"><label><input type="checkbox" name="use_credits" value="1" <?php checked( $sunshine->cart->use_credits,1 ); ?> />
				<?php printf( __( 'Use my %s in credit','sunshine' ),sunshine_money_format( $sunshine->cart->credits,false ) ); ?>
			</div>
		<?php }

	if ( $sunshine->cart->total > 0 ) { ?>
			<ul id="sunshine-payment-method-options">
			<?php
		$user_payment_method = SunshineUser::get_user_meta( 'payment_method' );
		foreach ( SunshinePaymentMethods::$payment_methods as $payment_method ) {
			echo '<li id="sunshine-payment-method-'.$payment_method['key'].'">
						<label><input type="radio" name="payment_method" value="'.$payment_method['key'].'" '.checked( $payment_method['key'], $user_payment_method, 0 ).' /> '.$payment_method['name'].'</label>
						<div class="sunshine-payment-method-description">'.$payment_method['description'].'</div>
						<div class="sunshine-payment-method-extra">';
			do_action( 'sunshine_payment_method_extra_'.$payment_method['key'] );
			echo '</div>
					</li>';
		}
?>
			</ul>
<?php
	}
	do_action( 'sunshine_checkout_payment_methods' );
	echo '</fieldset>';
}

function sunshine_checkout_account() {
	global $sunshine;
	if ( !is_user_logged_in() ) { ?>
		<fieldset id="sunshine-payment-methods">
			<h2><?php _e( 'Create Account', 'sunshine' ); ?></h2>
			<div class="field field-left"><label><?php _e( 'Username', 'sunshine' ); ?> <input type="text" name="username" value="<?php echo esc_attr( $_POST['username'] ); ?>" /></label></div>
			<div class="field field-right"><label><?php _e( 'Last Name', 'sunshine' ); ?> <input type="password" name="password" value="<?php echo esc_attr( $_POST['password'] ); ?>" /></label></div>
		</fieldset>
<?php
	}
}

function sunshine_checkout_order_review() {
	global $sunshine;
?>
	<div id="sunshine-checkout-order-review">
		<h2><?php _e( 'Order Summary', 'sunshine' ); ?></h2>
		<table>
		<tr class="sunshine-subtotal">
			<th><?php _e( 'Item(s) total', 'sunshine' ); ?></th>
			<td><?php sunshine_subtotal(); ?></td>
		</tr>
		<tr class="sunshine-tax">
			<th><?php _e( 'Tax', 'sunshine' ); ?></th>
			<td><?php sunshine_tax_total(); ?></td>
		</tr>
		<tr class="sunshine-shipping">
			<th><?php _e( 'Shipping', 'sunshine' ); ?></th>
			<td><?php sunshine_shipping_method(); ?></td>
		</tr>
		<tr class="sunshine-discount">
			<th><?php _e( 'Discounts', 'sunshine' ); ?></th>
			<td><?php sunshine_discount_total(); ?></td>
		</tr>
		<tr class="sunshine-credits"<?php if ( !$sunshine->cart->use_credits ) { echo 'style="display: none;"'; } ?>>
			<th><?php _e( 'Credits', 'sunshine' ); ?></th>
			<td><?php sunshine_usable_credits(); ?></td>
		</tr>
		<tr class="sunshine-total">
			<th><?php _e( 'Order Total', 'sunshine' ); ?></th>
			<td><?php sunshine_total(); ?></td>
		</tr>
		</table>
	</div>
<?php
}

add_action( 'sunshine_checkout_end_form', 'sunshine_checkout_ajax_js' );
function sunshine_checkout_ajax_js() {
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {

		// Updating Cart Totals
		jQuery('form#sunshine-checkout').on('change', 'select[name="country"], select[name="shipping_country"], select[name="state"], select[name="shipping_state"], input[name="billing_as_shipping"], input[name="shipping_method"], input[name="use_credits"]', function(event) {
			jQuery('#sunshine-checkout').css({ opacity: 0.2 });
			if (jQuery('input[name="billing_as_shipping"]').is(":checked")) {
				var state = jQuery('[name="state"]').val();
				var country = jQuery('[name="country"]').val();
			} else {
				var state = jQuery('[name="shipping_state"]').val();
				var country = jQuery('[name="shipping_country"]').val();
			}
			var shipping_method = jQuery('input[name="shipping_method"]:checked').val();
			var use_credits = jQuery('input[name="use_credits"]:checked').val();
			var billing_as_shipping = jQuery('input[name="billing_as_shipping"]:checked').val();
			setTimeout(function () {
				jQuery.ajax({
				  	type: 'POST',
				  	url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				  	data: {
				  		action: 'sunshine_checkout_update_totals',
						state: state,
						country: country,
						shipping_method: shipping_method,
						use_credits: use_credits,
						billing_as_shipping: billing_as_shipping
					},
				  	success: function(data, textStatus, XMLHttpRequest) {
						var obj = jQuery.parseJSON(data);
						if (obj) {
							jQuery('#sunshine-checkout-order-review .sunshine-shipping td').html(obj.shipping);
							jQuery('#sunshine-checkout-order-review .sunshine-tax td').html(obj.tax);
							jQuery('#sunshine-checkout-order-review .sunshine-credits td').html(obj.credits);
							jQuery('#sunshine-checkout-order-review .sunshine-total td').html(obj.total);
							if (jQuery('input[name="use_credits"]:checked').val() == 1)
								jQuery('#sunshine-checkout-order-review .sunshine-credits').show();
							else
								jQuery('#sunshine-checkout-order-review .sunshine-credits').hide();
							if (obj.free == 1)
								jQuery('#sunshine-payment-method-options').hide();
							else
								jQuery('#sunshine-payment-method-options').show();
						}
						jQuery('#sunshine-checkout').css({ opacity: 1.0 });
				  	},
				  	error: function(MLHttpRequest, textStatus, errorThrown) {
						alert('Sorry, there was an error with your request');
				  	}
				});
			}, 1000);
			return false;
		});

		jQuery('.sunshine-payment-method-extra').hide();
		jQuery('input[name="payment_method"]').change(function() {
			jQuery('.sunshine-payment-method-extra').hide();
			jQuery(this).parent().parent().children('div.sunshine-payment-method-extra').show();
		});

		var payment_method = jQuery('input[name="payment_method"]:checked').val();
		jQuery('#sunshine-payment-method-'+payment_method+' .sunshine-payment-method-extra').show();

	});
	</script>
<?php
}


function sunshine_get_order_items( $order_id ) {
	$order_items = unserialize( get_post_meta( $order_id, '_sunshine_order_items', true ) );
	return apply_filters( 'sunshine_order_items', $order_items );
}

function sunshine_get_order_data( $order_id ) {
	$order_data = unserialize( get_post_meta( $order_id, '_sunshine_order_data', true ) );
	return apply_filters( 'sunshine_order_data', $order_data );
}

function sunshine_get_order_status( $order_id ) {
	$status = array_values( get_the_terms( $order_id, 'sunshine-order-status' ) );
	$status[0]->description = apply_filters( 'sunshine_order_status_description', $status[0]->description, $status[0], $order_id );
	return $status[0];
}

function sunshine_logo() {
	global $sunshine;
	if ( $sunshine->options['template_logo'] > 0 ) {
		echo wp_get_attachment_image( $sunshine->options['template_logo'], 'full' );
	} else
		bloginfo( 'name' );
}

function sunshine_sidebar() {
	/* REMOVED
	if ( is_active_sidebar( 'sunshine-sidebar' ) ) {
		echo '<div id="sunshine-sidebar">';
		dynamic_sidebar( 'sunshine-sidebar' );
		echo '</div>';
	}
	*/
	return;
}

function sunshine_next_image_url( $current_image ) {
	return '#';
}

function sunshine_prev_image_url( $current_image ) {
	return '#';
}

function sunshine_adjacent_image_link( $prev = true, $size = 'thumbnail', $text = false ) {
	$attachments = array_values( get_children( array( 'post_parent' => SunshineFrontend::$current_image->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );

	foreach ( $attachments as $k => $attachment )
		if ( $attachment->ID == SunshineFrontend::$current_image->ID )
			break;

		$k = $prev ? $k - 1 : $k + 1;

	$output = $attachment_id = null;
	if ( isset( $attachments[ $k ] ) ) {
		$attachment_id = $attachments[ $k ]->ID;
		$output = wp_get_attachment_link( $attachment_id, $size, true, false, $text );
	}

	// Disable any lightbox actions on these next/prev links by altering the rel/class attr. May not work for everyone.
	$output = str_replace( 'rel="', 'rel="sunshine-', $output );
	$output = str_replace( 'class="', 'class="sunshine-', $output );

	$adjacent = $prev ? 'previous' : 'next';

	echo apply_filters( "sunshine_{$adjacent}_image_link", $output, $attachment_id, $size, $text );
}

function sunshine_breadcrumb( $divider = ' / ', $echo = true ) {
	global $sunshine;
	$breadcrumb = '<a href="'.get_permalink( $sunshine->options['page'] ).'">'.get_the_title( $sunshine->options['page'] ).'</a>';
	if ( isset( SunshineFrontend::$current_gallery ) )
		$breadcrumb .= sunshine_breadcrumb_gallery( SunshineFrontend::$current_gallery, $divider );
	if ( isset( SunshineFrontend::$current_image->ID ) ) {
		$breadcrumb .= $divider.'<a href="'.get_permalink( SunshineFrontend::$current_image->ID ).'">'.get_the_title( SunshineFrontend::$current_image->ID ).'</a>';
	}
	if ( $echo )
		echo $breadcrumb;
	else
		return $breadcrumb;
}
function sunshine_breadcrumb_gallery( $gallery, $divider ) {
	if ( $gallery->post_parent == 0 )
		$breadcrumb = $divider.'<a href="'.get_permalink( $gallery->ID ).'">'.get_the_title( $gallery->ID ).'</a>';
	else {
		$parent = get_post( $gallery->post_parent );
		$breadcrumb = sunshine_breadcrumb_gallery( $parent, $divider );
		$breadcrumb .= $divider.'<a href="'.get_permalink( $gallery->ID ).'">'.get_the_title( $gallery->ID ).'</a>';
	}
	return $breadcrumb;
}

function sunshine_gallery_password_form( $echo = true ) {
	$form = '<form method="post" action="">
		<div>
			<input type="text" name="sunshine_gallery_password" />
			<input type="submit" value="'.__( 'Go', 'sunshine' ).'" class="sunshine-button" />
		</div>
	</form>';
	if ( $echo )
		echo $form;
	else
		return $form;
}

function sunshine_gallery_expiration_notice() {
	if ( isset( SunshineFrontend::$current_gallery->ID ) ) {
		$end_date = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_end_date', true );
		if ( $end_date != '' && $end_date > current_time( 'timestamp' ) ) {
			echo '<div id="sunshine-gallery-expiration-notice">';
			echo apply_filters( 'sunshine_gallery_expiration_notice', sprintf( __( 'This gallery is set to expire on <strong>%s</strong>','sunshine' ), date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $end_date ) ) );
			echo '</div>';
		}
	}
}

//add_filter('sunshine_cart_image_html', 'sunshine_default_cart_image', 1, 3);
function sunshine_default_cart_image( $html, $item, $thumb ) {
	if ( !$thumb[0] && $item['type'] != 'gallery_download' )
		$html = '<img src="http://placehold.it/100&text=Image+deleted" alt="Image has been deleted" />';
	return $html;
}

add_filter( 'sunshine_cart_image_html', 'sunshine_cart_item_name', 100, 3 );
add_filter( 'sunshine_order_image_html', 'sunshine_cart_item_name', 100, 3 );
function sunshine_cart_item_name( $html, $item, $thumb ) {
	if ( $item['image_id'] > 0 ) {
		$image = get_post( $item['image_id'] );
		$html .= '<br />'.$image->post_title;
		$gallery = get_post( $image->post_parent );
		$html .= ' in <a href="'.get_permalink( $gallery->ID ).'">'.$gallery->post_title.'</a>';
	}
	return $html;
}

/*
* 	Show a search form
*
*	@return void
*/
function sunshine_search( $echo = true ) {
	global $sunshine;
	$form = '<form method="get" action="'.get_permalink( $sunshine->options['page'] ).'">
		<div>
			<input type="text" name="sunshine_search" />
			<input type="submit" value="'.__( 'Go', 'sunshine' ).'" class="sunshine-button" />
		</div>
	</form>';
	if ( $echo )
		echo $form;
	else
		return $form;
}

/*
* Show the email requirement to access gallery form
*/
function sunshine_gallery_email_form() {

	$form = '<form class="sunshine-gallery-email-form" action="'.get_permalink( SunshineFrontend::$current_gallery->ID ).'" method="post">';
	$form .= '<div class="sunshine-gallery-email-description"><p>' . sprintf( __( 'To view the gallery "%s", please enter your email address:', 'sunshine' ), trim( get_the_title( SunshineFrontend::$current_gallery->ID ) ) ) . '</p></div>';
	$form .= '<div class="sunshine-gallery-email-submit"><label">' . __( "Email", 'sunshine' ) . ': </label><input name="sunshine_gallery_email" type="email" size="20" /> ';
	$form .= '<input type="submit" name="Submit" value="' . esc_attr__( "Submit", 'sunshine' ) . '" class="sunshine-button" /></div>';
	$form .= '<input type="hidden" name="sunshine_gallery_id" value="'.SunshineFrontend::$current_gallery->ID.'" />';
	$form .= '</form>';

	return $form;
}

function sunshine_gallery_requires_email( $gallery_id ) {
	$requires_email = get_post_meta( $gallery_id, 'sunshine_gallery_access', true );
	if ( $requires_email != 'email' ) {
		return false;
	} 
	if ( !is_array( SunshineSession::instance()->gallery_emails ) ) {
		return true;
	}
	sunshine_dump_var( SunshineSession::instance()->gallery_emails );
	$in_gallery_emails = ( in_array( $gallery_id, SunshineSession::instance()->gallery_emails ) ) ? true : false;
	if ( $in_gallery_emails ) {
		return false;
	}
	return true;
}

?>