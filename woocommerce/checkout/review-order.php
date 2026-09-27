<?php
/**
 * Checkout Order Review (mockup invoice card: totals + outlined orange CTA)
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="review-order pzh-review-order woocommerce-checkout-review-order-table">

    <!-- Totals -->
    <div class="review-order__totals pzh-invoice-rows">
        <div class="cart-total-row">
            <span><?php _e('قیمت محصولات', 'piazhen'); ?></span>
            <span><?php wc_cart_totals_subtotal_html(); ?></span>
        </div>

        <?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
            <div class="cart-total-row cart-total-row--discount">
                <span><?php _e('تخفیف', 'piazhen'); ?></span>
                <span><?php wc_cart_totals_coupon_html($coupon); ?></span>
            </div>
        <?php endforeach; ?>

        <?php foreach (WC()->cart->get_fees() as $fee): ?>
            <div class="cart-total-row">
                <span><?php echo esc_html($fee->name); ?></span>
                <span><?php wc_cart_totals_fee_html($fee); ?></span>
            </div>
        <?php endforeach; ?>

        <?php if (WC()->cart->needs_shipping()): ?>
            <div class="cart-total-row cart-total-row--shipping">
                <span class="cart-total-row__label">
                    <?php _e('هزینه ارسال', 'piazhen'); ?>
                    <svg viewBox="0 0 40 30" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 8v12H2v-7h8.5L14 8h5"/>
                        <rect x="19" y="6" width="19" height="14" rx="1"/>
                        <circle cx="9" cy="23" r="3"/>
                        <circle cx="30" cy="23" r="3"/>
                    </svg>
                </span>
                <span>
                    <?php
                    $chosen = WC()->session->get('chosen_shipping_methods');
                    $chosen_id = !empty($chosen[0]) ? $chosen[0] : '';
                    // A stale pickup choice in ship mode must not show here
                    if ($chosen_id && !pzh_checkout_is_pickup() && strpos($chosen_id, 'local_pickup') === 0) {
                        $chosen_id = '';
                    }
                    $label = '';
                    $packages = WC()->shipping->get_packages();
                    if (!empty($packages[0]['rates'])) {
                        foreach ($packages[0]['rates'] as $rate) {
                            if (strpos($rate->get_id(), 'local_pickup') === 0) continue;
                            // No choice yet: default to the first ship rate
                            // (same default the WC totals use)
                            if ($chosen_id === '' || $chosen_id === $rate->get_id()) {
                                if (0 < $rate->get_cost()) {
                                    // Full label includes the price; strip the
                                    // PWS image so it stays plain text
                                    $full = preg_replace('/<img[^>]*>/', '', wc_cart_totals_shipping_method_label($rate));
                                    $label = trim(wp_strip_all_tags($full));
                                } else {
                                    $label = __('رایگان', 'piazhen');
                                }
                                break;
                            }
                        }
                    }
                    if ($label === '' && $chosen_id && strpos($chosen_id, 'local_pickup') === 0) {
                        $label = __('رایگان', 'piazhen');
                    }
                    echo $label ? esc_html($label) : '<span class="muted-note">' . esc_html__('انتخاب نشده', 'piazhen') . '</span>';
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="cart-total-row cart-total-row--total">
            <span><?php _e('مجموع سبد خرید:', 'piazhen'); ?></span>
            <span><?php wc_cart_totals_order_total_html(); ?></span>
        </div>
    </div>

    <!-- Place order (outlined orange pill, per the mockup — the mockup has no
         terms checkbox in the invoice card, so terms.php is intentionally not
         rendered here) -->
    <div class="review-order__actions">
        <?php do_action('woocommerce_review_order_before_submit'); ?>
        <?php
        // payment.php normally defines $order_button_text, but this template
        // renders the button itself (the mockup CTA lives in the invoice card)
        $order_button_text = apply_filters('woocommerce_order_button_text', __('ثبت اطلاعات و پرداخت', 'piazhen'));
        echo apply_filters('woocommerce_order_button_html', '<button type="submit" class="pzh-invoice-cta" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>');
        ?>
        <?php do_action('woocommerce_review_order_after_submit'); ?>
    </div>

</div>
