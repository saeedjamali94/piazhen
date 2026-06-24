<?php
/**
 * Homepage: Instagram Posts + Blog Posts
 * Two-column section with carousels (1 item per view each)
 */

// Instagram posts (from a specific category or custom)
$instagram_query = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 6,
    'category_name'  => 'instagram',
    'orderby'        => 'date',
    'order'          => 'DESC',
));

// Blog posts
$blog_query = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 6,
    'orderby'        => 'date',
    'order'          => 'DESC',
));
?>
<section class="home_instagram_blog py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Instagram Section -->
            <div class="col-md-6">
                <div class="section-card">
                    <?php
                    set_query_var('texts', array(
                        'heading' => __('اینستاگرام پی‌آژن', 'piazhen'),
                        'text'    => __('ما را در اینستاگرام دنبال کنید', 'piazhen'),
                    ));
                    get_template_part('template-parts/global/section', 'title');
                    ?>

                    <?php if ($instagram_query->have_posts()): ?>
                        <div class="instagram-swiper-wrapper position-relative">
                            <div class="swiper instagram-swiper">
                                <div class="swiper-wrapper">
                                    <?php while ($instagram_query->have_posts()): $instagram_query->the_post(); ?>
                                        <div class="swiper-slide">
                                            <div class="instagram-card">
                                                <?php if (has_post_thumbnail()): ?>
                                                    <div class="instagram-card__image">
                                                        <?php the_post_thumbnail('large', array('class' => 'w-100')); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="instagram-card__content">
                                                    <h4 class="instagram-card__title"><?php the_title(); ?></h4>
                                                    <p class="instagram-card__excerpt"><?= wp_trim_words(get_the_excerpt(), 15); ?></p>
                                                    <a href="<?php the_permalink(); ?>" class="instagram-card__link">
                                                        <?php _e('مشاهده در اینستاگرام', 'piazhen'); ?>
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 4L14 8L10 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M14 8H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <button class="instagram-prev swiper-nav-btn swiper-nav-btn--prev">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                            <button class="instagram-next swiper-nav-btn swiper-nav-btn--next">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="instagram-placeholder text-center py-4">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#E1306C" fill-opacity="0.1"/><path d="M24 16C19.6 16 16 19.6 16 24C16 28.4 19.6 32 24 32C28.4 32 32 28.4 32 24C32 19.6 28.4 16 24 16ZM29.6 30.8C28.8 31.2 27.6 31.6 24 31.6C20.4 31.6 19.2 31.2 18.4 30.8C17.6 30.4 17.2 29.6 17.2 28.8C17.2 28 17.2 26 17.2 24C17.2 20.4 17.6 19.2 18.4 18.4C19.2 17.6 20.4 17.2 24 17.2C27.6 17.2 28.8 17.6 29.6 18.4C30.4 19.2 30.8 20.4 30.8 24C30.8 27.6 30.4 28.8 29.6 30.8Z" fill="#E1306C"/><circle cx="30" cy="18" r="1.5" fill="#E1306C"/></svg>
                            <p class="mt-3"><?php _e('پست‌های اینستاگرام به زودی نمایش داده می‌شوند.', 'piazhen'); ?></p>
                            <a href="https://instagram.com/piazhen" target="_blank" rel="noopener" class="mainBtn small mt-2">
                                <?php _e('ما را دنبال کنید', 'piazhen'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>

            <!-- Blog Section -->
            <div class="col-md-6">
                <div class="section-card">
                    <?php
                    set_query_var('texts', array(
                        'heading' => __('وبلاگ پی‌آژن', 'piazhen'),
                        'text'    => __('جدیدترین مقالات و اخبار', 'piazhen'),
                    ));
                    get_template_part('template-parts/global/section', 'title');
                    ?>

                    <?php if ($blog_query->have_posts()): ?>
                        <div class="blog-swiper-wrapper position-relative">
                            <div class="swiper blog-swiper">
                                <div class="swiper-wrapper">
                                    <?php while ($blog_query->have_posts()): $blog_query->the_post(); ?>
                                        <div class="swiper-slide">
                                            <div class="blog-card">
                                                <?php if (has_post_thumbnail()): ?>
                                                    <div class="blog-card__image">
                                                        <?php the_post_thumbnail('medium_large', array('class' => 'w-100')); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="blog-card__content">
                                                    <span class="blog-card__date"><?= get_the_date('d F Y'); ?></span>
                                                    <h4 class="blog-card__title"><?php the_title(); ?></h4>
                                                    <p class="blog-card__excerpt"><?= wp_trim_words(get_the_excerpt(), 15); ?></p>
                                                    <a href="<?php the_permalink(); ?>" class="blog-card__link">
                                                        <?php _e('ادامه مطلب', 'piazhen'); ?>
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 4L14 8L10 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M14 8H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <button class="blog-prev swiper-nav-btn swiper-nav-btn--prev">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                            <button class="blog-next swiper-nav-btn swiper-nav-btn--next">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    <?php else: ?>
                        <p class="text-center py-4"><?php _e('هنوز مقاله‌ای منتشر نشده است.', 'piazhen'); ?></p>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
