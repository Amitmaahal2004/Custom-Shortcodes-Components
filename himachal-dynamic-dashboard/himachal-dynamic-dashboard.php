<?php
/**
 * Plugin Name:       Himachal Dynamic Dashboard
 * Plugin URI:        https://example.com/himachal-dynamic-dashboard
 * Description:       Dynamic Himachal Pradesh minority & demographic dashboard with interactive SVG map, dynamic tabs, announcements, and important downloads.
 * Version:           1.0.0
 * Author:            Himachal Dashboard Team
 * Text Domain:       himachal-dynamic-dashboard
 * Domain Path:       /languages
 * Requires at least: 5.6
 * Requires PHP:      7.4
 *
 * @package           HimachalDynamicDashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

// If already running inside Himachal Dynamic Content Manager, do not duplicate registrations.
if ( defined( 'CSC_VERSION' ) ) {
    return;
}

// Define plugin constants if not already defined.
if ( ! defined( 'HDD_VERSION' ) ) {
    define( 'HDD_VERSION', '1.0.0' );
}
if ( ! defined( 'HDD_PATH' ) ) {
    define( 'HDD_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'HDD_URL' ) ) {
    define( 'HDD_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'HDD_BASENAME' ) ) {
    define( 'HDD_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Main Plugin Class
 */
final class Himachal_Dynamic_Dashboard {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load PHP dependencies
     */
    private function load_dependencies() {
        require_once HDD_PATH . 'includes/class-cpt.php';
        require_once HDD_PATH . 'includes/class-taxonomies.php';
        require_once HDD_PATH . 'includes/class-fields.php';
        require_once HDD_PATH . 'includes/class-admin.php';
        require_once HDD_PATH . 'includes/class-map.php';
        require_once HDD_PATH . 'includes/class-shortcode.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        HDD_CPT::init();
        HDD_Taxonomies::init();
        HDD_Fields::init();
        HDD_Admin::init();
        HDD_Shortcode::init();
    }

    /**
     * Plugin Activation Routine
     */
    public static function activate() {
        require_once HDD_PATH . 'includes/class-cpt.php';
        require_once HDD_PATH . 'includes/class-taxonomies.php';
        require_once HDD_PATH . 'includes/class-admin.php';

        HDD_CPT::register_post_types();
        HDD_Taxonomies::register_taxonomies();
        flush_rewrite_rules();

        // Seed demo data if no districts exist yet
        $existing = get_posts(array(
            'post_type'   => 'district_map',
            'post_status' => 'any',
            'numberposts' => 1,
        ));

        if (empty($existing)) {
            HDD_Admin::seed_demo_data();
        }
    }

    /**
     * Plugin Deactivation Routine
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }
}

// Register activation & deactivation hooks
register_activation_hook(__FILE__, array('Himachal_Dynamic_Dashboard', 'activate'));
register_deactivation_hook(__FILE__, array('Himachal_Dynamic_Dashboard', 'deactivate'));

// Bootstrap the plugin
Himachal_Dynamic_Dashboard::get_instance();
