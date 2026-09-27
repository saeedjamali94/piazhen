<?php
/**
 * Checkout Form (mockup: Shipping information1/2/3.png)
 *
 * Right column: shipping choice cards + contact + shipping method + address.
 * Left column: invoice card (totals + CTA). The Neshan map lives in a modal
 * opened from the "افزودن آدرس" link.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('برای تسویه حساب باید وارد حساب کاربری شوید.', 'piazhen')));
    return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout"
      action="<?php echo esc_url(wc_get_checkout_url()); ?>"
      enctype="multipart/form-data"
      aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">

    <div class="pzh-checkout-layout">

        <!-- ============ Form column ============ -->
        <div class="pzh-checkout-form">

            <?php if ($checkout->get_checkout_fields()): ?>

                <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                <div id="customer_details">
                    <?php do_action('woocommerce_checkout_billing'); ?>
                </div>

                <?php do_action('woocommerce_checkout_after_customer_details'); ?>

            <?php endif; ?>

            <?php
            // Only one gateway (زرین‌پال) is configured and the mockup has no
            // payment step — keep the radios + nonce in the DOM, hidden.
            ?>
            <?php do_action('woocommerce_review_order_before_payment'); ?>
            <div class="pzh-payment-hidden">
                <?php woocommerce_checkout_payment(); ?>
            </div>
            <?php do_action('woocommerce_review_order_after_payment'); ?>

        </div>

        <!-- ============ Invoice column ============ -->
        <div class="pzh-checkout-summary">
            <div class="pzh-invoice-head">
                <span class="pzh-invoice-head__title"><?php _e('صورت حساب', 'piazhen'); ?></span>
                <span class="pzh-invoice-head__count"><?php echo pzh_fa_num(WC()->cart->get_cart_contents_count()) . ' ' . __('محصول', 'piazhen'); ?></span>
            </div>
            <div class="checkout-summary-card">
                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action('woocommerce_checkout_order_review'); ?>
                </div>
            </div>
        </div>

    </div>
</form>

<!-- ============ Neshan map modal (افزودن آدرس) ============ -->
<div class="pzh-map-modal-overlay" id="checkout-map-modal" hidden>
    <div class="pzh-map-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('افزودن آدرس', 'piazhen'); ?>">

        <div class="pzh-map-modal__header">
            <div class="pzh-map-modal__title">
                <svg viewBox="0 0 34 34" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <circle cx="17" cy="17" r="14"/>
                    <path d="M17 9.5v15M9.5 17h15"/>
                </svg>
                <span><?php _e('افزودن آدرس', 'piazhen'); ?></span>
            </div>
            <button type="button" class="pzh-map-modal__close" id="checkout-map-modal-close" aria-label="<?php esc_attr_e('بستن', 'piazhen'); ?>">
                <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 4l24 24M28 4L4 28"/>
                </svg>
            </button>
        </div>

        <div class="pzh-map-modal__body">
            <div class="pzh-map-search">
                <svg class="pzh-map-search__icon" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <circle cx="14" cy="14" r="9"/>
                    <path d="M21 21l7 7"/>
                </svg>
                <input type="text" id="checkout-map-search" placeholder="<?php esc_attr_e('جست‌وجو آدرس', 'piazhen'); ?>" autocomplete="off" />
                <div class="pzh-map-search__results" id="checkout-map-search-results"></div>
            </div>

            <div id="checkout-map-modal-map" class="checkout-map pzh-map-modal__map"></div>

            <div class="pzh-map-modal__footer">
                <button type="button" class="pzh-map-modal__cta" id="checkout-map-modal-apply"><?php _e('ثبت آدرس و ادامه', 'piazhen'); ?></button>
            </div>
        </div>

    </div>
</div>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
