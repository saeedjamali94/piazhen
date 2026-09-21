<?php
/**
 * WooCommerce Single Product Page
 * All actions (favorite, compare, add to cart, variations) are custom AJAX — no plugins.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()): the_post();
    global $product;
    if (!$product) $product = wc_get_product(get_the_ID());

    $product_id      = $product->get_id();
    $gallery_ids     = $product->get_gallery_image_ids();
    $all_images      = array_values(array_filter(array_merge(
        array(get_post_thumbnail_id($product_id)),
        $gallery_ids
    )));
    $brand_name      = pzh_get_product_brand($product_id);
    $specs           = pzh_get_product_specs($product_id);

    // Some products carry a specs <table> inside the short description.
    // Extract its rows so they render as the mockup's spec panels (instead of
    // the raw HTML table), leaving any surrounding text as the subtitle.
    // Two table variants exist in the data: label-first rows (default) and
    // value-first rows (dir="ltr" tables) — both are normalized to label/value.
    $excerpt_html  = $product->get_short_description();
    $excerpt_text  = '';
    $excerpt_specs = array();
    if ($excerpt_html) {
        if (stripos($excerpt_html, '<table') !== false) {
            $excerpt_text = trim(strip_tags(preg_replace('#<table[^>]*>.*?</table>#is', ' ', $excerpt_html)));
            if (preg_match('#<table[^>]*>(.*?)</table>#is', $excerpt_html, $table_match)) {
                $is_ltr      = (bool) preg_match('#dir\s*=\s*["\']\s*ltr#i', $table_match[0]);
                $rows_source = $table_match[1];
                if (preg_match('#<tbody[^>]*>(.*?)</tbody>#is', $table_match[1], $tbody_match)) {
                    $rows_source = $tbody_match[1]; // skip <thead> header rows
                }
                preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $rows_source, $row_matches);
                foreach ($row_matches[1] as $row_html) {
                    preg_match_all('#<t[dh][^>]*>(.*?)</t[dh]>#is', $row_html, $cell_matches);
                    $cells = array();
                    foreach ($cell_matches[1] as $cell_html) {
                        $cell = trim(strip_tags($cell_html));
                        if ($cell !== '') {
                            $cells[] = $cell;
                        }
                    }
                    if (count($cells) >= 2) {
                        $label = $is_ltr ? $cells[1] : $cells[0];
                        $value = $is_ltr ? $cells[0] : $cells[1];
                        $excerpt_specs[$label] = $value;
                    }
                }
            }
        } else {
            $excerpt_text = trim(strip_tags($excerpt_html));
        }
    }
    $faqs            = pzh_get_product_faqs($product_id);
    $related_ids     = pzh_get_related_products($product_id, 10);
    $whatsapp_number = pzh_whatsapp_number();
    $whatsapp_url    = ($whatsapp_number ? 'https://wa.me/' . $whatsapp_number : 'https://wa.me/') . '?text=' . urlencode($product->get_name() . ' - ' . get_permalink($product_id));
    $is_variable     = $product->is_type('variable');
    $variation_picker = $is_variable ? pzh_get_variation_picker_data($product) : array();
    $can_purchase    = pzh_variable_has_stock($product);

    // Is the product (or its default variation) already in the cart?
    // If so, show the quantity selector instead of the add-to-cart button.
    $default_variation_id = 0;
    if ($is_variable) {
        $default_attrs = $product->get_default_attributes();
        if (!empty($default_attrs)) {
            $data_store = WC_Data_Store::load('product');
            $default_variation_id = intval($data_store->find_matching_product_variation($product, $default_attrs));
        }
    }
    $initial_cart_item = pzh_find_cart_item($product_id, $is_variable ? $default_variation_id : 0);
    $initial_cart_qty  = $initial_cart_item ? $initial_cart_item['quantity'] : 0;
    $initial_cart_key  = $initial_cart_item ? $initial_cart_item['key'] : '';

    // Variable products show a single entry price (as in the mockup); the
    // exact variation price replaces it via AJAX once options are selected.
    $min_variation_price = $is_variable ? $product->get_variation_price('min') : '';
    $display_price_html  = ($is_variable && $min_variation_price !== '')
        ? wc_price($min_variation_price)
        : $product->get_price_html();
?>
<main class="woo-single-product">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="single-breadcrumb py-3">
            <?php woocommerce_breadcrumb(array(
                'delimiter'   => ' <span class="breadcrumb-delimiter">/</span> ',
                'wrap_before' => '<div class="breadcrumb-trail">',
                'wrap_after'  => '</div>',
                'home'        => __('خانه', 'piazhen'),
            )); ?>
        </nav>

        <!-- Product Top: Gallery + Info (mockup proportions: 5/12 gallery, 7/12 info) -->
        <div class="single-product-top row g-4">
            <!-- Product Gallery (per single-product-right.png) -->
            <div class="col-lg-4">
                <div class="product-gallery">

                    <!-- Main Image Card -->
                    <div class="product-gallery__main">
                        <?php if (!empty($all_images)): ?>
                            <img id="main-product-image"
                                 src="<?php echo wp_get_attachment_image_url($all_images[0], 'woocommerce_single'); ?>"
                                 alt="<?php echo esc_attr($product->get_name()); ?>"
                                 class="product-gallery__main-img"
                                 data-zoom="<?php echo wp_get_attachment_image_url($all_images[0], 'full'); ?>">
                        <?php else: ?>
                            <div class="product-gallery__placeholder">
                                <?php echo wc_placeholder_img('woocommerce_single'); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons (favorite / compare / whatsapp) -->
                        <div class="product-gallery__actions">
                            <button class="gallery-action-btn favorite-btn product-card__favorite position-relative <?php echo pzh_is_favorited($product_id) ? 'active' : ''; ?>"
                                    data-product-id="<?php echo $product_id; ?>"
                                    title="<?php _e('افزودن به علاقه‌مندی‌ها', 'piazhen'); ?>"
                                    style="top: 0;right: 0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                            <button class="gallery-action-btn compare-btn"
                                    data-product-id="<?php echo $product_id; ?>"
                                    title="<?php _e('مقایسه', 'piazhen'); ?>">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2.5" y="2.5" width="6" height="6" rx="1"/>
                                    <path d="M15.5 2.5l-6.5 6H14l1.5-6z"/>
                                    <circle cx="5.5" cy="13.5" r="3"/>
                                    <line x1="11.5" y1="12.5" x2="17.5" y2="12.5"/>
                                    <line x1="11.5" y1="14.5" x2="17.5" y2="14.5"/>
                                    <line x1="11.5" y1="16.5" x2="17.5" y2="16.5"/>
                                </svg>
                            </button>
                            <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener"
                               class="gallery-action-btn whatsapp-btn"
                               title="<?php _e('اشتراک‌گذاری در واتساپ', 'piazhen'); ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" fill-rule="evenodd">
                                    <path d="M12.05 2C6.99 2 2.89 6.1 2.89 11.16c0 1.62.42 3.19 1.22 4.58L3 22l6.43-1.69a9.12 9.12 0 0 0 4.62 1.18h.01c5.05 0 9.16-4.1 9.16-9.16 0-2.44-.95-4.74-2.68-6.47A9.09 9.09 0 0 0 12.05 2zm5.42 13.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.26-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.44-.53.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51-.17-.01-.37-.01-.57-.01-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.06 2.87 1.21 3.07.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.62.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Horizontal Thumbnail Strip (active thumb is darkened, no ring) -->
                    <?php if (count($all_images) > 1): ?>
                    <div class="product-gallery__thumbs" id="product-thumbs">
                        <?php foreach ($all_images as $idx => $img_id):
                            $full_url = wp_get_attachment_image_url($img_id, 'woocommerce_single');
                            $zoom_url = wp_get_attachment_image_url($img_id, 'full');
                        ?>
                            <div class="product-gallery__thumb <?php echo $idx === 0 ? 'active' : ''; ?>"
                                 role="button" tabindex="0"
                                 data-full="<?php echo esc_url($full_url); ?>"
                                 data-zoom="<?php echo esc_url($zoom_url); ?>"
                                 data-index="<?php echo $idx; ?>">
                                <?php echo wp_get_attachment_image($img_id, 'pzh_product_thumb', false, array('draggable' => 'false')); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-8">
                <div class="product-info">

                    <!-- Title -->
                    <h1 class="product-info__title"><?php the_title(); ?></h1>

                    <!-- Subtitle / Short Description (table part, if any, becomes the spec panels below) -->
                    <?php if ($excerpt_text !== ''): ?>
                        <p class="product-info__subtitle"><?php echo esc_html($excerpt_text); ?></p>
                    <?php endif; ?>

                    <div class="product-info__row">
                        <!-- Specs Column (renders right in RTL) -->
                        <div class="product-info__specs-col">
                            <?php
                            // Quick info panels: brand, product type (category), then specs — up to 4
                            $panel_pool = array();
                            if ($brand_name) {
                                $panel_pool[__('برند', 'piazhen')] = $brand_name;
                            }
                            $product_cats = get_the_terms($product_id, 'product_cat');
                            if ($product_cats && !is_wp_error($product_cats)) {
                                $panel_pool[__('نوع محصول', 'piazhen')] = $product_cats[0]->name;
                            }
                            if (!empty($excerpt_specs)) {
                                // The excerpt's specs table is the panel list itself (all rows)
                                foreach ($excerpt_specs as $excerpt_label => $excerpt_value) {
                                    $panel_pool[$excerpt_label] = $excerpt_value;
                                }
                                $info_panels    = $panel_pool;
                                $has_more_specs = false;
                            } else {
                                foreach ($specs as $spec_label => $spec_value) {
                                    if (!isset($panel_pool[$spec_label])) {
                                        $panel_pool[$spec_label] = $spec_value;
                                    }
                                }
                                $info_panels    = array_slice($panel_pool, 0, 4, true);
                                $has_more_specs = count($panel_pool) > 4;
                            }
                            ?>
                            <?php if (!empty($info_panels)): ?>
                                <div class="product-info__panels">
                                    <?php foreach ($info_panels as $panel_label => $panel_value): ?>
                                        <div class="info-panel">
                                            <span class="info-panel__label"><?php echo esc_html(pzh_fa_num($panel_label)); ?></span>
                                            <span class="info-panel__value"><?php echo esc_html(pzh_fa_num($panel_value)); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($has_more_specs): ?>
                                    <a href="#section-specs" class="product-info__more"><?php _e('بیشتر', 'piazhen'); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Price (bottom-aligned with the add-to-cart button) -->
                            <div class="product-info__price" id="product-price"
                                 data-base-price="<?php echo esc_attr($display_price_html); ?>">
                                <?php echo $display_price_html; ?>
                            </div>
                        </div>

                        <!-- Controls Column (renders left in RTL) -->
                        <div class="product-info__controls">
                            <!-- Variations (custom AJAX picker — no plugin) -->
                            <?php if ($is_variable && !empty($variation_picker)): ?>
                                <div class="pzh-variation-picker" data-product-id="<?php echo $product_id; ?>">
                                    <?php foreach ($variation_picker as $attribute): ?>
                                        <div class="variation-group variation-group--<?php echo esc_attr($attribute['type']); ?>">
                                            <?php if ($attribute['type'] === 'swatch'): ?>
                                                <!-- Color panel -->
                                                <div class="variation-panel">
                                                    <div class="variation-panel__header">
                                                        <span class="variation-group__label"><?php echo esc_html($attribute['label']); ?></span>
                                                        <span class="variation-group__selected"></span>
                                                    </div>
                                                    <div class="variation-group__options variation-group__options--swatch">
                                                        <?php foreach ($attribute['items'] as $item): ?>
                                                            <button type="button"
                                                                    class="swatch-option <?php echo $item['color'] ? '' : 'swatch-option--no-color'; ?>"
                                                                    data-attr="<?php echo esc_attr($attribute['attr_key']); ?>"
                                                                    data-value="<?php echo esc_attr($item['slug']); ?>"
                                                                    title="<?php echo esc_attr($item['label']); ?>"
                                                                    <?php echo $item['color'] ? 'style="background-color:' . esc_attr($item['color']) . '"' : ''; ?>
                                                                    aria-label="<?php echo esc_attr($item['label']); ?>"></button>
                                                        <?php endforeach; ?>
                                                        <?php if (count($attribute['items']) > 4): ?>
                                                            <span class="swatch-more-dots" aria-hidden="true"></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php elseif ($attribute['type'] === 'radio'): ?>
                                                <!-- Radio group (e.g. warranty) -->
                                                <span class="variation-group__label">
                                                    <?php echo esc_html($attribute['label']); ?>:
                                                    <span class="variation-group__selected"></span>
                                                </span>
                                                <div class="variation-group__options variation-group__options--radio">
                                                    <?php foreach ($attribute['items'] as $item): ?>
                                                        <button type="button"
                                                                class="variation-radio"
                                                                data-attr="<?php echo esc_attr($attribute['attr_key']); ?>"
                                                                data-value="<?php echo esc_attr($item['slug']); ?>"
                                                                title="<?php echo esc_attr($item['label']); ?>"
                                                                aria-label="<?php echo esc_attr($item['label']); ?>">
                                                            <span class="variation-radio__dot"></span>
                                                            <span class="variation-radio__text"><?php echo esc_html(pzh_fa_num($item['label'])); ?></span>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <!-- Chip group -->
                                                <span class="variation-group__label">
                                                    <?php echo esc_html($attribute['label']); ?>:
                                                    <span class="variation-group__selected"></span>
                                                </span>
                                                <div class="variation-group__options variation-group__options--chip">
                                                    <?php foreach ($attribute['items'] as $item): ?>
                                                        <button type="button"
                                                                class="variation-chip"
                                                                data-attr="<?php echo esc_attr($attribute['attr_key']); ?>"
                                                                data-value="<?php echo esc_attr($item['slug']); ?>">
                                                            <?php echo esc_html(pzh_fa_num($item['label'])); ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="variation-picker__status"></div>
                                    <input type="hidden" id="selected-variation-id" value="">
                                </div>
                            <?php endif; ?>

                            <!-- Add to Cart (bottom-aligned with the price) -->
                            <button class="add-to-cart-single <?php echo $can_purchase ? '' : 'out-of-stock'; ?>"
                                    data-product-id="<?php echo $product_id; ?>"
                                    <?php echo $is_variable ? 'data-variable="1"' : ''; ?>
                                    <?php echo $can_purchase ? '' : 'disabled'; ?>
                                    <?php echo ($can_purchase && $initial_cart_qty > 0) ? 'style="display:none;"' : ''; ?>>
                                <?php echo $can_purchase ? __('افزودن به سبد خرید', 'piazhen') : __('ناموجود', 'piazhen'); ?>
                            </button>

                            <!-- Quantity selector — replaces the button while the product
                                 (or the selected variation) is in the cart -->
                            <div class="single-qty-box"
                                 data-product-id="<?php echo $product_id; ?>"
                                 data-variation-id="<?php echo $default_variation_id; ?>"
                                 data-cart-key="<?php echo esc_attr($initial_cart_key); ?>"
                                 <?php echo ($can_purchase && $initial_cart_qty > 0) ? '' : 'style="display:none;"'; ?>>
                                <button type="button" class="single-qty-box__btn single-qty-box__minus" aria-label="<?php esc_attr_e('کمتر', 'piazhen'); ?>">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </button>
                                <input type="number" class="single-qty-box__input"
                                       value="<?php echo $initial_cart_qty > 0 ? $initial_cart_qty : 1; ?>"
                                       min="1" max="99">
                                <button type="button" class="single-qty-box__btn single-qty-box__plus" aria-label="<?php esc_attr_e('بیشتر', 'piazhen'); ?>">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Tabs Navigation (Anchor Links) -->
        <div class="single-product-tabs-nav" id="product-tabs-nav">
            <div class="tabs-nav-inner">
                <a href="#section-description" class="tab-nav-link active"><?php _e('توضیحات', 'piazhen'); ?></a>
                <a href="#section-specs" class="tab-nav-link"><?php _e('مشخصات فنی', 'piazhen'); ?></a>
                <a href="#section-comments" class="tab-nav-link"><?php _e('نظرات کاربران', 'piazhen'); ?></a>
                <a href="#section-related" class="tab-nav-link"><?php _e('محصولات مرتبط', 'piazhen'); ?></a>
                <a href="#section-faq" class="tab-nav-link"><?php _e('سوالات متداول', 'piazhen'); ?></a>
            </div>
        </div>

        <!-- Description Section -->
        <section class="single-product-section" id="section-description">
            <h2 class="section-heading">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <?php _e('توضیحات محصول', 'piazhen'); ?>
            </h2>
            <div class="section-content product-description">
                <?php the_content(); ?>
            </div>
        </section>

        <!-- Technical Specifications -->
        <section class="single-product-section" id="section-specs">
            <h2 class="section-heading">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <?php _e('مشخصات فنی', 'piazhen'); ?>
            </h2>
            <div class="section-content">
                <?php if (!empty($specs)): ?>
                    <table class="specs-table">
                        <tbody>
                            <?php foreach ($specs as $label => $value): ?>
                                <tr>
                                    <td class="specs-table__label"><?php echo esc_html($label); ?></td>
                                    <td class="specs-table__value"><?php echo esc_html($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted"><?php _e('مشخصات فنی برای این محصول ثبت نشده است.', 'piazhen'); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Comments / Reviews -->
        <section class="single-product-section" id="section-comments">
            <h2 class="section-heading">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <?php _e('نظرات کاربران', 'piazhen'); ?>
            </h2>
            <div class="section-content">
                <?php comments_template('/woocommerce/single-product-reviews.php'); ?>
            </div>
        </section>

        <!-- Related Products -->
        <?php if (!empty($related_ids)): ?>
        <section class="single-product-section" id="section-related">
            <h2 class="section-heading">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="6" height="6" rx="1"/><rect x="10" y="3" width="6" height="6" rx="1"/><rect x="16" y="7" width="6" height="6" rx="1"/><rect x="6" y="11" width="6" height="6" rx="1"/><rect x="14" y="11" width="6" height="6" rx="1"/></svg>
                <?php _e('محصولات مرتبط', 'piazhen'); ?>
            </h2>
            <div class="section-content">
                <div class="related-products-swiper-wrapper position-relative">
                    <div class="swiper related-products-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($related_ids as $related_id):
                                $related_product = wc_get_product($related_id);
                                if (!$related_product) continue;
                            ?>
                                <div class="swiper-slide">
                                    <?php echo pzh_get_product_card_html($related_id); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="related-prev swiper-nav-btn swiper-nav-btn--prev" aria-label="<?php _e('قبلی', 'piazhen'); ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <button class="related-next swiper-nav-btn swiper-nav-btn--next" aria-label="<?php _e('بعدی', 'piazhen'); ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- FAQ Section -->
        <section class="single-product-section" id="section-faq">
            <h2 class="section-heading">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <?php _e('سوالات متداول', 'piazhen'); ?>
            </h2>
            <div class="section-content">
                <div class="faq-accordion">
                    <?php foreach ($faqs as $faq_index => $faq): ?>
                        <div class="faq-item <?php echo $faq_index === 0 ? 'open' : ''; ?>">
                            <button class="faq-item__question" type="button">
                                <span><?php echo esc_html($faq['question']); ?></span>
                                <svg class="faq-item__icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <div class="faq-item__answer">
                                <p><?php echo wp_kses_post($faq['answer']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    </div>

    <!-- Sticky Add to Cart Bar (mobile) -->
    <div class="sticky-add-to-cart d-md-none">
        <div class="sticky-price" id="sticky-price"><?php echo $display_price_html; ?></div>
        <button class="add-to-cart-single <?php echo $can_purchase ? '' : 'out-of-stock'; ?>"
                data-product-id="<?php echo $product_id; ?>"
                <?php echo $is_variable ? 'data-variable="1"' : ''; ?>
                <?php echo $can_purchase ? '' : 'disabled'; ?>>
            <?php echo $can_purchase ? __('افزودن به سبد', 'piazhen') : __('ناموجود', 'piazhen'); ?>
        </button>
    </div>
</main>

<?php endwhile; ?>

<?php get_footer(); ?>
