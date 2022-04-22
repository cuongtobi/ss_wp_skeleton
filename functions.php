<?php

define('SSWPS_VERSION', '1.0.0');
define('SSWPS_POSTS_PER_PAGE', 10);

if (!function_exists('theme_setup')) {
	function theme_setup() {
		add_theme_support('title-tag');

		add_theme_support('post-thumbnails');

		add_theme_support( 'automatic-feed-links' );

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
	}
}	
add_action('after_setup_theme', 'theme_setup');

function theme_scripts() {
	wp_enqueue_style('style', get_template_directory_uri() . '/style.css', array(), SSWPS_VERSION);
	wp_enqueue_style('main', get_template_directory_uri() . '/css/main.css', array(), SSWPS_VERSION);
	wp_enqueue_script('custom', get_template_directory_uri() . '/js/main.js', array(), SSWPS_VERSION, true);
}
add_action('wp_enqueue_scripts', 'theme_scripts');

add_filter('use_block_editor_for_post', '__return_false');
