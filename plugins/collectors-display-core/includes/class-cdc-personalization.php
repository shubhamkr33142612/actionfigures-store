<?php
/**
 * Product personalization for collectible display products.
 *
 * @package CollectorsDisplayCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-product personalization: admin config, frontend field, cart/checkout
 * display, order item meta, optional fee, and server-side validation.
 *
 * Cart item keys act as the field schema, so additional fields (image upload,
 * background choice, etc.) can be added later without restructuring.
 */
class CDC_Personalization {

	const NONCE_ACTION = 'cdc_add_to_cart';
	const NONCE_FIELD  = 'cdc_personalization_nonce';

	/**
	 * Hook everything into WordPress/WooCommerce.
	 */
	public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_data_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product_meta' ) );

		add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'render_frontend_field' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );

		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate_add_to_cart' ), 10, 3 );
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 4 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'display_cart_item_data' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'apply_personalization_fee' ) );

		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'save_order_item_meta' ), 10, 4 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'hidden_order_itemmeta' ) );
	}

	/**
	 * Read personalization config from a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return array
	 */
	public static function get_config( $product ) {
		if ( ! $product ) {
			return array();
		}
		return array(
			'enabled'     => 'yes' === $product->get_meta( '_cdc_personalization_enabled' ),
			'required'    => 'yes' === $product->get_meta( '_cdc_personalization_required' ),
			'label'       => $product->get_meta( '_cdc_personalization_label' ) ? $product->get_meta( '_cdc_personalization_label' ) : __( 'Personalized Text', 'collectors-display-core' ),
			'placeholder' => $product->get_meta( '_cdc_personalization_placeholder' ) ? $product->get_meta( '_cdc_personalization_placeholder' ) : __( "e.g. Shubham's Garage", 'collectors-display-core' ),
			'max_length'  => (int) $product->get_meta( '_cdc_personalization_max_length' ) > 0 ? (int) $product->get_meta( '_cdc_personalization_max_length' ) : 40,
			'fee'         => (float) $product->get_meta( '_cdc_personalization_fee' ),
		);
	}

	/**
	 * Register the Personalization tab in the product data metabox.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public static function product_data_tab( $tabs ) {
		$tabs['cdc_personalization'] = array(
			'label'  => __( 'Personalization', 'collectors-display-core' ),
			'target' => 'cdc_personalization_product_data',
			'class'  => array( 'show_if_simple', 'show_if_variable' ),
		);
		return $tabs;
	}

	/**
	 * Render the personalization config panel.
	 */
	public static function product_data_panel() {
		global $post;
		$product = wc_get_product( $post->ID );
		$config  = self::get_config( $product );
		?>
		<div id="cdc_personalization_product_data" class="panel woocommerce_options_panel">
			<div class="options_group">
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => '_cdc_personalization_enabled',
						'label'       => __( 'Enable personalization', 'collectors-display-core' ),
						'description' => __( 'Let customers add personalized text to this product.', 'collectors-display-core' ),
					)
				);
				woocommerce_wp_checkbox(
					array(
						'id'          => '_cdc_personalization_required',
						'label'       => __( 'Require personalization', 'collectors-display-core' ),
						'description' => __( 'Customers must enter personalized text before adding to cart.', 'collectors-display-core' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'          => '_cdc_personalization_label',
						'label'       => __( 'Field label', 'collectors-display-core' ),
						'placeholder' => 'Personalized Text',
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'          => '_cdc_personalization_placeholder',
						'label'       => __( 'Placeholder', 'collectors-display-core' ),
						'placeholder' => "e.g. Shubham's Garage",
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'                => '_cdc_personalization_max_length',
						'label'             => __( 'Maximum characters', 'collectors-display-core' ),
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => '1',
							'max'  => '200',
							'step' => '1',
						),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'                => '_cdc_personalization_fee',
						'label'             => __( 'Personalization fee (₹)', 'collectors-display-core' ),
						'description'       => __( 'Added to the product price when personalization is used.', 'collectors-display-core' ),
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => '0',
							'step' => '1',
						),
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Persist personalization config. WC verifies nonce + capability first.
	 *
	 * @param int $post_id Product ID.
	 */
	public static function save_product_meta( $post_id ) {
		update_post_meta( $post_id, '_cdc_personalization_enabled', isset( $_POST['_cdc_personalization_enabled'] ) ? 'yes' : 'no' );
		update_post_meta( $post_id, '_cdc_personalization_required', isset( $_POST['_cdc_personalization_required'] ) ? 'yes' : 'no' );

		$label = isset( $_POST['_cdc_personalization_label'] ) ? sanitize_text_field( wp_unslash( $_POST['_cdc_personalization_label'] ) ) : '';
		update_post_meta( $post_id, '_cdc_personalization_label', '' === $label ? __( 'Personalized Text', 'collectors-display-core' ) : $label );

		$placeholder = isset( $_POST['_cdc_personalization_placeholder'] ) ? sanitize_text_field( wp_unslash( $_POST['_cdc_personalization_placeholder'] ) ) : '';
		update_post_meta( $post_id, '_cdc_personalization_placeholder', $placeholder );

		$max = isset( $_POST['_cdc_personalization_max_length'] ) ? max( 1, (int) $_POST['_cdc_personalization_max_length'] ) : 40;
		update_post_meta( $post_id, '_cdc_personalization_max_length', $max );

		$fee = isset( $_POST['_cdc_personalization_fee'] ) ? max( 0, (float) $_POST['_cdc_personalization_fee'] ) : 0;
		update_post_meta( $post_id, '_cdc_personalization_fee', $fee );
	}

	/**
	 * Render the personalization field on the single product page.
	 */
	public static function render_frontend_field() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$config = self::get_config( $product );
		if ( empty( $config ) || ! $config['enabled'] ) {
			return;
		}
		$required = $config['required'] ? ' required' : '';
		?>
		<div class="cdc-personalization" data-required="<?php echo $config['required'] ? 'yes' : 'no'; ?>">
			<label for="cdc_personalized_text">
				<?php echo esc_html( $config['label'] ); ?><?php echo $config['required'] ? ' <span class="cdc-required">*</span>' : ''; ?>
			</label>
			<input
				type="text"
				id="cdc_personalized_text"
				name="cdc_personalized_text"
				class="input-text"
				maxlength="<?php echo esc_attr( $config['max_length'] ); ?>"
				placeholder="<?php echo esc_attr( $config['placeholder'] ); ?>"
				autocomplete="off"
			/>
			<span class="cdc-count">0/<?php echo esc_html( $config['max_length'] ); ?></span>
			<?php if ( $config['fee'] > 0 ) : ?>
				<p class="cdc-fee">
					<?php
					printf(
						/* translators: %s: formatted fee */
						esc_html__( 'Personalization fee: %s', 'collectors-display-core' ),
						wp_kses_post( wc_price( $config['fee'] ) )
					);
					?>
				</p>
			<?php endif; ?>
			<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD ); ?>
		</div>
		<?php
	}

	/**
	 * Enqueue the frontend script only where personalization is used.
	 */
	public static function register_assets() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		$product = wc_get_product( get_queried_object_id() );
		$config  = self::get_config( $product );
		if ( empty( $config ) || ! $config['enabled'] ) {
			return;
		}
		wp_enqueue_script(
			'cdc-personalization',
			CDC_PLUGIN_URL . 'assets/cdc-frontend.js',
			array(),
			CDC_VERSION,
			true
		);
	}

	/**
	 * Validate personalization input before add-to-cart.
	 *
	 * @param bool $passed     Whether validation already passed.
	 * @param int  $product_id Product ID.
	 * @param int  $quantity   Quantity.
	 * @return bool
	 */
	public static function validate_add_to_cart( $passed, $product_id, $quantity ) {
		$product = wc_get_product( $product_id );
		$config  = self::get_config( $product );
		if ( empty( $config ) || ! $config['enabled'] ) {
			return $passed;
		}

		$submitted = isset( $_POST['cdc_personalized_text'] );

		if ( $submitted ) {
			$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
				wc_add_notice( __( 'Security check failed. Please try again.', 'collectors-display-core' ), 'error' );
				return false;
			}
		}

		$text  = $submitted ? sanitize_text_field( wp_unslash( $_POST['cdc_personalized_text'] ) ) : '';
		$text  = trim( $text );

		if ( $config['required'] && '' === $text ) {
			wc_add_notice(
				sprintf(
					/* translators: %s: field label */
					__( '%s is required for this product.', 'collectors-display-core' ),
					$config['label']
				),
				'error'
			);
			return false;
		}

		if ( '' !== $text && mb_strlen( $text ) > $config['max_length'] ) {
			wc_add_notice(
				sprintf(
					/* translators: 1: field label, 2: max length */
					__( '%1$s must be %2$d characters or fewer.', 'collectors-display-core' ),
					$config['label'],
					$config['max_length']
				),
				'error'
			);
			return false;
		}

		return $passed;
	}

	/**
	 * Store personalization against the cart item.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @param int   $product_id     Product ID.
	 * @param int   $variation_id   Variation ID.
	 * @param int   $quantity       Quantity.
	 * @return array
	 */
	public static function add_cart_item_data( $cart_item_data, $product_id, $variation_id = 0, $quantity = 1 ) {
		$purchased = wc_get_product( $variation_id ? $variation_id : $product_id );
		$config    = self::get_config( wc_get_product( $product_id ) );
		if ( empty( $config ) || ! $config['enabled'] ) {
			return $cart_item_data;
		}
		if ( empty( $_POST['cdc_personalized_text'] ) ) {
			return $cart_item_data;
		}

		$text = sanitize_text_field( wp_unslash( $_POST['cdc_personalized_text'] ) );
		if ( '' === trim( $text ) ) {
			return $cart_item_data;
		}

		$cart_item_data['cdc_personalized_text']          = $text;
		$cart_item_data['cdc_personalization_label']      = $config['label'];
		$cart_item_data['cdc_personalization_fee']        = $config['fee'];
		$cart_item_data['cdc_personalization_base_price'] = (float) $purchased->get_price();

		return $cart_item_data;
	}

	/**
	 * Show personalization in the cart and checkout review.
	 *
	 * @param array $item_data Item display data.
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public static function display_cart_item_data( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['cdc_personalized_text'] ) ) {
			$item_data[] = array(
				'key'   => $cart_item['cdc_personalization_label'] ? $cart_item['cdc_personalization_label'] : __( 'Personalized Text', 'collectors-display-core' ),
				'value' => wptexturize( $cart_item['cdc_personalized_text'] ),
			);
		}
		if ( ! empty( $cart_item['cdc_personalization_fee'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Personalization fee', 'collectors-display-core' ),
				'value' => wc_price( (float) $cart_item['cdc_personalization_fee'] ),
			);
		}
		return $item_data;
	}

	/**
	 * Add the personalization fee to the line total.
	 *
	 * Resets to the stored base price first so the calculation is idempotent
	 * even though WooCommerce recalculates totals several times per request.
	 *
	 * @param WC_Cart $cart Cart object.
	 */
	public static function apply_personalization_fee( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		if ( ! $cart instanceof WC_Cart ) {
			return;
		}
		foreach ( $cart->get_cart() as $cart_item ) {
			if ( empty( $cart_item['cdc_personalization_fee'] ) || empty( $cart_item['cdc_personalization_base_price'] ) ) {
				continue;
			}
			$cart_item['data']->set_price( (float) $cart_item['cdc_personalization_base_price'] + (float) $cart_item['cdc_personalization_fee'] );
		}
	}

	/**
	 * Save readable personalization meta against the order line item.
	 *
	 * @param WC_Order_Item_Product $item           Order item.
	 * @param string                $cart_item_key  Cart item key.
	 * @param array                 $values         Cart item values.
	 * @param WC_Order              $order          Order.
	 */
	public static function save_order_item_meta( $item, $cart_item_key, $values, $order ) {
		if ( empty( $values['cdc_personalized_text'] ) ) {
			return;
		}
		$label = $values['cdc_personalization_label'] ? $values['cdc_personalization_label'] : __( 'Personalized Text', 'collectors-display-core' );
		$item->add_meta_data( $label, $values['cdc_personalized_text'], true );
	}

	/**
	 * Keep raw internal meta keys out of emails, admin, and order views.
	 *
	 * @param array $keys Hidden keys.
	 * @return array
	 */
	public static function hidden_order_itemmeta( $keys ) {
		$keys[] = 'cdc_personalized_text';
		$keys[] = 'cdc_personalization_label';
		$keys[] = 'cdc_personalization_fee';
		$keys[] = 'cdc_personalization_base_price';
		return $keys;
	}
}
