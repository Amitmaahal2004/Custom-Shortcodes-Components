<?php
/**
 * Schemes & Services Swiper Slider Shortcode
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a single Scheme slide matching the design.
 * Shared by both initial shortcode rendering and AJAX category filter.
 *
 * @param int $post_id Post ID of the csc_scheme.
 * @return string HTML markup for the swiper slide.
 */
function csc_render_scheme_slide( $post_id ) {
	$title = get_the_title( $post_id );

	// Accent color.
	$accent_color = get_post_meta( $post_id, '_csc_scheme_color', true );
	if ( empty( $accent_color ) ) {
		$accent_color = '#109b48';
	}

	// Compute soft pastel background tint (10-12% opacity).
	$clean_hex = ltrim( $accent_color, '#' );
	if ( 6 === strlen( $clean_hex ) ) {
		$r    = hexdec( substr( $clean_hex, 0, 2 ) );
		$g    = hexdec( substr( $clean_hex, 2, 2 ) );
		$b    = hexdec( substr( $clean_hex, 4, 2 ) );
		$tint = "rgba({$r}, {$g}, {$b}, 0.12)";
	} else {
		$tint = 'rgba(16, 155, 72, 0.12)';
	}

	// Icon.
	$icon_type = get_post_meta( $post_id, '_csc_scheme_icon_type', true );
	$preset    = get_post_meta( $post_id, '_csc_scheme_icon_preset', true );
	$img_id    = get_post_meta( $post_id, '_csc_scheme_icon_image_id', true );
	$icon_html = '';

	if ( 'image' === $icon_type && ! empty( $img_id ) ) {
		$img_url = wp_get_attachment_image_url( $img_id, 'thumbnail' );
		if ( $img_url ) {
			$icon_html = '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy" />';
		}
	}

	if ( empty( $icon_html ) ) {
		if ( ! empty( $preset ) && function_exists( 'csc_get_scheme_svg_icon' ) ) {
			$icon_html = csc_get_scheme_svg_icon( $preset, 32, $accent_color );
		} elseif ( has_post_thumbnail( $post_id ) ) {
			$icon_html = get_the_post_thumbnail( $post_id, array( 32, 32 ), array( 'alt' => esc_attr( $title ) ) );
		} elseif ( function_exists( 'csc_get_scheme_svg_icon' ) ) {
			$icon_html = csc_get_scheme_svg_icon( 'education', 32, $accent_color );
		}
	}

	// Description.
	$desc = get_post_meta( $post_id, '_csc_scheme_short_desc', true );
	if ( empty( $desc ) ) {
		if ( has_excerpt( $post_id ) ) {
			$desc = get_the_excerpt( $post_id );
		} else {
			$raw_content = get_the_content( null, false, $post_id );
			$desc        = wp_trim_words( wp_strip_all_tags( $raw_content ), 18, '...' );
		}
	}

	// Button & Target URL.
	$button_text = get_post_meta( $post_id, '_csc_scheme_button_text', true );
	if ( empty( $button_text ) ) {
		$button_text = __( 'View Details', 'custom-shortcodes-components' );
	}

	$cust_url     = get_post_meta( $post_id, '_csc_scheme_button_url', true );
	$button_url   = ! empty( $cust_url ) ? esc_url( $cust_url ) : get_permalink( $post_id );
	$target_blank = get_post_meta( $post_id, '_csc_scheme_target_blank', true );
	$target_attr  = ! empty( $target_blank ) ? ' target="_blank" rel="noopener noreferrer"' : '';

	ob_start();
	?>
	<div class="swiper-slide">
		<div
			class="csc-scheme-card"
			style="
				--csc-scheme-color: <?php echo esc_attr( $accent_color ); ?>;
				--csc-scheme-tint: <?php echo esc_attr( $tint ); ?>;
			">

			<!-- Full Card Clickable Overlay (Accessibility-friendly) -->
			<a href="<?php echo esc_url( $button_url ); ?>" class="csc-scheme-card__full-link" tabindex="-1" aria-hidden="true" <?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></a>

			<!-- Card Content Body -->
			<div class="csc-scheme-card__body">

				<!-- Icon Circular Badge -->
				<div class="csc-scheme-card__icon-badge">
					<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<!-- Scheme Title -->
				<h3 class="csc-scheme-card__title">
					<a href="<?php echo esc_url( $button_url ); ?>" <?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo esc_html( $title ); ?>
					</a>
				</h3>

				<!-- Scheme Description -->
				<?php if ( ! empty( $desc ) ) : ?>
					<div class="csc-scheme-card__desc">
						<?php echo esc_html( $desc ); ?>
					</div>
				<?php endif; ?>

				<!-- View Details Action Button -->
				<div class="csc-scheme-card__action">
					<a href="<?php echo esc_url( $button_url ); ?>" class="csc-scheme-card__btn" <?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<span><?php echo esc_html( $button_text ); ?></span>
					</a>
				</div>

			</div>

		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render [csc_schemes] / [csc_scheme_slider] / [schemes] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string Rendered HTML.
 */
function csc_schemes_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'        => '',
			'limit'           => -1,
			'slides_per_view' => 4,
			'space_between'   => 24,
			'autoplay'        => 'no',
			'autoplay_delay'  => 3500,
			'loop'            => 'no',
			'speed'           => 600,
			'arrows'          => 'yes',
			'dots'            => 'no',
			'orderby'         => 'menu_order title',
			'order'           => 'ASC',
			'class'           => '',
		),
		$atts,
		'csc_schemes'
	);

	// Setup WP_Query arguments.
	$limit = intval( $atts['limit'] );
	if ( 0 === $limit ) {
		$limit = -1;
	}

	$allowed_orderby = array( 'title', 'date', 'menu_order', 'rand', 'ID', 'menu_order title' );
	$orderby         = in_array( $atts['orderby'], $allowed_orderby, true ) ? $atts['orderby'] : 'menu_order title';
	$order           = strtoupper( trim( $atts['order'] ) ) === 'DESC' ? 'DESC' : 'ASC';

	$query_args = array(
		'post_type'              => 'csc_scheme',
		'post_status'            => 'publish',
		'posts_per_page'         => $limit,
		'orderby'                => $orderby,
		'order'                  => $order,
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	// Category filter: check shortcode param or URL query param.
	$category_filter = ! empty( $atts['category'] ) ? $atts['category'] : '';
	if ( empty( $category_filter ) && ! empty( $_GET['scheme_category'] ) ) {
		$raw_cat = sanitize_title( wp_unslash( $_GET['scheme_category'] ) );
		$settings = function_exists( 'csc_get_scheme_settings' ) ? csc_get_scheme_settings() : array();
		if ( 'cat-2' === $raw_cat ) {
			$category_filter = ! empty( $settings['logo2_category'] ) ? $settings['logo2_category'] : 'disabilities-in-hp';
		} elseif ( 'cat-1' === $raw_cat ) {
			$category_filter = ! empty( $settings['logo1_category'] ) ? $settings['logo1_category'] : 'demographic-spread-of-minorities';
		} else {
			$category_filter = $raw_cat;
		}
	} elseif ( empty( $category_filter ) && ! empty( $_GET['category'] ) ) {
		$category_filter = sanitize_title( wp_unslash( $_GET['category'] ) );
	}

	if ( ! empty( $category_filter ) ) {
		$categories = array_filter( array_map( 'sanitize_title', explode( ',', $category_filter ) ) );
		if ( ! empty( $categories ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'         => 'csc_scheme_category',
					'field'            => 'slug',
					'terms'            => $categories,
					'include_children' => true,
				),
			);
		}
	}

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() && empty( $category_filter ) ) {
		// If logged-in admin and no schemes exist, show helpful message.
		if ( current_user_can( 'edit_posts' ) ) {
			return '<div style="padding: 24px; background: #fff; border: 2px dashed #cbd5e1; border-radius: 8px; text-align: center; color: #64748b; font-family: sans-serif;">'
				. '<strong>' . esc_html__( 'Schemes Slider:', 'custom-shortcodes-components' ) . '</strong> '
				. esc_html__( 'No published schemes found. Add schemes in the WordPress Dashboard under "Custom Shortcodes > Schemes".', 'custom-shortcodes-components' )
				. '</div>';
		}
		return '';
	}

	// Slider settings data attributes.
	$slides_per_view = max( 1, min( 6, intval( $atts['slides_per_view'] ) ) );
	$space_between   = max( 0, intval( $atts['space_between'] ) );
	$is_autoplay     = strtolower( trim( $atts['autoplay'] ) ) === 'yes' ? 'yes' : 'no';
	$autoplay_delay  = max( 1000, intval( $atts['autoplay_delay'] ) );
	$is_loop         = strtolower( trim( $atts['loop'] ) ) === 'yes' ? 'yes' : 'no';
	$speed           = max( 200, intval( $atts['speed'] ) );
	$show_arrows     = strtolower( trim( $atts['arrows'] ) ) !== 'no';
	$show_dots       = strtolower( trim( $atts['dots'] ) ) === 'yes';

	$wrapper_classes = array( 'csc-schemes-wrapper' );
	if ( ! empty( $atts['class'] ) ) {
		$custom_class = sanitize_html_class( $atts['class'] );
		if ( ! empty( $custom_class ) ) {
			$wrapper_classes[] = $custom_class;
		}
	}

	// Unique slider ID for scoped DOM querying.
	$unique_id = 'csc-schemes-' . wp_rand( 1000, 9999 );

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" id="<?php echo esc_attr( $unique_id ); ?>">

		<?php if ( $show_arrows ) : ?>
			<!-- Navigation Prev Button -->
			<button type="button" class="csc-swiper-button-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'custom-shortcodes-components' ); ?>">
				<svg viewBox="0 0 24 24">
					<polyline points="15 18 9 12 15 6"></polyline>
				</svg>
			</button>
		<?php endif; ?>

		<div class="swiper csc-schemes-slider"
			data-category="<?php echo esc_attr( $category_filter ); ?>"
			data-limit="<?php echo esc_attr( $limit ); ?>"
			data-orderby="<?php echo esc_attr( $orderby ); ?>"
			data-order="<?php echo esc_attr( $order ); ?>"
			data-slides-per-view="<?php echo esc_attr( $slides_per_view ); ?>"
			data-space-between="<?php echo esc_attr( $space_between ); ?>"
			data-autoplay="<?php echo esc_attr( $is_autoplay ); ?>"
			data-autoplay-delay="<?php echo esc_attr( $autoplay_delay ); ?>"
			data-loop="<?php echo esc_attr( $is_loop ); ?>"
			data-speed="<?php echo esc_attr( $speed ); ?>"
			data-arrows="<?php echo $show_arrows ? 'yes' : 'no'; ?>"
			data-dots="<?php echo $show_dots ? 'yes' : 'no'; ?>">
			<div class="swiper-wrapper">
				<?php
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						echo csc_render_scheme_slide( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					wp_reset_postdata();
				} else {
					?>
					<div class="swiper-slide csc-schemes-empty-slide" style="width: 100%; display: flex; justify-content: center;">
						<div class="csc-schemes-empty">
							<svg class="csc-schemes-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<line x1="12" y1="8" x2="12" y2="12"></line>
								<line x1="12" y1="16" x2="12.01" y2="16"></line>
							</svg>
							<p><?php esc_html_e( 'No schemes available in this category.', 'custom-shortcodes-components' ); ?></p>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>

		<?php if ( $show_arrows ) : ?>
			<!-- Navigation Next Button -->
			<button type="button" class="csc-swiper-button-next" aria-label="<?php esc_attr_e( 'Next slide', 'custom-shortcodes-components' ); ?>">
				<svg viewBox="0 0 24 24">
					<polyline points="9 18 15 12 9 6"></polyline>
				</svg>
			</button>
		<?php endif; ?>

		<?php if ( $show_dots ) : ?>
			<!-- Pagination Dots -->
			<div class="csc-schemes-pagination"></div>
		<?php endif; ?>

	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'csc_schemes', 'csc_schemes_shortcode' );
add_shortcode( 'csc_scheme_slider', 'csc_schemes_shortcode' );
add_shortcode( 'schemes', 'csc_schemes_shortcode' );
