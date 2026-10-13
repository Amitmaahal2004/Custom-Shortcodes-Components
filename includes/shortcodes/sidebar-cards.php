<?php
/**
 * Shortcode: [hp_sidebar_cards]
 * Renders dynamic Latest Announcements & Important Downloads sidebar cards.
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all sidebar and standalone cards shortcodes on init.
 */
function csc_register_sidebar_cards_shortcode() {
	add_shortcode( 'hp_sidebar_cards', 'csc_hp_sidebar_cards_shortcode' );
	add_shortcode( 'important_downloads', 'csc_important_downloads_shortcode' );
	add_shortcode( 'hp_downloads', 'csc_important_downloads_shortcode' );
	add_shortcode( 'csc_downloads', 'csc_important_downloads_shortcode' );
	add_shortcode( 'downloads_list', 'csc_important_downloads_shortcode' );
	add_shortcode( 'latest_announcements', 'csc_latest_announcements_shortcode' );
	add_shortcode( 'hp_announcements', 'csc_latest_announcements_shortcode' );
}
add_action( 'init', 'csc_register_sidebar_cards_shortcode' );

/**
 * Fetch published announcements query.
 *
 * @param int $limit Number of items to retrieve (-1 for all).
 * @return WP_Query
 */
function csc_get_published_announcements( $limit = -1 ) {
	$args = array(
		'post_type'      => 'announcement',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	);

	return new WP_Query( $args );
}

/**
 * Fetch published downloads query.
 *
 * @param int $limit Number of items to retrieve (-1 for all).
 * @return WP_Query
 */
function csc_get_published_downloads( $limit = -1 ) {
	$args = array(
		'post_type'      => 'important_download',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	);

	return new WP_Query( $args );
}

/**
 * Render the Announcements and Important Downloads sidebar cards HTML.
 *
 * @param int $announcement_limit Number of visible announcements before View All.
 * @param int $download_limit     Number of visible downloads before View All.
 * @return string HTML output.
 */
function csc_render_sidebar_cards( $announcement_limit = 3, $download_limit = 3 ) {
	$announcements_query = csc_get_published_announcements( -1 );
	$downloads_query     = csc_get_published_downloads( -1 );
	$ann_visible_limit   = max( 1, absint( $announcement_limit ) );
	$dl_visible_limit    = max( 1, absint( $download_limit ) );

	ob_start();

	$template_path = CSC_PATH . 'templates/sidebar-cards.php';
	if ( file_exists( $template_path ) ) {
		include $template_path;
	}

	return ob_get_clean();
}

/**
 * Handler for [hp_sidebar_cards] shortcode.
 *
 * Usage:
 * [hp_sidebar_cards]
 * [hp_sidebar_cards announcement_limit="3" download_limit="3"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function csc_hp_sidebar_cards_shortcode( $atts ) {
	// Ensure sidebar cards stylesheet is enqueued
	wp_enqueue_style( 'csc-hp-map' );

	$atts = shortcode_atts(
		array(
			'announcement_limit' => 3,
			'download_limit'     => 3,
			'announcements'      => '', // alias
			'downloads'          => '', // alias
		),
		$atts,
		'hp_sidebar_cards'
	);

	$ann_limit = ! empty( $atts['announcements'] ) ? $atts['announcements'] : $atts['announcement_limit'];
	$dl_limit  = ! empty( $atts['downloads'] ) ? $atts['downloads'] : $atts['download_limit'];

	return csc_render_sidebar_cards( absint( $ann_limit ), absint( $dl_limit ) );
}

/**
 * Render standalone Important Downloads card HTML.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function csc_render_standalone_downloads( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'limit'        => -1,
			'title'        => __( 'Important Downloads', 'custom-shortcodes-components' ),
			'show_header'  => 'yes',
			'view_all'     => 'no',
			'view_all_url' => home_url( '/downloads/' ),
			'card_wrap'    => 'yes',
			'orderby'      => 'menu_order',
			'order'        => 'ASC',
		),
		$atts,
		'important_downloads'
	);

	$limit = (int) $atts['limit'];
	if ( 0 === $limit ) {
		$limit = -1;
	}

	$order = strtoupper( sanitize_text_field( $atts['order'] ) ) === 'DESC' ? 'DESC' : 'ASC';

	$args = array(
		'post_type'      => 'important_download',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => array(
			'menu_order' => $order,
			'date'       => 'DESC',
		),
	);

	$downloads_query = new WP_Query( $args );

	ob_start();
	?>
	<div class="hp-standalone-downloads-wrapper <?php echo 'yes' === $atts['card_wrap'] ? 'hp-dashboard-card hp-downloads-card' : ''; ?>">
		<?php if ( 'yes' === $atts['show_header'] && ! empty( $atts['title'] ) ) : ?>
			<div class="hp-card-header">
				<h3 class="hp-card-title">
					<?php echo esc_html( $atts['title'] ); ?>
				</h3>
				<?php if ( 'yes' === $atts['view_all'] && ! empty( $atts['view_all_url'] ) ) : ?>
					<div class="hp-card-footer">
						<a href="<?php echo esc_url( $atts['view_all_url'] ); ?>" class="hp-view-all-btn">
							<span class="hp-view-btn-text">
								<?php esc_html_e( 'View All', 'custom-shortcodes-components' ); ?>
							</span>
						</a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="hp-downloads-list hp-standalone-downloads-list">
			<?php
			if ( $downloads_query->have_posts() ) :
				while ( $downloads_query->have_posts() ) :
					$downloads_query->the_post();
					$dl_id    = get_the_ID();
					$file_url = get_post_meta( $dl_id, '_download_file_url', true );
					if ( empty( $file_url ) ) {
						$file_url = get_post_meta( $dl_id, '_hdd_download_file_url', true );
					}
					$has_file = ! empty( $file_url ) && '#' !== $file_url;
					?>
					<div class="hp-download-item">
						<!-- Document Icon -->
						<div class="hp-download-doc-icon">
							<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
								<polyline points="14 2 14 8 20 8"></polyline>
								<line x1="16" y1="13" x2="8" y2="13"></line>
								<line x1="16" y1="17" x2="8" y2="17"></line>
								<polyline points="10 9 9 9 8 9"></polyline>
							</svg>
						</div>

						<!-- Download Title -->
						<div class="hp-download-content">
							<?php if ( $has_file ) : ?>
								<a href="<?php echo esc_url( $file_url ); ?>" download class="hp-download-title" target="_blank" rel="noopener noreferrer">
									<?php the_title(); ?>
								</a>
							<?php else : ?>
								<span class="hp-download-title">
									<?php the_title(); ?>
								</span>
							<?php endif; ?>
						</div>

						<!-- Download Action Icon / Button -->
						<?php if ( $has_file ) : ?>
							<a href="<?php echo esc_url( $file_url ); ?>" download class="hp-download-action-btn" aria-label="<?php echo esc_attr( sprintf( __( 'Download %s', 'custom-shortcodes-components' ), get_the_title() ) ); ?>" target="_blank" rel="noopener noreferrer">
								<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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
				<p class="hp-empty-message">
					<?php esc_html_e( 'No downloads currently available.', 'custom-shortcodes-components' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Shortcode handler for [important_downloads] / [hp_downloads] / [downloads_list].
 *
 * Usage:
 * [important_downloads]
 * [important_downloads limit="10"]
 * [hp_downloads title="Downloads"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function csc_important_downloads_shortcode( $atts ) {
	wp_enqueue_style( 'csc-hp-map' );
	return csc_render_standalone_downloads( (array) $atts );
}

/**
 * Shortcode handler for standalone [latest_announcements] / [hp_announcements].
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function csc_latest_announcements_shortcode( $atts ) {
	wp_enqueue_style( 'csc-hp-map' );
	wp_enqueue_script( 'csc-hp-map' );

	$atts = shortcode_atts(
		array(
			'limit' => 3,
		),
		$atts,
		'latest_announcements'
	);

	$ann_limit = max( 1, absint( $atts['limit'] ) );
	$announcements_query = csc_get_published_announcements( -1 );
	$downloads_query     = new WP_Query(); // Empty
	$dl_visible_limit    = 0;
	$ann_visible_limit   = $ann_limit;

	ob_start();
	$template_path = CSC_PATH . 'templates/sidebar-cards.php';
	if ( file_exists( $template_path ) ) {
		include $template_path;
	}
	return ob_get_clean();
}
