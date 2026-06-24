<?php
/**
 * WooCommerce Single Product Page
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
    $all_images      = array_merge(
        array(get_post_thumbnail_id($product_id)),
        $gallery_ids
    );
    $all_images      = array_filter($all_images);
    $brand_name      = pzh_get_product_brand($product_id);
    $brand_link      = pzh_get_product_brand_link($product_id);
    $categories      = wc_get_product_category_list($product_id, ', ');
    $specs           = pzh_get_product_specs($product_id);
    $faqs            = pzh_get_product_faqs($product_id);
    $related_ids     = pzh_get_related_products($product_id, 10);
    $whatsapp_url    = 'https://wa.me/?text=' . urlencode($product->get_name() . ' - ' . get_permalink($product_id));
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

        <!-- Product Top: Gallery + Info -->
        <div class="single-product-top row g-4">
            <!-- Product Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <!-- Main Image -->
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

                        <!-- Action Buttons Overlay -->
                        <div class="product-gallery__actions">
                            <button class="gallery-action-btn favorite-btn <?php echo pzh_is_favorited($product_id) ? 'active' : ''; ?>"
                                    data-product-id="<?php echo $product_id; ?>"
                                    title="<?php _e('افزودن به علاقه‌مندی‌ها', 'piazhen'); ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                            <button class="gallery-action-btn compare-btn"
                                    data-product-id="<?php echo $product_id; ?>"
                                    title="<?php _e('مقایسه', 'piazhen'); ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="8" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="16" y2="21"/>
                                    <circle cx="8" cy="8" r="2"/><circle cx="8" cy="16" r="2"/>
                                    <circle cx="16" cy="11" r="2"/>
                                </svg>
                            </button>
                            <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener"
                               class="gallery-action-btn whatsapp-btn"
                               title="<?php _e('اشتراک‌گذاری در واتساپ', 'piazhen'); ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Sale Badge -->
                        <?php if ($product->is_on_sale()): ?>
                            <span class="product-gallery__sale-badge">٪<?php echo pzh_get_discount_percentage($product); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Thumbnails -->
                    <?php if (count($all_images) > 1): ?>
                    <div class="product-gallery__thumbs">
                        <div class="swiper product-thumbs-swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($all_images as $img_id): ?>
                                    <div class="swiper-slide">
                                        <div class="product-gallery__thumb <?php echo $img_id === $all_images[0] ? 'active' : ''; ?>"
                                             data-full="<?php echo wp_get_attachment_image_url($img_id, 'woocommerce_single'); ?>"
                                             data-zoom="<?php echo wp_get_attachment_image_url($img_id, 'full'); ?>">
                                            <?php echo wp_get_attachment_image($img_id, 'pzh_product_thumb', false, array('class' => 'w-100')); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button class="thumb-nav thumb-nav--prev">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M9 11L5 7L9 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                        <button class="thumb-nav thumb-nav--next">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3L9 7L5 11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-info">

                    <!-- Title -->
                    <h1 class="product-info__title"><?php the_title(); ?></h1>

                    <!-- Subtitle / Short Description -->
                    <?php if ($product->get_short_description()): ?>
                        <p class="product-info__subtitle"><?php echo $product->get_short_description(); ?></p>
                    <?php endif; ?>

                    <!-- Brand + Category + Meta -->
                    <div class="product-info__meta">
                        <?php if ($brand_name): ?>
                            <span class="product-info__brand">
                                <span class="meta-label"><?php _e('برند:', 'piazhen'); ?></span>
                                <a href="<?php echo esc_url($brand_link); ?>"><?php echo esc_html($brand_name); ?></a>
                            </span>
                            <span class="meta-sep">|</span>
                        <?php endif; ?>
                        <?php if ($categories): ?>
                            <span class="product-info__category">
                                <span class="meta-label"><?php _e('دسته:', 'piazhen'); ?></span>
                                <?php echo $categories; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Price -->
                    <div class="product-info__price">
                        <?php echo $product->get_price_html(); ?>
                    </div>

                    <!-- Variations -->
                    <?php if ($product->is_type('variable')): ?>
                        <div class="product-info__variations">
                            <?php
                            // Render WooCommerce variation form
                            woocommerce_template_single_add_to_cart();
                            ?>
                        </div>
                    <?php else: ?>
                        <!-- Simple Product Add to Cart -->
                        <div class="product-info__add-to-cart">
                            <div class="quantity-selector d-flex align-items-center gap-2 mb-3">
                                <button class="qty-btn qty-minus">-</button>
                                <input type="number" class="qty-input" value="1" min="1" max="<?php echo $product->get_stock_quantity() ?: 99; ?>" id="single-qty">
                                <button class="qty-btn qty-plus">+</button>
                            </div>
                            <button class="add-to-cart-single mainBtn mainBtn--yellow"
                                    data-product-id="<?php echo $product_id; ?>">
                                <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <use xlink:href="<?php echo SPRITE_URL; ?>#cart"></use>
                                </svg>
                                <?php _e('افزودن به سبد خرید', 'piazhen'); ?>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Specs -->
                    <?php if (!empty($specs)): ?>
                        <div class="product-info__quick-specs">
                            <h4 class="quick-specs-title"><?php _e('مشخصات کلی', 'piazhen'); ?></h4>
                            <ul class="quick-specs-list">
                                <?php $i = 0; foreach ($specs as $label => $value): if ($i++ >= 5) break; ?>
                                    <li>
                                        <span class="spec-label"><?php echo esc_html($label); ?>:</span>
                                        <span class="spec-value"><?php echo esc_html($value); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- SKU / Tags -->
                    <div class="product-info__footer-meta">
                        <?php if ($product->get_sku()): ?>
                            <span class="sku"><?php _e('کد محصول:', 'piazhen'); ?> <?php echo $product->get_sku(); ?></span>
                        <?php endif; ?>
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
            <div class="section-content">
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
                    <button class="related-prev swiper-nav-btn swiper-nav-btn--prev">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <button class="related-next swiper-nav-btn swiper-nav-btn--next">
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
                            <button class="faq-item__question">
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
</main>

<?php endwhile; ?>

<?php get_footer(); ?>
