<?php
/**
 * Taxonomies for Himachal Dynamic Dashboard
 *
 * @package HimachalDynamicDashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class HDD_Taxonomies {

    /**
     * Initialize taxonomies
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'register_taxonomies'));
    }

    /**
     * Register all required Taxonomies
     */
    public static function register_taxonomies() {
        self::register_dashboard_category();
        self::register_announcement_category();
        self::register_download_category();
    }

    /**
     * 1. Dashboard Category (Tabs)
     * Each category creates a dynamic frontend tab (e.g. Demographic Spread Of Minorities, Disabilities In H.P)
     */
    private static function register_dashboard_category() {
        $labels = array(
            'name'              => _x('Dashboard Categories (Tabs)', 'taxonomy general name', 'himachal-dynamic-dashboard'),
            'singular_name'     => _x('Dashboard Category', 'taxonomy singular name', 'himachal-dynamic-dashboard'),
            'search_items'      => __('Search Categories', 'himachal-dynamic-dashboard'),
            'all_items'         => __('All Categories', 'himachal-dynamic-dashboard'),
            'parent_item'       => __('Parent Category', 'himachal-dynamic-dashboard'),
            'parent_item_colon' => __('Parent Category:', 'himachal-dynamic-dashboard'),
            'edit_item'         => __('Edit Category', 'himachal-dynamic-dashboard'),
            'update_item'       => __('Update Category', 'himachal-dynamic-dashboard'),
            'add_new_item'      => __('Add New Category / Tab', 'himachal-dynamic-dashboard'),
            'new_item_name'     => __('New Category Name', 'himachal-dynamic-dashboard'),
            'menu_name'         => __('Dashboard Categories (Tabs)', 'himachal-dynamic-dashboard'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => false,
            'rewrite'           => false,
            'show_in_rest'      => true,
        );

        register_taxonomy('dashboard_category', array('district_map'), $args);
    }

    /**
     * 2. Announcement Category
     */
    private static function register_announcement_category() {
        $labels = array(
            'name'              => _x('Announcement Categories', 'taxonomy general name', 'himachal-dynamic-dashboard'),
            'singular_name'     => _x('Announcement Category', 'taxonomy singular name', 'himachal-dynamic-dashboard'),
            'search_items'      => __('Search Categories', 'himachal-dynamic-dashboard'),
            'all_items'         => __('All Categories', 'himachal-dynamic-dashboard'),
            'edit_item'         => __('Edit Category', 'himachal-dynamic-dashboard'),
            'update_item'       => __('Update Category', 'himachal-dynamic-dashboard'),
            'add_new_item'      => __('Add New Category', 'himachal-dynamic-dashboard'),
            'new_item_name'     => __('New Category Name', 'himachal-dynamic-dashboard'),
            'menu_name'         => __('Announcement Categories', 'himachal-dynamic-dashboard'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => false,
            'rewrite'           => false,
            'show_in_rest'      => true,
        );

        register_taxonomy('announcement_category', array('announcement'), $args);
    }

    /**
     * 3. Download Category
     */
    private static function register_download_category() {
        $labels = array(
            'name'              => _x('Download Categories', 'taxonomy general name', 'himachal-dynamic-dashboard'),
            'singular_name'     => _x('Download Category', 'taxonomy singular name', 'himachal-dynamic-dashboard'),
            'search_items'      => __('Search Categories', 'himachal-dynamic-dashboard'),
            'all_items'         => __('All Categories', 'himachal-dynamic-dashboard'),
            'edit_item'         => __('Edit Category', 'himachal-dynamic-dashboard'),
            'update_item'       => __('Update Category', 'himachal-dynamic-dashboard'),
            'add_new_item'      => __('Add New Category', 'himachal-dynamic-dashboard'),
            'new_item_name'     => __('New Category Name', 'himachal-dynamic-dashboard'),
            'menu_name'         => __('Download Categories', 'himachal-dynamic-dashboard'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => false,
            'rewrite'           => false,
            'show_in_rest'      => true,
        );

        register_taxonomy('download_category', array('important_download'), $args);
    }
}
