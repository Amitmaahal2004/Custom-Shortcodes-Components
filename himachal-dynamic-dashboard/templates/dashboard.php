<?php
/**
 * Frontend Template for [hp_minority_dashboard]
 *
 * @package HimachalDynamicDashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = HDD_Admin::get_setting();
?>

<div class="hp-dashboard-wrapper" id="hp-dashboard-root">
    <div class="hp-dashboard-grid">

        <!-- ============================================================== -->
        <!-- LEFT COLUMN: MAP CARD WITH DYNAMIC TABS & SVG MAP              -->
        <!-- ============================================================== -->
        <div class="hp-dashboard-card hp-map-card">
            
            <!-- Dynamic Dashboard Tabs -->
            <?php if (!empty($categories)) : ?>
                <div class="hp-tabs-container" role="tablist" aria-label="<?php esc_attr_e('Dashboard Category Tabs', 'himachal-dynamic-dashboard'); ?>">
                    <?php foreach ($categories as $index => $cat) : 
                        $is_active = ($active_cat && $cat->term_id === $active_cat->term_id);
                    ?>
                        <button type="button"
                                class="hp-tab-btn <?php echo $is_active ? 'hp-tab-active' : ''; ?>"
                                role="tab"
                                aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                aria-controls="hp-tab-panel-<?php echo esc_attr($cat->slug); ?>"
                                id="hp-tab-<?php echo esc_attr($cat->slug); ?>"
                                data-slug="<?php echo esc_attr($cat->slug); ?>"
                                data-index="<?php echo esc_attr($index); ?>">
                            <?php echo esc_html($cat->name); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Interactive Map Viewport -->
            <div class="hp-map-viewport" id="hp-map-container">
                
                <!-- Rendered SVG Map -->
                <?php echo HDD_Map::render_svg($districts_data, $categories, $active_index); ?>

                <!-- Bottom-Left Total Population Card -->
                <div class="hp-total-pop-card">
                    <div class="hp-total-label"><?php echo esc_html($settings['total_population_label']); ?></div>
                    <div class="hp-total-value" id="hp-total-display"><?php echo esc_html($formatted_total); ?></div>
                </div>

                <!-- Hover Tooltip -->
                <div class="hp-map-tooltip" id="hp-map-tooltip" style="opacity:0; visibility:hidden;">
                    <div class="hp-tooltip-title"></div>
                    <div class="hp-tooltip-val"></div>
                </div>
            </div>

        </div>

        <!-- ============================================================== -->
        <!-- RIGHT COLUMN: ANNOUNCEMENTS & IMPORTANT DOWNLOADS CARDS        -->
        <!-- ============================================================== -->
        <div class="hp-sidebar-cards">

            <!-- 1. LATEST ANNOUNCEMENTS CARD -->
            <div class="hp-dashboard-card hp-announcements-card">
                <div class="hp-card-header">
                    <h3 class="hp-card-title"><?php _e('Latest Announcements', 'himachal-dynamic-dashboard'); ?></h3>
                    <?php if (!empty($settings['announcements_archive_url'])) : ?>
                        <a href="<?php echo esc_url($settings['announcements_archive_url']); ?>" class="hp-view-all-link">
                            <?php _e('View All', 'himachal-dynamic-dashboard'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="hp-announcements-list">
                    <?php if ($announcements_query->have_posts()) : ?>
                        <?php while ($announcements_query->have_posts()) : $announcements_query->the_post(); 
                            $ann_id    = get_the_ID();
                            $date_raw  = get_post_meta($ann_id, '_hdd_announcement_date', true);
                            $timestamp = !empty($date_raw) ? strtotime($date_raw) : get_the_time('U');
                            $day       = date_i18n('d', $timestamp);
                            $month_yr  = date_i18n('M Y', $timestamp);
                            $is_new    = (get_post_meta($ann_id, '_hdd_announcement_is_new', true) === '1');
                            $link      = get_post_meta($ann_id, '_hdd_announcement_link', true);
                        ?>
                            <div class="hp-announcement-item">
                                <!-- Green Date Box -->
                                <div class="hp-date-box">
                                    <span class="hp-date-day"><?php echo esc_html($day); ?></span>
                                    <span class="hp-date-my"><?php echo esc_html($month_yr); ?></span>
                                </div>

                                <!-- Announcement Content -->
                                <div class="hp-ann-body">
                                    <div class="hp-ann-text">
                                        <?php if (!empty($link)) : ?>
                                            <a href="<?php echo esc_url($link); ?>" class="hp-ann-title-link">
                                                <?php the_title(); ?>
                                            </a>
                                        <?php else : ?>
                                            <span class="hp-ann-title"><?php the_title(); ?></span>
                                        <?php endif; ?>

                                        <?php if ($is_new) : ?>
                                            <span class="hp-badge-new"><?php _e('New', 'himachal-dynamic-dashboard'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php else : ?>
                        <p class="hp-empty-message"><?php _e('No announcements currently available.', 'himachal-dynamic-dashboard'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. IMPORTANT DOWNLOADS CARD -->
            <div class="hp-dashboard-card hp-downloads-card">
                <div class="hp-card-header">
                    <h3 class="hp-card-title"><?php _e('Important Downloads', 'himachal-dynamic-dashboard'); ?></h3>
                    <a href="<?php echo esc_url( home_url( '/downloads/' ) ); ?>" class="hp-view-all-link">
                        <?php _e('View All', 'himachal-dynamic-dashboard'); ?>
                    </a>
                </div>

                <div class="hp-downloads-list">
                    <?php if ($downloads_query->have_posts()) : ?>
                        <?php while ($downloads_query->have_posts()) : $downloads_query->the_post(); 
                            $dl_id      = get_the_ID();
                            $file_url   = get_post_meta($dl_id, '_hdd_download_file_url', true);
                            $ext_url    = get_post_meta($dl_id, '_hdd_download_ext_url', true);
                            $final_url  = !empty($ext_url) ? $ext_url : (!empty($file_url) ? $file_url : '#');
                            $new_tab    = (get_post_meta($dl_id, '_hdd_download_new_tab', true) === '1');
                            $target     = $new_tab ? '_blank' : '_self';
                        ?>
                            <div class="hp-download-item">
                                <!-- Red Document / PDF Icon -->
                                <div class="hp-download-doc-icon">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </div>

                                <!-- Download Title -->
                                <div class="hp-download-content">
                                    <a href="<?php echo esc_url($final_url); ?>" target="<?php echo esc_attr($target); ?>" class="hp-download-title" <?php echo ($target === '_blank') ? 'rel="noopener noreferrer"' : ''; ?>>
                                        <?php the_title(); ?>
                                    </a>
                                </div>

                                <!-- Green Download Arrow Icon -->
                                <a href="<?php echo esc_url($final_url); ?>" target="<?php echo esc_attr($target); ?>" class="hp-download-action-btn" aria-label="<?php esc_attr_e('Download file', 'himachal-dynamic-dashboard'); ?>" <?php echo ($target === '_blank') ? 'rel="noopener noreferrer"' : ''; ?>>
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </a>
                            </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php else : ?>
                        <p class="hp-empty-message"><?php _e('No downloads currently available.', 'himachal-dynamic-dashboard'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>
