<?php
/**
 * Shortcode: Dynamic Organization Chart / Hierarchy Tree ([csc_org_chart])
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Shortcode [csc_org_chart] and aliases.
 */
function csc_register_org_chart_shortcode() {
	add_shortcode( 'csc_org_chart', 'csc_render_org_chart_shortcode' );
	add_shortcode( 'csc_hierarchy', 'csc_render_org_chart_shortcode' );
	add_shortcode( 'csc_org_tree', 'csc_render_org_chart_shortcode' );
	add_shortcode( 'csc_team_tree', 'csc_render_org_chart_shortcode' );
}
add_action( 'init', 'csc_register_org_chart_shortcode' );

/**
 * Render the Organization Chart Shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function csc_render_org_chart_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'    => '',
			'show_tabs'   => 'no',
			'title'       => '',
			'subtitle'    => '',
			'line_color'  => '#009639',
			'layout'      => 'tree',
			'limit'       => -1,
			'order'       => 'ASC',
			'orderby'     => 'menu_order',
		),
		$atts,
		'csc_org_chart'
	);

	// Fetch categories if show_tabs is enabled
	$all_categories = array();
	if ( 'yes' === strtolower( $atts['show_tabs'] ) ) {
		$all_categories = get_terms( array(
			'taxonomy'   => 'csc_org_category',
			'hide_empty' => true,
		) );
	}

	// Prepare WP_Query
	$query_args = array(
		'post_type'      => 'csc_org_member',
		'post_status'    => 'publish',
		'posts_per_page' => intval( $atts['limit'] ),
		'orderby'        => sanitize_key( $atts['orderby'] ),
		'order'          => 'DESC' === strtoupper( $atts['order'] ) ? 'DESC' : 'ASC',
	);

	// Filter by taxonomy if specified
	if ( ! empty( $atts['category'] ) ) {
		$categories = array_map( 'trim', explode( ',', $atts['category'] ) );
		$field_type = is_numeric( $categories[0] ) ? 'term_id' : 'slug';

		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'csc_org_category',
				'field'    => $field_type,
				'terms'    => $categories,
			),
		);
	}

	$members_query = new WP_Query( $query_args );

	// Fallback: If no posts exist yet, render built-in default items matching the screenshot
	$is_fallback = false;
	$apex_members = array();
	$subordinate_members = array();

	if ( ! $members_query->have_posts() ) {
		$is_fallback = true;
		$default_photo = CSC_URL . 'assets/images/org-chart/dr-dhani-ram-shandil.png';

		$apex_members[] = array(
			'id'           => 0,
			'name'         => 'Dr. (Col.) Dhani Ram Shandil',
			'designation'  => 'Chairman',
			'cadre'        => '',
			'photo_url'    => $default_photo,
			'avatar_color' => 'green',
		);

		$subordinate_members = array(
			array(
				'id'           => 1,
				'name'         => 'Sh. Chander Prakash Verma',
				'designation'  => 'Director',
				'cadre'        => '(IAS)',
				'photo_url'    => '',
				'avatar_color' => 'blue',
			),
			array(
				'id'           => 2,
				'name'         => 'Sh. Vijay Vardhan',
				'designation'  => 'Director',
				'cadre'        => '(IAS)',
				'photo_url'    => '',
				'avatar_color' => 'green',
			),
			array(
				'id'           => 3,
				'name'         => 'Sh. Sumit Khimta',
				'designation'  => 'Director',
				'cadre'        => '(IAS)',
				'photo_url'    => '',
				'avatar_color' => 'peach',
			),
			array(
				'id'           => 4,
				'name'         => 'Sh. Manoj Kumar',
				'designation'  => 'Director',
				'cadre'        => 'IAS',
				'photo_url'    => '',
				'avatar_color' => 'blue',
			),
			array(
				'id'           => 5,
				'name'         => 'Sh. Mohammad Latif',
				'designation'  => 'Non Official Director',
				'cadre'        => '',
				'photo_url'    => '',
				'avatar_color' => 'green',
			),
			array(
				'id'           => 6,
				'name'         => 'Sh. Ashok Messy',
				'designation'  => 'Non Official Director',
				'cadre'        => '',
				'photo_url'    => '',
				'avatar_color' => 'blue',
			),
		);
	} else {
		while ( $members_query->have_posts() ) {
			$members_query->the_post();
			$post_id      = get_the_ID();
			$level        = get_post_meta( $post_id, '_csc_org_level', true );
			$designation  = get_post_meta( $post_id, '_csc_org_designation', true );
			$cadre        = get_post_meta( $post_id, '_csc_org_cadre', true );
			$photo_url    = get_post_meta( $post_id, '_csc_org_photo_url', true );
			$avatar_color = get_post_meta( $post_id, '_csc_org_avatar_color', true );

			if ( empty( $photo_url ) && has_post_thumbnail( $post_id ) ) {
				$photo_url = get_the_post_thumbnail_url( $post_id, 'full' );
			}

			if ( empty( $avatar_color ) ) {
				$avatar_color = 'blue';
			}

			$cat_slugs = array();
			$terms = get_the_terms( $post_id, 'csc_org_category' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$cat_slugs = wp_list_pluck( $terms, 'slug' );
			}

			$member_data = array(
				'id'           => $post_id,
				'name'         => get_the_title(),
				'designation'  => ! empty( $designation ) ? $designation : __( 'Member', 'custom-shortcodes-components' ),
				'cadre'        => $cadre,
				'photo_url'    => $photo_url,
				'avatar_color' => $avatar_color,
				'categories'   => $cat_slugs,
			);

			if ( 'apex' === $level || 'chairman' === $level || 'level_1' === $level ) {
				$apex_members[] = $member_data;
			} else {
				$subordinate_members[] = $member_data;
			}
		}
		wp_reset_postdata();

		// If no apex member was explicitly marked, use the first member as apex
		if ( empty( $apex_members ) && ! empty( $subordinate_members ) ) {
			$apex_members[] = array_shift( $subordinate_members );
		}
	}

	$line_color = esc_attr( $atts['line_color'] );
	$unique_id  = 'csc-org-' . wp_rand( 1000, 9999 );

	ob_start();
	?>
	<div class="csc-org-tree-wrapper" id="<?php echo esc_attr( $unique_id ); ?>" style="--csc-tree-line-color: <?php echo esc_attr( $line_color ); ?>;">

		<?php if ( ! empty( $atts['title'] ) || ! empty( $atts['subtitle'] ) ) : ?>
			<div class="csc-org-tree-header">
				<?php if ( ! empty( $atts['title'] ) ) : ?>
					<h2 class="csc-org-tree-title"><?php echo esc_html( $atts['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $atts['subtitle'] ) ) : ?>
					<p class="csc-org-tree-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $all_categories ) && ! is_wp_error( $all_categories ) && count( $all_categories ) > 1 ) : ?>
			<div class="csc-org-tabs" role="tablist">
				<button type="button" class="csc-org-tab is-active" data-filter="all" role="tab" aria-selected="true">
					<?php esc_html_e( 'All Members', 'custom-shortcodes-components' ); ?>
				</button>
				<?php foreach ( $all_categories as $term ) : ?>
					<button type="button" class="csc-org-tab" data-filter="<?php echo esc_attr( $term->slug ); ?>" role="tab" aria-selected="false">
						<?php echo esc_html( $term->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Main Hierarchy Tree Canvas -->
		<div class="csc-org-tree-canvas">

			<!-- 1. APEX / TOP LEADER LEVEL -->
			<?php if ( ! empty( $apex_members ) ) : ?>
				<div class="csc-org-apex-container">
					<?php foreach ( $apex_members as $apex ) : ?>
						<div class="csc-org-apex-card">
							<?php if ( ! empty( $apex['photo_url'] ) ) : ?>
								<div class="csc-org-apex-portrait">
									<img src="<?php echo esc_url( $apex['photo_url'] ); ?>" alt="<?php echo esc_attr( $apex['name'] ); ?>" />
								</div>
							<?php endif; ?>

							<div class="csc-org-apex-badge">
								<span class="csc-org-apex-name"><?php echo esc_html( $apex['name'] ); ?></span>
								<?php if ( ! empty( $apex['cadre'] ) ) : ?>
									<span class="csc-org-apex-cadre"><?php echo esc_html( $apex['cadre'] ); ?></span>
								<?php endif; ?>
							</div>

							<div class="csc-org-apex-designation-box">
								<?php echo esc_html( $apex['designation'] ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- 2. CONNECTING STEM & BRANCH LINES -->
			<?php if ( ! empty( $subordinate_members ) ) : ?>
				<div class="csc-org-connector-container">
					<!-- Vertical Drop from Apex -->
					<div class="csc-org-vertical-stem"></div>

					<!-- Dynamic Horizontal Branch Bar -->
					<div class="csc-org-horizontal-branch"></div>
				</div>

				<!-- 3. SUBORDINATES / DIRECTORS ROW -->
				<div class="csc-org-subordinates-scroll-wrap">
					<div class="csc-org-subordinates-row" data-count="<?php echo count( $subordinate_members ); ?>">
						<?php
						$color_classes = array( 'blue' => 'csc-avatar-blue', 'green' => 'csc-avatar-green', 'peach' => 'csc-avatar-peach', 'purple' => 'csc-avatar-purple', 'teal' => 'csc-avatar-teal' );
						$auto_colors = array( 'blue', 'green', 'peach', 'blue', 'green', 'blue' );
						$idx = 0;

						foreach ( $subordinate_members as $sub ) :
							$assigned_color = ! empty( $sub['avatar_color'] ) ? $sub['avatar_color'] : $auto_colors[ $idx % count( $auto_colors ) ];
							$color_class = isset( $color_classes[ $assigned_color ] ) ? $color_classes[ $assigned_color ] : 'csc-avatar-blue';
							$sub_categories = ! empty( $sub['categories'] ) ? implode( ' ', (array) $sub['categories'] ) : '';
							$idx++;
						?>
							<div class="csc-org-subordinate-item" data-category="<?php echo esc_attr( $sub_categories ); ?>">
								<!-- Vertical Connector into Card -->
								<div class="csc-org-item-connector"></div>

								<!-- Member Card -->
								<div class="csc-org-member-card">
									<div class="csc-org-avatar-wrap <?php echo esc_attr( $color_class ); ?>">
										<?php if ( ! empty( $sub['photo_url'] ) ) : ?>
											<img src="<?php echo esc_url( $sub['photo_url'] ); ?>" class="csc-org-avatar-img" alt="<?php echo esc_attr( $sub['name'] ); ?>" />
										<?php else : ?>
											<!-- SVG User Outline Icon -->
											<svg class="csc-org-avatar-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
												<circle cx="12" cy="7" r="4"></circle>
											</svg>
										<?php endif; ?>
									</div>

									<div class="csc-org-card-body">
										<h4 class="csc-org-member-name">
											<?php echo esc_html( $sub['name'] ); ?>
											<?php if ( ! empty( $sub['cadre'] ) ) : ?>
												<span class="csc-org-member-cadre"><?php echo esc_html( $sub['cadre'] ); ?></span>
											<?php endif; ?>
										</h4>

										<div class="csc-org-card-divider <?php echo esc_attr( $color_class ); ?>"></div>

										<div class="csc-org-member-designation">
											<?php echo esc_html( $sub['designation'] ); ?>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>
	<?php
	return ob_get_clean();
}
