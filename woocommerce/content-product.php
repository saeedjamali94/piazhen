<?php
/**
 * WooCommerce Product Card (Archive Loop)
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;

// Ensure visibility
if (empty($product) || !$product->is_visible()) {
    return;
}

echo pzh_get_product_card_html($product->get_id());
