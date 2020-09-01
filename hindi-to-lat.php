<?php
/**
 * @package hindi-to-lat
 * @version 1.0
 */
/*
Plugin Name: hindi-to-lat
Text Domain: hindi-to-lat
Plugin URI: https://wordpress.org/plugins/hindi-to-lat
Description: Converts Hindi characters in post and term slugs to Latin characters. Useful for creating human-readable URLs. Based on the original plugin by Anton Skorobogatov and Cyr-To-Lat by SergeyBiryukov and Ukr-To-Lat Alexander Butyuhin.
Author: Dilip Soni
Author URI: https://dilipsoni.in
Version: 1.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
 
/** Array of letters to convert */
function ctl_hindi_to_lat_title($title) {
	global $wpdb;

	$hindi_table = array(
'अ' => 'a',
'आ' => 'aa',
'इ' => 'i',
'ई' => 'ee',
'उ' => 'u',
'ऊ' => 'oo',
'ए'  => 'e',
'ऐ' => 'ai',
'ओ' => 'o',
'औ' => 'au',
'ऍ' => 'ai',
'ऎ' => 'ae',
'ऑ' => 'o',
'ऒ' => 'o',
'अं' => 'an',
'अः' => 'ah',
'्' => '',
'ं' => 'n',
'ः' => 'h',
'ा' => 'a',
'ि' => 'i',
'ी' => 'ee',
'ू' => 'oo',
'ु' => 'u',
'े' => 'e',
'ै' => 'ai',
'ौ' => 'au',
'ो' => 'o',
'क' => 'k',
'क्' => 'k',
'का' => 'ka',
'कि' => 'ki',
'कू' => 'koo',
'कु' => 'ku',
'की' => 'kee',
'के' => 'ke',
'कै' => 'kai',
'को' => 'ko',
'कौ' => 'kau',
'कं' => 'kan',
'कः' => 'kah',
'कॉ'=> 'co',
'ख' => 'kh',
'ख्' => 'kh',
'खा' => 'kha',
'खि' => 'khi',
'खी' => 'khee',
'खु' => 'khu',
'खू' => 'khoo',
'खे' => 'khe',
'खै' => 'khai',
'खो' => 'kho',
'खौ' => 'khau',
'खं' => 'khan',
'खः' => 'khah',
'ग' => 'g',
'ग्' => 'g',
'गा' => 'ga',
'गि' => 'gi',
'गी' => 'gee',
'गु' => 'gu',
'गू' => 'goo',
'गे' => 'ge',
'गै' => 'gai',
'गो' => 'go',
'गौ' => 'gau',
'गं' => 'gan',
'गः' => 'gah',
'घ' => 'gh',
'घ्' => 'gh',
'घा' => 'gha',
'घि' => 'ghi',
'घी' => 'ghee',
'घु' => 'ghu',
'घू' => 'ghoo',
'घे' => 'ghe',
'घै' => 'ghai',
'घो' => 'gho',
'घौ' => 'ghau',
'घं' => 'ghan',
'घः' => 'ghah',
'च' => 'ch',
'चा' => 'cha',
'च्' => 'ch',
'चि' => 'chi',
'ची' => 'chee',
'चु' => 'chu',
'चू' => 'choo',
'चे' => 'che',
'चै' => 'chai',
'चो' => 'cho',
'चौ' => 'chau',
'चौ' => 'chau',
'चौ' => 'chau',
'चं' => 'chan',
'चः' => 'chh',
'छ' => 'chh',
'छा' => 'chha',
'छि' => 'chhi',
'छी' => 'chhee',
'छु' => 'chhu',
'छू' => 'chhoo',
'छे' => 'chhe',
'छै' => 'chhai',
'छो' => 'chho',
'छौ' => 'chhau',
'छं' => 'chhan',
'छः' => 'chhah',
'छ्' => 'chh',
'ज' => 'j',
'ज्' => 'j',
'जा' => 'ja',
'जि' => 'ji',
'जी' => 'jee',
'जु' => 'ju',
'जू' => 'joo',
'जे' => 'je',
'जै' => 'jai',
'जो' => 'jo',
'जौ' => 'jau',
'जं' => 'jan',
'जः' => 'jah',
'झ' => 'jh',
'झा' => 'jh',
'झ्' => 'jh',
'झि' => 'jhi',
'झी' => 'jhee',
'झु' => 'jhu',
'झू' => 'jhoo',
'झे' => 'jhe',
'झै' => 'jhai',
'झो' => 'jho',
'झौ' => 'jhau',
'झं' => 'jhan',
'झः' => 'jhah',
'ट' => 't',
'टा' => 'ta',
'ट्' => 't',
'टि' => 'ti',
'टी' => 'tee',
'टु' => 'tu',
'टू' => 'too',
'टे' => 'te',
'टै' => 'tai',
'टं' => 'tan',
'टः' => 'tah',
'टो' => 'to',
'टौ' => 'tau',
'ठ' => 'th',
'ठ्' => 'th',
'ठा' => 'tha',
'ठि' => 'thi',
'ठी' => 'thee',
'ठु' => 'thu',
'ठू' => 'thoo',
'ठे' => 'the',
'ठै' => 'thai',
'ठो' => 'tho',
'ठौ' => 'thau',
'ठं' => 'than',
'ठः' => 'thah',
'ड' => 'd',
'ड्' => 'd',
'डा' => 'da',
'डि' => 'di',
'डी' => 'dee',
'डु' => 'du',
'डू' => 'doo',
'डे' => 'de',
'डै' => 'dai',
'डो' => 'do',
'डौ' => 'dau',
'डं' => 'dan',
'डः' => 'dah',
'ढ' => 'dh',
'ढ्' => 'dh',
'ढा' => 'dha',
'ढि' => 'dhi',
'ढी' => 'dhee',
'ढु' => 'dhu',
'ढू' => 'dhoo',
'ढे' => 'dhe',
'ढै' => 'dhai',
'ढो' => 'dho',
'ढौ' => 'dhau',
'ढं' => 'dhan',
'ढः' => 'dhah',
'त' => 't',
'त्' => 't',
'ता' => 'ta',
'ती' => 'tee',
'ति' => 'ti',
'तु' => 'tu',
'तू' => 'too',
'ते' => 'te',
'तै' => 'tai',
'तो' => 'to',
'तौ' => 'tau',
'तं' => 'tan',
'तः' => 'tah',
'थ' => 'th',
'थ्' => 'th',
'था' => 'tha',
'थि' => 'thi',
'थी' => 'thee',
'थु' => 'thu',
'थू' => 'thoo',
'थे' => 'the',
'थै' => 'thai',
'थो' => 'tho',
'थौ' => 'thau',
'थं' => 'than',
'थः' => 'thah',
'द' => 'd',
'द्' => 'd',
'दा' => 'da',
'दि' => 'di',
'दी' => 'dee',
'दु' => 'du',
'दू' => 'doo',
'दे' => 'de',
'दै' => 'dai',
'दो' => 'do',
'दौ' => 'dau',
'दं' => 'dan',
'दः' => 'dah',
'ध' => 'dh',
'ध्' => 'dh',
'धा' => 'dha',
'धि' => 'dhi',
'धी' => 'dhee',
'धु' => 'dhu',
'धू' => 'dhoo',
'धे' => 'dhe',
'धै' => 'dhai',
'धो' => 'dho',
'धौ' => 'dhau',
'धं' => 'dhan',
'धः' => 'dhah',
'न' => 'n',
'न्' => 'n',
'ना' => 'na',
'नि' => 'ni',
'नी' => 'nee',
'नु' => 'nu',
'नू' => 'noo',
'ने' => 'ne',
'नै' => 'nai',
'नो' => 'no',
'नौ' => 'nau',
'नं' => 'nan',
'नः' => 'nah',
'प' => 'p',
'प्' => 'p',
'पा' => 'pa',
'पि' => 'pi',
'पी' => 'pee',
'पु' => 'pu',
'पू' => 'poo',
'पे' => 'pe',
'पै' => 'pai',
'पो' => 'po',
'पौ' => 'pau',
'पं' => 'pan',
'पः' => 'pah',
'फ' => 'f',
'फ्' => 'f',
'फा' => 'fa',
'फी' => 'fee',
'फि' => 'fi',
'फु' => 'fu',
'फू' => 'foo',
'फे' => 'fe',
'फै' => 'fai',
'फो' => 'fo',
'फौ' => 'fau',
'फं' => 'fan',
'फः' => 'fah',
'ब' => 'b',
'ब्' => 'b',
'बा' => 'ba',
'बि' => 'bi',
'बी' => 'bee',
'बु' => 'bu',
'बू' => 'boo',
'बे' => 'be',
'बै' => 'bai',
'बो' => 'bo',
'बौ' => 'bau',
'बं' => 'ban',
'बः' => 'bah',
'भ' => 'bh',
'भ्' => 'bh',
'भा' => 'bhaa',
'भि' => 'bhi',
'भी' => 'bhee',
'भु' => 'bhu',
'भू' => 'bhoo',
'भे' => 'bhe',
'भै' => 'bhai',
'भो' => 'bho',
'भौ' => 'bhau',
'भं' => 'bhan',
'भः' => 'bhah',
'म' => 'm',
'म्' => 'm',
'मा' => 'ma',
'मि' => 'mi',
'मी' => 'mee',
'मु' => 'mu',
'मू' => 'moo',
'मे' => 'me',
'मै' => 'mai',
'मो' => 'mo',
'मौ' => 'mau',
'मं' => 'man',
'मः' => 'mah',
'य' => 'y',
'य्' => 'y',
'या' => 'ya',
'यि' => 'yi',
'यी' => 'yee',
'यु' => 'yu',
'यू' => 'yoo',
'ये' => 'ye',
'यै' => 'yai',
'यो' => 'yo',
'यौ' => 'yau',
'यं' => 'yan',
'यः' => 'yah',
'र' => 'r',
'र्' => 'r',
'रा' => 'ra',
'रि' => 'ri',
'री' => 'ree',
'रु' => 'ru',
'रू' => 'roo',
'रे' => 're',
'रै' => 'rai',
'रो' => 'ro',
'रौ' => 'rau',
'रं' => 'ran',
'रः' => 'rah',
'ल' => 'l',
'ल्' => 'l',
'ला' => 'la',
'लि' => 'li',
'ली' => 'lee',
'लु' => 'lu',
'लू' => 'loo',
'ले' => 'le',
'लै' => 'lai',
'लो' => 'lo',
'लौ' => 'lau',
'लं' => 'lan',
'लः' => 'lah',
'व' => 'v',
'व्' => 'v',
'वा' => 'va',
'वि' => 'vi',
'वी' => 'vee',
'वु' => 'vu',
'वू' => 'voo',
'वे' => 've',
'वै' => 'vai',
'वो' => 'vo',
'वौ' => 'vau',
'वं' => 'van',
'वः' => 'vah',
'स' => 's',
'स्' => 's',
'सा' => 'sa',
'सि' => 'si',
'सी' => 'see',
'सु' => 'su',
'सू' => 'soo',
'से' => 'se',
'सै' => 'sai',
'सो' => 'so',
'सौ' => 'sau',
'सं' => 'san',
'सः' => 'sah',
'श' => 'sh',
'श्' => 'sh',
'शा' => 'sha',
'शि' => 'shi',
'शी' => 'shee',
'शु' => 'shu',
'शू' => 'shoo',
'शे' => 'she',
'शै' => 'shai',
'शो' => 'sho',
'शौ' => 'shau',
'शं' => 'shan',
'शः' => 'shah',
'ष' => 'shh',
'ष्' => 'shh',
'षा' => 'shha',
'षि' => 'shhi',
'षी' => 'shhee',
'षु' => 'shhu',
'षू' => 'shhoo',
'षे' => 'shhe',
'षै' => 'shhai',
'षो' => 'shho',
'षौ' => 'shhau',
'षं' => 'shhan',
'षः' => 'shhah',
'ह' => 'h',
'ह्' => 'h',
'हा' => 'ha',
'हि' => 'hi',
'ही' => 'hee',
'हु' => 'hu',
'हू' => 'hoo',
'हे' => 'he',
'है' => 'hai',
'हो' => 'ho',
'हौ' => 'hau',
'हं' => 'han',
'हः' => 'hah',
'क्ष' => 'ksh',
'त्र' => 'tr',
'ज्ञ' => 'gy',
'ळ' => 'li',
'ऌ' => 'lri',
'ऴ' => 'll',
'ॡ' => 'lree',
'ङ' => 'ada',
'ञ' => 'nia',
'ण' => 'an',
'ऩ' => 'n',
'ॐ' => 'oms',
'क़' => 'q',
'ऋ' => 'ri',
'ॠ' => 'ri',
'ऱ' => 'r',
'ड़' => 'ad',
'ढ़' => 'dh',
'य़' => 'y',
'ज़' => 'z',
'फ़' => 'f',
'ग़' => 'gh',
'कॉ'=> 'Ko'		
	
	);	

	//$locale = get_locale();

	$is_term = false;
	$backtrace = debug_backtrace();
	foreach ( $backtrace as $backtrace_entry ) {
		if ( $backtrace_entry['function'] == 'wp_insert_term' ) {
			$is_term = true;
			break;
		}
	}

/** Convert new slugs */        
        
	$term = $is_term ? $wpdb->get_var("SELECT slug FROM {$wpdb->terms} WHERE name = '$title'") : '';
	if ( empty($term) ) {
		$title = strtr($title, apply_filters('ctl_table', $hindi_table));
		if (function_exists('iconv')){
			$title = iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $title);
		}
		$title = preg_replace("/[^A-Za-z0-9'_\-\.]/", '-', $title);
		$title = preg_replace('/\-+/', '-', $title);
		$title = preg_replace('/^-+/', '', $title);
		$title = preg_replace('/-+$/', '', $title);
	} else {
		$title = $term;
	}

	return $title;
}
add_filter('sanitize_title', 'ctl_hindi_to_lat_title', 9);
add_filter('sanitize_file_name', 'ctl_hindi_to_lat_title');

/** Convert existing slugs */

function ctl_hindi_to_lat_existing_slugs() {
	global $wpdb;

	$posts = $wpdb->get_results("SELECT ID, post_name FROM {$wpdb->posts} WHERE post_name REGEXP('[^A-Za-z0-9\-]+') AND post_status IN ('publish', 'future', 'private')");
	foreach ( (array) $posts as $post ) {
		$sanitized_name = ctl_hindi_to_lat_title(urldecode($post->post_name));
		if ( $post->post_name != $sanitized_name ) {
			add_post_meta($post->ID, '_wp_old_slug', $post->post_name);
			$wpdb->update($wpdb->posts, array( 'post_name' => $sanitized_name ), array( 'ID' => $post->ID ));
		}
	}

	$terms = $wpdb->get_results("SELECT term_id, slug FROM {$wpdb->terms} WHERE slug REGEXP('[^A-Za-z0-9\-]+') ");
	foreach ( (array) $terms as $term ) {
		$sanitized_slug = ctl_hindi_to_lat_title(urldecode($term->slug));
		if ( $term->slug != $sanitized_slug ) {
			$wpdb->update($wpdb->terms, array( 'slug' => $sanitized_slug ), array( 'term_id' => $term->term_id ));
		}
	}
}

function ctl_future_conversion() {
	add_action('shutdown', 'ctl_hindi_to_lat_existing_slugs');
}
register_activation_hook(__FILE__, 'ctl_future_conversion');
