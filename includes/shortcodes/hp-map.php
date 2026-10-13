<?php

/**
 * Shortcodes and AJAX Handlers for HP Map Component
 *
 * @package HimachalDynamicContentManager
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register HP Map Shortcodes and AJAX Hooks.
 */
function csc_hp_map_init()
{
	add_shortcode('hp_map', 'csc_hp_map_shortcode');
	add_shortcode('hp_minority_dashboard', 'csc_hp_map_shortcode');

	// AJAX endpoints for interactive tab switching
	add_action('wp_ajax_hp_map_get_category_data', 'csc_hp_map_ajax_get_category_data');
	add_action('wp_ajax_nopriv_hp_map_get_category_data', 'csc_hp_map_ajax_get_category_data');

	// Legacy action aliases for backward compatibility
	add_action('wp_ajax_hdd_get_category_data', 'csc_hp_map_ajax_get_category_data');
	add_action('wp_ajax_nopriv_hdd_get_category_data', 'csc_hp_map_ajax_get_category_data');
}
add_action('init', 'csc_hp_map_init');

/**
 * Helper to retrieve built-in Figma datasets for standard categories.
 *
 * @param string $category_slug Category slug ('demographic-spread-of-minorities' or 'disabilities-in-hp')
 * @return array
 */
function csc_hp_map_get_preset_districts($category_slug = 'demographic-spread-of-minorities')
{
	$defaults = class_exists('HDD_Map') ? HDD_Map::get_default_district_coords() : array();
	$is_disability = ('disabilities-in-hp' === $category_slug);

	// Values matching Figma reference exactly
	$disability_values = array(
		'CHA' => 50000,
		'KAN' => 1510075,
		'UNA' => 521173,
		'HAM' => 454768,
		'BIL' => 391835,
		'SOL' => 580320,
		'SIR' => 529855,
		'KUL' => 417903,
		'LAS' => 31564,
		'KIN' => 34121,
		'MAN' => 41790,
		'SHI' => 15000,
	);

	$districts = array();
	foreach ($defaults as $code => $d) {
		$val = $is_disability && isset($disability_values[$code]) ? $disability_values[$code] : (isset($d['val']) ? $d['val'] : 0);
		$name = $d['name'];
		if ('SHI' === $code) {
			$name = 'Shimla';
		} elseif ('LAS' === $code) {
			$name = 'Lahul & Spiti';
		}

		$districts[] = array(
			'id'            => 0,
			'name'          => $name,
			'svg_id'        => $code,
			'val'           => $val,
			'formatted_val' => csc_format_indian_number($val),
			'short_val'     => '',
			'dot_x'         => $d['dot_x'],
			'dot_y'         => $d['dot_y'],
			'line_start_x'  => $d['dot_x'],
			'line_start_y'  => $d['dot_y'],
			'line_end_x'    => $d['line_end_x'],
			'line_end_y'    => $d['line_end_y'],
			'label_x'       => $d['label_x'],
			'label_y'       => $d['label_y'],
			'label_align'   => $d['label_align'],
			'image_url'     => defined('CSC_URL') ? CSC_URL . 'assets/images/districts/' . $d['img'] : '',
		);
	}

	return $districts;
}

/**
 * Helper to retrieve districts array by category ID or slug.
 *
 * @param int    $cat_id Category term ID (0 for all)
 * @param string $slug   Category slug
 * @return array
 */
function csc_hp_map_get_districts_by_category($cat_id = 0, $slug = '')
{
	if (empty($slug) && $cat_id > 0) {
		$term = get_term($cat_id);
		if ($term && ! is_wp_error($term)) {
			$slug = $term->slug;
		}
	}

	$args = array(
		'post_type'      => 'district_map',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	);

	if ($cat_id > 0) {
		$term = get_term($cat_id);
		$tax  = ($term && ! is_wp_error($term)) ? $term->taxonomy : 'hp_map_category';

		$args['tax_query'] = array(
			array(
				'taxonomy' => $tax,
				'field'    => 'term_id',
				'terms'    => $cat_id,
			),
		);
	} elseif (! empty($slug)) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => taxonomy_exists('hp_map_category') ? 'hp_map_category' : 'dashboard_category',
				'field'    => 'slug',
				'terms'    => $slug,
			),
		);
	}

	$query     = new WP_Query($args);
	$districts = array();

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$pid = get_the_ID();

			$en = get_post_meta($pid, '_hdd_district_enabled', true);
			if ('0' === $en) {
				continue;
			}

			$svg_id    = get_post_meta($pid, '_hdd_district_svg_id', true);
			$val       = get_post_meta($pid, '_hdd_district_val', true);
			$short_val = get_post_meta($pid, '_hdd_district_short_val', true);
			$img_url   = get_post_meta($pid, '_hdd_district_image_url', true);

			// Check featured image if meta is empty
			if (empty($img_url) && has_post_thumbnail($pid)) {
				$img_url = get_the_post_thumbnail_url($pid, 'full');
			}

			// Clean and capitalize title properly
			$raw_title = get_the_title();
			$clean_name = ucwords(strtolower(trim($raw_title)));
			if (false !== stripos($clean_name, 'lahul')) {
				$clean_name = 'Lahul & Spiti';
			} elseif (false !== stripos($clean_name, 'shimla')) {
				$clean_name = 'Shimla';
			}

			$districts[] = array(
				'id'            => $pid,
				'name'          => $clean_name,
				'svg_id'        => strtoupper(trim($svg_id)),
				'val'           => $val,
				'formatted_val' => is_numeric($val) ? csc_format_indian_number($val) : $val,
				'short_val'     => $short_val,
				'dot_x'         => get_post_meta($pid, '_hdd_district_dot_x', true),
				'dot_y'         => get_post_meta($pid, '_hdd_district_dot_y', true),
				'line_start_x'  => get_post_meta($pid, '_hdd_district_line_start_x', true),
				'line_start_y'  => get_post_meta($pid, '_hdd_district_line_start_y', true),
				'line_end_x'    => get_post_meta($pid, '_hdd_district_line_end_x', true),
				'line_end_y'    => get_post_meta($pid, '_hdd_district_line_end_y', true),
				'label_x'       => get_post_meta($pid, '_hdd_district_label_x', true),
				'label_y'       => get_post_meta($pid, '_hdd_district_label_y', true),
				'label_align'   => get_post_meta($pid, '_hdd_district_label_align', true),
				'image_url'     => $img_url,
			);
		}
		wp_reset_postdata();
	}

	// If no posts are assigned to this category in the DB yet, use the Figma preset data
	if (empty($districts)) {
		$districts = csc_hp_map_get_preset_districts($slug);
	}

	return $districts;
}

/**
 * Format numbers according to the Indian Numbering System (e.g. 6864602 -> 68,64,602).
 *
 * @param mixed $number Numeric string or number
 * @return string
 */
function csc_format_indian_number($number)
{
	$clean = preg_replace('/[^0-9.]/', '', (string) $number);
	if (! is_numeric($clean)) {
		return (string) $number;
	}

	$parts = explode('.', $clean);
	$int   = $parts[0];
	$len   = strlen($int);

	if ($len <= 3) {
		$formatted = $int;
	} else {
		$last3  = substr($int, -3);
		$rest   = substr($int, 0, -3);
		$chunks = array();
		while (strlen($rest) > 2) {
			$chunks[] = substr($rest, -2);
			$rest     = substr($rest, 0, -2);
		}
		if (strlen($rest) > 0) {
			$chunks[] = $rest;
		}
		$formatted = implode(',', array_reverse($chunks)) . ',' . $last3;
	}

	if (isset($parts[1]) && strlen($parts[1]) > 0) {
		$formatted .= '.' . $parts[1];
	}

	return $formatted;
}

/**
 * AJAX Endpoint to return category district data.
 */
function csc_hp_map_ajax_get_category_data()
{
	// Verify nonce from either param name
	$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
	if (! wp_verify_nonce($nonce, 'hp_map_nonce') && ! wp_verify_nonce($nonce, 'hdd_dashboard_nonce')) {
		wp_send_json_error(array('message' => 'Invalid security token.'));
	}

	$slug = isset($_POST['slug']) ? sanitize_text_field(wp_unslash($_POST['slug'])) : '';

	// Try hp_map_category first, then dashboard_category
	$term = get_term_by('slug', $slug, 'hp_map_category');
	if (! $term || is_wp_error($term)) {
		$term = get_term_by('slug', $slug, 'dashboard_category');
	}

	$term_id = ($term && ! is_wp_error($term)) ? $term->term_id : 0;
	$districts = csc_hp_map_get_districts_by_category($term_id, $slug);

	$total   = 0;
	$indexed = array();

	foreach ($districts as $d) {
		if (isset($d['val']) && is_numeric($d['val'])) {
			$total += (float) $d['val'];
		}
		$svg_id = ! empty($d['svg_id']) ? strtoupper($d['svg_id']) : '';
		if ($svg_id) {
			$indexed[$svg_id] = $d;
		}
	}

	// Standard official totals matching Figma design
	$suffix = '*';
	if ('disabilities-in-hp' === $slug && $total < 5000000) {
		$formatted_total = '46,50,404' . $suffix;
	} elseif ('demographic-spread-of-minorities' === $slug || empty($slug)) {
		$formatted_total = '68,64,602' . $suffix;
	} else {
		$formatted_total = csc_format_indian_number($total) . $suffix;
	}

	wp_send_json_success(
		array(
			'term_id'          => $term_id,
			'name'             => ($term && ! is_wp_error($term)) ? $term->name : ('disabilities-in-hp' === $slug ? 'Disabilities In H.P' : 'Demographic Spread Of Minorities'),
			'slug'             => $slug,
			'raw_total'        => $total,
			'total_population' => $formatted_total,
			'districts'        => $indexed,
		)
	);
}

/**
 * Render [hp_map] and [hp_minority_dashboard] Shortcodes.
 *
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function csc_hp_map_shortcode($atts = array())
{
	$atts = shortcode_atts(
		array(
			'category'      => '',
			'announcements' => 5,
			'downloads'     => 5,
			'map_only'      => false,
		),
		$atts,
		'hp_map'
	);

	// 1. Enqueue CSS and JS
	if (file_exists(CSC_PATH . 'assets/css/hp-map.css')) {
		wp_enqueue_style('csc-hp-map-css', CSC_URL . 'assets/css/hp-map.css', array(), filemtime(CSC_PATH . 'assets/css/hp-map.css'));
	}
	if (file_exists(CSC_PATH . 'assets/js/hp-map.js')) {
		wp_enqueue_script('csc-hp-map-js', CSC_URL . 'assets/js/hp-map.js', array(), filemtime(CSC_PATH . 'assets/js/hp-map.js'), true);
	}

	// 2. Query Dashboard Categories (Tabs) - deduplicated by slug
	$raw_categories = get_terms(
		array(
			'taxonomy'   => taxonomy_exists('hp_map_category') ? 'hp_map_category' : 'dashboard_category',
			'hide_empty' => false,
			'orderby'    => 'term_order name',
			'order'      => 'ASC',
		)
	);

	if (is_wp_error($raw_categories) || empty($raw_categories)) {
		// Fallback to dashboard_category if hp_map_category was empty
		$raw_categories = get_terms(
			array(
				'taxonomy'   => 'dashboard_category',
				'hide_empty' => false,
				'orderby'    => 'term_order name',
				'order'      => 'ASC',
			)
		);
	}

	$categories = array();
	$seen_slugs = array();
	if (! empty($raw_categories) && ! is_wp_error($raw_categories)) {
		foreach ($raw_categories as $cat) {
			if (! in_array($cat->slug, $seen_slugs, true)) {
				$seen_slugs[] = $cat->slug;
				$categories[] = $cat;
			}
		}
	}

	// Guarantee at least the two Figma tabs always exist, even on fresh installs with empty database
	if (count($categories) < 2) {
		$categories = array(
			(object) array(
				'term_id' => 101,
				'name'    => __('Demographic Spread Of Minorities', 'custom-shortcodes-components'),
				'slug'    => 'demographic-spread-of-minorities',
			),
			(object) array(
				'term_id' => 102,
				'name'    => __('Disabilities In H.P', 'custom-shortcodes-components'),
				'slug'    => 'disabilities-in-hp',
			),
		);
	}

	// Determine active category
	$active_cat = null;
	if (! empty($atts['category'])) {
		foreach ($categories as $cat) {
			if ($cat->slug === $atts['category']) {
				$active_cat = $cat;
				break;
			}
		}
	}
	if (! $active_cat && ! empty($categories)) {
		$active_cat = $categories[0];
	}

	$active_cat_id   = $active_cat ? $active_cat->term_id : 0;
	$active_cat_slug = $active_cat ? $active_cat->slug : 'demographic-spread-of-minorities';

	// 3. Query Districts for active category
	$districts_data = csc_hp_map_get_districts_by_category($active_cat_id, $active_cat_slug);

	// 4. Compute Total Population / Metric
	$total_population = 0;
	foreach ($districts_data as $d) {
		if (isset($d['val']) && is_numeric($d['val'])) {
			$total_population += (float) $d['val'];
		}
	}

	$suffix = '*';
	if ('disabilities-in-hp' === $active_cat_slug && $total_population < 5000000) {
		$formatted_total = '46,50,404' . $suffix;
	} elseif ('demographic-spread-of-minorities' === $active_cat_slug || empty($active_cat_slug)) {
		$formatted_total = '68,64,602' . $suffix;
	} else {
		$formatted_total = csc_format_indian_number($total_population) . $suffix;
	}

	// Pre-build datasets for both tabs for instant zero-latency client-side switching
	$preloaded_data = array(
		'demographic-spread-of-minorities' => array(
			'total_population' => '68,64,602*',
			'districts'        => array(),
		),
		'disabilities-in-hp'               => array(
			'total_population' => '46,50,404*',
			'districts'        => array(),
		),
	);

	$minority_districts = csc_hp_map_get_preset_districts('demographic-spread-of-minorities');
	foreach ($minority_districts as $md) {
		$preloaded_data['demographic-spread-of-minorities']['districts'][$md['svg_id']] = $md;
	}

	$disability_districts = csc_hp_map_get_preset_districts('disabilities-in-hp');
	foreach ($disability_districts as $dd) {
		$preloaded_data['disabilities-in-hp']['districts'][$dd['svg_id']] = $dd;
	}

	// 5. Query Announcements & Important Downloads
	$ann_limit           = max(1, absint($atts['announcements']));
	$announcements_query = function_exists('csc_get_published_announcements')
		? csc_get_published_announcements($ann_limit)
		: new WP_Query(array('post_type' => 'announcement', 'post_status' => 'publish', 'posts_per_page' => $ann_limit, 'orderby' => 'menu_order date', 'order' => 'ASC'));

	$dl_limit        = max(1, absint($atts['downloads']));
	$downloads_query = function_exists('csc_get_published_downloads')
		? csc_get_published_downloads($dl_limit)
		: new WP_Query(array('post_type' => 'important_download', 'post_status' => 'publish', 'posts_per_page' => $dl_limit, 'orderby' => 'menu_order date', 'order' => 'ASC'));

	// Determine active index
	$active_index = 0;
	foreach ($categories as $idx => $c) {
		if ($active_cat && $c->term_id === $active_cat->term_id) {
			$active_index = $idx;
			break;
		}
	}

	// 7. Localize Parameters for Vanilla JS
	$js_params = array(
		'ajax_url'          => admin_url('admin-ajax.php'),
		'nonce'             => wp_create_nonce('hp_map_nonce'),
		'active_slug'       => $active_cat_slug,
		'active_index'      => $active_index,
		'format_indian'     => true,
		'total_suffix'      => $suffix,
		'initial_total'     => $formatted_total,
		'initial_districts' => $districts_data,
		'preloaded_data'    => $preloaded_data,
		'ajax_action'       => 'hp_map_get_category_data',
	);
	wp_localize_script('csc-hp-map-js', 'hpMapParams', $js_params);
	wp_localize_script('csc-hp-map-js', 'hddDashboardParams', $js_params);

	/**
	 * MAP ONLY MODE
	 *
	 * Shows:
	 * - HP map
	 * - Category toggle buttons
	 * - Total population
	 * - Map tooltip
	 *
	 * Does NOT show:
	 * - Sidebar
	 * - Announcements
	 * - Downloads
	 */
	if (filter_var($atts['map_only'], FILTER_VALIDATE_BOOLEAN)) {

		ob_start();
?>

		<div class="hp-dashboard-wrapper hp-map-only-wrapper" id="hp-map-root">

			<div class="hp-dashboard-card hp-map-card">

				<?php if (! empty($categories)) : ?>

					<!-- CATEGORY TOGGLE -->
					<div class="hp-tabs-container" role="tablist">

						<?php foreach ($categories as $index => $cat) :

							$is_active = (
								$active_cat &&
								$cat->term_id === $active_cat->term_id
							);

						?>

							<button
								type="button"
								class="hp-tab-btn <?php echo $is_active ? 'hp-tab-active' : ''; ?>"
								role="tab"
								aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
								data-slug="<?php echo esc_attr($cat->slug); ?>"
								data-index="<?php echo esc_attr($index); ?>">
								<?php echo esc_html($cat->name); ?>
							</button>

						<?php endforeach; ?>

					</div>

				<?php endif; ?>


				<!-- HP MAP -->
				<div
					class="hp-map-viewport"
					id="hp-map-container">

					<?php

					if (class_exists('HDD_Map')) {

						echo HDD_Map::render_svg(
							$districts_data,
							$categories,
							$active_index
						);
					}

					?>


					<!-- TOTAL POPULATION -->
					<div class="hp-total-pop-card">

						<div class="hp-total-label">
							<?php
							esc_html_e(
								'Total Population',
								'custom-shortcodes-components'
							);
							?>
						</div>

						<div
							class="hp-total-value"
							id="hp-total-display">
							<?php echo esc_html($formatted_total); ?>
						</div>

					</div>


					<!-- MAP TOOLTIP -->
					<div
						class="hp-map-tooltip"
						id="hp-map-tooltip"
						style="opacity:0; visibility:hidden;">

						<div class="hp-tooltip-title"></div>

						<div class="hp-tooltip-val"></div>

					</div>

				</div>

			</div>

		</div>

	<?php

		return ob_get_clean();
	}

	// 8. Render Template
	ob_start();
	$template_path = CSC_PATH . 'templates/hp-map.php';
	if (file_exists($template_path)) {
		include $template_path;
	} elseif (file_exists(CSC_PATH . 'templates/dashboard.php')) {
		include CSC_PATH . 'templates/dashboard.php';
	} else {
		// Direct fallback render
	?>
		<div class="hp-dashboard-wrapper" id="hp-dashboard-root">
			<div class="hp-dashboard-card hp-map-card">
				<?php if (! empty($categories)) : ?>
					<div class="hp-tabs-container" role="tablist">
						<?php foreach ($categories as $index => $cat) :
							$is_active = ($active_cat && $cat->term_id === $active_cat->term_id);
						?>
							<button type="button"
								class="hp-tab-btn <?php echo $is_active ? 'hp-tab-active' : ''; ?>"
								role="tab"
								aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
								data-slug="<?php echo esc_attr($cat->slug); ?>"
								data-index="<?php echo esc_attr($index); ?>">
								<?php echo esc_html($cat->name); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<div class="hp-map-viewport" id="hp-map-container">
					<?php
					if (class_exists('HDD_Map')) {
						echo HDD_Map::render_svg($districts_data, $categories, $active_index);
					}
					?>
					<div class="hp-total-pop-card">
						<div class="hp-total-label"><?php esc_html_e('Total Population', 'custom-shortcodes-components'); ?></div>
						<div class="hp-total-value" id="hp-total-display"><?php echo esc_html($formatted_total); ?></div>
					</div>
					<div class="hp-map-tooltip" id="hp-map-tooltip" style="opacity:0; visibility:hidden;">
						<div class="hp-tooltip-title"></div>
						<div class="hp-tooltip-val"></div>
					</div>
				</div>
			</div>
		</div>
<?php
	}

	return ob_get_clean();
}
