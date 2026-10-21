<?php
/**
 * Product FX price fields — a simple product (Product Data → General, right
 * between the regular and sale price inputs) and each product variation
 * (Variations panel) can optionally carry a price in a foreign currency
 * (currency code + value). "USD + 3" means the price equals 3 US dollars.
 *
 * Meta keys (on the product / variation post):
 *   _pzh_fx_currency — ISO currency code (e.g. USD)
 *   _pzh_fx_price    — numeric value in that currency (e.g. 3)
 *
 * The pair is optional: only posts that the admin fills in get the meta;
 * a value without a currency (or vice versa) is dropped on save.
 *
 * Form names: product-level fields use _pzh_fx_currency / _pzh_fx_price
 * (scalar), variation fields use pzh_fx_currency[i] / pzh_fx_price[i] — the
 * names must differ or PHP merges them into one corrupted $_POST array.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Currency options for the select field (ISO code => label).
 *
 * @return array
 */
function pzh_fx_currency_options() {
    $options = array(
        ''    => __('— بدون ارز —', 'piazhen'),
        'USD' => __('دلار آمریکا (USD)', 'piazhen'),
        'EUR' => __('یورو (EUR)', 'piazhen'),
        'AED' => __('درهم امارات (AED)', 'piazhen'),
        'GBP' => __('پوند انگلیس (GBP)', 'piazhen'),
        'TRY' => __('لیر ترکیه (TRY)', 'piazhen'),
        'CNY' => __('یوان چین (CNY)', 'piazhen'),
        'RUB' => __('روبل روسیه (RUB)', 'piazhen'),
        'CAD' => __('دلار کانادا (CAD)', 'piazhen'),
        'AUD' => __('دلار استرالیا (AUD)', 'piazhen'),
        'CHF' => __('فرانک سوئیس (CHF)', 'piazhen'),
        'JPY' => __('ین ژاپن (JPY)', 'piazhen'),
        'SAR' => __('ریال عربستان (SAR)', 'piazhen'),
        'QAR' => __('ریال قطر (QAR)', 'piazhen'),
        'KWD' => __('دینار کویت (KWD)', 'piazhen'),
        'IQD' => __('دینار عراق (IQD)', 'piazhen'),
        'AFN' => __('افغانی (AFN)', 'piazhen'),
    );
    return apply_filters('pzh_fx_currency_options', $options);
}

/**
 * Persist (or clear) an FX pair for one variation ID.
 *
 * Only a complete pair (valid currency + positive value) is stored; anything
 * partial is removed so get_post_meta never returns half-configured data.
 */
function pzh_fx_save_pair($variation_id, $currency, $price) {
    if (!preg_match('/^[A-Z]{3}$/', strtoupper($currency))) {
        $currency = '';
    }

    if ($currency !== '' && $price !== '' && is_numeric($price) && floatval($price) > 0) {
        update_post_meta($variation_id, '_pzh_fx_currency', strtoupper($currency));
        update_post_meta($variation_id, '_pzh_fx_price', wc_format_decimal($price));
    } else {
        delete_post_meta($variation_id, '_pzh_fx_currency');
        delete_post_meta($variation_id, '_pzh_fx_price');
    }
}

/**
 * Render the FX fields in Product Data → General, between the regular and
 * sale price inputs (woocommerce_product_options_pricing). The surrounding
 * options group is shown for simple products only, so variable products
 * never display these — their prices live in the variations.
 */
function pzh_fx_product_fields() {
    global $product_object;

    $product_id = ($product_object && is_object($product_object)) ? $product_object->get_id() : 0;
    $currency   = $product_id ? (string) get_post_meta($product_id, '_pzh_fx_currency', true) : '';
    $price      = $product_id ? (string) get_post_meta($product_id, '_pzh_fx_price', true) : '';

    woocommerce_wp_select(array(
        'id'            => '_pzh_fx_currency',
        'label'         => __('ارز محصول', 'piazhen'),
        'description'   => __('اختیاری — در صورت انتخاب، قیمت محصول معادل همین ارز در نظر گرفته می‌شود.', 'piazhen'),
        'options'       => pzh_fx_currency_options(),
        'value'         => $currency,
        'desc_tip'      => true,
        'wrapper_class' => 'show_if_simple',
    ));

    woocommerce_wp_text_input(array(
        'id'                => '_pzh_fx_price',
        'label'             => __('قیمت به ارز انتخابی', 'piazhen'),
        'description'       => __('اختیاری — مثلاً ۳ یعنی قیمت محصول معادل ۳ واحد از ارز انتخابی است. بدون انتخاب ارز، این مقدار ذخیره نمی‌شود.', 'piazhen'),
        'type'              => 'number',
        'custom_attributes' => array('step' => '0.01', 'min' => '0', 'dir' => 'ltr'),
        'value'             => $price,
        'desc_tip'          => true,
        'wrapper_class'     => 'show_if_simple',
    ));
}
add_action('woocommerce_product_options_pricing', 'pzh_fx_product_fields');

/**
 * Save the product-level FX fields.
 */
function pzh_fx_save_product_fields($product_id) {
    $currency = (isset($_POST['_pzh_fx_currency']) && is_scalar($_POST['_pzh_fx_currency']))
        ? sanitize_text_field(wp_unslash($_POST['_pzh_fx_currency']))
        : '';
    $price = (isset($_POST['_pzh_fx_price']) && is_scalar($_POST['_pzh_fx_price']))
        ? sanitize_text_field(wp_unslash($_POST['_pzh_fx_price']))
        : '';

    pzh_fx_save_pair($product_id, $currency, $price);
}
add_action('woocommerce_process_product_meta', 'pzh_fx_save_product_fields');

/**
 * Render the FX fields in each variation row of the Variations panel.
 *
 * Kept minimal and defensive: nothing here must ever be able to fatal and
 * break the variation rows (they load over AJAX, so any error would leave
 * the panel empty).
 */
function pzh_fx_variation_fields($loop, $variation_data, $variation) {
    $variation_id = (is_object($variation) && method_exists($variation, 'get_id')) ? $variation->get_id() : 0;
    $currency     = $variation_id ? (string) get_post_meta($variation_id, '_pzh_fx_currency', true) : '';
    $price        = $variation_id ? (string) get_post_meta($variation_id, '_pzh_fx_price', true) : '';

    echo '<p class="form-field pzh-fx-field">';
    echo '<label for="pzh_fx_currency_' . esc_attr($loop) . '">' . esc_html__('ارز محصول', 'piazhen') . '</label>';
    echo '<select id="pzh_fx_currency_' . esc_attr($loop) . '" name="pzh_fx_currency[' . esc_attr($loop) . ']" class="select short">';
    foreach (pzh_fx_currency_options() as $code => $label) {
        echo '<option value="' . esc_attr($code) . '"' . selected($currency, $code, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></p>';

    echo '<p class="form-field pzh-fx-field">';
    echo '<label for="pzh_fx_price_' . esc_attr($loop) . '">' . esc_html__('قیمت به ارز انتخابی', 'piazhen') . '</label>';
    echo '<input type="number" id="pzh_fx_price_' . esc_attr($loop) . '" name="pzh_fx_price[' . esc_attr($loop) . ']" value="' . esc_attr($price) . '" step="0.01" min="0" dir="ltr" placeholder="' . esc_attr__('مثلاً ۳', 'piazhen') . '"></p>';
}
add_action('woocommerce_product_after_variable_attributes', 'pzh_fx_variation_fields', 10, 3);

/**
 * Save the FX fields for one variation.
 */
function pzh_fx_save_variation_fields($variation_id, $loop) {
    $currencies = isset($_POST['pzh_fx_currency']) ? (array) wp_unslash($_POST['pzh_fx_currency']) : array();
    $prices     = isset($_POST['pzh_fx_price']) ? (array) wp_unslash($_POST['pzh_fx_price']) : array();

    $currency = (isset($currencies[$loop]) && is_scalar($currencies[$loop])) ? sanitize_text_field($currencies[$loop]) : '';
    $price    = (isset($prices[$loop]) && is_scalar($prices[$loop])) ? sanitize_text_field($prices[$loop]) : '';

    pzh_fx_save_pair($variation_id, $currency, $price);
}
add_action('woocommerce_save_product_variation', 'pzh_fx_save_variation_fields', 10, 2);

/**
 * FX price of a product or variation.
 *
 * For variations, falls back to the parent product's FX pair when the
 * variation itself has none.
 *
 * @param int  $product_id         Product or variation ID.
 * @param bool $fallback_to_parent When true and $product_id is a variation,
 *                                 check the parent product too.
 * @return array|null array('currency' => 'USD', 'value' => 3.0) or null
 *                    when no complete FX price pair exists.
 */
function pzh_get_product_fx_price($product_id, $fallback_to_parent = true) {
    $product_id = intval($product_id);
    $currency   = get_post_meta($product_id, '_pzh_fx_currency', true);
    $value      = get_post_meta($product_id, '_pzh_fx_price', true);

    if ($fallback_to_parent && (!$currency || !is_numeric($value) || floatval($value) <= 0)) {
        $parent_id = wp_get_post_parent_id($product_id);
        if ($parent_id) {
            return pzh_get_product_fx_price($parent_id, false);
        }
    }

    if (!$currency || !is_numeric($value) || floatval($value) <= 0) {
        return null;
    }

    return array(
        'currency' => strtoupper($currency),
        'value'    => floatval($value),
    );
}
