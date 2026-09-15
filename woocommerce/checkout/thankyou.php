<?php
/**
 * Order Received / Thank You page (custom design)
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="woocommerce-order pzh-thankyou">

    <?php if ($order):

        do_action('woocommerce_before_thankyou', $order->get_id());

        $status    = $order->get_status();
        $is_paid   = $order->is_paid();
        $is_failed = in_array($status, array('failed', 'cancelled'), true);
        ?>

        <!-- Status Header -->
        <div class="thankyou-header <?php echo $is_failed ? 'thankyou-header--failed' : ''; ?>">
            <div class="thankyou-header__icon">
                <?php if ($is_failed): ?>
                    <i class="fa-solid fa-circle-xmark"></i>
                <?php elseif ($is_paid): ?>
                    <i class="fa-solid fa-circle-check"></i>
                <?php else: ?>
                    <i class="fa-regular fa-clock"></i>
                <?php endif; ?>
            </div>
            <h2 class="thankyou-header__title">
                <?php
                if ($is_failed) {
                    _e('سفارش شما ناموفق بود', 'piazhen');
                } elseif ($is_paid) {
                    _e('سفارش شما با موفقیت ثبت شد', 'piazhen');
                } else {
                    _e('سفارش شما ثبت شد و در انتظار پرداخت است', 'piazhen');
                }
                ?>
            </h2>
            <p class="thankyou-header__text">
                <?php if ($is_failed): ?>
                    <?php _e('پرداخت این سفارش انجام نشد. می‌توانید دوباره تلاش کنید.', 'piazhen'); ?>
                <?php elseif ($is_paid): ?>
                    <?php _e('از خرید شما متشکریم؛ سفارش شما در حال آماده‌سازی است.', 'piazhen'); ?>
                <?php else: ?>
                    <?php _e('برای تکمیل سفارش، پرداخت را انجام دهید.', 'piazhen'); ?>
                <?php endif; ?>
            </p>

            <div class="thankyou-header__meta">
                <span class="thankyou-header__meta-item">
                    <?php _e('شماره سفارش:', 'piazhen'); ?>
                    <strong><?php echo pzh_fa_num($order->get_order_number()); ?></strong>
                </span>
                <span class="thankyou-header__meta-item">
                    <?php _e('تاریخ ثبت:', 'piazhen'); ?>
                    <strong><?php echo get_the_date('Y/m/d', $order->get_id()); ?></strong>
                </span>
                <span class="thankyou-header__meta-item">
                    <?php _e('وضعیت:', 'piazhen'); ?>
                    <strong><?php echo esc_html(wc_get_order_status_name($status)); ?></strong>
                </span>
            </div>
        </div>

        <!-- Order Details -->
        <div class="thankyou-details">
            <div class="thankyou-details__col">
                <h4 class="thankyou-details__title"><?php _e('جزئیات سفارش', 'piazhen'); ?></h4>
                <div class="thankyou-items">
                    <?php foreach ($order->get_items() as $item_id => $item):
                        $product = $item->get_product();
                        if (!$product) continue;
                        ?>
                        <div class="thankyou-item">
                            <div class="thankyou-item__image">
                                <?php echo $product->get_image('pzh_product_thumb'); ?>
                                <span class="thankyou-item__qty"><?php echo pzh_fa_num($item->get_quantity()); ?></span>
                            </div>
                            <div class="thankyou-item__name"><?php echo esc_html($item->get_name()); ?></div>
                            <div class="thankyou-item__total"><?php echo $order->get_formatted_line_subtotal($item); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="thankyou-details__col">
                <h4 class="thankyou-details__title"><?php _e('مبالغ', 'piazhen'); ?></h4>
                <div class="thankyou-totals">
                    <div class="cart-total-row">
                        <span><?php _e('جمع کالاها', 'piazhen'); ?></span>
                        <span><?php echo $order->get_subtotal_to_display(); ?></span>
                    </div>
                    <?php if ($order->get_total_discount() > 0): ?>
                        <div class="cart-total-row cart-total-row--discount">
                            <span><?php _e('تخفیف', 'piazhen'); ?></span>
                            <span>-<?php echo wp_kses_post(wc_price($order->get_total_discount())); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order->get_shipping_total() > 0): ?>
                        <div class="cart-total-row">
                            <span><?php _e('هزینه ارسال', 'piazhen'); ?></span>
                            <span><?php echo wp_kses_post(wc_price($order->get_shipping_total())); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="cart-total-row cart-total-row--total">
                        <span><?php _e('مبلغ پرداخت شده', 'piazhen'); ?></span>
                        <span><?php echo $order->get_formatted_order_total(); ?></span>
                    </div>
                </div>

                <?php if (!$is_paid && !$is_failed): ?>
                    <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="mainBtn mainBtn--yellow w-100 mt-3">
                        <?php _e('پرداخت سفارش', 'piazhen'); ?>
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="thankyou-actions">
            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="mainBtn small"><?php _e('پیگیری سفارش', 'piazhen'); ?></a>
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="mainBtn mainBtn--yellow small"><?php _e('بازگشت به فروشگاه', 'piazhen'); ?></a>
        </div>

        <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
        <?php do_action('woocommerce_thankyou', $order->get_id()); ?>

    <?php else: ?>

        <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters('woocommerce_thankyou_order_received_text', esc_html__('از خرید شما متشکریم.', 'piazhen'), null); ?></p>

    <?php endif; ?>

</div>
