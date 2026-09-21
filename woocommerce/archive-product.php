<?php
/**
 * WooCommerce Product Archive (shop, categories, brands)
 * Custom AJAX filters — no filter plugins.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Current queried term (product_cat or product_brand archive)
$current_term = get_queried_object();
$current_category = null;
if ($current_term && is_a($current_term, 'WP_Term') && $current_term->taxonomy === 'product_cat') {
    $current_category = $current_term;
}
$category_id = $current_category ? $current_category->term_id : 0;

// Filter params from the request (deep links)
$req_params = pzh_get_filter_params_from_request($_GET);

// Filter data scoped to the current category context
$filter_data = pzh_get_archive_filter_data($category_id);
$prices      = $filter_data['price_range'];

// Slider values (from GET if present, otherwise full range)
$val_min = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? intval($_GET['min_price']) : $prices['min'];
$val_max = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? intval($_GET['max_price']) : $prices['max'];

// Current page + per page (for pagination state)
$current_page = max(1, get_query_var('paged'));
$per_page     = intval($GLOBALS['wp_query']->query_vars['posts_per_page'] ?? 12);
?>
<main class="woo-archive">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="archive-breadcrumb py-3" aria-label="<?php _e('مسیر صفحه', 'piazhen'); ?>">
            <?php woocommerce_breadcrumb(array(
                'delimiter'   => ' <span class="breadcrumb-delimiter">/</span> ',
                'wrap_before' => '<div class="breadcrumb-trail">',
                'wrap_after'  => '</div>',
                'home'        => __('خانه', 'piazhen'),
            )); ?>
        </nav>

        <!-- Page Title -->
        <header class="archive-head pb-3">
            <?php if ($current_category): ?>
                <h1 class="archive-title"><?= esc_html($current_category->name); ?></h1>
            <?php elseif (is_shop()): ?>
                <h1 class="archive-title"><?php _e('فروشگاه', 'piazhen'); ?></h1>
            <?php elseif ($current_term && is_a($current_term, 'WP_Term')): ?>
                <h1 class="archive-title"><?= esc_html($current_term->name); ?></h1>
            <?php endif; ?>
        </header>

        <!-- a. Categories / Subcategories Carousel -->
        <section class="archive-categories pb-4">
            <?php
            // Children of the current category; siblings on a sub-category; top-level on shop root
            if ($current_category) {
                $categories = get_terms(array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'parent'     => $current_category->term_id,
                    'number'     => 15,
                ));
                if (empty($categories) || is_wp_error($categories)) {
                    // No children: show siblings (same parent level)
                    $categories = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'parent'     => $current_category->parent,
                        'number'     => 15,
                    ));
                }
            } else {
                $categories = get_terms(array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'parent'     => 0,
                    'number'     => 15,
                ));
            }

            if (!empty($categories) && !is_wp_error($categories)):
                $brands_cat = pzh_get_brands_category();
            ?>
                <div class="categories-swiper-wrapper position-relative">
                    <div class="swiper categories-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($categories as $cat):
                                // Skip the "برند ها" container category (brands have their own filter)
                                if ($brands_cat && intval($cat->term_id) === intval($brands_cat->term_id)) continue;
                                $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                                $image = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : wc_placeholder_img_src('thumbnail');
                            ?>
                                <div class="swiper-slide">
                                    <a href="<?= esc_url(get_term_link($cat)); ?>" class="category-card">
                                        <div class="category-card__image">
                                            <img src="<?= esc_url($image); ?>" alt="<?= esc_attr($cat->name); ?>" loading="lazy">
                                        </div>
                                        <span class="category-card__title"><?= esc_html($cat->name); ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="categories-prev swiper-nav-btn swiper-nav-btn--prev" aria-label="<?php _e('قبلی', 'piazhen'); ?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 4L6 8L10 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <button class="categories-next swiper-nav-btn swiper-nav-btn--next" aria-label="<?php _e('بعدی', 'piazhen'); ?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            <?php endif; ?>
        </section>

        <div class="row g-4 align-items-start">
            <!-- b. Sidebar with AJAX Filters -->
            <aside class="col-lg-3">
                <div class="archive-filters"
                     data-category-id="<?= esc_attr($category_id); ?>"
                     data-per-page="<?= esc_attr($per_page); ?>">
                    <div class="archive-filters__header">
                        <h3 class="archive-filters__title">
                            <i class="fa-solid fa-sliders"></i>
                            <?php _e('فیلترها', 'piazhen'); ?>
                        </h3>
                        <button type="button" class="clear-all-filters"><?php _e('حذف همه', 'piazhen'); ?></button>
                    </div>

                    <!-- Category Tree (links with counts) -->
                    <?php if (!empty($filter_data['tree']['items'])): ?>
                    <div class="filter-group filter-group--categories">
                        <h4 class="filter-group__title"><?php _e('دسته‌بندی کالاها', 'piazhen'); ?></h4>
                        <ul class="category-tree">
                            <?php foreach ($filter_data['tree']['items'] as $node): ?>
                                <li class="category-tree__item <?= $node['current'] ? 'current' : ''; ?>">
                                    <div class="category-tree__row">
                                        <?php if (!empty($node['children'])): ?>
                                            <button type="button" class="category-tree__toggle <?= ($filter_data['tree']['show_children_for'] == $node['id']) ? 'open' : ''; ?>" aria-label="<?php _e('باز کردن زیردسته‌ها', 'piazhen'); ?>">
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M4.5 3L7.5 6L4.5 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </button>
                                        <?php else: ?>
                                            <span class="category-tree__toggle-placeholder"></span>
                                        <?php endif; ?>
                                        <a href="<?= esc_url($node['link']); ?>" class="category-tree__link">
                                            <?= esc_html($node['name']); ?>
                                            <span class="filter-count"><?= pzh_fa_num($node['count']); ?></span>
                                        </a>
                                    </div>
                                    <?php if (!empty($node['children'])): ?>
                                        <ul class="category-tree__children <?= ($filter_data['tree']['show_children_for'] == $node['id']) ? 'open' : ''; ?>">
                                            <?php foreach ($node['children'] as $child): ?>
                                                <li class="category-tree__item <?= $child['current'] ? 'current' : ''; ?>">
                                                    <div class="category-tree__row">
                                                        <span class="category-tree__toggle-placeholder"></span>
                                                        <a href="<?= esc_url($child['link']); ?>" class="category-tree__link">
                                                            <?= esc_html($child['name']); ?>
                                                            <span class="filter-count"><?= pzh_fa_num($child['count']); ?></span>
                                                        </a>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Brand Filter -->
                    <?php if (!empty($filter_data['brands'])): ?>
                        <div class="filter-group">
                            <h4 class="filter-group__title"><?php _e('برند', 'piazhen'); ?></h4>
                            <div class="filter-group__items">
                                <?php foreach ($filter_data['brands'] as $brand): ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="brands[]" value="<?= $brand['id']; ?>"
                                            <?php checked(in_array($brand['id'], $req_params['brands'])); ?>>
                                        <span class="filter-checkbox__mark"></span>
                                        <span class="filter-checkbox__label">
                                            <?= esc_html($brand['name']); ?>
                                            <span class="filter-count"><?= pzh_fa_num($brand['count']); ?></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- In-Stock Only -->
                    <div class="filter-group filter-group--stock">
                        <label class="filter-switch">
                            <input type="checkbox" name="in_stock" value="1" <?php checked($req_params['in_stock']); ?>>
                            <span class="filter-switch__track"><span class="filter-switch__thumb"></span></span>
                            <span class="filter-switch__label"><?php _e('فقط کالاهای موجود', 'piazhen'); ?></span>
                        </label>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-group" id="price-filter-group">
                        <h4 class="filter-group__title"><?php _e('محدوده قیمت', 'piazhen'); ?></h4>
                        <div class="price-range">
                            <div class="price-range__inputs d-flex gap-2">
                                <input type="number" id="price-min" class="mainInput" placeholder="<?php _e('حداقل', 'piazhen'); ?>"
                                       value="<?= $val_min; ?>" min="<?= $prices['min']; ?>" max="<?= $prices['max']; ?>" step="1000">
                                <span class="price-range__separator">-</span>
                                <input type="number" id="price-max" class="mainInput" placeholder="<?php _e('حداکثر', 'piazhen'); ?>"
                                       value="<?= $val_max; ?>" min="<?= $prices['min']; ?>" max="<?= $prices['max']; ?>" step="1000">
                            </div>
                            <div class="price-range__slider-wrapper">
                                <input type="range" id="price-range-min" class="price-range__slider price-range__slider--min"
                                       min="<?= $prices['min']; ?>" max="<?= $prices['max']; ?>" step="1000"
                                       value="<?= $val_min; ?>">
                                <input type="range" id="price-range-max" class="price-range__slider price-range__slider--max"
                                       min="<?= $prices['min']; ?>" max="<?= $prices['max']; ?>" step="1000"
                                       value="<?= $val_max; ?>">
                            </div>
                            <div class="price-range__display"></div>
                        </div>
                    </div>

                    <!-- Dynamic Attribute Filters (scoped to this category) -->
                    <?php foreach ($filter_data['attributes'] as $attribute): ?>
                        <div class="filter-group">
                            <h4 class="filter-group__title"><?= esc_html($attribute['label']); ?></h4>
                            <div class="filter-group__items">
                                <?php foreach ($attribute['terms'] as $term): ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="attr_<?= esc_attr($attribute['taxonomy']); ?>[]"
                                               value="<?= esc_attr($term['slug']); ?>"
                                            <?php checked(isset($req_params['attributes'][$attribute['taxonomy']]) && in_array($term['slug'], $req_params['attributes'][$attribute['taxonomy']])); ?>>
                                        <span class="filter-checkbox__mark"></span>
                                        <span class="filter-checkbox__label">
                                            <?= esc_html($term['name']); ?>
                                            <span class="filter-count"><?= pzh_fa_num($term['count']); ?></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Filter Actions -->
                    <div class="filter-group filter-actions d-flex gap-2">
                        <button type="button" class="apply-filters-btn mainBtn mainBtn--yellow small w-100">
                            <?php _e('اعمال فیلتر', 'piazhen'); ?>
                        </button>
                        <button type="button" class="reset-filters-btn mainBtn small w-100">
                            <?php _e('حذف فیلترها', 'piazhen'); ?>
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Products Grid Area -->
            <div class="col-lg-9">
                <!-- c. Toolbar: Sort Pills + Total Count -->
                <div class="archive-toolbar d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="products-count-wrapper">
                        <span class="products-count"><?= pzh_fa_num($GLOBALS['wp_query']->found_posts); ?></span>
                        <span><?php _e('کالا', 'piazhen'); ?></span>
                    </div>

                    <div class="sort-options d-flex align-items-center gap-2 flex-wrap">
                        <?php
                        $sort_options = array(
                            'popularity' => __('پرفروش‌ترین', 'piazhen'),
                            'newest'     => __('جدیدترین', 'piazhen'),
                            'price-asc'  => __('ارزان‌ترین', 'piazhen'),
                            'price-desc' => __('گران‌ترین', 'piazhen'),
                            'discount'   => __('بیشترین تخفیف', 'piazhen'),
                        );
                        foreach ($sort_options as $value => $label): ?>
                            <label class="sort-radio">
                                <input type="radio" name="sort" value="<?= $value; ?>"
                                    <?php checked($req_params['sort'], $value); ?>>
                                <span><?= $label; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mobile filter toggle -->
                    <button type="button" class="mobile-filters-toggle d-lg-none">
                        <i class="fa-solid fa-sliders"></i>
                        <?php _e('فیلترها', 'piazhen'); ?>
                    </button>
                </div>

                <!-- d. Products Grid + Pagination -->
                <div class="products-grid-wrapper">
                    <?php
                    echo pzh_render_products_grid($GLOBALS['wp_query'], $current_page, $per_page);
                    ?>
                </div>
            </div>
        </div>

        <!-- e. SEO Description Box -->
        <?php
        $seo_content = '';
        $seo_title   = '';
        if ($current_category && $current_category->description) {
            $seo_content = $current_category->description;
            $seo_title   = sprintf(__('درباره دسته‌بندی %s', 'piazhen'), $current_category->name);
        } elseif (is_shop()) {
            $shop_page_id = wc_get_page_id('shop');
            $seo_content  = $shop_page_id ? trim(get_post_field('post_content', $shop_page_id)) : '';
            if (!$seo_content) {
                $seo_content = __('فروشگاه اینترنتی پیاژن عرضه‌کننده انواع عطر و ادکلن اورجینال، لوازم آرایشی و محصولات بهداشتی با ضمانت اصالت کالا و بهترین قیمت است. تمامی محصولات پیاژن دارای گارانتی اصالت بوده و با ارسال سریع به سراسر کشور ارسال می‌شوند.', 'piazhen');
            }
            $seo_title = __('درباره فروشگاه پیاژن', 'piazhen');
        }
        ?>
        <?php if ($seo_content): ?>
        <section class="seo-section py-4">
            <h3 class="seo-section__title"><?= esc_html($seo_title); ?></h3>
            <div class="textBox">
                <?= wpautop($seo_content); ?>
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

</main>

<?php get_footer(); ?>
