<?php

/**
 * Success Stories Video Slider Shortcode
 *
 * @package CustomShortcodesComponents
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Render [csc_story_slider] / [csc_stories_slider] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string Rendered HTML.
 */
function csc_story_slider_shortcode($atts)
{
	$atts = shortcode_atts(
		array(
			'category'        => '',
			'district'        => '',
			'limit'           => 5,
			'autoplay'        => 'no',
			'autoplay_delay'  => 5000,
			'speed'           => 600,
			'view_more_url'   => '',
			'view_more_text'  => __('View More Stories', 'custom-shortcodes-components'),
			'class'           => '',
		),
		$atts,
		'csc_story_slider'
	);

	$query_args = array(
		'post_type'      => 'csc_story',
		'post_status'    => 'publish',
		'posts_per_page' => intval($atts['limit']) > 0 ? intval($atts['limit']) : 5,
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
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
				. '<span>' . esc_html__('Success Stories Slider:', 'custom-shortcodes-components') . '</span> '
				. esc_html__('No published success stories found. Go to Custom Shortcodes > Success Stories to add or import demo stories.', 'custom-shortcodes-components')
				. '</div>';
		}
		return '';
	}

	// Determine default "View More" link if not provided in shortcode.
$default_view_more_url = ! empty($atts['view_more_url'])
    ? esc_url($atts['view_more_url'])
    : home_url('/success-stories/');	if (empty($default_view_more_url)) {
		$default_view_more_url = '#';
	}

	$slider_id = 'csc-story-slider-' . wp_rand(1000, 9999);

	ob_start();
?>
	<div class="csc-story-slider-section <?php echo esc_attr($atts['class']); ?>">
		<div class="csc-story-slider-container">
			<div id="<?php echo esc_attr($slider_id); ?>"
				class="swiper csc-story-swiper"
				data-autoplay="<?php echo esc_attr($atts['autoplay']); ?>"
				data-autoplay-delay="<?php echo esc_attr($atts['autoplay_delay']); ?>"
				data-speed="<?php echo esc_attr($atts['speed']); ?>">

				<div class="swiper-wrapper">
					<?php while ($query->have_posts()) : $query->the_post();
						$post_id       = get_the_ID();
						$loan_amount   = get_post_meta($post_id, '_csc_story_loan_amount', true);
						$scheme        = get_post_meta($post_id, '_csc_story_scheme', true);
						$sanction_date = get_post_meta($post_id, '_csc_story_sanction_date', true);
						$video_url     = get_post_meta($post_id, '_csc_story_video_url', true);
						$video_poster  = get_post_meta($post_id, '_csc_story_video_poster', true);
						$custom_link   = get_post_meta($post_id, '_csc_story_view_more_url', true);
						$btn_url       = ! empty($custom_link) ? esc_url($custom_link) : $default_view_more_url;

						// Determine video thumbnail.
						$thumb_url = '';
						if (! empty($video_poster)) {
							$thumb_url = $video_poster;
						} elseif (has_post_thumbnail($post_id)) {
							$thumb_url = get_the_post_thumbnail_url($post_id, 'large');
						}

						// Video parsed.
						$video_data = csc_parse_video_source($video_url);
					?>
						<div class="swiper-slide csc-story-slide">
							<div class="csc-story-card">
								<!-- Left Column: Video Container -->
								<div class="csc-story-video-col">
									<div class="csc-story-video-box" data-video-type="<?php echo esc_attr($video_data['type']); ?>" data-video-url="<?php echo esc_attr($video_data['url']); ?>" data-video-embed="<?php echo esc_attr($video_data['embed'] ?? ''); ?>">
										<?php if (! empty($thumb_url)) : ?>
											<img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" class="csc-story-poster-img" />
										<?php else : ?>
											<div class="csc-story-placeholder-poster">
												<span class="dashicons dashicons-video-alt3" style="font-size: 48px; width: 48px; height: 48px; color: #94a3b8;"></span>
											</div>
										<?php endif; ?>

										<!-- Center Big Play Button Overlay -->
										<button type="button" class="csc-story-play-trigger" aria-label="<?php esc_attr_e('Play Video', 'custom-shortcodes-components'); ?>">
											<span class="csc-play-circle">
												<?php echo csc_get_story_svg_icon('play', 20, '#ffffff'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
												?>
											</span>
										</button>

										<!-- Bottom Media Player Controls Bar Overlay -->
										<div class="csc-story-video-bar">
											<button type="button" class="csc-bar-play-icon" aria-label="<?php esc_attr_e('Toggle play', 'custom-shortcodes-components'); ?>">
												<?php echo csc_get_story_svg_icon('play', 14, '#ffffff'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
												?>
											</button>
											<div class="csc-bar-timeline">
												<div class="csc-bar-progress" style="width: 25%;">
													<span class="csc-bar-scrubber"></span>
												</div>
											</div>
											<button type="button" class="csc-bar-fs-icon" aria-label="<?php esc_attr_e('Fullscreen', 'custom-shortcodes-components'); ?>">
												<?php echo csc_get_story_svg_icon('fullscreen', 16, '#ffffff'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
												?>
											</button>
										</div>

										<!-- Container for active video player embed -->
										<div class="csc-video-embed-container"></div>
									</div>
								</div>

								<!-- Right Column: Meta & Story Content -->
								<div class="csc-story-content-col">
									<div class="csc-story-meta-list">
										<?php if (! empty($loan_amount)) : ?>
											<div class="csc-story-meta-item">
												<span class="csc-meta-icon csc-icon-rupee">
													<img
														src="<?php echo esc_url('/wp-content/uploads/2026/09/Vector-21.png'); ?>"
														alt="Rupee"
														width="20"
														height="20">
												</span>
												<span class="csc-meta-text">
													<span class="csc-meta-label"><?php esc_html_e('Loan Amount', 'custom-shortcodes-components'); ?> :</span>
													<span class="csc-meta-val"><?php echo esc_html($loan_amount); ?></span>
												</span>
											</div>
										<?php endif; ?>

										<?php if (! empty($scheme)) : ?>
											<div class="csc-story-meta-item">
												<span class="csc-meta-icon csc-icon-scheme">
													<img
														src="<?php echo esc_url('/wp-content/uploads/2026/09/Vector-23.png'); ?>"
														alt="Scheme"
														width="20"
														height="20">
												</span>
												<span class="csc-meta-text">
													<span class="csc-meta-label"><?php esc_html_e('Scheme', 'custom-shortcodes-components'); ?> :</span>
													<span class="csc-meta-val"><?php echo esc_html($scheme); ?></span>
												</span>
											</div>
										<?php endif; ?>

										<?php if (! empty($sanction_date)) : ?>
											<div class="csc-story-meta-item">
												<span class="csc-meta-icon csc-icon-calendar">
													<img
														src="<?php echo esc_url('/wp-content/uploads/2026/09/Vector-22.png'); ?>"
														alt="Calendar"
														width="20"
														height="20">
												</span>
												<span class="csc-meta-text">
													<span class="csc-meta-label"><?php esc_html_e('Sanction Date', 'custom-shortcodes-components'); ?> :</span>
													<span class="csc-meta-val"><?php echo esc_html($sanction_date); ?></span>
												</span>
											</div>
										<?php endif; ?>
									</div>

									<div class="csc-story-excerpt-body">
										<?php
										if (has_excerpt()) {
											the_excerpt();
										} else {
											$content = get_the_content();
											$paragraphs = explode("\n", $content);
											$p_count = 0;
											foreach ($paragraphs as $paragraph) {
												$trimmed = trim($paragraph);
												if (! empty($trimmed) && $p_count < 2) {
													echo '<p>' . wp_kses_post($trimmed) . '</p>';
													$p_count++;
												}
											}
										}
										?>
									</div>

									<div class="csc-story-action-row">
										<a href="<?php echo esc_url($btn_url); ?>" class="csc-story-view-more-btn">
											<span><?php echo esc_html($atts['view_more_text']); ?></span>
											<?php echo csc_get_story_svg_icon('chevron_right', 16, 'currentColor');  
											?>
										</a>
									</div>
								</div>
							</div>
						</div>
					<?php endwhile;
					wp_reset_postdata(); ?>
				</div>

				<!-- Swiper Pagination Dots -->
				<div class="csc-story-pagination"></div>
			</div>
		</div>
	</div>
<?php
	return ob_get_clean();
}
add_shortcode('csc_story_slider', 'csc_story_slider_shortcode');
add_shortcode('csc_stories_slider', 'csc_story_slider_shortcode');
