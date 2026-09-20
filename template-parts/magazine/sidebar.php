<?php
/**
 * Magazine sidebar (all magazine pages): 3 gold cards + newsletter pill.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

$socials = pzh_social_links();
?>
<aside class="mag-sidebar">

    <!-- Card 1: buying guides (pencil + sparkle, arrow affordance) -->
    <a href="<?php echo esc_url(home_url('/blog/')); ?>" class="mag-side-card">
        <span class="mag-side-card__icon">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="#121212" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M33.5 4.5a3 3 0 0 1 3 3L24 20l-5 2 2-5L33.5 4.5z"/><path d="M28.5 16.5l2.8 2.8"/></svg>
        </span>
        <span class="mag-side-card__text"><?php _e('راهنمای خرید تخصصی', 'piazhen'); ?></span>
        <span class="mag-side-card__arrow">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="#121212"><path d="M14.2 3.2L14.2 12.4H22.6V9.6L9.6 9.6L9.6 2.4H5V5.2H3.2V7.4H5V12.4H8.6L8.6 14.4H3.2V16.6H8.6V20.8H10.8V16.6H16.8L16.8 10.2H14.2V3.2Z"/></svg>
        </span>
    </a>

    <!-- Card 2: follow us (3 social squares) -->
    <div class="mag-side-card mag-side-card--social">
        <div class="mag-side-card__socials">
            <a href="<?php echo esc_url($socials['instagram']); ?>" target="_blank" rel="noopener" aria-label="Instagram" class="mag-social-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#121212" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.5" cy="6.5" r="1.2" fill="#121212" stroke="none"/></svg>
            </a>
            <a href="<?php echo esc_url($socials['telegram']); ?>" target="_blank" rel="noopener" aria-label="Telegram" class="mag-social-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#121212" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 3.5L2.5 10.5l6 2.5m13-9.5l-5 17-4.5-6m-2-4l4.5 6"/></svg>
            </a>
            <a href="<?php echo esc_url($socials['whatsapp']); ?>" target="_blank" rel="noopener" aria-label="WhatsApp" class="mag-social-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#121212" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5a9.5 9.5 0 0 0-8.2 14.3L3 21.5l4.9-0.8A9.5 9.5 0 1 0 12 2.5z"/><path d="M8.5 8.5c.5 2 3 3.5 4.5 4s2.5 1 3.5 1.5"/></svg>
            </a>
        </div>
        <span class="mag-side-card__text"><?php _e('ما را دنبال کنید', 'piazhen'); ?></span>
    </div>

    <!-- Card 3: support (person + shield) -->
    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="mag-side-card">
        <span class="mag-side-card__icon">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="#121212" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="20" cy="14" r="5.5"/><path d="M8 34c0-6.6 5.4-10 12-10s12 3.4 12 10"/><path d="M13.5 26l3.5 3.5L28 18.5"/></svg>
        </span>
        <span class="mag-side-card__text"><?php _e('پشتیبانی پی‌آژن', 'piazhen'); ?></span>
    </a>

    <!-- Newsletter pill (AJAX subscribe) -->
    <form class="mag-newsletter" data-newsletter="1">
        <input type="email" class="mag-newsletter__input" placeholder="<?php _e('ایمیل خود را وارد کنید', 'piazhen'); ?>" required>
        <button type="submit" class="mag-newsletter__btn" aria-label="<?php esc_attr_e('عضویت', 'piazhen'); ?>">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#828282" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
        </button>
    </form>

</aside>
