<?php
/**
 * Order admin + invoice pickup details — for orders shipped via local pickup
 * (تحویل حضوری), show the customer's national ID and phone:
 *
 *  1. WooCommerce order preview modal (Orders admin / wc-orders list):
 *     the national ID is appended inside the Billing details box
 *     (the phone already prints there via core).
 *  2. The woocommerce-invoice-pro invoice page
 *     (?action=wip-print&type=invoice&order-id=…): both the national ID and
 *     phone are appended to the formatted billing address, guarded to that
 *     page so emails / admin / thank-you pages are unaffected.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Is the order shipped via the local pickup method (حضوری تحویل می‌گیرم)?
 *
 * @param WC_Order $order
 * @return bool
 */
function pzh_order_is_pickup($order) {
    if (!$order || !is_callable(array($order, 'get_shipping_methods'))) {
        return false;
    }
    foreach ($order->get_shipping_methods() as $item) {
        $method_id = (string) $item->get_method_id();
        if ($method_id === 'local_pickup' || strpos($method_id, 'local_pickup:') === 0) {
            return true;
        }
    }
    return false;
}

/**
 * Pickup detail lines (national ID + phone) for an order — empty when the
 * values are missing, so pickup orders without them show nothing extra.
 *
 * @param WC_Order $order
 * @return string[] HTML lines.
 */
function pzh_pickup_detail_lines($order) {
    $lines  = array();
    $nid    = (string) $order->get_meta('billing_national_id');
    $phone  = (string) $order->get_billing_phone();

    if ($nid !== '') {
        $lines[] = '<strong>' . esc_html__('کد ملی', 'piazhen') . ':</strong> <span dir="ltr">' . esc_html($nid) . '</span>';
    }
    if ($phone !== '') {
        $lines[] = '<strong>' . esc_html__('شماره تماس', 'piazhen') . ':</strong> <span dir="ltr">' . esc_html($phone) . '</span>';
    }
    return $lines;
}

// ============================================================================
// 1. WooCommerce order preview modal (admin order list)
// ============================================================================

/**
 * Append the national ID to the preview modal's billing box for pickup
 * orders. The phone is already rendered there by WooCommerce core
 * (data.data.billing.phone), so only the national ID is added.
 */
add_filter('woocommerce_admin_order_preview_get_order_details', function ($details, $order) {
    if (!pzh_order_is_pickup($order)) {
        return $details;
    }

    $nid = (string) $order->get_meta('billing_national_id');
    if ($nid === '') {
        return $details;
    }

    $extra = '<strong>' . esc_html__('کد ملی', 'piazhen') . ':</strong> <span dir="ltr">' . esc_html($nid) . '</span>';

    $current = isset($details['formatted_billing_address']) ? (string) $details['formatted_billing_address'] : '';
    if ($current === '' || $current === __('N/A', 'woocommerce')) {
        $details['formatted_billing_address'] = $extra;
    } else {
        $details['formatted_billing_address'] = $current . '<br/>' . $extra;
    }

    return $details;
}, 10, 2);

// ============================================================================
// 2. Invoice page of the woocommerce-invoice-pro plugin (?action=wip-print)
// ============================================================================

/**
 * On the WIP invoice page, append the national ID + phone to the formatted
 * billing address for pickup orders. Guarded to the wip-print page so the
 * rest of the site (emails, admin, thank-you) is untouched.
 *
 * Best-effort hook: works as long as the plugin prints the address via
 * WC_Order::get_formatted_billing_address() (the standard way). Note the
 * filter receives the formatted HTML string (the array filter is
 * woocommerce_order_formatted_billing_address).
 */
add_filter('woocommerce_order_get_formatted_billing_address', function ($address, $raw_address, $order) {
    if (!isset($_GET['action']) || 'wip-print' !== $_GET['action']) {
        return $address;
    }
    if (!pzh_order_is_pickup($order)) {
        return $address;
    }

    $lines = pzh_pickup_detail_lines($order);
    if (empty($lines)) {
        return $address;
    }

    $extra = implode('<br/>', $lines);
    return ($address === '' ? '' : $address . '<br/>') . $extra;
}, 20, 3);
