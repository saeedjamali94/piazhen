<?php
/**
 * App-style bottom navigation (mobile only)
 *
 * خانه / فروشگاه / منو (opens the drawer) / سبد خرید (with badge) / حساب
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

$home_url     = home_url('/');
$shop_url     = function_exists('wc_get_page_permalink') ? (wc_get_page_permalink('shop') ?: home_url('/shop/')) : home_url('/shop/');
$cart_url     = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
$account_url  = is_user_logged_in()
    ? (function_exists('wc_get_page_permalink') ? (wc_get_page_permalink('myaccount') ?: home_url('/my-account/')) : home_url('/my-account/'))
    : pzh_auth_page_url();
$cart_count   = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<nav class="bottomNav" id="bottomNav" aria-label="<?php esc_attr_e('منوی پایین', 'piazhen'); ?>">
    <a href="<?php echo esc_url($home_url); ?>" class="bottomNav__item" data-nav="home">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></svg>
        <span><?php _e('خانه', 'piazhen'); ?></span>
    </a>

    <a href="<?php echo esc_url($shop_url); ?>" class="bottomNav__item" data-nav="shop">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        <span><?php _e('فروشگاه', 'piazhen'); ?></span>
    </a>

    <button type="button" class="bottomNav__item" data-nav="menu" aria-label="<?php esc_attr_e('منو', 'piazhen'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        <span><?php _e('منو', 'piazhen'); ?></span>
    </button>

    <a href="<?php echo esc_url($cart_url); ?>" class="bottomNav__item" data-nav="cart">
        <span class="bottomNav__icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <span class="bottomNav__badge mobileNav__cart-count" <?php echo $cart_count > 0 ? '' : 'style="display:none;"'; ?>><?php echo pzh_fa_num($cart_count); ?></span>
        </span>
        <span><?php _e('سبد خرید', 'piazhen'); ?></span>
    </a>

    <a href="<?php echo esc_url($account_url); ?>" class="bottomNav__item" data-nav="account">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
        <span><?php _e('حساب', 'piazhen'); ?></span>
    </a>
</nav>
