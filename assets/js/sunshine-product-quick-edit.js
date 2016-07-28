(function($) {

	// we create a copy of the WP inline edit post function
	var $wp_inline_edit = inlineEditPost.edit;

	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {

		// "call" the original WP edit function
		// we don't want to leave WordPress hanging
		$wp_inline_edit.apply( this, arguments );

		// now we take care of our business

		// get the post ID
		var $post_id = 0;
		if ( typeof( id ) == 'object' ) {
			$post_id = parseInt( this.getId( id ) );
		}
		
		if ( $post_id > 0 ) {
			// define the edit row
			var $edit_row = $( '#edit-' + $post_id );
			var $post_row = $( '#post-' + $post_id );

			// get the data
			var $shipping = $( 'input[name="sunshine_product_shipping_value"]', $post_row ).val();
			var $taxable = $( 'input[name="sunshine_product_taxable_value"]', $post_row ).val();
			// populate the data
			$( ':input[name="sunshine_product_shipping"]', $edit_row ).val( $shipping );
			$( ':input[name="sunshine_product_taxable"]', $edit_row ).prop('checked', $taxable );
			
			var price_level_id = price = '';
			// get the price levels, set the price levels
			$( "input[data-price-level]", $post_row ).each(function(){
			    price_level_id = $(this).data( 'price-level' );
				price =  $( this ).val(); 
			    $( 'input[name="sunshine_product_price_' + price_level_id + '"]', $edit_row ).val( price );
			});

		}
	};

})(jQuery);
