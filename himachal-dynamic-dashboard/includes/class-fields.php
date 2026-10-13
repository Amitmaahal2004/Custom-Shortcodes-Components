<?php
/**
 * Custom Fields and Meta Boxes for Himachal Dynamic Dashboard
 *
 * @package HimachalDynamicDashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class HDD_Fields {

    /**
     * Initialize meta boxes and custom columns
     */
    public static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'register_meta_boxes'));
        add_action('save_post', array(__CLASS__, 'save_meta_boxes'), 10, 2);
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));

        // Custom columns for District Map
        add_filter('manage_district_map_posts_columns', array(__CLASS__, 'district_columns'));
        add_action('manage_district_map_posts_custom_column', array(__CLASS__, 'district_column_content'), 10, 2);

        // Custom columns for Announcements
        add_filter('manage_announcement_posts_columns', array(__CLASS__, 'announcement_columns'));
        add_action('manage_announcement_posts_custom_column', array(__CLASS__, 'announcement_column_content'), 10, 2);

        // Custom columns for Downloads
        add_filter('manage_important_download_posts_columns', array(__CLASS__, 'download_columns'));
        add_action('manage_important_download_posts_custom_column', array(__CLASS__, 'download_column_content'), 10, 2);
    }

    /**
     * Enqueue media uploader script and styles for admin
     */
    public static function enqueue_admin_scripts($hook) {
        global $post_type;
        if (in_array($post_type, array('district_map', 'announcement', 'important_download'), true)) {
            wp_enqueue_media();
            wp_add_inline_style('wp-admin', '
                .hdd-meta-section { margin-bottom: 22px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; }
                .hdd-meta-section h4 { margin: 0 0 12px; padding-bottom: 8px; border-bottom: 2px solid #0b3b60; color: #0b3b60; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
                .hdd-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .hdd-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
                .hdd-grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; }
                .hdd-field-group { margin-bottom: 12px; }
                .hdd-field-group label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 12px; color: #334155; }
                .hdd-field-group input[type="text"], .hdd-field-group input[type="number"], .hdd-field-group input[type="date"], .hdd-field-group input[type="url"], .hdd-field-group select { width: 100%; border-radius: 4px; border: 1px solid #cbd5e1; padding: 5px 8px; }
                .hdd-field-desc { font-size: 11px; color: #64748b; margin-top: 3px; }
                .hdd-img-preview-box { max-width: 140px; margin-top: 8px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px; background: #fff; display: inline-block; }
                .hdd-img-preview-box img { max-width: 100%; height: auto; display: block; border-radius: 3px; }
                .hdd-coord-badge { display: inline-block; background: #0b3b60; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 700; margin-right: 4px; }
            ');

            wp_add_inline_script('common', '
                jQuery(document).ready(function($) {
                    // Image upload handler
                    $(document).on("click", ".hdd-upload-btn", function(e) {
                        e.preventDefault();
                        var btn = $(this);
                        var targetInput = $("#" + btn.data("target"));
                        var previewContainer = $("#" + btn.data("preview"));

                        var frame = wp.media({
                            title: "Select or Upload Image",
                            button: { text: "Use Image" },
                            multiple: false
                        });

                        frame.on("select", function() {
                            var attachment = frame.state().get("selection").first().toJSON();
                            targetInput.val(attachment.url);
                            previewContainer.html(\'<div class="hdd-img-preview-box"><img src="\' + attachment.url + \'" alt="" /><button type="button" class="button button-link-delete hdd-remove-img-btn" style="margin-top:4px;">Remove</button></div>\');
                        });

                        frame.open();
                    });

                    // Remove image handler
                    $(document).on("click", ".hdd-remove-img-btn", function(e) {
                        e.preventDefault();
                        var container = $(this).closest(".hdd-img-field-wrap");
                        container.find(".hdd-img-url-input").val("");
                        container.find(".hdd-preview-area").html("");
                    });

                    // File upload handler for Downloads
                    $(document).on("click", ".hdd-upload-file-btn", function(e) {
                        e.preventDefault();
                        var btn = $(this);
                        var targetInput = $("#" + btn.data("target"));
                        var nameDisplay = $("#" + btn.data("name"));

                        var frame = wp.media({
                            title: "Select or Upload Document",
                            button: { text: "Use File" },
                            multiple: false
                        });

                        frame.on("select", function() {
                            var attachment = frame.state().get("selection").first().toJSON();
                            targetInput.val(attachment.url);
                            if (nameDisplay.length) {
                                nameDisplay.text(attachment.filename || attachment.title);
                            }
                        });

                        frame.open();
                    });
                });
            ');
        }
    }

    /**
     * Register Meta Boxes
     */
    public static function register_meta_boxes() {
        // District Map Meta Box
        add_meta_box(
            'hdd_district_meta_box',
            __('District Configuration & Map Positioning', 'himachal-dynamic-dashboard'),
            array(__CLASS__, 'render_district_meta_box'),
            'district_map',
            'normal',
            'high'
        );

        // Announcement Meta Box
        add_meta_box(
            'hdd_announcement_meta_box',
            __('Announcement Details', 'himachal-dynamic-dashboard'),
            array(__CLASS__, 'render_announcement_meta_box'),
            'announcement',
            'normal',
            'high'
        );

        // Important Download Meta Box
        add_meta_box(
            'hdd_download_meta_box',
            __('Download File & Settings', 'himachal-dynamic-dashboard'),
            array(__CLASS__, 'render_download_meta_box'),
            'important_download',
            'normal',
            'high'
        );
    }

    /**
     * Render District Map Meta Box
     */
    public static function render_district_meta_box($post) {
        wp_nonce_field('hdd_save_district_meta', 'hdd_district_meta_nonce');

        $val         = get_post_meta($post->ID, '_hdd_district_val', true);
        $short_val   = get_post_meta($post->ID, '_hdd_district_short_val', true);
        $svg_id      = get_post_meta($post->ID, '_hdd_district_svg_id', true);
        $dot_x       = get_post_meta($post->ID, '_hdd_district_dot_x', true);
        $dot_y       = get_post_meta($post->ID, '_hdd_district_dot_y', true);
        $line_start_x= get_post_meta($post->ID, '_hdd_district_line_start_x', true);
        $line_start_y= get_post_meta($post->ID, '_hdd_district_line_start_y', true);
        $line_end_x  = get_post_meta($post->ID, '_hdd_district_line_end_x', true);
        $line_end_y  = get_post_meta($post->ID, '_hdd_district_line_end_y', true);
        $label_x     = get_post_meta($post->ID, '_hdd_district_label_x', true);
        $label_y     = get_post_meta($post->ID, '_hdd_district_label_y', true);
        $label_align = get_post_meta($post->ID, '_hdd_district_label_align', true);
        $image_url   = get_post_meta($post->ID, '_hdd_district_image_url', true);
        $is_enabled  = get_post_meta($post->ID, '_hdd_district_enabled', true);
        if ($is_enabled === '') {
            $is_enabled = '1';
        }
        $sort_order  = get_post_meta($post->ID, '_hdd_district_sort', true);

        $districts_list = array(
            'CHA' => 'Chamba (district-chamba)',
            'KAN' => 'Kangra (district-kangra)',
            'UNA' => 'Una (district-una)',
            'HAM' => 'Hamirpur (district-hamirpur)',
            'BIL' => 'Bilaspur (district-bilaspur)',
            'SOL' => 'Solan (district-solan)',
            'SIR' => 'Sirmaur (district-sirmaur)',
            'SHI' => 'Shimla (district-shimla)',
            'MAN' => 'Mandi (district-mandi)',
            'KUL' => 'Kullu (district-kullu)',
            'LAS' => 'Lahul & Spiti (district-lahaul-spiti)',
            'KIN' => 'Kinnaur (district-kinnaur)',
        );
        ?>
        <div class="hdd-meta-container">
            <!-- GENERAL SECTION -->
            <div class="hdd-meta-section">
                <h4><span class="dashicons dashicons-admin-settings"></span> General Settings</h4>
                <div class="hdd-grid-3">
                    <div class="hdd-field-group">
                        <label for="hdd_district_val"><?php _e('Population / Value', 'himachal-dynamic-dashboard'); ?> <span style="color:red;">*</span></label>
                        <input type="number" id="hdd_district_val" name="hdd_district_val" value="<?php echo esc_attr($val); ?>" placeholder="e.g. 519080" required />
                        <div class="hdd-field-desc"><?php _e('Raw numeric population or metric value (used in total sum & display)', 'himachal-dynamic-dashboard'); ?></div>
                    </div>
                    <div class="hdd-field-group">
                        <label for="hdd_district_short_val"><?php _e('Short Value (Optional)', 'himachal-dynamic-dashboard'); ?></label>
                        <input type="text" id="hdd_district_short_val" name="hdd_district_short_val" value="<?php echo esc_attr($short_val); ?>" placeholder="e.g. 5.19 Lakh" />
                        <div class="hdd-field-desc"><?php _e('Alternative compact representation (if needed)', 'himachal-dynamic-dashboard'); ?></div>
                    </div>
                    <div class="hdd-field-group">
                        <label for="hdd_district_svg_id"><?php _e('Map District Region', 'himachal-dynamic-dashboard'); ?> <span style="color:red;">*</span></label>
                        <select id="hdd_district_svg_id" name="hdd_district_svg_id" required>
                            <option value=""><?php _e('-- Select District Region --', 'himachal-dynamic-dashboard'); ?></option>
                            <?php foreach ($districts_list as $code => $dname) : ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($svg_id, $code); ?>><?php echo esc_html($dname); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="hdd-field-desc"><?php _e('Connects this entry to the HP SVG district boundary', 'himachal-dynamic-dashboard'); ?></div>
                    </div>
                </div>
            </div>

            <!-- MAP COORDINATES SECTION -->
            <div class="hdd-meta-section">
                <h4><span class="dashicons dashicons-location"></span> Map Positions & Connector Line Coordinates (SVG Coordinates)</h4>
                <p class="hdd-field-desc" style="margin-bottom:12px;">
                    <?php _e('All positions use SVG canvas coordinates (0 to 1000 X, 0 to 850 Y). Allows pixel-perfect repositioning without touching code.', 'himachal-dynamic-dashboard'); ?>
                </p>

                <div class="hdd-grid-3" style="margin-bottom:12px;">
                    <!-- Blue Location Dot -->
                    <div style="background:#fff; padding:10px; border-radius:4px; border:1px solid #e2e8f0;">
                        <span class="hdd-coord-badge">Dot</span> <strong>Location Marker Dot</strong>
                        <div class="hdd-grid-2" style="margin-top:8px;">
                            <div class="hdd-field-group">
                                <label>Dot X</label>
                                <input type="number" step="any" name="hdd_district_dot_x" value="<?php echo esc_attr($dot_x); ?>" placeholder="X" />
                            </div>
                            <div class="hdd-field-group">
                                <label>Dot Y</label>
                                <input type="number" step="any" name="hdd_district_dot_y" value="<?php echo esc_attr($dot_y); ?>" placeholder="Y" />
                            </div>
                        </div>
                    </div>

                    <!-- Connector Line Start & End -->
                    <div style="background:#fff; padding:10px; border-radius:4px; border:1px solid #e2e8f0;">
                        <span class="hdd-coord-badge">Line</span> <strong>Connector Line End</strong>
                        <div class="hdd-grid-2" style="margin-top:8px;">
                            <div class="hdd-field-group">
                                <label>End X</label>
                                <input type="number" step="any" name="hdd_district_line_end_x" value="<?php echo esc_attr($line_end_x); ?>" placeholder="X" />
                            </div>
                            <div class="hdd-field-group">
                                <label>End Y</label>
                                <input type="number" step="any" name="hdd_district_line_end_y" value="<?php echo esc_attr($line_end_y); ?>" placeholder="Y" />
                            </div>
                        </div>
                        <div class="hdd-field-desc"><?php _e('Start automatically connects from Dot X/Y (or custom line_start)', 'himachal-dynamic-dashboard'); ?></div>
                    </div>

                    <!-- Label Position -->
                    <div style="background:#fff; padding:10px; border-radius:4px; border:1px solid #e2e8f0;">
                        <span class="hdd-coord-badge">Label</span> <strong>Text Label Coordinates</strong>
                        <div class="hdd-grid-2" style="margin-top:8px;">
                            <div class="hdd-field-group">
                                <label>Label X</label>
                                <input type="number" step="any" name="hdd_district_label_x" value="<?php echo esc_attr($label_x); ?>" placeholder="X" />
                            </div>
                            <div class="hdd-field-group">
                                <label>Label Y</label>
                                <input type="number" step="any" name="hdd_district_label_y" value="<?php echo esc_attr($label_y); ?>" placeholder="Y" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hdd-grid-3">
                    <div class="hdd-field-group">
                        <label for="hdd_district_label_align"><?php _e('Label Text Alignment', 'himachal-dynamic-dashboard'); ?></label>
                        <select id="hdd_district_label_align" name="hdd_district_label_align">
                            <option value="left" <?php selected($label_align, 'left'); ?>><?php _e('Left (start at coordinate)', 'himachal-dynamic-dashboard'); ?></option>
                            <option value="right" <?php selected($label_align, 'right'); ?>><?php _e('Right (end at coordinate, for left-side districts)', 'himachal-dynamic-dashboard'); ?></option>
                            <option value="center" <?php selected($label_align, 'center'); ?>><?php _e('Center (middle at coordinate)', 'himachal-dynamic-dashboard'); ?></option>
                        </select>
                    </div>
                    <div class="hdd-field-group">
                        <label><?php _e('Custom Line Start (Optional)', 'himachal-dynamic-dashboard'); ?></label>
                        <div class="hdd-grid-2">
                            <input type="number" step="any" name="hdd_district_line_start_x" value="<?php echo esc_attr($line_start_x); ?>" placeholder="Start X" />
                            <input type="number" step="any" name="hdd_district_line_start_y" value="<?php echo esc_attr($line_start_y); ?>" placeholder="Start Y" />
                        </div>
                    </div>
                    <div class="hdd-field-group">
                        <label for="hdd_district_sort"><?php _e('Sort Order', 'himachal-dynamic-dashboard'); ?></label>
                        <input type="number" id="hdd_district_sort" name="hdd_district_sort" value="<?php echo esc_attr($sort_order); ?>" placeholder="0" />
                    </div>
                </div>
            </div>

            <!-- IMAGE SECTION -->
            <div class="hdd-meta-section hdd-img-field-wrap">
                <h4><span class="dashicons dashicons-format-image"></span> District Background Image</h4>
                <p class="hdd-field-desc"><?php _e('Displays inside the district boundary shape via SVG clip path. Defaults to scenic district photo if left empty.', 'himachal-dynamic-dashboard'); ?></p>
                
                <div style="display:flex; gap:10px; align-items:center; margin-top:8px;">
                    <input type="text" id="hdd_district_image_url" class="hdd-img-url-input" name="hdd_district_image_url" value="<?php echo esc_url($image_url); ?>" style="flex:1;" placeholder="https://..." />
                    <button type="button" class="button button-secondary hdd-upload-btn" data-target="hdd_district_image_url" data-preview="hdd_img_preview">
                        <span class="dashicons dashicons-upload" style="vertical-align:text-top;"></span> <?php _e('Choose Image', 'himachal-dynamic-dashboard'); ?>
                    </button>
                </div>
                <div id="hdd_img_preview" class="hdd-preview-area">
                    <?php if (!empty($image_url)) : ?>
                        <div class="hdd-img-preview-box">
                            <img src="<?php echo esc_url($image_url); ?>" alt="" />
                            <button type="button" class="button button-link-delete hdd-remove-img-btn" style="margin-top:4px;"><?php _e('Remove', 'himachal-dynamic-dashboard'); ?></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- STATUS TOGGLE -->
            <div class="hdd-meta-section" style="margin-bottom:0;">
                <label>
                    <input type="checkbox" name="hdd_district_enabled" value="1" <?php checked($is_enabled, '1'); ?> />
                    <strong><?php _e('Enable this district on the map', 'himachal-dynamic-dashboard'); ?></strong>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * Render Announcement Meta Box
     */
    public static function render_announcement_meta_box($post) {
        wp_nonce_field('hdd_save_announcement_meta', 'hdd_announcement_meta_nonce');

        $date      = get_post_meta($post->ID, '_hdd_announcement_date', true);
        if (empty($date)) {
            $date = current_time('Y-m-d');
        }
        $is_new    = get_post_meta($post->ID, '_hdd_announcement_is_new', true);
        $link_url  = get_post_meta($post->ID, '_hdd_announcement_link', true);
        $short_desc= get_post_meta($post->ID, '_hdd_announcement_desc', true);
        ?>
        <div class="hdd-meta-container">
            <div class="hdd-grid-2">
                <div class="hdd-field-group">
                    <label for="hdd_announcement_date"><?php _e('Announcement Date', 'himachal-dynamic-dashboard'); ?> <span style="color:red;">*</span></label>
                    <input type="date" id="hdd_announcement_date" name="hdd_announcement_date" value="<?php echo esc_attr($date); ?>" required />
                    <div class="hdd-field-desc"><?php _e('Dynamically generates Day (e.g. 21) and Month Year (e.g. Aug 2025)', 'himachal-dynamic-dashboard'); ?></div>
                </div>
                <div class="hdd-field-group">
                    <label for="hdd_announcement_link"><?php _e('Link / View URL (Optional)', 'himachal-dynamic-dashboard'); ?></label>
                    <input type="url" id="hdd_announcement_link" name="hdd_announcement_link" value="<?php echo esc_url($link_url); ?>" placeholder="https://..." />
                    <div class="hdd-field-desc"><?php _e('Direct link when clicking the announcement', 'himachal-dynamic-dashboard'); ?></div>
                </div>
            </div>

            <div class="hdd-field-group">
                <label for="hdd_announcement_desc"><?php _e('Short Description (Optional)', 'himachal-dynamic-dashboard'); ?></label>
                <input type="text" id="hdd_announcement_desc" name="hdd_announcement_desc" value="<?php echo esc_attr($short_desc); ?>" />
            </div>

            <div style="margin-top:10px;">
                <label>
                    <input type="checkbox" name="hdd_announcement_is_new" value="1" <?php checked($is_new, '1'); ?> />
                    <strong><?php _e('Show "New" badge next to this announcement', 'himachal-dynamic-dashboard'); ?></strong>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * Render Important Download Meta Box
     */
    public static function render_download_meta_box($post) {
        wp_nonce_field('hdd_save_download_meta', 'hdd_download_meta_nonce');

        $file_url    = get_post_meta($post->ID, '_hdd_download_file_url', true);
        $ext_url     = get_post_meta($post->ID, '_hdd_download_ext_url', true);
        $new_tab     = get_post_meta($post->ID, '_hdd_download_new_tab', true);
        $is_enabled  = get_post_meta($post->ID, '_hdd_download_enabled', true);
        if ($is_enabled === '') {
            $is_enabled = '1';
        }
        ?>
        <div class="hdd-meta-container">
            <div class="hdd-field-group">
                <label><?php _e('Downloadable File (PDF, DOC, DOCX, XLS, XLSX, ZIP)', 'himachal-dynamic-dashboard'); ?></label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" id="hdd_download_file_url" name="hdd_download_file_url" value="<?php echo esc_url($file_url); ?>" style="flex:1;" placeholder="https://..." />
                    <button type="button" class="button button-secondary hdd-upload-file-btn" data-target="hdd_download_file_url" data-name="hdd_file_name_display">
                        <span class="dashicons dashicons-media-document" style="vertical-align:text-top;"></span> <?php _e('Select File from Library', 'himachal-dynamic-dashboard'); ?>
                    </button>
                </div>
                <div id="hdd_file_name_display" style="font-weight:600; color:#0b3b60; margin-top:5px; font-size:12px;"></div>
            </div>

            <div class="hdd-field-group">
                <label for="hdd_download_ext_url"><?php _e('External / Custom URL Override (Optional)', 'himachal-dynamic-dashboard'); ?></label>
                <input type="url" id="hdd_download_ext_url" name="hdd_download_ext_url" value="<?php echo esc_url($ext_url); ?>" placeholder="https://..." />
                <div class="hdd-field-desc"><?php _e('If provided, clicking the download will open this URL instead of the file', 'himachal-dynamic-dashboard'); ?></div>
            </div>

            <div style="display:flex; gap:20px; margin-top:12px;">
                <label>
                    <input type="checkbox" name="hdd_download_new_tab" value="1" <?php checked($new_tab, '1'); ?> />
                    <?php _e('Open in new tab (_blank)', 'himachal-dynamic-dashboard'); ?>
                </label>
                <label>
                    <input type="checkbox" name="hdd_download_enabled" value="1" <?php checked($is_enabled, '1'); ?> />
                    <?php _e('Enable download item', 'himachal-dynamic-dashboard'); ?>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * Save Meta Boxes
     */
    public static function save_meta_boxes($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // District Map Save
        if (isset($_POST['hdd_district_meta_nonce']) && wp_verify_nonce($_POST['hdd_district_meta_nonce'], 'hdd_save_district_meta')) {
            if (current_user_can('edit_post', $post_id)) {
                $fields_text = array(
                    'hdd_district_val'        => '_hdd_district_val',
                    'hdd_district_short_val'  => '_hdd_district_short_val',
                    'hdd_district_svg_id'     => '_hdd_district_svg_id',
                    'hdd_district_label_align'=> '_hdd_district_label_align',
                    'hdd_district_sort'       => '_hdd_district_sort',
                );
                foreach ($fields_text as $input => $meta_key) {
                    if (isset($_POST[$input])) {
                        update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$input]));
                    }
                }

                // Coordinates (floatval)
                $coords = array(
                    'hdd_district_dot_x'        => '_hdd_district_dot_x',
                    'hdd_district_dot_y'        => '_hdd_district_dot_y',
                    'hdd_district_line_start_x' => '_hdd_district_line_start_x',
                    'hdd_district_line_start_y' => '_hdd_district_line_start_y',
                    'hdd_district_line_end_x'   => '_hdd_district_line_end_x',
                    'hdd_district_line_end_y'   => '_hdd_district_line_end_y',
                    'hdd_district_label_x'      => '_hdd_district_label_x',
                    'hdd_district_label_y'      => '_hdd_district_label_y',
                );
                foreach ($coords as $input => $meta_key) {
                    if (isset($_POST[$input]) && $_POST[$input] !== '') {
                        update_post_meta($post_id, $meta_key, floatval($_POST[$input]));
                    } else {
                        delete_post_meta($post_id, $meta_key);
                    }
                }

                // Image URL
                if (isset($_POST['hdd_district_image_url'])) {
                    update_post_meta($post_id, '_hdd_district_image_url', esc_url_raw($_POST['hdd_district_image_url']));
                }

                // Enabled toggle
                $enabled = isset($_POST['hdd_district_enabled']) ? '1' : '0';
                update_post_meta($post_id, '_hdd_district_enabled', $enabled);
            }
        }

        // Announcement Save
        if (isset($_POST['hdd_announcement_meta_nonce']) && wp_verify_nonce($_POST['hdd_announcement_meta_nonce'], 'hdd_save_announcement_meta')) {
            if (current_user_can('edit_post', $post_id)) {
                if (isset($_POST['hdd_announcement_date'])) {
                    update_post_meta($post_id, '_hdd_announcement_date', sanitize_text_field($_POST['hdd_announcement_date']));
                }
                if (isset($_POST['hdd_announcement_link'])) {
                    update_post_meta($post_id, '_hdd_announcement_link', esc_url_raw($_POST['hdd_announcement_link']));
                }
                if (isset($_POST['hdd_announcement_desc'])) {
                    update_post_meta($post_id, '_hdd_announcement_desc', sanitize_text_field($_POST['hdd_announcement_desc']));
                }
                $is_new = isset($_POST['hdd_announcement_is_new']) ? '1' : '0';
                update_post_meta($post_id, '_hdd_announcement_is_new', $is_new);
            }
        }

        // Download Save
        if (isset($_POST['hdd_download_meta_nonce']) && wp_verify_nonce($_POST['hdd_download_meta_nonce'], 'hdd_save_download_meta')) {
            if (current_user_can('edit_post', $post_id)) {
                if (isset($_POST['hdd_download_file_url'])) {
                    update_post_meta($post_id, '_hdd_download_file_url', esc_url_raw($_POST['hdd_download_file_url']));
                }
                if (isset($_POST['hdd_download_ext_url'])) {
                    update_post_meta($post_id, '_hdd_download_ext_url', esc_url_raw($_POST['hdd_download_ext_url']));
                }
                $new_tab = isset($_POST['hdd_download_new_tab']) ? '1' : '0';
                update_post_meta($post_id, '_hdd_download_new_tab', $new_tab);

                $enabled = isset($_POST['hdd_download_enabled']) ? '1' : '0';
                update_post_meta($post_id, '_hdd_download_enabled', $enabled);
            }
        }
    }

    /**
     * District Map Custom Columns
     */
    public static function district_columns($columns) {
        $new_cols = array();
        $new_cols['cb']        = $columns['cb'];
        $new_cols['image']     = __('Image', 'himachal-dynamic-dashboard');
        $new_cols['title']     = __('District Name', 'himachal-dynamic-dashboard');
        $new_cols['category']  = __('Dashboard Category / Tab', 'himachal-dynamic-dashboard');
        $new_cols['val']       = __('Population / Value', 'himachal-dynamic-dashboard');
        $new_cols['svg_id']    = __('SVG Region', 'himachal-dynamic-dashboard');
        $new_cols['coords']    = __('Coordinates Set', 'himachal-dynamic-dashboard');
        $new_cols['status']    = __('Status', 'himachal-dynamic-dashboard');
        $new_cols['date']      = $columns['date'];
        return $new_cols;
    }

    public static function district_column_content($column, $post_id) {
        switch ($column) {
            case 'image':
                $img = get_post_meta($post_id, '_hdd_district_image_url', true);
                if ($img) {
                    echo '<img src="' . esc_url($img) . '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #cbd5e1;" />';
                } else {
                    echo '<span style="color:#94a3b8;">&mdash;</span>';
                }
                break;
            case 'category':
                $terms = get_the_term_list($post_id, 'dashboard_category', '', ', ');
                echo $terms ? $terms : '<span style="color:#94a3b8;">Unassigned</span>';
                break;
            case 'val':
                $val = get_post_meta($post_id, '_hdd_district_val', true);
                echo '<strong>' . esc_html(number_format((float)$val)) . '</strong>';
                break;
            case 'svg_id':
                $svg_id = get_post_meta($post_id, '_hdd_district_svg_id', true);
                echo $svg_id ? '<span class="hdd-coord-badge">' . esc_html($svg_id) . '</span>' : '&mdash;';
                break;
            case 'coords':
                $dot_x = get_post_meta($post_id, '_hdd_district_dot_x', true);
                $lbl_x = get_post_meta($post_id, '_hdd_district_label_x', true);
                if ($dot_x !== '' && $lbl_x !== '') {
                    echo '<span style="color:#16a34a; font-weight:600;">&#10003; Configured</span>';
                } else {
                    echo '<span style="color:#eab308; font-weight:600;">Defaults</span>';
                }
                break;
            case 'status':
                $en = get_post_meta($post_id, '_hdd_district_enabled', true);
                echo ($en === '0') ? '<span style="color:#ef4444;">Disabled</span>' : '<span style="color:#16a34a;">Active</span>';
                break;
        }
    }

    /**
     * Announcement Custom Columns
     */
    public static function announcement_columns($columns) {
        $new_cols = array();
        $new_cols['cb']       = $columns['cb'];
        $new_cols['title']    = __('Title', 'himachal-dynamic-dashboard');
        $new_cols['date_box'] = __('Display Date', 'himachal-dynamic-dashboard');
        $new_cols['is_new']   = __('New Badge', 'himachal-dynamic-dashboard');
        $new_cols['category'] = __('Category', 'himachal-dynamic-dashboard');
        $new_cols['date']     = $columns['date'];
        return $new_cols;
    }

    public static function announcement_column_content($column, $post_id) {
        switch ($column) {
            case 'date_box':
                $d = get_post_meta($post_id, '_hdd_announcement_date', true);
                if ($d) {
                    echo esc_html(date_i18n('d M Y', strtotime($d)));
                } else {
                    echo '&mdash;';
                }
                break;
            case 'is_new':
                $is_new = get_post_meta($post_id, '_hdd_announcement_is_new', true);
                echo ($is_new === '1') ? '<span style="background:#107c41;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;">NEW</span>' : '&mdash;';
                break;
            case 'category':
                $terms = get_the_term_list($post_id, 'announcement_category', '', ', ');
                echo $terms ? $terms : '<span style="color:#94a3b8;">General</span>';
                break;
        }
    }

    /**
     * Download Custom Columns
     */
    public static function download_columns($columns) {
        $new_cols = array();
        $new_cols['cb']       = $columns['cb'];
        $new_cols['title']    = __('Title', 'himachal-dynamic-dashboard');
        $new_cols['file']     = __('File Link', 'himachal-dynamic-dashboard');
        $new_cols['category'] = __('Category', 'himachal-dynamic-dashboard');
        $new_cols['status']   = __('Status', 'himachal-dynamic-dashboard');
        $new_cols['date']     = $columns['date'];
        return $new_cols;
    }

    public static function download_column_content($column, $post_id) {
        switch ($column) {
            case 'file':
                $f = get_post_meta($post_id, '_hdd_download_file_url', true);
                $ext = get_post_meta($post_id, '_hdd_download_ext_url', true);
                $url = $ext ? $ext : $f;
                if ($url) {
                    echo '<a href="' . esc_url($url) . '" target="_blank" style="color:#0b3b60; font-weight:600;">' . esc_html(basename(parse_url($url, PHP_URL_PATH))) . ' &rarr;</a>';
                } else {
                    echo '<span style="color:#ef4444;">No file attached</span>';
                }
                break;
            case 'category':
                $terms = get_the_term_list($post_id, 'download_category', '', ', ');
                echo $terms ? $terms : '<span style="color:#94a3b8;">General</span>';
                break;
            case 'status':
                $en = get_post_meta($post_id, '_hdd_download_enabled', true);
                echo ($en === '0') ? '<span style="color:#ef4444;">Disabled</span>' : '<span style="color:#16a34a;">Active</span>';
                break;
        }
    }
}
