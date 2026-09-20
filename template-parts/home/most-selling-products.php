<?php
/**
 * Homepage: Newest Products - 2x2 grid carousel (col-md-6)
 */
$products_query = pzh_get_most_selling_products(8);

if (!$products_query->have_posts()) {
    return;
}

$shop_url = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : SITE_URL . '/shop';
?>
<div class="home_newest col-md-6">
    <div class="section-head">
        <div class="section-head__right">
            <span class="section-head__icon">
                <svg width="24" height="24" viewBox="0 0 14 14" fill="currentColor">
                    <path d="M6.8 13.6C6.8 7.5 6.8 7.5 6.8 13.6C6.4 7.5 6.1 7.2 0 6.8C6.1 6.8 6.1 6.8 0 6.8C6.1 6.4 6.4 6.1 6.8 0C6.8 6.1 6.8 6.1 6.8 0C7.2 6.1 7.5 6.4 13.6 6.8C7.5 6.8 7.5 6.8 13.6 6.8C7.5 7.2 7.2 7.5 6.8 13.6Z"/>
                </svg>
            </span>
            <div>
                <h3 class="section-head__title"><?php _e('محصولات پرفروش', 'piazhen'); ?></h3>
            </div>
        </div>

    </div>

    <div class="newest-products-swiper-wrapper position-relative">
        <div class="swiper newest-products-swiper">
            <div class="swiper-wrapper">
                <?php while ($products_query->have_posts()): $products_query->the_post(); ?>
                    <div class="swiper-slide">
                        <?= pzh_get_product_card_html(get_the_ID()); ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
<?php wp_reset_postdata(); ?>
