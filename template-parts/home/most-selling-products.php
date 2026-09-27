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
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 33 33" fill="none">
                <g clip-path="url(#clip0_22_104)">
                <path d="M25.4102 31.3393C24.7693 31.3393 24.2495 30.8195 24.2495 30.1785C24.2495 29.5376 24.7693 29.0178 25.4102 29.0178C26.0514 29.0178 26.5709 29.5376 26.5709 30.1785C26.5709 30.8195 26.0514 31.3393 25.4102 31.3393Z" stroke="#FBCA38" stroke-width="3" stroke-linejoin="round"/>
                <path d="M10.321 31.3393C9.67995 31.3393 9.16028 30.8195 9.16028 30.1785C9.16028 29.5376 9.67995 29.0178 10.321 29.0178C10.9621 29.0178 11.4817 29.5376 11.4817 30.1785C11.4817 30.8195 10.9621 31.3393 10.321 31.3393Z" stroke="#FBCA38" stroke-width="3" stroke-linejoin="round"/>
                <path d="M1.16077 1.16071H6.96434L7.57525 6.96428M7.57525 6.96428L9.067 21.1359C9.19135 22.3173 10.1876 23.2143 11.3757 23.2143H26.0446C27.1099 23.2143 28.0385 22.4893 28.2969 21.4559L31.1987 9.84874C31.565 8.38357 30.4567 6.96428 28.9464 6.96428H7.57525Z" stroke="#FBCA38" stroke-width="3" stroke-linejoin="round"/>
                <path d="M14.9037 15.6696L17.9989 17.9911L23.0771 12.1875" stroke="#FBCA38" stroke-width="3" stroke-linejoin="round"/>
                </g>
                <defs>
                <clipPath id="clip0_22_104">
                <rect width="32.5" height="32.5" fill="white"/>
                </clipPath>
                </defs>
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
        <div class="swiper-dots newest-products-dots"></div>
    </div>
</div>
<?php wp_reset_postdata(); ?>
