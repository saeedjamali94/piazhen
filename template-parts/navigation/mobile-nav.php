<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$cart_count = class_exists('WooCommerce') ? WC()->cart->get_cart_contents_count() : 0;
?>
<nav class="mobileNav" id="mobileNav" aria-label="<?php esc_attr_e('منوی موبایل', 'piazhen'); ?>">

    <div class="mobileNav__head">
        <a href="<?= esc_url(SITE_URL); ?>" class="mobileNav__brand">
            <img src="<?php echo esc_url(pzh_logo_url()); ?>" alt="<?php bloginfo('name'); ?>">
            <span class="mobileNav__brand-name">لوازم آرایشی برقی پیاژن</span>
        </a>
        <button class="menuClose" aria-label="<?php esc_attr_e('بستن منو', 'piazhen'); ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="mobileNav__body">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_class'     => 'mobile-menu',
            'container'      => false,
            'walker'         => new PZH_Mobile_Menu_Walker(),
            'fallback_cb'    => function () {
                pzh_primary_menu_fallback('mobile-menu');
            },
        ));
        ?>
    </div>

    <div class="mobileNav__foot">
        <a href="tel:<?= esc_attr(pzh_whatsapp_number()); ?>" class="mobileNav__support">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <span><?php echo esc_html(pzh_phones()); ?></span>
        </a>

        <div class="mobileNav__actions">
            <a href="<?= esc_url(pzhDashboardUrl()); ?>" class="mobileNav__action">
                <i class="fa-regular fa-user"></i>
                <span><?= is_user_logged_in() ? __('حساب کاربری', 'piazhen') : __('ورود / ثبت‌نام', 'piazhen'); ?></span>
            </a>
            <a href="<?= class_exists('WooCommerce') ? esc_url(wc_get_cart_url()) : '#'; ?>" class="mobileNav__action mobileNav__action--cart">
                <i class="fa-solid fa-bag-shopping"></i>
                <span><?php _e('سبد خرید', 'piazhen'); ?></span>
                <?php if ($cart_count > 0): ?>
                    <span class="mobileNav__cart-count"><?= $cart_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</nav>
