<?php
/**
 * Minimal dependency-free transliteration regression tests.
 */

define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/includes/class-hindi-to-lat-transliterator.php';

$engine = new Hindi_To_Lat_Transliterator();

$cases = array(
	'जैसलमेर'       => 'jaisalmer',
	'राजस्थान'       => 'rajasthan',
	'हिंदी'          => 'hindi',
	'हिन्दी'         => 'hindi',
	'भारत'           => 'bharat',
	'प्रधानमंत्री'   => 'pradhanmantri',
	'मुख्यमंत्री'    => 'mukhyamantri',
	'नमस्ते'         => 'namaste',
	'खबर'            => 'khabar',
	'दिल्ली'         => 'delhi',
	'१२३'            => '123',
);

$failed = 0;
foreach ( $cases as $input => $expected ) {
	$actual = $engine->transliterate( $input );
	if ( $actual !== $expected ) {
		fwrite( STDERR, sprintf( "FAIL: %s => %s (expected %s)\n", $input, $actual, $expected ) );
		$failed++;
	}
}

$custom = $engine->transliterate( 'प्रधानमंत्री योजना', array( 'प्रधानमंत्री' => 'prime-minister' ) );
if ( 'prime-minister yojan' !== $custom ) {
	fwrite( STDERR, "FAIL: custom dictionary => {$custom}\n" );
	$failed++;
}

if ( $failed > 0 ) {
	exit( 1 );
}

echo "Hindi To Lat transliteration smoke tests passed.\n";
