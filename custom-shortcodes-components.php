<?php

/**
 * Plugin Name:       Custom Shortcodes
 * Plugin URI:        https://example.com/custom-shortcodes
 * Description:       A dynamic content management framework for Himachal Pradesh demographic dashboards with interactive SVG map, continuous marquee slider, schemes and services, and success stories.
 * Version:           1.1.0
 * Author:            Custom Shortcodes
 * Text Domain:       custom-shortcodes-components
 *
 * @package           CustomShortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'CSC_VERSION', '1.1.0' );
define( 'CSC_PATH', plugin_dir_path( __FILE__ ) );
define( 'CSC_URL', plugin_dir_url( __FILE__ ) );
define( 'CSC_BASENAME', plugin_basename( __FILE__ ) );

// Aliases for Himachal Dynamic Content Manager
if ( ! defined( 'HDCM_VERSION' ) ) {
	define( 'HDCM_VERSION', CSC_VERSION );
	define( 'HDCM_PATH', CSC_PATH );
	define( 'HDCM_URL', CSC_URL );
	define( 'HDCM_BASENAME', CSC_BASENAME );
}

// Aliases for backward compatibility with HDD constants
if ( ! defined( 'HDD_VERSION' ) ) {
	define( 'HDD_VERSION', CSC_VERSION );
	define( 'HDD_PATH', CSC_PATH );
	define( 'HDD_URL', CSC_URL );
	define( 'HDD_BASENAME', CSC_BASENAME );
}

/**
 * Class CSC_Plugin
 * Handles automatic loading of custom PHP files and automatic enqueuing of CSS/JS assets.
 */
final class CSC_Plugin {

	/**
	 * Directories to automatically scan and load PHP files from.
	 *
	 * @var array
	 */
	private static $php_directories = array(
		'includes/functions',
		'includes/components',
		'includes/taxonomies',
		'includes/shortcodes',
		'admin',
	);

	/**
	 * Run the plugin bootstrap.
	 */
	public static function init() {
		// Automatically load PHP files.
		self::load_php_files();

		// Automatically enqueue frontend CSS & JS assets.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Automatically scan and include PHP files from the defined includes directories.
	 */
	public static function load_php_files() {
		foreach ( self::$php_directories as $rel_dir ) {
			$dir_path = CSC_PATH . $rel_dir;

			if ( ! is_dir( $dir_path ) ) {
				continue;
			}

			self::require_php_files_in_directory( $dir_path );
		}
	}

	/**
	 * Safely scan a directory (and any subdirectories) and include all .php files.
	 *
	 * @param string $directory Absolute directory path.
	 */
	private static function require_php_files_in_directory( $directory ) {
		try {
			$directory_iterator = new RecursiveDirectoryIterator(
				$directory,
				FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
			);
			$iterator = new RecursiveIteratorIterator( $directory_iterator );

			foreach ( $iterator as $file_info ) {
				if ( $file_info->isFile() && 'php' === $file_info->getExtension() ) {
					require_once $file_info->getRealPath();
				}
			}
		} catch ( Exception $e ) {
			// Fail silently to prevent fatal errors during directory iteration.
		}
	}

	/**
	 * Automatically enqueue all CSS and JS assets found in the assets directory.
	 * Only enqueued on the frontend with dynamic filemtime cache busting.
	 */
	public static function enqueue_assets() {
		self::enqueue_css_assets();
		self::enqueue_js_assets();
	}

	/**
	 * Scan assets/css/ and automatically enqueue all .css files.
	 */
	private static function enqueue_css_assets() {
		$css_dir = CSC_PATH . 'assets/css';

		if ( ! is_dir( $css_dir ) ) {
			return;
		}

		$files = glob( $css_dir . '/*.css' );

		if ( empty( $files ) || ! is_array( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			if ( ! is_file( $file ) ) {
				continue;
			}

			$filename = basename( $file, '.css' );
			$handle   = 'csc-' . sanitize_title( $filename );
			$src      = CSC_URL . 'assets/css/' . basename( $file );

			$version = defined( 'CSC_DEV_MODE' ) && CSC_DEV_MODE
				? time()
				: filemtime( $file );

			wp_enqueue_style(
				$handle,
				$src,
				array(),
				$version
			);
		}
	}

	/**
	 * Scan assets/js/ and automatically enqueue all .js files in footer.
	 */
	private static function enqueue_js_assets() {
		$js_dir = CSC_PATH . 'assets/js';

		if ( ! is_dir( $js_dir ) ) {
			return;
		}

		$files = glob( $js_dir . '/*.js' );
		if ( empty( $files ) || ! is_array( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			if ( ! is_file( $file ) ) {
				continue;
			}

			$filename = basename( $file, '.js' );
			$handle   = 'csc-' . sanitize_title( $filename );
			$src      = CSC_URL . 'assets/js/' . basename( $file );
			$version  = filemtime( $file );

			$deps = array();
			if ( strpos( $filename, 'swiper' ) === false && file_exists( $js_dir . '/swiper-bundle.min.js' ) ) {
				$deps[] = 'csc-swiper-bundle-min';
			}

			wp_enqueue_script( $handle, $src, $deps, $version, true );

			// Ensure localized parameters are provided for HP Map script
			if ( 'hp-map' === $filename || 'dashboard' === $filename ) {
				$map_params = array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( 'hp_map_nonce' ),
					'format_indian' => true,
					'ajax_action'   => 'hp_map_get_category_data',
				);
				wp_localize_script( $handle, 'hpMapParams', $map_params );
				wp_localize_script( $handle, 'hddDashboardParams', $map_params );
			}

			// Localize Schemes Slider script with Scheme Logo & Category Settings
			if ( 'schemes-slider' === $filename ) {
				$settings = function_exists( 'csc_get_scheme_settings' ) ? csc_get_scheme_settings() : array();
				$schemes_params = array(
					'ajax_url'            => admin_url( 'admin-ajax.php' ),
					'nonce'               => wp_create_nonce( 'csc_schemes_nonce' ),
					'logo1_selector'      => ! empty( $settings['logo1_selector'] ) ? $settings['logo1_selector'] : '.csc-header-logo-1, .header-logo-1, .site-header .custom-logo-link:first-of-type, .elementor-widget-theme-site-logo:first-of-type a, [data-scheme-logo="1"], [data-scheme-logo="primary"], .logo-1, #logo-1',
					'logo1_category'      => ! empty( $settings['logo1_category'] ) ? $settings['logo1_category'] : 'demographic-spread-of-minorities',
					'logo1_name'          => ! empty( $settings['logo1_name'] ) ? $settings['logo1_name'] : __( 'Demographic Spread Of Minorities', 'custom-shortcodes-components' ),
					'logo2_selector'      => ! empty( $settings['logo2_selector'] ) ? $settings['logo2_selector'] : '.csc-header-logo-2, .header-logo-2, .site-header .custom-logo-link:last-of-type, .elementor-widget-theme-site-logo:last-of-type a, [data-scheme-logo="2"], [data-scheme-logo="secondary"], .logo-2, #logo-2',
					'logo2_category'      => ! empty( $settings['logo2_category'] ) ? $settings['logo2_category'] : 'disabilities-in-hp',
					'logo2_name'          => ! empty( $settings['logo2_name'] ) ? $settings['logo2_name'] : __( 'Disabilities In H.P', 'custom-shortcodes-components' ),
					'active_class'        => ! empty( $settings['active_class'] ) ? $settings['active_class'] : 'is-active',
					'enable_color_switch' => isset( $settings['enable_color_switch'] ) ? $settings['enable_color_switch'] : 'yes',
					'enable_storage'      => isset( $settings['enable_storage'] ) ? $settings['enable_storage'] : 'yes',
					'enable_url_param'    => isset( $settings['enable_url_param'] ) ? $settings['enable_url_param'] : 'yes',
					'prevent_navigation'  => isset( $settings['prevent_navigation'] ) ? $settings['prevent_navigation'] : 'yes',
					'empty_message'       => __( 'No schemes available in this category.', 'custom-shortcodes-components' ),
				);
				wp_localize_script( $handle, 'cscSchemesParams', $schemes_params );
			}
		}
	}

	/**
	 * Output early head script to prevent FOUC when secondary scheme is persisted.
	 */
	public static function render_early_scheme_head_script() {
		$settings = function_exists( 'csc_get_scheme_settings' ) ? csc_get_scheme_settings() : array();
		$logo2_cat = ! empty( $settings['logo2_category'] ) ? $settings['logo2_category'] : 'disabilities-in-hp';
		?>
		<script>
		(function(){
			try {
				var savedLogo = localStorage.getItem('csc_active_scheme_logo');
				var urlParams = new URLSearchParams(window.location.search);
				var urlCategory = urlParams.get('scheme_category') || urlParams.get('category');
				var secCat = '<?php echo esc_js( $logo2_cat ); ?>';
				if (savedLogo === '2' || (urlCategory && secCat && urlCategory === secCat)) {
					document.documentElement.classList.add('scheme-secondary-active');
				} else {
					document.documentElement.classList.add('scheme-primary-active');
				}
			} catch(e){}
		})();
		</script>
		<?php
	}
}

// Add early head script hook.
add_action( 'wp_head', array( 'CSC_Plugin', 'render_early_scheme_head_script' ), 1 );

// Bootstrap plugin.
CSC_Plugin::init();

