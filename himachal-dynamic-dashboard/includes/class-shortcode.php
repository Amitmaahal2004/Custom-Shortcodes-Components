<?php
/**
 * Shortcode & AJAX Handlers for Himachal Dynamic Dashboard
 *
 * @package HimachalDynamicDashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class HDD_Shortcode {

    /**
     * Shortcode tag name
     */
    const SHORTCODE_TAG = 'hp_minority_dashboard';

    /**
     * Initialize Shortcode and AJAX hooks
     */
    public static function init() {
        add_shortcode(self::SHORTCODE_TAG, array(__CLASS__, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'register_assets'));

        // AJAX tab switching
        add_action('wp_ajax_hdd_get_category_data', array(__CLASS__, 'ajax_get_category_data'));
        add_action('wp_ajax_nopriv_hdd_get_category_data', array(__CLASS__, 'ajax_get_category_data'));
    }

    /**
     * Register scripts and styles
     */
    public static function register_assets() {
        $version = defined('HDD_VERSION') ? HDD_VERSION : '1.0.0';

        wp_register_style(
            'hdd-dashboard-css',
            HDD_URL . 'assets/css/dashboard.css',
            array(),
            $version
        );

        wp_register_script(
            'hdd-dashboard-js',
            HDD_URL . 'assets/js/dashboard.js',
            array(),
            $version,
            true
        );
    }

    /**
     * Render Shortcode [hp_minority_dashboard]
     */
    public static function render_shortcode($atts = array()) {
        // Enqueue assets when shortcode is rendered
        wp_enqueue_style('hdd-dashboard-css');
        wp_enqueue_script('hdd-dashboard-js');

        $settings = HDD_Admin::get_setting();

        $atts = shortcode_atts(array(
            'category'      => '',
            'districts'     => '12',
            'announcements' => $settings['announcements_count'],
            'downloads'     => $settings['downloads_count'],
        ), $atts, self::SHORTCODE_TAG);

        // 1. Get all Dashboard Categories (Tabs)
        $categories = get_terms(array(
            'taxonomy'   => 'dashboard_category',
            'hide_empty' => false,
            'orderby'    => 'term_order',
            'order'      => 'ASC',
        ));

        if (is_wp_error($categories) || empty($categories)) {
            // Fallback default tabs if none created yet
            $categories = array();
        }

        // Determine active category
        $active_cat = null;
        if (!empty($atts['category'])) {
            foreach ($categories as $cat) {
                if ($cat->slug === $atts['category']) {
                    $active_cat = $cat;
                    break;
                }
            }
        }
        if (!$active_cat && !empty($categories)) {
            $active_cat = $categories[0];
        }

        $active_cat_id = $active_cat ? $active_cat->term_id : 0;
        $active_cat_slug = $active_cat ? $active_cat->slug : '';

        // 2. Query Districts for active category
        $districts_data = self::get_districts_by_category($active_cat_id);

        // 3. Compute Total Population
        $total_population = 0;
        foreach ($districts_data as $d) {
            if (isset($d['val']) && is_numeric($d['val'])) {
                $total_population += (float)$d['val'];
            }
        }

        $suffix = $settings['total_population_suffix'];
        $formatted_total = ($settings['format_indian_number'] === '1')
            ? self::format_indian_number($total_population) . $suffix
            : number_format($total_population) . $suffix;

        // 4. Query Announcements
        $ann_limit = max(1, absint($atts['announcements']));
        $announcements_query = new WP_Query(array(
            'post_type'      => 'announcement',
            'post_status'    => 'publish',
            'posts_per_page' => $ann_limit,
            'meta_key'       => '_hdd_announcement_date',
            'orderby'        => 'meta_value',
            'order'          => 'DESC',
        ));

        // 5. Query Important Downloads
        $dl_limit = max(1, absint($atts['downloads']));
        $downloads_query = new WP_Query(array(
            'post_type'      => 'important_download',
            'post_status'    => 'publish',
            'posts_per_page' => $dl_limit,
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        ));

        // Localize params for Vanilla JS
        $active_index = 0;
        foreach ($categories as $idx => $c) {
            if ($active_cat && $c->term_id === $active_cat->term_id) {
                $active_index = $idx;
                break;
            }
        }

        $js_params = array(
            'ajax_url'        => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('hdd_dashboard_nonce'),
            'active_slug'     => $active_cat_slug,
            'active_index'    => $active_index,
            'format_indian'   => ($settings['format_indian_number'] === '1'),
            'total_suffix'    => $suffix,
            'initial_total'   => $formatted_total,
            'initial_districts' => $districts_data,
        );
        wp_localize_script('hdd-dashboard-js', 'hddDashboardParams', $js_params);

        // Load template
        ob_start();
        $template_file = HDD_PATH . 'templates/dashboard.php';
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo '<p>Dashboard template not found.</p>';
        }
        return ob_get_clean();
    }

    /**
     * Helper to retrieve districts array by category ID
     */
    public static function get_districts_by_category($cat_id = 0) {
        $args = array(
            'post_type'      => 'district_map',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        );

        if ($cat_id > 0) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'dashboard_category',
                    'field'    => 'term_id',
                    'terms'    => $cat_id,
                ),
            );
        }

        $query = new WP_Query($args);
        $districts = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $pid = get_the_ID();

                $en = get_post_meta($pid, '_hdd_district_enabled', true);
                if ($en === '0') {
                    continue;
                }

                $svg_id = get_post_meta($pid, '_hdd_district_svg_id', true);
                $val    = get_post_meta($pid, '_hdd_district_val', true);

                $districts[] = array(
                    'id'            => $pid,
                    'name'          => get_the_title(),
                    'svg_id'        => strtoupper($svg_id),
                    'val'           => $val,
                    'formatted_val' => is_numeric($val) ? number_format((float)$val) : $val,
                    'short_val'     => get_post_meta($pid, '_hdd_district_short_val', true),
                    'dot_x'         => get_post_meta($pid, '_hdd_district_dot_x', true),
                    'dot_y'         => get_post_meta($pid, '_hdd_district_dot_y', true),
                    'line_start_x'  => get_post_meta($pid, '_hdd_district_line_start_x', true),
                    'line_start_y'  => get_post_meta($pid, '_hdd_district_line_start_y', true),
                    'line_end_x'    => get_post_meta($pid, '_hdd_district_line_end_x', true),
                    'line_end_y'    => get_post_meta($pid, '_hdd_district_line_end_y', true),
                    'label_x'       => get_post_meta($pid, '_hdd_district_label_x', true),
                    'label_y'       => get_post_meta($pid, '_hdd_district_label_y', true),
                    'label_align'   => get_post_meta($pid, '_hdd_district_label_align', true),
                    'image_url'     => get_post_meta($pid, '_hdd_district_image_url', true),
                );
            }
            wp_reset_postdata();
        }

        return $districts;
    }

    /**
     * AJAX endpoint to switch tabs and return new dataset
     */
    public static function ajax_get_category_data() {
        check_ajax_referer('hdd_dashboard_nonce', 'nonce');

        $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
        $term = get_term_by('slug', $slug, 'dashboard_category');

        if (!$term || is_wp_error($term)) {
            wp_send_json_error(array('message' => 'Category not found'));
        }

        $settings = HDD_Admin::get_setting();
        $districts = self::get_districts_by_category($term->term_id);

        $total = 0;
        $indexed = array();

        foreach ($districts as $d) {
            if (isset($d['val']) && is_numeric($d['val'])) {
                $total += (float)$d['val'];
            }
            $svg_id = !empty($d['svg_id']) ? strtoupper($d['svg_id']) : '';
            if ($svg_id) {
                $indexed[$svg_id] = $d;
            }
        }

        $suffix = $settings['total_population_suffix'];
        $formatted_total = ($settings['format_indian_number'] === '1')
            ? self::format_indian_number($total) . $suffix
            : number_format($total) . $suffix;

        wp_send_json_success(array(
            'term_id'          => $term->term_id,
            'name'             => $term->name,
            'slug'             => $term->slug,
            'raw_total'        => $total,
            'total_population' => $formatted_total,
            'districts'        => $indexed,
        ));
    }

    /**
     * Indian Number System Formatter (e.g. 6864602 -> 68,64,602)
     */
    public static function format_indian_number($number) {
        $clean = preg_replace('/[^0-9.]/', '', (string)$number);
        if (!is_numeric($clean)) {
            return $number;
        }

        $parts = explode('.', $clean);
        $int   = $parts[0];
        $len   = strlen($int);

        if ($len <= 3) {
            $formatted = $int;
        } else {
            $last3 = substr($int, -3);
            $rest  = substr($int, 0, -3);
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
}
