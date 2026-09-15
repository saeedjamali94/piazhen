<?php
/**
 * Checkout Order Review (custom design: items + totals + place order button)
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="review-order pzh-review-order woocommerce-checkout-review-order-table">

    <!-- Items -->
    <div class="review-order__items">
        <?php
        do_action('woocommerce_review_order_before_cart_contents');

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                ?>
                <div class="review-order__item">
                    <div class="review-order__item-image">
                        <?php echo $_product->get_image('pzh_product_thumb'); ?>
                        <span class="review-order__item-qty"><?php echo pzh_fa_num($cart_item['quantity']); ?></span>
                    </div>
                    <div class="review-order__item-name">
                        <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)); ?>
                        <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                    </div>
                    <div class="review-order__item-subtotal">
                        <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                    </div>
                </div>
                <?php
            }
        }

        do_action('woocommerce_review_order_after_cart_contents');
        ?>
    </div>

    <!-- Totals -->
    <div class="review-order__totals">
        <div class="cart-total-row">
            <span><?php _e('مبلغ کل کالاها', 'piazhen'); ?></span>
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
                <span><?php _e('هزینه ارسال', 'piazhen'); ?></span>
                <span>
                    <?php
                    $chosen = WC()->session->get('chosen_shipping_methods');
                    $label  = '';
                    if (!empty($chosen)) {
                        $packages = WC()->shipping->get_packages();
                        foreach ($packages as $i => $package) {
                            foreach ($package['rates'] as $rate) {
                                if ($chosen[$i] === $rate->id) {
                                    $label = wc_cart_totals_shipping_method_label($rate);
                                    break 2;
                                }
                            }
                        }
                    }
                    echo $label ? esc_html($label) : '<span class="muted-note">' . esc_html__('انتخاب نشده', 'piazhen') . '</span>';
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="cart-total-row cart-total-row--total">
            <span><?php _e('مبلغ قابل پرداخت', 'piazhen'); ?></span>
            <span><?php wc_cart_totals_order_total_html(); ?></span>
        </div>
    </div>

    <!-- Place order (in the summary card, per design) -->
    <div class="review-order__actions">
        <?php wc_get_template('checkout/terms.php'); ?>
        <?php do_action('woocommerce_review_order_before_submit'); ?>
        <?php echo apply_filters('woocommerce_order_button_html', '<button type="submit" class="mainBtn mainBtn--yellow w-100 place-order-btn" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); ?>
        <?php do_action('woocommerce_review_order_after_submit'); ?>
    </div>

</div>
