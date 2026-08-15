=== Hindi To Lat ===
Contributors: dilipnachna
Tags: hindi to latin, hindi slug, devanagari, transliteration, permalink, hinglish slug
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create readable Latin slugs from Hindi/Devanagari text without silently rewriting existing published URLs.

== Description ==

Hindi To Lat 2.0 is a safety-first rewrite of the original plugin.

The plugin transliterates Devanagari when WordPress creates or explicitly saves a new slug. Existing published URLs are not bulk-converted when the plugin is activated.

= Version 2 safety model =

* Activation never bulk-rewrites existing posts, pages, custom post types, categories, tags, or terms.
* New Hindi slugs are transliterated to deterministic ASCII Latin slugs.
* Manually entered Latin slugs are left unchanged.
* Hindi upload filenames can be transliterated independently.
* Existing Hindi URLs can be reviewed in Tools > Hindi To Lat Migration.
* Migration is opt-in, checkbox-based, nonce-protected, and limited to reviewed batches.
* When a selected URL changes, the plugin records a one-hop 301 redirect in its own redirect-memory table.
* Redirect memory is used only when the old request is unresolved (404), so it does not override valid WordPress content.
* WordPress APIs are used for post and term updates so normal uniqueness, hooks, cache handling, and old-slug behaviour remain available.

= Custom dictionary =

Go to Settings > Hindi To Lat and add exact replacements, one per line:

`जैसलमेर = jaisalmer`
`राजस्थान = rajasthan`
`प्रधानमंत्री = prime-minister`

The custom dictionary runs before automatic Devanagari transliteration.

== Installation ==

1. Back up your WordPress database before installing development builds.
2. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP from Plugins > Add New.
3. Activate Hindi To Lat.
4. New Hindi slugs will be transliterated automatically.
5. Existing URLs stay unchanged until you explicitly review them under Tools > Hindi To Lat Migration.

== Frequently Asked Questions ==

= Will activating version 2 change my existing URLs? =

No. Version 2 deliberately removes the old activation-time bulk conversion behaviour.

= What happens when I migrate an old Hindi URL? =

The selected object is updated through WordPress APIs. The plugin also stores the old path and new URL in its redirect-memory table and serves a 301 only when the old path otherwise returns 404.

= Does the plugin translate Hindi words into English? =

No. Its default job is transliteration, not semantic translation. You can use the custom dictionary for selected preferred replacements.

= Does it still support the old ctl_table filter? =

Yes. Version 2 keeps `ctl_table` as a backward-compatible custom replacement filter and also provides `hindi_to_lat_dictionary`.

== Upgrade Notice ==

= 2.0.0 =
Version 2 changes legacy URL conversion from automatic-on-activation to explicit reviewed migration. Test on staging before production use.

== Changelog ==

= 2.0.0-alpha.1 =
* Rewritten as a modular WordPress plugin.
* Added deterministic Devanagari transliteration engine.
* Removed activation-time bulk URL rewriting.
* Added explicit legacy URL preview and reviewed migration tool.
* Added internal 301 redirect memory for migrated URLs.
* Replaced direct post/term database updates with WordPress APIs.
* Removed debug_backtrace-based term detection.
* Added upload filename service and custom dictionary UI.
* Preserved the legacy `ctl_hindi_to_lat_title()` helper and `ctl_table` filter.
* Added PHP 7.4/8.1/8.3 CI lint and transliteration regression tests.

= 1.0 =
* Initial release.
