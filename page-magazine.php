<?php
/**
 * Template Name: مجله پیاژن
 *
 * Magazine (blog) page per blog-page.png: left promo column (3 yellow
 * quick-links + search) beside the hero slider, category tabs, and a single
 * column of wide horizontal post cards. Custom AJAX filters — no plugins.
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
$per_page    = 6;

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

$blog_categories = pzh_get_blog_categories();
?>
<main class="pzh-magazine" data-magazine="1">

    <div class="container">

        <!-- ============ Hero: slider (right) + promo column (left) ============ -->
        <section class="mag-hero">

            <!-- Main column: image slider (first in DOM = right side in RTL) -->
            <div class="mag-slider" data-hero-carousel="1">
                <?php if ($hero_query->have_posts()): ?>
                    <div class="swiper mag-hero-swiper">
                        <div class="swiper-wrapper">
                            <?php while ($hero_query->have_posts()): $hero_query->the_post();
                                $thumb = pzh_post_thumb_src(get_the_ID(), 'large');
                            ?>
                                <div class="swiper-slide">
                                    <article class="mag-hero-slide" style="background-image: url('<?php echo esc_url($thumb); ?>');">
                                        <h2 class="mag-hero-slide__title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h2>
                                        <a href="<?php the_permalink(); ?>" class="mag-hero-slide__btn">
                                            <?php _e('مشاهده مقاله', 'piazhen'); ?>
                                        </a>
                                    </article>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="mag-hero-pagination"></div>
                    <?php wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>

            <!-- Left promo column: 3 yellow quick-links + search (shared part) -->
            <?php get_template_part('template-parts/magazine/promo-column'); ?>

        </section>

        <!-- ============ Category tabs ============ -->
        <section class="mag-tabs">
            <?php
            $active_cat = $category_id;
            foreach ($blog_categories as $cat):
                if ($active_cat === 0) $active_cat = $cat->term_id; // first category is the default
                ?>
                <button type="button" class="mag-tab <?php echo $category_id === $cat->term_id || ($category_id === 0 && $cat->term_id === $active_cat) ? 'active' : ''; ?>"
                        data-category="<?php echo intval($cat->term_id); ?>">
                    <?php echo esc_html($cat->name); ?>
                </button>
            <?php endforeach; ?>
        </section>

        <!-- ============ Posts (AJAX, single column of wide cards) ============ -->
        <div class="mag-results" id="mag-results"
             data-per-page="<?php echo intval($per_page); ?>"
             data-category="<?php echo intval($active_cat); ?>">
            <?php echo pzh_render_magazine_grid($grid_query, $page, $per_page); ?>
        </div>
        <?php wp_reset_postdata(); ?>

    </div>
</main>

<?php get_footer(); ?>
