<?php
/**
 * Checkout Billing Fields (custom design: contact step + address step)
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

$billing_fields = $checkout->get_checkout_fields('billing');
$contact_keys   = array('billing_email', 'billing_phone');
?>

<!-- Step 1: Contact info -->
<div class="checkout-step">
    <div class="checkout-step__head">
        <span class="checkout-step__num">1</span>
        <h4 class="checkout-step__title"><?php _e('اطلاعات تماس', 'piazhen'); ?></h4>
    </div>
    <div class="checkout-step__body">
        <div class="checkout-fields-grid">
            <?php foreach ($billing_fields as $key => $field):
                if (!in_array($key, $contact_keys, true)) continue;
                woocommerce_form_field($key, $field, $checkout->get_value($key));
            endforeach; ?>
        </div>
    </div>
</div>

<!-- Step 2: Shipping address -->
<div class="checkout-step">
    <div class="checkout-step__head">
        <span class="checkout-step__num">2</span>
        <h4 class="checkout-step__title"><?php _e('اطلاعات ارسال', 'piazhen'); ?></h4>
    </div>
    <div class="checkout-step__body">
        <div class="checkout-fields-grid">
            <?php foreach ($billing_fields as $key => $field):
                if (in_array($key, $contact_keys, true)) continue;
                woocommerce_form_field($key, $field, $checkout->get_value($key));
            endforeach; ?>
        </div>
    </div>
</div>

<?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
