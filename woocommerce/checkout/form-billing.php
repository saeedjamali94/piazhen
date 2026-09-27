<?php
/**
 * Checkout Billing — the whole dynamic form column (shipping choice cards,
 * contact fields, shipping methods and the address form) is rendered by
 * pzh_render_checkout_dynamic() so the update_checkout AJAX fragment can
 * swap it when the pickup/ship choice changes.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

pzh_render_checkout_dynamic();

do_action('woocommerce_after_checkout_billing_form', $checkout);
