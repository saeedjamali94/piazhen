<?php
/**
 * Homepage: Most Selling Products Carousel
 * 5 items in view, auto-loaded by WooCommerce total_sales
 */
$products_query = pzh_get_most_selling_products(15);

if (!$products_query->have_posts()) {
    return; // No products to show
}
?>
<section class="home_most_selling py-5">
    <div class="container">
        <?php
        // Section title
        set_query_var('texts', array(
            'topBtnText' => __('پرفروش‌ترین‌ها', 'piazhen'),
            'heading'    => __('محصولات پرفروش', 'piazhen'),
            'text'       => __('محبوب‌ترین محصولات فروشگاه ما که بیشترین خرید را داشته‌اند', 'piazhen'),
        ));
        get_template_part('template-parts/global/section', 'title');
        ?>

        <div class="most-selling-swiper-wrapper position-relative">
            <div class="swiper most-selling-swiper">
                <div class="swiper-wrapper">
                    <?php while ($products_query->have_posts()): $products_query->the_post(); ?>
                        <div class="swiper-slide">
                            <?= pzh_get_product_card_html(get_the_ID()); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Navigation Arrows -->
            <button class="most-selling-prev swiper-nav-btn swiper-nav-btn--prev">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <button class="most-selling-next swiper-nav-btn swiper-nav-btn--next">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); ?>
