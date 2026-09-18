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

<header class="siteHeader">
    <div class="container">
        <!-- Top Bar (exactly two links per the design) -->
        <div class="siteHeader__top">
            <a href="<?= SITE_URL ?>/about/"><?php _e('درباره ما', 'piazhen'); ?></a>
            <a href="<?= SITE_URL ?>/faq/"><?php _e('سوالات متداول', 'piazhen'); ?></a>
        </div>

        <!-- Main Row: menu toggle + logo + nav + search + icons -->
        <div class="siteHeader__main">
            <!-- Mobile Menu Toggle -->
            <button class="menuBtn d-lg-none" aria-label="<?php _e('منو', 'piazhen'); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>

            <!-- Logo -->
            <div class="logo">
                <a href="<?= SITE_URL ?>">
                    <img src="<?= PZH_THEME_URI ?>/assets/images/logo.png" alt="<?= esc_attr($site_title); ?>">
                </a>
            </div>

            <!-- Navigation (inline in the main row) -->
            <nav class="navBar d-none d-lg-block">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class'     => 'main-menu',
                    'container'      => 'ul',
                    'walker'         => new PZH_Mega_Menu_Walker(),
                    'fallback_cb'    => 'pzh_primary_menu_fallback',
                ));
                ?>
            </nav>

            <!-- AJAX Search (compact pill per the design) -->
            <div class="search-wrapper d-none d-md-block">
                <?php get_template_part('template-parts/global/search', 'box'); ?>
                <div class="search-results"></div>
            </div>

            <!-- Icons: User + Cart -->
            <div class="icons">
                <!-- Dashboard / Login -->
                <?php $current_user = wp_get_current_user(); ?>
                <div class="user-icon-wrapper">
                    <a href="<?= pzhDashboardUrl(); ?>" class="user-icon" aria-label="<?php _e('حساب کاربری', 'piazhen'); ?>">
                        <i class="fa-regular fa-user"></i>
                    </a>

                    <?php if (is_user_logged_in()): ?>
                        <div class="user-dropdown" data-user-dropdown="1">
                            <div class="user-dropdown__head">
                                <span class="user-dropdown__head-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F26A26" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                                </span>
                                <span class="user-dropdown__name"><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></span>
                            </div>

                            <nav class="user-dropdown__menu">
                                <a href="<?= pzhDashboardUrl(); ?>">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                                    <span><?php _e('حساب کاربری', 'piazhen'); ?></span>
                                    <svg class="user-dropdown__chevron" width="6" height="12" viewBox="0 0 6 12" fill="none"><path d="M5 1L1 6L5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                                <a href="<?= esc_url(add_query_arg('section', 'account', pzhDashboardUrl())); ?>">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    <span><?php _e('ویرایش مشخصات فردی', 'piazhen'); ?></span>
                                    <svg class="user-dropdown__chevron" width="6" height="12" viewBox="0 0 6 12" fill="none"><path d="M5 1L1 6L5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                                <a href="<?= esc_url(add_query_arg('section', 'wallet', pzhDashboardUrl())); ?>">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/><path d="M16 15h2"/></svg>
                                    <span><?php _e('کیف پول', 'piazhen'); ?></span>
                                    <svg class="user-dropdown__chevron" width="6" height="12" viewBox="0 0 6 12" fill="none"><path d="M5 1L1 6L5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                                <a href="<?= esc_url(add_query_arg('section', 'orders', pzhDashboardUrl())); ?>">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                    <span><?php _e('سفارش‌ها', 'piazhen'); ?></span>
                                    <svg class="user-dropdown__chevron" width="6" height="12" viewBox="0 0 6 12" fill="none"><path d="M5 1L1 6L5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                                <a href="<?= esc_url(add_query_arg('section', 'favorites', pzhDashboardUrl())); ?>">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                    <span><?php _e('کالاهای مورد علاقه', 'piazhen'); ?></span>
                                    <svg class="user-dropdown__chevron" width="6" height="12" viewBox="0 0 6 12" fill="none"><path d="M5 1L1 6L5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                                <a href="<?= esc_url(wp_logout_url(pzh_auth_page_url())); ?>">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                                    <span><?php _e('خروج از حساب', 'piazhen'); ?></span>
                                    <svg class="user-dropdown__chevron" width="6" height="12" viewBox="0 0 6 12" fill="none"><path d="M5 1L1 6L5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Cart Icon with Count & Dropdown -->
                <div class="cart-icon-wrapper">
                    <a href="<?= class_exists('WooCommerce') ? wc_get_cart_url() : '#'; ?>" class="cart-icon">
                        <i class="fa-solid fa-bag-shopping"></i>
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
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <?php get_template_part('template-parts/navigation/mobile', 'nav'); ?>

    <!-- Mobile Search -->
    <div class="mobile-search d-md-none">
        <div class="container py-3">
            <?php get_template_part('template-parts/global/search', 'box'); ?>
            <div class="search-results"></div>
        </div>
    </div>
</header>
