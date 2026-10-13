<?php
/**
 * Admin Menus, Settings & Demo Data Importer for Himachal Dynamic Dashboard
 *
 * @package HimachalDynamicDashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class HDD_Admin {

    /**
     * Option group and key
     */
    const OPTION_KEY = 'hdd_settings';

    /**
     * Initialize Admin Menus and Settings
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_admin_menus'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_post_hdd_import_demo_data', array(__CLASS__, 'handle_demo_data_import'));
    }

    /**
     * Register Admin Menus
     */
    public static function register_admin_menus() {
        // Top-level menu: Himachal Dashboard
        add_menu_page(
            __('Himachal Dashboard', 'himachal-dynamic-dashboard'),
            __('Himachal Dashboard', 'himachal-dynamic-dashboard'),
            'manage_options',
            'himachal-dashboard',
            array(__CLASS__, 'render_overview_page'),
            'dashicons-chart-area',
            26
        );

        // Submenu: Dashboard Data (All Districts)
        add_submenu_page(
            'himachal-dashboard',
            __('District Data', 'himachal-dynamic-dashboard'),
            __('District Data', 'himachal-dynamic-dashboard'),
            'manage_options',
            'edit.php?post_type=district_map'
        );

        // Submenu: Add New District
        add_submenu_page(
            'himachal-dashboard',
            __('Add New District', 'himachal-dynamic-dashboard'),
            __('Add District', 'himachal-dynamic-dashboard'),
            'manage_options',
            'post-new.php?post_type=district_map'
        );

        // Submenu: Dashboard Categories / Tabs
        add_submenu_page(
            'himachal-dashboard',
            __('Dashboard Tabs', 'himachal-dynamic-dashboard'),
            __('Dashboard Tabs', 'himachal-dynamic-dashboard'),
            'manage_options',
            'edit-tags.php?taxonomy=dashboard_category&post_type=district_map'
        );

        // Submenu: Announcements
        add_submenu_page(
            'himachal-dashboard',
            __('Announcements', 'himachal-dynamic-dashboard'),
            __('Announcements', 'himachal-dynamic-dashboard'),
            'manage_options',
            'edit.php?post_type=announcement'
        );

        // Submenu: Important Downloads
        add_submenu_page(
            'himachal-dashboard',
            __('Important Downloads', 'himachal-dynamic-dashboard'),
            __('Important Downloads', 'himachal-dynamic-dashboard'),
            'manage_options',
            'edit.php?post_type=important_download'
        );

        // Submenu: Settings
        add_submenu_page(
            'himachal-dashboard',
            __('Dashboard Settings', 'himachal-dynamic-dashboard'),
            __('Settings & Demo Data', 'himachal-dynamic-dashboard'),
            'manage_options',
            'hp-dashboard-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    /**
     * Register Settings
     */
    public static function register_settings() {
        register_setting('hdd_settings_group', self::OPTION_KEY, array(
            'sanitize_callback' => array(__CLASS__, 'sanitize_settings'),
        ));
    }

    /**
     * Sanitize settings input
     */
    public static function sanitize_settings($input) {
        $clean = array();
        $clean['announcements_archive_url'] = isset($input['announcements_archive_url']) ? esc_url_raw($input['announcements_archive_url']) : '';
        $clean['downloads_archive_url']     = isset($input['downloads_archive_url']) ? esc_url_raw($input['downloads_archive_url']) : '';
        $clean['announcements_count']       = isset($input['announcements_count']) ? max(1, absint($input['announcements_count'])) : 2;
        $clean['downloads_count']           = isset($input['downloads_count']) ? max(1, absint($input['downloads_count'])) : 4;
        $clean['total_population_label']    = isset($input['total_population_label']) ? sanitize_text_field($input['total_population_label']) : 'Total Population';
        $clean['total_population_suffix']   = isset($input['total_population_suffix']) ? sanitize_text_field($input['total_population_suffix']) : '*';
        $clean['format_indian_number']      = isset($input['format_indian_number']) ? '1' : '0';
        return $clean;
    }

    /**
     * Get single setting or all settings
     */
    public static function get_setting($key = null, $default = null) {
        $defaults = array(
            'announcements_archive_url' => '',
            'downloads_archive_url'     => '',
            'announcements_count'       => 2,
            'downloads_count'           => 4,
            'total_population_label'    => 'Total Population',
            'total_population_suffix'   => '*',
            'format_indian_number'      => '1',
        );

        $options = get_option(self::OPTION_KEY, array());
        $settings = wp_parse_args($options, $defaults);

        if ($key !== null) {
            return isset($settings[$key]) ? $settings[$key] : $default;
        }

        return $settings;
    }

    /**
     * Render Overview Page
     */
    public static function render_overview_page() {
        $settings = self::get_setting();
        ?>
        <div class="wrap">
            <h1 style="color:#0b3b60; display:flex; align-items:center; gap:10px;">
                <span class="dashicons dashicons-chart-area" style="font-size:32px; width:32px; height:32px;"></span>
                <?php _e('Himachal Dynamic Dashboard', 'himachal-dynamic-dashboard'); ?>
            </h1>
            <p class="description" style="font-size:14px;">
                <?php _e('Manage interactive SVG district map data, tabs, announcements, and important downloads.', 'himachal-dynamic-dashboard'); ?>
            </p>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-top:20px;">
                <div>
                    <!-- Quick Stats -->
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:15px; margin-bottom:20px;">
                        <?php
                        $dist_count = wp_count_posts('district_map')->publish;
                        $ann_count  = wp_count_posts('announcement')->publish;
                        $dl_count   = wp_count_posts('important_download')->publish;
                        ?>
                        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Districts Configured</div>
                            <div style="color:#0b3b60; font-size:28px; font-weight:800; margin-top:5px;"><?php echo esc_html($dist_count); ?></div>
                            <a href="<?php echo admin_url('edit.php?post_type=district_map'); ?>" style="font-size:12px; color:#1a56db; text-decoration:none;">Manage Districts &rarr;</a>
                        </div>
                        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Announcements</div>
                            <div style="color:#107c41; font-size:28px; font-weight:800; margin-top:5px;"><?php echo esc_html($ann_count); ?></div>
                            <a href="<?php echo admin_url('edit.php?post_type=announcement'); ?>" style="font-size:12px; color:#1a56db; text-decoration:none;">Manage Announcements &rarr;</a>
                        </div>
                        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Important Downloads</div>
                            <div style="color:#dc2626; font-size:28px; font-weight:800; margin-top:5px;"><?php echo esc_html($dl_count); ?></div>
                            <a href="<?php echo admin_url('edit.php?post_type=important_download'); ?>" style="font-size:12px; color:#1a56db; text-decoration:none;">Manage Downloads &rarr;</a>
                        </div>
                    </div>

                    <!-- Shortcode Cheat Sheet -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                        <h2 style="margin-top:0; color:#0b3b60; font-size:18px;">
                            <span class="dashicons dashicons-shortcode"></span> <?php _e('Shortcode Usage', 'himachal-dynamic-dashboard'); ?>
                        </h2>
                        <p><?php _e('Embed the dynamic dashboard anywhere using the standard shortcode:', 'himachal-dynamic-dashboard'); ?></p>
                        
                        <div style="background:#f1f5f9; padding:12px 16px; border-radius:6px; font-family:monospace; font-size:15px; color:#0f172a; border-left:4px solid #0b3b60; margin:15px 0;">
                            [hp_minority_dashboard]
                        </div>

                        <p><strong><?php _e('Optional Shortcode Attributes:', 'himachal-dynamic-dashboard'); ?></strong></p>
                        <table class="widefat striped" style="border-radius:6px; overflow:hidden;">
                            <thead>
                                <tr>
                                    <th><?php _e('Attribute', 'himachal-dynamic-dashboard'); ?></th>
                                    <th><?php _e('Default', 'himachal-dynamic-dashboard'); ?></th>
                                    <th><?php _e('Description', 'himachal-dynamic-dashboard'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>category</code></td>
                                    <td><em>(First Tab)</em></td>
                                    <td><?php _e('Category slug to display initially (e.g. <code>demographic-spread-of-minorities</code>).', 'himachal-dynamic-dashboard'); ?></td>
                                </tr>
                                <tr>
                                    <td><code>announcements</code></td>
                                    <td><code>2</code></td>
                                    <td><?php _e('Number of latest announcements to display in the right column card.', 'himachal-dynamic-dashboard'); ?></td>
                                </tr>
                                <tr>
                                    <td><code>downloads</code></td>
                                    <td><code>4</code></td>
                                    <td><?php _e('Number of important downloads to display in the right column card.', 'himachal-dynamic-dashboard'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right sidebar: Demo Importer -->
                <div>
                    <div style="background:#f8fafc; border:2px dashed #0b3b60; border-radius:8px; padding:20px;">
                        <h3 style="margin-top:0; color:#0b3b60;">
                            <span class="dashicons dashicons-cloud-upload"></span> <?php _e('1-Click Reference Importer', 'himachal-dynamic-dashboard'); ?>
                        </h3>
                        <p style="font-size:13px; color:#475569;">
                            <?php _e('Automatically populate all 12 Himachal Pradesh districts with coordinates, scenic photo textures, 2 announcements with "New" badges, and 4 downloads matching the exact design specification.', 'himachal-dynamic-dashboard'); ?>
                        </p>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('hdd_import_demo_action', 'hdd_import_demo_nonce'); ?>
                            <input type="hidden" name="action" value="hdd_import_demo_data" />
                            <button type="submit" class="button button-primary button-hero" style="background:#0b3b60; border-color:#0b3b60; width:100%; text-align:center;">
                                <span class="dashicons dashicons-download" style="vertical-align:text-bottom;"></span> <?php _e('Load Demo Data (1-Click)', 'himachal-dynamic-dashboard'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Settings Page
     */
    public static function render_settings_page() {
        if (isset($_GET['imported']) && $_GET['imported'] === 'success') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Demo data successfully imported matching reference design!', 'himachal-dynamic-dashboard') . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1 style="color:#0b3b60;"><?php _e('Himachal Dynamic Dashboard Settings', 'himachal-dynamic-dashboard'); ?></h1>
            <p class="description"><?php _e('Configure display options, archive links, and total population settings.', 'himachal-dynamic-dashboard'); ?></p>

            <form method="post" action="options.php" style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:24px; max-width:800px; margin-top:15px;">
                <?php
                settings_fields('hdd_settings_group');
                $settings = self::get_setting();
                ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="announcements_archive_url"><?php _e('Announcements "View All" URL', 'himachal-dynamic-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="announcements_archive_url" name="<?php echo esc_attr(self::OPTION_KEY); ?>[announcements_archive_url]" value="<?php echo esc_url($settings['announcements_archive_url']); ?>" class="regular-text" placeholder="https://..." />
                            <p class="description"><?php _e('Destination URL for "View All" link on Announcements card. Leave empty to automatically hide the link.', 'himachal-dynamic-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="downloads_archive_url"><?php _e('Downloads "View All" URL', 'himachal-dynamic-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="downloads_archive_url" name="<?php echo esc_attr(self::OPTION_KEY); ?>[downloads_archive_url]" value="<?php echo esc_url($settings['downloads_archive_url']); ?>" class="regular-text" placeholder="https://..." />
                            <p class="description"><?php _e('Destination URL for "View All" link on Downloads card. Leave empty to automatically hide the link.', 'himachal-dynamic-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="announcements_count"><?php _e('Announcements to Display', 'himachal-dynamic-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="announcements_count" name="<?php echo esc_attr(self::OPTION_KEY); ?>[announcements_count]" value="<?php echo esc_attr($settings['announcements_count']); ?>" min="1" max="20" class="small-text" />
                            <p class="description"><?php _e('Number of items shown in Latest Announcements (Default: 2)', 'himachal-dynamic-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="downloads_count"><?php _e('Downloads to Display', 'himachal-dynamic-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="downloads_count" name="<?php echo esc_attr(self::OPTION_KEY); ?>[downloads_count]" value="<?php echo esc_attr($settings['downloads_count']); ?>" min="1" max="20" class="small-text" />
                            <p class="description"><?php _e('Number of items shown in Important Downloads (Default: 4)', 'himachal-dynamic-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="total_population_label"><?php _e('Total Population Label', 'himachal-dynamic-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="total_population_label" name="<?php echo esc_attr(self::OPTION_KEY); ?>[total_population_label]" value="<?php echo esc_attr($settings['total_population_label']); ?>" class="regular-text" />
                            <p class="description"><?php _e('Label displayed on the bottom-left map card (Default: "Total Population")', 'himachal-dynamic-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="total_population_suffix"><?php _e('Total Population Suffix', 'himachal-dynamic-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="total_population_suffix" name="<?php echo esc_attr(self::OPTION_KEY); ?>[total_population_suffix]" value="<?php echo esc_attr($settings['total_population_suffix']); ?>" class="small-text" />
                            <p class="description"><?php _e('Suffix like * on the total number (e.g. 68,64,602*)', 'himachal-dynamic-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Indian Number Formatting', 'himachal-dynamic-dashboard'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[format_indian_number]" value="1" <?php checked($settings['format_indian_number'], '1'); ?> />
                                <?php _e('Format numbers using Indian commas system (e.g. 68,64,602 instead of 6,864,602)', 'himachal-dynamic-dashboard'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Dashboard Settings', 'himachal-dynamic-dashboard')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Handle 1-Click Demo Data Import
     */
    public static function handle_demo_data_import() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user.', 'himachal-dynamic-dashboard'));
        }

        check_admin_referer('hdd_import_demo_action', 'hdd_import_demo_nonce');

        self::seed_demo_data();

        wp_safe_redirect(admin_url('admin.php?page=hp-dashboard-settings&imported=success'));
        exit;
    }

    /**
     * Core Seeder function: populates all 12 districts, tabs, announcements, and downloads
     */
    public static function seed_demo_data() {
        // 1. Create Dashboard Categories (Tabs)
        $cat1 = wp_insert_term('Demographic Spread Of Minorities', 'dashboard_category', array('slug' => 'demographic-spread-of-minorities'));
        $cat2 = wp_insert_term('Disabilities In H.P', 'dashboard_category', array('slug' => 'disabilities-in-hp'));

        $cat1_id = is_array($cat1) ? $cat1['term_id'] : (is_object($cat1) ? $cat1->term_id : term_exists('Demographic Spread Of Minorities', 'dashboard_category')['term_id']);
        $cat2_id = is_array($cat2) ? $cat2['term_id'] : (is_object($cat2) ? $cat2->term_id : term_exists('Disabilities In H.P', 'dashboard_category')['term_id']);

        // 2. District Data from Reference Image (All 12 Districts)
        $plugin_img_dir = HDD_URL . 'assets/images/districts/';

        $districts_data = array(
            'CHA' => array(
                'name'        => 'Chamba',
                'val'         => 519080,
                'svg_id'      => 'CHA',
                'dot_x'       => 250,
                'dot_y'       => 195,
                'line_end_x'  => 120,
                'line_end_y'  => 195,
                'label_x'     => 108,
                'label_y'     => 188,
                'label_align' => 'right',
                'image'       => $plugin_img_dir . 'chamba.jpg',
                'sort'        => 1,
            ),
            'KAN' => array(
                'name'        => 'Kangra',
                'val'         => 1510075,
                'svg_id'      => 'KAN',
                'dot_x'       => 195,
                'dot_y'       => 375,
                'line_end_x'  => 95,
                'line_end_y'  => 375,
                'label_x'     => 82,
                'label_y'     => 368,
                'label_align' => 'right',
                'image'       => $plugin_img_dir . 'kangra.jpg',
                'sort'        => 2,
            ),
            'UNA' => array(
                'name'        => 'Una',
                'val'         => 521173,
                'svg_id'      => 'UNA',
                'dot_x'       => 195,
                'dot_y'       => 545,
                'line_end_x'  => 105,
                'line_end_y'  => 545,
                'label_x'     => 92,
                'label_y'     => 538,
                'label_align' => 'right',
                'image'       => $plugin_img_dir . 'una.jpg',
                'sort'        => 3,
            ),
            'HAM' => array(
                'name'        => 'Hamirpur',
                'val'         => 454768,
                'svg_id'      => 'HAM',
                'dot_x'       => 375,
                'dot_y'       => 675,
                'line_end_x'  => 145,
                'line_end_y'  => 675,
                'label_x'     => 132,
                'label_y'     => 668,
                'label_align' => 'right',
                'image'       => $plugin_img_dir . 'hamirpur.jpg',
                'sort'        => 4,
            ),
            'BIL' => array(
                'name'        => 'Bilaspur',
                'val'         => 391835,
                'svg_id'      => 'BIL',
                'dot_x'       => 295,
                'dot_y'       => 785,
                'line_end_x'  => 200,
                'line_end_y'  => 785,
                'label_x'     => 188,
                'label_y'     => 778,
                'label_align' => 'right',
                'image'       => $plugin_img_dir . 'bilaspur.jpg',
                'sort'        => 5,
            ),
            'SOL' => array(
                'name'        => 'Solan',
                'val'         => 580320,
                'svg_id'      => 'SOL',
                'dot_x'       => 440,
                'dot_y'       => 810,
                'line_end_x'  => 440,
                'line_end_y'  => 875,
                'label_x'     => 440,
                'label_y'     => 900,
                'label_align' => 'center',
                'image'       => $plugin_img_dir . 'solan.jpg',
                'sort'        => 6,
            ),
            'SIR' => array(
                'name'        => 'Sirmaur',
                'val'         => 529855,
                'svg_id'      => 'SIR',
                'dot_x'       => 660,
                'dot_y'       => 915,
                'line_end_x'  => 760,
                'line_end_y'  => 915,
                'label_x'     => 775,
                'label_y'     => 908,
                'label_align' => 'left',
                'image'       => $plugin_img_dir . 'sirmaur.jpg',
                'sort'        => 7,
            ),
            'KUL' => array(
                'name'        => 'Kullu',
                'val'         => 437903,
                'svg_id'      => 'KUL',
                'dot_x'       => 530,
                'dot_y'       => 425,
                'line_end_x'  => 530,
                'line_end_y'  => 200,
                'label_x'     => 530,
                'label_y'     => 158,
                'label_align' => 'center',
                'image'       => $plugin_img_dir . 'kullu.jpg',
                'sort'        => 8,
            ),
            'LAS' => array(
                'name'        => 'Lahul & Spiti',
                'val'         => 31564,
                'svg_id'      => 'LAS',
                'dot_x'       => 645,
                'dot_y'       => 270,
                'line_end_x'  => 790,
                'line_end_y'  => 270,
                'label_x'     => 810,
                'label_y'     => 263,
                'label_align' => 'left',
                'image'       => $plugin_img_dir . 'lahul-spiti.jpg',
                'sort'        => 9,
            ),
            'KIN' => array(
                'name'        => 'Kinnaur',
                'val'         => 84121,
                'svg_id'      => 'KIN',
                'dot_x'       => 810,
                'dot_y'       => 480,
                'line_end_x'  => 880,
                'line_end_y'  => 480,
                'label_x'     => 895,
                'label_y'     => 473,
                'label_align' => 'left',
                'image'       => $plugin_img_dir . 'kinnaur.jpg',
                'sort'        => 10,
            ),
            'MAN' => array(
                'name'        => 'Mandi',
                'val'         => 43790,
                'svg_id'      => 'MAN',
                'dot_x'       => 480,
                'dot_y'       => 600,
                'line_end_x'  => 890,
                'line_end_y'  => 600,
                'label_x'     => 912,
                'label_y'     => 593,
                'label_align' => 'left',
                'image'       => $plugin_img_dir . 'mandi.jpg',
                'sort'        => 11,
            ),
            'SHI' => array(
                'name'        => 'Shimla',
                'val'         => 814010,
                'svg_id'      => 'SHI',
                'dot_x'       => 660,
                'dot_y'       => 770,
                'line_end_x'  => 760,
                'line_end_y'  => 770,
                'label_x'     => 775,
                'label_y'     => 763,
                'label_align' => 'left',
                'image'       => $plugin_img_dir . 'shimla.jpg',
                'sort'        => 12,
            ),
        );

        foreach ($districts_data as $key => $d) {
            // Check if already exists
            $existing = get_posts(array(
                'post_type'   => 'district_map',
                'title'       => $d['name'],
                'post_status' => 'any',
                'numberposts' => 1,
            ));

            $post_id = !empty($existing) ? $existing[0]->ID : wp_insert_post(array(
                'post_title'  => $d['name'],
                'post_status' => 'publish',
                'post_type'   => 'district_map',
                'menu_order'  => $d['sort'],
            ));

            if ($post_id && !is_wp_error($post_id)) {
                // Assign category
                if ($cat1_id) {
                    wp_set_object_terms($post_id, array((int)$cat1_id), 'dashboard_category');
                }

                update_post_meta($post_id, '_hdd_district_val', $d['val']);
                update_post_meta($post_id, '_hdd_district_svg_id', $d['svg_id']);
                update_post_meta($post_id, '_hdd_district_dot_x', $d['dot_x']);
                update_post_meta($post_id, '_hdd_district_dot_y', $d['dot_y']);
                update_post_meta($post_id, '_hdd_district_line_start_x', $d['dot_x']);
                update_post_meta($post_id, '_hdd_district_line_start_y', $d['dot_y']);
                update_post_meta($post_id, '_hdd_district_line_end_x', $d['line_end_x']);
                update_post_meta($post_id, '_hdd_district_line_end_y', $d['line_end_y']);
                update_post_meta($post_id, '_hdd_district_label_x', $d['label_x']);
                update_post_meta($post_id, '_hdd_district_label_y', $d['label_y']);
                update_post_meta($post_id, '_hdd_district_label_align', $d['label_align']);
                update_post_meta($post_id, '_hdd_district_image_url', $d['image']);
                update_post_meta($post_id, '_hdd_district_enabled', '1');
                update_post_meta($post_id, '_hdd_district_sort', $d['sort']);
            }
        }

        // Also add sample entries for Disabilities category so switching tabs works right away!
        $disability_multipliers = array(
            'Chamba' => 15200, 'Kangra' => 45100, 'Una' => 18400, 'Hamirpur' => 14300,
            'Bilaspur' => 12100, 'Solan' => 19500, 'Sirmaur' => 16800, 'Kullu' => 13400,
            'Lahul & Spiti' => 1200, 'Kinnaur' => 2800, 'Mandi' => 28900, 'Shimla' => 26700
        );
        if ($cat2_id) {
            foreach ($districts_data as $key => $d) {
                $existing2 = get_posts(array(
                    'post_type'   => 'district_map',
                    'title'       => $d['name'] . ' (Disabilities)',
                    'post_status' => 'any',
                    'numberposts' => 1,
                ));

                $dis_val = isset($disability_multipliers[$d['name']]) ? $disability_multipliers[$d['name']] : round($d['val'] * 0.03);

                $post2_id = !empty($existing2) ? $existing2[0]->ID : wp_insert_post(array(
                    'post_title'  => $d['name'] . ' (Disabilities)',
                    'post_status' => 'publish',
                    'post_type'   => 'district_map',
                    'menu_order'  => $d['sort'],
                ));

                if ($post2_id && !is_wp_error($post2_id)) {
                    wp_set_object_terms($post2_id, array((int)$cat2_id), 'dashboard_category');
                    update_post_meta($post2_id, '_hdd_district_val', $dis_val);
                    update_post_meta($post2_id, '_hdd_district_svg_id', $d['svg_id']);
                    update_post_meta($post2_id, '_hdd_district_dot_x', $d['dot_x']);
                    update_post_meta($post2_id, '_hdd_district_dot_y', $d['dot_y']);
                    update_post_meta($post2_id, '_hdd_district_line_start_x', $d['dot_x']);
                    update_post_meta($post2_id, '_hdd_district_line_start_y', $d['dot_y']);
                    update_post_meta($post2_id, '_hdd_district_line_end_x', $d['line_end_x']);
                    update_post_meta($post2_id, '_hdd_district_line_end_y', $d['line_end_y']);
                    update_post_meta($post2_id, '_hdd_district_label_x', $d['label_x']);
                    update_post_meta($post2_id, '_hdd_district_label_y', $d['label_y']);
                    update_post_meta($post2_id, '_hdd_district_label_align', $d['label_align']);
                    update_post_meta($post2_id, '_hdd_district_image_url', $d['image']);
                    update_post_meta($post2_id, '_hdd_district_enabled', '1');
                }
            }
        }

        // 3. Demo Announcements
        $announcements = array(
            array(
                'title'   => 'Last date extended for Self Employment Scheme applications.',
                'date'    => '2025-08-21',
                'is_new'  => '1',
                'link'    => '#',
            ),
            array(
                'title'   => 'Last date extended for Self Employment Scheme applications.',
                'date'    => '2025-08-22',
                'is_new'  => '1',
                'link'    => '#',
            ),
        );

        foreach ($announcements as $ann) {
            $existing_ann = get_posts(array(
                'post_type'   => 'announcement',
                'title'       => $ann['title'],
                'post_status' => 'any',
                'numberposts' => 1,
            ));

            $ann_id = !empty($existing_ann) ? $existing_ann[0]->ID : wp_insert_post(array(
                'post_title'  => $ann['title'],
                'post_status' => 'publish',
                'post_type'   => 'announcement',
            ));

            if ($ann_id && !is_wp_error($ann_id)) {
                update_post_meta($ann_id, '_hdd_announcement_date', $ann['date']);
                update_post_meta($ann_id, '_hdd_announcement_is_new', $ann['is_new']);
                update_post_meta($ann_id, '_hdd_announcement_link', $ann['link']);
            }
        }

        // 4. Demo Important Downloads
        $downloads = array(
            'Application form for Minorities',
            'Application form for Minorities',
            'Application form for Minorities',
            'Application form for Minorities',
        );

        foreach ($downloads as $i => $dtitle) {
            $dl_id = wp_insert_post(array(
                'post_title'  => $dtitle,
                'post_status' => 'publish',
                'post_type'   => 'important_download',
                'menu_order'  => $i + 1,
            ));

            if ($dl_id && !is_wp_error($dl_id)) {
                update_post_meta($dl_id, '_hdd_download_file_url', '#');
                update_post_meta($dl_id, '_hdd_download_new_tab', '1');
                update_post_meta($dl_id, '_hdd_download_enabled', '1');
            }
        }

        // 5. Update default options
        $default_settings = array(
            'announcements_archive_url' => '#',
            'downloads_archive_url'     => '#',
            'announcements_count'       => 2,
            'downloads_count'           => 4,
            'total_population_label'    => 'Total Population',
            'total_population_suffix'   => '*',
            'format_indian_number'      => '1',
        );
        update_option(self::OPTION_KEY, $default_settings);
    }
}
