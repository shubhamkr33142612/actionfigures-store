<?php
/**
 * Homepage for the collectible display store.
 *
 * Light + premium gallery presentation with a showcase-grade motion layer:
 * parallax hero, floating chip, marquee, sticky stacked-card showcase,
 * animated counters, and scroll reveals. Pulls live products, categories,
 * and collectible types from WordPress. Business logic stays in the plugin.
 *
 * @package ActionFiguresChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

?>
<main id="cdc-main" class="cdc-main">
<?php
$cdc_featured = wc_get_products(
	array(
		'featured' => true,
		'limit'    => 6,
		'status'   => 'publish',
	)
);
$cdc_hero_product      = ! empty( $cdc_featured ) ? $cdc_featured[0] : null;
$cdc_banner_products   = array_slice( $cdc_featured, 0, 2 );
$cdc_marquee           = array(
	'Free shipping over &#8377;1,999',
	'30-day returns',
	'Personalization available',
	'Fragile-safe dispatch',
	'Made for collectors',
);
$cdc_marquee_html      = '';
foreach ( $cdc_marquee as $cdc_marquee_item ) {
	$cdc_marquee_html .= '<span>' . wp_kses_post( $cdc_marquee_item ) . '</span>';
	$cdc_marquee_html .= '<span class="cdc-marquee-star" aria-hidden="true">&#10022;</span>';
}
?>

<div class="cdc-home">

	<section class="cdc-hero">
		<div class="cdc-container">
			<div class="cdc-hero-inner">
				<div class="cdc-hero-copy" data-cdc-parallax data-cdc-speed="0.06">
					<p class="cdc-hero-eyebrow">Collectible Display Frames &middot; Cases &middot; Shadow Boxes</p>
					<h1>Your collection <em>deserves</em> to be seen.</h1>
					<p class="cdc-hero-sub">Premium display products for die-cast cars, miniatures, action figures, cards, and memorabilia &mdash; with personalization that makes every showcase yours.</p>
					<div class="cdc-hero-cta">
						<a class="cdc-btn cdc-btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop the collection</a>
						<a class="cdc-btn cdc-btn-outline" href="<?php echo esc_url( home_url( '/personalized/' ) ); ?>">Personalize a frame</a>
					</div>
					<ul class="cdc-hero-trust">
						<li>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
							Free shipping over &#8377;1,999
						</li>
						<li>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
							30-day returns
						</li>
						<li>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
							Fragile-safe dispatch
						</li>
					</ul>
				</div>

				<div class="cdc-hero-visual" data-cdc-parallax data-cdc-speed="0.16">
					<div class="cdc-hero-float">
						<?php if ( $cdc_hero_product ) : ?>
							<div class="cdc-hero-card" data-cdc-tilt data-cdc-tilt-max="9">
								<?php echo wp_kses_post( $cdc_hero_product->get_image( 'woocommerce_single', array( 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ) ); ?>
								<div class="cdc-hero-badge">
									<div>
										<strong><?php echo esc_html( $cdc_hero_product->get_name() ); ?></strong>
										<span><?php echo esc_html( wp_strip_all_tags( wc_get_product_category_list( $cdc_hero_product->get_id() ) ) ); ?></span>
									</div>
									<span><?php echo wp_kses_post( $cdc_hero_product->get_price_html() ); ?></span>
								</div>
							</div>
						<?php else : ?>
							<div class="cdc-hero-card" aria-hidden="true"></div>
						<?php endif; ?>
					</div>
					<div class="cdc-float-chip cdc-float-slow">Free shipping over &#8377;1,999</div>
				</div>
			</div>
		</div>
	</section>

	<section class="cdc-marquee" aria-hidden="true">
		<div class="cdc-marquee-track">
			<span class="cdc-marquee-group"><?php echo $cdc_marquee_html; ?></span>
			<span class="cdc-marquee-group"><?php echo $cdc_marquee_html; ?></span>
		</div>
	</section>

	<section class="cdc-features" aria-label="Store highlights">
		<div class="cdc-container">
			<div class="cdc-features-grid">
				<div class="cdc-feature">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="22" height="22" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
					<div>
						<h3>Exact dimensions &amp; materials</h3>
						<p>Real measurements, materials, and contents on every product page.</p>
					</div>
				</div>
				<div class="cdc-feature">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="22" height="22" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
					<div>
						<h3>Personalization, printed clean</h3>
						<p>Your name or collection title on selected frames and shadow boxes.</p>
					</div>
				</div>
				<div class="cdc-feature">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="22" height="22" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
					<div>
						<h3>Fragile-safe, fast dispatch</h3>
						<p>Ships in 2&ndash;4 working days with protective packaging.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="cdc-section cdc-categories">
		<div class="cdc-container">
			<div class="cdc-section-head row" data-cdc-reveal="slide-right">
				<div>
					<p class="cdc-section-eyebrow">Shop by display type</p>
					<h2>Find the right showcase</h2>
					<p class="cdc-section-sub">Frames, cases, and shadow boxes &mdash; sized for the way you collect.</p>
				</div>
				<a class="cdc-link-arrow" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
					View all products
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
				</a>
			</div>
			<div class="cdc-cat-grid">
				<?php
				$categories = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'parent'     => 0,
						'menu_order' => 'ASC',
					)
				);
				$shown = 0;
				foreach ( $categories as $category ) {
					if ( 'uncategorized' === $category->slug || $shown >= 8 ) {
						continue;
					}
					$shown++;
					?>
					<a class="cdc-cat-tile" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
						<span class="cdc-cat-arrow">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="18" height="18" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
						</span>
						<span class="cdc-cat-name"><?php echo esc_html( $category->name ); ?></span>
						<span class="cdc-cat-count"><?php echo esc_html( $category->count ); ?> <?php echo esc_html( 1 === $category->count ? 'product' : 'products' ); ?></span>
					</a>
					<?php
				}
				?>
			</div>
		</div>
	</section>

	<section class="cdc-section cdc-featured">
		<div class="cdc-container">
			<div class="cdc-section-head row">
				<div>
					<p class="cdc-section-eyebrow">Best sellers</p>
					<h2>Chosen by our team</h2>
					<p class="cdc-section-sub">Featured frames, cases, and shadow boxes our collectors reach for first.</p>
				</div>
				<a class="cdc-link-arrow" href="<?php echo esc_url( home_url( '/best-sellers/' ) ); ?>">
					See all best sellers
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
				</a>
			</div>
			<?php if ( $cdc_featured ) : ?>
				<div class="cdc-grid">
					<?php foreach ( array_slice( $cdc_featured, 0, 4 ) as $product ) : ?>
						<div class="cdc-card">
							<a class="cdc-card-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>"></a>
							<?php actionfigures_child_wishlist_button( $product ); ?>
							<div class="cdc-card-media"><?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?></div>
							<div class="cdc-card-body">
								<span class="cdc-card-cat"><?php echo wp_kses_post( wc_get_product_category_list( $product->get_id(), ', ' ) ); ?></span>
								<h3><?php echo esc_html( $product->get_name() ); ?></h3>
								<span class="cdc-card-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
								<?php if ( 'yes' === $product->get_meta( '_cdc_personalization_enabled' ) ) : ?>
									<span class="cdc-card-badge">Personalizable</span>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="cdc-empty">No featured products yet. Import demo content from <strong>WooCommerce &rarr; Demo Content</strong>.</p>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( ! empty( $cdc_featured ) ) : ?>
		<section class="cdc-showcase" aria-label="Layered display products">
			<div class="cdc-showcase-pin">
				<div class="cdc-container cdc-showcase-grid">
					<div class="cdc-showcase-copy" data-cdc-reveal="fade">
						<p class="cdc-section-eyebrow">The collection</p>
						<h2>Displays that stack up.</h2>
						<p>Frames, cases, and shadow boxes designed to sit side by side &mdash; and shine as one wall.</p>
						<a class="cdc-btn cdc-btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Build your wall</a>
					</div>
					<div class="cdc-showcase-stack">
						<?php foreach ( array_slice( $cdc_featured, 0, 3 ) as $product ) : ?>
							<div class="cdc-stack-card">
								<?php echo wp_kses_post( $product->get_image( 'woocommerce_single', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?>
								<span class="cdc-stack-label"><?php echo esc_html( $product->get_name() ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="cdc-section">
		<div class="cdc-container">
			<div class="cdc-banner cdc-personalized-banner" data-cdc-reveal="zoom">
				<div class="cdc-banner-inner">
					<div>
						<p class="cdc-section-eyebrow">Personalization</p>
						<h2>Make it yours.</h2>
						<p>Add your name, your garage, your collection title &mdash; printed cleanly on selected frames and shadow boxes.</p>
						<a class="cdc-btn cdc-btn-light" href="<?php echo esc_url( home_url( '/personalized/' ) ); ?>">Explore personalized displays</a>
					</div>
					<div class="cdc-banner-visual">
						<?php if ( ! empty( $cdc_banner_products[0] ) ) : ?>
							<div class="bimg"><?php echo wp_kses_post( $cdc_banner_products[0]->get_image( 'woocommerce_medium_large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $cdc_banner_products[1] ) ) : ?>
							<div class="bimg"><?php echo wp_kses_post( $cdc_banner_products[1]->get_image( 'woocommerce_medium_large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="cdc-section cdc-new">
		<div class="cdc-container">
			<div class="cdc-section-head row" data-cdc-reveal="slide-left">
				<div>
					<p class="cdc-section-eyebrow">New arrivals</p>
					<h2>Fresh to the collection</h2>
					<p class="cdc-section-sub">The latest additions to the range.</p>
				</div>
				<a class="cdc-link-arrow" href="<?php echo esc_url( home_url( '/new-arrivals/' ) ); ?>">
					See what's new
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
				</a>
			</div>
			<?php
			$new = new WP_Query(
				array(
					'post_type'           => 'product',
					'posts_per_page'      => 4,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'ignore_sticky_posts' => true,
				)
			);
			if ( $new->have_posts() ) :
				?>
				<div class="cdc-grid">
					<?php
					while ( $new->have_posts() ) :
						$new->the_post();
						$product = wc_get_product( get_the_ID() );
						?>
						<div class="cdc-card">
							<a class="cdc-card-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( $product ? $product->get_name() : '' ); ?>"></a>
							<?php if ( $product ) : ?>
								<?php actionfigures_child_wishlist_button( $product ); ?>
							<?php endif; ?>
							<div class="cdc-card-media"><?php echo $product ? wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ) : ''; ?></div>
							<div class="cdc-card-body">
								<span class="cdc-card-cat"><?php echo $product ? wp_kses_post( wc_get_product_category_list( $product->get_id(), ', ' ) ) : ''; ?></span>
								<h3><?php the_title(); ?></h3>
								<?php if ( $product ) : ?>
									<span class="cdc-card-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
									<?php if ( 'yes' === $product->get_meta( '_cdc_personalization_enabled' ) ) : ?>
										<span class="cdc-card-badge">Personalizable</span>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<p class="cdc-empty">No products yet. Import demo content from <strong>WooCommerce &rarr; Demo Content</strong>.</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="cdc-section cdc-collectibles">
		<div class="cdc-container">
			<div class="cdc-section-head center">
				<p class="cdc-section-eyebrow">Built for your collection</p>
				<h2>One display, many collectibles</h2>
				<p class="cdc-section-sub">Pick your collectible type to see compatible products.</p>
			</div>
			<div class="cdc-chip-grid center">
				<?php
				$types = get_terms(
					array(
						'taxonomy'   => 'collectible_type',
						'hide_empty' => false,
					)
				);
				foreach ( $types as $type ) {
					?>
					<a class="cdc-chip" data-cdc-reveal href="<?php echo esc_url( get_term_link( $type ) ); ?>"><?php echo esc_html( $type->name ); ?></a>
					<?php
				}
				?>
			</div>
		</div>
	</section>

	<section class="cdc-stats">
		<div class="cdc-container cdc-stats-grid">
			<div class="cdc-stats-item">
				<span class="cdc-stat-num" data-cdc-count="2400" data-cdc-suffix="+">2,400+</span>
				<span class="cdc-stat-label">Happy collectors</span>
			</div>
			<div class="cdc-stats-item">
				<span class="cdc-stat-num" data-cdc-count="50" data-cdc-suffix="+">50+</span>
				<span class="cdc-stat-label">Display styles</span>
			</div>
			<div class="cdc-stats-item">
				<span class="cdc-stat-num" data-cdc-count="4.9" data-cdc-decimals="1" data-cdc-suffix="/5">4.9/5</span>
				<span class="cdc-stat-label">Average rating</span>
			</div>
			<div class="cdc-stats-item">
				<span class="cdc-stat-num" data-cdc-count="100" data-cdc-suffix="%">100%</span>
				<span class="cdc-stat-label">Quality checked</span>
			</div>
		</div>
	</section>

	<?php
	$cdc_gallery = wc_get_products(
		array(
			'limit'    => 9,
			'status'   => 'publish',
			'orderby'  => 'date',
			'order'    => 'DESC',
		)
	);
	if ( $cdc_gallery ) :
		?>
		<section class="cdc-section cdc-gallery">
			<div class="cdc-container">
				<div class="cdc-section-head row">
					<div>
						<p class="cdc-section-eyebrow">#ShowcaseYourWall</p>
						<h2>Collector spotlight</h2>
						<p class="cdc-section-sub">Real walls, real cases — shared by collectors like you.</p>
					</div>
					<a class="cdc-link-arrow" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer">
						Follow the community
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
					</a>
				</div>
				<div class="cdc-gallery-grid">
					<?php foreach ( $cdc_gallery as $cdc_gi => $cdc_g_product ) : ?>
						<div class="cdc-gallery-tile<?php echo 0 === $cdc_gi ? ' cdc-gallery-tile-featured' : ''; ?>" data-cdc-reveal>
							<a class="cdc-gallery-link" href="<?php echo esc_url( $cdc_g_product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $cdc_g_product->get_name() ); ?>"></a>
							<?php actionfigures_child_wishlist_button( $cdc_g_product ); ?>
							<?php echo wp_kses_post( $cdc_g_product->get_image( 0 === $cdc_gi ? 'woocommerce_medium_large' : 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?>
							<div class="cdc-gallery-overlay">
								<span class="cdc-gallery-cat"><?php echo wp_kses_post( wc_get_product_category_list( $cdc_g_product->get_id(), ', ' ) ); ?></span>
								<span class="cdc-gallery-name"><?php echo esc_html( $cdc_g_product->get_name() ); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="cdc-section cdc-about">
		<div class="cdc-container">
			<div class="cdc-about" data-cdc-reveal="fade">
				<p class="cdc-section-eyebrow">The store</p>
				<h2>Premium display, honest details</h2>
				<p>Exact dimensions, materials, and what's included on every product page. Collectibles shown in product photographs are for demonstration purposes unless explicitly included.</p>
				<a class="cdc-btn cdc-btn-outline" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About the store</a>
			</div>
		</div>
	</section>

</div>

</main>

<?php get_footer(); ?>
