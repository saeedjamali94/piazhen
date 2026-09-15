<?php
/**
 * Checkout Payment (custom design: gateway radios; place-order button lives
 * in the summary card via review-order.php)
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="payment" class="woocommerce-checkout-payment">
    <?php if (WC()->cart && WC()->cart->needs_payment()): ?>
        <ul class="wc_payment_methods payment_methods methods">
            <?php
            if (!empty($available_gateways)) {
                foreach ($available_gateways as $gateway) {
                    wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
                }
            } else {
                echo '<li class="payment-no-methods">';
                wc_print_notice(
                    apply_filters('woocommerce_no_available_payment_methods_message',
                        WC()->customer->get_billing_country()
                            ? __('در حال حاضر روش پرداختی در دسترس نیست. لطفاً با پشتیبانی تماس بگیرید.', 'piazhen')
                            : __('لطفاً اطلاعات خود را تکمیل کنید تا روش‌های پرداخت نمایش داده شوند.', 'piazhen')
                    ),
                    'notice'
                );
                echo '</li>';
            }
            ?>
        </ul>
    <?php endif; ?>

    <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
</div>
