<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$site_title = get_bloginfo('name');
$cart_count = class_exists('WooCommerce') ? WC()->cart->get_cart_contents_count() : 0;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header class="pzh_header">
    <!-- Top Bar -->
    <div class="pzh_header__top">
        <div class="container">
            <div class="top-bar d-flex align-items-center justify-content-between">
                <div class="top-bar__links d-flex gap-4">
                    <a href="<?= SITE_URL ?>/about"><?php _e('درباره ما', 'piazhen'); ?></a>
                    <a href="<?= SITE_URL ?>/faq"><?php _e('سوالات متداول', 'piazhen'); ?></a>
                </div>
                <div class="top-bar__contact d-none d-md-block">
                    <span><?php _e('تلفن:', 'piazhen'); ?> ۰۲۱-۱۲۳۴۵۶۷۸</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="pzh_header__main">
        <div class="container">
            <div class="header-main d-flex align-items-center justify-content-between">
                <!-- Mobile Menu Toggle -->
                <button class="menuBtn d-lg-none" aria-label="<?php _e('منو', 'piazhen'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <!-- Logo -->
                <a href="<?= SITE_URL ?>" class="header-main__logo">
                    <img src="<?= PZH_THEME_URI ?>/assets/images/logo.png" width="167" height="60" alt="<?= esc_attr($site_title); ?>">
                </a>

                <!-- Navigation (Desktop) -->
                <nav class="header-main__nav d-none d-lg-flex align-items-center">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_class'     => 'main-menu d-flex align-items-center gap-3',
                        'container'      => 'ul',
                        'walker'         => new PZH_Mega_Menu_Walker(),
                        'fallback_cb'    => false,
                    ));
                    ?>
                </nav>

                <!-- Search + Cart + User -->
                <div class="header-main__actions d-flex align-items-center gap-3">
                    <!-- Search Box -->
                    <div class="search-wrapper d-none d-md-block">
                        <?php get_template_part('template-parts/global/search', 'box'); ?>
                        <div class="search-results"></div>
                    </div>

                    <!-- Cart Icon with Count & Dropdown -->
                    <div class="cart-icon-wrapper">
                        <a href="<?= class_exists('WooCommerce') ? wc_get_cart_url() : '#'; ?>" class="cart-icon">
                            <svg class="icon stroke primary" width="24" height="24" viewBox="0 0 24 24">
                                <use xlink:href="<?= SPRITE_URL ?>#cart"></use>
                            </svg>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?= $cart_count; ?></span>
                            <?php else: ?>
                                <span class="cart-count" style="display:none;">0</span>
                            <?php endif; ?>
                        </a>
                        <div class="cart-dropdown">
                            <!-- Populated by AJAX -->
                            <div class="mini-cart">
                                <p class="mini-cart__empty"><?php _e('در حال بارگذاری...', 'piazhen'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Dashboard / Login -->
                    <a href="<?= pzhDashboardUrl(); ?>" class="user-icon" aria-label="<?php _e('حساب کاربری', 'piazhen'); ?>">
                        <svg class="icon stroke primary" width="24" height="24" viewBox="0 0 24 24">
                            <use xlink:href="<?= SPRITE_URL ?>#user"></use>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <?php get_template_part('template-parts/navigation/mobile', 'nav'); ?>

    <!-- Mobile Search (visible only on mobile) -->
    <div class="mobile-search d-md-none">
        <div class="container py-3">
            <?php get_template_part('template-parts/global/search', 'box'); ?>
            <div class="search-results"></div>
        </div>
    </div>
</header>
