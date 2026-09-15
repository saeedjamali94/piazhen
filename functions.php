<?php
/**
 * Piazhen theme functions and definitions
 *
 * @package Piazhen
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('PZH_THEME_DIR', get_template_directory());
define('PZH_THEME_URI', get_template_directory_uri());
define('SPRITE_URL', PZH_THEME_URI . '/assets/images/sprite.svg');
define('SITE_URL', get_site_url());

// ============================================================================
// Theme Setup
// ============================================================================
function piazhen_theme_setup() {
    // Core theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Image sizes
    add_image_size('pzh_product_card', 300, 300, true);
    add_image_size('pzh_product_thumb', 150, 150, true);
    add_image_size('pzh_hero_banner', 800, 500, true);
    add_image_size('pzh_hero_small', 400, 250, true);

    // Navigation menus
    register_nav_menus(array(
        'primary' => __('منوی اصلی', 'piazhen'),
        'footer'  => __('منوی فوتر', 'piazhen'),
    ));
}
add_action('after_setup_theme', 'piazhen_theme_setup');

// ============================================================================
// Enqueue Scripts & Styles
// ============================================================================
function piazhen_scripts() {
    $version = wp_get_theme()->get('Version');

    // Styles
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.1.0');
    wp_enqueue_style('piazhen-font-awesome', PZH_THEME_URI . '/assets/font-icons/css/all.min.css', array(), '7.3.1');
    wp_enqueue_style('piazhen-main-style', PZH_THEME_URI . '/assets/css/styles.css', array('swiper-css'), $version);

    // Scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.1.0', true);
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.8', true);
    wp_enqueue_script('piazhen-js', PZH_THEME_URI . '/assets/js/app.js', array('jquery', 'swiper-js'), $version, true);

    // Checkout map (Neshan SDK with API key, or plain Leaflet + OSM fallback)
    if (is_checkout()) {
        if (pzh_neshan_api_key()) {
            // Official Neshan Leaflet SDK (Persian map tiles)
            wp_enqueue_style('leaflet-css', 'https://static.neshan.org/sdk/leaflet/v1.9.4/neshan-sdk/v1.0.8/index.css', array(), '1.0.8');
            wp_enqueue_script('leaflet-js', 'https://static.neshan.org/sdk/leaflet/v1.9.4/neshan-sdk/v1.0.8/index.js', array('jquery'), '1.0.8', true);
        } else {
            wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
            wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array('jquery'), '1.9.4', true);
        }
    }

    wp_localize_script('piazhen-js', 'pzh_options', array(
        'theme_url'  => PZH_THEME_URI,
        'ajax_url'   => admin_url('admin-ajax.php'),
        'sprite_url' => SPRITE_URL,
        'site_url'   => SITE_URL,
        'nonce'      => wp_create_nonce('pzh_ajax_nonce'),
        'is_rtl'     => is_rtl(),
        'neshan_key' => pzh_neshan_api_key(),
        'map_center' => apply_filters('pzh_map_center', array(35.7219, 51.3347)), // Tehran
        'map_zoom'   => apply_filters('pzh_map_zoom', 12),
    ));
}
add_action('wp_enqueue_scripts', 'piazhen_scripts');

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Get dashboard URL (WooCommerce my-account or wp-admin)
 */
function pzhDashboardUrl() {
    if (class_exists('WooCommerce')) {
        return wc_get_page_permalink('myaccount');
    }
    return SITE_URL . '/my-account';
}

/**
 * Get product card HTML
 */
function pzh_get_product_card_html($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) return '';

    ob_start();
    ?>
    <div class="product-card" data-product-id="<?php echo esc_attr($product_id); ?>">
        <div class="product-card__image">
            <a href="<?php echo get_permalink($product_id); ?>">
                <?php echo $product->get_image('pzh_product_card'); ?>
            </a>
            <?php if ($product->is_on_sale()): ?>
                <span class="product-card__badge product-card__badge--sale">٪<?php echo pzh_get_discount_percentage($product); ?></span>
            <?php endif; ?>
            <button class="product-card__favorite <?php echo pzh_is_favorited($product_id) ? 'active' : ''; ?>"
                    data-product-id="<?php echo esc_attr($product_id); ?>"
                    aria-label="<?php _e('افزودن به علاقه‌مندی', 'piazhen'); ?>">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
        </div>
        <div class="product-card__details">
            <h3 class="product-card__title">
                <a href="<?php echo get_permalink($product_id); ?>"><?php echo $product->get_name(); ?></a>
            </h3>
            <div class="product-card__price">
                <?php echo $product->get_price_html(); ?>
            </div>
            <?php if ($product->is_type('variable')): ?>
                <button class="product-card__add-to-cart mainBtn small product-card__add-to-cart--variable"
                        data-product-id="<?php echo esc_attr($product_id); ?>"
                        data-has-variations="1">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <?php _e('انتخاب و خرید', 'piazhen'); ?>
                </button>
            <?php else: ?>
                <button class="product-card__add-to-cart mainBtn small"
                        data-product-id="<?php echo esc_attr($product_id); ?>">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <?php _e('افزودن به سبد', 'piazhen'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Check if product is favorited by current user
 */
function pzh_is_favorited($product_id) {
    if (is_user_logged_in()) {
        $favorites = get_user_meta(get_current_user_id(), 'pzh_favorites', true);
        return is_array($favorites) && in_array($product_id, $favorites);
    }
    // Check cookie for guests
    return isset($_COOKIE['pzh_favorites']) && in_array($product_id, json_decode(stripslashes($_COOKIE['pzh_favorites']), true));
}

/**
 * Get discount percentage for a product
 */
function pzh_get_discount_percentage($product) {
    if (!$product->is_on_sale()) return 0;

    $regular_price = $product->get_regular_price();
    $sale_price    = $product->get_sale_price();

    if ($regular_price > 0) {
        return round((($regular_price - $sale_price) / $regular_price) * 100);
    }
    return 0;
}

/**
 * Get actual min/max prices for products (optionally scoped to a category)
 * Returns array('min' => int, 'max' => int) in Tomans (rounded up/down to nearest 1000)
 */
function pzh_get_category_price_range($category_id = 0) {
    global $wpdb;

    $join  = '';
    $where = "WHERE post_type = 'product' AND post_status = 'publish'";

    if ($category_id) {
        $join  = "INNER JOIN {$wpdb->term_relationships} AS tr ON ({$wpdb->posts}.ID = tr.object_id)";
        $join .= " INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)";
        $where .= $wpdb->prepare(" AND tt.term_id = %d AND tt.taxonomy = 'product_cat'", $category_id);
    }

    $sql = "SELECT MIN(CAST(pm_min.meta_value AS UNSIGNED)) as min_price,
                   MAX(CAST(pm_max.meta_value AS UNSIGNED)) as max_price
            FROM {$wpdb->posts}
            {$join}
            INNER JOIN {$wpdb->postmeta} AS pm_min ON ({$wpdb->posts}.ID = pm_min.post_id AND pm_min.meta_key = '_price')
            INNER JOIN {$wpdb->postmeta} AS pm_max ON ({$wpdb->posts}.ID = pm_max.post_id AND pm_max.meta_key = '_price')
            {$where}";

    $result = $wpdb->get_row($sql);

    $min = $result && $result->min_price ? intval($result->min_price) : 0;
    $max = $result && $result->max_price ? intval($result->max_price) : 50000000;

    // Round down min to nearest 1000, round up max to nearest 1000
    $min = floor($min / 1000) * 1000;
    $max = ceil($max / 1000) * 1000;

    // Ensure a sensible range
    if ($max <= $min) $max = $min + 1000000;

    return array('min' => $min, 'max' => $max);
}

/**
 * Get the "برند ها" product category (brands are stored as its children)
 */
function pzh_get_brands_category() {
    $cat = get_term_by('name', 'برند ها', 'product_cat');
    if (!$cat) {
        $cat = get_term_by('slug', 'brands', 'product_cat');
    }
    return $cat ?: null;
}

/**
 * Get brand terms. Prefers the product_brand taxonomy; falls back to the
 * children of the "برند ها" product category (how this site stores brands).
 */
function pzh_get_brand_terms($hide_empty = true) {
    if (taxonomy_exists('product_brand')) {
        $terms = get_terms(array('taxonomy' => 'product_brand', 'hide_empty' => $hide_empty));
        if (!empty($terms) && !is_wp_error($terms)) return $terms;
    }

    $brands_cat = pzh_get_brands_category();
    if ($brands_cat) {
        $terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => $hide_empty,
            'parent'     => $brands_cat->term_id,
        ));
        if (!empty($terms) && !is_wp_error($terms)) return $terms;
    }

    return array();
}

/**
 * Get the taxonomy brand terms belong to: 'product_brand' if populated,
 * otherwise 'product_cat' (برند ها children).
 */
function pzh_get_brand_taxonomy() {
    if (taxonomy_exists('product_brand')) {
        $terms = get_terms(array('taxonomy' => 'product_brand', 'hide_empty' => true, 'number' => 1));
        if (!empty($terms) && !is_wp_error($terms)) return 'product_brand';
    }
    return 'product_cat';
}

/**
 * Get site brand logos
 */
function pzh_get_brands() {
    $brands = array();
    foreach (pzh_get_brand_terms() as $term) {
        $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
        $brands[] = array(
            'name'  => $term->name,
            'image' => $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '',
            'link'  => get_term_link($term),
        );
        if (count($brands) >= 8) break;
    }
    return $brands;
}

/**
 * Hero banners data (title / image / link)
 * Grid: one large banner (col-md-8) + two stacked cards (col-md-4).
 * Editable via the 'pzh_hero_banners' filter.
 */
function pzh_hero_banners() {
    $shop_url = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : SITE_URL . '/shop';

    $banners = array(
        // Large panel (right side)
        'main' => array(
            'badge'    => __('جشنواره پاییزی پی‌آژن', 'piazhen'),
            'title'    => __('عطر و ادکلن اورجینال', 'piazhen'),
            'subtitle' => __('با ضمانت اصالت کالا و ارسال سریع به سراسر کشور', 'piazhen'),
            'image'    => PZH_THEME_URI . '/assets/images/cover1.png',
            'link'     => $shop_url,
            'cta'      => __('خرید کنید', 'piazhen'),
        ),
        // Two cards on the left column
        'side_cards' => array(
            array('title' => __('لوازم آرایشی اورجینال', 'piazhen'), 'image' => PZH_THEME_URI . '/assets/images/image.png',  'link' => $shop_url),
            array('title' => __('جشنواره تخفیف‌های ویژه', 'piazhen'), 'image' => PZH_THEME_URI . '/assets/images/card2.png', 'link' => $shop_url),
        ),
    );

    return apply_filters('pzh_hero_banners', $banners);
}

/**
 * Get most selling products
 */
function pzh_get_most_selling_products($limit = 15) {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $limit,
        'meta_key'       => 'total_sales',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    );
    return new WP_Query($args);
}

/**
 * Get newest products
 */
function pzh_get_newest_products($limit = 8) {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    );
    return new WP_Query($args);
}

/**
 * Get on-sale products
 */
function pzh_get_on_sale_products($limit = 8) {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_sale_price',
                'value'   => '',
                'compare' => '!=',
            ),
            array(
                'key'     => '_min_variation_sale_price',
                'value'   => '',
                'compare' => '!=',
            ),
        ),
    );
    return new WP_Query($args);
}

// ============================================================================
// Archive Filters (custom AJAX, no plugins)
// ============================================================================

/**
 * Convert latin digits to Persian digits
 */
function pzh_fa_num($num) {
    return strtr((string) $num, array(
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ));
}

/**
 * Read filter params from $_GET (deep links) or $_POST (AJAX)
 * Returns a normalized params array.
 */
function pzh_get_filter_params_from_request($source = null) {
    $source = $source ?: $_GET;

    $attributes = array();
    foreach ($source as $key => $val) {
        if (strpos($key, 'attr_') === 0 && is_array($val)) {
            $tax = substr($key, 5);
            if (taxonomy_exists($tax) && !empty($val)) {
                $attributes[$tax] = array_map('sanitize_title', (array) $val);
            }
        }
    }

    return array(
        'brands'     => isset($source['brands']) && is_array($source['brands']) ? array_map('intval', $source['brands']) : array(),
        'categories' => isset($source['categories']) && is_array($source['categories']) ? array_map('intval', $source['categories']) : array(),
        'attributes' => $attributes,
        'min_price'  => isset($source['min_price']) && $source['min_price'] !== '' ? floatval($source['min_price']) : 0,
        'max_price'  => isset($source['max_price']) && $source['max_price'] !== '' ? floatval($source['max_price']) : 0,
        'in_stock'   => !empty($source['in_stock']),
        'sort'       => isset($source['sort']) ? sanitize_key($source['sort']) : 'popularity',
    );
}

/**
 * Build WP_Query args additions for a filter params array.
 * $base_tax_query / $base_meta_query preserve the queried term's own clauses.
 */
function pzh_get_product_filter_args($params, $base_tax_query = array(), $base_meta_query = array()) {
    $out = array();

    // --- Taxonomies (all AND-combined; terms inside a taxonomy are OR) ---
    $clauses = array();
    if (!empty($base_tax_query)) {
        foreach ($base_tax_query as $k => $v) {
            if ($k === 'relation') continue;
            $clauses[] = $v;
        }
    }

    if (!empty($params['brands'])) {
        $clauses[] = array(
            'taxonomy' => pzh_get_brand_taxonomy(),
            'field'    => 'term_id',
            'terms'    => $params['brands'],
            'operator' => 'IN',
        );
    }
    if (!empty($params['categories'])) {
        $clauses[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $params['categories'],
            'operator' => 'IN',
        );
    }
    if (!empty($params['attributes'])) {
        foreach ($params['attributes'] as $tax => $slugs) {
            if (!taxonomy_exists($tax) || empty($slugs)) continue;
            $clauses[] = array(
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => $slugs,
                'operator' => 'IN',
            );
        }
    }

    if (count($clauses) > 1) {
        $out['tax_query'] = array_merge(array('relation' => 'AND'), $clauses);
    } elseif (!empty($clauses)) {
        // Always wrap clauses in an array — a bare clause is dropped by WP_Tax_Query
        $out['tax_query'] = $clauses;
    }

    // --- Meta (price range, in-stock only) ---
    $meta_clauses = array();
    if (!empty($base_meta_query)) {
        foreach ($base_meta_query as $k => $v) {
            if ($k === 'relation') continue;
            $meta_clauses[] = $v;
        }
    }

    if ($params['min_price'] > 0 || $params['max_price'] > 0) {
        $meta_clauses[] = array(
            'key'     => '_price',
            'value'   => array($params['min_price'] ?: 0, $params['max_price'] ?: 999999999999),
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        );
    }
    if ($params['in_stock']) {
        $meta_clauses[] = array(
            'key'   => '_stock_status',
            'value' => 'instock',
        );
    }

    if (count($meta_clauses) > 1) {
        $out['meta_query'] = array_merge(array('relation' => 'AND'), $meta_clauses);
    } elseif (!empty($meta_clauses)) {
        $out['meta_query'] = $meta_clauses;
    }

    // --- Sort ---
    switch ($params['sort']) {
        case 'price-asc':
            $out['orderby']  = 'meta_value_num';
            $out['meta_key'] = '_price';
            $out['order']    = 'ASC';
            break;
        case 'price-desc':
            $out['orderby']  = 'meta_value_num';
            $out['meta_key'] = '_price';
            $out['order']    = 'DESC';
            break;
        case 'newest':
            $out['orderby'] = 'date';
            $out['order']   = 'DESC';
            break;
        case 'discount':
            // Sorted by discount percentage via the pzh_discount_order_clauses filter
            $out['orderby'] = 'pzh_discount';
            $out['order']   = 'DESC';
            break;
        default: // popularity (most sells)
            $out['meta_key'] = 'total_sales';
            $out['orderby']  = array('meta_value_num' => 'DESC', 'date' => 'DESC');
            $out['order']    = 'DESC';
    }

    return $out;
}

/**
 * Apply archive filter params from the request to the main product query
 * (server-side render / deep links with ?brands[]=...&min_price=... etc.)
 */
function pzh_product_archive_query($query) {
    if (is_admin() || !$query->is_main_query()) return;
    if (!(is_shop() || is_product_taxonomy())) return;

    $params = pzh_get_filter_params_from_request($_GET);
    $args   = pzh_get_product_filter_args($params, $query->get('tax_query'), $query->get('meta_query'));

    foreach ($args as $key => $value) {
        $query->set($key, $value);
    }
}
add_action('pre_get_posts', 'pzh_product_archive_query', 20);

/**
 * Order products by discount percentage (regular - sale) / regular, DESC
 */
function pzh_discount_order_clauses($clauses, $query) {
    if ($query->get('orderby') !== 'pzh_discount') return $clauses;

    global $wpdb;
    $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS pzh_reg ON ({$wpdb->posts}.ID = pzh_reg.post_id AND pzh_reg.meta_key = '_regular_price')";
    $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS pzh_sale ON ({$wpdb->posts}.ID = pzh_sale.post_id AND pzh_sale.meta_key = '_sale_price')";
    $clauses['orderby'] = "( ( CAST(COALESCE(pzh_reg.meta_value,'0') AS DECIMAL(14,2)) - CAST(COALESCE(pzh_sale.meta_value,'0') AS DECIMAL(14,2)) ) / NULLIF(CAST(COALESCE(pzh_reg.meta_value,'0') AS DECIMAL(14,2)),0) ) DESC, {$wpdb->posts}.post_date DESC";

    return $clauses;
}
add_filter('posts_clauses', 'pzh_discount_order_clauses', 10, 2);

/**
 * Render the archive pagination (prev / numbers / next)
 */
function pzh_render_pagination($page, $total_pages) {
    if ($total_pages <= 1) return;

    $page = max(1, intval($page));
    echo '<nav class="products-pagination" aria-label="' . esc_attr__('صفحه‌بندی', 'piazhen') . '">';

    // Prev
    if ($page > 1) {
        echo '<button class="products-pagination__btn products-pagination__btn--arrow" data-page="' . ($page - 1) . '" aria-label="' . esc_attr__('صفحه قبل', 'piazhen') . '"><i class="fa-solid fa-angle-right"></i></button>';
    } else {
        echo '<span class="products-pagination__btn products-pagination__btn--arrow disabled" aria-hidden="true"><i class="fa-solid fa-angle-right"></i></span>';
    }

    // Numbers with ellipsis
    $last_printed = 0;
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i === 1 || $i === $total_pages || abs($i - $page) <= 2) {
            $active = ($i === $page) ? ' active' : '';
            echo '<button class="products-pagination__btn' . $active . '" data-page="' . $i . '">' . pzh_fa_num($i) . '</button>';
            $last_printed = $i;
        } elseif ($last_printed !== $i - 1 && $last_printed !== -1) {
            echo '<span class="products-pagination__dots">…</span>';
            $last_printed = -1;
        }
    }

    // Next
    if ($page < $total_pages) {
        echo '<button class="products-pagination__btn products-pagination__btn--arrow" data-page="' . ($page + 1) . '" aria-label="' . esc_attr__('صفحه بعد', 'piazhen') . '"><i class="fa-solid fa-angle-left"></i></button>';
    } else {
        echo '<span class="products-pagination__btn products-pagination__btn--arrow disabled" aria-hidden="true"><i class="fa-solid fa-angle-left"></i></span>';
    }

    echo '</nav>';
}

/**
 * Render the products grid + pagination for a query.
 * Shared by the initial server render and the AJAX filter handler.
 */
function pzh_render_products_grid($query, $page, $per_page) {
    $total       = intval($query->found_posts);
    $total_pages = $per_page ? intval(ceil($total / $per_page)) : 0;

    ob_start();

    if ($query->have_posts()) {
        echo '<div class="products-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            echo pzh_get_product_card_html(get_the_ID());
        }
        echo '</div>';

        pzh_render_pagination($page, $total_pages);
    } else {
        echo '<div class="products-grid__empty">';
        echo '<p>' . __('محصولی با این مشخصات پیدا نشد.', 'piazhen') . '</p>';
        echo '<button type="button" class="reset-filters-btn mainBtn mainBtn--yellow small">' . __('حذف فیلترها', 'piazhen') . '</button>';
        echo '</div>';
    }

    return ob_get_clean();
}

/**
 * Get filter data for the archive sidebar, scoped to a category context:
 * brands, dynamic attributes (with per-context counts), price range, category tree.
 */
function pzh_get_archive_filter_data($category_id = 0) {
    // Product IDs inside the current archive context (used for term counts)
    $product_args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    );
    if ($category_id) {
        $product_args['tax_query'] = array(
            array(
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => $category_id,
                'include_children' => true,
            ),
        );
    }
    $product_ids = get_posts($product_args);

    // Fetch used terms of the brand taxonomy + all attribute taxonomies in ONE query
    $taxonomies = array(pzh_get_brand_taxonomy());
    foreach (wc_get_attribute_taxonomies() as $attribute) {
        $tax = wc_attribute_taxonomy_name($attribute->attribute_name);
        if (taxonomy_exists($tax)) $taxonomies[] = $tax;
    }

    $counts_by_tax = array();
    if (!empty($product_ids) && !empty($taxonomies)) {
        $object_terms = wp_get_object_terms($product_ids, array_unique($taxonomies), array('fields' => 'all_with_object_id'));
        if (!is_wp_error($object_terms)) {
            foreach ($object_terms as $t) {
                if (!isset($counts_by_tax[$t->taxonomy])) $counts_by_tax[$t->taxonomy] = array();
                if (!isset($counts_by_tax[$t->taxonomy][$t->term_id])) {
                    $counts_by_tax[$t->taxonomy][$t->term_id] = array('name' => $t->name, 'slug' => $t->slug, 'count' => 0);
                }
                $counts_by_tax[$t->taxonomy][$t->term_id]['count']++;
            }
        }
    }

    // Brands (the brand taxonomy terms actually used in this context)
    $brands = array();
    $brand_tax = pzh_get_brand_taxonomy();
    foreach (pzh_get_brand_terms() as $term) {
        if (empty($counts_by_tax[$brand_tax][$term->term_id])) continue;
        $brands[] = array(
            'id'    => $term->term_id,
            'name'  => $term->name,
            'slug'  => $term->slug,
            'count' => $counts_by_tax[$brand_tax][$term->term_id]['count'],
        );
    }
    usort($brands, function ($a, $b) { return strcmp($a['name'], $b['name']); });

    // Dynamic attributes (only those with terms used in this context)
    $attributes = array();
    foreach (wc_get_attribute_taxonomies() as $attribute) {
        $tax = wc_attribute_taxonomy_name($attribute->attribute_name);
        if (!isset($counts_by_tax[$tax]) || empty($counts_by_tax[$tax])) continue;

        $terms = array();
        foreach ($counts_by_tax[$tax] as $term_id => $data) {
            $terms[] = array(
                'id'    => $term_id,
                'name'  => $data['name'],
                'slug'  => $data['slug'],
                'count' => $data['count'],
            );
        }
        usort($terms, function ($a, $b) { return strcmp($a['name'], $b['name']); });

        $attributes[] = array(
            'taxonomy' => $tax,
            'label'    => wc_attribute_label($tax),
            'terms'    => $terms,
        );
    }

    return array(
        'brands'     => $brands,
        'attributes' => $attributes,
        'price_range' => pzh_get_category_price_range($category_id),
        'tree'       => pzh_get_sidebar_category_tree($category_id),
    );
}

/**
 * Category tree for the sidebar filter (links with counts, expandable children)
 */
function pzh_get_sidebar_category_tree($category_id = 0) {
    $current  = null;
    $show_children_for = 0;
    $brands_cat = pzh_get_brands_category();
    $brands_cat_id = $brands_cat ? intval($brands_cat->term_id) : 0;

    if ($category_id) {
        $current = get_term($category_id, 'product_cat');
        if (is_wp_error($current) || !$current) {
            $current = null;
            $category_id = 0;
        } else {
            $show_children_for = $current->term_id;
        }
    }

    if ($current && $current->parent) {
        // Category page: show the siblings level (parent's children)
        $level_cats = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => $current->parent,
        ));
    } elseif ($current) {
        // Top-level category page: show just the current top-level category
        $level_cats = array($current);
    } else {
        // Shop root: show all top-level categories
        $level_cats = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
        ));
    }

    if (is_wp_error($level_cats)) $level_cats = array();

    $tree = array();
    foreach ($level_cats as $cat) {
        // Skip the "برند ها" container category (brands have their own filter group)
        if ($brands_cat_id && intval($cat->term_id) === $brands_cat_id) continue;

        $children = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => $cat->term_id,
        ));
        if (is_wp_error($children)) $children = array();

        $tree[] = array(
            'id'      => $cat->term_id,
            'name'    => $cat->name,
            'link'    => get_term_link($cat),
            'count'   => $cat->count,
            'current' => ($current && $current->term_id === $cat->term_id),
            'children' => array_map(function ($child) use ($current) {
                return array(
                    'id'      => $child->term_id,
                    'name'    => $child->name,
                    'link'    => get_term_link($child),
                    'count'   => $child->count,
                    'current' => ($current && $current->term_id === $child->term_id),
                );
            }, $children),
        );
    }

    return array('items' => $tree, 'show_children_for' => $show_children_for);
}

// ============================================================================
// AJAX Handlers
// ============================================================================

/**
 * AJAX Product Search
 */
function pzh_ajax_search() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

    if (strlen($search_term) < 2) {
        wp_send_json_success(array('html' => '', 'count' => 0));
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 8,
        's'              => $search_term,
        'post_status'    => 'publish',
    );

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        echo '<ul class="search-results__list">';
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());
            ?>
            <li class="search-results__item">
                <a href="<?php the_permalink(); ?>">
                    <?php echo $product->get_image('pzh_product_thumb'); ?>
                    <div class="search-results__info">
                        <span class="search-results__name"><?php echo $product->get_name(); ?></span>
                        <span class="search-results__price"><?php echo $product->get_price_html(); ?></span>
                    </div>
                </a>
            </li>
            <?php
        }
        echo '</ul>';
        echo '<a href="' . SITE_URL . '/?s=' . urlencode($search_term) . '&post_type=product" class="search-results__all">';
        printf(__('مشاهده همه نتایج (%d)', 'piazhen'), $query->found_posts);
        echo '</a>';
    } else {
        echo '<p class="search-results__empty">' . __('محصولی یافت نشد.', 'piazhen') . '</p>';
    }

    wp_reset_postdata();
    $html = ob_get_clean();

    wp_send_json_success(array(
        'html'  => $html,
        'count' => $query->found_posts,
    ));
}
add_action('wp_ajax_pzh_ajax_search', 'pzh_ajax_search');
add_action('wp_ajax_nopriv_pzh_ajax_search', 'pzh_ajax_search');

/**
 * AJAX Get Cart Data (count + mini-cart HTML)
 */
function pzh_get_cart_data() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_total = WC()->cart->get_cart_total();

    ob_start();
    ?>
    <div class="mini-cart">
        <?php if ($cart_count > 0): ?>
            <ul class="mini-cart__items">
                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
                    $_product = $cart_item['data'];
                    $product_id = $cart_item['product_id'];
                    ?>
                    <li class="mini-cart__item">
                        <div class="mini-cart__item-image">
                            <?php echo $_product->get_image('pzh_product_thumb'); ?>
                        </div>
                        <div class="mini-cart__item-info">
                            <span class="mini-cart__item-name"><?php echo $_product->get_name(); ?></span>
                            <span class="mini-cart__item-qty"><?php echo $cart_item['quantity']; ?> × <?php echo wc_price($_product->get_price()); ?></span>
                            <?php if (!empty($cart_item['variation'])): ?>
                                <span class="mini-cart__item-variation">
                                    <?php foreach ($cart_item['variation'] as $key => $value): ?>
                                        <?php echo esc_html(wc_attribute_label(str_replace('attribute_', '', $key))); ?>: <?php echo esc_html($value); ?>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <button class="mini-cart__remove" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="mini-cart__footer">
                <div class="mini-cart__total">
                    <span><?php _e('جمع کل:', 'piazhen'); ?></span>
                    <span><?php echo $cart_total; ?></span>
                </div>
                <?php if (WC()->cart->get_cart_discount_total() > 0): ?>
                    <div class="mini-cart__discount">
                        <span><?php _e('تخفیف:', 'piazhen'); ?></span>
                        <span><?php echo wc_price(WC()->cart->get_cart_discount_total()); ?></span>
                    </div>
                <?php endif; ?>
                <a href="<?php echo wc_get_cart_url(); ?>" class="mini-cart__cart-btn mainBtn small">
                    <?php _e('مشاهده سبد خرید', 'piazhen'); ?>
                </a>
                <a href="<?php echo wc_get_checkout_url(); ?>" class="mini-cart__checkout-btn mainBtn small">
                    <?php _e('تسویه حساب', 'piazhen'); ?>
                </a>
            </div>
        <?php else: ?>
            <p class="mini-cart__empty"><?php _e('سبد خرید خالی است.', 'piazhen'); ?></p>
        <?php endif; ?>
    </div>
    <?php
    $html = ob_get_clean();

    wp_send_json_success(array(
        'count' => $cart_count,
        'html'  => $html,
        'total' => $cart_total,
    ));
}
add_action('wp_ajax_pzh_get_cart_data', 'pzh_get_cart_data');
add_action('wp_ajax_nopriv_pzh_get_cart_data', 'pzh_get_cart_data');

/**
 * AJAX Add to Cart
 */
function pzh_add_to_cart() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $product_id   = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity     = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
    $variation    = isset($_POST['variation']) ? $_POST['variation'] : array();

    // If variation_id is set, use it as the product to add
    $add_id = $variation_id ? $variation_id : $product_id;

    if (!$add_id) {
        wp_send_json_error(array('message' => __('محصول نامعتبر است.', 'piazhen')));
    }

    $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);

    if ($cart_item_key) {
        // Get updated cart data
        ob_start();
        ?>
        <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
        <?php
        $cart_badge = ob_get_clean();

        wp_send_json_success(array(
            'message'    => __('محصول به سبد خرید اضافه شد.', 'piazhen'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_badge' => $cart_badge,
        ));
    } else {
        wp_send_json_error(array('message' => __('خطا در افزودن به سبد خرید.', 'piazhen')));
    }
}
add_action('wp_ajax_pzh_add_to_cart', 'pzh_add_to_cart');
add_action('wp_ajax_nopriv_pzh_add_to_cart', 'pzh_add_to_cart');

/**
 * AJAX Get Variation Popup HTML
 */
function pzh_get_variation_popup() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    if (!$product_id) {
        wp_send_json_error(array('message' => __('محصول نامعتبر است.', 'piazhen')));
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->is_type('variable')) {
        wp_send_json_error(array('message' => __('این محصول متغیر نیست.', 'piazhen')));
    }

    ob_start();
    ?>
    <div class="variation-popup" id="variation-popup-content">
        <button class="variation-popup__close" type="button">&times;</button>

        <div class="variation-popup__header">
            <div class="variation-popup__image">
                <?php echo $product->get_image('pzh_product_card'); ?>
            </div>
            <div class="variation-popup__info">
                <h3 class="variation-popup__title"><?php echo $product->get_name(); ?></h3>
                <div class="variation-popup__price"><?php echo $product->get_price_html(); ?></div>
            </div>
        </div>

        <div class="variation-popup__form">
            <?php
            // Get available variations as JSON for WooCommerce variation form
            $available_variations = $product->get_available_variations();
            $attributes = $product->get_variation_attributes();
            ?>

            <?php if (!empty($attributes)): ?>
                <div class="variation-fields">
                    <?php foreach ($attributes as $attribute_name => $options): ?>
                        <?php
                        $selected = isset($_REQUEST['attribute_' . sanitize_title($attribute_name)])
                            ? wc_clean(stripslashes($_REQUEST['attribute_' . sanitize_title($attribute_name)]))
                            : $product->get_variation_default_attribute($attribute_name);
                        ?>
                        <div class="variation-field">
                            <label class="variation-field__label">
                                <?php echo wc_attribute_label($attribute_name); ?>
                            </label>
                            <div class="variation-field__options">
                                <select class="variation-select"
                                        data-attribute_name="<?php echo esc_attr(wc_variation_attribute_name($attribute_name)); ?>"
                                        name="<?php echo esc_attr(wc_variation_attribute_name($attribute_name)); ?>">
                                    <option value=""><?php echo esc_html(sprintf(__('انتخاب %s', 'piazhen'), wc_attribute_label($attribute_name))); ?></option>
                                    <?php foreach ($options as $option): ?>
                                        <option value="<?php echo esc_attr($option); ?>"
                                            <?php selected($selected, $option); ?>>
                                            <?php echo esc_html(apply_filters('woocommerce_variation_option_name', $option, null, $attribute_name, $product)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="variation-popup__qty">
                <label class="variation-field__label"><?php _e('تعداد', 'piazhen'); ?></label>
                <div class="quantity-selector d-flex align-items-center gap-2">
                    <button class="qty-btn qty-minus" type="button">-</button>
                    <input type="number" class="qty-input" id="popup-qty" value="1" min="1" max="99">
                    <button class="qty-btn qty-plus" type="button">+</button>
                </div>
            </div>

            <div class="variation-popup__message"></div>

            <button class="variation-popup__submit mainBtn mainBtn--yellow w-100"
                    data-product-id="<?php echo $product_id; ?>">
                <?php _e('افزودن به سبد خرید', 'piazhen'); ?>
            </button>
        </div>
    </div>
    <?php
    $html = ob_get_clean();

    wp_send_json_success(array('html' => $html));
}
add_action('wp_ajax_pzh_get_variation_popup', 'pzh_get_variation_popup');
add_action('wp_ajax_nopriv_pzh_get_variation_popup', 'pzh_get_variation_popup');

/**
 * AJAX Match Variation - find variation ID from selected attributes
 */
function pzh_get_variation_match() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();

    if (!$product_id || empty($attributes)) {
        wp_send_json_error();
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->is_type('variable')) {
        wp_send_json_error();
    }

    // Find matching variation
    $data_store = WC_Data_Store::load('product');
    $variation_id = $data_store->find_matching_product_variation($product, $attributes);

    if ($variation_id) {
        $variation = wc_get_product($variation_id);
        $availability = $variation->is_in_stock()
            ? __('موجود در انبار', 'piazhen')
            : __('ناموجود', 'piazhen');

        // Variation image (falls back to the parent image)
        $image = $variation->get_image_id() ? wp_get_attachment_image_url($variation->get_image_id(), 'woocommerce_single') : '';

        wp_send_json_success(array(
            'variation_id' => $variation_id,
            'price_html'   => $variation->get_price_html(),
            'availability' => $availability,
            'in_stock'     => $variation->is_in_stock(),
            'image'        => $image,
        ));
    } else {
        wp_send_json_error(array('message' => __('ترکیب انتخاب شده موجود نیست.', 'piazhen')));
    }
}
add_action('wp_ajax_pzh_get_variation_match', 'pzh_get_variation_match');
add_action('wp_ajax_nopriv_pzh_get_variation_match', 'pzh_get_variation_match');

// ============================================================================
// Single Product Helpers
// ============================================================================

/**
 * Stars HTML for a rating value (0-5)
 */
function pzh_stars_html($rating) {
    $rating = max(0, min(5, floatval($rating)));
    $full = intval(floor($rating));
    $half = ($rating - $full) >= 0.5;

    $html = '<span class="pzh-stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($half && $i === $full + 1) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star"></i>';
        }
    }
    $html .= '</span>';
    return $html;
}

/**
 * Per-star review count breakdown for a product
 */
function pzh_get_review_breakdown($product_id) {
    global $wpdb;
    $counts = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT cm.meta_value AS rating, COUNT(*) AS c
         FROM {$wpdb->commentmeta} cm
         INNER JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id
         WHERE c.comment_post_ID = %d AND c.comment_approved = '1' AND cm.meta_key = 'rating'
         GROUP BY cm.meta_value",
        $product_id
    ));

    if (!empty($rows)) {
        foreach ($rows as $row) {
            $rating = intval($row->rating);
            if (isset($counts[$rating])) {
                $counts[$rating] = intval($row->c);
            }
        }
    }
    return $counts;
}

/**
 * Map a Persian color name to a hex value (fallback when no color meta is stored).
 * No plugin — a self-contained map, longest key matched first.
 */
function pzh_color_hex($name) {
    static $map = null;
    if ($map === null) {
        $map = array(
            'آبی فیروزه ای' => '#40e0d0',
            'آبی کاربنی'    => '#27355e',
            'آبی آسمانی'    => '#87ceeb',
            'آبی روشن'      => '#9cc3ff',
            'سبز روشن'      => '#8fd694',
            'سبز تیره'      => '#1e5e2e',
            'نارنجی پررنگ'  => '#e0691b',
            'قرمز جگری'     => '#8c2f39',
            'مغز پسته‌ای'   => '#a7d28d',
            'بنفش روشن'     => '#c9a7eb',
            'مشکی'          => '#1a1a1a',
            'سیاه'          => '#1a1a1a',
            'سفید'          => '#ffffff',
            'سرمه‌ای'       => '#1f2a44',
            'سورمه ای'      => '#1f2a44',
            'قرمز'          => '#dc3545',
            'زرشکی'         => '#8b0000',
            'شرابی'         => '#722f37',
            'صورتی'         => '#ff8fab',
            'آبی'           => '#4a7fd4',
            'لاجوردی'       => '#2e5cb8',
            'فیروزه‌ای'     => '#40e0d0',
            'سبز'           => '#3a9d4b',
            'یشمی'          => '#00a86b',
            'زرد'           => '#f6c944',
            'طلایی'         => '#d4af37',
            'نارنجی'        => '#f28c28',
            'بنفش'          => '#8e5bb5',
            'بادمجانی'      => '#5a2d50',
            'یاسی'          => '#b39ddb',
            'شکلاتی'        => '#5d3a1a',
            'قهوه ای'       => '#8b5a2b',
            'کرم'           => '#f5e6c8',
            'بژ'            => '#e8d5b7',
            'نود'           => '#e6cfb5',
            'خاکستری'       => '#9e9e9e',
            'طوسی'          => '#9e9e9e',
            'نقره ای'       => '#cfd4da',
            'دودی'          => '#6e7480',
            'رزگلد'         => '#d8a49b',
            'مسی'           => '#b87333',
            'برنزی'         => '#a97142',
        );
        uksort($map, function ($a, $b) { return mb_strlen($b) - mb_strlen($a); });
    }

    $name = trim(mb_strtolower($name));
    foreach ($map as $key => $hex) {
        if ($name === $key || mb_strpos($name, $key) !== false) {
            return $hex;
        }
    }
    return '';
}

/**
 * Variation picker data: attributes as chips (text) or swatches (colors),
 * resolved from WooCommerce data only — no plugins.
 */
function pzh_get_variation_picker_data($product) {
    if (!$product->is_type('variable')) return array();

    $data = array();
    foreach ($product->get_variation_attributes() as $name => $options) {
        $tax      = sanitize_title($name);
        $is_tax   = taxonomy_exists($tax);
        $label    = wc_attribute_label($name, $product);
        $is_color = (stripos($label, 'رنگ') !== false || stripos($name, 'color') !== false);

        $items = array();
        foreach ($options as $option) {
            $term  = $is_tax ? get_term_by('slug', $option, $tax) : null;
            $color = '';
            if ($is_color && $term) {
                $color = get_term_meta($term->term_id, 'product_attribute_color', true);
                if (!$color) {
                    $color = pzh_color_hex($term->name);
                }
            }
            $items[] = array(
                'slug'  => (string) $option,
                'label' => $term ? $term->name : $option,
                'color' => $color ?: '',
            );
        }

        if (empty($items)) continue;

        $data[] = array(
            'name'     => $name,
            'label'    => $label,
            'taxonomy' => $tax,
            'type'     => $is_color ? 'swatch' : 'chip',
            'items'    => $items,
        );
    }
    return $data;
}

/**
 * AJAX Remove from Cart
 */
function pzh_remove_from_cart() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $cart_item_key = isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '';

    if ($cart_item_key && WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_success(array(
            'message'    => __('محصول از سبد خرید حذف شد.', 'piazhen'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ));
    }

    wp_send_json_error(array('message' => __('خطا در حذف محصول.', 'piazhen')));
}
add_action('wp_ajax_pzh_remove_from_cart', 'pzh_remove_from_cart');
add_action('wp_ajax_nopriv_pzh_remove_from_cart', 'pzh_remove_from_cart');

/**
 * AJAX Toggle Favorite
 */
function pzh_toggle_favorite() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(array('message' => __('محصول نامعتبر است.', 'piazhen')));
    }

    if (is_user_logged_in()) {
        $user_id   = get_current_user_id();
        $favorites = get_user_meta($user_id, 'pzh_favorites', true);
        if (!is_array($favorites)) $favorites = array();

        if (in_array($product_id, $favorites)) {
            $favorites = array_diff($favorites, array($product_id));
            $action = 'removed';
        } else {
            $favorites[] = $product_id;
            $action = 'added';
        }
        update_user_meta($user_id, 'pzh_favorites', array_values($favorites));
    } else {
        // Guest - use cookie
        $favorites = isset($_COOKIE['pzh_favorites']) ? json_decode(stripslashes($_COOKIE['pzh_favorites']), true) : array();
        if (!is_array($favorites)) $favorites = array();

        if (in_array($product_id, $favorites)) {
            $favorites = array_values(array_diff($favorites, array($product_id)));
            $action = 'removed';
        } else {
            $favorites[] = $product_id;
            $action = 'added';
        }
        setcookie('pzh_favorites', json_encode($favorites), time() + (30 * DAY_IN_SECONDS), '/');
    }

    wp_send_json_success(array(
        'action'    => $action,
        'message'   => $action === 'added' ? __('به علاقه‌مندی‌ها اضافه شد.', 'piazhen') : __('از علاقه‌مندی‌ها حذف شد.', 'piazhen'),
        'favorites' => $favorites,
    ));
}
add_action('wp_ajax_pzh_toggle_favorite', 'pzh_toggle_favorite');
add_action('wp_ajax_nopriv_pzh_toggle_favorite', 'pzh_toggle_favorite');

/**
 * AJAX Filter Products (custom, no plugins)
 */
function pzh_filter_products() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $page        = max(1, isset($_POST['page']) ? intval($_POST['page']) : 1);
    $per_page    = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

    $params = pzh_get_filter_params_from_request($_POST);

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
    );

    // Base context: the current category page (if any)
    $base_tax_query = array();
    if ($category_id && term_exists($category_id, 'product_cat')) {
        $base_tax_query = array(
            array(
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => $category_id,
                'include_children' => true,
            ),
        );
    }

    $filter_args = pzh_get_product_filter_args($params, $base_tax_query);
    $args = array_merge($args, $filter_args);

    $query = new WP_Query($args);
    $html  = pzh_render_products_grid($query, $page, $per_page);
    wp_reset_postdata();

    wp_send_json_success(array(
        'html'        => $html,
        'total'       => intval($query->found_posts),
        'page'        => $page,
        'total_pages' => $per_page ? intval(ceil($query->found_posts / $per_page)) : 0,
    ));
}
add_action('wp_ajax_pzh_filter_products', 'pzh_filter_products');
add_action('wp_ajax_nopriv_pzh_filter_products', 'pzh_filter_products');

/**
 * AJAX Get Mini-Cart HTML (used after cart fragment updates)
 */
function pzh_get_mini_cart() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_total = WC()->cart->get_cart_total();

    ob_start();
    // Same mini-cart HTML as pzh_get_cart_data
    ?>
    <div class="mini-cart">
        <?php if ($cart_count > 0): ?>
            <ul class="mini-cart__items">
                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
                    $_product = $cart_item['data'];
                    ?>
                    <li class="mini-cart__item">
                        <div class="mini-cart__item-image">
                            <?php echo $_product->get_image('pzh_product_thumb'); ?>
                        </div>
                        <div class="mini-cart__item-info">
                            <span class="mini-cart__item-name"><?php echo $_product->get_name(); ?></span>
                            <span class="mini-cart__item-qty"><?php echo $cart_item['quantity']; ?> × <?php echo wc_price($_product->get_price()); ?></span>
                        </div>
                        <button class="mini-cart__remove" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="mini-cart__footer">
                <div class="mini-cart__total">
                    <span><?php _e('جمع کل:', 'piazhen'); ?></span>
                    <span><?php echo $cart_total; ?></span>
                </div>
                <a href="<?php echo wc_get_cart_url(); ?>" class="mini-cart__cart-btn mainBtn small"><?php _e('مشاهده سبد خرید', 'piazhen'); ?></a>
                <a href="<?php echo wc_get_checkout_url(); ?>" class="mini-cart__checkout-btn mainBtn small"><?php _e('تسویه حساب', 'piazhen'); ?></a>
            </div>
        <?php else: ?>
            <p class="mini-cart__empty"><?php _e('سبد خرید خالی است.', 'piazhen'); ?></p>
        <?php endif; ?>
    </div>
    <?php
    $html = ob_get_clean();

    wp_send_json_success(array(
        'count' => $cart_count,
        'html'  => $html,
        'total' => $cart_total,
    ));
}
add_action('wp_ajax_pzh_get_mini_cart', 'pzh_get_mini_cart');
add_action('wp_ajax_nopriv_pzh_get_mini_cart', 'pzh_get_mini_cart');

// ============================================================================
// Cart Page (custom AJAX: qty, remove, coupons — no plugins)
// ============================================================================

/**
 * Render cart items list (shared by the cart page and the AJAX handler)
 */
function pzh_cart_items_html() {
    ob_start();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $_product  = $cart_item['data'];
        $permalink = $_product->get_permalink($cart_item);
        $price     = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
        $subtotal  = apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
        $max_qty   = $_product->get_max_purchase_quantity() > 0 ? $_product->get_max_purchase_quantity() : 99;
        ?>
        <div class="cart-item" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
            <div class="cart-item__image">
                <a href="<?php echo esc_url($permalink); ?>">
                    <?php echo $_product->get_image('pzh_product_thumb'); ?>
                </a>
            </div>
            <div class="cart-item__info">
                <a class="cart-item__name" href="<?php echo esc_url($permalink); ?>">
                    <?php echo esc_html($_product->get_name()); ?>
                </a>
                <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
            </div>
            <div class="cart-item__price" data-label="<?php esc_attr_e('قیمت واحد', 'piazhen'); ?>">
                <?php echo $price; ?>
            </div>
            <div class="cart-item__qty" data-label="<?php esc_attr_e('تعداد', 'piazhen'); ?>">
                <div class="cart-qty d-inline-flex align-items-center">
                    <button type="button" class="cart-qty__btn cart-qty__btn--minus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="<?php esc_attr_e('کمتر', 'piazhen'); ?>">-</button>
                    <input type="number" class="cart-qty__input" value="<?php echo esc_attr($cart_item['quantity']); ?>"
                           min="1" max="<?php echo esc_attr($max_qty); ?>" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                    <button type="button" class="cart-qty__btn cart-qty__btn--plus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="<?php esc_attr_e('بیشتر', 'piazhen'); ?>">+</button>
                </div>
            </div>
            <div class="cart-item__subtotal" data-label="<?php esc_attr_e('جمع', 'piazhen'); ?>">
                <?php echo $subtotal; ?>
            </div>
            <button type="button" class="cart-item__remove" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="<?php esc_attr_e('حذف از سبد', 'piazhen'); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </button>
        </div>
        <?php
    }
    return ob_get_clean();
}

/**
 * Render the cart summary card (coupons + totals + buttons)
 */
function pzh_cart_totals_html() {
    ob_start();
    $coupons = WC()->cart->get_coupons();
    ?>
    <div class="cart-summary-card">
        <!-- Coupon -->
        <form class="coupon-form" method="post">
            <label class="coupon-form__label"><?php _e('کد تخفیف', 'piazhen'); ?></label>
            <div class="coupon-form__row d-flex gap-2">
                <input type="text" name="coupon_code" class="coupon-form__input"
                       placeholder="<?php _e('کد تخفیف خود را وارد کنید', 'piazhen'); ?>" autocomplete="off">
                <button type="submit" class="coupon-form__btn mainBtn small"><?php _e('اعمال', 'piazhen'); ?></button>
            </div>
        </form>

        <?php if (!empty($coupons)): ?>
            <div class="applied-coupons">
                <?php foreach ($coupons as $code => $coupon): ?>
                    <span class="applied-coupon">
                        <i class="fa-solid fa-tag"></i>
                        <?php echo esc_html($code); ?>
                        <button type="button" class="applied-coupon__remove" data-code="<?php echo esc_attr($code); ?>" aria-label="<?php esc_attr_e('حذف کد', 'piazhen'); ?>">&times;</button>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Totals -->
        <div class="cart-totals-rows">
            <div class="cart-total-row">
                <span><?php _e('مبلغ کل کالاها', 'piazhen'); ?></span>
                <span><?php wc_cart_totals_subtotal_html(); ?></span>
            </div>

            <?php foreach ($coupons as $code => $coupon): ?>
                <div class="cart-total-row cart-total-row--discount">
                    <span><?php _e('تخفیف', 'piazhen'); ?></span>
                    <span><?php wc_cart_totals_coupon_html($coupon); ?></span>
                </div>
            <?php endforeach; ?>

            <?php if (WC()->cart->needs_shipping()): ?>
                <div class="cart-total-row">
                    <span><?php _e('هزینه ارسال', 'piazhen'); ?></span>
                    <span class="cart-shipping-note"><?php _e('در مرحله بعد محاسبه می‌شود', 'piazhen'); ?></span>
                </div>
            <?php endif; ?>

            <div class="cart-total-row cart-total-row--total">
                <span><?php _e('مبلغ قابل پرداخت', 'piazhen'); ?></span>
                <span><?php wc_cart_totals_order_total_html(); ?></span>
            </div>
        </div>

        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="cart-checkout-btn mainBtn mainBtn--yellow w-100">
            <?php _e('ادامه فرایند خرید', 'piazhen'); ?>
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="cart-continue-link">
            <?php _e('ادامه خرید', 'piazhen'); ?>
        </a>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Cart page empty state
 */
function pzh_cart_empty_html() {
    ob_start();
    ?>
    <div class="cart-empty">
        <div class="cart-empty__icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        </div>
        <h3 class="cart-empty__title"><?php _e('سبد خرید شما خالی است', 'piazhen'); ?></h3>
        <p class="cart-empty__text"><?php _e('محصولات مورد علاقه خود را به سبد خرید اضافه کنید.', 'piazhen'); ?></p>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="mainBtn mainBtn--yellow"><?php _e('بازگشت به فروشگاه', 'piazhen'); ?></a>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * AJAX Cart Actions (qty update, remove, apply/remove coupon)
 */
function pzh_cart_ajax() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $cart_action = isset($_POST['cart_action']) ? sanitize_key($_POST['cart_action']) : '';
    $cart_key    = isset($_POST['cart_key']) ? sanitize_text_field(wp_unslash($_POST['cart_key'])) : '';
    $message     = '';

    switch ($cart_action) {
        case 'set_qty':
            $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
            if ($cart_key && $qty > 0) {
                WC()->cart->set_quantity($cart_key, $qty);
            }
            break;

        case 'remove':
            if ($cart_key) {
                WC()->cart->remove_cart_item($cart_key);
                $message = __('محصول از سبد خرید حذف شد.', 'piazhen');
            }
            break;

        case 'coupon':
            $code = isset($_POST['code']) ? sanitize_text_field(wp_unslash($_POST['code'])) : '';
            if ($code) {
                WC()->cart->apply_coupon($code);
                if (!WC()->cart->has_discount($code)) {
                    wp_send_json_error(array('message' => __('کد تخفیف معتبر نیست.', 'piazhen')));
                }
                $message = __('کد تخفیف با موفقیت اعمال شد.', 'piazhen');
            }
            break;

        case 'remove_coupon':
            $code = isset($_POST['code']) ? sanitize_text_field(wp_unslash($_POST['code'])) : '';
            if ($code) {
                WC()->cart->remove_coupon($code);
                $message = __('کد تخفیف حذف شد.', 'piazhen');
            }
            break;
    }

    wp_send_json_success(array(
        'message'    => $message,
        'count'      => WC()->cart->get_cart_contents_count(),
        'items_html' => pzh_cart_items_html(),
        'totals_html' => pzh_cart_totals_html(),
        'empty_html' => pzh_cart_empty_html(),
        'is_empty'   => WC()->cart->is_empty(),
    ));
}
add_action('wp_ajax_pzh_cart_ajax', 'pzh_cart_ajax');
add_action('wp_ajax_nopriv_pzh_cart_ajax', 'pzh_cart_ajax');

/**
 * Add a live-updating shipping methods fragment to checkout AJAX updates
 * (the shipping step lives in the form column, outside the default fragments)
 */
function pzh_checkout_shipping_fragment($fragments) {
    ob_start();
    wc_cart_totals_shipping_html();
    $shipping_html = ob_get_clean();

    if (!trim($shipping_html)) {
        $shipping_html = '<p class="shipping-methods-note">' . esc_html__('برای مشاهده روش‌های ارسال، آدرس خود را تکمیل کنید.', 'piazhen') . '</p>';
    }

    $fragments['#pzh-shipping-methods'] = $shipping_html;
    return $fragments;
}
add_filter('woocommerce_update_order_review_fragments', 'pzh_checkout_shipping_fragment');

/**
 * The checkout design has a single address form, so the shop ships to the
 * billing address only (set at runtime in case the option gets reset).
 */
function pzh_force_ship_to_billing_only() {
    if (get_option('woocommerce_ship_to_destination') !== 'billing_only') {
        update_option('woocommerce_ship_to_destination', 'billing_only');
    }
}
add_action('init', 'pzh_force_ship_to_billing_only');

// ============================================================================
// Checkout Map (Neshan raster tiles + optional reverse geocoding — AJAX)
// ============================================================================

/**
 * Neshan API key (optional). Define PZH_NESHAN_API_KEY in wp-config.php or use
 * the 'pzh_neshan_api_key' filter. Without a key the map still works
 * (raster tiles are public); only reverse geocoding/search is skipped.
 */
function pzh_neshan_api_key() {
    return apply_filters('pzh_neshan_api_key', defined('PZH_NESHAN_API_KEY') ? PZH_NESHAN_API_KEY : '');
}

/**
 * Checkout fields: hide company, force country to IR, add the محله field
 */
function pzh_checkout_fields($fields) {
    // Country is always Iran (hidden field)
    if (isset($fields['billing']['billing_country'])) {
        $fields['billing']['billing_country'] = array(
            'type'     => 'hidden',
            'default'  => 'IR',
            'required' => true,
            'class'    => array('form-row-wide'),
        );
    }

    // No company field in the design
    unset($fields['billing']['billing_company']);

    // محله (district) — plain text; the map can fill it via reverse geocoding
    if (!isset($fields['billing']['billing_district'])) {
        $fields['billing']['billing_district'] = array(
            'label'       => __('محله', 'piazhen'),
            'type'        => 'text',
            'placeholder' => __('مثال: سعادت‌آباد', 'piazhen'),
            'required'    => false,
            'class'       => array('form-row-wide'),
            'clear'       => true,
            'priority'    => 47,
        );
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'pzh_checkout_fields', 30);

/**
 * AJAX: reverse geocode map coordinates via Neshan (requires the API key)
 */
function pzh_reverse_geocode() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : 0;
    $lng = isset($_POST['lng']) ? floatval($_POST['lng']) : 0;

    if (!$lat || !$lng) {
        wp_send_json_error(array('message' => __('موقعیت نامعتبر است.', 'piazhen')));
    }

    $key = pzh_neshan_api_key();
    if (!$key) {
        // No key: coordinates are still saved, address stays manual
        wp_send_json_success(array(
            'geocoded' => false,
            'address'  => '',
            'state'    => '',
            'city'     => '',
            'district' => '',
        ));
    }

    $url = 'https://api.neshan.org/v5/reverse?lat=' . $lat . '&lng=' . $lng;
    $resp = wp_remote_get($url, array(
        'headers' => array('Api-Key' => $key),
        'timeout' => 10,
    ));

    if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
        wp_send_json_error(array('message' => __('خطا در دریافت آدرس از نقشه.', 'piazhen')));
    }

    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (empty($body)) {
        wp_send_json_error(array('message' => __('پاسخی از نقشه دریافت نشد.', 'piazhen')));
    }

    wp_send_json_success(array(
        'geocoded' => true,
        'address'  => isset($body['formatted_address']) ? $body['formatted_address'] : (isset($body['address']) ? $body['address'] : ''),
        'state'    => isset($body['state']) ? $body['state'] : '',
        'city'     => isset($body['city']) ? $body['city'] : '',
        'district' => isset($body['neighbourhood']) ? $body['neighbourhood'] : (isset($body['neighborhood']) ? $body['neighborhood'] : (isset($body['district']) ? $body['district'] : '')),
    ));
}
add_action('wp_ajax_pzh_reverse_geocode', 'pzh_reverse_geocode');
add_action('wp_ajax_nopriv_pzh_reverse_geocode', 'pzh_reverse_geocode');

/**
 * Save the picked map coordinates to the order
 */
function pzh_checkout_save_map_coords($order_id, $posted) {
    if (isset($posted['billing_latitude']) && $posted['billing_latitude'] !== '') {
        $order = wc_get_order($order_id);
        $order->update_meta_data('_billing_latitude', sanitize_text_field($posted['billing_latitude']));
        $order->update_meta_data('_billing_longitude', sanitize_text_field($posted['billing_longitude']));
        $order->save();
    }
}
add_action('woocommerce_checkout_update_order_meta', 'pzh_checkout_save_map_coords', 20, 2);
add_filter('woocommerce_update_order_review_fragments', 'pzh_checkout_shipping_fragment');

// ============================================================================
// WooCommerce Hooks
// ============================================================================

// Remove default WooCommerce breadcrumbs (we'll add our own if needed)
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

// Wrap WooCommerce content
add_action('woocommerce_before_main_content', 'pzh_woo_wrapper_start', 10);
function pzh_woo_wrapper_start() {
    echo '<main class="woo-main"><div class="container">';
}

add_action('woocommerce_after_main_content', 'pzh_woo_wrapper_end', 10);
function pzh_woo_wrapper_end() {
    echo '</div></main>';
}

// Remove default WooCommerce sidebar
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// ============================================================================
// Mega Menu Walker
// ============================================================================

/**
 * Primary menu fallback: use the first available menu (preferring one whose
 * name contains "هدر") when no menu is assigned to the 'primary' location.
 */
function pzh_primary_menu_fallback() {
    $menus = wp_get_nav_menus();
    if (empty($menus)) return;

    $chosen = null;
    foreach ($menus as $menu) {
        if (false !== strpos($menu->name, 'هدر')) {
            $chosen = $menu;
            break;
        }
    }
    if (!$chosen) {
        $chosen = $menus[0];
    }

    wp_nav_menu(array(
        'menu'       => $chosen,
        'menu_class' => 'main-menu',
        'container'  => 'ul',
        'walker'     => new PZH_Mega_Menu_Walker(),
    ));
}

class PZH_Mega_Menu_Walker extends Walker_Nav_Menu {

    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $classes   = empty($item->classes) ? array() : (array) $item->classes;
        $has_mega  = in_array('mega-menu', $classes);
        $has_children = in_array('menu-item-has-children', $classes);

        $output .= '<li class="' . esc_attr(implode(' ', $classes)) . '">';

        $attributes  = '';
        $attributes .= !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        if ($has_children || $has_mega) {
            $item_output .= ' <svg class="menu-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
        }
        $item_output .= '</a>';

        // Mega menu: show WooCommerce product categories grid
        if ($has_mega && $depth === 0) {
            $item_output .= '<div class="mega-menu">';
            $item_output .= '<div class="mega-menu__inner container">';
            $item_output .= pzh_get_mega_menu_categories();
            $item_output .= '</div></div>';
        }

        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    function start_lvl(&$output, $depth = 0, $args = array()) {
        $output .= '<ul class="sub-menu">';
    }
}

/**
 * Get mega menu categories HTML
 */
function pzh_get_mega_menu_categories() {
    ob_start();

    $parent_categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => 0,
        'number'     => 8,
    ));

    if (!empty($parent_categories) && !is_wp_error($parent_categories)) {
        echo '<div class="mega-menu__grid">';
        foreach ($parent_categories as $cat) {
            $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
            $image        = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : wc_placeholder_img_src('thumbnail');

            // Get subcategories
            $sub_cats = get_terms(array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'parent'     => $cat->term_id,
                'number'     => 6,
            ));

            echo '<div class="mega-menu__category">';
            echo '<a href="' . get_term_link($cat) . '" class="mega-menu__category-header">';
            echo '<img src="' . esc_url($image) . '" alt="' . esc_attr($cat->name) . '" class="mega-menu__category-image">';
            echo '<span class="mega-menu__category-title">' . esc_html($cat->name) . '</span>';
            echo '</a>';

            if (!empty($sub_cats) && !is_wp_error($sub_cats)) {
                echo '<ul class="mega-menu__subcategories">';
                foreach ($sub_cats as $sub) {
                    echo '<li><a href="' . get_term_link($sub) . '">' . esc_html($sub->name) . '</a></li>';
                }
                echo '</ul>';
            }

            echo '</div>';
        }
        echo '</div>';
    }

    return ob_get_clean();
}

// ============================================================================
// WooCommerce AJAX Cart Fragments
// ============================================================================
add_filter('woocommerce_add_to_cart_fragments', 'pzh_cart_fragments');
function pzh_cart_fragments($fragments) {
    ob_start();
    ?>
    <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    <?php
    $fragments['.cart-count'] = ob_get_clean();

    return $fragments;
}

// Remove default WooCommerce styles (we use our own)
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// ============================================================================
// Single Product Helpers
// ============================================================================

/**
 * Get product brand name
 */
function pzh_get_product_brand($product_id) {
    if (taxonomy_exists('product_brand')) {
        $terms = get_the_terms($product_id, 'product_brand');
        if ($terms && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
    }
    return '';
}

/**
 * Get product brand link
 */
function pzh_get_product_brand_link($product_id) {
    if (taxonomy_exists('product_brand')) {
        $terms = get_the_terms($product_id, 'product_brand');
        if ($terms && !is_wp_error($terms)) {
            return get_term_link($terms[0]);
        }
    }
    return '';
}

/**
 * Get product specifications (attributes formatted for display)
 */
function pzh_get_product_specs($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) return array();

    $specs = array();

    // Weight
    if ($product->has_weight()) {
        $specs['وزن'] = $product->get_weight() . ' ' . get_option('woocommerce_weight_unit');
    }

    // Dimensions
    if ($product->has_dimensions()) {
        $specs['ابعاد'] = $product->get_dimensions();
    }

    // SKU
    if ($product->get_sku()) {
        $specs['کد محصول'] = $product->get_sku();
    }

    // Attributes
    foreach ($product->get_attributes() as $attribute) {
        if ($attribute->get_variation()) continue; // Skip variation attributes
        $label = wc_attribute_label($attribute->get_name());
        $value = $attribute->is_taxonomy()
            ? implode(', ', wc_get_product_terms($product_id, $attribute->get_name(), array('fields' => 'names')))
            : $attribute->get_options()[0] ?? '';
        if ($value) {
            $specs[$label] = $value;
        }
    }

    return $specs;
}

/**
 * Get product FAQ items
 */
function pzh_get_product_faqs($product_id) {
    $faqs = get_post_meta($product_id, '_pzh_faqs', true);
    if (!empty($faqs) && is_array($faqs)) {
        return $faqs;
    }

    // Default FAQs if none set
    return array(
        array(
            'question' => __('چگونه می‌توانم این محصول را سفارش دهم؟', 'piazhen'),
            'answer'   => __('برای سفارش این محصول، آن را به سبد خرید اضافه کرده و مراحل پرداخت را تکمیل کنید. پس از ثبت سفارش، محصول در سریع‌ترین زمان ممکن ارسال خواهد شد.', 'piazhen'),
        ),
        array(
            'question' => __('مدت زمان ارسال چقدر است؟', 'piazhen'),
            'answer'   => __('ارسال به تهران ۱ تا ۲ روز کاری و به شهرستان‌ها ۳ تا ۵ روز کاری زمان می‌برد.', 'piazhen'),
        ),
        array(
            'question' => __('شرایط بازگشت کالا چگونه است؟', 'piazhen'),
            'answer'   => __('در صورت وجود هرگونه مشکل در محصول، تا ۷ روز پس از دریافت، امکان بازگشت یا تعویض کالا وجود دارد.', 'piazhen'),
        ),
    );
}

/**
 * Get related products
 */
function pzh_get_related_products($product_id, $limit = 10) {
    $product = wc_get_product($product_id);
    if (!$product) return array();

    $related = wc_get_related_products($product_id, $limit, array());

    if (empty($related)) {
        // Fallback: same category products
        $cats = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
        $related = get_posts(array(
            'post_type'      => 'product',
            'posts_per_page' => $limit,
            'post__not_in'   => array($product_id),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $cats,
                ),
            ),
            'fields' => 'ids',
        ));
    }

    return $related;
}

// ============================================================================
// FAQ Meta Box
// ============================================================================
function pzh_add_faq_metabox() {
    add_meta_box(
        'pzh_product_faqs',
        __('سوالات متداول محصول', 'piazhen'),
        'pzh_faq_metabox_callback',
        'product',
        'normal',
        'low'
    );
}
add_action('add_meta_boxes', 'pzh_add_faq_metabox');

function pzh_faq_metabox_callback($post) {
    wp_nonce_field('pzh_faq_metabox', 'pzh_faq_nonce');
    $faqs = get_post_meta($post->ID, '_pzh_faqs', true);
    if (!is_array($faqs)) $faqs = array(array('question' => '', 'answer' => ''));
    ?>
    <div class="pzh-faq-repeater">
        <div class="pzh-faq-items">
            <?php foreach ($faqs as $index => $faq): ?>
            <div class="pzh-faq-item" style="margin-bottom:12px;padding:12px;background:#f9f9f9;border-radius:6px;border:1px solid #eee;">
                <div style="margin-bottom:8px;">
                    <label style="display:block;font-weight:600;margin-bottom:4px;"><?php _e('سوال', 'piazhen'); ?></label>
                    <input type="text" name="pzh_faqs[<?php echo $index; ?>][question]"
                           value="<?php echo esc_attr($faq['question'] ?? ''); ?>"
                           style="width:100%;padding:6px 10px;"
                           placeholder="<?php _e('متن سوال را وارد کنید...', 'piazhen'); ?>">
                </div>
                <div style="margin-bottom:8px;">
                    <label style="display:block;font-weight:600;margin-bottom:4px;"><?php _e('پاسخ', 'piazhen'); ?></label>
                    <textarea name="pzh_faqs[<?php echo $index; ?>][answer]"
                              style="width:100%;padding:6px 10px;min-height:60px;"
                              placeholder="<?php _e('متن پاسخ را وارد کنید...', 'piazhen'); ?>"><?php echo esc_textarea($faq['answer'] ?? ''); ?></textarea>
                </div>
                <button type="button" class="button pzh-remove-faq"><?php _e('حذف', 'piazhen'); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary pzh-add-faq" style="margin-top:8px;"><?php _e('+ افزودن سوال جدید', 'piazhen'); ?></button>
    </div>
    <script>
    jQuery(function($){
        var faqIndex = <?php echo count($faqs); ?>;
        $('.pzh-add-faq').click(function(){
            var html = '<div class="pzh-faq-item" style="margin-bottom:12px;padding:12px;background:#f9f9f9;border-radius:6px;border:1px solid #eee;">'+
                '<div style="margin-bottom:8px;"><label style="display:block;font-weight:600;margin-bottom:4px;">سوال</label>'+
                '<input type="text" name="pzh_faqs['+faqIndex+'][question]" style="width:100%;padding:6px 10px;" placeholder="متن سوال را وارد کنید..."></div>'+
                '<div style="margin-bottom:8px;"><label style="display:block;font-weight:600;margin-bottom:4px;">پاسخ</label>'+
                '<textarea name="pzh_faqs['+faqIndex+'][answer]" style="width:100%;padding:6px 10px;min-height:60px;" placeholder="متن پاسخ را وارد کنید..."></textarea></div>'+
                '<button type="button" class="button pzh-remove-faq">حذف</button></div>';
            $('.pzh-faq-items').append(html);
            faqIndex++;
        });
        $(document).on('click', '.pzh-remove-faq', function(){
            if($('.pzh-faq-item').length > 1) {
                $(this).closest('.pzh-faq-item').remove();
            } else {
                alert('حداقل یک سوال باید وجود داشته باشد.');
            }
        });
    });
    </script>
    <?php
}

function pzh_save_faq_metabox($post_id) {
    if (!isset($_POST['pzh_faq_nonce'])) return;
    if (!wp_verify_nonce($_POST['pzh_faq_nonce'], 'pzh_faq_metabox')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['pzh_faqs']) && is_array($_POST['pzh_faqs'])) {
        $faqs = array();
        foreach ($_POST['pzh_faqs'] as $faq) {
            if (!empty(trim($faq['question'])) && !empty(trim($faq['answer']))) {
                $faqs[] = array(
                    'question' => sanitize_text_field($faq['question']),
                    'answer'   => wp_kses_post($faq['answer']),
                );
            }
        }
        if (!empty($faqs)) {
            update_post_meta($post_id, '_pzh_faqs', $faqs);
        } else {
            delete_post_meta($post_id, '_pzh_faqs');
        }
    }
}
add_action('save_post', 'pzh_save_faq_metabox');
