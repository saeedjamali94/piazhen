<?php
/**
 * Product box shortcode + admin picker.
 *
 * Shortcode: [pzh_product id="123"] — renders a styled product box inside
 * any content (product description, blog posts, pages): image, title,
 * price (original crossed out + discounted when on sale) and a link button.
 *
 * Admin: a «درج محصول» button is added above the classic editor on the
 * product edit page AND on blog posts. Clicking it opens a search modal
 * (WooCommerce's own product search endpoint); picking a product inserts the
 * shortcode at the cursor.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// Shortcode
// ============================================================================

function pzh_product_box_shortcode($atts) {
    $atts = shortcode_atts(array('id' => 0), $atts, 'pzh_product');

    $product = wc_get_product(intval($atts['id']));
    if (!$product || 'publish' !== $product->get_status()) {
        return '';
    }

    $url   = get_permalink($product->get_id());
    $title = $product->get_name();

    // Price: original crossed out + discounted when on sale
    $price_html = '';
    if ($product->is_on_sale()) {
        $regular = wc_get_price_to_display($product, array('price' => $product->get_regular_price()));
        $sale    = wc_get_price_to_display($product);
        if ($regular > $sale) {
            $price_html = '<del aria-hidden="true">' . wc_price($regular) . '</del>'
                . '<ins>' . wc_price($sale) . '</ins>';
        }
    }
    if ($price_html === '') {
        $price_html = wc_price(wc_get_price_to_display($product));
    }

    ob_start();
    ?>
    <div class="pzh-product-box">
        <a class="pzh-product-box__image" href="<?php echo esc_url($url); ?>" aria-hidden="true" tabindex="-1">
            <?php echo $product->get_image('pzh_product_thumb'); ?>
        </a>
        <div class="pzh-product-box__info">
            <h4 class="pzh-product-box__title">
                <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
            </h4>
            <div class="pzh-product-box__price"><?php echo $price_html; ?></div>
            <a class="pzh-product-box__btn" href="<?php echo esc_url($url); ?>"><?php esc_html_e('مشاهده محصول', 'piazhen'); ?></a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pzh_product', 'pzh_product_box_shortcode');

// ============================================================================
// Admin: «درج محصول» button + product search modal (product & post editors)
// ============================================================================

/**
 * Print the button + hidden Thickbox modal above the classic editor.
 * A static guard prints it once per request — the product screen renders
 * more than one editor, but the button belongs to the main description
 * editor only (duplicate modal IDs would confuse Thickbox).
 */
function pzh_product_box_media_button() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !in_array($screen->post_type, array('product', 'post'), true)) {
        return;
    }

    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <a href="#TB_inline?&width=460&height=320&inlineId=pzh-product-picker" class="button thickbox pzh-insert-product-btn">
        <span class="dashicons dashicons-cart" style="vertical-align: text-bottom;"></span>
        <?php esc_html_e('درج محصول', 'piazhen'); ?>
    </a>
    <div id="pzh-product-picker" style="display:none;">
        <div class="pzh-product-picker">
            <input type="text" id="pzh-product-picker-search" placeholder="<?php esc_attr_e('نام محصول را جستجو کنید…', 'piazhen'); ?>" autocomplete="off">
            <div class="pzh-product-picker__results" id="pzh-product-picker-results"></div>
        </div>
    </div>
    <style>
        .pzh-product-picker { padding: 14px; font-family: inherit; }
        .pzh-product-picker input { width: 100%; padding: 8px 10px; font-size: 14px; }
        .pzh-product-picker__results { max-height: 200px; overflow-y: auto; margin-top: 10px; }
        .pzh-product-picker__item { padding: 8px 10px; cursor: pointer; border-radius: 6px; font-size: 13px; }
        .pzh-product-picker__item:hover { background: #f0f0f1; }
        .pzh-product-picker__empty { color: #999; font-size: 13px; margin: 10px 0 0; }
    </style>
    <script>
    jQuery(function ($) {
        var $input   = $('#pzh-product-picker-search');
        var $results = $('#pzh-product-picker-results');
        var timer    = null;

        $input.on('input', function () {
            clearTimeout(timer);
            var term = $input.val().trim();
            if (term.length < 2) { $results.html(''); return; }
            timer = setTimeout(function () {
                $results.html('<p class="pzh-product-picker__empty">…</p>');
                $.get(pzhProductPicker.ajaxUrl, {
                    action:   'woocommerce_json_search_products',
                    security: pzhProductPicker.nonce,
                    term:     term
                }, function (resp) {
                    var html = '';
                    $.each(resp || [], function (id, label) {
                        html += '<div class="pzh-product-picker__item" data-id="' + id + '">' + label + '</div>';
                    });
                    $results.html(html || '<p class="pzh-product-picker__empty">' + pzhProductPicker.noResults + '</p>');
                });
            }, 300);
        });

        $(document).on('click', '.pzh-product-picker__item', function () {
            var code = '[pzh_product id="' + $(this).data('id') + '"]';
            if (window.tinyMCE && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
                tinyMCE.activeEditor.insertContent(code);
            } else {
                var ta = document.getElementById('content');
                if (ta) {
                    var start = ta.selectionStart, end = ta.selectionEnd;
                    ta.value = ta.value.substring(0, start) + code + ta.value.substring(end);
                    ta.focus();
                    ta.selectionStart = ta.selectionEnd = start + code.length;
                }
            }
            if (window.tb_remove) tb_remove();
        });
    });
    </script>
    <?php
}
add_action('media_buttons', 'pzh_product_box_media_button');

/**
 * Localize the picker data on the product / post edit screens.
 */
function pzh_product_box_admin_scripts() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !in_array($screen->post_type, array('product', 'post'), true)) {
        return;
    }
    add_thickbox();
    wp_localize_script('jquery', 'pzhProductPicker', array(
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('search-products'),
        'noResults' => __('محصولی یافت نشد', 'piazhen'),
    ));
}
add_action('admin_enqueue_scripts', 'pzh_product_box_admin_scripts');
