<?php
/**
 * @package Hindi-To-Lat
 * @version 1.0
 */
/*
Plugin Name: Hindi-To-Lat
Text Domain: hindi-to-lat
Plugin URI: https://wordpress.org/plugins/hindi-to-lat/
Description: Converts Hindi characters in post and term slugs to Latin characters. Useful for creating human-readable URLs. Based on the original plugin by Anton Skorobogatov and Cyr-To-Lat by SergeyBiryukov and Ukr-To-Lat Alexander Butyuhin.
Author: Dilip Soni
Author URI: https://dilipsoni.in
Version: 1.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
 
/** Array of letters to convert */
function ctl_sanitize_title($title) {
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
'क' => 'K',
'क्' => 'K',
'का' => 'Ka',
'कि' => 'Ki',
'कू' => 'koo',
'कु' => 'Ku',
'की' => 'Kee',
'के' => 'Ke',
'कै' => 'Kai',
'को' => 'Ko',
'कौ' => 'Kau',
'कं' => 'Kan',
'कः' => 'Kah',
'ख' => 'Kh',
'ख्' => 'kh',
'खा' => 'Kha',
'खि' => 'Khi',
'खी' => 'Khee',
'खु' => 'Khu',
'खू' => 'Khoo',
'खे' => 'Khe',
'खै' => 'Khai',
'खो' => 'Kho',
'खौ' => 'Khau',
'खं' => 'Khan',
'खः' => 'khah',
'ग' => 'G',
'ग्' => 'G',
'गा' => 'Ga',
'गि' => 'Gi',
'गी' => 'Gee',
'गु' => 'Gu',
'गू' => 'Goo',
'गे' => 'Ge',
'गै' => 'Gai',
'गो' => 'Go',
'गौ' => 'Gau',
'गं' => 'Gan',
'गः' => 'Gah',
'घ' => 'Gh',
'घ्' => 'Gh',
'घा' => 'Gha',
'घि' => 'Ghi',
'घी' => 'Ghee',
'घु' => 'Ghu',
'घू' => 'Ghoo',
'घे' => 'Ghe',
'घै' => 'Ghai',
'घो' => 'Gho',
'घौ' => 'Ghau',
'घं' => 'Ghan',
'घः' => 'Ghah',
'च' => 'Ch',
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
'छ' => 'Chh',
'छा' => 'Chha',
'छि' => 'Chhi',
'छी' => 'Chhee',
'छु' => 'Chhu',
'छू' => 'Chhoo',
'छे' => 'Chhe',
'छै' => 'Chhai',
'छो' => 'Chho',
'छौ' => 'Chhau',
'छं' => 'Chhan',
'छः' => 'Chhah',
'छ्' => 'Chh',
'ज' => 'J',
'ज्' => 'J',
'जा' => 'Ja',
'जि' => 'Ji',
'जी' => 'Jee',
'जु' => 'Ju',
'जू' => 'Joo',
'जे' => 'Je',
'जै' => 'Jai',
'जो' => 'Jo',
'जौ' => 'Jau',
'जं' => 'Jan',
'जः' => 'Jah',
'झ' => 'Jh',
'झा' => 'Jh',
'झ्' => 'Jh',
'झि' => 'Jhi',
'झी' => 'Jhee',
'झु' => 'Jhu',
'झू' => 'Jhoo',
'झे' => 'Jhe',
'झै' => 'Jhai',
'झो' => 'Jho',
'झौ' => 'Jhau',
'झं' => 'Jhan',
'झः' => 'Jhah',
'ट' => 'T',
'टा' => 'Ta',
'ट्' => 'T',
'टि' => 'Ti',
'टी' => 'Tee',
'टु' => 'Tu',
'टू' => 'Too',
'टे' => 'Te',
'टै' => 'Tai',
'टं' => 'Tan',
'टः' => 'Tah',
'टो' => 'To',
'टौ' => 'Tau',
'ठ' => 'Th',
'ठ्' => 'Th',
'ठा' => 'Tha',
'ठि' => 'Thi',
'ठी' => 'Thee',
'ठु' => 'Thu',
'ठू' => 'Thoo',
'ठे' => 'The',
'ठै' => 'Thai',
'ठो' => 'Tho',
'ठौ' => 'Thau',
'ठं' => 'Than',
'ठः' => 'Thah',
'ड' => 'D',
'ड्' => 'D',
'डा' => 'Da',
'डि' => 'Di',
'डी' => 'Dee',
'डु' => 'Du',
'डू' => 'Doo',
'डे' => 'De',
'डै' => 'Dai',
'डो' => 'Do',
'डौ' => 'Dau',
'डं' => 'Dan',
'डः' => 'Dah',
'ढ' => 'Dh',
'ढ्' => 'Dh',
'ढा' => 'Dha',
'ढि' => 'Dhi',
'ढी' => 'Dhee',
'ढु' => 'Dhu',
'ढू' => 'Dhoo',
'ढे' => 'Dhe',
'ढै' => 'Dhai',
'ढो' => 'Dho',
'ढौ' => 'Dhau',
'ढं' => 'Dhan',
'ढः' => 'Dhah',
'त' => 'T',
'त्' => 'T',
'ता' => 'Ta',
'ती' => 'Tee',
'ति' => 'Ti',
'तु' => 'Tu',
'तू' => 'Too',
'ते' => 'Te',
'तै' => 'Tai',
'तो' => 'To',
'तौ' => 'Tau',
'तं' => 'Tan',
'तः' => 'Tah',
'थ' => 'Th',
'थ्' => 'Th',
'था' => 'Tha',
'थि' => 'Thi',
'थी' => 'Thee',
'थु' => 'Thu',
'थू' => 'Thoo',
'थे' => 'The',
'थै' => 'Thai',
'थो' => 'Tho',
'थौ' => 'Thau',
'थं' => 'Than',
'थः' => 'Thah',
'द' => 'D',
'द्' => 'D',
'दा' => 'Da',
'दि' => 'Di',
'दी' => 'Dee',
'दु' => 'Du',
'दू' => 'Doo',
'दे' => 'De',
'दै' => 'Dai',
'दो' => 'Do',
'दौ' => 'Dau',
'दं' => 'Dan',
'दः' => 'Dah',
'ध' => 'Dh',
'ध्' => 'Dh',
'धा' => 'Dha',
'धि' => 'Dhi',
'धी' => 'Dhee',
'धु' => 'Dhu',
'धू' => 'Dhoo',
'धे' => 'Dhe',
'धै' => 'Dhai',
'धो' => 'Dho',
'धौ' => 'Dhau',
'धं' => 'Dhan',
'धः' => 'Dhah',
'न' => 'N',
'न्' => 'N',
'ना' => 'Na',
'नि' => 'Ni',
'नी' => 'Nee',
'नु' => 'Nu',
'नू' => 'Noo',
'ने' => 'Ne',
'नै' => 'Nai',
'नो' => 'No',
'नौ' => 'Nau',
'नं' => 'Nan',
'नः' => 'Nah',
'प' => 'P',
'प्' => 'P',
'पा' => 'Pa',
'पि' => 'Pi',
'पी' => 'Pee',
'पु' => 'Pu',
'पू' => 'Poo',
'पे' => 'Pe',
'पै' => 'Pai',
'पो' => 'Po',
'पौ' => 'Pau',
'पं' => 'Pan',
'पः' => 'Pah',
'फ' => 'F',
'फ्' => 'F',
'फा' => 'Fa',
'फी' => 'Fee',
'फि' => 'Fi',
'फु' => 'Fu',
'फू' => 'Foo',
'फे' => 'Fe',
'फै' => 'Fai',
'फो' => 'Fo',
'फौ' => 'Fau',
'फं' => 'Fan',
'फः' => 'Fah',
'ब' => 'B',
'ब्' => 'B',
'बा' => 'Ba',
'बि' => 'Bi',
'बी' => 'Bee',
'बु' => 'Bu',
'बू' => 'Boo',
'बे' => 'Be',
'बै' => 'Bai',
'बो' => 'Bo',
'बौ' => 'Bau',
'बं' => 'Ban',
'बः' => 'Bah',
'भ' => 'Bh',
'भ्' => 'Bh',
'भा' => 'Bhaa',
'भि' => 'Bhi',
'भी' => 'Bhee',
'भु' => 'Bhu',
'भू' => 'Bhoo',
'भे' => 'Bhe',
'भै' => 'Bhai',
'भो' => 'Bho',
'भौ' => 'Bhau',
'भं' => 'Bhan',
'भः' => 'Bhah',
'म' => 'M',
'म्' => 'M',
'मा' => 'Ma',
'मि' => 'Mi',
'मी' => 'Mee',
'मु' => 'Mu',
'मू' => 'Moo',
'मे' => 'Me',
'मै' => 'Mai',
'मो' => 'Mo',
'मौ' => 'Mau',
'मं' => 'Man',
'मः' => 'Mah',
'य' => 'Y',
'य्' => 'Y',
'या' => 'Ya',
'यि' => 'Yi',
'यी' => 'Yee',
'यु' => 'Yu',
'यू' => 'Yoo',
'ये' => 'Ye',
'यै' => 'Yai',
'यो' => 'Yo',
'यौ' => 'Yau',
'यं' => 'Yan',
'यः' => 'Yah',
'र' => 'R',
'र्' => 'R',
'रा' => 'Ra',
'रि' => 'Ri',
'री' => 'Ree',
'रु' => 'Ru',
'रू' => 'Roo',
'रे' => 'Re',
'रै' => 'Rai',
'रो' => 'Ro',
'रौ' => 'Rau',
'रं' => 'Ran',
'रः' => 'Rah',
'ल' => 'L',
'ल्' => 'L',
'ला' => 'La',
'लि' => 'Li',
'ली' => 'Lee',
'लु' => 'Lu',
'लू' => 'Loo',
'ले' => 'Le',
'लै' => 'Lai',
'लो' => 'Lo',
'लौ' => 'Lau',
'लं' => 'Lan',
'लः' => 'Lah',
'व' => 'V',
'व्' => 'V',
'वा' => 'Va',
'वि' => 'Vi',
'वी' => 'Vee',
'वु' => 'Vu',
'वू' => 'Voo',
'वे' => 'Ve',
'वै' => 'Vai',
'वो' => 'Vo',
'वौ' => 'Vau',
'वं' => 'Van',
'वः' => 'Vah',
'स' => 'S',
'स्' => 'S',
'सा' => 'Sa',
'सि' => 'Si',
'सी' => 'See',
'सु' => 'Su',
'सू' => 'Soo',
'से' => 'Se',
'सै' => 'Sai',
'सो' => 'So',
'सौ' => 'Sau',
'सं' => 'San',
'सः' => 'Sah',
'श' => 'Sh',
'श्' => 'Sh',
'शा' => 'Sha',
'शि' => 'Shi',
'शी' => 'Shee',
'शु' => 'Shu',
'शू' => 'Shoo',
'शे' => 'She',
'शै' => 'Shai',
'शो' => 'Sho',
'शौ' => 'Shau',
'शं' => 'Shan',
'शः' => 'Shah',
'ष' => 'Shh',
'ष्' => 'Shh',
'षा' => 'Shha',
'षि' => 'Shhi',
'षी' => 'Shhee',
'षु' => 'Shhu',
'षू' => 'Shhoo',
'षे' => 'Shhe',
'षै' => 'Shhai',
'षो' => 'Shho',
'षौ' => 'Shhau',
'षं' => 'Shhan',
'षः' => 'Shhah',
'ह' => 'H',
'ह्' => 'H',
'हा' => 'Ha',
'हि' => 'Hi',
'ही' => 'Hee',
'हु' => 'Hu',
'हू' => 'Hoo',
'हे' => 'He',
'है' => 'Hai',
'हो' => 'Ho',
'हौ' => 'Hau',
'हं' => 'Han',
'हः' => 'Hah',
'क्ष' => 'Ksh',
'त्र' => 'Tr',
'ज्ञ' => 'Gy',
'ळ' => 'Li',
'ऌ' => 'Li',
'ऴ' => 'Lii',
'ॡ' => 'Lii',
'ङ' => 'Na',
'ञ' => 'Nia',
'ण' => 'Nae',
'ऩ' => 'Ni',
'ॐ' => 'oms',
'क़' => 'Qi',
'ऋ' => 'Ri',
'ॠ' => 'Ri',
'ऱ' => 'Ri',
'ड़' => 'Adha',
'ढ़' => 'Dhha',
'य़' => 'Yi',
'ज़' => 'Za',
'फ़' => 'Fi',
'ग़' => 'Ghi'
	
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
add_filter('sanitize_title', 'ctl_sanitize_title', 9);
add_filter('sanitize_file_name', 'ctl_sanitize_title');

/** Convert existing slugs */

function ctl_convert_existing_slugs() {
	global $wpdb;

	$posts = $wpdb->get_results("SELECT ID, post_name FROM {$wpdb->posts} WHERE post_name REGEXP('[^A-Za-z0-9\-]+') AND post_status IN ('publish', 'future', 'private')");
	foreach ( (array) $posts as $post ) {
		$sanitized_name = ctl_sanitize_title(urldecode($post->post_name));
		if ( $post->post_name != $sanitized_name ) {
			add_post_meta($post->ID, '_wp_old_slug', $post->post_name);
			$wpdb->update($wpdb->posts, array( 'post_name' => $sanitized_name ), array( 'ID' => $post->ID ));
		}
	}

	$terms = $wpdb->get_results("SELECT term_id, slug FROM {$wpdb->terms} WHERE slug REGEXP('[^A-Za-z0-9\-]+') ");
	foreach ( (array) $terms as $term ) {
		$sanitized_slug = ctl_sanitize_title(urldecode($term->slug));
		if ( $term->slug != $sanitized_slug ) {
			$wpdb->update($wpdb->terms, array( 'slug' => $sanitized_slug ), array( 'term_id' => $term->term_id ));
		}
	}
}

function ctl_schedule_conversion() {
	add_action('shutdown', 'ctl_convert_existing_slugs');
}
register_activation_hook(__FILE__, 'ctl_schedule_conversion');
