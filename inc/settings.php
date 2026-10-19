<?php
/**
 * Piazhen Site Settings — admin panel for site-wide configuration
 *
 * Uses the native WordPress Options API. One option array per tab:
 *   pzh_settings_general | contact | hero | menu | delivery | map | sms
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
            'phones'    => '۰۹۱۲ ۷۷۷ ۲۱ ۶۷ - ۰۹۱۲ ۰۴۳ ۴۹ ۴۳ - ۰۲۱ ۹۱۶۹۲۲۳۲',
            'emails'    => 'info@piazhen.ir',
            'address'   => "خیابان جمهوری، بعد از ولیعصر، پاساژ علائدین آرایشی، طبقه\nهمکف، واحد ۲۲",
            'hours'     => "شنبه تا چهارشنبه: ۹ صبح تا ۶ عصر\nپنجشنبه: ۹ صبح تا ۱ بعدازظهر\nجمعه: تعطیل",
            'whatsapp'  => '989127772167',
            'telegram'  => 'https://t.me/piazhen',
            'instagram' => 'https://instagram.com/piazhen',
            'twitter'   => 'https://x.com/piazhen',
            'linkedin'  => 'https://linkedin.com/company/piazhen',
        ),
        'hero' => array(
            // Carousel slides live in the 'slides' repeater (array of
            // array('image' => …, 'link' => …)) — see pzh_hero_slide_rows().
            // Two stacked side tiles
            'side_1_image'  => PZH_THEME_URI . '/assets/images/image.png',
            'side_1_title'  => __('اصلاح سر و صورت', 'piazhen'),
            'side_1_link'   => '',
            'side_2_image'  => PZH_THEME_URI . '/assets/images/card2.png',
            'side_2_title'  => __('اپیلاتور', 'piazhen'),
            'side_2_link'   => '',
            // Bottom row tiles
            'bottom_1_image' => PZH_THEME_URI . '/assets/images/card3.png',
            'bottom_1_title' => __('سایر محصولات', 'piazhen'),
            'bottom_1_link'  => '',
            'bottom_2_image' => PZH_THEME_URI . '/assets/images/card2.png',
            'bottom_2_title' => __('حالت دهنده مو', 'piazhen'),
            'bottom_2_link'  => '',
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
        'instagram' => array(
            'instagram_username' => 'piazhen',
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
 * FAQ items from the settings repeater (tab 'faq'): array of
 * array('question' => …, 'answer' => …). Empty until the admin adds items.
 */
function pzh_faq_items() {
    $saved = get_option('pzh_settings_faq', array());
    if (is_array($saved) && isset($saved['items']) && is_array($saved['items'])) {
        return $saved['items'];
    }
    return array();
}

/**
 * Resolve a stored image value to a URL.
 *
 * Handles three storage formats:
 *  - empty string → returns $default
 *  - numeric (attachment ID) → wp_get_attachment_image_url()
 *  - full URL → returned as-is
 *  - bare filename → prefixed with PZH_THEME_URI (legacy compat)
 */
function pzh_resolve_image_value($value, $default = '') {
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

/**
 * Resolve an image setting to a URL.
 */
function pzh_setting_image($tab, $key) {
    $value    = pzh_setting($tab, $key);
    $defaults = pzh_settings_defaults($tab);
    $default  = isset($defaults[$key]) ? $defaults[$key] : '';
    return pzh_resolve_image_value($value, $default);
}

// ============================================================================
// Hero carousel slides (repeater)
// ============================================================================

/**
 * Raw hero slide rows as stored in settings: array of array('image' => …, 'link' => …).
 *
 *  - If the 'slides' repeater was saved → those rows (may be empty).
 *  - Else if legacy fixed slide_1..3 fields hold values → migrated from them.
 *  - Else out-of-box default: 3 slides with the theme cover image.
 */
function pzh_hero_slide_rows() {
    $saved = get_option('pzh_settings_hero', array());
    if (!is_array($saved)) {
        $saved = array();
    }

    if (isset($saved['slides']) && is_array($saved['slides'])) {
        return $saved['slides'];
    }

    // Legacy: previously saved fixed slide fields (before the repeater)
    $rows = array();
    for ($i = 1; $i <= 3; $i++) {
        $image = isset($saved["slide_{$i}_image"]) ? $saved["slide_{$i}_image"] : '';
        $link  = isset($saved["slide_{$i}_link"]) ? $saved["slide_{$i}_link"] : '';
        if ($image !== '' || $link !== '') {
            $rows[] = array('image' => $image, 'link' => $link);
        }
    }
    if (!empty($rows)) {
        return $rows;
    }

    // Out-of-box: three slides with the default cover image
    $cover = PZH_THEME_URI . '/assets/images/cover1.png';
    return array(
        array('image' => $cover, 'link' => ''),
        array('image' => $cover, 'link' => ''),
        array('image' => $cover, 'link' => ''),
    );
}

/**
 * Resolved slides for the front-end: array of array('image' => url, 'link' => url).
 * Rows without an image are skipped; empty link = shop page.
 */
function pzh_hero_slides($shop_url = '') {
    if (!$shop_url) {
        $shop_url = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : SITE_URL . '/shop';
    }

    $slides = array();
    foreach (pzh_hero_slide_rows() as $row) {
        if (!is_array($row)) {
            continue;
        }
        $image = pzh_resolve_image_value(isset($row['image']) ? $row['image'] : '');
        if (!$image) {
            continue;
        }
        $slides[] = array(
            'image' => $image,
            'link'  => !empty($row['link']) ? $row['link'] : $shop_url,
        );
    }
    return $slides;
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
    // The tiles URL must be a Leaflet {z}/{x}/{y} template. A value saved
    // without the placeholders (e.g. /z/x/y.png) makes the browser request
    // the literal path and tile.openstreetmap.org answers 400 for every tile.
    $tiles = pzh_setting('map', 'osm_tiles');
    if (!is_string($tiles)
        || strpos($tiles, '{z}') === false
        || strpos($tiles, '{x}') === false
        || strpos($tiles, '{y}') === false
    ) {
        $tiles = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
    }

    // OSM serves tiles up to zoom 19 — requests above that get a 400.
    $zoom = absint(pzh_setting('map', 'map_zoom'));
    $zoom = max(5, min(19, $zoom));

    return array(
        'lat'   => floatval(pzh_setting('map', 'map_lat')),
        'lng'   => floatval(pzh_setting('map', 'map_lng')),
        'zoom'  => $zoom,
        'tiles' => $tiles,
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
        'compare_limit'        => __('حداکثر ۲ محصول قابل مقایسه است.', 'piazhen'),
        'compare_title'        => __('مقایسه محصولات', 'piazhen'),
        'compare_clear'        => __('پاک کردن لیست مقایسه', 'piazhen'),
        'compare_close'        => __('بستن', 'piazhen'),
        'compare_empty'        => __('لیست مقایسه خالی است.', 'piazhen'),
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
        // Contact form (contact.png)
        'contact_name_required'  => __('نام الزامی است.', 'piazhen'),
        'contact_family_required' => __('نام خانوادگی الزامی است.', 'piazhen'),
        'contact_phone_invalid'  => __('شماره تماس معتبر نیست. (مثال: 09123456789)', 'piazhen'),
        'contact_email_invalid'  => __('ایمیل وارد شده معتبر نیست.', 'piazhen'),
        'contact_subject_required' => __('لطفاً موضوع را انتخاب کنید.', 'piazhen'),
        'contact_message_short'  => __('متن پیام خیلی کوتاه است.', 'piazhen'),
        'contact_sending'        => __('در حال ارسال...', 'piazhen'),
        'contact_send_error'     => __('خطا در ارسال پیام. لطفاً دوباره تلاش کنید.', 'piazhen'),
    ));
}

// ============================================================================
// Admin: Menu Registration
// ============================================================================

function pzh_admin_settings_menu() {
    add_menu_page(
        __('تنظیمات پیاژن', 'piazhen'),
        __('تنظیمات پیاژن', 'piazhen'),
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
    // Filemtime-based versions (pzh_asset_version) so the browser never
    // serves a stale cached copy after an edit — the theme version alone
    // doesn't change on every edit.
    wp_enqueue_script(
        'pzh-settings-admin',
        PZH_THEME_URI . '/assets/js/settings-admin.js',
        array('jquery'),
        pzh_asset_version('/assets/js/settings-admin.js'),
        true
    );
    wp_enqueue_style(
        'pzh-settings-admin',
        PZH_THEME_URI . '/assets/css/settings-admin.css',
        array(),
        pzh_asset_version('/assets/css/settings-admin.css')
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
        case 'textarea':
            return sanitize_textarea_field($value);
        case 'products':
            // Multi-select of product IDs (array) → unique positive ints
            $ids = array_map('absint', (array) $value);
            return array_values(array_filter(array_unique($ids)));
        case 'hero_slides':
            // Repeater rows: array('image' => …, 'link' => …)
            $rows = array();
            foreach ((array) $value as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $image = isset($row['image']) ? $row['image'] : '';
                $link  = isset($row['link']) ? $row['link'] : '';
                // Image: attachment ID, URL, or empty
                $image = is_numeric($image) ? absint($image) : (empty($image) ? '' : esc_url_raw($image));
                $link  = esc_url_raw($link);
                if ($image === '' && $link === '') {
                    continue; // drop fully-empty rows
                }
                $rows[] = array('image' => $image, 'link' => $link);
            }
            return $rows;
        case 'menu_items':
            // Repeater of selected item IDs (menu items / brand terms)
            $ids = array_map('absint', (array) $value);
            return array_values(array_filter(array_unique($ids)));
        case 'faq_items':
            // Repeater rows: array('question' => …, 'answer' => …)
            $rows = array();
            foreach ((array) $value as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $question = isset($row['question']) ? sanitize_text_field($row['question']) : '';
                $answer   = isset($row['answer']) ? sanitize_textarea_field($row['answer']) : '';
                if ($question === '' && $answer === '') {
                    continue;
                }
                $rows[] = array('question' => $question, 'answer' => $answer);
            }
            return $rows;
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
 * Render a multi-line textarea row (address / working hours).
 */
function pzh_render_textarea_field($tab, $key, $label, $description = '', $rows = 3, $dir = '') {
    $value    = pzh_setting($tab, $key);
    $dir_attr = $dir ? ' dir="' . esc_attr($dir) . '"' : '';
    ?>
    <tr>
        <th scope="row">
            <label for="pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>">
                <?php echo esc_html($label); ?>
            </label>
        </th>
        <td>
            <textarea id="pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>"
                      name="pzh_<?php echo esc_attr($tab); ?>[<?php echo esc_attr($key); ?>]"
                      class="large-text"
                      rows="<?php echo esc_attr($rows); ?>"
                      <?php echo $dir_attr; ?>><?php echo esc_textarea($value); ?></textarea>
            <?php if ($description): ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Render a section heading row (groups fields inside a tab).
 */
function pzh_render_section_row($title, $description = '') {
    ?>
    <tr class="pzh-section-row">
        <th scope="row" colspan="2">
            <h2 style="margin: 0.5em 0 0.25em;"><?php echo esc_html($title); ?></h2>
            <?php if ($description): ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </th>
    </tr>
    <?php
}

/**
 * Render one hero-slide repeater row (image + link). $index is the numeric
 * row key, or '__i__' for the JS template row.
 */
function pzh_render_slide_row($index, $row = array()) {
    $image   = isset($row['image']) ? $row['image'] : '';
    $link    = isset($row['link']) ? $row['link'] : '';
    $img_src = pzh_resolve_image_value($image, '');
    $prefix  = $index;
    ?>
    <div class="pzh-repeater-row">
        <div class="pzh-media-row">
            <input type="hidden"
                   name="pzh_hero[slides][<?php echo esc_attr($prefix); ?>][image]"
                   id="pzh_hero_slides_<?php echo esc_attr($prefix); ?>_image"
                   value="<?php echo esc_attr($image); ?>">
            <div class="pzh-media-preview" id="pzh_preview_hero_slides_<?php echo esc_attr($prefix); ?>_image">
                <?php if ($img_src): ?>
                    <img src="<?php echo esc_url($img_src); ?>" alt="" style="max-width:120px;max-height:80px;">
                <?php endif; ?>
            </div>
            <button type="button"
                    class="button pzh-media-upload"
                    data-target="pzh_hero_slides_<?php echo esc_attr($prefix); ?>_image"
                    data-preview="pzh_preview_hero_slides_<?php echo esc_attr($prefix); ?>_image">
                <?php _e('انتخاب تصویر', 'piazhen'); ?>
            </button>
        </div>
        <input type="url"
               name="pzh_hero[slides][<?php echo esc_attr($prefix); ?>][link]"
               class="regular-text pzh-repeater-link"
               value="<?php echo esc_attr($link); ?>"
               dir="ltr"
               placeholder="<?php esc_attr_e('خالی = صفحه فروشگاه', 'piazhen'); ?>">
        <button type="button" class="button pzh-repeater-remove"><?php _e('حذف اسلاید', 'piazhen'); ?></button>
    </div>
    <?php
}

/**
 * Render the hero slides repeater (saved rows + hidden JS template + add button).
 */
function pzh_render_hero_slides_repeater() {
    $rows = pzh_hero_slide_rows();
    ?>
    <tr>
        <th scope="row">
            <label><?php _e('اسلایدهای کاروسل', 'piazhen'); ?></label>
        </th>
        <td>
            <div class="pzh-repeater" id="pzh_hero_slides" data-next-index="<?php echo esc_attr(count($rows)); ?>">
                <?php foreach ($rows as $i => $row): ?>
                    <?php pzh_render_slide_row($i, $row); ?>
                <?php endforeach; ?>

                <!-- JS template for new rows ('__i__' replaced on clone) -->
                <div class="pzh-repeater-template" style="display:none;">
                    <?php pzh_render_slide_row('__i__'); ?>
                </div>
            </div>
            <p>
                <button type="button" class="button pzh-repeater-add" data-target="#pzh_hero_slides">
                    <?php _e('افزودن اسلاید', 'piazhen'); ?>
                </button>
            </p>
            <p class="description"><?php _e('تعداد اسلایدها دلخواه است؛ هر اسلاید فقط یک تصویر + لینک دارد. اسلاید بدون تصویر نمایش داده نمی‌شود.', 'piazhen'); ?></p>
        </td>
    </tr>
    <?php
}

/**
 * Render one select-repeater row (dropdown + remove button). $prefix is the
 * numeric row key, or '__i__' for the JS template row.
 */
function pzh_render_select_row($tab, $key, $prefix, $options, $value = '') {
    ?>
    <div class="pzh-repeater-row">
        <select name="pzh_<?php echo esc_attr($tab); ?>[<?php echo esc_attr($key); ?>][<?php echo esc_attr($prefix); ?>]"
                class="regular-text pzh-repeater-select">
            <option value=""><?php _e('— انتخاب —', 'piazhen'); ?></option>
            <?php foreach ($options as $id => $label): ?>
                <option value="<?php echo esc_attr($id); ?>" <?php selected((string) $id === (string) $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="button pzh-repeater-remove"><?php _e('حذف', 'piazhen'); ?></button>
    </div>
    <?php
}

/**
 * Render a dropdown repeater: $rows are the current selected values, $max
 * caps how many rows can be added (JS enforces it).
 */
function pzh_render_select_repeater($tab, $key, $label, $options, $rows, $max, $description = '') {
    ?>
    <tr>
        <th scope="row">
            <label><?php echo esc_html($label); ?></label>
        </th>
        <td>
            <div class="pzh-repeater"
                 id="pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>"
                 data-next-index="<?php echo esc_attr(count($rows)); ?>"
                 <?php if ($max > 0): ?>data-max="<?php echo esc_attr($max); ?>"<?php endif; ?>>
                <?php foreach ($rows as $i => $value): ?>
                    <?php pzh_render_select_row($tab, $key, $i, $options, $value); ?>
                <?php endforeach; ?>

                <!-- JS template for new rows ('__i__' replaced on clone) -->
                <div class="pzh-repeater-template" style="display:none;">
                    <?php pzh_render_select_row($tab, $key, '__i__', $options); ?>
                </div>
            </div>
            <p>
                <button type="button" class="button pzh-repeater-add" data-target="#pzh_<?php echo esc_attr($tab); ?>_<?php echo esc_attr($key); ?>">
                    <?php _e('افزودن', 'piazhen'); ?>
                </button>
            </p>
            <?php if ($description): ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Render one FAQ repeater row (question input + answer textarea). $prefix is
 * the numeric row key, or '__i__' for the JS template row.
 */
function pzh_render_faq_row($prefix, $row = array()) {
    $question = isset($row['question']) ? $row['question'] : '';
    $answer   = isset($row['answer']) ? $row['answer'] : '';
    ?>
    <div class="pzh-repeater-row pzh-repeater-row--faq">
        <div class="pzh-faq-row-fields">
            <input type="text"
                   class="large-text"
                   name="pzh_faq[items][<?php echo esc_attr($prefix); ?>][question]"
                   value="<?php echo esc_attr($question); ?>"
                   placeholder="<?php esc_attr_e('سوال', 'piazhen'); ?>">
            <textarea class="large-text" rows="3"
                      name="pzh_faq[items][<?php echo esc_attr($prefix); ?>][answer]"
                      placeholder="<?php esc_attr_e('پاسخ', 'piazhen'); ?>"><?php echo esc_textarea($answer); ?></textarea>
        </div>
        <button type="button" class="button pzh-repeater-remove"><?php _e('حذف', 'piazhen'); ?></button>
    </div>
    <?php
}

/**
 * Render the FAQ repeater (saved rows + hidden JS template + add button).
 */
function pzh_render_faq_repeater() {
    $rows = pzh_faq_items();
    ?>
    <tr>
        <th scope="row">
            <label><?php _e('سوال و جواب‌ها', 'piazhen'); ?></label>
        </th>
        <td>
            <div class="pzh-repeater" id="pzh_faq_items" data-next-index="<?php echo esc_attr(count($rows)); ?>">
                <?php foreach ($rows as $i => $row): ?>
                    <?php pzh_render_faq_row($i, $row); ?>
                <?php endforeach; ?>

                <!-- JS template for new rows ('__i__' replaced on clone) -->
                <div class="pzh-repeater-template" style="display:none;">
                    <?php pzh_render_faq_row('__i__'); ?>
                </div>
            </div>
            <p>
                <button type="button" class="button pzh-repeater-add" data-target="#pzh_faq_items">
                    <?php _e('افزودن سوال', 'piazhen'); ?>
                </button>
            </p>
            <p class="description"><?php _e('این سوال‌ها در صفحه «سوالات متداول» (قالب صفحه FAQ) به صورت آکاردئون نمایش داده می‌شوند.', 'piazhen'); ?></p>
        </td>
    </tr>
    <?php
}

/**
 * Save handler for a settings tab.
 */
function pzh_save_settings_tab($tab, $field_types) {
    if (!isset($_POST['pzh_' . $tab . '_save']) && !($tab === 'instagram' && isset($_POST['pzh_instagram_purge']))) {
        return;
    }
    if (!check_admin_referer('pzh_settings_' . $tab)) {
        return;
    }

    // Purge button on the Instagram tab — clear the cache and fetch the
    // newest posts right now so the admin sees the result immediately.
    if ($tab === 'instagram' && isset($_POST['pzh_instagram_purge'])) {
        pzh_instagram_clear_cache();
        pzh_instagram_refresh_cache();
        $feed = get_transient('pzh_instagram_feed');
        if (is_array($feed) && !empty($feed)) {
            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__('کش پاک شد و %d پست جدید از اینستاگرام دریافت شد.', 'piazhen'), count($feed)) . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('کش پاک شد اما اتصال به اینستاگرام برقرار نشد. سرور باید به instagram.com دسترسی داشته باشد؛ تلاش بعدی خودکار انجام می‌شود.', 'piazhen') . '</p></div>';
        }
        return;
    }

    $new   = array();
    $input = isset($_POST['pzh_' . $tab]) ? wp_unslash($_POST['pzh_' . $tab]) : array();

    foreach ($field_types as $key => $type) {
        $raw = isset($input[$key]) ? $input[$key] : '';
        $new[$key] = pzh_sanitize_setting($raw, $type);
    }

    update_option('pzh_settings_' . $tab, $new);

    // Username may have changed — drop the cached feed so it refreshes now
    if ($tab === 'instagram') {
        pzh_instagram_clear_cache();
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('تنظیمات ذخیره شد.', 'piazhen') . '</p></div>';
}

/**
 * Main render callback.
 */
function pzh_admin_settings_render() {
    $tabs = array(
        'general' => __('عمومی', 'piazhen'),
        'contact' => __('تماس و شبکه‌های اجتماعی', 'piazhen'),
        'hero'    => __('هیرو صفحه اصلی', 'piazhen'),
        'menu'    => __('مگامنو', 'piazhen'),
        'delivery' => __('ارسال رایگان', 'piazhen'),
        'faq'      => __('سوالات متداول', 'piazhen'),
        'map'     => __('نقشه و API', 'piazhen'),
        'sms'     => __('پیامک', 'piazhen'),
        'instagram' => __('اینستاگرام', 'piazhen'),
    );

    $current_tab = isset($_GET['tab']) && isset($tabs[$_GET['tab']]) ? $_GET['tab'] : 'general';

    // Field definitions per tab: key => sanitize_type
    $field_types = array(
        'general' => array(
            'logo' => 'image', 'login_cover' => 'image', 'sprite' => 'image',
        ),
        'contact' => array(
            'phones' => 'text', 'emails' => 'textarea', 'address' => 'textarea', 'hours' => 'textarea',
            'whatsapp' => 'digits',
            'telegram' => 'url', 'instagram' => 'url',
            'twitter' => 'url', 'linkedin' => 'url',
        ),
        'hero' => array(
            'slides' => 'hero_slides',
            'side_1_image' => 'image', 'side_1_title' => 'text', 'side_1_link' => 'url',
            'side_2_image' => 'image', 'side_2_title' => 'text', 'side_2_link' => 'url',
            'bottom_1_image' => 'image', 'bottom_1_title' => 'text', 'bottom_1_link' => 'url',
            'bottom_2_image' => 'image', 'bottom_2_title' => 'text', 'bottom_2_link' => 'url',
        ),
        'delivery' => array(
            'threshold' => 'number', 'products' => 'products',
        ),
        'faq' => array(
            'items' => 'faq_items',
        ),
        'menu' => array(
            'header_items' => 'menu_items', 'brand_items' => 'menu_items',
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
        'instagram' => array(
            'instagram_username' => 'text',
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
                            pzh_render_text_field('contact', 'phones', __('شماره‌های تماس', 'piazhen'), 'text', __('با خط تیره جدا کنید؛ هر شماره در یک خطِ کارت تماس نمایش داده می‌شود.', 'piazhen'), 'ltr');
                            pzh_render_textarea_field('contact', 'emails', __('ایمیل‌ها', 'piazhen'), __('هر ایمیل در یک خط (اولین ایمیل، گیرنده پیام‌های فرم تماس است).', 'piazhen'), 2, 'ltr');
                            pzh_render_textarea_field('contact', 'address', __('آدرس فروشگاه', 'piazhen'), __('هر خط، یک خط از آدرس در صفحه تماس با ما.', 'piazhen'), 3);
                            pzh_render_textarea_field('contact', 'hours', __('ساعات کاری', 'piazhen'), __('هر روز در یک خط؛ در ستون کنار فرم تماس نمایش داده می‌شود.', 'piazhen'), 4);
                            pzh_render_text_field('contact', 'whatsapp', __('شماره واتساپ', 'piazhen'), 'text', __('فقط ارقام، بدون + یا فاصله (مثلاً 989127772167)', 'piazhen'), 'ltr');
                            pzh_render_text_field('contact', 'telegram', __('تلگرام', 'piazhen'), 'url');
                            pzh_render_text_field('contact', 'instagram', __('اینستاگرام', 'piazhen'), 'url');
                            pzh_render_text_field('contact', 'twitter', __('ایکس (توییتر)', 'piazhen'), 'url');
                            pzh_render_text_field('contact', 'linkedin', __('لینکدین', 'piazhen'), 'url');
                            break;

                        case 'hero':
                            $hero_empty = __('خالی = صفحه فروشگاه', 'piazhen');
                            pzh_render_section_row(__('اسلایدهای کاروسل اصلی', 'piazhen'));
                            pzh_render_hero_slides_repeater();

                            pzh_render_section_row(__('کارت‌های کناری (ستون باریک)', 'piazhen'));
                            pzh_render_image_field('hero', 'side_1_image', __('تصویر کارت کناری ۱', 'piazhen'));
                            pzh_render_text_field('hero', 'side_1_title', __('عنوان کارت کناری ۱', 'piazhen'));
                            pzh_render_text_field('hero', 'side_1_link', __('لینک کارت کناری ۱', 'piazhen'), 'url', $hero_empty, 'ltr');
                            pzh_render_image_field('hero', 'side_2_image', __('تصویر کارت کناری ۲', 'piazhen'));
                            pzh_render_text_field('hero', 'side_2_title', __('عنوان کارت کناری ۲', 'piazhen'));
                            pzh_render_text_field('hero', 'side_2_link', __('لینک کارت کناری ۲', 'piazhen'), 'url', $hero_empty, 'ltr');

                            pzh_render_section_row(__('کارت‌های ردیف پایین', 'piazhen'));
                            pzh_render_image_field('hero', 'bottom_1_image', __('تصویر کارت پایین ۱', 'piazhen'));
                            pzh_render_text_field('hero', 'bottom_1_title', __('عنوان کارت پایین ۱', 'piazhen'));
                            pzh_render_text_field('hero', 'bottom_1_link', __('لینک کارت پایین ۱', 'piazhen'), 'url', $hero_empty, 'ltr');
                            pzh_render_image_field('hero', 'bottom_2_image', __('تصویر کارت پایین ۲', 'piazhen'));
                            pzh_render_text_field('hero', 'bottom_2_title', __('عنوان کارت پایین ۲', 'piazhen'));
                            pzh_render_text_field('hero', 'bottom_2_link', __('لینک کارت پایین ۲', 'piazhen'), 'url', $hero_empty, 'ltr');
                            break;

                        case 'menu':
                            $menu_options = array();
                            foreach (pzh_get_mega_menu_categories() as $cat) {
                                $menu_options[$cat['id']] = $cat['title'];
                            }
                            $brand_options = array();
                            foreach (pzh_get_brand_terms() as $term) {
                                $brand_options[$term->term_id] = $term->name;
                            }
                            pzh_render_select_repeater(
                                'menu', 'header_items',
                                __('هدِرهای مگامنو', 'piazhen'),
                                $menu_options,
                                pzh_mega_menu_header_ids(),
                                0,
                                __('پیش‌فرض ۶ ردیف. هر ردیف را می‌توانید حذف یا اضافه کنید؛ ستون برندها همیشه کنار همین هدرها نمایش داده می‌شود.', 'piazhen')
                            );
                            pzh_render_select_repeater(
                                'menu', 'brand_items',
                                __('برندهای مگامنو', 'piazhen'),
                                $brand_options,
                                pzh_mega_menu_brand_ids(),
                                4,
                                __('حداکثر ۴ برند در ستون برندهای مگامنو نمایش داده می‌شود. ترتیب ردیف‌ها = ترتیب نمایش.', 'piazhen')
                            );
                            break;

                        case 'delivery':
                            $delivery     = pzh_free_delivery_data();
                            $selected_ids = $delivery['product_ids'];
                            $products     = get_posts(array(
                                'post_type'      => array('product', 'product_variation'),
                                'post_status'    => 'publish',
                                'posts_per_page' => -1,
                                'orderby'        => 'title',
                                'order'          => 'ASC',
                            ));
                            ?>
                            <tr>
                                <th scope="row">
                                    <label for="pzh_delivery_threshold"><?php _e('حداقل مبلغ برای ارسال رایگان (تومان)', 'piazhen'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="pzh_delivery_threshold" name="pzh_delivery[threshold]" class="regular-text"
                                           value="<?php echo esc_attr($delivery['threshold']); ?>" min="0" step="1000" style="direction: ltr;">
                                    <p class="description"><?php _e('مثلاً ۵۰۰۰۰۰۰ — اگر مجموع سبد کمتر از این مبلغ باشد، نوار پیشرفت و محصولات پیشنهادی نمایش داده می‌شود. خالی یا ۰ یعنی ارسال رایگان غیرفعال.', 'piazhen'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="pzh_delivery_products"><?php _e('محصولات پیشنهادی', 'piazhen'); ?></label>
                                </th>
                                <td>
                                    <select id="pzh_delivery_products" name="pzh_delivery[products][]" multiple size="12" style="min-width: 420px; max-width: 100%;">
                                        <?php foreach ($products as $product_post): ?>
                                            <?php $product = function_exists('wc_get_product') ? wc_get_product($product_post) : false; ?>
                                            <?php if (!$product || !$product->is_purchasable()) continue; ?>
                                            <option value="<?php echo esc_attr($product_post->ID); ?>" <?php selected(in_array($product_post->ID, $selected_ids, true)); ?>>
                                                <?php echo esc_html($product->get_name()); ?> (#<?php echo $product_post->ID; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php _e('برای انتخاب چند محصول، کلید Ctrl (یا Cmd) را نگه دارید. این محصولات وقتی سبد به حد ارسال رایگان نرسیده پیشنهاد می‌شوند.', 'piazhen'); ?></p>
                                </td>
                            </tr>
                            <?php
                            break;

                        case 'faq':
                            pzh_render_faq_repeater();
                            break;

                        case 'map':
                            pzh_render_text_field('map', 'map_lat', __('عرض جغرافیایی مرکز نقشه', 'piazhen'), 'number', '', 'ltr');
                            pzh_render_text_field('map', 'map_lng', __('طول جغرافیایی مرکز نقشه', 'piazhen'), 'number', '', 'ltr');
                            pzh_render_text_field('map', 'map_zoom', __('زوم پیش‌فرض نقشه', 'piazhen'), 'number', __('عددی بین ۱ تا ۱۹', 'piazhen'), 'ltr');
                            pzh_render_text_field('map', 'osm_tiles', __('آدرس تایل OSM', 'piazhen'), 'url', __('الگو باید شامل {z}، {x} و {y} باشد — مثال: https://tile.openstreetmap.org/{z}/{x}/{y}.png', 'piazhen'), 'ltr');
                            pzh_render_text_field('map', 'nominatim', __('آدرس Nominatim', 'piazhen'), 'url');
                            pzh_render_text_field('map', 'neshan_key', __('کلید API نقشه نشان', 'piazhen'), 'text', __('توصیه می‌شود — تایل‌های OSM در ایران گاهی مسدود یا ناپایدار می‌شوند. بدون کلید، نقشه با OSM نمایش داده می‌شود.', 'piazhen'), 'ltr');
                            pzh_render_text_field('map', 'neshan_url', __('آدرس API نقشه نشان', 'piazhen'), 'url');
                            break;

                        case 'sms':
                            pzh_render_text_field('sms', 'sms_username', __('نام کاربری Melipayamak', 'piazhen'), 'text', '', 'ltr');
                            pzh_render_text_field('sms', 'sms_password', __('رمز سرویس پیامک', 'piazhen'), 'text', '', 'ltr');
                            pzh_render_text_field('sms', 'sms_sender', __('شماره ارسال‌کننده', 'piazhen'), 'text', '', 'ltr');
                            pzh_render_text_field('sms', 'sms_pattern', __('شناسه الگو (bodyId)', 'piazhen'), 'number', '', 'ltr');
                            break;

                        case 'instagram':
                            pzh_render_text_field('instagram', 'instagram_username', __('نام کاربری اینستاگرام', 'piazhen'), 'text', __('بدون @ — جدیدترین پست‌های همین حساب در کاروسل صفحه اصلی نمایش داده می‌شوند. سرور باید به instagram.com دسترسی داشته باشد.', 'piazhen'), 'ltr');
                            ?>
                            <tr>
                                <th scope="row"><?php _e('کش فید', 'piazhen'); ?></th>
                                <td>
                                    <button type="submit" name="pzh_instagram_purge" class="button"><?php _e('پاکسازی کش اینستاگرام', 'piazhen'); ?></button>
                                    <p class="description"><?php _e('فید هر ۶ ساعت به‌روز می‌شود؛ برای دریافت فوری پست‌های جدید، کش را پاک کنید.', 'piazhen'); ?></p>
                                </td>
                            </tr>
                            <?php
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