<?php
/**
 * Checkout Billing Fields (custom design: contact step + address step + map)
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

$billing_fields = $checkout->get_checkout_fields('billing');
$contact_keys   = array('billing_email', 'billing_phone');

// Address step order per the design
$address_keys = array(
    'billing_first_name',
    'billing_last_name',
    'billing_country', // hidden (IR)
    'billing_state',
    'billing_city',
    'billing_district',
    'billing_address_1',
    'billing_address_2',
    'billing_postcode',
);
?>

<!-- Step 1: Contact info -->
<div class="checkout-step">
    <div class="checkout-step__head">
        <span class="checkout-step__num">1</span>
        <h4 class="checkout-step__title"><?php _e('اطلاعات تماس', 'piazhen'); ?></h4>
    </div>
    <div class="checkout-step__body">
        <div class="checkout-fields-grid">
            <?php foreach ($contact_keys as $key):
                if (!isset($billing_fields[$key])) continue;
                woocommerce_form_field($key, $billing_fields[$key], $checkout->get_value($key));
            endforeach; ?>
        </div>
    </div>
</div>

<!-- Step 2: Shipping address + map -->
<div class="checkout-step">
    <div class="checkout-step__head">
        <span class="checkout-step__num">2</span>
        <h4 class="checkout-step__title"><?php _e('اطلاعات ارسال', 'piazhen'); ?></h4>
    </div>
    <div class="checkout-step__body">
        <div class="checkout-fields-grid">
            <?php foreach ($address_keys as $key):
                if (!isset($billing_fields[$key])) continue;
                // The PWS plugin injects a bogus default ('0') for district via
                // checkout_get_value — render it empty instead.
                $value = ($key === 'billing_district') ? '' : $checkout->get_value($key);
                woocommerce_form_field($key, $billing_fields[$key], $value);
            endforeach; ?>
        </div>

        <!-- Map: pick the location (Neshan tiles, optional reverse geocoding) -->
        <div class="checkout-map-block">
            <div class="checkout-map-block__head">
                <i class="fa-solid fa-location-dot"></i>
                <span><?php _e('انتخاب موقعیت روی نقشه (اختیاری)', 'piazhen'); ?></span>
            </div>
            <div id="checkout-map" class="checkout-map" data-map="1"></div>
            <div class="checkout-map-block__address" id="checkout-map-address">
                <?php _e('روی نقشه کلیک کنید تا موقعیت دقیق شما ثبت شود.', 'piazhen'); ?>
            </div>
            <input type="hidden" name="billing_latitude" id="billing-latitude" value="">
            <input type="hidden" name="billing_longitude" id="billing-longitude" value="">
        </div>
    </div>
</div>

<?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
