<?php

//Add prices to variations
add_filter( 'woocommerce_variation_option_name', 'display_price_in_variation_option_name' );

function display_price_in_variation_option_name( $term ) {
	global $wpdb, $product;

	if ( empty( $term ) ) {
		return $term;
	}
	if ( empty( $product->id ) ) {
		return $term;
	}

	$result = $wpdb->get_col( "SELECT slug FROM {$wpdb->prefix}terms WHERE name = '$term'" );

	$term_slug = ( ! empty( $result ) ) ? $result[0] : $term;

	$query = "SELECT postmeta.post_id AS product_id
                FROM {$wpdb->prefix}postmeta AS postmeta
                    LEFT JOIN {$wpdb->prefix}posts AS products ON ( products.ID = postmeta.post_id )
                WHERE postmeta.meta_key LIKE 'attribute_%'
                    AND postmeta.meta_value = '$term_slug'
                    AND products.post_parent = $product->id";

	$variation_id = $wpdb->get_col( $query );

	$parent = wp_get_post_parent_id( $variation_id[0] );

	if ( $parent > 0 ) {
		$_product = new WC_Product_Variation( $variation_id[0] );

		return $term . ' (' . wp_kses( woocommerce_price( $_product->get_price() ), array() ) . ')';
	}

	return $term;

}

//* Force all products to one page - eliminating pagination 
add_filter( 'prefix_shop_per_page', create_function( '$cols', 'return -1;' ), 20 );

//* Remove tabs from product details page
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
function woo_remove_product_tabs( $tabs ) {

	//comment out what you want to hide
	unset( $tabs['description'] ); 
	unset( $tabs['reviews'] );
	unset( $tabs['additional_information'] );

	return $tabs;

}

// Hides the product's weight and dimension in teh single product page.
add_filter( 'wc_product_enable_dimensions_display', '__return_false' );

//* Replace the image filename/path with custom image
add_action( 'init', 'prefix_fix_thumbnail' );

function prefix_fix_thumbnail() {
	add_filter( 'woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src' );

	function custom_woocommerce_placeholder_img_src( $src ) {
		$upload_dir = wp_upload_dir();
		$uploads    = untrailingslashit( $upload_dir['baseurl'] );
		// add file path to new placeholder image
		$src        = $uploads . '/new-coming-soon.jpg';

		return $src;
	}
}

// Add necessary class to WooCommerce pages for FacetWP
add_filter( 'genesis_attr_content', 'custom_attributes_content' );
function custom_attributes_content( $attributes ) {

	if ( is_shop() || is_product() || is_product_category() || is_product_tag() ) {
		$attributes['class'] .= ' facetwp-template';
	}

	return $attributes;


}

// Remove Reset button from Price Facet (slider type)
function prefix_facetwp_facet_html( $output, $params ) {
	if ( 'price' == $params['facet']['name'] ) {
		$output = '';
		$value  = $params['selected_values'];
		$output .= '<div class="facetwp-slider-wrap">';
		$output .= '<div class="facetwp-slider"></div>';
		$output .= '</div>';
		$output .= '<span class="facetwp-slider-label"></span>';
		$output .= '<div><input type="button" class="facetwp-slider-reset" value="' . __( 'Reset', 'fwp' ) . '" /></div>';
	}

	return $output;
}

add_filter( 'facetwp_facet_html', 'prefix_facetwp_facet_html', 10, 2 );

?>
