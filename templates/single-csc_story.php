<?php

/**
 * Single Story Template: Success Stories Detail
 *
 * Matches Screenshot 3 design with:
 * - Top Hero Banner with beneficiary illustrations & subtitle
 * - Featured story card with media & meta
 * - 4 structured info cards (About The Beneficiary, The Challenge, Our Support, Impact & Outcome)
 * - "GLIMPSES OF OUR INITIATIVES" 4-photo gallery
 *
 * @package CustomShortcodesComponents
 */

if (! defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) : the_post();

	$post_id       = get_the_ID();
	$loan_amount   = get_post_meta($post_id, '_csc_story_loan_amount', true);
	$scheme        = get_post_meta($post_id, '_csc_story_scheme', true);
	$sanction_date = get_post_meta($post_id, '_csc_story_sanction_date', true);
	$district      = get_post_meta($post_id, '_csc_story_district', true);
	$video_url     = get_post_meta($post_id, '_csc_story_video_url', true);
	$video_poster  = get_post_meta($post_id, '_csc_story_video_poster', true);

	// 4 Information Cards content.
	$sec1_title   = get_post_meta($post_id, '_csc_story_sec1_title', true) ?: __('About The Beneficiary', 'custom-shortcodes-components');
	$sec1_content = get_post_meta($post_id, '_csc_story_sec1_content', true);

	$sec2_title   = get_post_meta($post_id, '_csc_story_sec2_title', true) ?: __('The Challenge', 'custom-shortcodes-components');
	$sec2_content = get_post_meta($post_id, '_csc_story_sec2_content', true);

	$sec3_title   = get_post_meta($post_id, '_csc_story_sec3_title', true) ?: __('Our Support', 'custom-shortcodes-components');
	$sec3_content = get_post_meta($post_id, '_csc_story_sec3_content', true);

	$sec4_title   = get_post_meta($post_id, '_csc_story_sec4_title', true) ?: __('Impact & Outcome', 'custom-shortcodes-components');
	$sec4_content = get_post_meta($post_id, '_csc_story_sec4_content', true);

	// Gallery Images.
	$gallery = array();

	for ($i = 1; $i <= 4; $i++) {

		$img = get_post_meta(
			$post_id,
			'_csc_story_gallery_img_' . $i,
			true
		);

		if (! empty($img)) {
			$gallery[] = $img;
		}
	}

	// Featured Image.
	$feat_img = '';

	if (has_post_thumbnail($post_id)) {

		$feat_img = get_the_post_thumbnail_url(
			$post_id,
			'full'
		);

	} elseif (! empty($video_poster)) {

		$feat_img = $video_poster;
	}
?>

	<div class="csc-single-story-page">

		<!-- =====================================================
		     HERO SECTION
		====================================================== -->

		<?php
		$hero_template_id = 4229;

		if (did_action('elementor/loaded')) {

			echo \Elementor\Plugin::instance()
				->frontend
				->get_builder_content_for_display(
					$hero_template_id
				);
		}
		?>


		<!-- =====================================================
		     SECTION 1
		     Featured Story
		====================================================== -->

		<section class="csc-story-section-one">

			<main class="csc-story-main-wrap">

				<div class="csc-story-main-wrap-inner">

					<!-- Main Story Featured Card -->
					<section class="csc-story-featured-card">

						<div class="csc-featured-card-grid">

							<!-- Left Column: Featured Image / Media -->
							<div class="csc-featured-media-col">

								<?php if (! empty($feat_img)) : ?>

									<div class="csc-featured-img-wrap">

										<img
											src="<?php echo esc_url($feat_img); ?>"
											alt="<?php the_title_attribute(); ?>"
											class="csc-featured-img"
										/>

									</div>

								<?php else : ?>

									<div class="csc-featured-placeholder">

										<span class="dashicons dashicons-format-image"></span>

									</div>

								<?php endif; ?>

							</div>


							<!-- Right Column: Meta & Story Narrative -->
							<div class="csc-featured-info-col">

								<!-- Story Meta -->
								<div class="csc-story-meta-list">

									<?php if (! empty($loan_amount)) : ?>

										<div class="csc-story-meta-item">

											<span class="csc-meta-icon csc-icon-rupee">

												<img
													src="<?php echo esc_url('/wp-content/uploads/2026/09/Vector-21.png'); ?>"
													alt="Rupee"
													width="20"
													height="20"
												>

											</span>

											<span class="csc-meta-text">

												<strong class="csc-meta-label">

													<?php
													esc_html_e(
														'Loan Amount',
														'custom-shortcodes-components'
													);
													?>

													:

												</strong>

												<span class="csc-meta-val">

													<?php
													echo esc_html($loan_amount);
													?>

												</span>

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
													height="20"
												>

											</span>

											<span class="csc-meta-text">

												<strong class="csc-meta-label">

													<?php
													esc_html_e(
														'Scheme',
														'custom-shortcodes-components'
													);
													?>

													:

												</strong>

												<span class="csc-meta-val">

													<?php
													echo esc_html($scheme);
													?>

												</span>

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
													height="20"
												>

											</span>

											<span class="csc-meta-text">

												<strong class="csc-meta-label">

													<?php
													esc_html_e(
														'Sanction Date',
														'custom-shortcodes-components'
													);
													?>

													:

												</strong>

												<span class="csc-meta-val">

													<?php
													echo esc_html($sanction_date);
													?>

												</span>

											</span>

										</div>

									<?php endif; ?>

								</div>


								<!-- Story Narrative -->
								<div class="csc-story-full-narrative">

									<?php the_content(); ?>

								</div>

							</div>

						</div>

					</section>

				</div>

			</main>

		</section>


		<!-- =====================================================
		     SECTION 2
		     Information Cards + Gallery
		====================================================== -->

		<section class="csc-story-section-two">

			<div class="csc-story-main-wrap-inner csc-story-main-wrap-inner-two">


				<!-- =================================================
				     4 STRUCTURED INFORMATION CARDS
				================================================== -->

				<?php
				if (
					! empty($sec1_content) ||
					! empty($sec2_content) ||
					! empty($sec3_content) ||
					! empty($sec4_content)
				) :
				?>

					<section class="csc-story-structured-grid">


						<!-- About The Beneficiary -->
						<?php if (! empty($sec1_content)) : ?>

							<div class="csc-info-card">

								<h3 class="csc-info-card-title">

									<?php
									echo esc_html($sec1_title);
									?>

								</h3>

								<div class="csc-info-card-content">

									<?php
									echo wp_kses_post(
										wpautop($sec1_content)
									);
									?>

								</div>

							</div>

						<?php endif; ?>


						<!-- The Challenge -->
						<?php if (! empty($sec2_content)) : ?>

							<div class="csc-info-card">

								<h3 class="csc-info-card-title">

									<?php
									echo esc_html($sec2_title);
									?>

								</h3>

								<div class="csc-info-card-content">

									<?php
									echo wp_kses_post(
										wpautop($sec2_content)
									);
									?>

								</div>

							</div>

						<?php endif; ?>


						<!-- Our Support -->
						<?php if (! empty($sec3_content)) : ?>

							<div class="csc-info-card">

								<h3 class="csc-info-card-title">

									<?php
									echo esc_html($sec3_title);
									?>

								</h3>

								<div class="csc-info-card-content">

									<?php
									echo wp_kses_post(
										wpautop($sec3_content)
									);
									?>

								</div>

							</div>

						<?php endif; ?>


						<!-- Impact & Outcome -->
						<?php if (! empty($sec4_content)) : ?>

							<div class="csc-info-card">

								<h3 class="csc-info-card-title">

									<?php
									echo esc_html($sec4_title);
									?>

								</h3>

								<div class="csc-info-card-content">

									<?php
									echo wp_kses_post(
										wpautop($sec4_content)
									);
									?>

								</div>

							</div>

						<?php endif; ?>


					</section>

				<?php endif; ?>


				<!-- =================================================
				     GLIMPSES OF OUR INITIATIVES
				================================================== -->

				<?php if (! empty($gallery)) : ?>

					<section class="csc-story-glimpses-section">

						<h2 class="csc-glimpses-heading">

							<?php
							esc_html_e(
								'GLIMPSES OF OUR INITIATIVES',
								'custom-shortcodes-components'
							);
							?>

						</h2>


						<div class="csc-glimpses-grid">

							<?php foreach ($gallery as $index => $g_img) : ?>

								<div class="csc-glimpse-item">

									<a
										href="<?php echo esc_url($g_img); ?>"
										class="csc-glimpse-link"
										data-lightbox="csc-glimpses"
									>

										<img
											src="<?php echo esc_url($g_img); ?>"
											alt="<?php printf(
												esc_attr__(
													'Glimpse photo %d',
													'custom-shortcodes-components'
												),
												$index + 1
											); ?>"
											loading="lazy"
										/>

									</a>

								</div>

							<?php endforeach; ?>

						</div>

					</section>

				<?php endif; ?>


			</div>

		</section>


	</div>

<?php

endwhile;

get_footer();