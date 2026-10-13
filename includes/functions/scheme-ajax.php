<?php
/**
 * AJAX Handler: Dynamic Scheme Category Filtering
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX request for category filtering in Schemes slider.
 */
function csc_filter_schemes_ajax_handler() {
	// Verify nonce for security.
	if ( ! check_ajax_referer( 'csc_schemes_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'custom-shortcodes-components' ) ), 403 );
	}

	$category_slug = isset( $_POST['category'] ) ? sanitize_title( wp_unslash( $_POST['category'] ) ) : '';
	if ( 'cat-2' === $category_slug ) {
		$settings = function_exists( 'csc_get_scheme_settings' ) ? csc_get_scheme_settings() : array();
		$category_slug = ! empty( $settings['logo2_category'] ) ? $settings['logo2_category'] : 'disabilities-in-hp';
	} elseif ( 'cat-1' === $category_slug ) {
		$settings = function_exists( 'csc_get_scheme_settings' ) ? csc_get_scheme_settings() : array();
		$category_slug = ! empty( $settings['logo1_category'] ) ? $settings['logo1_category'] : 'demographic-spread-of-minorities';
	}
	$limit         = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : -1;
	if ( 0 === $limit ) {
		$limit = -1;
	}

	$allowed_orderby = array( 'title', 'date', 'menu_order', 'rand', 'ID', 'menu_order title' );
	$orderby_raw     = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'menu_order title';
	$orderby         = in_array( $orderby_raw, $allowed_orderby, true ) ? $orderby_raw : 'menu_order title';

	$order_raw       = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'ASC';
	$order           = strtoupper( trim( $order_raw ) ) === 'DESC' ? 'DESC' : 'ASC';

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

	if ( ! empty( $category_slug ) && 'all' !== $category_slug ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy'         => 'csc_scheme_category',
				'field'            => 'slug',
				'terms'            => array( $category_slug ),
				'include_children' => true,
			),
		);
	}

	$query = new WP_Query( $query_args );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			if ( function_exists( 'csc_render_scheme_slide' ) ) {
				echo csc_render_scheme_slide( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
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

	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html'     => $html,
			'count'    => $query->post_count,
			'category' => $category_slug,
		)
	);
}
add_action( 'wp_ajax_csc_filter_schemes', 'csc_filter_schemes_ajax_handler' );
add_action( 'wp_ajax_nopriv_csc_filter_schemes', 'csc_filter_schemes_ajax_handler' );
