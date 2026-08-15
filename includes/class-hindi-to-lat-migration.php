<?php
/**
 * Explicit migration service for legacy Hindi slugs.
 *
 * @package Hindi_To_Lat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Hindi_To_Lat_Migration {

	/** @var Hindi_To_Lat_Transliterator */
	private $transliterator;

	/** @var Hindi_To_Lat_Redirect_Store */
	private $redirects;

	public function __construct( Hindi_To_Lat_Transliterator $transliterator, Hindi_To_Lat_Redirect_Store $redirects ) {
		$this->transliterator = $transliterator;
		$this->redirects      = $redirects;
	}

	/**
	 * Preview a bounded batch. This never mutates URLs.
	 *
	 * @param int $limit Maximum candidates.
	 * @return array
	 */
	public function preview( $limit = 50 ) {
		$limit      = max( 1, min( 200, absint( $limit ) ) );
		$candidates = array();

		foreach ( $this->post_candidates( $limit ) as $candidate ) {
			$candidates[] = $candidate;
			if ( count( $candidates ) >= $limit ) {
				return $candidates;
			}
		}

		foreach ( $this->term_candidates( $limit - count( $candidates ) ) as $candidate ) {
			$candidates[] = $candidate;
		}

		return $candidates;
	}

	/**
	 * Convert a reviewed batch by opaque candidate keys.
	 *
	 * @param array $keys Candidate keys from preview.
	 * @return array Result counters.
	 */
	public function convert( array $keys ) {
		$result = array(
			'converted' => 0,
			'skipped'   => 0,
			'errors'    => array(),
		);

		foreach ( array_slice( array_unique( array_map( 'sanitize_text_field', $keys ) ), 0, 50 ) as $key ) {
			if ( 0 === strpos( $key, 'post:' ) ) {
				$id      = absint( substr( $key, 5 ) );
				$outcome = $this->convert_post( $id );
			} elseif ( 0 === strpos( $key, 'term:' ) ) {
				$parts    = explode( ':', $key, 3 );
				$term_id  = isset( $parts[1] ) ? absint( $parts[1] ) : 0;
				$taxonomy = isset( $parts[2] ) ? sanitize_key( $parts[2] ) : '';
				$outcome  = $this->convert_term( $term_id, $taxonomy );
			} else {
				$outcome = new WP_Error( 'invalid_candidate', __( 'Unknown migration candidate.', 'hindi-to-lat' ) );
			}

			if ( is_wp_error( $outcome ) ) {
				$result['errors'][] = $outcome->get_error_message();
			} elseif ( true === $outcome ) {
				$result['converted']++;
			} else {
				$result['skipped']++;
			}
		}

		return $result;
	}

	/** @return array */
	private function post_candidates( $limit ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_name, post_type, post_status FROM {$wpdb->posts} WHERE post_name <> '' AND post_type <> 'revision' AND post_status NOT IN ('auto-draft','trash') ORDER BY ID ASC LIMIT %d",
				max( 100, $limit * 10 )
			)
		);

		$out = array();
		foreach ( (array) $rows as $row ) {
			$decoded = rawurldecode( (string) $row->post_name );
			if ( ! $this->transliterator->contains_devanagari( $decoded ) ) {
				continue;
			}

			$new_slug = $this->make_slug( $decoded );
			if ( '' === $new_slug || $new_slug === $decoded ) {
				continue;
			}

			$out[] = array(
				'key'       => 'post:' . absint( $row->ID ),
				'type'      => 'post',
				'id'        => absint( $row->ID ),
				'label'     => get_the_title( $row->ID ),
				'old_slug'  => $decoded,
				'new_slug'  => $new_slug,
				'old_url'   => get_permalink( $row->ID ),
				'post_type' => sanitize_key( $row->post_type ),
			);

			if ( count( $out ) >= $limit ) {
				break;
			}
		}

		return $out;
	}

	/** @return array */
	private function term_candidates( $limit ) {
		if ( $limit <= 0 ) {
			return array();
		}

		$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		if ( empty( $taxonomies ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => array_values( $taxonomies ),
				'hide_empty' => false,
				'number'     => max( 100, $limit * 10 ),
				'orderby'    => 'term_id',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$out = array();
		foreach ( $terms as $term ) {
			$decoded = rawurldecode( (string) $term->slug );
			if ( ! $this->transliterator->contains_devanagari( $decoded ) ) {
				continue;
			}

			$new_slug = $this->make_slug( $decoded );
			if ( '' === $new_slug || $new_slug === $decoded ) {
				continue;
			}

			$old_url = get_term_link( $term );
			if ( is_wp_error( $old_url ) ) {
				$old_url = '';
			}

			$out[] = array(
				'key'      => 'term:' . absint( $term->term_id ) . ':' . sanitize_key( $term->taxonomy ),
				'type'     => 'term',
				'id'       => absint( $term->term_id ),
				'label'    => $term->name,
				'old_slug' => $decoded,
				'new_slug' => $new_slug,
				'old_url'  => $old_url,
				'taxonomy' => sanitize_key( $term->taxonomy ),
			);

			if ( count( $out ) >= $limit ) {
				break;
			}
		}

		return $out;
	}

	/** @return bool|WP_Error */
	private function convert_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'revision' === $post->post_type ) {
			return new WP_Error( 'missing_post', __( 'Post no longer exists.', 'hindi-to-lat' ) );
		}

		$decoded = rawurldecode( (string) $post->post_name );
		if ( ! $this->transliterator->contains_devanagari( $decoded ) ) {
			return false;
		}

		$new_slug = $this->make_slug( $decoded );
		if ( '' === $new_slug ) {
			return new WP_Error( 'empty_slug', __( 'Transliteration produced an empty slug.', 'hindi-to-lat' ) );
		}

		$old_url = get_permalink( $post_id );
		$updated = wp_update_post(
			array(
				'ID'        => $post_id,
				'post_name' => $new_slug,
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		$new_url = get_permalink( $post_id );
		$this->redirects->remember( $old_url, $new_url, 'post', $post_id );
		return true;
	}

	/** @return bool|WP_Error */
	private function convert_term( $term_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy );
		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'missing_term', __( 'Term no longer exists.', 'hindi-to-lat' ) );
		}

		$decoded = rawurldecode( (string) $term->slug );
		if ( ! $this->transliterator->contains_devanagari( $decoded ) ) {
			return false;
		}

		$new_slug = $this->make_slug( $decoded );
		if ( '' === $new_slug ) {
			return new WP_Error( 'empty_slug', __( 'Transliteration produced an empty slug.', 'hindi-to-lat' ) );
		}

		$old_url = get_term_link( $term );
		$updated = wp_update_term( $term_id, $taxonomy, array( 'slug' => $new_slug ) );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		$new_term = get_term( $term_id, $taxonomy );
		$new_url  = $new_term && ! is_wp_error( $new_term ) ? get_term_link( $new_term ) : '';
		if ( ! is_wp_error( $old_url ) && ! is_wp_error( $new_url ) ) {
			$this->redirects->remember( $old_url, $new_url, 'term', $term_id );
		}
		return true;
	}

	/** @return string */
	private function make_slug( $text ) {
		$dictionary = get_option( 'hindi_to_lat_dictionary', array() );
		$dictionary = is_array( $dictionary ) ? $dictionary : array();
		$latin      = $this->transliterator->transliterate( $text, $dictionary );
		return sanitize_title_with_dashes( $latin, '', 'save' );
	}
}
