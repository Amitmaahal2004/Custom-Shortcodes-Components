<?php

/**
 * Success Stories Grid Cards Shortcode
 *
 * @package CustomShortcodesComponents
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Render [csc_story_grid] / [csc_stories_grid] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string Rendered HTML.
 */
function csc_story_grid_shortcode($atts)
{
	$atts = shortcode_atts(
		array(
			'category'    => '',
			'district'    => '',
			'columns'     => 3,
			'limit'       => -1,
			'show_filter' => 'yes',
			'filter_by'   => 'district', // 'district' or 'category'
			'orderby'     => 'menu_order date',
			'order'       => 'ASC',
			'class'       => '',
		),
		$atts,
		'csc_story_grid'
	);

	$limit = intval($atts['limit']);
	if (0 === $limit) {
		$limit = -1;
	}

	$query_args = array(
		'post_type'      => 'csc_story',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
	);

	$tax_queries = array();

	if (! empty($atts['category'])) {
		$categories = array_filter(array_map('trim', explode(',', $atts['category'])));
		$tax_queries[] = array(
			'taxonomy' => 'csc_story_category',
			'field'    => 'slug',
			'terms'    => $categories,
		);
	}

	if (! empty($atts['district'])) {
		$districts = array_filter(array_map('trim', explode(',', $atts['district'])));
		$tax_queries[] = array(
			'taxonomy' => 'csc_story_district',
			'field'    => 'slug',
			'terms'    => $districts,
		);
	}

	if (count($tax_queries) > 1) {
		$tax_queries['relation'] = 'AND';
	}

	if (! empty($tax_queries)) {
		$query_args['tax_query'] = $tax_queries;
	}

	$query = new WP_Query($query_args);

	if (! $query->have_posts()) {
		if (current_user_can('edit_posts')) {
			return '<div class="csc-story-empty-notice" style="padding: 24px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; text-align: center; color: #64748b; font-family: sans-serif;">'
				. '<strong>' . esc_html__('Success Stories Grid:', 'custom-shortcodes-components') . '</strong> '
				. esc_html__('No stories found matching your criteria. Add stories or run the demo importer under Custom Shortcodes.', 'custom-shortcodes-components')
				. '</div>';
		}
		return '';
	}

	// Filter terms if filter is enabled.
	$filter_terms = array();
	$filter_tax   = 'category' === $atts['filter_by'] ? 'csc_story_category' : 'csc_story_district';
	if ('yes' === strtolower($atts['show_filter'])) {
		$terms = get_terms(array(
			'taxonomy'   => $filter_tax,
			'hide_empty' => true,
		));
		if (! empty($terms) && ! is_wp_error($terms)) {
			$filter_terms = $terms;
		}
	}

	$columns = max(1, min(4, intval($atts['columns'])));
	$grid_id = 'csc-story-grid-' . wp_rand(1000, 9999);

	ob_start();
?>
	<div class="csc-story-grid-wrapper <?php echo esc_attr($atts['class']); ?>" id="<?php echo esc_attr($grid_id); ?>">

		<?php if (! empty($filter_terms)) : ?>
			<!-- Interactive Taxonomy Filter Bar -->
			<div class="csc-story-filter-bar">
				<button type="button" class="csc-filter-btn is-active" data-filter="*">
					<?php esc_html_e('All', 'custom-shortcodes-components'); ?>
				</button>
				<?php foreach ($filter_terms as $term) : ?>
					<button type="button" class="csc-filter-btn" data-filter=".term-<?php echo esc_attr($term->slug); ?>">
						<?php echo esc_html($term->name); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Cards Grid -->
		<div class="csc-story-cards-grid csc-grid-cols-<?php echo esc_attr($columns); ?>">
			<?php while ($query->have_posts()) : $query->the_post();
				$post_id       = get_the_ID();
				$loan_amount   = get_post_meta($post_id, '_csc_story_loan_amount', true);
				$scheme        = get_post_meta($post_id, '_csc_story_scheme', true);
				$meta_district = get_post_meta($post_id, '_csc_story_district', true);
				$video_poster  = get_post_meta($post_id, '_csc_story_video_poster', true);
				$permalink     = get_permalink();

				// District name.
				$district_terms = get_the_terms($post_id, 'csc_story_district');
				$district_name  = '';
				if (! empty($district_terms) && ! is_wp_error($district_terms)) {
					$district_name = $district_terms[0]->name;
				} elseif (! empty($meta_district)) {
					$district_name = $meta_district;
				}

				// Build filter classes.
				$post_terms = get_the_terms($post_id, $filter_tax);
				$filter_classes = array();
				if (! empty($post_terms) && ! is_wp_error($post_terms)) {
					foreach ($post_terms as $pt) {
						$filter_classes[] = 'term-' . sanitize_html_class($pt->slug);
					}
				}

				// Card Thumbnail.
				$card_img = '';
				if (has_post_thumbnail($post_id)) {
					$card_img = get_the_post_thumbnail_url($post_id, 'large');
				} elseif (! empty($video_poster)) {
					$card_img = $video_poster;
				}
			?>
				<article class="csc-story-grid-card <?php echo esc_attr(implode(' ', $filter_classes)); ?>">
					<!-- Top Card Media -->
					<div class="csc-grid-card-media">
						<a href="<?php echo esc_url($permalink); ?>" tabindex="-1" aria-hidden="true">
							<?php if (! empty($card_img)) : ?>
								<img src="<?php echo esc_url($card_img); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
							<?php else : ?>
								<div class="csc-grid-card-placeholder">
									<span class="dashicons dashicons-format-image"></span>
								</div>
							<?php endif; ?>
						</a>
					</div>

					<!-- Card Body -->
					<div class="csc-grid-card-body">
						<div class="csc-grid-meta-list">
							<?php if (! empty($district_name)) : ?>
								<div class="csc-grid-meta-item">
									<span class="csc-grid-meta-icon">
										<img
											src="<?php echo esc_url('/wp-content/uploads/2026/09/ion_location-outline-1.png'); ?>"
											alt="Location"
											width="20"
											height="20">
									</span>
									<span class="csc-grid-meta-text">
										<strong class="csc-grid-meta-label"><?php esc_html_e('District', 'custom-shortcodes-components'); ?> :</strong>
										<span class="csc-grid-meta-val"><?php echo esc_html($district_name); ?></span>
									</span>
								</div>
							<?php endif; ?>

							<?php if (! empty($loan_amount)) : ?>
								<div class="csc-grid-meta-item">
									<span class="csc-grid-meta-icon">
										<img
											src="<?php echo esc_url('/wp-content/uploads/2026/09/Vector-21.png'); ?>"
											alt="Rupee"
											width="20"
											height="20">
									</span>
									<span class="csc-grid-meta-text">
										<strong class="csc-grid-meta-label"><?php esc_html_e('Loan Amount', 'custom-shortcodes-components'); ?> :</strong>
										<span class="csc-grid-meta-val"><?php echo esc_html($loan_amount); ?></span>
									</span>
								</div>
							<?php endif; ?>

							<?php if (! empty($scheme)) : ?>
								<div class="csc-grid-meta-item">
									<span class="csc-grid-meta-icon">
										<img
											src="<?php echo esc_url('/wp-content/uploads/2026/09/Vector-23.png'); ?>"
											alt="Scheme"
											width="20"
											height="20">
									</span>
									<span class="csc-grid-meta-text">
										<strong class="csc-grid-meta-label"><?php esc_html_e('Scheme', 'custom-shortcodes-components'); ?> :</strong>
										<span class="csc-grid-meta-val"><?php echo esc_html($scheme); ?></span>
									</span>
								</div>
							<?php endif; ?>
						</div>

						<!-- Card Divider & Read More Action -->
						<div class="csc-grid-card-footer">
							<a href="<?php echo esc_url($permalink); ?>" class="csc-grid-read-more">
								<?php esc_html_e('Read More', 'custom-shortcodes-components'); ?>
							</a>
						</div>
					</div>
				</article>
			<?php endwhile;
			wp_reset_postdata(); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}
add_shortcode('csc_story_grid', 'csc_story_grid_shortcode');
add_shortcode('csc_stories_grid', 'csc_story_grid_shortcode');
