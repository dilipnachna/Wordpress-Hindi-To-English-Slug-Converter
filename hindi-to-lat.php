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
define( 'HINDI_TO_LAT_DB_VERSION', '2.0.0' );
define( 'HINDI_TO_LAT_FILE', __FILE__ );
define( 'HINDI_TO_LAT_DIR', plugin_dir_path( __FILE__ ) );

require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-transliterator.php';
require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-redirect-store.php';
require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-migration.php';
require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-admin.php';
require_once HINDI_TO_LAT_DIR . 'includes/class-hindi-to-lat-plugin.php';

/**
 * Install/update plugin-owned storage and defaults.
 *
 * This routine never changes post, page, term, or taxonomy slugs.
 *
 * @return void
 */
function hindi_to_lat_install() {
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

	update_option( 'hindi_to_lat_db_version', HINDI_TO_LAT_DB_VERSION, false );
}

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
	hindi_to_lat_install();
}
register_activation_hook( __FILE__, 'hindi_to_lat_activate' );

/**
 * Existing v1 installations do not re-run an activation hook when WordPress
 * updates plugin files. Ensure the v2 redirect-memory table is therefore
 * created through a versioned, idempotent upgrade check.
 *
 * @return void
 */
function hindi_to_lat_maybe_upgrade() {
	$installed = (string) get_option( 'hindi_to_lat_db_version', '' );
	if ( HINDI_TO_LAT_DB_VERSION !== $installed ) {
		hindi_to_lat_install();
	}
}
add_action( 'plugins_loaded', 'hindi_to_lat_maybe_upgrade', 5 );

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
 * Start runtime after storage upgrades are complete.
 *
 * @return void
 */
function hindi_to_lat_boot() {
	Hindi_To_Lat_Plugin::instance();
}
add_action( 'plugins_loaded', 'hindi_to_lat_boot', 10 );
