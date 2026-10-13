<?php
/**
 * Admin Menu: Custom Shortcodes Parent Menu
 *
 * @package CustomShortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register parent admin menu for Custom Shortcodes.
 */
function csc_register_custom_shortcodes_menu() {
	add_menu_page(
		__( 'Custom Shortcodes', 'custom-shortcodes-components' ),
		__( 'Custom Shortcodes', 'custom-shortcodes-components' ),
		'edit_posts',
		'custom-shortcodes',
		'csc_render_custom_shortcodes_dashboard',
		'dashicons-location-alt',
		26
	);

	// Dashboard Overview submenu.
	add_submenu_page(
		'custom-shortcodes',
		__( 'Overview', 'custom-shortcodes-components' ),
		__( 'Overview', 'custom-shortcodes-components' ),
		'edit_posts',
		'custom-shortcodes',
		'csc_render_custom_shortcodes_dashboard'
	);
}
add_action( 'admin_menu', 'csc_register_custom_shortcodes_menu', 9 );

/**
 * Ensure clean submenu ordering under Custom Shortcodes.
 */
function csc_reorder_admin_submenus() {
	global $submenu;

	if ( empty( $submenu['custom-shortcodes'] ) || ! is_array( $submenu['custom-shortcodes'] ) ) {
		return;
	}

	// Priority sort items based on expected section groups
	$ordered            = array();
	$overview           = null;
	$hp_map_items       = array();
	$announcement_items = array();
	$download_items     = array();
	$marquee_items      = array();
	$scheme_items       = array();
	$story_items        = array();
	$others             = array();

	foreach ( $submenu['custom-shortcodes'] as $item ) {
		$link = isset( $item[2] ) ? $item[2] : '';

		if ( 'custom-shortcodes' === $link ) {
			$overview = $item;
		} elseif ( strpos( $link, 'post_type=district_map' ) !== false || strpos( $link, 'taxonomy=hp_map_category' ) !== false || strpos( $link, 'taxonomy=dashboard_category' ) !== false ) {
			$hp_map_items[] = $item;
		} elseif ( strpos( $link, 'post_type=announcement' ) !== false ) {
			$announcement_items[] = $item;
		} elseif ( strpos( $link, 'post_type=important_download' ) !== false ) {
			$download_items[] = $item;
		} elseif ( strpos( $link, 'post_type=csc_marquee' ) !== false || strpos( $link, 'taxonomy=csc_marquee_category' ) !== false ) {
			$marquee_items[] = $item;
		} elseif ( strpos( $link, 'post_type=csc_scheme' ) !== false || strpos( $link, 'taxonomy=csc_scheme_category' ) !== false || strpos( $link, 'csc-scheme-settings' ) !== false ) {
			$scheme_items[] = $item;
		} elseif ( strpos( $link, 'post_type=csc_story' ) !== false || strpos( $link, 'taxonomy=csc_story_' ) !== false ) {
			$story_items[] = $item;
		} elseif ( strpos( $link, 'post_type=csc_org_member' ) !== false || strpos( $link, 'taxonomy=csc_org_category' ) !== false || strpos( $link, 'taxonomy=csc_org_level' ) !== false ) {
			$org_items[] = $item;
		} else {
			$others[] = $item;
		}
	}

	if ( $overview ) {
		$ordered[] = $overview;
	}
	foreach ( $hp_map_items as $item ) {
		$ordered[] = $item;
	}
	foreach ( $announcement_items as $item ) {
		$ordered[] = $item;
	}
	foreach ( $download_items as $item ) {
		$ordered[] = $item;
	}
	foreach ( $marquee_items as $item ) {
		$ordered[] = $item;
	}
	foreach ( $scheme_items as $item ) {
		$ordered[] = $item;
	}
	foreach ( $story_items as $item ) {
		$ordered[] = $item;
	}
	if ( ! empty( $org_items ) ) {
		foreach ( $org_items as $item ) {
			$ordered[] = $item;
		}
	}
	foreach ( $others as $item ) {
		$ordered[] = $item;
	}

	$submenu['custom-shortcodes'] = $ordered;
}
add_action( 'admin_menu', 'csc_reorder_admin_submenus', 999 );

/**
 * Render the Himachal Dynamic Content Manager dashboard page.
 */
function csc_render_custom_shortcodes_dashboard() {
	// HP Map stats
	$map_count     = wp_count_posts( 'district_map' );
	$map_cat_count = wp_count_terms( array( 'taxonomy' => 'hp_map_category', 'hide_empty' => false ) );
	if ( is_wp_error( $map_cat_count ) || 0 === $map_cat_count ) {
		$map_cat_count = wp_count_terms( array( 'taxonomy' => 'dashboard_category', 'hide_empty' => false ) );
		if ( is_wp_error( $map_cat_count ) ) {
			$map_cat_count = 0;
		}
	}

	// Marquee stats
	$marquee_count  = wp_count_posts( 'csc_marquee' );
	$category_count = wp_count_terms( array( 'taxonomy' => 'csc_marquee_category', 'hide_empty' => false ) );
	if ( is_wp_error( $category_count ) ) {
		$category_count = 0;
	}
	?>
	<div class="wrap" style="max-width: 1060px; margin-top: 20px;">

		<h1 style="font-size: 24px; font-weight: 600; margin-bottom: 24px;">
			<span class="dashicons dashicons-location-alt" style="font-size: 28px; width: 28px; height: 28px; margin-right: 6px; vertical-align: middle; color: #0b3b60;"></span>
			<?php esc_html_e( 'Custom Shortcodes', 'custom-shortcodes-components' ); ?>
		</h1>

		<!-- 1. HP MAP SECTION -->
		<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
			<h2 style="margin-top: 0; font-size: 18px; color: #0b3b60; display: flex; align-items: center; gap: 8px;">
				<span class="dashicons dashicons-location" style="font-size: 22px; color: #0b3b60;"></span>
				<?php esc_html_e( 'HP Map (Himachal Interactive Map & Demographic Dashboard)', 'custom-shortcodes-components' ); ?>
			</h2>
			<p style="font-size: 14px; line-height: 1.6; color: #50575e;">
				<?php esc_html_e( 'Manage the interactive Himachal Pradesh SVG district map, dynamic category tabs, demographic statistics, and district photo textures directly from the WordPress Dashboard.', 'custom-shortcodes-components' ); ?>
			</p>

			<div style="display: flex; gap: 16px; margin: 20px 0; flex-wrap: wrap;">
				<div style="flex: 1; min-width: 220px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Published HP Map Items', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #0b3b60; margin: 6px 0;">
						<?php echo esc_html( $map_count->publish ?? 0 ); ?>
					</div>
					<div style="display: flex; gap: 8px; flex-wrap: wrap;">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=district_map' ) ); ?>" class="button button-secondary">
							<?php esc_html_e( 'All HP Map Items', 'custom-shortcodes-components' ); ?> &rarr;
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=district_map' ) ); ?>" class="button button-primary" style="background: #0b3b60; border-color: #0b3b60;">
							+ <?php esc_html_e( 'Add New', 'custom-shortcodes-components' ); ?>
						</a>
					</div>
				</div>

				<div style="flex: 1; min-width: 220px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'HP Map Categories', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #107c41; margin: 6px 0;">
						<?php echo esc_html( $map_cat_count ); ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=hp_map_category&post_type=district_map' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Manage Categories', 'custom-shortcodes-components' ); ?> &rarr;
					</a>
				</div>
			</div>

			<h3 style="font-size: 15px; margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e( 'Shortcode Usage Examples', 'custom-shortcodes-components' ); ?></h3>
			<table class="widefat striped" style="margin-top: 10px;">
				<thead>
					<tr>
						<th style="width: 40%;"><?php esc_html_e( 'Shortcode', 'custom-shortcodes-components' ); ?></th>
						<th><?php esc_html_e( 'Description', 'custom-shortcodes-components' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>[hp_map]</code></td>
						<td><?php esc_html_e( 'Displays the interactive HP Map demographic dashboard with tabs, SVG map, announcements & downloads.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[hp_map category="demographic-spread-of-minorities"]</code></td>
						<td><?php esc_html_e( 'Displays the HP Map with a specific category tab active by default.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[hp_minority_dashboard]</code></td>
						<td><?php esc_html_e( 'Backward-compatible shortcode for the complete dashboard component.', 'custom-shortcodes-components' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- 2. ANNOUNCEMENTS & IMPORTANT DOWNLOADS SECTION -->
		<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
			<h2 style="margin-top: 0; font-size: 18px; color: #0b3b60; display: flex; align-items: center; gap: 8px;">
				<span class="dashicons dashicons-megaphone" style="font-size: 22px; color: #0b3b60;"></span>
				<?php esc_html_e( 'Announcements & Important Downloads (Sidebar Cards)', 'custom-shortcodes-components' ); ?>
			</h2>
			<p style="font-size: 14px; line-height: 1.6; color: #50575e;">
				<?php esc_html_e( 'Manage your dynamic Announcements and downloadable documents (PDF, Word, Excel, ZIP) with the native WordPress Media Library. Render them anywhere via shortcode or alongside the HP Map.', 'custom-shortcodes-components' ); ?>
			</p>

			<?php
			$ann_count = wp_count_posts( 'announcement' );
			$dl_count  = wp_count_posts( 'important_download' );
			?>
			<div style="display: flex; gap: 16px; margin: 20px 0; flex-wrap: wrap;">
				<div style="flex: 1; min-width: 220px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Published Announcements', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #107c41; margin: 6px 0;">
						<?php echo esc_html( $ann_count->publish ?? 0 ); ?>
					</div>
					<div style="display: flex; gap: 8px; flex-wrap: wrap;">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=announcement' ) ); ?>" class="button button-secondary">
							<?php esc_html_e( 'All Announcements', 'custom-shortcodes-components' ); ?> &rarr;
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=announcement' ) ); ?>" class="button button-primary" style="background: #107c41; border-color: #107c41;">
							+ <?php esc_html_e( 'Add New', 'custom-shortcodes-components' ); ?>
						</a>
					</div>
				</div>

				<div style="flex: 1; min-width: 220px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Published Downloads', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #ef4444; margin: 6px 0;">
						<?php echo esc_html( $dl_count->publish ?? 0 ); ?>
					</div>
					<div style="display: flex; gap: 8px; flex-wrap: wrap;">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=important_download' ) ); ?>" class="button button-secondary">
							<?php esc_html_e( 'All Downloads', 'custom-shortcodes-components' ); ?> &rarr;
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=important_download' ) ); ?>" class="button button-primary" style="background: #ef4444; border-color: #ef4444;">
							+ <?php esc_html_e( 'Add New', 'custom-shortcodes-components' ); ?>
						</a>
					</div>
				</div>
			</div>

			<h3 style="font-size: 15px; margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e( 'Shortcode Usage Examples', 'custom-shortcodes-components' ); ?></h3>
			<table class="widefat striped" style="margin-top: 10px;">
				<thead>
					<tr>
						<th style="width: 40%;"><?php esc_html_e( 'Shortcode', 'custom-shortcodes-components' ); ?></th>
						<th><?php esc_html_e( 'Description', 'custom-shortcodes-components' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>[hp_sidebar_cards]</code></td>
						<td><?php esc_html_e( 'Displays the Latest Announcements and Important Downloads sidebar cards dynamically (3 latest items each).', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[hp_sidebar_cards announcement_limit="5" download_limit="5"]</code></td>
						<td><?php esc_html_e( 'Customizes the number of announcements and downloads displayed in the cards.', 'custom-shortcodes-components' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- 2. CONTINUOUS MARQUEE SLIDER SECTION -->
		<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
			<h2 style="margin-top: 0; font-size: 18px; color: #1d2327;">
				<?php esc_html_e( 'Continuous Marquee Slider', 'custom-shortcodes-components' ); ?>
			</h2>
			<p style="font-size: 14px; line-height: 1.6; color: #50575e;">
				<?php esc_html_e( 'Manage your continuous marquee logos and items directly from the WordPress Dashboard. Assign them to categories and display them anywhere using the shortcode.', 'custom-shortcodes-components' ); ?>
			</p>

			<div style="display: flex; gap: 16px; margin: 20px 0; flex-wrap: wrap;">
				<div style="flex: 1; min-width: 220px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Published Items', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #2271b1; margin: 6px 0;">
						<?php echo esc_html( $marquee_count->publish ?? 0 ); ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=csc_marquee' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Manage Marquee Items', 'custom-shortcodes-components' ); ?> &rarr;
					</a>
				</div>

				<div style="flex: 1; min-width: 220px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Categories', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #2271b1; margin: 6px 0;">
						<?php echo esc_html( $category_count ); ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=csc_marquee_category&post_type=csc_marquee' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Manage Categories', 'custom-shortcodes-components' ); ?> &rarr;
					</a>
				</div>
			</div>

			<h3 style="font-size: 15px; margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e( 'Shortcode Usage Examples', 'custom-shortcodes-components' ); ?></h3>
			<table class="widefat striped" style="margin-top: 10px;">
				<thead>
					<tr>
						<th style="width: 40%;"><?php esc_html_e( 'Shortcode', 'custom-shortcodes-components' ); ?></th>
						<th><?php esc_html_e( 'Description', 'custom-shortcodes-components' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>[csc_marquee]</code></td>
						<td><?php esc_html_e( 'Displays all published Marquee Items.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_marquee category="brands"]</code></td>
						<td><?php esc_html_e( 'Displays items assigned to the "brands" category.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_marquee category="partners" speed="25" gap="50"]</code></td>
						<td><?php esc_html_e( 'Custom speed (25s cycle) and item gap (50px).', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_marquee category="clients" pause_on_hover="yes"]</code></td>
						<td><?php esc_html_e( 'Pauses continuous animation on mouse hover.', 'custom-shortcodes-components' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- 3. SCHEMES & SERVICES SWIPER SLIDER SECTION -->
		<?php
		$scheme_count          = wp_count_posts( 'csc_scheme' );
		$scheme_category_count = wp_count_terms( array( 'taxonomy' => 'csc_scheme_category', 'hide_empty' => false ) );
		if ( is_wp_error( $scheme_category_count ) ) {
			$scheme_category_count = 0;
		}
		?>
		<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
			<h2 style="margin-top: 0; font-size: 18px; color: #1d2327;">
				<?php esc_html_e( 'Schemes & Services Swiper Slider', 'custom-shortcodes-components' ); ?>
			</h2>
			<p style="font-size: 14px; line-height: 1.6; color: #50575e;">
				<?php esc_html_e( 'Create and display colorful scheme cards (like Education Loan, Self Employment, Skill Development, Women Empowerment) inside a responsive Swiper slider with custom accent colors, icons, and action buttons.', 'custom-shortcodes-components' ); ?>
			</p>

			<div style="display: flex; gap: 16px; margin: 20px 0; flex-wrap: wrap;">
				<div style="flex: 1; min-width: 220px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Published Schemes', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #109b48; margin: 6px 0;">
						<?php echo esc_html( $scheme_count->publish ?? 0 ); ?>
					</div>
					<div style="display: flex; gap: 8px; flex-wrap: wrap;">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=csc_scheme' ) ); ?>" class="button button-secondary">
							<?php esc_html_e( 'All Schemes', 'custom-shortcodes-components' ); ?> &rarr;
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=csc_scheme' ) ); ?>" class="button button-primary">
							+ <?php esc_html_e( 'Add New Scheme', 'custom-shortcodes-components' ); ?>
						</a>
					</div>
				</div>

				<div style="flex: 1; min-width: 220px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Scheme Categories', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #0a3b75; margin: 6px 0;">
						<?php echo esc_html( $scheme_category_count ); ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=csc_scheme_category&post_type=csc_scheme' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Manage Categories', 'custom-shortcodes-components' ); ?> &rarr;
					</a>
				</div>

				<div style="flex: 1; min-width: 220px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #166534;"><?php esc_html_e( 'Header Logos & Colors', 'custom-shortcodes-components' ); ?></span>
					<p style="font-size: 13px; color: #15803d; margin: 6px 0 12px;">
						<?php esc_html_e( 'Map existing header logos to categories & Elementor colors.', 'custom-shortcodes-components' ); ?>
					</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=csc-scheme-settings' ) ); ?>" class="button button-secondary" style="background: #fff; border-color: #16a34a; color: #166534; font-weight: 600;">
						<span class="dashicons dashicons-admin-generic" style="vertical-align: middle; margin-top: -2px;"></span>
						<?php esc_html_e( 'Logo & Color Settings', 'custom-shortcodes-components' ); ?> &rarr;
					</a>
				</div>
			</div>

			<h3 style="font-size: 15px; margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e( 'Shortcode Usage Examples', 'custom-shortcodes-components' ); ?></h3>
			<table class="widefat striped" style="margin-top: 10px;">
				<thead>
					<tr>
						<th style="width: 40%;"><?php esc_html_e( 'Shortcode', 'custom-shortcodes-components' ); ?></th>
						<th><?php esc_html_e( 'Description', 'custom-shortcodes-components' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>[csc_schemes]</code></td>
						<td><?php esc_html_e( 'Displays all published Schemes in a 4-column responsive Swiper slider.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_schemes category="education,business"]</code></td>
						<td><?php esc_html_e( 'Displays schemes assigned to specific categories.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_schemes slides_per_view="3" space_between="30"]</code></td>
						<td><?php esc_html_e( 'Custom desktop columns (3 slides) and 30px card spacing.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_schemes autoplay="yes" autoplay_delay="4000" loop="yes"]</code></td>
						<td><?php esc_html_e( 'Continuous autoplay slider with 4s interval.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_schemes arrows="yes" dots="yes"]</code></td>
						<td><?php esc_html_e( 'Includes both side arrow buttons and bottom pagination dots.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[schemes_services_grid]</code></td>
						<td><?php esc_html_e( 'Displays schemes and services in a responsive grid layout with pagination.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[schemes_services_grid category="cat-1"]</code></td>
						<td><?php esc_html_e( 'Displays schemes from Category 1 (or specific category slug) in a paginated grid.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[schemes_services_grid category="cat-2" columns="3" posts_per_page="6"]</code></td>
						<td><?php esc_html_e( 'Category 2 schemes in a 3-column grid showing 6 items per page with page numbers.', 'custom-shortcodes-components' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- 4. SUCCESS STORIES SECTION -->
		<?php
		$story_count          = wp_count_posts( 'csc_story' );
		$story_cat_count      = wp_count_terms( array( 'taxonomy' => 'csc_story_category', 'hide_empty' => false ) );
		$story_district_count = wp_count_terms( array( 'taxonomy' => 'csc_story_district', 'hide_empty' => false ) );
		if ( is_wp_error( $story_cat_count ) ) {
			$story_cat_count = 0;
		}
		if ( is_wp_error( $story_district_count ) ) {
			$story_district_count = 0;
		}
		?>
		<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
			<h2 style="margin-top: 0; font-size: 18px; color: #1d2327;">
				<span class="dashicons dashicons-awards" style="color: #002d62; vertical-align: middle; margin-right: 4px;"></span>
				<?php esc_html_e( 'Success Stories (Video Slider, Grid Cards & Single Page)', 'custom-shortcodes-components' ); ?>
			</h2>
			<p style="font-size: 14px; line-height: 1.6; color: #50575e;">
				<?php esc_html_e( 'Display inspirational beneficiary success stories with video, loan details, 4 structured cards (About, Challenge, Support, Impact), and "Glimpses of our initiatives" photo gallery across your home page, grid archive, and individual story pages.', 'custom-shortcodes-components' ); ?>
			</p>

			<div style="display: flex; gap: 16px; margin: 20px 0; flex-wrap: wrap;">
				<div style="flex: 1; min-width: 200px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Published Stories', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #002d62; margin: 6px 0;">
						<?php echo esc_html( $story_count->publish ?? 0 ); ?>
					</div>
					<div style="display: flex; gap: 8px; flex-wrap: wrap;">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=csc_story' ) ); ?>" class="button button-secondary">
							<?php esc_html_e( 'All Stories', 'custom-shortcodes-components' ); ?> &rarr;
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=csc_story' ) ); ?>" class="button button-primary">
							+ <?php esc_html_e( 'Add New', 'custom-shortcodes-components' ); ?>
						</a>
					</div>
				</div>

				<div style="flex: 1; min-width: 180px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Districts', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #1d4ed8; margin: 6px 0;">
						<?php echo esc_html( $story_district_count ); ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=csc_story_district&post_type=csc_story' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Manage Districts', 'custom-shortcodes-components' ); ?> &rarr;
					</a>
				</div>

				<div style="flex: 1; min-width: 180px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Categories', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #0284c7; margin: 6px 0;">
						<?php echo esc_html( $story_cat_count ); ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=csc_story_category&post_type=csc_story' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Manage Categories', 'custom-shortcodes-components' ); ?> &rarr;
					</a>
				</div>
			</div>

			<h3 style="font-size: 15px; margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e( 'Shortcode Usage Reference', 'custom-shortcodes-components' ); ?></h3>
			<table class="widefat striped" style="margin-top: 10px;">
				<thead>
					<tr>
						<th style="width: 42%;"><?php esc_html_e( 'Shortcode', 'custom-shortcodes-components' ); ?></th>
						<th><?php esc_html_e( 'Description & Usage', 'custom-shortcodes-components' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>[csc_story_slider]</code></td>
						<td><?php esc_html_e( 'Home Page section: 2-column video player + story details + "View More Stories >" button and pagination dots.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_story_slider district="chamba"]</code></td>
						<td><?php esc_html_e( 'Filters the slider to show only stories from a specific district or category.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_story_grid]</code></td>
						<td><?php esc_html_e( 'Grid Cards Page: 3-column responsive cards showing District, Loan Amount, Scheme, and "Read More" button with interactive filter tabs.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_story_grid show_filter="yes" columns="3"]</code></td>
						<td><?php esc_html_e( 'Grid with interactive District filter pills and custom columns (3 or 4).', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_story_single id="123"]</code></td>
						<td><?php esc_html_e( 'Embeds the complete single story view (Hero banner, featured card, 4 info cards, and glimpses gallery) anywhere.', 'custom-shortcodes-components' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- 5. ORGANIZATION CHART & BOARD HIERARCHY TREE SECTION -->
		<?php
		$org_count     = wp_count_posts( 'csc_org_member' );
		$org_cat_count = wp_count_terms( array( 'taxonomy' => 'csc_org_category', 'hide_empty' => false ) );
		if ( is_wp_error( $org_cat_count ) ) {
			$org_cat_count = 0;
		}
		?>
		<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
			<h2 style="margin-top: 0; font-size: 18px; color: #1d2327;">
				<span class="dashicons dashicons-networking" style="color: #009639; vertical-align: middle; margin-right: 4px;"></span>
				<?php esc_html_e( 'Organization Chart / Hierarchy Tree (Board of Directors)', 'custom-shortcodes-components' ); ?>
			</h2>
			<p style="font-size: 14px; line-height: 1.6; color: #50575e;">
				<?php esc_html_e( 'Manage your dynamic Organization Hierarchy Tree with Apex Leader (Chairman portrait & green badge), tree connector lines, and subordinate Director cards with customizable avatar colors and taxonomies.', 'custom-shortcodes-components' ); ?>
			</p>

			<div style="display: flex; gap: 16px; margin: 20px 0; flex-wrap: wrap;">
				<div style="flex: 1; min-width: 200px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Published Members', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #009639; margin: 6px 0;">
						<?php echo esc_html( $org_count->publish ?? 0 ); ?>
					</div>
					<div style="display: flex; gap: 8px; flex-wrap: wrap;">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=csc_org_member' ) ); ?>" class="button button-secondary">
							<?php esc_html_e( 'All Members', 'custom-shortcodes-components' ); ?> &rarr;
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=csc_org_member' ) ); ?>" class="button button-primary" style="background: #009639; border-color: #009639;">
							+ <?php esc_html_e( 'Add New Member', 'custom-shortcodes-components' ); ?>
						</a>
					</div>
				</div>

				<div style="flex: 1; min-width: 180px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Org Categories', 'custom-shortcodes-components' ); ?></span>
					<div style="font-size: 28px; font-weight: 700; color: #0284c7; margin: 6px 0;">
						<?php echo esc_html( $org_cat_count ); ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=csc_org_category&post_type=csc_org_member' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Manage Categories', 'custom-shortcodes-components' ); ?> &rarr;
					</a>
				</div>

				<div style="flex: 1; min-width: 220px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 16px;">
					<span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #166534;"><?php esc_html_e( 'One-Click Demo Setup', 'custom-shortcodes-components' ); ?></span>
					<p style="font-size: 13px; color: #15803d; margin: 6px 0 12px;">
						<?php esc_html_e( 'Auto-populate the 7 board members matching the design.', 'custom-shortcodes-components' ); ?>
					</p>
					<button type="button" class="button button-secondary csc-seed-org-data-btn" style="background: #fff; border-color: #16a34a; color: #166534; font-weight: 600;">
						<span class="dashicons dashicons-update" style="vertical-align: middle; margin-top: -2px;"></span>
						<?php esc_html_e( 'Seed 7 Sample Members', 'custom-shortcodes-components' ); ?>
					</button>
					<span class="csc-seed-status" style="margin-left: 8px; font-size: 12px; font-weight: 600;"></span>
				</div>
			</div>

			<h3 style="font-size: 15px; margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e( 'Shortcode Usage Reference', 'custom-shortcodes-components' ); ?></h3>
			<table class="widefat striped" style="margin-top: 10px;">
				<thead>
					<tr>
						<th style="width: 42%;"><?php esc_html_e( 'Shortcode', 'custom-shortcodes-components' ); ?></th>
						<th><?php esc_html_e( 'Description & Options', 'custom-shortcodes-components' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>[csc_org_chart]</code></td>
						<td><?php esc_html_e( 'Displays the full Organization Hierarchy Tree matching the pixel-perfect design.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_org_chart category="board-of-directors"]</code></td>
						<td><?php esc_html_e( 'Displays members belonging to a specific taxonomy category or department.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_org_chart show_tabs="yes"]</code></td>
						<td><?php esc_html_e( 'Displays dynamic category filter tabs for switching between committees/wings.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_org_chart line_color="#009639"]</code></td>
						<td><?php esc_html_e( 'Customizes the connecting tree branch line color (e.g. #009639, #0b3b60).', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[csc_org_chart title="Board of Directors" subtitle="Himachal Pradesh Leadership"]</code></td>
						<td><?php esc_html_e( 'Adds a centered title and subtitle heading above the chart.', 'custom-shortcodes-components' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.csc-seed-org-data-btn').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var $status = $('.csc-seed-status');
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Generating...', 'custom-shortcodes-components' ) ); ?>');
				$status.css('color', '#64748b').text('Seeding data...');

				$.post(ajaxurl, {
					action: 'csc_seed_org_chart_data',
					nonce: '<?php echo esc_js( wp_create_nonce( 'csc_org_seed_nonce' ) ); ?>'
				}, function(res) {
					if (res.success) {
						$status.css('color', '#16a34a').text(' ' + res.data.message);
						setTimeout(function() {
							location.reload();
						}, 1200);
					} else {
						$status.css('color', '#dc2626').text(' Error: ' + (res.data ? res.data.message : 'Failed'));
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Seed 7 Sample Members', 'custom-shortcodes-components' ) ); ?>');
					}
				}).fail(function() {
					$status.css('color', '#dc2626').text(' Request failed');
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Seed 7 Sample Members', 'custom-shortcodes-components' ) ); ?>');
				});
			});
		});
		</script>
	</div>
	<?php
}
