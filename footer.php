<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<footer class="siteFooter">
    <div class="container">
        <div class="footer-grid">
            <!-- Column 1: About -->
            <div class="footer-col footer-col--about">
                <div class="footer-logo">
                    <img src="<?= PZH_THEME_URI ?>/assets/images/logo.png" width="140" height="50" alt="<?= esc_attr(get_bloginfo('name')); ?>">
                </div>
                <p class="footer-about-text">
                    <?php _e('فروشگاه اینترنتی پی‌آژن، ارائه‌دهنده بهترین محصولات با کیفیت و قیمت مناسب. ارسال سریع به سراسر کشور.', 'piazhen'); ?>
                </p>
                <div class="footer-social d-flex gap-3">
                    <a href="#" aria-label="Instagram" class="footer-social__link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="#" aria-label="Telegram" class="footer-social__link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.46-1.901-.903-1.056-.692-1.653-1.123-2.678-1.799-1.185-.781-.417-1.21.258-1.911.177-.184 3.247-2.977 3.307-3.23.007-.032.015-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.241-1.865-.44-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.141.119.098.152.228.168.331.016.104.036.34.026.529z"/></svg>
                    </a>
                    <a href="#" aria-label="WhatsApp" class="footer-social__link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-col footer-col--links">
                <h4 class="footer-col__title footerNavToggle" data-nav="1">
                    <?php _e('دسترسی سریع', 'piazhen'); ?>
                    <svg class="d-md-none ms-2" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </h4>
                <div class="footer-nav" data-nav="1">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_class'     => 'footer-menu',
                        'container'      => 'ul',
                        'fallback_cb'    => false,
                        'depth'          => 1,
                    ));
                    ?>
                </div>
            </div>

            <!-- Column 3: Customer Service -->
            <div class="footer-col footer-col--service">
                <h4 class="footer-col__title footerNavToggle" data-nav="2">
                    <?php _e('خدمات مشتریان', 'piazhen'); ?>
                    <svg class="d-md-none ms-2" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </h4>
                <ul class="footer-nav" data-nav="2">
                    <li><a href="<?= SITE_URL ?>/my-account"><?php _e('حساب کاربری', 'piazhen'); ?></a></li>
                    <li><a href="<?= SITE_URL ?>/cart"><?php _e('سبد خرید', 'piazhen'); ?></a></li>
                    <li><a href="<?= SITE_URL ?>/track-order"><?php _e('پیگیری سفارش', 'piazhen'); ?></a></li>
                    <li><a href="<?= SITE_URL ?>/return-policy"><?php _e('شرایط بازگشت کالا', 'piazhen'); ?></a></li>
                    <li><a href="<?= SITE_URL ?>/faq"><?php _e('سوالات متداول', 'piazhen'); ?></a></li>
                </ul>
            </div>

            <!-- Column 4: Contact Info -->
            <div class="footer-col footer-col--contact">
                <h4 class="footer-col__title footerNavToggle" data-nav="3">
                    <?php _e('تماس با ما', 'piazhen'); ?>
                    <svg class="d-md-none ms-2" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </h4>
                <ul class="footer-nav footer-nav--contact" data-nav="3">
                    <li>
                        <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                        <span dir="ltr">۰۲۱-۱۲۳۴۵۶۷۸</span>
                    </li>
                    <li>
                        <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span>info@piazhen.com</span>
                    </li>
                    <li>
                        <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span><?php _e('تهران، خیابان ولیعصر، کوچه برج', 'piazhen'); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="footer-bottom">
            <div class="footer-bottom__inner">
                <p class="footer-copyright">
                    &copy; <?= date('Y'); ?> <?php bloginfo('name'); ?> — <?php _e('تمامی حقوق محفوظ است.', 'piazhen'); ?>
                </p>
                <div class="footer-namad d-flex gap-3">
                    <!-- Trust badges / enamad placeholders -->
                    <div class="namad-placeholder">نماد اعتماد</div>
                    <div class="namad-placeholder">اینماد</div>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
