<?php
/**
 * Cart Page (custom design, custom AJAX actions — no plugins)
 *
 * @package Piazhen
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');
?>

<!-- Breadcrumb + Title -->
<nav class="cart-breadcrumb py-3">
    <div class="breadcrumb-trail">
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('خانه', 'piazhen'); ?></a>
        <span class="breadcrumb-delimiter">/</span>
        <span><?php _e('سبد خرید', 'piazhen'); ?></span>
    </div>
</nav>
<h1 class="cart-title"><?php _e('سبد خرید', 'piazhen'); ?></h1>

<?php if (!WC()->cart->is_empty()): ?>

    <div class="pzh-cart-page" data-cart-page="1">

        <!-- Items -->
        <div class="pzh-cart-items">
            <div class="cart-items-head">
                <span></span>
                <span><?php _e('کالا', 'piazhen'); ?></span>
                <span><?php _e('قیمت', 'piazhen'); ?></span>
                <span><?php _e('تعداد', 'piazhen'); ?></span>
                <span><?php _e('جمع', 'piazhen'); ?></span>
                <span></span>
            </div>
            <div class="pzh-cart-items__rows">
                <?php echo pzh_cart_items_html(); ?>
            </div>
        </div>

        <!-- Summary -->
        <div class="pzh-cart-totals">
            <?php echo pzh_cart_totals_html(); ?>
        </div>

    </div>

<?php else: ?>

    <?php echo pzh_cart_empty_html(); ?>

<?php endif; ?>

<?php do_action('woocommerce_after_cart'); ?>
