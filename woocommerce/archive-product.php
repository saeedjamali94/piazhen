<?php
/**
 * WooCommerce Product Archive
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get current category if on category page
$current_category = null;
$current_term = get_queried_object();
if ($current_term && is_a($current_term, 'WP_Term') && $current_term->taxonomy === 'product_cat') {
    $current_category = $current_term;
}
?>

<main class="woo-archive">
    <div class="container">
        <!-- a. Categories / Subcategories Carousel -->
        <section class="archive-categories py-4">
            <?php
            // Get subcategories if on a parent category, otherwise get top-level categories
            $parent_id = $current_category ? $current_category->term_id : 0;
            $categories = get_terms(array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => true,
                'parent'     => $parent_id,
                'number'     => 15,
            ));

            // If no children, get siblings
            if (empty($categories) || is_wp_error($categories)) {
                if ($current_category && $current_category->parent) {
                    $categories = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'parent'     => $current_category->parent,
                        'number'     => 15,
                    ));
                } else {
                    $categories = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'parent'     => 0,
                        'number'     => 15,
                    ));
                }
            }

            if (!empty($categories) && !is_wp_error($categories)):
            ?>
                <div class="categories-swiper-wrapper position-relative">
                    <div class="swiper categories-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($categories as $cat):
                                $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                                $image = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : wc_placeholder_img_src('thumbnail');
                            ?>
                                <div class="swiper-slide">
                                    <a href="<?= get_term_link($cat); ?>" class="category-card">
                                        <div class="category-card__image">
                                            <img src="<?= esc_url($image); ?>" alt="<?= esc_attr($cat->name); ?>">
                                        </div>
                                        <span class="category-card__title"><?= esc_html($cat->name); ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="categories-prev swiper-nav-btn swiper-nav-btn--prev">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 4L6 8L10 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <button class="categories-next swiper-nav-btn swiper-nav-btn--next">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            <?php endif; ?>
        </section>

        <div class="row">
            <!-- b. Sidebar with AJAX Filters -->
            <aside class="col-lg-3">
                <div class="archive-filters" data-category-id="<?= $current_category ? esc_attr($current_category->term_id) : ''; ?>">
                    <h3 class="archive-filters__title"><?php _e('فیلترها', 'piazhen'); ?></h3>

                    <!-- Brand Filter -->
                    <?php if (taxonomy_exists('product_brand')):
                        $brands = get_terms(array('taxonomy' => 'product_brand', 'hide_empty' => true));
                        if (!empty($brands) && !is_wp_error($brands)):
                    ?>
                        <div class="filter-group">
                            <h4 class="filter-group__title"><?php _e('برند', 'piazhen'); ?></h4>
                            <div class="filter-group__items">
                                <?php foreach ($brands as $brand): ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="brands[]" value="<?= $brand->term_id; ?>">
                                        <span class="filter-checkbox__mark"></span>
                                        <span class="filter-checkbox__label"><?= esc_html($brand->name); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; endif; ?>

                    <!-- Price Range Filter -->
                    <?php
                    $cat_id  = $current_category ? $current_category->term_id : 0;
                    $prices  = pzh_get_category_price_range($cat_id);
                    $p_min   = $prices['min'];
                    $p_max   = $prices['max'];
                    // Use GET params if present, otherwise auto-detect from category
                    $val_min = isset($_GET['min_price']) ? intval($_GET['min_price']) : $p_min;
                    $val_max = isset($_GET['max_price']) ? intval($_GET['max_price']) : $p_max;
                    ?>
                    <div class="filter-group" id="price-filter-group">
                        <h4 class="filter-group__title"><?php _e('محدوده قیمت', 'piazhen'); ?></h4>
                        <div class="price-range">
                            <div class="price-range__inputs d-flex gap-2">
                                <input type="number" id="price-min" class="mainInput" placeholder="<?php _e('حداقل', 'piazhen'); ?>"
                                       value="<?= $val_min; ?>" min="<?= $p_min; ?>" step="1000">
                                <span class="price-range__separator">-</span>
                                <input type="number" id="price-max" class="mainInput" placeholder="<?php _e('حداکثر', 'piazhen'); ?>"
                                       value="<?= $val_max; ?>" min="<?= $p_min; ?>" step="1000">
                            </div>
                            <div class="price-range__slider-wrapper">
                                <input type="range" id="price-range-min" class="price-range__slider price-range__slider--min"
                                       min="<?= $p_min; ?>" max="<?= $p_max; ?>" step="1000"
                                       value="<?= $val_min; ?>">
                                <input type="range" id="price-range-max" class="price-range__slider price-range__slider--max"
                                       min="<?= $p_min; ?>" max="<?= $p_max; ?>" step="1000"
                                       value="<?= $val_max; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Attribute Filters -->
                    <?php
                    $attribute_taxonomies = wc_get_attribute_taxonomies();
                    if (!empty($attribute_taxonomies)):
                        foreach ($attribute_taxonomies as $attribute):
                            $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);
                            if (!taxonomy_exists($taxonomy)) continue;

                            $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => true));
                            if (empty($terms) || is_wp_error($terms)) continue;
                    ?>
                        <div class="filter-group">
                            <h4 class="filter-group__title"><?= esc_html($attribute->attribute_label); ?></h4>
                            <div class="filter-group__items">
                                <?php foreach ($terms as $term): ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="attr_<?= esc_attr($taxonomy); ?>[]" value="<?= $term->slug; ?>">
                                        <span class="filter-checkbox__mark"></span>
                                        <span class="filter-checkbox__label"><?= esc_html($term->name); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>

                    <!-- Filter Buttons -->
                    <div class="filter-group filter-actions d-flex gap-2">
                        <button type="button" class="apply-filters-btn mainBtn small w-100"><?php _e('اعمال فیلتر', 'piazhen'); ?></button>
                        <button type="button" class="reset-filters-btn mainBtn small w-100" style="background: transparent; color: var(--textColor); border-color: #ddd;">
                            <?php _e('حذف فیلتر', 'piazhen'); ?>
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Products Grid Area -->
            <div class="col-lg-9">
                <!-- c. Sort Options + Total Count -->
                <div class="archive-toolbar d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                    <div class="sort-options d-flex align-items-center gap-3">
                        <span class="sort-label"><?php _e('مرتب‌سازی:', 'piazhen'); ?></span>
                        <label class="sort-radio">
                            <input type="radio" name="sort" value="newest" checked>
                            <span><?php _e('جدیدترین', 'piazhen'); ?></span>
                        </label>
                        <label class="sort-radio">
                            <input type="radio" name="sort" value="price-asc">
                            <span><?php _e('ارزان‌ترین', 'piazhen'); ?></span>
                        </label>
                        <label class="sort-radio">
                            <input type="radio" name="sort" value="price-desc">
                            <span><?php _e('گران‌ترین', 'piazhen'); ?></span>
                        </label>
                        <label class="sort-radio">
                            <input type="radio" name="sort" value="most-sells">
                            <span><?php _e('پرفروش‌ترین', 'piazhen'); ?></span>
                        </label>
                        <label class="sort-radio">
                            <input type="radio" name="sort" value="discount">
                            <span><?php _e('بیشترین تخفیف', 'piazhen'); ?></span>
                        </label>
                    </div>

                    <div class="products-count-wrapper">
                        <span class="products-count"><?= wc_get_loop_prop('total', 0); ?></span>
                        <span><?php _e('محصول', 'piazhen'); ?></span>
                    </div>
                </div>

                <!-- d. Products Grid -->
                <div class="products-grid-wrapper">
                    <?php
                    // Initial load - render products via WooCommerce default loop
                    if (wc_get_loop_prop('total', 0) > 0){
                        echo '<div class="products-grid">';
                        if (woocommerce_product_loop()) {
                            while (have_posts()) {
                                the_post();
                                wc_get_template_part('content', 'product');
                            }
                        }
                        echo '</div>';

                        // Pagination
                        $total_pages = wc_get_loop_prop('total_pages', 0);
                        if ($total_pages > 1) {
                            echo '<div class="products-pagination-wrapper">';
                            echo '<div class="products-pagination">';
                            $current_page = max(1, get_query_var('paged'));
                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active = ($i === $current_page) ? ' active' : '';
                                echo '<button class="products-pagination__btn' . $active . '" data-page="' . $i . '">' . $i . '</button>';
                            }
                            echo '</div></div>';
                        }
                    }else{
                        echo '<p class="products-grid__empty">' . __('محصولی یافت نشد.', 'piazhen') . '</p>';
                    }
                    ?>
                </div>

                <!-- e. SEO Description Box -->
                <?php if ($current_category && $current_category->description): ?>
                <section class="seo-section py-4 mt-3">
                    <div class="textBox">
                        <?= wpautop($current_category->description); ?>
                    </div>
                    <button class="showMore">
                        <?php _e('مشاهده بیشتر', 'piazhen'); ?>
                        <svg class="ms-2" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4 6L7.29289 9.29289C7.62623 9.62623 7.79289 9.79289 8 9.79289C8.20711 9.79289 8.37377 9.62623 8.70711 9.29289L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Variation Popup Modal (hidden by default) -->
    <div class="variation-modal-overlay" id="variation-modal" style="display:none;">
        <div class="variation-modal">
            <div class="variation-modal__inner" id="variation-modal-inner">
                <!-- AJAX content loads here -->
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
