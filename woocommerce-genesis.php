<?php

//Add prices to variations
add_filter( 'woocommerce_variation_option_name', 'display_price_in_variation_option_name' );

function display_price_in_variation_option_name( $term ) {
global $wpdb, $product;

$result = $wpdb->get_col( "SELECT slug FROM {$wpdb->prefix}terms WHERE name = '$term'" );

$term_slug = ( !empty( $result ) ) ? $result[0] : $term;

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

//this is where you can actually customize how the price is displayed
return $term . ' (' . woocommerce_price( $_product->get_price() ) . ')';
}
return $term;

} 

?>
