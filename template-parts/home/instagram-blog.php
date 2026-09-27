<?php
/**
 * Homepage: Blog + Instagram section (per home-end.png)
 * Two 50/50 cards — blog (right) & Instagram (left) — each a 1-per-view
 * dots-only carousel: image + headline + excerpt.
 * The Instagram card shows the newest posts of the shop's Instagram account
 * (see inc/instagram.php), linking each slide to the post on instagram.com.
 *
 * @package Piazhen
 */

// Blog posts
$blog_query = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 6,
    'orderby'        => 'date',
    'order'          => 'DESC',
));

// Instagram posts — newest posts of the shop's account (inc/instagram.php).
// Falls back to local 'instagram'-category posts when the feed is unavailable.
$instagram_posts = pzh_instagram_feed(6);
if (empty($instagram_posts)) {
    $instagram_query = new WP_Query(array(
        'post_type'      => 'post',
        'posts_per_page' => 6,
        'category_name'  => 'instagram',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
}
?>
<section class="home-end py-5">
    <div class="container">
        <div class="row g-4">

            <!-- Blog card (RTL first = right) -->
            <div class="col-md-6">
                <div class="home-end-card">
                    <div class="home-end-card__head">
                        <h3 class="home-end-card__title"><?php _e('مجله پیاژن', 'piazhen'); ?></h3>
                        <span class="home-end-card__icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4h6a4 4 0 0 1 4 4v12a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v12a3 3 0 0 1 3-3h7z"/></svg>
                        </span>
                    </div>

                    <?php if ($blog_query->have_posts()): ?>
                        <div class="swiper home-end-blog-swiper">
                            <div class="swiper-wrapper">
                                <?php while ($blog_query->have_posts()): $blog_query->the_post();
                                    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium_large') ?: wc_placeholder_img_src('medium_large');
                                ?>
                                    <div class="swiper-slide">
                                        <a href="<?php the_permalink(); ?>" class="home-end-slide">
                                            <div class="home-end-slide__image">
                                                <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                                            </div>
                                            <h4 class="home-end-slide__title"><?php the_title(); ?></h4>
                                            <p class="home-end-slide__excerpt">
                                                <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt() ?: get_the_content()), 28, '…')); ?>
                                            </p>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="home-end-dots home-end-blog-dots"></div>
                        </div>
                    <?php else: ?>
                        <p class="home-end-card__empty"><?php _e('هنوز مقاله‌ای منتشر نشده است.', 'piazhen'); ?></p>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>

            <!-- Instagram card (left) — live feed from instagram.com/piazhen -->
            <div class="col-md-6">
                <div class="home-end-card">
                    <div class="home-end-card__head">
                        <h3 class="home-end-card__title">
                            <a href="<?php echo esc_url(pzh_instagram_profile_url()); ?>" target="_blank" rel="noopener"><?php _e('اینستاگرام', 'piazhen'); ?></a>
                        </h3>
                        <span class="home-end-card__icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>
                        </span>
                    </div>

                    <?php if (!empty($instagram_posts)): ?>
                        <div class="swiper home-end-instagram-swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($instagram_posts as $instagram_post):
                                    $ig_caption = trim($instagram_post['caption']);
                                    if (!$ig_caption) {
                                        $ig_caption = __('مشاهده پست در اینستاگرام', 'piazhen');
                                    }
                                ?>
                                    <div class="swiper-slide">
                                        <a href="<?php echo esc_url($instagram_post['url']); ?>" class="home-end-slide" target="_blank" rel="noopener">
                                            <div class="home-end-slide__image">
                                                <img src="<?php echo esc_url($instagram_post['image']); ?>" alt="<?php echo esc_attr(pzh_instagram_username()); ?> در اینستاگرام" loading="lazy">
                                            </div>
                                            <h4 class="home-end-slide__title"><?php echo esc_html(wp_trim_words($ig_caption, 8, '…')); ?></h4>
                                            <p class="home-end-slide__excerpt">
                                                <?php echo esc_html(wp_trim_words($ig_caption, 28, '…')); ?>
                                            </p>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="home-end-dots home-end-instagram-dots"></div>
                        </div>
                    <?php elseif (isset($instagram_query) && $instagram_query->have_posts()): ?>
                        <div class="swiper home-end-instagram-swiper">
                            <div class="swiper-wrapper">
                                <?php while ($instagram_query->have_posts()): $instagram_query->the_post();
                                    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium_large') ?: wc_placeholder_img_src('medium_large');
                                ?>
                                    <div class="swiper-slide">
                                        <a href="<?php the_permalink(); ?>" class="home-end-slide">
                                            <div class="home-end-slide__image">
                                                <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                                            </div>
                                            <h4 class="home-end-slide__title"><?php the_title(); ?></h4>
                                            <p class="home-end-slide__excerpt">
                                                <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt() ?: get_the_content()), 28, '…')); ?>
                                            </p>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="home-end-dots home-end-instagram-dots"></div>
                        </div>
                    <?php else: ?>
                        <p class="home-end-card__empty">
                            <?php _e('پست جدیدی برای نمایش وجود ندارد.', 'piazhen'); ?>
                            <br>
                            <a href="<?php echo esc_url(pzh_instagram_profile_url()); ?>" target="_blank" rel="noopener"><?php _e('ما را در اینستاگرام دنبال کنید', 'piazhen'); ?></a>
                        </p>
                    <?php endif; ?>
                    <?php if (isset($instagram_query)) { wp_reset_postdata(); } ?>
                </div>
            </div>

        </div>
    </div>
</section>
