<?php
/**
 * Taxonomies for collectible display products.
 *
 * @package CollectorsDisplayCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers custom taxonomies that scale as the catalog grows.
 *
 * The collectible compatibility axis is deliberately a non-hierarchical,
 * many-to-many taxonomy instead of a product category. A display frame can
 * suit several collectible types at once, and tomorrow's new collectible
 * categories are simply new admin-created terms - no code changes.
 */
class CDC_Taxonomies {

	const COLLECTIBLE_TYPE = 'collectible_type';

	/**
	 * Hook taxonomy registrations into WordPress.
	 */
	public static function register() {
		add_action( 'init', array( __CLASS__, 'register_collectible_type_taxonomy' ) );
	}

	/**
	 * Registers the collectible compatibility taxonomy.
	 */
	public static function register_collectible_type_taxonomy() {
		register_taxonomy(
			self::COLLECTIBLE_TYPE,
			array( 'product' ),
			array(
				'labels'            => array(
					'name'          => __( 'Collectible Types', 'collectors-display-core' ),
					'singular_name' => __( 'Collectible Type', 'collectors-display-core' ),
					'menu_name'     => __( 'Collectible Types', 'collectors-display-core' ),
					'search_items'  => __( 'Search Collectible Types', 'collectors-display-core' ),
					'all_items'     => __( 'All Collectible Types', 'collectors-display-core' ),
					'edit_item'     => __( 'Edit Collectible Type', 'collectors-display-core' ),
					'update_item'   => __( 'Update Collectible Type', 'collectors-display-core' ),
					'add_new_item'  => __( 'Add New Collectible Type', 'collectors-display-core' ),
					'new_item_name' => __( 'New Collectible Type Name', 'collectors-display-core' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'show_ui'           => true,
				'rewrite'           => array(
					'slug'       => 'collectible-type',
					'with_front' => false,
				),
				'query_var'         => true,
			)
		);
	}
}
