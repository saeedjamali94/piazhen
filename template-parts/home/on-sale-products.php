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
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 28 33" fill="none">
                <path d="M25.8628 19.8917C25.8628 8.18457 15.5945 2.76827 10.758 1.5C12.5679 6.05706 12.4695 9.43047 10.1332 13.5876C9.75491 14.2606 8.83516 14.3275 8.32723 13.7474L5.35518 10.3535C-2.41865 18.6706 2.05689 32.0485 14.3666 31.4265C23.7849 30.841 25.8628 24.5254 25.8628 19.8917Z" stroke="#FBCA38" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10.5882 25.6446L18.2102 17.9703" stroke="#FBCA38" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M11.5114 19.086C11.1909 19.086 10.9311 18.8244 10.9311 18.5017C10.9311 18.179 11.1909 17.9174 11.5114 17.9174" stroke="#FBCA38" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M11.5114 19.086C11.832 19.086 12.0918 18.8244 12.0918 18.5017C12.0918 18.179 11.832 17.9174 11.5114 17.9174" stroke="#FBCA38" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17.684 25.6988C17.3634 25.6988 17.1036 25.437 17.1036 25.1145C17.1036 24.7917 17.3634 24.5302 17.684 24.5302" stroke="#FBCA38" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17.684 25.6988C18.0045 25.6988 18.2643 25.437 18.2643 25.1145C18.2643 24.7917 18.0045 24.5302 17.684 24.5302" stroke="#FBCA38" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
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
