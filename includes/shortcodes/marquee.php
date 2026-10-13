<?php
/**
 * Dashboard-Driven Continuous Marquee Slider Shortcode
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render [csc_marquee] shortcode querying csc_marquee CPT and csc_marquee_category taxonomy.
 *
 * @param array $atts Shortcode attributes.
 * @return string Rendered HTML.
 */
function csc_marquee_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'       => '',
			'limit'          => -1,
			'speed'          => 30,
			'gap'            => 40,
			'direction'      => 'left',
			'pause_on_hover' => 'no',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'class'          => '',
		),
		$atts,
		'csc_marquee'
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
		'post_type'              => 'csc_marquee',
		'post_status'            => 'publish',
		'posts_per_page'         => $limit,
		'orderby'                => $orderby,
		'order'                  => $order,
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	// Category filter via tax_query.
	if ( ! empty( $atts['category'] ) ) {
		$categories = array_filter( array_map( 'sanitize_title', explode( ',', $atts['category'] ) ) );
		if ( ! empty( $categories ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'csc_marquee_category',
					'field'    => 'slug',
					'terms'    => $categories,
				),
			);
		}
	}

	$marquee_query = new WP_Query( $query_args );

	if ( ! $marquee_query->have_posts() ) {
		return '';
	}

	// Collect items.
	$items = array();
	while ( $marquee_query->have_posts() ) {
		$marquee_query->the_post();
		$post_id   = get_the_ID();
		$title     = get_the_title();
		$cust_link = get_post_meta( $post_id, '_csc_marquee_link', true );
		$target_b  = get_post_meta( $post_id, '_csc_marquee_target_blank', true );
		$link      = ! empty( $cust_link ) ? esc_url( $cust_link ) : get_permalink( $post_id );

		$image_html = '';
		if ( has_post_thumbnail( $post_id ) ) {
			$image_html = get_the_post_thumbnail(
				$post_id,
				'full',
				array(
					'class'   => 'csc-marquee__item-image',
					'alt'     => esc_attr( $title ),
					'loading' => 'lazy',
				)
			);
		}

		$items[] = array(
			'id'           => $post_id,
			'title'        => $title,
			'link'         => $link,
			'target_blank' => ! empty( $target_b ),
			'image'        => $image_html,
		);
	}
	wp_reset_postdata();

	if ( empty( $items ) ) {
		return '';
	}

	// Repeat items if count is small (<10) to ensure continuous width exceeds any viewport.
	$display_items = $items;
	$count         = count( $items );
	if ( $count < 10 ) {
		$multiplier = (int) ceil( 10 / $count );
		$expanded   = array();
		for ( $i = 0; $i < $multiplier; $i++ ) {
			$expanded = array_merge( $expanded, $items );
		}
		$display_items = $expanded;
	}

	// Speed & Gap.
	$speed = floatval( $atts['speed'] );
	if ( $speed <= 0 ) {
		$speed = 30;
	}
	$gap = max( 0, intval( $atts['gap'] ) );

	// Direction & Hover.
	$direction      = strtolower( trim( $atts['direction'] ) ) === 'right' ? 'right' : 'left';
	$pause_on_hover = strtolower( trim( $atts['pause_on_hover'] ) ) === 'yes';

	$wrapper_classes = array( 'csc-marquee', 'csc-marquee--direction-' . $direction );
	if ( $pause_on_hover ) {
		$wrapper_classes[] = 'csc-marquee--pause-hover';
	}
	if ( ! empty( $atts['class'] ) ) {
		$custom_class = sanitize_html_class( $atts['class'] );
		if ( ! empty( $custom_class ) ) {
			$wrapper_classes[] = $custom_class;
		}
	}

	$style_attr = sprintf(
		'--csc-marquee-duration: %fs; --csc-marquee-gap: %dpx;',
		esc_attr( $speed ),
		esc_attr( $gap )
	);

	// Render items list helper.
	$render_group_items = function ( $group_items ) {
		ob_start();
		foreach ( $group_items as $item ) {
			$target_attr = $item['target_blank'] ? ' target="_blank" rel="noopener noreferrer"' : '';
			?>
			<div class="csc-marquee__item">
				<a href="<?php echo esc_url( $item['link'] ); ?>" class="csc-marquee__item-link" title="<?php echo esc_attr( $item['title'] ); ?>"<?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php if ( ! empty( $item['image'] ) ) : ?>
						<span class="csc-marquee__item-media"><?php echo wp_kses_post( $item['image'] ); ?></span>
					<?php endif; ?>
					<span class="csc-marquee__item-title"><?php echo esc_html( $item['title'] ); ?></span>
				</a>
			</div>
			<?php
		}
		return ob_get_clean();
	};

	$group_html = $render_group_items( $display_items );

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" style="<?php echo esc_attr( $style_attr ); ?>" data-csc-marquee>
		<div class="csc-marquee__viewport">
			<div class="csc-marquee__track">
				<div class="csc-marquee__group">
					<?php echo $group_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="csc-marquee__group" aria-hidden="true">
					<?php echo $group_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'csc_marquee', 'csc_marquee_shortcode' );
