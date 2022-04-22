<?php

if ( ! defined( '_S_VERSION' ) ) {
	define( '_S_VERSION', '1.0.0' );
}

if ( ! function_exists( 'ringtone_setup' ) ) :
	function ringtone_setup() {
		load_theme_textdomain( 'ringtone', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );

		add_theme_support( 'title-tag' );

		add_theme_support( 'post-thumbnails' );

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		add_theme_support( 'customize-selective-refresh-widgets' );
	}
endif;
add_action( 'after_setup_theme', 'ringtone_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function ringtone_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'ringtone_content_width', 640 );
}
add_action( 'after_setup_theme', 'ringtone_content_width', 0 );

/**
 * Enqueue scripts and styles.
 */
function sonneriefrance_scripts() {
	wp_enqueue_style( 'normalize', get_template_directory_uri() . '/css/normalize.css', array(), _S_VERSION );
	wp_enqueue_style( 'style', get_template_directory_uri() . '/css/style.css', array(), _S_VERSION );
	wp_enqueue_style( 'responsive', get_template_directory_uri() . '/css/responsive.css', array(), _S_VERSION );
	wp_enqueue_style( 'custom', get_template_directory_uri() . '/css/custom.css', array(), _S_VERSION );
	wp_enqueue_script( 'custom', get_template_directory_uri() . '/js/custom.js', array(), _S_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'sonneriefrance_scripts' );

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

add_action('save_post', 'update_metas', 8, 2);

function update_metas($post_ID) {
	$attachment_id = get_field('file', $post_ID);
	$filesize = filesize(get_attached_file($attachment_id));
	$filesize = size_format($filesize, 2);
	update_field('file_size', $filesize, $post_ID);
}

function insert_seo_metas($post_id) {
	global $wpdb;
    $table_name = $wpdb->prefix . 'ringstone_settings';
    $settings = $wpdb->get_results("SELECT * FROM " . $table_name);
	$setting = $settings[0];
	$title_templates = explode('|', $setting->title_template);
	$title_templates = array_map(function ($template) {
		$find = array(
			'[rtTitle]',
			'[rtFileSize]',
			'[rtCategory]'
		);
		$replace = array(
			'%%title%%',
			'%%cf_file_size%%',
			'%%primary_category%%'
		);
		return str_replace($find, $replace, trim($template));
	}, $title_templates);

	$description_templates = explode('|', $setting->description_template);
	$description_templates = array_map(function ($template) {
		$find = array(
			'[rtTitle]',
			'[rtFileSize]',
			'[rtCategory]'
		);
		$replace = array(
			'%%title%%',
			'%%cf_file_size%%',
			'%%primary_category%%'
		);
		return str_replace($find, $replace, trim($template));
	}, $description_templates);
	$description = $description_templates[array_rand($description_templates)];

	add_post_meta($post_id, '_yoast_wpseo_title', $title_templates[array_rand($title_templates)]);
	add_post_meta($post_id, '_yoast_wpseo_metadesc', $description);
}
// add_action('wp_insert_post', 'insert_seo_metas', 10, 3);

add_filter( 'wp_insert_post_data' , 'filter_post_data' , 9, 2 );

function filter_post_data( $data , $postarr ) {
	if (isset($postarr['ID']) && $postarr['ID'] != 0 && empty($data['post_content'])) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ringstone_settings';
		$settings = $wpdb->get_results("SELECT * FROM " . $table_name);
		$setting = $settings[0];
		$attachment_id = get_field('file', $postarr['ID']);
		$filesize = filesize(get_attached_file(array_values($postarr['acf'])[0]));
		$filesize = size_format($filesize, 2);
		$categories = get_categories(
			array(
				'include' => $postarr['post_category']
			)
		);
		$category_names = implode(', ', array_map(function ($category) {
			return $category->name;
		}, $categories));
		$category_name = isset($categories[0]) && (!in_array('0', $postarr['post_category']) || count($postarr['post_category']) > 1) ? $category_names : '';
		$description_templates = explode('|', $setting->description_template);
		$description_templates = array_map(function ($template) use ($postarr, $filesize, $category_name) {
			$find = array(
				'[rtTitle]',
				'[rtFileSize]',
				'[rtCategory]'
			);
			$replace = array(
				$postarr['post_title'],
				$filesize,
				$category_name
			);
			return str_replace($find, $replace, trim($template));
		}, $description_templates);
		$description = $description_templates[array_rand($description_templates)];

		$data['post_content'] = $description;
	}
	

    return $data;
}

function get_meta_description($des, $title, $file_size, $category_name) {
	$category = get_the_category()[0];
	$find = array(
		'%%title%%',
		'%%cf_file_size%%',
		'%%primary_category%%'
	);
	$replace = array(
		$title,
		$file_size,
		$category_name
	);
	return str_replace($find, $replace, $des);
}

add_filter('use_block_editor_for_post', '__return_false');

add_filter( 'get_the_archive_title', 'my_theme_archive_title' );
/**
 * Remove archive labels.
 *
 * @param  string $title Current archive title to be displayed.
 * @return string        Modified archive title to be displayed.
 */
function my_theme_archive_title( $title ) {
	if ( is_category() ) {
		$title = single_cat_title( '', false );
	} elseif ( is_tag() ) {
		$title = single_tag_title( '', false );
	} elseif ( is_author() ) {
		$title = get_the_author();
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_tax() ) {
		$title = single_term_title( '', false );
	} elseif ( is_home() ) {
		$title = single_post_title( '', false );
	}

	return $title;
}

function sonnerie_next_posts_link_attributes($attr) {

	$attr = 'rel="next"';

	return $attr;

}

function sonnerie_previous_posts_link_attributes($attr) {

	$attr = 'rel="prev"';

	return $attr;

}

add_filter('next_posts_link_attributes', 'sonnerie_next_posts_link_attributes');
add_filter('previous_posts_link_attributes', 'sonnerie_previous_posts_link_attributes');

function get_filesize () {
	$attachment_id = get_field('file');
	$filesize = filesize(get_attached_file($attachment_id));
	$filesize = size_format($filesize, 2);
	return $filesize;
}

function yst_wpseo_change_og_locale( $locale ) {
	return 'fr_FR';
}

add_filter( 'wpseo_locale', 'yst_wpseo_change_og_locale' );

show_admin_bar(false);

function client_country() {
	try {
		$ip = $_SERVER["REMOTE_ADDR"];
		if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		$reader = new Reader(ABSPATH . 'GeoLite2-Country.mmdb');
		$record = $reader->country($ip);
		return $record->country->isoCode;		
	} catch (Exception $e) {
		return 'error';
	}
}

function is_mobile() {
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent,0,4))) {
		return true;
	}
	return false;
}

function is_show_ads () {
	$c = client_country();
	return $c != "VN" && $c != "PK" && is_mobile() && is_online_valid();
}

function user_online(){
   global $wpdb;

   $online_users = $wpdb->get_var("SELECT COUNT(*) FROM wp_useronline");

   return (int) $online_users;
}

function ios_online(){
   global $wpdb;

   $online_users = $wpdb->get_var("SELECT COUNT(*) FROM wp_useronline WHERE user_agent LIKE '%iphone%'");

   return (int) $online_users;
}

function win_online(){
   global $wpdb;

   $online_users = $wpdb->get_var("SELECT COUNT(*) FROM wp_useronline WHERE user_agent LIKE '%windows%'");

   return (int) $online_users;
}

function is_online_valid() {
	return user_online() <= 40 && ios_online() <= 15 && win_online() <= 15;
}

add_action( 'test_cron', 'test_cron_function' );

function test_cron_function() {
	update_field('download_count', rand(1, 1000), 21);
}