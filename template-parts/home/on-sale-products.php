<?php
/**
 * Homepage: On Sale Products - 2x2 grid carousel (col-md-6)
 */
$products_query = pzh_get_on_sale_products(8);

if (!$products_query->have_posts()) {
    return;
}

$shop_url = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : SITE_URL . '/shop';
?>
<div class="home_on_sale col-md-6">
    <div class="section-head">
        <div class="section-head__right">
            <span class="section-head__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13 2L4.5 13.5H11L10 22L19.5 10H13L13 2Z"/>
                </svg>
            </span>
            <div>
                <h3 class="section-head__title"><?php _e('محصولات تخفیف‌دار', 'piazhen'); ?></h3>
            </div>
        </div>

    </div>

    <div class="on-sale-swiper-wrapper position-relative">
        <div class="swiper on-sale-swiper">
            <div class="swiper-wrapper">
                <?php while ($products_query->have_posts()): $products_query->the_post(); ?>
                    <div class="swiper-slide">
                        <?= pzh_get_product_card_html(get_the_ID()); ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <div class="swiper-dots on-sale-dots"></div>
    </div>
</div>
<?php wp_reset_postdata(); ?>
