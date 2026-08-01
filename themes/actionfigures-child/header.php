<?php
/**
 * Custom header for the collectible display store.
 *
 * Replaces the Astra header builder with a premium light-gallery header:
 * utility bar, wordmark, primary nav (with dropdowns), account/cart icons,
 * and a mobile drawer.
 *
 * @package ActionFiguresChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cdc_cart_count = 0;
if ( function_exists( 'WC' ) && WC()->cart ) {
	$cdc_cart_count = WC()->cart->get_cart_contents_count();
}
$cdc_cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$cdc_account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<script>
(function () {
	if (!window.matchMedia || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}
	document.documentElement.classList.add('cdc-anim');
})();
</script>
<head>
<?php astra_head_top(); ?>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ( apply_filters( 'astra_header_profile_gmpg_link', true ) ) : ?>
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php endif; ?>
<?php wp_head(); ?>
<?php astra_head_bottom(); ?>
</head>

<body <?php astra_schema_body(); ?> <?php body_class(); ?>>
<?php astra_body_top(); ?>
<?php wp_body_open(); ?>

<div id="cdc-progress" aria-hidden="true"></div>

<a class="skip-link screen-reader-text" href="#content"><?php echo esc_html( astra_default_strings( 'string-header-skip-link', false ) ); ?></a>

<div id="page" class="hfeed site">
	<?php astra_header_before(); ?>

	<div class="cdc-topbar">
		<div class="cdc-container">
			<div class="cdc-topbar-left">
				<ul>
					<li>Free shipping over &#8377;1,999</li>
					<li>30-day returns</li>
				</ul>
			</div>
			<div class="cdc-topbar-right">
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/track-order/' ) ); ?>">Track order</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
				</ul>
			</div>
		</div>
	</div>

	<header class="cdc-header">
		<div class="cdc-container">
			<a class="cdc-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="cdc-brand-mark">AF</span>
				<span class="cdc-brand-name">Action<em>Figures</em></span>
			</a>

			<nav class="cdc-nav" aria-label="<?php esc_attr_e( 'Primary', 'actionfigures-child' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => '',
						'fallback_cb'    => false,
						'depth'          => 2,
					)
				);
				?>
			</nav>

			<div class="cdc-actions">
				<button class="cdc-icon-btn" id="cdc-wishlist-toggle" aria-label="<?php echo esc_attr__( 'Wishlist', 'actionfigures-child' ); ?>" aria-expanded="false" aria-controls="cdc-wishlist-panel">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
					<span class="cdc-count-badge cdc-wishlist-count" id="cdc-wishlist-count" hidden>0</span>
				</button>
				<a class="cdc-icon-btn" href="<?php echo esc_url( $cdc_account_url ); ?>" aria-label="<?php echo esc_attr__( 'My account', 'actionfigures-child' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
				</a>
				<a class="cdc-icon-btn" href="<?php echo esc_url( $cdc_cart_url ); ?>" aria-label="<?php echo esc_attr__( 'Cart', 'actionfigures-child' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" /></svg>
					<span class="cdc-count-badge cdc-cart-count" id="cdc-cart-count"<?php echo $cdc_cart_count > 0 ? '' : ' hidden'; ?>><?php echo esc_html( $cdc_cart_count ); ?></span>
				</a>
				<button class="cdc-burger" id="cdc-burger" aria-label="<?php echo esc_attr__( 'Open menu', 'actionfigures-child' ); ?>" aria-expanded="false" aria-controls="cdc-drawer">
					<span></span><span></span><span></span>
				</button>
			</div>
			<span id="cdc-wl-live" class="screen-reader-text" aria-live="polite"></span>
		</div>
	</header>

	<div class="cdc-drawer" id="cdc-drawer" aria-hidden="true">
		<div class="cdc-drawer-backdrop" id="cdc-drawer-backdrop"></div>
		<div class="cdc-drawer-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Menu', 'actionfigures-child' ); ?>">
			<div class="cdc-drawer-head">
				<span class="cdc-brand-name">Action<em>Figures</em></span>
				<button class="cdc-drawer-close" id="cdc-drawer-close" aria-label="<?php echo esc_attr__( 'Close menu', 'actionfigures-child' ); ?>">&times;</button>
			</div>
			<nav class="cdc-drawer-nav" aria-label="<?php esc_attr_e( 'Mobile', 'actionfigures-child' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => '',
						'fallback_cb'    => false,
						'depth'          => 2,
					)
				);
				?>
			</nav>
			<div class="cdc-drawer-cta">
				<a class="cdc-btn cdc-btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop the collection</a>
				<a class="cdc-btn cdc-btn-outline" href="<?php echo esc_url( home_url( '/personalized/' ) ); ?>">Personalize</a>
			</div>
		</div>
	</div>

	<?php astra_header_after(); ?>

	<div class="cdc-wishlist-panel" id="cdc-wishlist-panel" role="dialog" aria-modal="true" aria-label="Wishlist" data-shop-url="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
		<div class="cdc-wishlist-panel-head">
			<span>Wishlist</span>
			<button class="cdc-wishlist-panel-close" id="cdc-wishlist-panel-close" aria-label="Close wishlist">&times;</button>
		</div>
		<div class="cdc-wishlist-panel-list" id="cdc-wishlist-panel-list"></div>
	</div>

	<?php astra_content_before(); ?>
	<div id="content" class="site-content">
		<div class="ast-container">
		<?php astra_content_top(); ?>
