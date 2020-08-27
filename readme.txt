=== Hindi-To-Lat ===
Contributors: Dilip Soni, BArS, SergeyBiryukov, karevn, webvitaly,Alexander Butyuhin
Tags: hindi to lat,hi2lat, hindi to latin, cyr2lat, slugs, translations, transliteration, cyrillic, Hindi Slug,Hindi Permalink, Hinglish Slug, Hindi to Hinglish,
Requires at least: 4.6
Tested up to: 5.4.2
Stable tag: 1.3.5
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Converts Hindi characters in post, page and term slugs to Latin characters.

== Description ==

Converts Hindi characters in post, page and term slugs to Latin characters. Useful for creating human-readable URLs.

= Features =
* Automatically converts existing post, page and term slugs on activation
* Saves existing post and page permalinks integrity
* Performs transliteration of attachment file names
* Includes just Ukrainian characters
* Transliteration table can be customized without editing the plugin itself

Based on the original Rus-To-Lat plugin by Anton Skorobogatov and Cyr-To-Lat by SergeyBiryukov, karevn, webvitaly.

== Installation ==

1. Upload `hindi-to-lat` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

= Translations =

You can [translate hindi-To-Lat](https://translate.wordpress.org/projects/wp-plugins/hindi-to-lat) on [__translate.wordpress.org__]().

== Frequently Asked Questions ==

= How can I define my own substitutions? =

Add this code to your theme's `functions.php` file:
`
function my_cyr_to_lat_table($ctl_table) {
   $ctl_table['ะช'] = 'U';
   $ctl_table['ั'] = 'u';
   return $ctl_table;
}
add_filter('ctl_table', 'my_cyr_to_lat_table');
`

= How to redirect old link to new? =

To prevent losing you SEO position you can use plugin LCH (https://wordpress.org/plugins/link-changer-htaccess-for-better-seo/) to prepare redirect from old links to new one.

== Upgrade Notice ==

None

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png

== Changelog ==

= 1.0 =
* Initial release
