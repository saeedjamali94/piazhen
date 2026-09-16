<?php
/**
 * Single blog post (مجله) — with the magazine sidebar.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()): the_post();
    $categories = get_the_category();
    $cat_name   = !empty($categories) ? $categories[0]->name : '';
    $cat_link   = !empty($categories) ? get_category_link($categories[0]) : home_url('/blog/');
?>
<main class="pzh-single-post">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="mag-breadcrumb py-3">
            <div class="breadcrumb-trail">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('خانه', 'piazhen'); ?></a>
                <span class="breadcrumb-delimiter">/</span>
                <a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php _e('مجله پیاژن', 'piazhen'); ?></a>
                <?php if ($cat_name): ?>
                    <span class="breadcrumb-delimiter">/</span>
                    <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat_name); ?></a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="mag-layout">

            <!-- Article -->
            <article class="mag-layout__main post-article">
                <header class="post-article__header">
                    <?php if ($cat_name): ?>
                        <a href="<?php echo esc_url($cat_link); ?>" class="post-article__category"><?php echo esc_html($cat_name); ?></a>
                    <?php endif; ?>
                    <h1 class="post-article__title"><?php the_title(); ?></h1>
                    <div class="post-article__meta">
                        <span class="post-article__date">
                            <i class="fa-regular fa-calendar"></i>
                            <?php echo get_the_date('Y/m/d'); ?>
                        </span>
                        <span class="post-article__read-time">
                            <i class="fa-regular fa-clock"></i>
                            <?php printf(__('%s دقیقه مطالعه', 'piazhen'), pzh_fa_num(pzh_post_reading_time(get_the_ID()))); ?>
                        </span>
                        <span class="post-article__author">
                            <i class="fa-regular fa-user"></i>
                            <?php echo esc_html(get_the_author()); ?>
                        </span>
                    </div>
                </header>

                <?php if (has_post_thumbnail()): ?>
                    <div class="post-article__thumb">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="post-article__content">
                    <?php the_content(); ?>
                </div>

                <footer class="post-article__footer">
                    <?php if ($cat_name): ?>
                        <a href="<?php echo esc_url(home_url('/blog/')); ?>" class="post-article__back">
                            <i class="fa-solid fa-arrow-right"></i>
                            <?php _e('بازگشت به مجله', 'piazhen'); ?>
                        </a>
                    <?php endif; ?>
                </footer>
            </article>

            <!-- Sidebar (all magazine pages) -->
            <?php get_template_part('template-parts/magazine/sidebar'); ?>

        </div>

    </div>
</main>
<?php endwhile; ?>

<?php get_footer(); ?>
