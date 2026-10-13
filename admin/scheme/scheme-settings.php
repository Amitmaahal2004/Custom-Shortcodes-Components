<?php
/**
 * Admin Settings: Scheme Logo & Category Mapping
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Scheme settings with sensible defaults.
 *
 * @return array
 */
function csc_get_scheme_settings() {
	$defaults = array(
		'logo1_selector'      => '.csc-header-logo-1, .header-logo-1, .site-header .custom-logo-link:first-of-type, .elementor-widget-theme-site-logo:first-of-type a, [data-scheme-logo="1"], [data-scheme-logo="primary"], .logo-1, #logo-1',
		'logo1_category'      => 'demographic-spread-of-minorities',
		'logo1_name'          => __( 'Demographic Spread Of Minorities', 'custom-shortcodes-components' ),
		'logo2_selector'      => '.csc-header-logo-2, .header-logo-2, .site-header .custom-logo-link:last-of-type, .elementor-widget-theme-site-logo:last-of-type a, [data-scheme-logo="2"], [data-scheme-logo="secondary"], .logo-2, #logo-2',
		'logo2_category'      => 'disabilities-in-hp',
		'logo2_name'          => __( 'Disabilities In H.P', 'custom-shortcodes-components' ),
		'active_class'        => 'is-active',
		'enable_color_switch' => 'yes',
		'enable_storage'      => 'yes',
		'enable_url_param'    => 'yes',
		'prevent_navigation'  => 'yes',
	);

	$saved = get_option( 'csc_scheme_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Register Scheme settings with WordPress Settings API.
 */
function csc_register_scheme_settings() {
	register_setting(
		'csc_scheme_settings_group',
		'csc_scheme_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'csc_sanitize_scheme_settings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'csc_register_scheme_settings' );

/**
 * Sanitize Scheme settings before saving.
 *
 * @param array $input Raw input.
 * @return array Sanitized input.
 */
function csc_sanitize_scheme_settings( $input ) {
	$sanitized = array();

	$sanitized['logo1_selector']      = isset( $input['logo1_selector'] ) ? sanitize_text_field( $input['logo1_selector'] ) : '';
	$sanitized['logo1_category']      = isset( $input['logo1_category'] ) ? sanitize_title( $input['logo1_category'] ) : '';
	$sanitized['logo1_name']          = isset( $input['logo1_name'] ) ? sanitize_text_field( $input['logo1_name'] ) : '';

	$sanitized['logo2_selector']      = isset( $input['logo2_selector'] ) ? sanitize_text_field( $input['logo2_selector'] ) : '';
	$sanitized['logo2_category']      = isset( $input['logo2_category'] ) ? sanitize_title( $input['logo2_category'] ) : '';
	$sanitized['logo2_name']          = isset( $input['logo2_name'] ) ? sanitize_text_field( $input['logo2_name'] ) : '';

	$sanitized['active_class']        = isset( $input['active_class'] ) ? sanitize_html_class( $input['active_class'], 'is-active' ) : 'is-active';
	$sanitized['enable_color_switch'] = ! empty( $input['enable_color_switch'] ) && 'yes' === $input['enable_color_switch'] ? 'yes' : 'no';
	$sanitized['enable_storage']      = ! empty( $input['enable_storage'] ) && 'yes' === $input['enable_storage'] ? 'yes' : 'no';
	$sanitized['enable_url_param']    = ! empty( $input['enable_url_param'] ) && 'yes' === $input['enable_url_param'] ? 'yes' : 'no';
	$sanitized['prevent_navigation']  = ! empty( $input['prevent_navigation'] ) && 'yes' === $input['prevent_navigation'] ? 'yes' : 'no';

	return $sanitized;
}

/**
 * Register admin submenu for Scheme settings.
 */
function csc_register_scheme_settings_submenu() {
	add_submenu_page(
		'custom-shortcodes',
		__( 'Scheme Logo & Category Settings', 'custom-shortcodes-components' ),
		__( 'Scheme Settings', 'custom-shortcodes-components' ),
		'manage_options',
		'csc-scheme-settings',
		'csc_render_scheme_settings_page'
	);
}
add_action( 'admin_menu', 'csc_register_scheme_settings_submenu', 25 );

/**
 * Render the Scheme Settings admin page.
 */
function csc_render_scheme_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings   = csc_get_scheme_settings();
	$categories = get_terms(
		array(
			'taxonomy'   => 'csc_scheme_category',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $categories ) ) {
		$categories = array();
	}
	?>
	<div class="wrap" style="max-width: 1000px; margin-top: 20px;">
		<h1 style="font-size: 24px; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
			<span class="dashicons dashicons-admin-generic" style="font-size: 28px; width: 28px; height: 28px; color: #109b48;"></span>
			<?php esc_html_e( 'Scheme Logo & Category Mapping', 'custom-shortcodes-components' ); ?>
		</h1>
		<p style="font-size: 14px; color: #64748b; margin-top: 0; margin-bottom: 24px;">
			<?php esc_html_e( 'Configure the relationship between your two existing website header logos, Scheme categories, and Elementor Global Colors.', 'custom-shortcodes-components' ); ?>
		</p>

		<?php settings_errors( 'csc_scheme_settings_group' ); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'csc_scheme_settings_group' ); ?>

			<!-- INFO BANNER: ZERO DUPLICATE LOGOS -->
			<div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a; border-radius: 6px; padding: 14px 18px; margin-bottom: 24px;">
				<strong style="color: #166534; font-size: 14px;">
					<span class="dashicons dashicons-yes-alt" style="vertical-align: middle; margin-right: 4px; color: #16a34a;"></span>
					<?php esc_html_e( 'Header Integration Active (Uses Existing Header Logos)', 'custom-shortcodes-components' ); ?>
				</strong>
				<p style="margin: 6px 0 0 0; font-size: 13px; color: #15803d; line-height: 1.5;">
					<?php esc_html_e( 'No new logo HTML or duplicates are rendered. The JavaScript automatically attaches to the two existing logo elements in your website header using the CSS selectors configured below.', 'custom-shortcodes-components' ); ?>
				</p>
			</div>

			<!-- SECTION 1: EXISTING LOGO 1 (PRIMARY) -->
			<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
				<div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
					<div style="display: flex; align-items: center; gap: 10px;">
						<span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 14px;">1</span>
						<h2 style="margin: 0; font-size: 17px; color: #0f172a; font-weight: 600;">
							<?php esc_html_e( 'Existing Header Logo 1 (Primary Scheme)', 'custom-shortcodes-components' ); ?>
						</h2>
					</div>
					<span style="background: #e2e8f0; color: #334155; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 12px;">
						<?php esc_html_e( 'Normal Elementor Primary Color', 'custom-shortcodes-components' ); ?>
					</span>
				</div>

				<table class="form-table" role="presentation" style="margin-top: 0;">
					<tr>
						<th scope="row" style="width: 260px;">
							<label for="csc_logo1_category" style="font-weight: 600;">
								<?php esc_html_e( 'Mapped Scheme Category', 'custom-shortcodes-components' ); ?> <span style="color: #dc2626;">*</span>
							</label>
						</th>
						<td>
							<select name="csc_scheme_settings[logo1_category]" id="csc_logo1_category" style="min-width: 280px;">
								<option value=""><?php esc_html_e( '— Show All Published Schemes —', 'custom-shortcodes-components' ); ?></option>
								<?php foreach ( $categories as $cat ) : ?>
									<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $settings['logo1_category'], $cat->slug ); ?>>
										<?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'When Logo 1 is clicked, only schemes assigned to this category will be displayed.', 'custom-shortcodes-components' ); ?>
								<?php if ( empty( $categories ) ) : ?>
									<br><span style="color: #b45309;">
										<?php
										printf(
											/* translators: %s: Link to add category */
											__( 'No categories found. <a href="%s">Create Scheme Categories</a> first.', 'custom-shortcodes-components' ),
											esc_url( admin_url( 'edit-tags.php?taxonomy=csc_scheme_category&post_type=csc_scheme' ) )
										);
										?>
									</span>
								<?php endif; ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="csc_logo1_selector" style="font-weight: 600;">
								<?php esc_html_e( 'Existing Logo 1 CSS Selector', 'custom-shortcodes-components' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="csc_scheme_settings[logo1_selector]" id="csc_logo1_selector" value="<?php echo esc_attr( $settings['logo1_selector'] ); ?>" class="large-text" style="font-family: monospace;" />
							<p class="description">
								<?php esc_html_e( 'CSS selector for Logo 1 in your existing header. You can add class "csc-header-logo-1" or data attribute data-scheme-logo="1" to your Elementor logo widget, or use any existing class/ID.', 'custom-shortcodes-components' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="csc_logo1_name">
								<?php esc_html_e( 'Admin Label / Aria Label', 'custom-shortcodes-components' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="csc_scheme_settings[logo1_name]" id="csc_logo1_name" value="<?php echo esc_attr( $settings['logo1_name'] ); ?>" class="regular-text" />
						</td>
					</tr>
				</table>
			</div>

			<!-- SECTION 2: EXISTING LOGO 2 (SECONDARY) -->
			<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
				<div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
					<div style="display: flex; align-items: center; gap: 10px;">
						<span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #fef3c7; color: #92400e; font-weight: 700; font-size: 14px;">2</span>
						<h2 style="margin: 0; font-size: 17px; color: #0f172a; font-weight: 600;">
							<?php esc_html_e( 'Existing Header Logo 2 (Secondary Scheme)', 'custom-shortcodes-components' ); ?>
						</h2>
					</div>
					<span style="background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 12px;">
						<?php esc_html_e( 'Switches Primary → Elementor Secondary Color', 'custom-shortcodes-components' ); ?>
					</span>
				</div>

				<table class="form-table" role="presentation" style="margin-top: 0;">
					<tr>
						<th scope="row" style="width: 260px;">
							<label for="csc_logo2_category" style="font-weight: 600;">
								<?php esc_html_e( 'Mapped Scheme Category', 'custom-shortcodes-components' ); ?> <span style="color: #dc2626;">*</span>
							</label>
						</th>
						<td>
							<select name="csc_scheme_settings[logo2_category]" id="csc_logo2_category" style="min-width: 280px;">
								<option value=""><?php esc_html_e( '— Select Category 2 —', 'custom-shortcodes-components' ); ?></option>
								<?php foreach ( $categories as $cat ) : ?>
									<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $settings['logo2_category'], $cat->slug ); ?>>
										<?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'When Logo 2 is clicked, only schemes assigned to this category will be displayed.', 'custom-shortcodes-components' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="csc_logo2_selector" style="font-weight: 600;">
								<?php esc_html_e( 'Existing Logo 2 CSS Selector', 'custom-shortcodes-components' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="csc_scheme_settings[logo2_selector]" id="csc_logo2_selector" value="<?php echo esc_attr( $settings['logo2_selector'] ); ?>" class="large-text" style="font-family: monospace;" />
							<p class="description">
								<?php esc_html_e( 'CSS selector for Logo 2 in your existing header. You can add class "csc-header-logo-2" or data attribute data-scheme-logo="2" to your Elementor logo widget, or use any existing class/ID.', 'custom-shortcodes-components' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="csc_logo2_name">
								<?php esc_html_e( 'Admin Label / Aria Label', 'custom-shortcodes-components' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="csc_scheme_settings[logo2_name]" id="csc_logo2_name" value="<?php echo esc_attr( $settings['logo2_name'] ); ?>" class="regular-text" />
						</td>
					</tr>
				</table>
			</div>

			<!-- SECTION 3: ELEMENTOR GLOBAL COLOR SWITCHING & OPTIONS -->
			<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
				<h2 style="margin-top: 0; font-size: 17px; color: #0f172a; font-weight: 600; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
					<?php esc_html_e( 'Elementor Global Colors & Behavior Settings', 'custom-shortcodes-components' ); ?>
				</h2>

				<table class="form-table" role="presentation" style="margin-top: 0;">
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Elementor Global Color Switching', 'custom-shortcodes-components' ); ?>
						</th>
						<td>
							<label style="font-weight: 500;">
								<input type="checkbox" name="csc_scheme_settings[enable_color_switch]" value="yes" <?php checked( $settings['enable_color_switch'], 'yes' ); ?> />
								<?php esc_html_e( 'Enable automatic dynamic color switching (var(--e-global-color-primary) visually adopts var(--e-global-color-secondary) when Logo 2 is active)', 'custom-shortcodes-components' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Non-destructive, frontend CSS Custom Property switching. Does NOT alter Elementor database settings.', 'custom-shortcodes-components' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'State Persistence (localStorage)', 'custom-shortcodes-components' ); ?>
						</th>
						<td>
							<label style="font-weight: 500;">
								<input type="checkbox" name="csc_scheme_settings[enable_storage]" value="yes" <?php checked( $settings['enable_storage'], 'yes' ); ?> />
								<?php esc_html_e( 'Remember the user\'s active scheme/logo selection across page refreshes and navigation', 'custom-shortcodes-components' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'URL Parameter Sync', 'custom-shortcodes-components' ); ?>
						</th>
						<td>
							<label style="font-weight: 500;">
								<input type="checkbox" name="csc_scheme_settings[enable_url_param]" value="yes" <?php checked( $settings['enable_url_param'], 'yes' ); ?> />
								<?php esc_html_e( 'Support URL query parameters (?scheme_category=slug) for direct deep linking', 'custom-shortcodes-components' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'In-Place AJAX Filtering', 'custom-shortcodes-components' ); ?>
						</th>
						<td>
							<label style="font-weight: 500;">
								<input type="checkbox" name="csc_scheme_settings[prevent_navigation]" value="yes" <?php checked( $settings['prevent_navigation'], 'yes' ); ?> />
								<?php esc_html_e( 'If current page contains a Schemes slider, prevent full-page navigation on logo click and filter schemes in-place via smooth AJAX', 'custom-shortcodes-components' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="csc_active_class">
								<?php esc_html_e( 'Logo Active CSS Class', 'custom-shortcodes-components' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="csc_scheme_settings[active_class]" id="csc_active_class" value="<?php echo esc_attr( $settings['active_class'] ); ?>" class="regular-text" style="font-family: monospace;" />
							<p class="description">
								<?php esc_html_e( 'Class added to the currently active header logo (default: is-active).', 'custom-shortcodes-components' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<?php submit_button( __( 'Save Scheme Settings', 'custom-shortcodes-components' ), 'primary button-hero' ); ?>
		</form>

		<!-- SECTION 4: SHORTCODE REFERENCE -->
		<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-top: 32px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; font-size: 17px; color: #0f172a; font-weight: 600; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
				<span class="dashicons dashicons-shortcode" style="vertical-align: middle; margin-right: 4px; color: #2563eb;"></span>
				<?php esc_html_e( 'Schemes & Services Shortcodes Quick Reference', 'custom-shortcodes-components' ); ?>
			</h2>

			<table class="widefat striped" style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
				<thead>
					<tr>
						<th style="width: 45%;"><?php esc_html_e( 'Shortcode', 'custom-shortcodes-components' ); ?></th>
						<th><?php esc_html_e( 'Description / Usage', 'custom-shortcodes-components' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>[csc_schemes]</code></td>
						<td><?php esc_html_e( 'Responsive Swiper Slider with dynamic logo category filtering.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[schemes_services_grid]</code></td>
						<td><?php esc_html_e( 'Grid View displaying all published schemes with pagination.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[schemes_services_grid category="cat-1"]</code></td>
						<td><?php esc_html_e( 'Grid View displaying only schemes from Category 1 with pagination.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[schemes_services_grid category="cat-2"]</code></td>
						<td><?php esc_html_e( 'Grid View displaying only schemes from Category 2 with pagination.', 'custom-shortcodes-components' ); ?></td>
					</tr>
					<tr>
						<td><code>[schemes_services_grid category="slug-name" columns="3" posts_per_page="6"]</code></td>
						<td><?php esc_html_e( 'Grid View for specific category slug with custom 3 columns and 6 items per page.', 'custom-shortcodes-components' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
