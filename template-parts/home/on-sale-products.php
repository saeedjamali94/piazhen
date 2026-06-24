<?php
/**
 * Homepage: On Sale Products - 2x2 grid carousel (col-md-6)
 */
$products_query = pzh_get_on_sale_products(8);

if (!$products_query->have_posts()) {
    return;
}
?>
<div class="home_on_sale col-md-6">
    <?php
    set_query_var('texts', array(
        'topBtnText' => __('تخفیف‌ها', 'piazhen'),
        'heading'    => __('محصولات تخفیف‌دار', 'piazhen'),
        'text'       => __('محصولاتی که شامل تخفیف ویژه شده‌اند', 'piazhen'),
    ));
    get_template_part('template-parts/global/section', 'title');
    ?>

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

        <button class="sale-prev swiper-nav-btn swiper-nav-btn--prev">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <button class="sale-next swiper-nav-btn swiper-nav-btn--next">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
    </div>
</div>
<?php wp_reset_postdata(); ?>
