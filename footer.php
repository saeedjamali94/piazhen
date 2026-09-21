<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<footer class="siteFooter">
    <div class="container">
        <div class="footer-grid">

            <!-- Column 1 (RTL first): Brand + About + e-Namad -->
            <div class="footer-col footer-col--about">
                <div class="footer-logo">
                    <img src="<?php echo esc_url(pzh_logo_url()); ?>" width="138" height="40" alt="<?= esc_attr(get_bloginfo('name')); ?>">
                </div>
                <p class="footer-about-text">
                    <?php _e('با گذشته‌ی بیش از ۱۰ سال در بازار ایران، ما در «پیاژن» بهترین محصولات اصلی و با کیفیت در حوزهٔ ماشین‌های اصلاح مردانه و زنانه، دستگاه ماشین زن خط زن، اپیلاتور برس بادی و برس حرارتی را به شما عرضه می‌کنیم.', 'piazhen'); ?>
                </p>
                <div class="footer-namad" aria-label="<?php esc_attr_e('نماد اعتماد الکترونیکی', 'piazhen'); ?>">
                    <span class="footer-namad__mark">e</span>
                    <span class="footer-namad__domain">eNAMAD.ir</span>
                    <span class="footer-namad__stars">
                        <i class="fa-solid fa-star footer-namad__star footer-namad__star--orange"></i>
                        <i class="fa-solid fa-star footer-namad__star"></i>
                        <i class="fa-solid fa-star footer-namad__star"></i>
                        <i class="fa-solid fa-star footer-namad__star"></i>
                        <i class="fa-solid fa-star footer-namad__star"></i>
                    </span>
                </div>
            </div>

            <!-- Column 2: دسترسی آسان -->
            <div class="footer-col">
                <h4 class="footer-col__title"><?php _e('دسترسی آسان', 'piazhen'); ?></h4>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/shop/"><?php _e('فروشگاه', 'piazhen'); ?></a></li>
                    <li><a href="<?= SITE_URL ?>/about/"><?php _e('درباره ما', 'piazhen'); ?></a></li>
                    <li><a href="<?= SITE_URL ?>/faq/"><?php _e('سوالات متداول', 'piazhen'); ?></a></li>
                    <li><a href="<?= SITE_URL ?>/blog/"><?php _e('مجله پیاژن', 'piazhen'); ?></a></li>
                    <li><a href="<?= SITE_URL ?>/guarantee/"><?php _e('گارانتی', 'piazhen'); ?></a></li>
                </ul>
            </div>

            <!-- Column 3: اطلاعات تماس -->
            <div class="footer-col footer-col--contact">
                <h4 class="footer-col__title"><?php _e('اطلاعات تماس', 'piazhen'); ?></h4>
                <ul class="footer-contact">
                    <li>
                        <svg class="footer-contact__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span dir="ltr"><?php echo esc_html(pzh_phones()); ?></span>
                    </li>
                    <li>
                        <svg class="footer-contact__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span><?php _e('ساعت پاسخگویی تلفنی: ۱۲ الی ۱۹', 'piazhen'); ?></span>
                    </li>
                    <li>
                        <svg class="footer-contact__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span><?php _e('فعالیت فروشگاه: ۱۰ الی ۲۰', 'piazhen'); ?></span>
                    </li>
                    <li>
                        <svg class="footer-contact__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span><?php _e('خیابان جمهوری، بعد از ولیعصر، پاساژ علاءالدین آرایشی، طبقه همکف، واحد ۲۳', 'piazhen'); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar: bare outline socials + centered copyright -->
        <?php $social_links = pzh_social_links(); ?>
        <div class="footer-bottom">
            <div class="footer-social">
                <a href="<?php echo esc_url($social_links['telegram']); ?>" target="_blank" rel="noopener" aria-label="Telegram">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 3.5L2.5 10.5l6 2.5m13-9.5l-5 17-4.5-6m-2-4l4.5 6"/></svg>
                </a>
                <a href="<?php echo esc_url($social_links['twitter']); ?>" target="_blank" rel="noopener" aria-label="X">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l16 16M20 4L4 20"/></svg>
                </a>
                <a href="<?php echo esc_url($social_links['instagram']); ?>" target="_blank" rel="noopener" aria-label="Instagram">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>
                </a>
                <a href="<?php echo esc_url($social_links['linkedin']); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4V8h6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
            </div>
            <p class="footer-copyright">&copy; <?php _e('تمام حقوق برای پیاژن محفوظ است', 'piazhen'); ?></p>
            <span class="footer-bottom__spacer"></span>
        </div>
    </div>
</footer>

    <!-- Variation Popup Modal (hidden by default; shared by product cards site-wide) -->
    <div class="variation-modal-overlay" id="variation-modal" style="display:none;">
        <div class="variation-modal">
            <div class="variation-modal__inner" id="variation-modal-inner">
                <!-- AJAX content loads here -->
            </div>
        </div>
    </div>

<?php wp_footer(); ?>
</body>
</html>
