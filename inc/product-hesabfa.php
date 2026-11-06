<?php
/**
 * Product Hesabfa item code field — maps a WooCommerce product / variation to
 * its Hesabfa کالا code so the live stock (status + count) can be shown.
 *
 * Meta key: _pzh_hesabfa_code (optional — when empty, the product SKU is used
 * as the Hesabfa code, see PZH_Hesabfa::product_code()).
 *
 * Form names: product-level uses the scalar _pzh_hesabfa_code (rendered next
 * to the SKU in Product Data → Inventory); variation rows use
 * pzh_hesabfa_code[i] — different names so PHP doesn't merge the arrays.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the field next to the SKU (Product Data → Inventory).
 */
function pzh_hesabfa_product_field() {
    global $product_object;

    $product_id = ($product_object && is_object($product_object)) ? $product_object->get_id() : 0;
    $code       = $product_id ? (string) get_post_meta($product_id, '_pzh_hesabfa_code', true) : '';

    woocommerce_wp_text_input(array(
        'id'                => '_pzh_hesabfa_code',
        'label'             => __('کد کالای حسابفا', 'piazhen'),
        'description'       => __('کد کالا در حسابفا (مثلاً 000123) — برای نمایش موجودی واقعی از حسابفا. خالی = استفاده از شناسه محصول (SKU).', 'piazhen'),
        'value'             => $code,
        'desc_tip'          => true,
        'custom_attributes' => array('dir' => 'ltr'),
    ));
}
add_action('woocommerce_product_options_sku', 'pzh_hesabfa_product_field');

/**
 * Save the product-level Hesabfa code.
 */
function pzh_hesabfa_save_product_field($product_id) {
    $code = (isset($_POST['_pzh_hesabfa_code']) && is_scalar($_POST['_pzh_hesabfa_code']))
        ? sanitize_text_field(wp_unslash($_POST['_pzh_hesabfa_code']))
        : '';

    if ($code !== '') {
        update_post_meta($product_id, '_pzh_hesabfa_code', $code);
    } else {
        delete_post_meta($product_id, '_pzh_hesabfa_code');
    }
}
add_action('woocommerce_process_product_meta', 'pzh_hesabfa_save_product_field');

/**
 * Render the field in each variation row (WC 10 passes $variation as WP_Post —
 * see pzh_fx_variation_fields for the same caveat).
 */
function pzh_hesabfa_variation_field($loop, $variation_data, $variation) {
    $variation_id = 0;
    if ($variation instanceof WP_Post) {
        $variation_id = intval($variation->ID);
    } elseif (is_object($variation) && method_exists($variation, 'get_id')) {
        $variation_id = intval($variation->get_id());
    }

    $code = $variation_id ? (string) get_post_meta($variation_id, '_pzh_hesabfa_code', true) : '';

    echo '<p class="form-field pzh-hesabfa-field">';
    echo '<label for="pzh_hesabfa_code_' . esc_attr($loop) . '">' . esc_html__('کد حسابفا', 'piazhen') . '</label>';
    echo '<input type="text" id="pzh_hesabfa_code_' . esc_attr($loop) . '" name="pzh_hesabfa_code[' . esc_attr($loop) . ']" value="' . esc_attr($code) . '" dir="ltr" placeholder="' . esc_attr__('مثلاً 000123', 'piazhen') . '">';
    echo '</p>';
}
add_action('woocommerce_product_after_variable_attributes', 'pzh_hesabfa_variation_field', 10, 3);

/**
 * Save the variation-level Hesabfa code.
 */
function pzh_hesabfa_save_variation_field($variation_id, $loop) {
    $codes = isset($_POST['pzh_hesabfa_code']) ? (array) wp_unslash($_POST['pzh_hesabfa_code']) : array();

    $code = (isset($codes[$loop]) && is_scalar($codes[$loop])) ? sanitize_text_field($codes[$loop]) : '';

    if ($code !== '') {
        update_post_meta($variation_id, '_pzh_hesabfa_code', $code);
    } else {
        delete_post_meta($variation_id, '_pzh_hesabfa_code');
    }
}
add_action('woocommerce_save_product_variation', 'pzh_hesabfa_save_variation_field', 10, 2);
