<?php
/**
 * Redirect memory for intentional slug migrations.
 *
 * @package Hindi_To_Lat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Hindi_To_Lat_Redirect_Store {

	/**
	 * Install the redirect table without changing any existing URL.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			old_hash char(64) NOT NULL,
			old_path text NOT NULL,
			new_url text NOT NULL,
			object_type varchar(32) NOT NULL DEFAULT '',
			object_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY old_hash (old_hash),
			KEY object_lookup (object_type, object_id)
		) {$charset};";

		dbDelta( $sql );
	}

	/**
	 * Remember a one-hop redirect.
	 *
	 * @param string $old_url     Previous URL.
	 * @param string $new_url     New canonical URL.
	 * @param string $object_type Object type.
	 * @param int    $object_id   Object ID.
	 * @return bool
	 */
	public function remember( $old_url, $new_url, $object_type = '', $object_id = 0 ) {
		global $wpdb;

		$old_path = $this->normalize_path_from_url( $old_url );
		$new_url  = esc_url_raw( $new_url );

		if ( '' === $old_path || '' === $new_url || $this->normalize_path_from_url( $new_url ) === $old_path ) {
			return false;
		}

		$result = $wpdb->replace(
			self::table_name(),
			array(
				'old_hash'    => hash( 'sha256', $old_path ),
				'old_path'    => $old_path,
				'new_url'     => $new_url,
				'object_type' => sanitize_key( $object_type ),
				'object_id'   => absint( $object_id ),
				'created_at'  => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Redirect only unresolved front-end GET/HEAD requests.
	 *
	 * @return void
	 */
	public function maybe_redirect() {
		if ( is_admin() || ! is_404() || headers_sent() ) {
			return;
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
		if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path        = $this->normalize_path_from_url( $request_uri );
		if ( '' === $path ) {
			return;
		}

		global $wpdb;
		$new_url = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT new_url FROM ' . self::table_name() . ' WHERE old_hash = %s LIMIT 1',
				hash( 'sha256', $path )
			)
		);

		if ( ! is_string( $new_url ) || '' === $new_url ) {
			return;
		}

		wp_safe_redirect( $new_url, 301, 'Hindi-To-Lat' );
		exit;
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'hindi_to_lat_redirects';
	}

	/**
	 * @param string $url URL or request path.
	 * @return string
	 */
	private function normalize_path_from_url( $url ) {
		$url  = (string) $url;
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! is_string( $path ) || '' === $path ) {
			return '';
		}

		$path = '/' . ltrim( rawurldecode( $path ), '/' );
		return untrailingslashit( $path );
	}
}
