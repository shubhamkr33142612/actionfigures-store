<?php
/**
 * Product page presentation: demo-collectible disclaimer and structured data tab.
 *
 * @package CollectorsDisplayCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds trust and specification content to the single product page.
 */
class CDC_Product_Data {

	/**
	 * Hook into the single product page.
	 */
	public static function init() {
		add_action( 'astra_woo_single_short_description_after', array( __CLASS__, 'demo_disclaimer' ), 5 );
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'specs_tab' ) );
	}

	/**
	 * Show a clear notice that collectibles in photos are for demonstration.
	 */
	public static function demo_disclaimer() {
		global $product;
		if ( ! $product ) {
			return;
		}
		?>
		<div class="cdc-disclaimer">
			<?php esc_html_e( 'Collectibles shown in product photographs are for demonstration purposes unless explicitly included.', 'collectors-display-core' ); ?>
		</div>
		<?php
	}

	/**
	 * Register the specifications tab.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public static function specs_tab( $tabs ) {
		global $product;
		if ( ! $product ) {
			return $tabs;
		}
		$dimensions = $product->get_meta( '_cdc_dimensions' );
		$materials  = $product->get_meta( '_cdc_materials' );
		$includes   = $product->get_meta( '_cdc_includes' );
		$care       = $product->get_meta( '_cdc_care' );

		if ( ! $dimensions && ! $materials && ! $includes && ! $care ) {
			return $tabs;
		}

		$tabs['cdc_specs'] = array(
			'title'    => __( 'Dimensions & Details', 'collectors-display-core' ),
			'priority' => 30,
			'callback' => array( __CLASS__, 'render_specs_tab' ),
		);
		return $tabs;
	}

	/**
	 * Render the specifications tab content.
	 */
	public static function render_specs_tab() {
		global $product;
		if ( ! $product ) {
			return;
		}
		?>
		<div class="cdc-specs">
			<?php if ( $product->get_meta( '_cdc_dimensions' ) ) : ?>
				<p><strong><?php esc_html_e( 'Dimensions:', 'collectors-display-core' ); ?></strong> <?php echo esc_html( $product->get_meta( '_cdc_dimensions' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $product->get_meta( '_cdc_materials' ) ) : ?>
				<p><strong><?php esc_html_e( 'Materials:', 'collectors-display-core' ); ?></strong> <?php echo esc_html( $product->get_meta( '_cdc_materials' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $product->get_meta( '_cdc_includes' ) ) : ?>
				<p><strong><?php esc_html_e( 'What\'s included:', 'collectors-display-core' ); ?></strong> <?php echo esc_html( $product->get_meta( '_cdc_includes' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $product->get_meta( '_cdc_care' ) ) : ?>
				<p><strong><?php esc_html_e( 'Care instructions:', 'collectors-display-core' ); ?></strong> <?php echo esc_html( $product->get_meta( '_cdc_care' ) ); ?></p>
			<?php endif; ?>
			<p><strong><?php esc_html_e( 'Shipping:', 'collectors-display-core' ); ?></strong> <?php esc_html_e( 'Dispatched in 2–4 working days with fragile-safe packaging. Personalization adds up to 2 working days.', 'collectors-display-core' ); ?></p>
			<p><strong><?php esc_html_e( 'Returns:', 'collectors-display-core' ); ?></strong> <?php esc_html_e( '30-day returns on unused items. Personalized items are non-returnable unless damaged or incorrect.', 'collectors-display-core' ); ?></p>
		</div>
		<?php
	}
}
