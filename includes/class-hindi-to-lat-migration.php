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

	/**
	 * Scan posts by primary-key cursor rather than only inspecting the first
	 * rows in the table. This remains bounded but works on large publications.
	 *
	 * @param int $limit Candidate limit.
	 * @return array
	 */
	private function post_candidates( $limit ) {
		global $wpdb;

		$out       = array();
		$cursor    = 0;
		$scanned   = 0;
		$scan_cap  = 50000;
		$chunk     = 500;

		while ( count( $out ) < $limit && $scanned < $scan_cap ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_name, post_type, post_status, post_parent FROM {$wpdb->posts} WHERE ID > %d AND post_name <> '' AND post_type <> 'revision' AND post_status NOT IN ('auto-draft','trash') ORDER BY ID ASC LIMIT %d",
					$cursor,
					$chunk
				)
			);

			if ( empty( $rows ) ) {
				break;
			}

			foreach ( $rows as $row ) {
				$cursor = max( $cursor, absint( $row->ID ) );
				$scanned++;

				$decoded = rawurldecode( (string) $row->post_name );
				if ( ! $this->transliterator->contains_devanagari( $decoded ) ) {
					continue;
				}

				$new_slug = $this->make_slug( $decoded );
				if ( '' === $new_slug || $new_slug === $decoded ) {
					continue;
				}

				$suggested = wp_unique_post_slug(
					$new_slug,
					absint( $row->ID ),
					(string) $row->post_status,
					(string) $row->post_type,
					absint( $row->post_parent )
				);

				$out[] = array(
					'key'          => 'post:' . absint( $row->ID ),
					'type'         => 'post',
					'id'           => absint( $row->ID ),
					'label'        => get_the_title( $row->ID ),
					'old_slug'     => $decoded,
					'new_slug'     => $suggested,
					'base_slug'    => $new_slug,
					'collision'    => $suggested !== $new_slug,
					'old_url'      => get_permalink( $row->ID ),
					'post_type'    => sanitize_key( $row->post_type ),
				);

				if ( count( $out ) >= $limit ) {
					break 2;
				}
			}
		}

		return $out;
	}

	/**
	 * Scan public taxonomy terms by primary-key cursor.
	 *
	 * @param int $limit Candidate limit.
	 * @return array
	 */
	private function term_candidates( $limit ) {
		if ( $limit <= 0 ) {
			return array();
		}

		global $wpdb;
		$out      = array();
		$cursor   = 0;
		$scanned  = 0;
		$scan_cap = 50000;
		$chunk    = 500;

		while ( count( $out ) < $limit && $scanned < $scan_cap ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT t.term_id, t.name, t.slug, tt.taxonomy FROM {$wpdb->terms} t INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE t.term_id > %d AND t.slug <> '' ORDER BY t.term_id ASC LIMIT %d",
					$cursor,
					$chunk
				)
			);

			if ( empty( $rows ) ) {
				break;
			}

			foreach ( $rows as $row ) {
				$cursor = max( $cursor, absint( $row->term_id ) );
				$scanned++;

				$taxonomy_object = get_taxonomy( $row->taxonomy );
				if ( ! $taxonomy_object || empty( $taxonomy_object->public ) ) {
					continue;
				}

				$decoded = rawurldecode( (string) $row->slug );
				if ( ! $this->transliterator->contains_devanagari( $decoded ) ) {
					continue;
				}

				$new_slug = $this->make_slug( $decoded );
				if ( '' === $new_slug || $new_slug === $decoded ) {
					continue;
				}

				$term = get_term( absint( $row->term_id ), sanitize_key( $row->taxonomy ) );
				if ( ! $term || is_wp_error( $term ) ) {
					continue;
				}

				$suggested = wp_unique_term_slug( $new_slug, $term );
				$old_url   = get_term_link( $term );
				if ( is_wp_error( $old_url ) ) {
					$old_url = '';
				}

				$out[] = array(
					'key'       => 'term:' . absint( $row->term_id ) . ':' . sanitize_key( $row->taxonomy ),
					'type'      => 'term',
					'id'        => absint( $row->term_id ),
					'label'     => (string) $row->name,
					'old_slug'  => $decoded,
					'new_slug'  => $suggested,
					'base_slug' => $new_slug,
					'collision' => $suggested !== $new_slug,
					'old_url'   => $old_url,
					'taxonomy'  => sanitize_key( $row->taxonomy ),
				);

				if ( count( $out ) >= $limit ) {
					break 2;
				}
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

		$updated_post = get_post( $post_id );
		if ( ! $updated_post || $updated_post->post_name === $post->post_name ) {
			return false;
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
		if ( ! $new_term || is_wp_error( $new_term ) || $new_term->slug === $term->slug ) {
			return false;
		}

		$new_url = get_term_link( $new_term );
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
