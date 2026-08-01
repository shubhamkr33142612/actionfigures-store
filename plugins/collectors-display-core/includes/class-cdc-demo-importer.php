<?php
/**
 * Demo content importer for the collectible display store.
 *
 * @package CollectorsDisplayCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-click demo products so the storefront has data to display.
 *
 * Idempotent: re-importing skips products that already exist. Images are
 * generated as PNG placeholders via GD, so no external services are needed.
 */
class CDC_Demo_Importer {

	/** @var string */
	private static $notice = '';

	/** @var array */
	private static $demo_slugs = array(
		'collector-display-frame-medium',
		'acrylic-display-case-single',
		'personalized-shadow-box',
		'wall-display-shelf',
		'die-cast-display-riser',
		'miniature-display-case-2pack',
		'personalized-collector-frame',
	);

	/**
	 * Hook into admin.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
	}

	/**
	 * Public wrapper for the import routine (used by admin page and CLI).
	 *
	 * @return string Result message.
	 */
	public static function import() {
		return self::import_products();
	}

	/**
	 * Public wrapper for the remove routine (used by admin page and CLI).
	 *
	 * @return string Result message.
	 */
	public static function remove() {
		return self::remove_products();
	}

	/**
	 * Register the Demo Content admin page under WooCommerce.
	 */
	public static function register_admin_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Demo Content', 'collectors-display-core' ),
			__( 'Demo Content', 'collectors-display-core' ),
			'manage_woocommerce',
			'cdc-demo-content',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Render the admin page and handle import/remove actions.
	 */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'collectors-display-core' ) );
		}

		if ( isset( $_POST['cdc_demo_action'] ) && isset( $_POST['cdc_demo_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cdc_demo_nonce'] ) ), 'cdc_demo_import' ) ) {
			$action = sanitize_key( wp_unslash( $_POST['cdc_demo_action'] ) );
			if ( 'import' === $action ) {
				self::$notice = self::import();
			}
			if ( 'remove' === $action ) {
				self::$notice = self::remove();
			}
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Demo Content', 'collectors-display-core' ); ?></h1>
			<?php if ( self::$notice ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( self::$notice ); ?></p></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'Import sample products so the storefront has data to display. Re-running skips products that already exist.', 'collectors-display-core' ); ?></p>
			<p><?php echo esc_html( sprintf( __( 'Demo products currently in the catalog: %d of %d', 'collectors-display-core' ), self::count_imported(), count( self::$demo_slugs ) ) ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'cdc_demo_import', 'cdc_demo_nonce' ); ?>
				<p>
					<button type="submit" class="button button-primary" name="cdc_demo_action" value="import"><?php esc_html_e( 'Import Demo Products', 'collectors-display-core' ); ?></button>
					<button type="submit" class="button" name="cdc_demo_action" value="remove" onclick="return confirm('<?php esc_attr_e( 'Remove all demo products?', 'collectors-display-core' ); ?>');"><?php esc_html_e( 'Remove Demo Products', 'collectors-display-core' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Count how many demo products already exist.
	 *
	 * @return int
	 */
	private static function count_imported() {
		$count = 0;
		foreach ( self::$demo_slugs as $slug ) {
			if ( wc_get_product_id_by_sku( strtoupper( $slug ) ) || get_page_by_path( $slug, OBJECT, 'product' ) ) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Import all demo products.
	 *
	 * @return string Result message.
	 */
	private static function import_products() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return __( 'WooCommerce is not active.', 'collectors-display-core' );
		}
		$created = 0;
		$skipped = 0;

		self::import_variable_frame();
		foreach ( self::simple_products() as $data ) {
			if ( self::product_exists( $data['slug'] ) ) {
				$skipped++;
				continue;
			}
			self::create_simple_product( $data );
			$created++;
		}

		return sprintf(
			/* translators: 1: created, 2: skipped */
			__( 'Demo import complete. Created: %1$d, skipped (already present): %2$d.', 'collectors-display-core' ),
			$created,
			$skipped
		);
	}

	/**
	 * Remove all demo products and their attached images.
	 *
	 * @return string Result message.
	 */
	private static function remove_products() {
		$removed = 0;
		foreach ( self::$demo_slugs as $slug ) {
			$product_id = wc_get_product_id_by_sku( strtoupper( $slug ) );
			if ( ! $product_id ) {
				$page = get_page_by_path( $slug, OBJECT, 'product' );
				$product_id = $page ? $page->ID : 0;
			}
			if ( ! $product_id ) {
				continue;
			}
			wp_delete_post( $product_id, true );
			$removed++;
		}
		return sprintf(
			/* translators: %d: removed count */
			__( 'Removed %d demo product(s).', 'collectors-display-core' ),
			$removed
		);
	}

	/**
	 * Whether a demo product already exists.
	 *
	 * @param string $slug Product slug.
	 * @return bool
	 */
	private static function product_exists( $slug ) {
		return (bool) wc_get_product_id_by_sku( strtoupper( $slug ) ) || (bool) get_page_by_path( $slug, OBJECT, 'product' );
	}

	/**
	 * Definition of the simple demo products.
	 *
	 * @return array
	 */
	private static function simple_products() {
		return array(
			array(
				'slug'         => 'acrylic-display-case-single',
				'sku'          => 'ACRYLIC-DISPLAY-CASE-SINGLE',
				'name'         => 'Acrylic Display Case (Single)',
				'price'        => '1599',
				'short'        => 'Crystal-clear acrylic case that protects and showcases a single collectible.',
				'cats'         => array( 'display-cases', 'acrylic-cases' ),
				'collectibles' => array( 'watches', 'coins' ),
				'attributes'   => array(
					'pa_material'         => array( 'Acrylic' ),
					'pa_orientation'      => array( 'Portrait' ),
					'pa_display-capacity' => array( '1' ),
					'pa_mount-type'       => array( 'Tabletop Stand' ),
				),
				'dimensions'   => array( 12, 12, 18 ),
				'weight'       => 0.9,
				'image'        => array(
					'bg'   => array( 210, 232, 250 ),
					'fg'   => array( 26, 78, 120 ),
					'title' => 'Acrylic Display Case',
					'lines' => array( 'Single collectible', 'Crystal clear acrylic' ),
					'footer' => 'Dimensions: 12 x 12 x 18 cm',
				),
			),
			array(
				'slug'         => 'personalized-shadow-box',
				'sku'          => 'PERSONALIZED-SHADOW-BOX',
				'name'         => 'Personalized Shadow Box',
				'price'        => '3299',
				'sale'         => '2899',
				'featured'     => true,
				'short'        => 'Deep wall-mounted shadow box with room for your own personalized text.',
				'cats'         => array( 'shadow-boxes', 'personalized-displays' ),
				'collectibles' => array( 'sports-memorabilia', 'medals', 'action-figures' ),
				'attributes'   => array(
					'pa_material'         => array( 'Wood' ),
					'pa_orientation'      => array( 'Portrait' ),
					'pa_display-capacity' => array( '6' ),
					'pa_mount-type'       => array( 'Wall Mount' ),
				),
				'dimensions'   => array( 45, 35, 10 ),
				'weight'       => 2.4,
				'personalize'  => array( 'required' => true, 'max' => 30, 'fee' => 299, 'label' => 'Personalized Text' ),
				'image'        => array(
					'bg'   => array( 245, 235, 220 ),
					'fg'   => array( 92, 60, 30 ),
					'title' => 'Personalized Shadow Box',
					'lines' => array( 'Add your own text', 'Wall mounted' ),
					'footer' => 'Personalization available',
				),
			),
			array(
				'slug'         => 'wall-display-shelf',
				'sku'          => 'WALL-DISPLAY-SHELF',
				'name'         => 'Wall Display Shelf',
				'price'        => '1999',
				'short'        => 'Minimal wall shelf to arrange your collection in layers.',
				'cats'         => array( 'wall-displays' ),
				'collectibles' => array( 'miniatures', 'action-figures', 'artwork' ),
				'attributes'   => array(
					'pa_material'         => array( 'MDF' ),
					'pa_display-capacity' => array( '3' ),
					'pa_mount-type'       => array( 'Wall Mount' ),
				),
				'dimensions'   => array( 60, 15, 20 ),
				'weight'       => 1.8,
				'image'        => array(
					'bg'   => array( 235, 240, 245 ),
					'fg'   => array( 55, 70, 90 ),
					'title' => 'Wall Display Shelf',
					'lines' => array( 'Layer your collection', 'Minimal design' ),
					'footer' => 'Wall mounted',
				),
			),
			array(
				'slug'         => 'die-cast-display-riser',
				'sku'          => 'DIE-CAST-DISPLAY-RISER',
				'name'         => 'Die-Cast Display Riser (Set of 3)',
				'price'        => '499',
				'short'        => 'Acrylic risers that step your die-cast models up for a clean sight line.',
				'cats'         => array( 'accessories' ),
				'collectibles' => array( 'die-cast-cars', 'miniatures' ),
				'attributes'   => array(
					'pa_material'         => array( 'Acrylic' ),
					'pa_display-capacity' => array( '3' ),
					'pa_mount-type'       => array( 'Tabletop Stand' ),
				),
				'dimensions'   => array( 20, 6, 3 ),
				'weight'       => 0.4,
				'manage_stock' => true,
				'stock'        => 0,
				'image'        => array(
					'bg'   => array( 255, 240, 240 ),
					'fg'   => array( 130, 40, 40 ),
					'title' => 'Die-Cast Display Riser',
					'lines' => array( 'Set of 3 steps', 'Acrylic' ),
					'footer' => 'Currently out of stock',
				),
			),
			array(
				'slug'         => 'miniature-display-case-2pack',
				'sku'          => 'MINIATURE-DISPLAY-CASE-2PACK',
				'name'         => 'Miniature Display Case (2-Pack)',
				'price'        => '2599',
				'sale'         => '2199',
				'featured'     => true,
				'short'        => 'Two stackable wood display cases for small builds and miniatures.',
				'cats'         => array( 'display-cases', 'wood-cases' ),
				'collectibles' => array( 'miniatures', 'lego-building-sets' ),
				'attributes'   => array(
					'pa_material'         => array( 'Wood' ),
					'pa_display-capacity' => array( '2' ),
					'pa_mount-type'       => array( 'Freestanding' ),
				),
				'dimensions'   => array( 30, 20, 22 ),
				'weight'       => 3.0,
				'image'        => array(
					'bg'   => array( 238, 232, 224 ),
					'fg'   => array( 70, 55, 40 ),
					'title' => 'Miniature Display Case',
					'lines' => array( '2-pack, stackable', 'Wood finish' ),
					'footer' => 'Freestanding',
				),
			),
			array(
				'slug'         => 'personalized-collector-frame',
				'sku'          => 'PERSONALIZED-COLLECTOR-FRAME',
				'name'         => 'Personalized Collector Frame',
				'price'        => '2799',
				'short'        => 'Tabletop frame with optional personalized text and a plain background.',
				'cats'         => array( 'display-frames', 'tabletop-frames', 'personalized-displays' ),
				'collectibles' => array( 'coins', 'watches', 'other-collectibles' ),
				'attributes'   => array(
					'pa_material'         => array( 'Wood' ),
					'pa_orientation'      => array( 'Square' ),
					'pa_display-capacity' => array( '4' ),
					'pa_mount-type'       => array( 'Tabletop Stand' ),
				),
				'dimensions'   => array( 25, 25, 8 ),
				'weight'       => 1.1,
				'personalize'  => array( 'required' => false, 'max' => 50, 'fee' => 0, 'label' => 'Personalized Text' ),
				'image'        => array(
					'bg'   => array( 240, 245, 240 ),
					'fg'   => array( 45, 90, 60 ),
					'title' => 'Personalized Collector Frame',
					'lines' => array( 'Square format', 'Optional text' ),
					'footer' => 'Tabletop stand',
				),
			),
		);
	}

	/**
	 * Create the variable demo product.
	 *
	 * @return void
	 */
	private static function import_variable_frame() {
		$slug = 'collector-display-frame-medium';
		if ( self::product_exists( $slug ) ) {
			return;
		}

		$product = new WC_Product_Variable();
		$product->set_name( 'Collector Display Frame (Medium)' );
		$product->set_slug( $slug );
		$product->set_sku( 'COLLECTOR-DISPLAY-FRAME-MEDIUM' );
		$product->set_short_description( 'Wall-mounted display frame for six collectibles with a garage-style background.' );
		$product->set_description( self::build_description( 'Collector Display Frame (Medium)', 'Dimensions', '40 x 30 x 12 cm', 'Materials', 'Hardwood frame with a matte black finish and tempered-glass front.', 'What\'s included', 'Frame, wall-mount kit, background panel (Garage), and a mini screwdriver.', 'Shipping', 'Dispatched in 2–4 working days. Fragile-safe packaging.', 'Returns', '30-day returns on unused frames. Personalized items are non-returnable unless damaged.' ) );
		$product->set_regular_price( '' );
		$product->set_manage_stock( false );
		$product->set_featured( true );
		$product->set_catalog_visibility( 'visible' );

		$attributes = array();
		$attributes[] = self::make_attribute( 'pa_size', array( 'Medium', 'Large' ), true );
		$attributes[] = self::make_attribute( 'pa_frame-color', array( 'Black', 'Walnut' ), true );
		$attributes[] = self::make_attribute( 'pa_material', array( 'Wood' ), false );
		$attributes[] = self::make_attribute( 'pa_orientation', array( 'Landscape' ), false );
		$attributes[] = self::make_attribute( 'pa_display-capacity', array( '6' ), false );
		$attributes[] = self::make_attribute( 'pa_mount-type', array( 'Wall Mount' ), false );
		$attributes[] = self::make_attribute( 'pa_background-style', array( 'Garage' ), false );
		$product->set_attributes( $attributes );
		$product->set_default_attributes( array( 'pa_size' => 'medium', 'pa_frame-color' => 'black' ) );

		$id = $product->save();

		wp_set_object_terms( $id, array( 'display-frames', 'wall-frames' ), 'product_cat' );
		wp_set_object_terms( $id, array( 'die-cast-cars', 'miniatures' ), 'collectible_type' );
		self::set_structured_meta( $id, 'Medium (6 collectibles)', 'Hardwood, tempered glass, matte black or walnut finish' );

		update_post_meta( $id, '_cdc_personalization_enabled', 'yes' );
		update_post_meta( $id, '_cdc_personalization_required', 'no' );
		update_post_meta( $id, '_cdc_personalization_label', 'Personalized Text' );
		update_post_meta( $id, '_cdc_personalization_placeholder', "e.g. Shubham's Garage" );
		update_post_meta( $id, '_cdc_personalization_max_length', 40 );
		update_post_meta( $id, '_cdc_personalization_fee', 199 );

		$variations = array(
			array( 'pa_size' => 'medium', 'pa_frame-color' => 'black',  'sku' => 'CDF-M-BLK',  'price' => '2499', 'stock' => 25 ),
			array( 'pa_size' => 'medium', 'pa_frame-color' => 'walnut', 'sku' => 'CDF-M-WAL',  'price' => '2799', 'stock' => 15 ),
			array( 'pa_size' => 'large',  'pa_frame-color' => 'black',  'sku' => 'CDF-L-BLK',  'price' => '2999', 'stock' => 20 ),
			array( 'pa_size' => 'large',  'pa_frame-color' => 'walnut', 'sku' => 'CDF-L-WAL',  'price' => '3299', 'stock' => 10 ),
		);
		foreach ( $variations as $var ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $id );
			$variation->set_attributes( array( 'pa_size' => $var['pa_size'], 'pa_frame-color' => $var['pa_frame-color'] ) );
			$variation->set_sku( $var['sku'] );
			$variation->set_regular_price( $var['price'] );
			$variation->set_manage_stock( true );
			$variation->set_stock_quantity( $var['stock'] );
			$variation->set_status( 'publish' );
			$variation->save();
		}

		WC_Product_Variable::sync( $id );
		wc_delete_product_transients( $id );

		self::attach_product_images( $id, array(
			array( 20, 24, 30, 'Collector Display Frame - front' ),
			array( 30, 34, 44, 'Collector Display Frame - angle' ),
		) );
	}

	/**
	 * Create a simple product from a definition array.
	 *
	 * @param array $data Product definition.
	 * @return int
	 */
	private static function create_simple_product( $data ) {
		$product = new WC_Product_Simple();
		$product->set_name( $data['name'] );
		$product->set_slug( $data['slug'] );
		$product->set_sku( $data['sku'] );
		$product->set_short_description( $data['short'] );
		$product->set_description( self::build_description( $data['name'], 'Dimensions', self::format_dimensions( $data['dimensions'] ), 'Materials', implode( ', ', $data['attributes']['pa_material'] ?? array( 'Wood' ) ), 'What\'s included', 'Product, mounting hardware, and care card.', 'Shipping', 'Dispatched in 2–4 working days. Fragile-safe packaging.', 'Returns', '30-day returns on unused items. Personalized items are non-returnable unless damaged.' ) );
		$product->set_regular_price( $data['price'] );
		if ( ! empty( $data['sale'] ) ) {
			$product->set_sale_price( $data['sale'] );
		}
		$product->set_featured( ! empty( $data['featured'] ) );
		$product->set_manage_stock( ! empty( $data['manage_stock'] ) );
		if ( ! empty( $data['manage_stock'] ) ) {
			$product->set_stock_quantity( $data['stock'] );
			$product->set_stock_status( 'outofstock' === ( $data['stock_status'] ?? '' ) ? 'outofstock' : ( $data['stock'] > 0 ? 'instock' : 'outofstock' ) );
		}
		if ( ! empty( $data['dimensions'] ) ) {
			$product->set_length( $data['dimensions'][0] );
			$product->set_width( $data['dimensions'][1] );
			$product->set_height( $data['dimensions'][2] );
		}
		if ( ! empty( $data['weight'] ) ) {
			$product->set_weight( $data['weight'] );
		}

		$attributes = array();
		foreach ( $data['attributes'] as $tax => $terms ) {
			$attributes[] = self::make_attribute( $tax, $terms, false );
		}
		$product->set_attributes( $attributes );

		$id = $product->save();

		wp_set_object_terms( $id, $data['cats'], 'product_cat' );
		wp_set_object_terms( $id, $data['collectibles'], 'collectible_type' );

		self::set_structured_meta( $id, self::format_dimensions( $data['dimensions'] ), implode( ', ', $data['attributes']['pa_material'] ?? array( 'Wood' ) ) );

		if ( ! empty( $data['personalize'] ) ) {
			update_post_meta( $id, '_cdc_personalization_enabled', 'yes' );
			update_post_meta( $id, '_cdc_personalization_required', ! empty( $data['personalize']['required'] ) ? 'yes' : 'no' );
			update_post_meta( $id, '_cdc_personalization_label', $data['personalize']['label'] ?? 'Personalized Text' );
			update_post_meta( $id, '_cdc_personalization_placeholder', "e.g. Shubham's Garage" );
			update_post_meta( $id, '_cdc_personalization_max_length', $data['personalize']['max'] ?? 40 );
			update_post_meta( $id, '_cdc_personalization_fee', $data['personalize']['fee'] ?? 0 );
		}

		self::attach_product_images( $id, array( $data['image'], array( $data['image']['fg'][0] + 20, $data['image']['fg'][1] + 20, $data['image']['fg'][2] + 20, $data['name'] . ' - detail' ) ) );

		return $id;
	}

	/**
	 * Store the structured product fields read by the product page tab.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $dimensions Dimensions text.
	 * @param string $materials  Materials text.
	 */
	private static function set_structured_meta( $product_id, $dimensions, $materials ) {
		update_post_meta( $product_id, '_cdc_dimensions', $dimensions );
		update_post_meta( $product_id, '_cdc_materials', $materials );
		update_post_meta( $product_id, '_cdc_includes', 'Product, mounting hardware, and a care card.' );
		update_post_meta( $product_id, '_cdc_care', 'Wipe with a soft, dry cloth. Avoid abrasive cleaners.' );
	}

	/**
	 * Build a WC_Product_Attribute for a global attribute.
	 *
	 * @param string $taxonomy  Attribute taxonomy (pa_*).
	 * @param array  $values    Term names (or slugs when used for variations).
	 * @param bool   $variation Whether it drives variations.
	 * @return WC_Product_Attribute
	 */
	private static function make_attribute( $taxonomy, $values, $variation = false ) {
		$attribute = new WC_Product_Attribute();
		$attribute->set_id( wc_attribute_taxonomy_id_by_name( $taxonomy ) );
		$attribute->set_name( $taxonomy );
		$attribute->set_options( $values );
		$attribute->set_visible( true );
		$attribute->set_variation( $variation );
		return $attribute;
	}

	/**
	 * Generate and attach product images.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $specs      Image specs.
	 */
	private static function attach_product_images( $product_id, $specs ) {
		$gallery = array();
		$first   = true;
		foreach ( $specs as $index => $spec ) {
			$path = self::make_image( $product_id . '-' . $index, $spec );
			if ( ! $path ) {
				continue;
			}
			$filetype = wp_check_filetype( $path, null );
			$attach_id = wp_insert_attachment(
				array(
					'post_mime_type' => $filetype['type'],
					'post_title'     => $spec[3],
					'post_status'    => 'inherit',
				),
				$path,
				$product_id
			);
			if ( ! $attach_id || is_wp_error( $attach_id ) ) {
				continue;
			}
			require_once ABSPATH . 'wp-admin/includes/image.php';
			wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $path ) );
			if ( $first ) {
				set_post_thumbnail( $product_id, $attach_id );
				$first = false;
			} else {
				$gallery[] = $attach_id;
			}
		}
		if ( $gallery ) {
			update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery ) );
		}
	}

	/**
	 * Generate a placeholder PNG product image with GD.
	 *
	 * @param string $slug File slug.
	 * @param array  $spec bg/fg/title/lines/footer.
	 * @return string|false
	 */
	private static function make_image( $slug, $spec ) {
		if ( ! function_exists( 'imagecreatetruecolor' ) ) {
			return false;
		}
		$w  = 800;
		$h  = 800;
		$im = imagecreatetruecolor( $w, $h );
		if ( ! $im ) {
			return false;
		}
		$bg = imagecolorallocate( $im, $spec['bg'][0], $spec['bg'][1], $spec['bg'][2] );
		$fg = imagecolorallocate( $im, $spec['fg'][0], $spec['fg'][1], $spec['fg'][2] );
		imagefill( $im, 0, 0, $bg );
		imagefilledrectangle( $im, 60, 140, $w - 60, $h - 140, $fg );
		imagefilledrectangle( $im, 80, 160, $w - 80, $h - 160, $bg );

		$font_bold = 'C:\Windows\Fonts\arialbd.ttf';
		$font_reg  = 'C:\Windows\Fonts\arial.ttf';

		if ( file_exists( $font_bold ) && file_exists( $font_reg ) ) {
			imagettftext( $im, 34, 0, 80, 80, $fg, $font_bold, $spec['title'] );
			$y = 300;
			foreach ( $spec['lines'] as $line ) {
				imagettftext( $im, 26, 0, 80, $y, $fg, $font_reg, $line );
				$y += 44;
			}
			imagettftext( $im, 20, 0, 80, $h - 60, $fg, $font_reg, $spec['footer'] );
		} else {
			imagestring( $im, 5, 40, 40, $spec['title'], $fg );
			imagestring( $im, 5, 40, 70, $spec['footer'], $fg );
		}

		$dir = wp_upload_dir();
		$path = trailingslashit( $dir['path'] ) . 'cdc-demo-' . sanitize_file_name( $slug ) . '.png';
		imagepng( $im, $path );
		imagedestroy( $im );
		return $path;
	}

	/**
	 * Format dimensions for display.
	 *
	 * @param array $d Length, width, height in cm.
	 * @return string
	 */
	private static function format_dimensions( $d ) {
		return $d[0] . ' x ' . $d[1] . ' x ' . $d[2] . ' cm (L x W x D)';
	}

	/**
	 * Build the long product description HTML.
	 *
	 * @return string
	 */
	private static function build_description() {
		$args = func_get_args();
		$title = array_shift( $args );
		$html = '<h2>' . esc_html( $title ) . '</h2>';
		$count = count( $args );
		for ( $i = 0; $i + 1 < $count; $i += 2 ) {
			$html .= '<h3>' . esc_html( $args[ $i ] ) . '</h3><p>' . esc_html( $args[ $i + 1 ] ) . '</p>';
		}
		$html .= '<p><em>' . esc_html__( 'Collectibles shown in product photographs are for demonstration purposes unless explicitly included.', 'collectors-display-core' ) . '</em></p>';
		return $html;
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'cdc-demo',
		function ( $args ) {
			$action = $args[0] ?? 'import';
			if ( 'remove' === $action ) {
				WP_CLI::success( CDC_Demo_Importer::remove() );
			} else {
				WP_CLI::success( CDC_Demo_Importer::import() );
			}
		}
	);
}
