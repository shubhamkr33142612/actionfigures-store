<?php
/**
 * ActionFigures Child theme bootstrap.
 *
 * Design direction: light + premium gallery. Fraunces (display serif) +
 * Inter (UI/body), warm near-white surfaces, single brass accent.
 *
 * @package ActionFiguresChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACF_CHILD_VERSION', '1.0.5' );

/**
 * Enqueue fonts and the child stylesheet after Astra's.
 */
function actionfigures_child_enqueue_styles() {
	wp_enqueue_style(
		'actionfigures-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'actionfigures-child',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'astra-theme-css' ),
		ACF_CHILD_VERSION
	);
	wp_enqueue_script(
		'actionfigures-child',
		get_stylesheet_directory_uri() . '/assets/cdc-theme.js',
		array(),
		ACF_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'actionfigures_child_enqueue_styles', 20 );

/**
 * Font delivery hints.
 */
function actionfigures_child_fonts_preconnect() {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com" />' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />' . "\n";
}
add_action( 'wp_head', 'actionfigures_child_fonts_preconnect', 1 );

/**
 * Whole-rupee pricing for the Indian market (₹2,899 not ₹2,899.00).
 *
 * @return int
 */
function actionfigures_child_price_decimals() {
	return 0;
}
add_filter( 'wc_get_price_decimals', 'actionfigures_child_price_decimals' );

/**
 * Keep the header cart badge in sync after AJAX add-to-cart.
 *
 * @param array $fragments WC fragments.
 * @return array
 */
function actionfigures_child_cart_fragments( $fragments ) {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$fragments['span.cdc-cart-count'] = '<span class="cdc-cart-count">' . esc_html( $count ) . '</span>';
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'actionfigures_child_cart_fragments' );

/**
 * Result count + ordering are rendered inside our custom shop toolbar.
 */
function actionfigures_child_shop_toolbar() {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
}
add_action( 'init', 'actionfigures_child_shop_toolbar' );

/**
 * Lightweight SEO + Open Graph tags (no SEO plugin installed).
 */
function actionfigures_child_head_meta() {
	if ( is_admin() ) {
		return;
	}

	if ( is_front_page() ) {
		$description = 'Premium display frames, cases, and shadow boxes for die-cast cars, action figures, cards, and memorabilia. Personalization available on selected frames.';
		$title       = get_bloginfo( 'name' ) . ' — Display Your Collection';
		$url         = home_url( '/' );
		$type        = 'website';
	} else {
		$description = get_bloginfo( 'description' );
		$title       = wp_get_document_title();
		$url         = get_permalink();
		if ( ! $url ) {
			$url = home_url( add_query_arg( array() ) );
		}
		$type = 'website';
	}

	$excerpt = wp_html_excerpt( $description, 160 );

	echo '<meta name="description" content="' . esc_attr( $excerpt ) . '" />' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $excerpt ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
	echo '<meta name="twitter:card" content="summary" />' . "\n";
}
	add_action( 'wp_head', 'actionfigures_child_head_meta', 5 );

/**
 * Data attributes that seed the client-side wishlist for a product.
 *
 * @param WC_Product $product Product object.
 * @return string Escaped HTML attribute string.
 */
function actionfigures_child_wishlist_attrs( $product ) {
	$price = $product ? $product->get_price_html() : '';
	$price = $price ? html_entity_decode( wp_strip_all_tags( $price ), ENT_QUOTES, 'UTF-8' ) : '';
	$img   = ( $product && $product->get_image_id() ) ? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) : '';

	return sprintf(
		'data-product-id="%d" data-name="%s" data-img="%s" data-url="%s" data-price="%s"',
		( $product ? (int) $product->get_id() : 0 ),
		esc_attr( $product ? $product->get_name() : '' ),
		esc_attr( $img ),
		esc_url( $product ? $product->get_permalink() : '' ),
		esc_attr( $price )
	);
}

/**
 * Wishlist heart button used on cards, gallery tiles, and the single page.
 *
 * @param WC_Product $product Product object.
 * @param string     $variant 'single' renders an icon + label button.
 */
function actionfigures_child_wishlist_button( $product, $variant = '' ) {
	$class  = 'cdc-wishlist-btn' . ( $variant ? ' cdc-wishlist-btn--' . $variant : '' );
	$name   = $product ? $product->get_name() : '';
	if ( 'single' === $variant ) {
		$action = esc_attr__( 'Save to wishlist', 'actionfigures-child' );
	} else {
		$action = sprintf( esc_attr__( 'Add %s to wishlist', 'actionfigures-child' ), esc_attr( $name ) );
	}
	echo '<button type="button" class="' . esc_attr( $class ) . '" ' . actionfigures_child_wishlist_attrs( $product ) . ' aria-label="' . esc_attr( $action ) . '" aria-pressed="false">';
	echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>';
	if ( 'single' === $variant ) {
		echo '<span class="cdc-wishlist-btn-label">' . esc_html__( 'Save to wishlist', 'actionfigures-child' ) . '</span>';
	}
	echo '</button>';
}

/**
 * Wishlist button in the single product summary (before add to cart).
 *
 * Astra rebuilds the whole summary in one callback
 * (single_product_content_structure) so the default WooCommerce priority
 * cannot interleave; this hook fires right after the short description.
 */
function actionfigures_child_wishlist_single() {
	global $product;
	if ( $product ) {
		actionfigures_child_wishlist_button( $product, 'single' );
	}
}
add_action( 'astra_woo_single_short_description_after', 'actionfigures_child_wishlist_single' );
