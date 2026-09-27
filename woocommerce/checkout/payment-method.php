<?php
/**
 * Output a single payment method (theme override)
 *
 * The mockup has no payment step and the CTA text is fixed
 * ("ثبت اطلاعات و پرداخت"), so the data-order_button_text attribute is
 * dropped — otherwise checkout.js overwrites the CTA label with the
 * gateway's own text on page load. The section is hidden via CSS anyway.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr($gateway->id); ?>">
    <input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> />

    <label for="payment_method_<?php echo esc_attr($gateway->id); ?>">
        <?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?> <?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
    </label>
    <?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
        <div class="payment_box payment_method_<?php echo esc_attr($gateway->id); ?>" <?php if (!$gateway->chosen) : /* phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace */ ?>style="display:none;"<?php endif; /* phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace */ ?>>
            <?php $gateway->payment_fields(); ?>
        </div>
    <?php endif; ?>
</li>
