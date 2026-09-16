<?php
/**
 * Template Name: داشبورد کاربری
 *
 * My-account dashboard: orange sidebar + AJAX section navigation.
 * All actions are custom AJAX — no plugins.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

// Guests go to the login/registration page
if (!is_user_logged_in()) {
    wp_safe_redirect(add_query_arg('redirect_to', get_permalink(), pzh_auth_page_url()));
    exit;
}

get_header();

$user    = wp_get_current_user();
$menu    = pzh_dashboard_menu();
$section = isset($_GET['section']) ? sanitize_key($_GET['section']) : 'dashboard';
if (!isset($menu[$section])) {
    $section = 'dashboard';
}
?>
<main class="pzh-dashboard" data-dashboard="1">
    <div class="container">
        <div class="pzh-dashboard__layout">

            <!-- Sidebar (orange, per design) -->
            <aside class="pzh-dash-sidebar">
                <div class="pzh-dash-sidebar__user">
                    <span class="pzh-dash-sidebar__avatar">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <span class="pzh-dash-sidebar__name"><?php echo esc_html($user->display_name ?: $user->user_login); ?></span>
                </div>

                <nav class="pzh-dash-menu">
                    <?php foreach ($menu as $slug => $item): ?>
                        <a href="<?php echo esc_url(add_query_arg('section', $slug, get_permalink())); ?>"
                           class="pzh-dash-menu__item <?php echo $slug === $section ? 'active' : ''; ?>"
                           data-section="<?php echo esc_attr($slug); ?>">
                            <span class="pzh-dash-menu__icon"><?php echo $item['icon']; ?></span>
                            <span class="pzh-dash-menu__label"><?php echo esc_html($item['label']); ?></span>
                        </a>
                    <?php endforeach; ?>

                    <a href="<?php echo esc_url(wp_logout_url(pzh_auth_page_url())); ?>"
                       class="pzh-dash-menu__item pzh-dash-menu__logout">
                        <span class="pzh-dash-menu__icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                        </span>
                        <span class="pzh-dash-menu__label"><?php _e('خروج از حساب', 'piazhen'); ?></span>
                    </a>
                </nav>
            </aside>

            <!-- Content -->
            <div class="pzh-dash-content" id="pzh-dash-content">
                <?php echo pzh_render_account_section($section, $_GET); ?>
            </div>

        </div>
    </div>

    <!-- Add-address Modal (fields + Neshan map with location→address) -->
    <div class="dash-modal-overlay" id="dash-address-modal" style="display:none;">
        <div class="dash-modal">
            <div class="dash-modal__header">
                <h3><?php _e('افزودن آدرس جدید', 'piazhen'); ?></h3>
                <button type="button" class="dash-modal__close" id="dash-address-modal-close" aria-label="<?php esc_attr_e('بستن', 'piazhen'); ?>">&times;</button>
            </div>
            <div class="dash-modal__body">
                <form id="dash-address-modal-form">
                    <input type="hidden" name="address_type" id="dash-modal-address-type" value="billing">

                    <div class="dash-form__grid">
                        <div class="dash-field">
                            <label><?php _e('استان', 'piazhen'); ?></label>
                            <select name="state" id="dash-modal-state">
                                <option value=""><?php _e('انتخاب کنید', 'piazhen'); ?></option>
                                <?php foreach (WC()->countries->get_states('IR') as $code => $name): ?>
                                    <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="dash-field">
                            <label><?php _e('شهر', 'piazhen'); ?></label>
                            <input type="text" name="city" id="dash-modal-city" placeholder="<?php _e('شهر', 'piazhen'); ?>">
                        </div>
                        <div class="dash-field dash-field--full">
                            <label><?php _e('آدرس', 'piazhen'); ?> <span class="req">*</span></label>
                            <textarea name="address_1" id="dash-modal-address-1" rows="3" placeholder="<?php _e('خیابان،کوچه، پلاک، واحد…', 'piazhen'); ?>"></textarea>
                        </div>
                        <div class="dash-field">
                            <label><?php _e('پلاک', 'piazhen'); ?></label>
                            <input type="text" name="plaque" id="dash-modal-plaque" placeholder="<?php _e('پلاک ۱۲', 'piazhen'); ?>">
                        </div>
                        <div class="dash-field">
                            <label><?php _e('واحد', 'piazhen'); ?></label>
                            <input type="text" name="unit" id="dash-modal-unit" placeholder="<?php _e('واحد ۳', 'piazhen'); ?>">
                        </div>
                        <div class="dash-field dash-field--full">
                            <label><?php _e('کد پستی', 'piazhen'); ?></label>
                            <input type="text" name="postcode" id="dash-modal-postcode" placeholder="1234567890" dir="ltr">
                        </div>
                    </div>

                    <!-- Map: pick the location, reverse geocoding fills the fields -->
                    <div class="checkout-map-block">
                        <div class="checkout-map-block__head">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?php _e('انتخاب موقعیت روی نقشه', 'piazhen'); ?></span>
                        </div>
                        <div id="dash-address-map" class="checkout-map"></div>
                        <div class="checkout-map-block__address" id="dash-address-map-bar">
                            <?php _e('روی نقشه کلیک کنید تا آدرس از موقعیت انتخاب‌شده پر شود.', 'piazhen'); ?>
                        </div>
                        <input type="hidden" name="latitude" id="dash-modal-lat" value="">
                        <input type="hidden" name="longitude" id="dash-modal-lng" value="">
                    </div>

                    <div class="dash-form__actions">
                        <button type="submit" class="dash-btn dash-btn--submit dirty"><?php _e('ثبت آدرس', 'piazhen'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Wallet charge modal -->
    <div class="dash-modal-overlay" id="dash-wallet-modal" style="display:none;">
        <div class="dash-modal">
            <div class="dash-modal__header">
                <h3><?php _e('شارژ کیف پول', 'piazhen'); ?></h3>
                <button type="button" class="dash-modal__close" id="dash-wallet-modal-close" aria-label="<?php esc_attr_e('بستن', 'piazhen'); ?>">&times;</button>
            </div>
            <div class="dash-modal__body">
                <form id="dash-wallet-form">
                    <div class="dash-field">
                        <label><?php _e('مبلغ شارژ (تومان)', 'piazhen'); ?></label>
                        <input type="number" name="amount" id="dash-wallet-amount" min="10000" max="100000000"
                               placeholder="<?php _e('مبلغ دلخواه را وارد کنید', 'piazhen'); ?>" dir="ltr">
                    </div>

                    <div class="dash-wallet-quick">
                        <?php foreach (array(1000000, 2000000, 5000000, 10000000) as $quick): ?>
                            <button type="button" class="dash-wallet-quick__btn" data-amount="<?php echo $quick; ?>">
                                <?php echo pzh_fa_num(number_format($quick)); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="dash-form__actions">
                        <button type="submit" class="dash-btn dash-btn--submit dirty w-100"><?php _e('پرداخت', 'piazhen'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Wallet withdrawal modal -->
    <div class="dash-modal-overlay" id="dash-withdraw-modal" style="display:none;">
        <div class="dash-modal">
            <div class="dash-modal__header">
                <h3><?php _e('انتقال وجه', 'piazhen'); ?></h3>
                <button type="button" class="dash-modal__close" id="dash-withdraw-modal-close" aria-label="<?php esc_attr_e('بستن', 'piazhen'); ?>">&times;</button>
            </div>
            <div class="dash-modal__body">
                <form id="dash-withdraw-form">
                    <div class="dash-field">
                        <label><?php _e('مبلغ انتقال (تومان)', 'piazhen'); ?></label>
                        <input type="number" name="amount" id="dash-withdraw-amount" min="10000"
                               placeholder="<?php _e('مبلغ را وارد کنید', 'piazhen'); ?>" dir="ltr">
                    </div>
                    <button type="button" class="dash-link" id="dash-withdraw-all" style="margin-bottom: 16px;">
                        <?php _e('انتقال کل موجودی', 'piazhen'); ?>
                        (<span id="dash-withdraw-balance">0</span> <?php _e('تومان', 'piazhen'); ?>)
                    </button>

                    <div class="dash-form__grid">
                        <div class="dash-field">
                            <label><?php _e('نام بانک', 'piazhen'); ?> <span class="req">*</span></label>
                            <input type="text" name="bank_name" placeholder="<?php _e('مثلاً ملت', 'piazhen'); ?>">
                        </div>
                        <div class="dash-field">
                            <label><?php _e('نام صاحب حساب', 'piazhen'); ?> <span class="req">*</span></label>
                            <input type="text" name="account_name" placeholder="<?php _e('نام و نام خانوادگی', 'piazhen'); ?>">
                        </div>
                        <div class="dash-field">
                            <label><?php _e('شماره کارت', 'piazhen'); ?></label>
                            <input type="text" name="card_number" placeholder="6037-XXXX-XXXX-XXXX" dir="ltr">
                        </div>
                        <div class="dash-field">
                            <label><?php _e('شماره حساب', 'piazhen'); ?></label>
                            <input type="text" name="account_number" placeholder="0000000000000" dir="ltr">
                        </div>
                        <div class="dash-field dash-field--full">
                            <label><?php _e('شبا', 'piazhen'); ?></label>
                            <input type="text" name="iban" placeholder="IR000000000000000000000000" dir="ltr">
                        </div>
                    </div>

                    <div class="dash-form__actions">
                        <button type="submit" class="dash-btn dash-btn--submit dirty w-100"><?php _e('ثبت درخواست برداشت', 'piazhen'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Variation Popup Modal (for variable products in the wishlist) -->
    <div class="variation-modal-overlay" id="variation-modal" style="display:none;">
        <div class="variation-modal">
            <div class="variation-modal__inner" id="variation-modal-inner">
                <!-- AJAX content loads here -->
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
