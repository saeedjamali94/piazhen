<?php
/**
 * Piazhen template functions and definitions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('PZH_THEME_DIR', get_template_directory());
define('PZH_THEME_URI', get_template_directory_uri());
define('SPRITE_URL' , PZH_THEME_URI.'/assets/images/sprite.svg?v='.time());
define('SITE_URL' , get_site_url());

// Theme setup
function piazhen_theme_setup() {
    // Add theme support for various features
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Add support for WooCommerce
    add_theme_support('woocommerce');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('منوی اصلی', 'piazhen'),
        'footer' => __('منوی فوتر', 'piazhen'),
    ));
}
add_action('after_setup_theme', 'piazhen_theme_setup');


// Enqueue scripts and styles
function piazhen_scripts() {
    // Enqueue styles
    wp_enqueue_style('owl-css', PZH_THEME_URI . '/assets/css/owl.carousel.min.css', array(), time());
    wp_enqueue_style('bozy-main-style', PZH_THEME_URI . '/assets/css/styles.css', array(), time());
    
    // Enqueue scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('owl-js', PZH_THEME_URI . '/assets/js/owl.carousel.min.js', array('jquery'), time());
    wp_enqueue_script('pzh-js', PZH_THEME_URI . '/assets/js/app.js', array('jquery'), time());
    wp_localize_script( 'pzh-js', 'pzh_options',
        array(
            'theme_url' => PZH_THEME_URI,
            'ajax_url' => admin_url('admin-ajax.php'),
            'sprite_url' => SPRITE_URL,
        )
    );
}
add_action('wp_enqueue_scripts', 'piazhen_scripts');




/**
 * Site Dashboard base url
 * @return string
 */
function pzhDashboardUrl()
{
    return SITE_URL.'/my-account';
}