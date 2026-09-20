<?php
/**
 * Homepage: Newsest Products Carousel
 * 5 items in view, auto-loaded by date sort
 */
$products_query = pzh_get_newest_products(15);

if (!$products_query->have_posts()) {
    return; // No products to show
}

$shop_url = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : SITE_URL . '/shop';
?>
<section class="home_most_selling py-5">
    <div class="container">
        <div class="section-title-row">
            <?php
            // Section title
            set_query_var('texts', array(
                'heading'    => __('جدیدترین محصولات', 'piazhen'),
            ));
            get_template_part('template-parts/global/section', 'title');
            ?>
        </div>

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
            <button class="most-selling-prev swiper-nav-btn swiper-nav-btn--prev" aria-label="<?php _e('قبلی', 'piazhen'); ?>">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <button class="most-selling-next swiper-nav-btn swiper-nav-btn--next" aria-label="<?php _e('بعدی', 'piazhen'); ?>">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); ?>
