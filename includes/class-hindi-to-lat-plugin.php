<?php
/**
 * Main plugin runtime.
 *
 * @package Hindi_To_Lat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Hindi_To_Lat_Plugin {

	/** @var Hindi_To_Lat_Plugin|null */
	private static $instance = null;

	/** @var Hindi_To_Lat_Transliterator */
	private $transliterator;

	/** @var Hindi_To_Lat_Redirect_Store */
	private $redirects;

	/** @var Hindi_To_Lat_Migration */
	private $migration;

	/**
	 * @return Hindi_To_Lat_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->transliterator = new Hindi_To_Lat_Transliterator();
		$this->redirects      = new Hindi_To_Lat_Redirect_Store();
		$this->migration      = new Hindi_To_Lat_Migration( $this->transliterator, $this->redirects );

		add_filter( 'sanitize_title', array( $this, 'filter_sanitize_title' ), 9, 3 );
		add_filter( 'sanitize_file_name', array( $this, 'filter_file_name' ), 9, 2 );
		add_action( 'template_redirect', array( $this->redirects, 'maybe_redirect' ), 1 );

		if ( is_admin() ) {
			new Hindi_To_Lat_Admin( $this->transliterator, $this->migration );
		}
	}

	/**
	 * Transliterate only when WordPress is saving a title/slug and only when
	 * Devanagari is actually present. Existing Latin/manual slugs pass through.
	 *
	 * @param string $title     Sanitized title.
	 * @param string $raw_title Raw title.
	 * @param string $context   Sanitization context.
	 * @return string
	 */
	public function filter_sanitize_title( $title, $raw_title = '', $context = 'save' ) {
		if ( 'save' !== $context ) {
			return $title;
		}

		$source = '' !== (string) $raw_title ? (string) $raw_title : (string) $title;
		if ( ! $this->transliterator->contains_devanagari( $source ) ) {
			return $title;
		}

		$dictionary = $this->dictionary();
		$latin      = $this->transliterator->transliterate( $source, $dictionary );
		$latin      = sanitize_title_with_dashes( $latin, '', 'save' );

		return '' !== $latin ? $latin : $title;
	}

	/**
	 * Transliterate the filename stem while leaving WordPress in charge of
	 * final filename sanitization and uniqueness.
	 *
	 * @param string $filename     Sanitized filename.
	 * @param string $filename_raw Original filename.
	 * @return string
	 */
	public function filter_file_name( $filename, $filename_raw = '' ) {
		$settings = get_option( 'hindi_to_lat_settings', array() );
		if ( isset( $settings['filenames'] ) && ! (bool) $settings['filenames'] ) {
			return $filename;
		}

		$source = '' !== (string) $filename_raw ? (string) $filename_raw : (string) $filename;
		if ( ! $this->transliterator->contains_devanagari( $source ) ) {
			return $filename;
		}

		$info      = pathinfo( $source );
		$stem      = isset( $info['filename'] ) ? $info['filename'] : $source;
		$extension = isset( $info['extension'] ) ? strtolower( $info['extension'] ) : '';
		$stem      = $this->transliterator->transliterate( $stem, $this->dictionary() );
		$stem      = sanitize_title_with_dashes( $stem, '', 'save' );

		if ( '' === $stem ) {
			return $filename;
		}

		return '' !== $extension ? $stem . '.' . sanitize_key( $extension ) : $stem;
	}

	/**
	 * Public helper for legacy integrations and tests.
	 *
	 * @param string $text Text to transliterate.
	 * @return string
	 */
	public function transliterate( $text ) {
		return $this->transliterator->transliterate( $text, $this->dictionary() );
	}

	/** @return array */
	private function dictionary() {
		$dictionary = get_option( 'hindi_to_lat_dictionary', array() );
		$dictionary = is_array( $dictionary ) ? $dictionary : array();

		// Backward compatibility: older integrations used ctl_table to alter
		// Hindi => Latin replacements. In v2 it is treated as a custom dictionary.
		$dictionary = apply_filters( 'ctl_table', $dictionary );
		$dictionary = apply_filters( 'hindi_to_lat_dictionary', $dictionary );

		return is_array( $dictionary ) ? $dictionary : array();
	}
}
