<?php
/**
 * Piazhen Site Settings — admin panel for site-wide configuration
 *
 * Uses the native WordPress Options API (matching the existing free-delivery
 * and wallet-withdrawals admin panels). One option array per tab:
 *   pzh_settings_general | contact | images | map | sms
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// Defaults
// ============================================================================

function pzh_settings_defaults($tab) {
    $defaults = array(
        'general' => array(
            'logo'        => PZH_THEME_URI . '/assets/images/logo.png',
            'login_cover' => PZH_THEME_URI . '/assets/images/login-cover.png',
            'sprite'      => SPRITE_URL,
        ),
        'contact' => array(
            'phones'    => '۰۹۱۲ ۷۷۷ ۲۱ ۶۷ - ۰۹۱۲ ۰۴۳ ۴۹ ۴۳ - ۰۲۱ ۹۱۶۹۲۲۳۳',
            'whatsapp'  => '989127772167',
            'telegram'  => 'https://t.me/piazhen',
            'instagram' => 'https://instagram.com/piazhen',
            'twitter'   => 'https://x.com/piazhen',
            'linkedin'  => 'https://linkedin.com/company/piazhen',
        ),
        'images' => array(
            'hero_slide'   => PZH_THEME_URI . '/assets/images/cover1.png',
            'hero_feature' => PZH_THEME_URI . '/assets/images/image.png',
            'hero_card2'   => PZH_THEME_URI . '/assets/images/card2.png',
            'hero_card3'   => PZH_THEME_URI . '/assets/images/card3.png',
        ),
        'map' => array(
            'map_lat'    => '35.7219',
            'map_lng'    => '51.3347',
            'map_zoom'   => '12',
            'osm_tiles'  => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            'nominatim'  => 'https://nominatim.openstreetmap.org/reverse',
            'neshan_key' => '',
            'neshan_url' => 'https://api.neshan.org/v5/reverse',
        ),
        'sms' => array(
            'sms_username' => '09127772167',
            'sms_password' => 'aa601577-a7ad-436d-9d42-4a2de9bfd2de',
            'sms_sender'   => '50002710072167',
            'sms_pattern'  => '186253',
        ),
    );

    return isset($defaults[$tab]) ? $defaults[$tab] : array();
}

// ============================================================================
// Getters
// ============================================================================

/**
 * Get all settings for a tab, merged over defaults.
 */
function pzh_settings($tab) {
    $defaults = pzh_settings_defaults($tab);
    $saved    = get_option('pzh_settings_' . $tab, array());
    if (!is_array($saved)) {
        $saved = array();
    }
    return array_merge($defaults, $saved);
}

/**
 * Get a single setting value with default fallback.
 */
function pzh_setting($tab, $key) {
    $settings = pzh_settings($tab);
    return isset($settings[$key]) ? $settings[$key] : '';
}

/**
 * Resolve an image setting to a URL.
 *
 * Handles three storage formats:
 *  - empty string → returns the tab default
 *  - numeric (attachment ID) → wp_get_attachment_image_url()
 *  - full URL → returned as-is
 *  - bare filename → prefixed with PZH_THEME_URI (legacy compat)
 */
function pzh_setting_image($tab, $key) {
    $value    = pzh_setting($tab, $key);
    $defaults = pzh_settings_defaults($tab);
    $default  = isset($defaults[$key]) ? $defaults[$key] : '';

    if (empty($value)) {
        return $default;
    }

    // Attachment ID
    if (is_numeric($value)) {
        $url = wp_get_attachment_image_url(intval($value), 'full');
        if ($url) {
            return $url;
        }
        return $default;
    }

    // Full URL (starts with http/https)
    if (preg_match('#^https?://#', $value)) {
        return $value;
    }

    // Bare filename — prefix with theme URI
    return PZH_THEME_URI . '/' . ltrim($value, '/');
}

// ============================================================================
// Front-end convenience wrappers
// ============================================================================

function pzh_logo_url() {
    return pzh_setting_image('general', 'logo');
}

function pzh_login_cover_url() {
    return pzh_setting_image('general', 'login_cover');
}

function pzh_sprite_url() {
    return pzh_setting_image('general', 'sprite');
}

function pzh_phones() {
    return pzh_setting('contact', 'phones');
}

function pzh_whatsapp_number() {
    $wa = pzh_setting('contact', 'whatsapp');
    // Strip everything except digits
    $digits = preg_replace('/[^0-9]/', '', $wa);
    return $digits ?: '989127772167';
}

function pzh_whatsapp_wa() {
    $num = pzh_whatsapp_number();
    return $num ? 'https://wa.me/' . $num : '';
}

function pzh_social_links() {
    return array(
        'telegram'  => pzh_setting('contact', 'telegram'),
        'instagram' => pzh_setting('contact', 'instagram'),
        'twitter'   => pzh_setting('contact', 'twitter'),
        'linkedin'  => pzh_setting('contact', 'linkedin'),
        'whatsapp'  => pzh_whatsapp_wa(),
    );
}

function pzh_map_defaults() {
    return array(
        'lat'   => floatval(pzh_setting('map', 'map_lat')),
        'lng'   => floatval(pzh_setting('map', 'map_lng')),
        'zoom'  => absint(pzh_setting('map', 'map_zoom')),
        'tiles' => pzh_setting('map', 'osm_tiles'),
    );
}

/**
 * Build the JS i18n strings array for wp_localize_script.
 */
function pzh_js_strings() {
    return apply_filters('pzh_js_strings', array(
        // Network errors
        'server_error'         => __('خطا در ارتباط با سرور.', 'piazhen'),
        // AJAX fallbacks
        'add_to_cart_error'    => __('خطا در افزودن به سبد خرید.', 'piazhen'),
        'add_to_cart_success'  => __('محصول به سبد خرید اضافه شد.', 'piazhen'),
        'save_error'           => __('خطا در ذخیره اطلاعات.', 'piazhen'),
        'address_save_error'   => __('خطا در ذخیره آدرس.', 'piazhen'),
        'cart_update_error'    => __('خطا در به‌روزرسانی سبد خرید.', 'piazhen'),
        'withdraw_error'       => __('خطا در ثبت درخواست برداشت.', 'piazhen'),
        'review_error'         => __('خطا در ثبت نظر.', 'piazhen'),
        'newsletter_error'     => __('خطا در ثبت عضویت.', 'piazhen'),
        'payment_error'        => __('خطا در اتصال به درگاه پرداخت.', 'piazhen'),
        // Auth
        'invalid_phone'        => __('شماره موبایل معتبر نیست. (مثال: 9123456789)', 'piazhen'),
        'name_required'        => __('نام و نام خانوادگی الزامی است.', 'piazhen'),
        'password_short'       => __('رمز عبور باید حداقل ۶ کاراکتر باشد.', 'piazhen'),
        'password_mismatch'    => __('تکرار رمز عبور مطابقت ندارد.', 'piazhen'),
        'otp_error'            => __('خطا در ارسال کد.', 'piazhen'),
        'register_error'       => __('خطا در ثبت‌نام.', 'piazhen'),
        'login_success'        => __('ورود با موفقیت انجام شد.', 'piazhen'),
        // Toasts / notifications
        'compare_added'        => __('محصول به لیست مقایسه اضافه شد.', 'piazhen'),
        'compare_removed'      => __('محصول از لیست مقایسه حذف شد.', 'piazhen'),
        'compare_limit'        => __('حداکثر ۴ محصول قابل مقایسه است.', 'piazhen'),
        'wallet_insufficient'  => __('موجودی کیف پول برای انتقال کافی نیست.', 'piazhen'),
        'coming_soon'          => __('این قابلیت به‌زودی فعال می‌شود.', 'piazhen'),
        'charge_amount'        => __('مبلغ شارژ را وارد کنید. (حداقل ۱۰٬۰۰۰ تومان)', 'piazhen'),
        'withdraw_amount'      => __('مبلغ انتقال را وارد کنید. (حداقل ۱۰٬۰۰۰ تومان)', 'piazhen'),
        'redirecting_gateway'  => __('در حال انتقال به درگاه پرداخت...', 'piazhen'),
        // DOM text
        'loading_address'      => __('در حال دریافت آدرس...', 'piazhen'),
        'location_set'         => __('موقعیت روی نقشه ثبت شد؛ لطفاً آدرس را در فرم تکمیل کنید.', 'piazhen'),
        'please_wait'          => __('لطفاً صبر کنید...', 'piazhen'),
        'product_load_error'   => __('خطا در بارگذاری اطلاعات محصول.', 'piazhen'),
        'select_options'       => __('لطفاً همه گزینه‌ها را انتخاب کنید', 'piazhen'),
        'select_product_opts'  => __('لطفاً گزینه‌های محصول را انتخاب کنید.', 'piazhen'),
        'select_specs'         => __('لطفاً مشخصات محصول را انتخاب کنید.', 'piazhen'),
        'add_to_cart'          => __('افزودن به سبد خرید', 'piazhen'),
        'out_of_stock'         => __('ناموجود', 'piazhen'),
        'combination_unavail'  => __('این ترکیب موجود نیست.', 'piazhen'),
        'checking'             => __('در حال بررسی...', 'piazhen'),
        'show_more'            => __('مشاهده بیشتر ...', 'piazhen'),
        'show_less'            => __('بستن ...', 'piazhen'),
        'map_click_hint'       => __('روی نقشه کلیک کنید تا آدرس از موقعیت انتخاب‌شده پر شود.', 'piazhen'),
        'both_addresses_set'   => __('هر دو آدرس ثبت شده‌اند؛ برای تغییر از «ویرایش آدرس» استفاده کنید.', 'piazhen'),
        'add_to_cart_short'    => __('خطا در افزودن به سبد.', 'piazhen'),
    ));
}

// ============================================================================
// Admin: Menu Registration
// ============================================================================

function pzh_admin_settings_menu() {
    add_menu_page(
        __('تنظیمات پی‌آژن', 'piazhen'),
        __('تنظیمات پی‌آژن', 'piazhen'),
        'manage_options',
        'pzh-settings',
        'pzh_admin_settings_render',
        'dashicons-admin-generic',
        60
    );
}
add_action('admin_menu', 'pzh_admin_settings_menu');

// ============================================================================
// Admin: Enqueue media + admin assets
// ============================================================================

function pzh_admin_settings_enqueue($hook) {
    if (strpos($hook, 'pzh-settings') === false) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script(
        'pzh-settings-admin',
        PZH_THEME_URI . '/assets/js/settings-admin.js',
        array('jquery'),
        wp_get_theme()->get('Version'),
        true
    );
    wp_enqueue_style(
        'pzh-settings-admin',
        PZH_THEME_URI . '/assets/css/settings-admin.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('admin_enqueue_scripts', 'pzh_admin_settings_enqueue');

// ============================================================================
// Admin: SVG Upload Support
// ============================================================================

function pzh_allow_svg_upload($mimes) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'pzh_allow_svg_upload');

// ============================================================================
// Admin: Render
// ============================================================================

/**
 * Sanitize a single field value by type.
 */
function pzh_sanitize_setting($value, $type) {
    switch ($type) {
        case 'url':
            return esc_url_raw($value);
        case 'number':
            return is_numeric($value) ? $value : '';
        case 'digits':
            return preg_replace('/[^0-9]/', '', $value);
        case 'float':
            return floatval($value);
        case 'image':
            // Can be attachment ID (numeric), URL, or empty
            if (is_numeric($value)) {
                return absint($value);
            }
            if (empty($value)) {
                return '';
            }
            return esc_url_raw($value);
        default:
            return sanitize_text_field($value);
    }
}

/**
 * Render a media-uploader row (image field with preview).
 */
function pzh_render_image_field($tab, $key, $label, $description = '') {
    $value    = pzh_setting($tab, $key);
    $img_src  = pzh_setting_image($tab, $key);
    ?>
    <tr>
        <th scope="row">
            <label><?php echo esc_html($label); ?></label>
        </th>
        <td>
            <div class="pzh-media-row">
                <input type="hidden"
                       name="pzh_<?php echo esc_attr($tab); ?>[<?php echo esc_attr($key); ?>]"
                       id="pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>"
                       value="<?php echo esc_attr($value); ?>">
                <div class="pzh-media-preview" id="pzh_preview_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>">
                    <?php if ($img_src): ?>
                        <img src="<?php echo esc_url($img_src); ?>" alt="" style="max-width:200px;max-height:120px;">
                    <?php endif; ?>
                </div>
                <button type="button"
                        class="button pzh-media-upload"
                        data-target="pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>"
                        data-preview="pzh_preview_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>">
                    <?php _e('انتخاب تصویر', 'piazhen'); ?>
                </button>
                <button type="button"
                        class="button pzh-media-remove"
                        data-target="pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>"
                        data-preview="pzh_preview_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>"
                        data-default="<?php echo esc_url(pzh_settings_defaults($tab)[$key] ?? ''); ?>"
                        <?php if (empty($value)) echo 'style="display:none;"'; ?>>
                    <?php _e('حذف', 'piazhen'); ?>
                </button>
            </div>
            <?php if ($description): ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Render a text field row.
 */
function pzh_render_text_field($tab, $key, $label, $type = 'text', $description = '', $dir = '') {
    $value = pzh_setting($tab, $key);
    $dir_attr = $dir ? ' dir="' . esc_attr($dir) . '"' : '';
    ?>
    <tr>
        <th scope="row">
            <label for="pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>">
                <?php echo esc_html($label); ?>
            </label>
        </th>
        <td>
            <input type="<?php echo esc_attr($type); ?>"
                   id="pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>"
                   name="pzh_<?php echo esc_attr($tab); ?>[<?php echo esc_attr($key); ?>]"
                   class="regular-text"
                   value="<?php echo esc_attr($value); ?>"
                   <?php echo $dir_attr; ?>>
            <?php if ($description): ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Save handler for a settings tab.
 */
function pzh_save_settings_tab($tab, $field_types) {
    if (!isset($_POST['pzh_' . $tab . '_save'])) {
        return;
    }
    if (!check_admin_referer('pzh_settings_' . $tab)) {
        return;
    }

    $new   = array();
    $input = isset($_POST['pzh_' . $tab]) ? wp_unslash($_POST['pzh_' . $tab]) : array();

    foreach ($field_types as $key => $type) {
        $raw = isset($input[$key]) ? $input[$key] : '';
        $new[$key] = pzh_sanitize_setting($raw, $type);
    }

    update_option('pzh_settings_' . $tab, $new);
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('تنظیمات ذخیره شد.', 'piazhen') . '</p></div>';
}

/**
 * Main render callback.
 */
function pzh_admin_settings_render() {
    $tabs = array(
        'general' => __('عمومی', 'piazhen'),
        'contact' => __('تماس و شبکه‌های اجتماعی', 'piazhen'),
        'images'  => __('تصاویر', 'piazhen'),
        'map'     => __('نقشه و API', 'piazhen'),
        'sms'     => __('پیامک', 'piazhen'),
    );

    $current_tab = isset($_GET['tab']) && isset($tabs[$_GET['tab']]) ? $_GET['tab'] : 'general';

    // Field definitions per tab: key => sanitize_type
    $field_types = array(
        'general' => array(
            'logo' => 'image', 'login_cover' => 'image', 'sprite' => 'image',
        ),
        'contact' => array(
            'phones' => 'text', 'whatsapp' => 'digits',
            'telegram' => 'url', 'instagram' => 'url',
            'twitter' => 'url', 'linkedin' => 'url',
        ),
        'images' => array(
            'hero_slide' => 'image', 'hero_feature' => 'image',
            'hero_card2' => 'image', 'hero_card3' => 'image',
        ),
        'map' => array(
            'map_lat' => 'float', 'map_lng' => 'float', 'map_zoom' => 'number',
            'osm_tiles' => 'url', 'nominatim' => 'url',
            'neshan_key' => 'text', 'neshan_url' => 'url',
        ),
        'sms' => array(
            'sms_username' => 'text', 'sms_password' => 'text',
            'sms_sender' => 'text', 'sms_pattern' => 'number',
        ),
    );

    // Handle save for the current tab
    if (isset($field_types[$current_tab])) {
        pzh_save_settings_tab($current_tab, $field_types[$current_tab]);
    }
    ?>
    <div class="wrap pzh-settings-wrap">
        <h1><?php _e('تنظیمات پی‌آژن', 'piazhen'); ?></h1>

        <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_key => $tab_label): ?>
                <a href="<?php echo esc_url(add_query_arg(array('page' => 'pzh-settings', 'tab' => $tab_key), admin_url('admin.php'))); ?>"
                   class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($tab_label); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="pzh-settings-tab-content">
            <form method="post" action="">
                <?php wp_nonce_field('pzh_settings_' . $current_tab); ?>
                <table class="form-table" role="presentation">
                    <?php
                    switch ($current_tab) {
                        case 'general':
                            pzh_render_image_field('general', 'logo', __('لوگو (هدر، فوتر، صفحه ورود)', 'piazhen'));
                            pzh_render_image_field('general', 'login_cover', __('تصویر پوشش صفحه ورود', 'piazhen'));
                            pzh_render_image_field('general', 'sprite', __('فایل آیکون SVG (sprite.svg)', 'piazhen'));
                            break;

                        case 'contact':
                            pzh_render_text_field('contact', 'phones', __('شماره‌های تماس', 'piazhen'), 'text', __('با همان قالب‌بندی دلخواه در فوتر نمایش داده می‌شود.', 'piazhen'), 'ltr');
                            pzh_render_text_field('contact', 'whatsapp', __('شماره واتساپ', 'piazhen'), 'text', __('فقط ارقام، بدون + یا فاصله (مثلاً 989127772167)', 'piazhen'), 'ltr');
                            pzh_render_text_field('contact', 'telegram', __('تلگرام', 'piazhen'), 'url');
                            pzh_render_text_field('contact', 'instagram', __('اینستاگرام', 'piazhen'), 'url');
                            pzh_render_text_field('contact', 'twitter', __('ایکس (توییتر)', 'piazhen'), 'url');
                            pzh_render_text_field('contact', 'linkedin', __('لینکدین', 'piazhen'), 'url');
                            break;

                        case 'images':
                            pzh_render_image_field('images', 'hero_slide', __('اسلایدر اصلی (cover1.png)', 'piazhen'));
                            pzh_render_image_field('images', 'hero_feature', __('تصویر کارت بزرگ (image.png)', 'piazhen'));
                            pzh_render_image_field('images', 'hero_card2', __('تصویر کارت ۲ (card2.png)', 'piazhen'));
                            pzh_render_image_field('images', 'hero_card3', __('تصویر کارت ۳ (card3.png)', 'piazhen'));
                            break;

                        case 'map':
                            pzh_render_text_field('map', 'map_lat', __('عرض جغرافیایی مرکز نقشه', 'piazhen'), 'number', '', 'ltr');
                            pzh_render_text_field('map', 'map_lng', __('طول جغرافیایی مرکز نقشه', 'piazhen'), 'number', '', 'ltr');
                            pzh_render_text_field('map', 'map_zoom', __('زوم پیش‌فرض نقشه', 'piazhen'), 'number', __('عددی بین ۱ تا ۱۹', 'piazhen'), 'ltr');
                            pzh_render_text_field('map', 'osm_tiles', __('آدرس تایل OSM', 'piazhen'), 'url');
                            pzh_render_text_field('map', 'nominatim', __('آدرس Nominatim', 'piazhen'), 'url');
                            pzh_render_text_field('map', 'neshan_key', __('کلید API نقشه نشان', 'piazhen'), 'text', __('اختیاری — بدون آن نقشه با OSM نمایش داده می‌شود.', 'piazhen'), 'ltr');
                            pzh_render_text_field('map', 'neshan_url', __('آدرس API نقشه نشان', 'piazhen'), 'url');
                            break;

                        case 'sms':
                            pzh_render_text_field('sms', 'sms_username', __('نام کاربری Melipayamak', 'piazhen'), 'text', '', 'ltr');
                            pzh_render_text_field('sms', 'sms_password', __('رمز سرویس پیامک', 'piazhen'), 'text', '', 'ltr');
                            pzh_render_text_field('sms', 'sms_sender', __('شماره ارسال‌کننده', 'piazhen'), 'text', '', 'ltr');
                            pzh_render_text_field('sms', 'sms_pattern', __('شناسه الگو (bodyId)', 'piazhen'), 'number', '', 'ltr');
                            break;
                    }
                    ?>
                </table>
                <p class="submit">
                    <button type="submit" name="pzh_<?php echo esc_attr($current_tab); ?>_save" class="button button-primary">
                        <?php _e('ذخیره تنظیمات', 'piazhen'); ?>
                    </button>
                </p>
            </form>
        </div>
    </div>
    <?php
}