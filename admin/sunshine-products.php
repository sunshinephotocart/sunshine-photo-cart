<?php
/* SETUP META BOXES */
add_action( 'add_meta_boxes', 'sunshine_products_meta_boxes' );
function sunshine_products_meta_boxes() {
	add_meta_box(
		'sunshine_products',
		__( 'Product Info', 'sunshine' ),
		'sunshine_products_box',
		'sunshine-product',
		'advanced',
		'high'
	);
	remove_meta_box( 'commentstatusdiv', 'sunshine_products' , 'normal' );
	remove_meta_box( 'slugdiv', 'sunshine_products' , 'normal' );
}

function sunshine_products_box( $post ) {
	global $sunshine;
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'sunshine_noncename' );

	$currency_symbol = sunshine_currency_symbol();
	$currency_symbol_format = sunshine_currency_symbol_format();

	echo '<table class="sunshine-meta">';
	echo '<tr><th><label for="sunshine_product_price">Price</label></th>';
	echo '<td>';
	$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
	$price_levels_count = count( $price_levels );
	if ( $price_levels_count > 1 ) {
		echo '<ul class="sunshine-price-levels">';
		foreach ( $price_levels as $price_level ) {
			echo '<li>'.$price_level->name.':<br />';
			$text_field = '<input type="text" name="sunshine_product_price_'.$price_level->term_id.'" value="'.get_post_meta( $post->ID, 'sunshine_product_price_'.$price_level->term_id, true ).'" />';
			echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
			echo '</li>';
		}
		echo '</ul>';
	} elseif ( $price_levels_count == 0 ) {
		echo 'No price levels setup';
	} else {
		$text_field = '<input type="text" name="sunshine_product_price_'.$price_levels[0]->term_id.'" value="'.get_post_meta( $post->ID, 'sunshine_product_price_'.$price_levels[0]->term_id, true ).'" />';
		echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
	}
	echo '</td></tr>';
	echo '<tr><th><label for="sunshine_product_taxable">Taxable</label></th>';
	echo '<td><input type="checkbox" name="sunshine_product_taxable" value="1" '.checked( get_post_meta( $post->ID, 'sunshine_product_taxable', true ), 1, 0 ).' /></td></tr>';
	echo '<tr><th><label for="sunshine_product_shipping">Shipping</label></th>';
	echo '<td>';
	$text_field = '<input type="text" name="sunshine_product_shipping" value="'.get_post_meta( $post->ID, 'sunshine_product_shipping', true ).'" />';
	echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
	echo '</td></tr>';
	do_action( 'sunshine_admin_products_meta', $post );
	echo '</table>';

}

/* When the post is saved, saves our custom data */
add_action( 'save_post', 'sunshine_products_save_postdata' );
function sunshine_products_save_postdata( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	if ( !isset( $_POST['sunshine_noncename'] ) || !wp_verify_nonce( $_POST['sunshine_noncename'], plugin_basename( __FILE__ ) ) )
		return;
	if ( $_POST['post_type'] == 'sunshine-product' ) {
		$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
		foreach ( $price_levels as $price_level ) {
			$key = 'sunshine_product_price_'.$price_level->term_id;
			$price = sanitize_text_field( $_POST[$key] );
			if ( $price == '' ) {
				delete_post_meta( $post_id, 'sunshine_product_price_'.$price_level->term_id );
			} else {
				update_post_meta( $post_id, 'sunshine_product_price_'.$price_level->term_id, $price );
			}
		}
		if ( isset( $_POST['sunshine_product_taxable'] ) )
			update_post_meta( $post_id, 'sunshine_product_taxable', intval( $_POST['sunshine_product_taxable'] ) );
		if ( isset( $_POST['sunshine_product_shipping'] ) )
			update_post_meta( $post_id, 'sunshine_product_shipping', sanitize_text_field( $_POST['sunshine_product_shipping'] ) );
	}
}

add_filter( 'manage_edit-sunshine-product_columns', 'sunshine_product_columns' ) ;
function sunshine_product_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Name' ),
		'category' => __( 'Category' ),
		'price' => __( 'Price' )
	);
	return $columns;
}

add_action( 'manage_sunshine-product_posts_custom_column', 'sunshine_product_columns_content', 10, 2 );
function sunshine_product_columns_content( $column, $post_id ) {
	global $post;

	switch( $column ) {
	case 'category':
		$package = get_post_meta( $post_id, 'sunshine_product_package', true );
		if ( $package ) {
			echo 'Package';
			break;
		}
		$terms = get_the_terms( $post_id, 'sunshine-product-category' );
		if ( !empty( $terms ) ) {
			$out = array();
			foreach ( $terms as $term ) {
				$out[] = sprintf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'sunshine-product-category' => $term->slug ), 'edit.php' ) ),
					esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'genre', 'display' ) )
				);
			}
			echo join( ', ', $out );
		}
		else {
			_e( 'No categories' );
		}
		break;
	case 'price':
		$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
		foreach ( $price_levels as $price_level ) {
			$price = get_post_meta( $post->ID, 'sunshine_product_price_'.$price_level->term_id, true );
			echo $price_level->name.': ';
			sunshine_money_format( $price );
			echo '<br />';
		}
		break;
	default:
		break;
	}
}

/**
 * Add duplicate link to action list for post_row_actions
 */
add_filter( 'post_row_actions', 'sunshine_duplicate_product_link_row',10,2 );
add_filter( 'page_row_actions', 'sunshine_duplicate_product_link_row',10,2 );
function sunshine_duplicate_product_link_row( $actions, $post ) {
	if ( $post->post_type == 'sunshine-product' ) {
		$actions['duplicate'] = '<a href="edit.php?post_type=sunshine-product&sunshine_action=duplicate&product_id='.$post->ID.'">Duplicate Product</a>';
	}
	return $actions;
}

add_action( 'admin_init', 'sunshine_duplicate_product' );
function sunshine_duplicate_product() {
	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sunshine-product' && isset( $_GET['sunshine_action'] ) && $_GET['sunshine_action'] == 'duplicate' && is_numeric( $_GET['product_id'] ) ) {
		// Get the original product
		$product = get_post( $_GET['product_id'] );
		// Get custom fields
		$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
		foreach ( $price_levels as $price_level )
			$prices[$price_level->term_id] = get_post_meta( $product->ID,'sunshine_product_price_'.$price_level->term_id, true );
		$taxable = get_post_meta( $product->ID,'sunshine_product_taxable', true );
		$shipping = get_post_meta( $product->ID,'sunshine_product_shipping', true );
		// Get categories
		$categories = wp_get_object_terms( $product->ID, 'sunshine-product-category' );
		foreach ( $categories as $category ) {
			$cats[] = $category->term_id;
		}
		// Set new title
		$product->post_title = $product->post_title . ' DUPLICATE';
		// Remove ID so we don't update the existing product
		unset( $product->ID );
		// Insert new product, update custom fields, assign taxonomies
		$new_product_id = wp_insert_post( $product );
		foreach ( $prices as $price_level => $price ) {
			if ( is_null( $price ) )
				delete_post_meta( $new_product_id, 'sunshine_product_price_'.$price_level );
			else
				update_post_meta( $new_product_id, 'sunshine_product_price_'.$price_level, $price );
		}
		update_post_meta( $new_product_id, 'sunshine_product_taxable', $taxable );
		update_post_meta( $new_product_id, 'sunshine_product_shipping', $shipping );
		wp_set_post_terms( $new_product_id, $cats, 'sunshine-product-category' );
		wp_redirect( get_admin_url().'edit.php?post_type=sunshine-product' );
		exit;
	}
}

add_filter( 'get_sample_permalink_html', 'sunshine_product_sample_permalink_html', 10, 4 );
function sunshine_product_sample_permalink_html( $html, $id, $new_title, $new_slug ) {
	if ( get_post_type( $id ) == 'sunshine-product' ) {
		return '';
	}
	return $html;
}

add_filter( 'post_updated_messages', 'sunshine_product_post_updated_messages' );
function sunshine_product_post_updated_messages( $messages ) {
	global $post;
	if ( $post->post_type == 'sunshine-product' ) {
		$messages["post"][1] = __( '<strong>Product updated</strong>','sunshine' );
		$messages["post"][6] = __( '<strong>Product created</strong>','sunshine' );
	}
	return $messages;
}


?>