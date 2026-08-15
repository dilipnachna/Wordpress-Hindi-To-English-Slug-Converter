<?php
/**
 * Plugin Name:       Hindi To Lat
 * Plugin URI:        https://github.com/dilipnachna/Wordpress-Hindi-To-English-Slug-Converter
 * Description:       Converts Hindi/Devanagari slugs and upload filenames to readable Latin text, with explicit SEO-safe legacy URL migration.
 * Version:           2.0.0-alpha.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Dilip Soni
 * Author URI:        https://dilipsoni.in
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hindi-to-lat
 *
 * @package Hindi_To_Lat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HINDI_TO_LAT_VERSION', '2.0.0-alpha.1' );
define( 'HINDI_TO_LAT_FILE', __FILE__ );
define( 'HINDI_TO_LAT_DIR', plugin_dir_path( __FILE__ ) );

require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-transliterator.php';
require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-redirect-store.php';
require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-migration.php';
require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-admin.php';
require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-plugin.php';

/**
 * Activation installs only plugin-owned storage and defaults.
 *
 * Important: v2 deliberately does NOT bulk-convert existing slugs on
 * activation. Legacy URL changes require explicit review in Tools > Hindi To
 * Lat Migration.
 *
 * @return void
 */
function hindi_to_lat_activate() {
	Hindi_To_Lat_Redirect_Store::install();

	if ( false === get_option( 'hindi_to_lat_settings', false ) ) {
		add_option(
			'hindi_to_lat_settings',
			array(
				'filenames' => 1,
			),
			'',
			false
		);
	}
}
register_activation_hook( __FILE__, 'hindi_to_lat_activate' );

/**
 * Backward-compatible helper retained for sites that called the old function.
 *
 * @param string $title Text to transliterate.
 * @return string
 */
function ctl_hindi_to_lat_title( $title ) {
	if ( ! class_exists( 'Hindi_To_Lat_Plugin' ) ) {
		return $title;
	}

	$latin = Hindi_To_Lat_Plugin::instance()->transliterate( (string) $title );
	return sanitize_title_with_dashes( $latin, '', 'save' );
}

/**
 * Start runtime after plugins are loaded.
 *
 * @return void
 */
function hindi_to_lat_boot() {
	Hindi_To_Lat_Plugin::instance();
}
add_action( 'plugins_loaded', 'hindi_to_lat_boot' );
