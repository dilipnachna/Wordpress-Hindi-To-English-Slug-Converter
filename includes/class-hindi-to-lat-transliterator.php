<?php
/**
 * Deterministic Devanagari transliteration for URL-safe Hindi slugs.
 *
 * @package Hindi_To_Lat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Hindi_To_Lat_Transliterator {

	/**
	 * Transliterate Hindi/Devanagari text to readable ASCII Latin text.
	 *
	 * The engine intentionally favours stable URL output over academic
	 * transliteration. A small dictionary handles common Hindi forms where
	 * automatic schwa deletion would otherwise be ambiguous.
	 *
	 * @param string $text              Input text.
	 * @param array  $custom_dictionary Optional exact replacements.
	 * @return string
	 */
	public function transliterate( $text, array $custom_dictionary = array() ) {
		if ( ! is_string( $text ) || '' === $text || ! $this->contains_devanagari( $text ) ) {
			return (string) $text;
		}

		$dictionary = array_merge( $this->default_dictionary(), $custom_dictionary );
		if ( ! empty( $dictionary ) ) {
			// Longest keys first so specific phrases win over shorter words.
			uksort(
				$dictionary,
				static function ( $a, $b ) {
					return strlen( $b ) <=> strlen( $a );
				}
			);
			$text = strtr( $text, $dictionary );
		}

		// Normalize nukta combinations before tokenization.
		$text = strtr(
			$text,
			array(
				'क़' => 'क़',
				'ख़' => 'ख़',
				'ग़' => 'ग़',
				'ज़' => 'ज़',
				'ड़' => 'ड़',
				'ढ़' => 'ढ़',
				'फ़' => 'फ़',
				'य़' => 'य़',
			)
		);

		$chars      = preg_split( '//u', $text, -1, PREG_SPLIT_NO_EMPTY );
		$consonants = $this->consonants();
		$vowels     = $this->independent_vowels();
		$matras     = $this->matras();
		$output     = '';
		$count      = is_array( $chars ) ? count( $chars ) : 0;

		for ( $i = 0; $i < $count; $i++ ) {
			$char = $chars[ $i ];

			if ( isset( $consonants[ $char ] ) ) {
				$latin = $consonants[ $char ];
				$next  = ( $i + 1 < $count ) ? $chars[ $i + 1 ] : '';

				if ( '्' === $next ) {
					$output .= $latin;
					$i++;
					continue;
				}

				if ( isset( $matras[ $next ] ) ) {
					$output .= $latin . $matras[ $next ];
					$i++;
					continue;
				}

				$output .= $latin . 'a';
				continue;
			}

			if ( isset( $vowels[ $char ] ) ) {
				$output .= $vowels[ $char ];
				continue;
			}

			if ( isset( $matras[ $char ] ) ) {
				$output .= $matras[ $char ];
				continue;
			}

			switch ( $char ) {
				case 'ं':
				case 'ँ':
					$output .= 'n';
					break;
				case 'ः':
					$output .= 'h';
					break;
				case 'ऽ':
					break;
				case 'ॐ':
					$output .= 'om';
					break;
				case '०': $output .= '0'; break;
				case '१': $output .= '1'; break;
				case '२': $output .= '2'; break;
				case '३': $output .= '3'; break;
				case '४': $output .= '4'; break;
				case '५': $output .= '5'; break;
				case '६': $output .= '6'; break;
				case '७': $output .= '7'; break;
				case '८': $output .= '8'; break;
				case '९': $output .= '9'; break;
				default:
					$output .= $char;
					break;
			}
		}

		// Compact long-vowel notation for human-readable web slugs.
		$output = str_replace( array( 'aa', 'ii', 'uu' ), array( 'a', 'i', 'u' ), $output );

		// Hindi commonly drops a final inherent schwa.
		$output = preg_replace( '/a(?=($|[\s\-_.\/,;:!?]))/i', '', $output );

		return strtolower( (string) $output );
	}

	/**
	 * Check whether text contains Devanagari code points.
	 *
	 * @param string $text Text to test.
	 * @return bool
	 */
	public function contains_devanagari( $text ) {
		return 1 === preg_match( '/[\x{0900}-\x{097F}]/u', (string) $text );
	}

	/**
	 * Common high-confidence replacements.
	 *
	 * @return array
	 */
	private function default_dictionary() {
		return array(
			'जैसलमेर'      => 'jaisalmer',
			'राजस्थान'      => 'rajasthan',
			'हिन्दी'        => 'hindi',
			'हिंदी'         => 'hindi',
			'भारत'          => 'bharat',
			'प्रधानमंत्री'  => 'pradhanmantri',
			'मुख्यमंत्री'   => 'mukhyamantri',
			'समाचार'        => 'samachar',
			'दिल्ली'        => 'delhi',
			'मुंबई'         => 'mumbai',
			'कोलकाता'       => 'kolkata',
			'चेन्नई'        => 'chennai',
			'उत्तर प्रदेश'   => 'uttar-pradesh',
			'मध्य प्रदेश'    => 'madhya-pradesh',
		);
	}

	/** @return array */
	private function consonants() {
		return array(
			'क' => 'k',  'ख' => 'kh', 'ग' => 'g',  'घ' => 'gh', 'ङ' => 'ng',
			'च' => 'ch', 'छ' => 'chh','ज' => 'j',  'झ' => 'jh', 'ञ' => 'ny',
			'ट' => 't',  'ठ' => 'th', 'ड' => 'd',  'ढ' => 'dh', 'ण' => 'n',
			'त' => 't',  'थ' => 'th', 'द' => 'd',  'ध' => 'dh', 'न' => 'n',
			'प' => 'p',  'फ' => 'ph', 'ब' => 'b',  'भ' => 'bh', 'म' => 'm',
			'य' => 'y',  'र' => 'r',  'ल' => 'l',  'व' => 'v',
			'श' => 'sh', 'ष' => 'sh', 'स' => 's',  'ह' => 'h',
			'ळ' => 'l',  'ऩ' => 'n',  'ऱ' => 'r',  'ऴ' => 'l',
			'क़' => 'q',  'ख़' => 'kh', 'ग़' => 'gh', 'ज़' => 'z',
			'ड़' => 'd',  'ढ़' => 'dh', 'फ़' => 'f',  'य़' => 'y',
		);
	}

	/** @return array */
	private function independent_vowels() {
		return array(
			'अ' => 'a', 'आ' => 'a', 'इ' => 'i', 'ई' => 'i',
			'उ' => 'u', 'ऊ' => 'u', 'ऋ' => 'ri','ॠ' => 'ri',
			'ऌ' => 'li','ॡ' => 'li','ए' => 'e', 'ऐ' => 'ai',
			'ओ' => 'o', 'औ' => 'au','ऍ' => 'e', 'ऑ' => 'o',
			'ऎ' => 'e', 'ऒ' => 'o',
		);
	}

	/** @return array */
	private function matras() {
		return array(
			'ा' => 'a', 'ि' => 'i', 'ी' => 'i', 'ु' => 'u', 'ू' => 'u',
			'ृ' => 'ri','ॄ' => 'ri','ॢ' => 'li','ॣ' => 'li','े' => 'e',
			'ै' => 'ai','ो' => 'o', 'ौ' => 'au','ॅ' => 'e', 'ॉ' => 'o',
			'ॆ' => 'e', 'ॊ' => 'o',
		);
	}
}
