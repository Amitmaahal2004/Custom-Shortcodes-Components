<?php
/**
 * Schemes & Services Grid Layout Shortcode with Pagination
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a single Scheme card matching the existing design.
 *
 * @param int $post_id Post ID of the csc_scheme.
 * @return string HTML markup for the scheme card.
 */
function csc_render_scheme_grid_card( $post_id ) {
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
	<?php
	return ob_get_clean();
}

/**
 * Render [schemes_services_grid] / [csc_schemes_grid] / [csc_scheme_grid] / [schemes_grid] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string Rendered HTML.
 */
function csc_schemes_grid_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'       => '',
			'columns'        => 3,
			'cols'           => '',
			'posts_per_page' => 9,
			'per_page'       => '',
			'limit'          => '',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'pagination'     => 'yes',
			'class'          => '',
		),
		$atts,
		'schemes_services_grid'
	);

	// Determine posts per page limit.
	$posts_per_page = 9;
	if ( '' !== $atts['per_page'] ) {
		$posts_per_page = intval( $atts['per_page'] );
	} elseif ( '' !== $atts['limit'] ) {
		$posts_per_page = intval( $atts['limit'] );
	} elseif ( '' !== $atts['posts_per_page'] ) {
		$posts_per_page = intval( $atts['posts_per_page'] );
	}

	if ( 0 === $posts_per_page ) {
		$posts_per_page = -1;
	}

	// Columns count (1 to 6).
	$columns = ! empty( $atts['cols'] ) ? intval( $atts['cols'] ) : intval( $atts['columns'] );
	$columns = max( 1, min( 6, $columns ) );

	// Order & Orderby.
	$allowed_orderby = array( 'title', 'date', 'menu_order', 'rand', 'ID', 'menu_order title' );
	$orderby         = in_array( $atts['orderby'], $allowed_orderby, true ) ? $atts['orderby'] : 'menu_order title';
	$order           = strtoupper( trim( $atts['order'] ) ) === 'DESC' ? 'DESC' : 'ASC';

	// Current page calculation for pagination.
	$paged = 1;
	if ( get_query_var( 'paged' ) ) {
		$paged = absint( get_query_var( 'paged' ) );
	} elseif ( get_query_var( 'page' ) ) {
		$paged = absint( get_query_var( 'page' ) );
	} elseif ( isset( $_GET['paged'] ) ) {
		$paged = max( 1, absint( $_GET['paged'] ) );
	}

	$query_args = array(
		'post_type'              => 'csc_scheme',
		'post_status'            => 'publish',
		'posts_per_page'         => $posts_per_page,
		'paged'                  => $paged,
		'orderby'                => $orderby,
		'order'                  => $order,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	// Category filter: check shortcode attribute, then URL query parameters if empty.
	$category_filter = ! empty( $atts['category'] ) ? trim( $atts['category'] ) : '';
	$settings        = function_exists( 'csc_get_scheme_settings' ) ? csc_get_scheme_settings() : array();

	if ( empty( $category_filter ) && ! empty( $_GET['scheme_category'] ) ) {
		$category_filter = sanitize_title( wp_unslash( $_GET['scheme_category'] ) );
	} elseif ( empty( $category_filter ) && ! empty( $_GET['category'] ) ) {
		$category_filter = sanitize_title( wp_unslash( $_GET['category'] ) );
	}

	if ( ! empty( $category_filter ) ) {
		$raw_cats = array_filter( array_map( 'trim', explode( ',', $category_filter ) ) );
		$slugs    = array();
		$ids      = array();

		foreach ( $raw_cats as $cat_item ) {
			$lower_item = strtolower( $cat_item );
			if ( 'cat-1' === $lower_item ) {
				$mapped = ! empty( $settings['logo1_category'] ) ? $settings['logo1_category'] : 'demographic-spread-of-minorities';
				$slugs[] = sanitize_title( $mapped );
			} elseif ( 'cat-2' === $lower_item ) {
				$mapped = ! empty( $settings['logo2_category'] ) ? $settings['logo2_category'] : 'disabilities-in-hp';
				$slugs[] = sanitize_title( $mapped );
			} elseif ( is_numeric( $cat_item ) ) {
				$ids[] = intval( $cat_item );
			} else {
				$slugs[] = sanitize_title( $cat_item );
			}
		}

		$tax_query = array( 'relation' => 'OR' );
		if ( ! empty( $slugs ) ) {
			$tax_query[] = array(
				'taxonomy'         => 'csc_scheme_category',
				'field'            => 'slug',
				'terms'            => $slugs,
				'include_children' => true,
			);
		}
		if ( ! empty( $ids ) ) {
			$tax_query[] = array(
				'taxonomy'         => 'csc_scheme_category',
				'field'            => 'term_id',
				'terms'            => $ids,
				'include_children' => true,
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$query_args['tax_query'] = $tax_query;
		}
	}

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() && empty( $category_filter ) ) {
		if ( current_user_can( 'edit_posts' ) ) {
			return '<div style="padding: 24px; background: #fff; border: 2px dashed #cbd5e1; border-radius: 8px; text-align: center; color: #64748b; font-family: sans-serif;">'
				. '<strong>' . esc_html__( 'Schemes Grid:', 'custom-shortcodes-components' ) . '</strong> '
				. esc_html__( 'No published schemes found. Add schemes in the WordPress Dashboard under "Custom Shortcodes > Schemes".', 'custom-shortcodes-components' )
				. '</div>';
		}
		return '';
	}

	$wrapper_classes = array( 'csc-schemes-grid-wrapper' );
	if ( ! empty( $atts['class'] ) ) {
		$custom_class = sanitize_html_class( $atts['class'] );
		if ( ! empty( $custom_class ) ) {
			$wrapper_classes[] = $custom_class;
		}
	}

	$unique_id = 'csc-schemes-grid-' . wp_rand( 1000, 9999 );
	$show_pagination = ( 'no' !== strtolower( trim( $atts['pagination'] ) ) && 'false' !== strtolower( trim( $atts['pagination'] ) ) && $posts_per_page > 0 );

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" id="<?php echo esc_attr( $unique_id ); ?>">

		<?php if ( $query->have_posts() ) : ?>
			<!-- Schemes Grid Container -->
			<div class="csc-schemes-grid csc-schemes-grid-cols-<?php echo esc_attr( $columns ); ?>">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<div class="csc-schemes-grid-item">
						<?php echo csc_render_scheme_grid_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endwhile; ?>
			</div>

			<?php
			// Pagination navigation.
			if ( $show_pagination && $query->max_num_pages > 1 ) :
				$big = 999999999;
				$pagination_links = paginate_links(
					array(
						'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, $paged ),
						'total'     => $query->max_num_pages,
						'prev_text' => '<span class="csc-pagination-arrow">&lsaquo;</span> ' . esc_html__( 'Previous', 'custom-shortcodes-components' ),
						'next_text' => esc_html__( 'Next', 'custom-shortcodes-components' ) . ' <span class="csc-pagination-arrow">&rsaquo;</span>',
						'type'      => 'list',
					)
				);

				if ( ! empty( $pagination_links ) ) :
					?>
					<nav class="csc-schemes-grid-pagination" aria-label="<?php esc_attr_e( 'Schemes pagination', 'custom-shortcodes-components' ); ?>">
						<?php echo $pagination_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</nav>
					<?php
				endif;
			endif;
			?>

		<?php else : ?>
			<!-- Empty category state -->
			<div class="csc-schemes-empty">
				<svg class="csc-schemes-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"></circle>
					<line x1="12" y1="8" x2="12" y2="12"></line>
					<line x1="12" y1="16" x2="12.01" y2="16"></line>
				</svg>
				<p><?php esc_html_e( 'No schemes available in this category.', 'custom-shortcodes-components' ); ?></p>
			</div>
		<?php endif; ?>

	</div>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

// Register shortcodes.
add_shortcode( 'schemes_services_grid', 'csc_schemes_grid_shortcode' );
add_shortcode( 'csc_schemes_grid', 'csc_schemes_grid_shortcode' );
add_shortcode( 'csc_scheme_grid', 'csc_schemes_grid_shortcode' );
add_shortcode( 'schemes_grid', 'csc_schemes_grid_shortcode' );
