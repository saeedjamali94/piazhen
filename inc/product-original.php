<?php
/**
 * Product «اوریجینال» checkbox — marks a product as original. Checked products
 * get an «اوریجینال» badge on the product card image (bottom-left).
 *
 * Global on the parent product (simple AND variable) — no per-variation
 * checkbox; the card represents the parent.
 *
 * Meta key: _pzh_original (WC checkbox convention: 'yes' = checked; '1' is
 * also accepted on read for legacy values).
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Is the product marked as original?
 *
 * @param int $product_id
 * @return bool
 */
function pzh_is_original($product_id) {
    return in_array(get_post_meta((int) $product_id, '_pzh_original', true), array('1', 'yes'), true);
}

/**
 * Render the checkbox in Product Data → General (fires for every product
 * type, so simple and variable products both get it).
 */
function pzh_original_product_field() {
    global $product_object;

    woocommerce_wp_checkbox(array(
        'id'          => '_pzh_original',
        'label'       => __('اوریجینال', 'piazhen'),
        'description' => __('نمایش برچسب «اوریجینال» روی تصویر کارت محصول', 'piazhen'),
    ), ($product_object instanceof WC_Data) ? $product_object : null);
}
add_action('woocommerce_product_options_general_product_data', 'pzh_original_product_field');

/**
 * Save the checkbox (WC checkbox convention: 'yes' when checked).
 */
function pzh_original_save_product_field($product_id) {
    if (isset($_POST['_pzh_original']) && 'yes' === (string) $_POST['_pzh_original']) {
        update_post_meta($product_id, '_pzh_original', 'yes');
    } else {
        delete_post_meta($product_id, '_pzh_original');
    }
}
add_action('woocommerce_process_product_meta', 'pzh_original_save_product_field');
