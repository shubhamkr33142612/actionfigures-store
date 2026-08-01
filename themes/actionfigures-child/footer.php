<?php
/**
 * Custom footer for the collectible display store.
 *
 * @package ActionFiguresChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

astra_content_bottom();
?>
		</div> <!-- ast-container -->
	</div><!-- #content -->

<?php
astra_content_after();

astra_footer_before();
?>

<footer class="cdc-footer">
	<div class="cdc-container">
		<div class="cdc-footer-top">
			<div class="cdc-footer-brand">
				<span class="cdc-brand-name">Action<em>Figures</em></span>
				<p>Premium display frames, cases, and shadow boxes for die-cast cars, action figures, cards, and memorabilia. Personalization available on selected frames.</p>
			</div>

			<div>
				<h4>Shop</h4>
				<ul>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">All products</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/display-frames/' ) ); ?>">Display frames</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/display-cases/' ) ); ?>">Display cases</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/shadow-boxes/' ) ); ?>">Shadow boxes</a></li>
					<li><a href="<?php echo esc_url( home_url( '/personalized/' ) ); ?>">Personalized</a></li>
				</ul>
			</div>

			<div>
				<h4>Help</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/track-order/' ) ); ?>">Track order</a></li>
					<li><a href="<?php echo esc_url( home_url( '/shipping-returns/' ) ); ?>">Shipping &amp; returns</a></li>
					<li><a href="<?php echo esc_url( home_url( '/refund_returns/' ) ); ?>">Refund policy</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact us</a></li>
				</ul>
			</div>

			<div>
				<h4>Company</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
					<li><a href="<?php echo esc_url( home_url( '/collections/' ) ); ?>">Collections</a></li>
					<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy policy</a></li>
					<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms of service</a></li>
				</ul>
			</div>
		</div>

		<div class="cdc-footer-bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> ActionFigures. All rights reserved.</span>
			<div class="cdc-footer-pay">
				<span>COD available</span>
				<span>UPI</span>
				<span>Cards</span>
			</div>
		</div>
	</div>
</footer>

<?php
astra_footer_after();
?>
	</div><!-- #page -->

<?php
astra_body_bottom();
wp_footer();
?>
	</body>
</html>
