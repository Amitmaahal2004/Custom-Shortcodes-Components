<?php

/**
 * Frontend Template: Dynamic Announcements & Important Downloads Sidebar Cards
 *
 * Used by:
 * - Shortcode: [hp_sidebar_cards]
 * - HP Map Right Column: [hp_map], [hp_minority_dashboard]
 *
 * @package CustomShortcodesComponents
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Variables expected:
 *
 * @var WP_Query $announcements_query
 * @var WP_Query $downloads_query
 * @var int      $ann_visible_limit (optional, default 3)
 * @var int      $dl_visible_limit  (optional, default 3)
 */

$ann_limit = isset($ann_visible_limit) ? max(1, absint($ann_visible_limit)) : 3;
$dl_limit  = isset($dl_visible_limit) ? max(1, absint($dl_visible_limit)) : 3;


/*
|--------------------------------------------------------------------------
| Calculate total items BEFORE rendering the cards
|--------------------------------------------------------------------------
*/

$total_ann = 0;

if (
	isset($announcements_query) &&
	$announcements_query instanceof WP_Query
) {
	$total_ann = (int) $announcements_query->post_count;
}


$total_dl = 0;

if (
	isset($downloads_query) &&
	$downloads_query instanceof WP_Query
) {
	$total_dl = (int) $downloads_query->post_count;
}

?>

<div class="hp-sidebar-cards">


	<!-- =========================================================
	     1. LATEST ANNOUNCEMENTS CARD
	     ========================================================= -->

	<div class="hp-dashboard-card hp-announcements-card">

		<div class="hp-card-header">

			<h3 class="hp-card-title">
				<?php esc_html_e('Latest Announcements', 'custom-shortcodes-components'); ?>
			</h3>

			<?php if ($total_ann > $ann_limit) : ?>

				<div class="hp-card-footer">

					<button
						type="button"
						class="hp-view-all-btn"
						data-target="hp-announcements-list"
						data-visible-limit="<?php echo esc_attr($ann_limit); ?>"
						data-more-text="<?php esc_attr_e('View All', 'custom-shortcodes-components'); ?>"
						data-less-text="<?php esc_attr_e('View Less', 'custom-shortcodes-components'); ?>"
						aria-expanded="false">
						<span class="hp-view-btn-text">
							<?php esc_html_e('View All', 'custom-shortcodes-components'); ?>
						</span>



					</button>

				</div>

			<?php endif; ?>

		</div>


		<div
			class="hp-announcements-list"
			id="hp-announcements-list">

			<?php

			$ann_count = 0;

			if (
				isset($announcements_query) &&
				$announcements_query->have_posts()
			) :

				while ($announcements_query->have_posts()) :

					$announcements_query->the_post();

					$ann_count++;

					$ann_id = get_the_ID();


					/* Date */

					$date_raw = get_post_meta(
						$ann_id,
						'_announcement_date',
						true
					);

					if (empty($date_raw)) {

						$date_raw = get_post_meta(
							$ann_id,
							'_hdd_announcement_date',
							true
						);
					}

					$timestamp = ! empty($date_raw)
						? strtotime($date_raw)
						: get_the_time('U');

					$day = date_i18n(
						'd',
						$timestamp
					);

					$month_yr = date_i18n(
						'M Y',
						$timestamp
					);


					/* NEW badge */

					$is_new = get_post_meta(
						$ann_id,
						'_announcement_is_new',
						true
					);

					if ('' === $is_new) {

						$is_new = get_post_meta(
							$ann_id,
							'_hdd_announcement_is_new',
							true
						);
					}

					$is_new_active = ('1' === $is_new);


					/* Link */

					$link = get_post_meta(
						$ann_id,
						'_announcement_url',
						true
					);

					if (empty($link)) {

						$link = get_post_meta(
							$ann_id,
							'_hdd_announcement_link',
							true
						);
					}


					/* Hide items after limit */

					$is_hidden = ($ann_count > $ann_limit);

			?>

					<div class="hp-announcement-item <?php echo $is_hidden ? 'hp-item-hidden' : ''; ?>">

						<div class="hp-date-box">

							<span class="hp-date-day">
								<?php echo esc_html($day); ?>
							</span>

							<span class="hp-date-my">
								<?php echo esc_html($month_yr); ?>
							</span>

						</div>


						<div class="hp-ann-body">

							<div class="hp-ann-text">

								<?php if (! empty($link)) : ?>

									<a
										href="<?php echo esc_url($link); ?>"
										class="hp-ann-link hp-ann-title-link"
										target="_blank"
										rel="noopener noreferrer">
										<?php the_title(); ?>
									</a>

								<?php else : ?>

									<?php the_title(); ?>

								<?php endif; ?>


								<?php if ($is_new_active) : ?>

									<span class="hp-new-badge hp-badge-new">
										<?php esc_html_e('NEW', 'custom-shortcodes-components'); ?>
									</span>

								<?php endif; ?>

							</div>

						</div>

					</div>

				<?php

				endwhile;

				wp_reset_postdata();

			else :

				?>

				<div class="hp-announcement-item">

					<div class="hp-ann-body">

						<div
							class="hp-ann-text"
							style="color:#64748b; font-style:italic;">
							<?php esc_html_e('No current announcements published.', 'custom-shortcodes-components'); ?>
						</div>

					</div>

				</div>

			<?php endif; ?>

		</div>

	</div>



	<!-- =========================================================
	     2. IMPORTANT DOWNLOADS CARD
	     ========================================================= -->

	<div class="hp-dashboard-card hp-downloads-card">

		<div class="hp-card-header">

			<h3 class="hp-card-title">
				<?php esc_html_e('Important Downloads', 'custom-shortcodes-components'); ?>
			</h3>


			<div class="hp-card-footer">
				<a
					href="<?php echo esc_url( home_url( '/downloads/' ) ); ?>"
					class="hp-view-all-btn">
					<span class="hp-view-btn-text">
						<?php esc_html_e('View All', 'custom-shortcodes-components'); ?>
					</span>
				</a>
			</div>

		</div>


		<div
			class="hp-downloads-list"
			id="hp-downloads-list">

			<?php

			$dl_count = 0;

			if (
				isset($downloads_query) &&
				$downloads_query->have_posts()
			) :

				while ($downloads_query->have_posts()) :

					$downloads_query->the_post();

					$dl_count++;

					$dl_id = get_the_ID();


					/* File URL */

					$file_url = get_post_meta(
						$dl_id,
						'_download_file_url',
						true
					);

					if (empty($file_url)) {

						$file_url = get_post_meta(
							$dl_id,
							'_hdd_download_file_url',
							true
						);
					}

					$has_file = (
						! empty($file_url) &&
						'#' !== $file_url
					);


					/* Hide items after limit */

					$is_hidden = ($dl_count > $dl_limit);

			?>

					<div class="hp-download-item <?php echo $is_hidden ? 'hp-item-hidden' : ''; ?>">


						<!-- Document Icon -->

						<div class="hp-download-doc-icon">

							<svg
								viewBox="0 0 24 24"
								width="22"
								height="22"
								fill="none"
								stroke="#ef4444"
								stroke-width="1.8"
								stroke-linecap="round"
								stroke-linejoin="round"
								aria-hidden="true">

								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>

								<polyline points="14 2 14 8 20 8"></polyline>

								<line x1="16" y1="13" x2="8" y2="13"></line>

								<line x1="16" y1="17" x2="8" y2="17"></line>

								<polyline points="10 9 9 9 8 9"></polyline>

							</svg>

						</div>


						<!-- Download Title -->

						<div class="hp-download-content">

							<?php if ($has_file) : ?>

								<a
									href="<?php echo esc_url($file_url); ?>"
									download
									class="hp-download-title"
									target="_blank"
									rel="noopener noreferrer">
									<?php the_title(); ?>
								</a>

							<?php else : ?>

								<span class="hp-download-title">
									<?php the_title(); ?>
								</span>

							<?php endif; ?>

						</div>


						<!-- Download Button -->

						<?php if ($has_file) : ?>

							<a
								href="<?php echo esc_url($file_url); ?>"
								download
								class="hp-download-action-btn"
								aria-label="<?php echo esc_attr(
												sprintf(
													__('Download %s', 'custom-shortcodes-components'),
													get_the_title()
												)
											); ?>"
								target="_blank"
								rel="noopener noreferrer">

								<svg
									viewBox="0 0 24 24"
									width="20"
									height="20"
									fill="none"
									stroke="#16a34a"
									stroke-width="2.2"
									stroke-linecap="round"
									stroke-linejoin="round"
									aria-hidden="true">

									<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>

									<polyline points="7 10 12 15 17 10"></polyline>

									<line x1="12" y1="15" x2="12" y2="3"></line>

								</svg>

							</a>

						<?php endif; ?>


					</div>

				<?php

				endwhile;

				wp_reset_postdata();

			else :

				?>

				<div class="hp-download-item">

					<div class="hp-download-content">

						<span
							style="color:#64748b; font-style:italic;">
							<?php esc_html_e('No current downloads available.', 'custom-shortcodes-components'); ?>
						</span>

					</div>

				</div>

			<?php endif; ?>

		</div>

	</div>

</div>