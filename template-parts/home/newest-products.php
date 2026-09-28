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
                'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="34" height="29" viewBox="0 0 34 29" fill="none">
                                    <path d="M25.0356 1.5H8.14305C7.7536 1.51078 7.37215 1.61371 7.0295 1.80049C6.68686 1.98727 6.39269 2.25265 6.17071 2.57519L1.9476 8.46533C1.63402 8.9117 1.47741 9.45079 1.50263 9.99703C1.52785 10.5433 1.73346 11.0654 2.08683 11.4805L14.7562 26.1824C14.9731 26.4641 15.251 26.692 15.5687 26.8486C15.8864 27.0054 16.2355 27.0867 16.5893 27.0867C16.943 27.0867 17.2921 27.0054 17.6099 26.8486C17.9276 26.692 18.2055 26.4641 18.4224 26.1824L31.0917 11.4805C31.445 11.0654 31.6507 10.5433 31.676 9.99703C31.7011 9.45079 31.5446 8.9117 31.231 8.46533L27.0079 2.57519C26.7859 2.25265 26.4918 1.98727 26.1492 1.80049C25.8065 1.61371 25.4249 1.51078 25.0356 1.5Z" stroke="#FBCA38" stroke-width="3" stroke-linejoin="round"/>
                                    <path d="M15.3822 1.47705L9.7179 10.3123L16.5893 27.0243" stroke="#FBCA38" stroke-width="3" stroke-linejoin="round"/>
                                    <path d="M17.866 1.47705L23.5071 10.3123L16.5892 27.0243" stroke="#FBCA38" stroke-width="3" stroke-linejoin="round"/>
                                    <path d="M1.54651 10.3124H31.6322" stroke="#FBCA38" stroke-width="3" stroke-linejoin="round"/>
                                </svg>'
            ));
            get_template_part('template-parts/global/section', 'title');
            ?>
        </div>

        <div class="most-selling-swiper-wrapper position-relative">
            <div class="swiper most-selling-swiper mb-5">
                <div class="swiper-wrapper">
                    <?php while ($products_query->have_posts()): $products_query->the_post(); ?>
                        <div class="swiper-slide">
                            <?= pzh_get_product_card_html(get_the_ID()); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="swiper-dots most-selling-dots"></div>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); ?>
