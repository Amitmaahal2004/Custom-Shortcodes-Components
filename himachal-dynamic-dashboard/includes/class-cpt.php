<?php
/**
 * Custom Post Types for Himachal Dynamic Dashboard
 *
 * @package HimachalDynamicDashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class HDD_CPT {

    /**
     * Initialize CPT registration
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'register_post_types'));
    }

    /**
     * Register all required CPTs
     */
    public static function register_post_types() {
        self::register_district_map_cpt();
        self::register_announcement_cpt();
        self::register_download_cpt();
    }

    /**
     * 1. District Map Post Type
     */
    private static function register_district_map_cpt() {
        $labels = array(
            'name'                  => _x('District Data', 'Post type general name', 'himachal-dynamic-dashboard'),
            'singular_name'         => _x('District Data', 'Post type singular name', 'himachal-dynamic-dashboard'),
            'menu_name'             => _x('District Map', 'Admin Menu text', 'himachal-dynamic-dashboard'),
            'name_admin_bar'        => _x('District Data', 'Add New on Toolbar', 'himachal-dynamic-dashboard'),
            'add_new'               => __('Add New District', 'himachal-dynamic-dashboard'),
            'add_new_item'          => __('Add New District Data', 'himachal-dynamic-dashboard'),
            'new_item'              => __('New District Data', 'himachal-dynamic-dashboard'),
            'edit_item'             => __('Edit District Data', 'himachal-dynamic-dashboard'),
            'view_item'             => __('View District Data', 'himachal-dynamic-dashboard'),
            'all_items'             => __('All Districts', 'himachal-dynamic-dashboard'),
            'search_items'          => __('Search Districts', 'himachal-dynamic-dashboard'),
            'not_found'             => __('No districts found.', 'himachal-dynamic-dashboard'),
            'not_found_in_trash'    => __('No districts found in Trash.', 'himachal-dynamic-dashboard'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false, // Managed under custom Himachal Dashboard menu
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'thumbnail'),
            'show_in_rest'       => true,
        );

        register_post_type('district_map', $args);
    }

    /**
     * 2. Announcement Post Type
     */
    private static function register_announcement_cpt() {
        $labels = array(
            'name'                  => _x('Announcements', 'Post type general name', 'himachal-dynamic-dashboard'),
            'singular_name'         => _x('Announcement', 'Post type singular name', 'himachal-dynamic-dashboard'),
            'menu_name'             => _x('Announcements', 'Admin Menu text', 'himachal-dynamic-dashboard'),
            'add_new'               => __('Add New', 'himachal-dynamic-dashboard'),
            'add_new_item'          => __('Add New Announcement', 'himachal-dynamic-dashboard'),
            'edit_item'             => __('Edit Announcement', 'himachal-dynamic-dashboard'),
            'all_items'             => __('All Announcements', 'himachal-dynamic-dashboard'),
            'search_items'          => __('Search Announcements', 'himachal-dynamic-dashboard'),
            'not_found'             => __('No announcements found.', 'himachal-dynamic-dashboard'),
            'not_found_in_trash'    => __('No announcements found in Trash.', 'himachal-dynamic-dashboard'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => array('title', 'editor'),
            'show_in_rest'       => true,
        );

        register_post_type('announcement', $args);
    }

    /**
     * 3. Important Download Post Type
     */
    private static function register_download_cpt() {
        $labels = array(
            'name'                  => _x('Important Downloads', 'Post type general name', 'himachal-dynamic-dashboard'),
            'singular_name'         => _x('Important Download', 'Post type singular name', 'himachal-dynamic-dashboard'),
            'menu_name'             => _x('Important Downloads', 'Admin Menu text', 'himachal-dynamic-dashboard'),
            'add_new'               => __('Add New', 'himachal-dynamic-dashboard'),
            'add_new_item'          => __('Add New Download', 'himachal-dynamic-dashboard'),
            'edit_item'             => __('Edit Download', 'himachal-dynamic-dashboard'),
            'all_items'             => __('All Downloads', 'himachal-dynamic-dashboard'),
            'search_items'          => __('Search Downloads', 'himachal-dynamic-dashboard'),
            'not_found'             => __('No downloads found.', 'himachal-dynamic-dashboard'),
            'not_found_in_trash'    => __('No downloads found in Trash.', 'himachal-dynamic-dashboard'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => array('title'),
            'show_in_rest'       => true,
        );

        register_post_type('important_download', $args);
    }
}
