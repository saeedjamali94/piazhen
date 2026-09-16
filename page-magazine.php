<?php
/**
 * Template Name: مجله پیاژن
 *
 * Magazine (blog) page: hero carousel + custom AJAX filters/search.
 * No plugins — Swiper carousel, custom AJAX endpoint.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Hero carousel posts (latest 6 with thumbnails)
$hero_query = new WP_Query(array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'orderby'        => 'date',
    'order'          => 'DESC',
));

// Initial grid
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search      = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
$page        = max(1, get_query_var('paged'));
$per_page    = 9;

$grid_args = array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $page,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
if ($category_id > 0) $grid_args['cat'] = $category_id;
if ($search !== '')   $grid_args['s'] = $search;

$grid_query = new WP_Query($grid_args);
?>
<main class="pzh-magazine" data-magazine="1">

    <div class="container">

        <!-- Breadcrumb + Title -->
        <nav class="mag-breadcrumb py-3">
            <div class="breadcrumb-trail">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('خانه', 'piazhen'); ?></a>
                <span class="breadcrumb-delimiter">/</span>
                <span><?php _e('مجله پیاژن', 'piazhen'); ?></span>
            </div>
        </nav>

        <header class="mag-head">
            <h1 class="mag-head__title"><?php _e('مجله پیاژن', 'piazhen'); ?></h1>
            <p class="mag-head__subtitle">
                <?php _e('جدیدترین مقالات آموزشی، نکات مراقبت از پوست و مو و راهنمای خرید', 'piazhen'); ?>
            </p>
        </header>

        <!-- Content + Sidebar layout -->
        <div class="mag-layout">

            <div class="mag-layout__main">

                <!-- Hero Carousel (inside the main column, beside the sidebar) -->
                <?php if ($hero_query->have_posts()): ?>
                    <section class="mag-hero" data-hero-carousel="1">
                        <div class="swiper mag-hero-swiper">
                            <div class="swiper-wrapper">
                                <?php while ($hero_query->have_posts()): $hero_query->the_post();
                                    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: wc_placeholder_img_src('large');
                                ?>
                                    <div class="swiper-slide">
                                        <article class="mag-hero-slide" style="background-image: url('<?php echo esc_url($thumb); ?>');">
                                            <div class="mag-hero-slide__overlay">
                                                <h2 class="mag-hero-slide__title">
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </h2>
                                                <a href="<?php the_permalink(); ?>" class="mag-hero-slide__btn">
                                                    <?php _e('مشاهده مقاله', 'piazhen'); ?>
                                                </a>
                                            </div>
                                        </article>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <div class="mag-hero-pagination"></div>
                    </section>
                    <?php wp_reset_postdata(); ?>
                <?php endif; ?>

                <!-- Filters + Search (custom AJAX) -->
                <section class="mag-toolbar" data-mag-toolbar="1">
                    <div class="mag-toolbar__filters">
                        <button type="button" class="mag-filter <?php echo $category_id === 0 ? 'active' : ''; ?>" data-category="0">
                            <?php _e('همه', 'piazhen'); ?>
                        </button>
                        <?php foreach (pzh_get_blog_categories() as $cat): ?>
                            <button type="button" class="mag-filter <?php echo $category_id === $cat->term_id ? 'active' : ''; ?>"
                                    data-category="<?php echo intval($cat->term_id); ?>">
                                <?php echo esc_html($cat->name); ?>
                                <span class="mag-filter__count"><?php echo pzh_fa_num($cat->count); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="mag-toolbar__search">
                        <input type="search" class="mag-search-input" id="mag-search"
                               placeholder="<?php _e('جستجو در مقالات...', 'piazhen'); ?>"
                               value="<?php echo esc_attr($search); ?>">
                        <button type="button" class="mag-search-btn" id="mag-search-btn" aria-label="<?php esc_attr_e('جستجو', 'piazhen'); ?>">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </section>

                <!-- Posts Grid (AJAX) -->
                <div class="mag-results" id="mag-results"
                     data-per-page="<?php echo intval($per_page); ?>"
                     data-category="<?php echo intval($category_id); ?>">
                    <?php echo pzh_render_magazine_grid($grid_query, $page, $per_page); ?>
                </div>
                <?php wp_reset_postdata(); ?>

            </div>

            <!-- Sidebar (all magazine pages) -->
            <?php get_template_part('template-parts/magazine/sidebar'); ?>

        </div>

    </div>
</main>

<?php get_footer(); ?>
