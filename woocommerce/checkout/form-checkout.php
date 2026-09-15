<?php
/**
 * Checkout Form (custom design)
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

<!-- Breadcrumb + Title -->
<nav class="cart-breadcrumb py-3">
    <div class="breadcrumb-trail">
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('خانه', 'piazhen'); ?></a>
        <span class="breadcrumb-delimiter">/</span>
        <span><?php _e('تسویه حساب', 'piazhen'); ?></span>
    </div>
</nav>
<h1 class="cart-title"><?php _e('تسویه حساب', 'piazhen'); ?></h1>

<form name="checkout" method="post" class="checkout woocommerce-checkout"
      action="<?php echo esc_url(wc_get_checkout_url()); ?>"
      enctype="multipart/form-data"
      aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">

    <div class="pzh-checkout-layout">

        <!-- ============ Form column (steps) ============ -->
        <div class="pzh-checkout-form">

            <?php if ($checkout->get_checkout_fields()): ?>

                <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                <div id="customer_details">
                    <?php do_action('woocommerce_checkout_billing'); ?>
                </div>

                <?php do_action('woocommerce_checkout_after_customer_details'); ?>

            <?php endif; ?>

            <?php $has_shipping = WC()->cart->needs_shipping(); ?>
            <?php if ($has_shipping): ?>
                <!-- Step 3: Shipping method (updates via checkout AJAX fragment) -->
                <div class="checkout-step">
                    <div class="checkout-step__head">
                        <span class="checkout-step__num">3</span>
                        <h4 class="checkout-step__title"><?php _e('روش ارسال', 'piazhen'); ?></h4>
                    </div>
                    <div class="checkout-step__body">
                        <div id="pzh-shipping-methods">
                            <?php
                            ob_start();
                            wc_cart_totals_shipping_html();
                            $shipping_html = ob_get_clean();
                            if (trim($shipping_html)) {
                                echo $shipping_html;
                            } else {
                                echo '<p class="shipping-methods-note">' . esc_html__('برای مشاهده روش‌های ارسال، آدرس خود را تکمیل کنید.', 'piazhen') . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Step 4: Payment method -->
            <div class="checkout-step">
                <div class="checkout-step__head">
                    <span class="checkout-step__num"><?php echo $has_shipping ? 4 : 3; ?></span>
                    <h4 class="checkout-step__title"><?php _e('روش پرداخت', 'piazhen'); ?></h4>
                </div>
                <div class="checkout-step__body">
                    <?php do_action('woocommerce_review_order_before_payment'); ?>
                    <?php woocommerce_checkout_payment(); ?>
                    <?php do_action('woocommerce_review_order_after_payment'); ?>
                </div>
            </div>

        </div>

        <!-- ============ Order summary column ============ -->
        <div class="pzh-checkout-summary">
            <div class="checkout-summary-card">
                <h4 class="checkout-summary-card__title"><?php _e('خلاصه سفارش', 'piazhen'); ?></h4>

                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action('woocommerce_checkout_order_review'); ?>
                </div>
            </div>
        </div>

    </div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
