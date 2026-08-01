<?php
/**
 * The Template for displaying product archives, including the main shop page.
 *
 * Premium light-gallery shop layout: header band, toolbar (result count +
 * ordering), clean responsive product grid, pagination.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package ActionFiguresChild
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

// Custom archive header band.
if ( is_shop() ) {
	$cdc_archive_title = __( 'Shop the collection', 'actionfigures-child' );
	$cdc_archive_desc  = get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : __( 'Display frames, cases, and shadow boxes for every collectible.', 'actionfigures-child' );
} elseif ( is_product_taxonomy() ) {
	$cdc_archive_title = single_term_title( '', false );
	$cdc_archive_desc  = term_description();
}
?>
<div class="cdc-container cdc-shop">
	<div class="cdc-shop-head">
		<p class="cdc-section-eyebrow"><?php echo esc_html( is_shop() ? 'The collection' : 'Category' ); ?></p>
		<h1><?php echo esc_html( $cdc_archive_title ); ?></h1>
		<?php if ( $cdc_archive_desc ) : ?>
			<p><?php echo wp_kses_post( $cdc_archive_desc ); ?></p>
		<?php endif; ?>
	</div>

	<?php
	if ( woocommerce_product_loop() ) {

		/**
		 * Hook: woocommerce_before_shop_loop.
		 *
		 * @hooked woocommerce_output_all_notices - 10
		 * @hooked woocommerce_result_count - 20
		 * @hooked woocommerce_catalog_ordering - 30
		 */
		do_action( 'woocommerce_before_shop_loop' );

		?>
		<div class="cdc-shop-toolbar">
			<div class="cdc-shop-count"><?php woocommerce_result_count(); ?></div>
			<?php woocommerce_catalog_ordering(); ?>
		</div>
		<?php

		woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();

				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action( 'woocommerce_shop_loop' );

				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();

		/**
		 * Hook: woocommerce_after_shop_loop.
		 *
		 * @hooked woocommerce_pagination - 10
		 */
		do_action( 'woocommerce_after_shop_loop' );
	} else {
		/**
		 * Hook: woocommerce_no_products_found.
		 *
		 * @hooked wc_no_products_found - 10
		 */
		do_action( 'woocommerce_no_products_found' );
	}
	?>
</div>
<?php

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
