<?php
/**
 * Homepage: Newest Products - 2x2 grid carousel (col-md-6)
 */
$products_query = pzh_get_newest_products(8);

if (!$products_query->have_posts()) {
    return;
}
?>
<div class="home_newest col-md-6">
    <?php
    set_query_var('texts', array(
        'topBtnText' => __('جدیدترین‌ها', 'piazhen'),
        'heading'    => __('محصولات جدید', 'piazhen'),
        'text'       => __('جدیدترین محصولات اضافه شده به فروشگاه', 'piazhen'),
    ));
    get_template_part('template-parts/global/section', 'title');
    ?>

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

        <button class="newest-prev swiper-nav-btn swiper-nav-btn--prev">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <button class="newest-next swiper-nav-btn swiper-nav-btn--next">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
    </div>
</div>
<?php wp_reset_postdata(); ?>
