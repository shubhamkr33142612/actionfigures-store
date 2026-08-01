<?php
/**
 * The template for displaying product content within loops.
 *
 * Premium gallery-style product card.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package ActionFiguresChild
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$cdc_cats = function_exists( 'wc_get_product_category_list' ) ? wc_get_product_category_list( $product->get_id(), ', ' ) : '';
$cdc_pers = 'yes' === $product->get_meta( '_cdc_personalization_enabled' );
?>
<li <?php wc_product_class( 'cdc-card', $product ); ?>>
	<?php
	do_action( 'woocommerce_before_shop_loop_item' );
	?>
	<a class="cdc-card-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>"></a>
	<?php actionfigures_child_wishlist_button( $product ); ?>
	<div class="cdc-card-media">
		<?php
		do_action( 'woocommerce_before_shop_loop_item_title' );
		?>
	</div>
	<div class="cdc-card-body">
		<?php if ( $cdc_cats ) : ?>
			<span class="cdc-card-cat"><?php echo wp_kses_post( $cdc_cats ); ?></span>
		<?php endif; ?>
		<?php
		do_action( 'woocommerce_shop_loop_item_title' );
		do_action( 'woocommerce_after_shop_loop_item_title' );
		?>
		<?php if ( $cdc_pers ) : ?>
			<span class="cdc-card-badge">Personalizable</span>
		<?php endif; ?>
	</div>
	<?php
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
