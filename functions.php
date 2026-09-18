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
// WooCommerce Price Format (per design mockups: Persian digits + تومان suffix)
// ============================================================================

// "۱۶.۵۵۰.۰۰۰ تومان" — number first, then the currency symbol
// (wc_price args: %1$s = symbol, %2$s = number)
add_filter('woocommerce_price_format', function () {
    return '%2$s %1$s';
});

// Convert price digits to Persian (۱۵.۵۵۰.۰۰۰ instead of 15.550.000)
add_filter('formatted_woocommerce_price', 'pzh_fa_num', 10, 6);

// ============================================================================
// Enqueue Scripts & Styles
// ============================================================================
function piazhen_scripts() {
    $version = wp_get_theme()->get('Version');

    // Styles
    wp_enqueue_style('swiper-css', PZH_THEME_URI . '/assets/css/swiper-bundle.min.css', array(), '12.1.3');
    wp_enqueue_style('piazhen-font-awesome', PZH_THEME_URI . '/assets/font-icons/css/all.min.css', array(), '7.3.1');
    wp_enqueue_style('piazhen-main-style', PZH_THEME_URI . '/assets/css/styles.css', array('swiper-css'), $version);

    // Scripts
    wp_enqueue_script('jquery');
    // Local Swiper (no CDN dependency — carousels must never break)
    wp_enqueue_script('swiper-js', PZH_THEME_URI . '/assets/js/swiper-bundle.min.js', array(), '12.1.3', true);
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
 * Get dashboard URL: WooCommerce my-account for logged-in users,
 * the login/registration page for guests.
 */
function pzhDashboardUrl() {
    if (is_user_logged_in()) {
        return class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : SITE_URL . '/my-account';
    }
    return pzh_auth_page_url();
}

/**
 * Get product card HTML (per the Row.png design: rounded gray image block +
 * heart button + centered title + green price pill — no card container)
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
            <button class="product-card__favorite <?php echo pzh_is_favorited($product_id) ? 'active' : ''; ?>"
                    data-product-id="<?php echo esc_attr($product_id); ?>"
                    aria-label="<?php _e('افزودن به علاقه‌مندی', 'piazhen'); ?>">
                <svg width="20" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
        </div>

        <h3 class="product-card__title">
            <a href="<?php echo get_permalink($product_id); ?>"><?php echo $product->get_name(); ?></a>
        </h3>

        <div class="product-card__price">
            <?php echo wc_price($product->get_price()); ?>
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

    // Hero carousel slides (yellow gradient, heading + 2-line subtitle + orange pill)
    $banners = array(
        'slides' => array(
            array(
                'title'    => __('اصلاحی سریع، دقیق و بین‌نقص', 'piazhen'),
                'subtitle' => array(
                    __('ظاهر جذاب، تیپی تازه', 'piazhen'),
                    __('همراه همیشگی آقایان خوش‌تیپ', 'piazhen'),
                ),
                'image'    => PZH_THEME_URI . '/assets/images/cover1.png',
                'link'     => $shop_url,
                'cta'      => __('مشاهده محصولات', 'piazhen'),
            ),
            array(
                'title'    => __('ست ماشین اصلاح حرفه‌ای', 'piazhen'),
                'subtitle' => array(
                    __('قدرت و دقت در یک دستگاه', 'piazhen'),
                    __('مناسب آرایشگاه و مصارف خانگی', 'piazhen'),
                ),
                'image'    => PZH_THEME_URI . '/assets/images/image.png',
                'link'     => $shop_url,
                'cta'      => __('مشاهده محصولات', 'piazhen'),
            ),
            array(
                'title'    => __('اپیلاتور و اصلاح موی بدن', 'piazhen'),
                'subtitle' => array(
                    __('پوستی صاف و لطیف', 'piazhen'),
                    __('بدون درد و سوزش', 'piazhen'),
                ),
                'image'    => PZH_THEME_URI . '/assets/images/card2.png',
                'link'     => $shop_url,
                'cta'      => __('مشاهده محصولات', 'piazhen'),
            ),
        ),
        // Two stacked photo tiles (left column)
        'side_cards' => array(
            array('title' => __('اصلاح سر و صورت', 'piazhen'), 'image' => PZH_THEME_URI . '/assets/images/image.png',  'link' => $shop_url),
            array('title' => __('اپیلاتور', 'piazhen'),          'image' => PZH_THEME_URI . '/assets/images/card2.png', 'link' => $shop_url),
        ),
        // Three photo tiles (bottom row)
        'bottom_cards' => array(
            array('title' => __('سایر محصولات', 'piazhen'),  'image' => PZH_THEME_URI . '/assets/images/card3.png', 'link' => $shop_url),
            array('title' => __('حالت دهنده مو', 'piazhen'), 'image' => PZH_THEME_URI . '/assets/images/card2.png', 'link' => $shop_url),
            array('title' => __('سشوار', 'piazhen'),          'image' => PZH_THEME_URI . '/assets/images/image.png',  'link' => $shop_url),
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
 * Does a variable product have at least one purchasable in-stock variation?
 */
function pzh_variable_has_stock($product) {
    if (!$product->is_type('variable')) {
        return $product->is_in_stock();
    }
    foreach ($product->get_available_variations() as $variation) {
        if (!empty($variation['is_in_stock'])) {
            return true;
        }
    }
    return false;
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

        // Color attributes → swatches; small option sets (e.g. warranty) → radios; the rest → chips
        $type = $is_color ? 'swatch' : (count($items) <= 4 ? 'radio' : 'chip');

        $data[] = array(
            'name'     => $name,
            'label'    => $label,
            'taxonomy' => $tax,
            'type'     => $type,
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
        'delivery_html' => pzh_free_delivery_html(),
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

    // No Neshan key: fall back to Nominatim (OpenStreetMap, free, keyless)
    if (!$key) {
        $url  = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' . $lat . '&lon=' . $lng . '&accept-language=fa';
        $resp = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array('User-Agent' => 'Piazhen-Theme/1.0 (localhost)'),
        ));

        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
            wp_send_json_success(array(
                'geocoded' => false,
                'address'  => '',
                'state'    => '',
                'city'     => '',
                'district' => '',
            ));
        }

        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if (empty($body) || empty($body['address'])) {
            wp_send_json_success(array('geocoded' => false, 'address' => '', 'state' => '', 'city' => '', 'district' => ''));
        }

        $addr = $body['address'];
        wp_send_json_success(array(
            'geocoded' => true,
            'address'  => isset($body['display_name']) ? $body['display_name'] : '',
            'state'    => isset($addr['state']) ? $addr['state'] : '',
            'city'     => isset($addr['city']) ? $addr['city'] : (isset($addr['town']) ? $addr['town'] : ''),
            'district' => isset($addr['suburb']) ? $addr['suburb'] : (isset($addr['neighbourhood']) ? $addr['neighbourhood'] : ''),
            'plaque'   => isset($addr['house_number']) ? $addr['house_number'] : '',
            'unit'     => '',
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
        'plaque'   => isset($body['plaque']) ? $body['plaque'] : (isset($body['plak']) ? $body['plak'] : ''),
        'unit'     => isset($body['unit']) ? $body['unit'] : (isset($body['vahed']) ? $body['vahed'] : ''),
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
// Auth: Login / Registration with SMS OTP (Melipayamak) — custom AJAX, no plugins
// ============================================================================

/**
 * Melipayamak credentials (override with constants or the pzh_sms_credentials filter)
 */
function pzh_sms_credentials() {
    return apply_filters('pzh_sms_credentials', array(
        'username' => defined('PZH_SMS_USERNAME') ? PZH_SMS_USERNAME : '09127772167',
        'password' => defined('PZH_SMS_PASSWORD') ? PZH_SMS_PASSWORD : 'aa601577-a7ad-436d-9d42-4a2de9bfd2de',
        'sender'   => defined('PZH_SMS_SENDER') ? PZH_SMS_SENDER : '50002710072167',
        'pattern'  => defined('PZH_SMS_PATTERN') ? PZH_SMS_PATTERN : '186253',
    ));
}

/**
 * Normalize an Iranian mobile number to 09xxxxxxxxx (Persian digits, +98, spaces)
 */
function pzh_normalize_phone($phone) {
    $phone = strtr(trim((string) $phone), array(
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ));
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (strlen($phone) === 10 && $phone[0] === '9') $phone = '0' . $phone;
    if (strlen($phone) === 12 && substr($phone, 0, 2) === '98') $phone = '0' . substr($phone, 2);
    if (strlen($phone) === 13 && substr($phone, 0, 3) === '980') $phone = '0' . substr($phone, 3);

    return preg_match('/^09[0-9]{9}$/', $phone) ? $phone : '';
}

/**
 * Send the OTP code through the Melipayamak pattern (bodyId)
 */
function pzh_send_sms_otp($phone, $code) {
    $creds = pzh_sms_credentials();

    $resp = wp_remote_post('https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber', array(
        'timeout' => 15,
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => wp_json_encode(array(
            'username' => $creds['username'],
            'password' => $creds['password'],
            'text'     => (string) $code,
            'to'       => $phone,
            'bodyId'   => intval($creds['pattern']),
        )),
    ));

    if (is_wp_error($resp)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($resp), true);
    return !empty($body) && isset($body['RetStatus']) && intval($body['RetStatus']) === 1;
}

// --- OTP storage (hashed, with attempts + resend cooldown) ---

function pzh_otp_store_key($phone) {
    return 'pzh_otp_' . md5($phone);
}

function pzh_otp_set($phone, $code) {
    set_transient(pzh_otp_store_key($phone), array(
        'hash'     => wp_hash($code),
        'attempts' => 0,
        'verified' => false,
        'sent_at'  => time(),
    ), 5 * MINUTE_IN_SECONDS);
}

function pzh_otp_get($phone) {
    return get_transient(pzh_otp_store_key($phone));
}

function pzh_otp_attempts($phone) {
    $data = pzh_otp_get($phone);
    return $data ? intval($data['attempts'] ?? 0) : 0;
}

function pzh_otp_is_verified($phone) {
    $data = pzh_otp_get($phone);
    return ($data && !empty($data['verified']));
}

function pzh_otp_delete($phone) {
    delete_transient(pzh_otp_store_key($phone));
}

/**
 * Check the entered code. On success marks the phone verified for 10 minutes.
 */
function pzh_otp_verify($phone, $code) {
    $data = pzh_otp_get($phone);
    if (!$data || empty($data['hash'])) {
        return false;
    }

    if (hash_equals($data['hash'], wp_hash($code))) {
        $data['verified'] = true;
        set_transient(pzh_otp_store_key($phone), $data, 10 * MINUTE_IN_SECONDS);
        return true;
    }

    $data['attempts'] = intval($data['attempts'] ?? 0) + 1;
    set_transient(pzh_otp_store_key($phone), $data, 5 * MINUTE_IN_SECONDS);
    return false;
}

/**
 * URL of the login/registration page (the page using the page-login.php template)
 */
function pzh_auth_page_url() {
    $pages = get_pages(array(
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'page-login.php',
        'number'     => 1,
    ));
    if (!empty($pages)) {
        return get_permalink($pages[0]);
    }
    return class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : home_url('/login/');
}

/**
 * Redirect target after successful login/registration
 */
function pzh_auth_redirect_url() {
    if (!empty($_REQUEST['redirect_to'])) {
        return esc_url_raw(wp_unslash($_REQUEST['redirect_to']));
    }
    return class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : home_url('/');
}

/**
 * AJAX: request an OTP (also used for resend)
 */
function pzh_auth_send_otp() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $phone = pzh_normalize_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
    if (!$phone) {
        wp_send_json_error(array('message' => __('شماره موبایل معتبر نیست.', 'piazhen')));
    }

    // Resend cooldown (60s)
    $existing = pzh_otp_get($phone);
    if ($existing && !empty($existing['sent_at']) && (time() - intval($existing['sent_at'])) < 60) {
        $wait = 60 - (time() - intval($existing['sent_at']));
        wp_send_json_error(array(
            'message'  => sprintf(__('لطفاً %s ثانیه صبر کنید و دوباره تلاش کنید.', 'piazhen'), pzh_fa_num($wait)),
            'cooldown' => $wait,
        ));
    }

    $code = str_pad((string) wp_rand(0, 99999), 5, '0', STR_PAD_LEFT);
    pzh_otp_set($phone, $code);

    $sent = pzh_send_sms_otp($phone, $code);
    if (!$sent) {
        pzh_otp_delete($phone);
        wp_send_json_error(array('message' => __('خطا در ارسال پیامک. لطفاً دوباره تلاش کنید.', 'piazhen')));
    }

    wp_send_json_success(array(
        'message' => __('کد تایید پیامک شد.', 'piazhen'),
        'phone'   => $phone,
    ));
}
add_action('wp_ajax_pzh_auth_send_otp', 'pzh_auth_send_otp');
add_action('wp_ajax_nopriv_pzh_auth_send_otp', 'pzh_auth_send_otp');

/**
 * AJAX: verify the OTP — logs in existing users, sends new users to the profile step
 */
function pzh_auth_verify_otp() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $phone = pzh_normalize_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
    $code  = trim(sanitize_text_field(isset($_POST['code']) ? $_POST['code'] : ''));

    if (!$phone || $code === '') {
        wp_send_json_error(array('message' => __('شماره یا کد نامعتبر است.', 'piazhen')));
    }

    if (pzh_otp_attempts($phone) >= 5) {
        wp_send_json_error(array('message' => __('تعداد تلاش‌های ناموفق زیاد است. لطفاً دوباره درخواست کد کنید.', 'piazhen')));
    }

    if (!pzh_otp_verify($phone, $code)) {
        $remaining = 5 - pzh_otp_attempts($phone);
        wp_send_json_error(array('message' => __('کد وارد شده صحیح نیست.', 'piazhen')));
    }

    // Existing user → log in directly
    $user = get_user_by('login', $phone);
    if ($user) {
        wp_set_auth_cookie($user->ID, true);
        do_action('wp_login', $user->user_login, $user);
        pzh_otp_delete($phone);
        wp_send_json_success(array(
            'next'     => 'done',
            'is_new'   => false,
            'redirect' => pzh_auth_redirect_url(),
        ));
    }

    // New user → profile completion step
    wp_send_json_success(array(
        'next'   => 'register',
        'is_new' => true,
    ));
}
add_action('wp_ajax_pzh_auth_verify_otp', 'pzh_auth_verify_otp');
add_action('wp_ajax_nopriv_pzh_auth_verify_otp', 'pzh_auth_verify_otp');

/**
 * AJAX: create the account after OTP verification
 */
function pzh_auth_register() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $phone = pzh_normalize_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
    if (!$phone || !pzh_otp_is_verified($phone)) {
        wp_send_json_error(array('message' => __('ابتدا کد تایید را وارد کنید.', 'piazhen')));
    }

    $first_name = sanitize_text_field(isset($_POST['first_name']) ? $_POST['first_name'] : '');
    $last_name  = sanitize_text_field(isset($_POST['last_name']) ? $_POST['last_name'] : '');
    $email      = sanitize_email(isset($_POST['email']) ? $_POST['email'] : '');
    $password   = isset($_POST['password']) ? $_POST['password'] : '';

    if (!$first_name || !$last_name) {
        wp_send_json_error(array('message' => __('نام و نام خانوادگی الزامی است.', 'piazhen')));
    }
    if (strlen($password) < 6) {
        wp_send_json_error(array('message' => __('رمز عبور باید حداقل ۶ کاراکتر باشد.', 'piazhen')));
    }

    $user_id = wp_insert_user(array(
        'user_login'      => $phone,
        'user_pass'       => $password,
        'user_email'      => $email ?: ($phone . '@piazhen.local'),
        'first_name'      => $first_name,
        'last_name'       => $last_name,
        'display_name'    => trim($first_name . ' ' . $last_name),
        'nickname'        => $first_name,
        'role'            => 'customer',
        'user_registered' => current_time('mysql'),
    ));

    if (is_wp_error($user_id)) {
        wp_send_json_error(array('message' => $user_id->get_error_message()));
    }

    update_user_meta($user_id, 'billing_phone', $phone);
    pzh_otp_delete($phone);

    $user = get_user_by('id', $user_id);
    wp_set_auth_cookie($user_id, true);
    do_action('wp_login', $user->user_login, $user);

    wp_send_json_success(array(
        'message'  => __('حساب کاربری شما با موفقیت ساخته شد.', 'piazhen'),
        'redirect' => pzh_auth_redirect_url(),
    ));
}
add_action('wp_ajax_pzh_auth_register', 'pzh_auth_register');
add_action('wp_ajax_nopriv_pzh_auth_register', 'pzh_auth_register');

// ============================================================================
// Dashboard (My Account) — custom AJAX sections, no plugins
// ============================================================================

/**
 * Sidebar menu definition (section slug, Persian label, icon SVG)
 */
function pzh_dashboard_menu() {
    return array(
        'dashboard'  => array('label' => __('داشبورد', 'piazhen'),           'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/></svg>'),
        'account'    => array('label' => __('اطلاعات کاربری', 'piazhen'),     'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>'),
        'addresses'  => array('label' => __('آدرس‌های من', 'piazhen'),        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.6"/></svg>'),
        'reviews'    => array('label' => __('نظرات', 'piazhen'),              'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a8 8 0 0 1-8 8H4l2.5-2.5A8 8 0 1 1 21 12z"/></svg>'),
        'orders'     => array('label' => __('سفارش‌ها', 'piazhen'),           'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>'),
        'favorites'  => array('label' => __('کالاهای مورد علاقه', 'piazhen'), 'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>'),
        'wallet'     => array('label' => __('کیف پول', 'piazhen'),           'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/><path d="M16 15h2"/></svg>'),
    );
}

/**
 * Wallet helpers (lightweight custom wallet stored in user meta)
 */
function pzh_wallet_balance($user_id = 0) {
    $uid = $user_id ?: get_current_user_id();
    return intval(get_user_meta($uid, 'pzh_wallet_balance', true));
}

function pzh_wallet_transactions($user_id = 0, $limit = 20) {
    $uid = $user_id ?: get_current_user_id();
    $tx  = get_user_meta($uid, 'pzh_wallet_transactions', true);
    if (!is_array($tx)) return array();
    return array_slice(array_reverse($tx), 0, $limit);
}

/**
 * Order status groups for the orders filter tabs
 */
function pzh_order_status_groups() {
    return array(
        'jari'      => array('pending', 'processing', 'on-hold'),
        'delivered' => array('completed'),
        'canceled'  => array('cancelled', 'failed'),
        'refunded'  => array('refunded'),
    );
}

/**
 * Map a WC order status to a Persian label
 */
function pzh_order_status_label($status) {
    $labels = array(
        'pending'    => __('در انتظار پرداخت', 'piazhen'),
        'processing' => __('در حال پردازش', 'piazhen'),
        'on-hold'    => __('در انتظار بررسی', 'piazhen'),
        'completed'  => __('تحویل شده', 'piazhen'),
        'cancelled'  => __('لغو شده', 'piazhen'),
        'failed'     => __('ناموفق', 'piazhen'),
        'refunded'   => __('مرجوع شده', 'piazhen'),
    );
    return isset($labels[$status]) ? $labels[$status] : $status;
}

/**
 * Has the user already reviewed this product?
 */
function pzh_user_reviewed($user_id, $product_id) {
    $count = get_comments(array(
        'user_id' => $user_id,
        'post_id' => $product_id,
        'count'   => true,
    ));
    return $count > 0;
}

/**
 * Products from the user's delivered orders that are still reviewable
 */
function pzh_get_reviewable_items($user_id, $limit = 10) {
    $orders = wc_get_orders(array(
        'customer_id' => $user_id,
        'status'      => 'completed',
        'limit'       => 20,
        'orderby'     => 'date',
        'order'       => 'DESC',
    ));

    $items = array();
    foreach ($orders as $order) {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (!$product_id || isset($items[$product_id])) continue;
            if (pzh_user_reviewed($user_id, $product_id)) continue;
            $product = wc_get_product($product_id);
            if (!$product) continue;
            $items[$product_id] = array('product' => $product, 'order' => $order, 'item' => $item);
            if (count($items) >= $limit) break 2;
        }
    }
    return $items;
}

/**
 * The order/product row card shared by the reviews and orders sections
 */
function pzh_render_order_row($args) {
    $product     = $args['product'];
    $order       = $args['order'];
    $product_id  = $product->get_id();
    $thumb       = $product->get_image('pzh_product_thumb');
    $subtitle    = $product->get_short_description() ? wp_strip_all_tags($product->get_short_description()) : $product->get_sku();
    $order_total = isset($args['total_html']) ? $args['total_html'] : $order->get_formatted_order_total();
    ?>
    <div class="dash-row-card">
        <div class="dash-row-card__thumb">
            <?php echo $thumb; ?>
        </div>
        <div class="dash-row-card__main">
            <div class="dash-row-card__title"><?php echo esc_html($product->get_name()); ?></div>
            <?php if ($subtitle): ?>
                <div class="dash-row-card__subtitle"><?php echo esc_html($subtitle); ?></div>
            <?php endif; ?>
            <div class="dash-row-card__rule"></div>
            <div class="dash-row-card__specs">
                <div class="dash-spec">
                    <span class="dash-spec__label"><?php _e('تاریخ ثبت', 'piazhen'); ?></span>
                    <span class="dash-spec__value"><?php echo get_the_date('Y/m/d', $order->get_id()); ?></span>
                </div>
                <div class="dash-spec">
                    <span class="dash-spec__label"><?php _e('شماره سفارش', 'piazhen'); ?></span>
                    <span class="dash-spec__value"><?php echo pzh_fa_num($order->get_order_number()); ?></span>
                </div>
                <div class="dash-spec">
                    <span class="dash-spec__label"><?php _e('قیمت', 'piazhen'); ?></span>
                    <span class="dash-spec__value"><?php echo wp_kses_post($order_total); ?></span>
                </div>
            </div>
            <div class="dash-row-card__rule"></div>
            <div class="dash-spec dash-spec--status">
                <span class="dash-spec__label"><?php _e('وضعیت', 'piazhen'); ?></span>
                <span class="dash-spec__value dash-status"><?php echo esc_html($args['status_label']); ?></span>
            </div>
        </div>
        <?php if (!empty($args['after'])): ?>
            <div class="dash-row-card__after">
                <?php echo $args['after']; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render a dashboard section (shared by the page template and the AJAX handler)
 */
function pzh_render_account_section($section, $params = array()) {
    $user = wp_get_current_user();
    $uid  = get_current_user_id();

    $title_icon = '';
    $title_text = '';
    $menu = pzh_dashboard_menu();
    if (isset($menu[$section])) {
        $title_icon = $menu[$section]['icon'];
        $title_text = $menu[$section]['label'];
    }

    ob_start();

    echo '<header class="dash-section-title">' . $title_icon . '<h2>' . esc_html($title_text) . '</h2></header>';

    switch ($section) {

        // ------------------------------------------------------------ dashboard
        case 'dashboard':
            $tiles = array('favorites', 'orders', 'account', 'wallet', 'reviews', 'addresses');
            echo '<div class="dash-tiles">';
            foreach ($tiles as $slug) {
                echo '<button type="button" class="dash-tile" data-section="' . esc_attr($slug) . '">';
                echo '<span class="dash-tile__icon">' . $menu[$slug]['icon'] . '</span>';
                echo '<span class="dash-tile__label">' . esc_html($menu[$slug]['label']) . '</span>';
                echo '</button>';
            }
            echo '</div>';
            break;

        // ------------------------------------------------------------ account
        case 'account':
            $states = function_exists('WC') ? WC()->countries->get_states('IR') : array();
            ?>
            <form class="dash-form dash-account-form" data-form="account">
                <div class="dash-form__grid">
                    <div class="dash-field">
                        <label><?php _e('نام', 'piazhen'); ?> <span class="req">*</span></label>
                        <input type="text" name="first_name" value="<?php echo esc_attr(get_user_meta($uid, 'billing_first_name', true) ?: $user->first_name); ?>" placeholder="<?php _e('نام', 'piazhen'); ?>">
                    </div>
                    <div class="dash-field">
                        <label><?php _e('نام خانوادگی', 'piazhen'); ?> <span class="req">*</span></label>
                        <input type="text" name="last_name" value="<?php echo esc_attr(get_user_meta($uid, 'billing_last_name', true) ?: $user->last_name); ?>" placeholder="<?php _e('نام خانوادگی', 'piazhen'); ?>">
                    </div>
                    <div class="dash-field">
                        <label><?php _e('نام شرکت', 'piazhen'); ?></label>
                        <input type="text" name="company" value="<?php echo esc_attr(get_user_meta($uid, 'billing_company', true)); ?>" placeholder="<?php _e('نام شرکت', 'piazhen'); ?>">
                    </div>
                    <div class="dash-field">
                        <label><?php _e('شماره تماس', 'piazhen'); ?> <span class="req">*</span></label>
                        <input type="tel" name="phone" value="<?php echo esc_attr(get_user_meta($uid, 'billing_phone', true)); ?>" placeholder="0912 345 6789" dir="ltr">
                    </div>
                    <div class="dash-field">
                        <label><?php _e('ایمیل', 'piazhen'); ?></label>
                        <input type="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" placeholder="example@mail.com" dir="ltr">
                    </div>
                    <div class="dash-field">
                        <label><?php _e('کد پستی', 'piazhen'); ?> <span class="req">*</span></label>
                        <input type="text" name="postcode" value="<?php echo esc_attr(get_user_meta($uid, 'billing_postcode', true)); ?>" placeholder="1234567890" dir="ltr">
                    </div>
                    <div class="dash-field">
                        <label><?php _e('استان', 'piazhen'); ?> <span class="req">*</span></label>
                        <select name="state">
                            <option value=""><?php _e('انتخاب کنید', 'piazhen'); ?></option>
                            <?php foreach ($states as $code => $name): ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected(get_user_meta($uid, 'billing_state', true), $code); ?>><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="dash-field">
                        <label><?php _e('شهر', 'piazhen'); ?> <span class="req">*</span></label>
                        <input type="text" name="city" value="<?php echo esc_attr(get_user_meta($uid, 'billing_city', true)); ?>" placeholder="<?php _e('شهر', 'piazhen'); ?>">
                    </div>
                    <div class="dash-field dash-field--full">
                        <label><?php _e('آدرس', 'piazhen'); ?> <span class="req">*</span></label>
                        <textarea name="address_1" rows="4" placeholder="<?php _e('خیابان،کوچه، پلاک، واحد…', 'piazhen'); ?>"><?php echo esc_textarea(get_user_meta($uid, 'billing_address_1', true)); ?></textarea>
                    </div>
                </div>
                <div class="dash-form__actions">
                    <button type="submit" class="dash-btn dash-btn--submit dash-account-save"><?php _e('ثبت تغییرات', 'piazhen'); ?></button>
                </div>
            </form>
            <?php
            break;

        // ------------------------------------------------------------ addresses
        case 'addresses':
            $addresses = array(
                'billing'  => __('آدرس صورتحساب', 'piazhen'),
                'shipping' => __('آدرس ارسال', 'piazhen'),
            );
            $states = WC()->countries->get_states('IR');
            $has_any = false;
            $empty_slot = '';
            foreach ($addresses as $type => $label) {
                $address = array(
                    'first_name' => get_user_meta($uid, $type . '_first_name', true),
                    'last_name'  => get_user_meta($uid, $type . '_last_name', true),
                    'state'      => get_user_meta($uid, $type . '_state', true),
                    'city'       => get_user_meta($uid, $type . '_city', true),
                    'address_1'  => get_user_meta($uid, $type . '_address_1', true),
                    'address_2'  => get_user_meta($uid, $type . '_address_2', true),
                    'postcode'   => get_user_meta($uid, $type . '_postcode', true),
                    'phone'      => get_user_meta($uid, $type . '_phone', true),
                );

                // Resolve numeric state/city codes (PWS) to names for display
                $state_name = isset($states[$address['state']]) ? $states[$address['state']] : $address['state'];
                $city_name  = $address['city'];
                if (is_numeric($address['city']) && function_exists('PWS') && method_exists(PWS(), 'get_city')) {
                    $resolved = PWS()->get_city($address['city']);
                    if ($resolved) $city_name = $resolved;
                }

                $formatted = trim(implode('، ', array_filter(array($state_name, $city_name, $address['address_1'], $address['address_2']))));
                if (!$formatted) {
                    if (!$empty_slot) $empty_slot = $type;
                    continue;
                }
                $has_any = true;
                ?>
                <div class="dash-address-card" data-address-type="<?php echo esc_attr($type); ?>">
                    <div class="dash-address-card__text">
                        <span class="dash-address-card__label"><?php echo esc_html($label); ?>:</span>
                        <span class="dash-address-card__value"><?php echo esc_html($formatted); ?></span>
                    </div>
                    <button type="button" class="dash-link dash-address-edit" data-address-type="<?php echo esc_attr($type); ?>">
                        <?php _e('ویرایش آدرس', 'piazhen'); ?>
                    </button>
                </div>
                <form class="dash-form dash-address-form" data-address-type="<?php echo esc_attr($type); ?>" hidden>
                    <div class="dash-form__grid">
                        <div class="dash-field">
                            <label><?php _e('استان', 'piazhen'); ?></label>
                            <select name="state">
                                <option value=""><?php _e('انتخاب کنید', 'piazhen'); ?></option>
                                <?php
                                $states = WC()->countries->get_states('IR');
                                foreach ($states as $code => $name): ?>
                                    <option value="<?php echo esc_attr($code); ?>" <?php selected($address['state'], $code); ?>><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="dash-field">
                            <label><?php _e('شهر', 'piazhen'); ?></label>
                            <input type="text" name="city" value="<?php echo esc_attr($address['city']); ?>">
                        </div>
                        <div class="dash-field dash-field--full">
                            <label><?php _e('آدرس', 'piazhen'); ?></label>
                            <textarea name="address_1" rows="2"><?php echo esc_textarea($address['address_1']); ?></textarea>
                        </div>
                        <div class="dash-field">
                            <label><?php _e('پلاک', 'piazhen'); ?></label>
                            <input type="text" name="plaque" value="<?php echo esc_attr(get_user_meta($uid, $type . '_plaque', true)); ?>" placeholder="<?php _e('پلاک ۱۲', 'piazhen'); ?>">
                        </div>
                        <div class="dash-field">
                            <label><?php _e('واحد', 'piazhen'); ?></label>
                            <input type="text" name="unit" value="<?php echo esc_attr(get_user_meta($uid, $type . '_unit', true)); ?>" placeholder="<?php _e('واحد ۳', 'piazhen'); ?>">
                        </div>
                        <div class="dash-field">
                            <label><?php _e('کد پستی', 'piazhen'); ?></label>
                            <input type="text" name="postcode" value="<?php echo esc_attr($address['postcode']); ?>" dir="ltr">
                        </div>
                        <div class="dash-field">
                            <label><?php _e('تلفن', 'piazhen'); ?></label>
                            <input type="tel" name="phone" value="<?php echo esc_attr($address['phone']); ?>" dir="ltr">
                        </div>
                    </div>
                    <div class="dash-form__actions">
                        <button type="submit" class="dash-btn dash-btn--submit"><?php _e('ذخیره آدرس', 'piazhen'); ?></button>
                    </div>
                </form>
                <?php
            }
            if (!$has_any) {
                echo '<div class="dash-empty"><p>' . __('هنوز آدرسی ثبت نکرده‌اید.', 'piazhen') . '</p></div>';
            }
            echo '<button type="button" class="dash-link dash-address-add" data-add-type="' . esc_attr($empty_slot) . '">' . __('افزودن آدرس', 'piazhen') . '</button>';
            break;

        // ------------------------------------------------------------ reviews
        case 'reviews':
            $items = pzh_get_reviewable_items($uid, 10);
            if (empty($items)) {
                echo '<div class="dash-empty"><p>' . __('محصولی برای ثبت نظر وجود ندارد.', 'piazhen') . '</p></div>';
            }
            foreach ($items as $product_id => $data) {
                pzh_render_order_row(array(
                    'product'      => $data['product'],
                    'order'        => $data['order'],
                    'total_html'   => $data['order']->get_formatted_line_subtotal($data['item']),
                    'status_label' => __('تحویل شده', 'piazhen'),
                ));
                ?>
                <form class="dash-review-form" data-product-id="<?php echo intval($product_id); ?>">
                    <div class="dash-review-form__row">
                        <input type="text" name="comment" class="dash-review-input" placeholder="<?php _e('بنویسید...', 'piazhen'); ?>">
                        <button type="submit" class="dash-btn dash-btn--submit"><?php _e('ثبت نظر', 'piazhen'); ?></button>
                    </div>
                </form>
                <?php
            }
            break;

        // ------------------------------------------------------------ orders
        case 'orders':
            $groups = array(
                'jari'      => __('جاری', 'piazhen'),
                'delivered' => __('تحویل شده', 'piazhen'),
                'canceled'  => __('لغو شده', 'piazhen'),
                'refunded'  => __('مرجوعی', 'piazhen'),
            );
            $current = isset($params['status']) && isset($groups[$params['status']]) ? $params['status'] : 'jari';

            echo '<div class="dash-orders-tabs">';
            foreach ($groups as $key => $label) {
                $count = count(wc_get_orders(array(
                    'customer_id' => $uid,
                    'status'      => pzh_order_status_groups()[$key],
                    'limit'       => -1,
                    'return'      => 'ids',
                    'meta_query'  => pzh_wallet_topup_meta_query(),
                )));
                $active = ($key === $current) ? ' active' : '';
                echo '<button type="button" class="dash-orders-tab' . $active . '" data-status="' . esc_attr($key) . '">';
                echo esc_html($label) . ' (' . pzh_fa_num($count) . ')';
                echo '</button>';
            }
            echo '</div>';

            $orders = wc_get_orders(array(
                'customer_id' => $uid,
                'status'      => pzh_order_status_groups()[$current],
                'limit'       => 10,
                'orderby'     => 'date',
                'order'       => 'DESC',
                'meta_query'  => pzh_wallet_topup_meta_query(),
            ));

            if (empty($orders)) {
                echo '<div class="dash-empty"><p>' . __('سفارشی در این بخش وجود ندارد.', 'piazhen') . '</p></div>';
            }
            foreach ($orders as $order) {
                $first_item = null;
                foreach ($order->get_items() as $item) { $first_item = $item; break; }
                if (!$first_item) continue;
                $product = $first_item->get_product();
                if (!$product) continue;
                pzh_render_order_row(array(
                    'product'      => $product,
                    'order'        => $order,
                    'status_label' => pzh_order_status_label($order->get_status()),
                ));
            }
            break;

        // ------------------------------------------------------------ favorites
        case 'favorites':
            $favorites = get_user_meta($uid, 'pzh_favorites', true);
            if (!is_array($favorites)) $favorites = array();
            if (empty($favorites)) {
                echo '<div class="dash-empty"><p>' . __('هنوز کالایی به علاقه‌مندی‌ها اضافه نکرده‌اید.', 'piazhen') . '</p></div>';
            }
            foreach (array_reverse($favorites) as $product_id) {
                $product = wc_get_product($product_id);
                if (!$product) continue;
                $attributes = $product->get_attributes();
                ?>
                <div class="dash-row-card dash-row-card--favorite" data-fav-id="<?php echo intval($product_id); ?>">
                    <div class="dash-row-card__thumb dash-row-card__thumb--fav">
                        <?php echo $product->get_image('pzh_product_thumb'); ?>
                        <button type="button" class="dash-fav-badge active" data-product-id="<?php echo intval($product_id); ?>" aria-label="<?php esc_attr_e('حذف از علاقه‌مندی‌ها', 'piazhen'); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="1"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </button>
                    </div>
                    <div class="dash-row-card__main">
                        <div class="dash-row-card__title"><?php echo esc_html($product->get_name()); ?></div>
                        <?php $sub = $product->get_short_description() ? wp_strip_all_tags($product->get_short_description()) : $product->get_sku(); ?>
                        <?php if ($sub): ?><div class="dash-row-card__subtitle"><?php echo esc_html($sub); ?></div><?php endif; ?>
                        <div class="dash-row-card__rule"></div>
                        <div class="dash-row-card__specs">
                            <?php $i = 0; foreach ($attributes as $attribute):
                                if ($attribute->get_variation()) continue;
                                if ($i++ >= 2) break;
                                $value = $attribute->is_taxonomy()
                                    ? implode('، ', wc_get_product_terms($product_id, $attribute->get_name(), array('fields' => 'names')))
                                    : implode('، ', $attribute->get_options());
                                if (!$value) continue;
                                ?>
                                <div class="dash-spec">
                                    <span class="dash-spec__label"><?php echo esc_html(wc_attribute_label($attribute->get_name())); ?></span>
                                    <span class="dash-spec__value"><?php echo esc_html($value); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <div class="dash-spec">
                                <span class="dash-spec__label"><?php _e('قیمت', 'piazhen'); ?></span>
                                <span class="dash-spec__value"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                            </div>
                        </div>
                        <div class="dash-row-card__rule"></div>
                        <div class="dash-fav-actions">
                            <?php if ($product->is_type('variable')): ?>
                                <!-- Variable products open the variation popup (same flow as the archive) -->
                                <button type="button" class="dash-btn dash-btn--atc product-card__add-to-cart--variable"
                                        data-product-id="<?php echo intval($product_id); ?>">
                                    <?php _e('افزودن به سبد خرید', 'piazhen'); ?>
                                </button>
                            <?php else: ?>
                                <button type="button" class="dash-btn dash-btn--atc dashboard-add-to-cart"
                                        data-product-id="<?php echo intval($product_id); ?>">
                                    <?php _e('افزودن به سبد خرید', 'piazhen'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            break;

        // ------------------------------------------------------------ wallet
        case 'wallet':
            $balance = pzh_wallet_balance($uid);
            ?>
            <div class="dash-wallet">
                <div class="dash-wallet__balance">
                    <span class="dash-wallet__balance-label"><?php _e('موجودی شما', 'piazhen'); ?></span>
                    <span class="dash-wallet__balance-amount"><?php echo pzh_fa_num(number_format($balance)) . ' ' . __('تومان', 'piazhen'); ?></span>
                </div>

                <div class="dash-wallet__tiles">
                    <button type="button" class="dash-tile wallet-tile" data-action="charge">
                        <span class="dash-tile__icon"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/><path d="M16 15h2"/></svg></span>
                        <span class="dash-tile__label"><?php _e('شارژ کیف پول', 'piazhen'); ?></span>
                    </button>
                    <button type="button" class="dash-tile wallet-tile" data-action="scan">
                        <span class="dash-tile__icon"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M7 9h3M7 15h3M14 9h3M14 15h3"/></svg></span>
                        <span class="dash-tile__label"><?php _e('اسکن', 'piazhen'); ?></span>
                    </button>
                    <button type="button" class="dash-tile wallet-tile" data-action="qr">
                        <span class="dash-tile__icon"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3"/></svg></span>
                        <span class="dash-tile__label"><?php _e('کد QR', 'piazhen'); ?></span>
                    </button>
                    <button type="button" class="dash-tile wallet-tile" data-action="transfer" data-balance="<?php echo intval($balance); ?>">
                        <span class="dash-tile__icon"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM15 14h1v1h-1zM18 14h1v1h-1zM15 17h1v1h-1zM18 17h1v1h-1zM16 16h2v2h-2z"/></svg></span>
                        <span class="dash-tile__label"><?php _e('انتقال وجه', 'piazhen'); ?></span>
                    </button>
                </div>

                <div class="dash-wallet__rule"></div>
                <h3 class="dash-wallet__transactions-title"><?php _e('آخرین تراکنش‌ها', 'piazhen'); ?></h3>

                <?php $transactions = pzh_wallet_transactions($uid, 10); ?>
                <?php if (empty($transactions)): ?>
                    <div class="dash-empty"><p><?php _e('تراکنشی ثبت نشده است.', 'piazhen'); ?></p></div>
                <?php else: ?>
                    <div class="dash-transactions">
                        <?php foreach ($transactions as $tx): ?>
                            <div class="dash-transaction">
                                <div class="dash-transaction__main">
                                    <div class="dash-transaction__title"><?php echo esc_html($tx['title'] ?? ''); ?></div>
                                    <div class="dash-transaction__desc"><?php echo esc_html($tx['desc'] ?? ''); ?></div>
                                </div>
                                <div class="dash-transaction__side">
                                    <div class="dash-transaction__amount"><?php echo pzh_fa_num(number_format(intval($tx['amount'] ?? 0))); ?> <?php _e('تومان', 'piazhen'); ?></div>
                                    <div class="dash-transaction__status <?php echo esc_attr($tx['status'] ?? 'success'); ?>">
                                        <?php
                                        $tx_status = $tx['status'] ?? 'success';
                                        if ($tx_status === 'failed') {
                                            _e('ناموفق', 'piazhen');
                                        } elseif ($tx_status === 'pending') {
                                            _e('در انتظار', 'piazhen');
                                        } else {
                                            _e('موفق', 'piazhen');
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            break;
    }

    return ob_get_clean();
}

/**
 * AJAX: charge the wallet — creates a top-up order and redirects to the
 * payment gateway (zarinpal / WC_ZPal).
 */
function pzh_wallet_charge() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('لطفاً وارد حساب کاربری شوید.', 'piazhen')));
    }

    $amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
    if ($amount < 10000 || $amount > 100000000) {
        wp_send_json_error(array('message' => __('مبلغ شارژ نامعتبر است. (حداقل ۱۰٬۰۰۰ تومان)', 'piazhen')));
    }

    $uid   = get_current_user_id();
    $order = wc_create_order();
    $order->set_customer_id($uid);
    $order->set_payment_method('WC_ZPal');
    $order->set_status('pending');

    $fee = new WC_Order_Item_Fee();
    $fee->set_name(__('شارژ کیف پول', 'piazhen'));
    $fee->set_total($amount);
    $order->add_item($fee);

    $order->update_meta_data('_pzh_wallet_topup', '1');
    $order->calculate_totals();
    $order->save();

    wc_maybe_define_constant('WOOCOMMERCE_CHECKOUT', true);

    $gateways = WC()->payment_gateways()->get_available_payment_gateways();
    $gateway  = isset($gateways['WC_ZPal']) ? $gateways['WC_ZPal'] : null;

    if (!$gateway) {
        $order->delete(true);
        wp_send_json_error(array('message' => __('درگاه پرداخت در دسترس نیست.', 'piazhen')));
    }

    try {
        $result = $gateway->process_payment($order->get_id());
    } catch (Exception $e) {
        $order->delete(true);
        wp_send_json_error(array('message' => $e->getMessage()));
    }

    if (!empty($result['result']) && $result['result'] === 'success' && !empty($result['redirect'])) {
        wp_send_json_success(array(
            'message'  => __('در حال انتقال به درگاه پرداخت...', 'piazhen'),
            'redirect' => $result['redirect'],
        ));
    }

    $order->delete(true);
    wp_send_json_error(array(
        'message' => !empty($result['messages']) ? wp_strip_all_tags($result['messages']) : __('خطا در اتصال به درگاه پرداخت.', 'piazhen'),
    ));
}
add_action('wp_ajax_pzh_wallet_charge', 'pzh_wallet_charge');

/**
 * Credit the wallet when a top-up order gets paid (runs once per order)
 */
function pzh_wallet_credit_topup($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || !$order->get_meta('_pzh_wallet_topup')) return;
    if ($order->get_meta('_pzh_wallet_credited')) return;

    $uid = intval($order->get_customer_id());
    if (!$uid) return;

    $amount  = intval(round($order->get_total()));
    $balance = pzh_wallet_balance($uid);
    update_user_meta($uid, 'pzh_wallet_balance', $balance + $amount);

    $tx = get_user_meta($uid, 'pzh_wallet_transactions', true);
    if (!is_array($tx)) $tx = array();
    $tx[] = array(
        'title'  => __('شارژ کیف پول', 'piazhen'),
        'desc'   => __('درگاه پرداخت اینترنتی', 'piazhen'),
        'amount' => $amount,
        'status' => 'success',
        'date'   => current_time('mysql'),
    );
    update_user_meta($uid, 'pzh_wallet_transactions', $tx);

    $order->update_meta_data('_pzh_wallet_credited', '1');
    $order->add_order_note(sprintf(__('کیف پول کاربر به مبلغ %s تومان شارژ شد.', 'piazhen'), number_format($amount)));
    $order->save();
}
add_action('woocommerce_order_status_completed', 'pzh_wallet_credit_topup');
add_action('woocommerce_order_status_processing', 'pzh_wallet_credit_topup');

/**
 * Exclude wallet top-up orders from the customer's order lists
 */
function pzh_wallet_topup_meta_query($meta_query = array()) {
    $meta_query[] = array(
        'relation' => 'OR',
        array('key' => '_pzh_wallet_topup', 'compare' => 'NOT EXISTS'),
        array('key' => '_pzh_wallet_topup', 'value' => '1', 'compare' => '!='),
    );
    return $meta_query;
}

/**
 * AJAX: wallet withdrawal request — holds the amount and queues it for admin
 */
function pzh_wallet_withdraw() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('لطفاً وارد حساب کاربری شوید.', 'piazhen')));
    }

    $uid    = get_current_user_id();
    $amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
    $balance = pzh_wallet_balance($uid);

    if ($amount < 10000) {
        wp_send_json_error(array('message' => __('مبلغ انتقال نامعتبر است. (حداقل ۱۰٬۰۰۰ تومان)', 'piazhen')));
    }
    if ($amount > $balance) {
        wp_send_json_error(array('message' => __('موجودی کیف پول کافی نیست.', 'piazhen')));
    }

    $bank_name    = isset($_POST['bank_name']) ? sanitize_text_field(wp_unslash($_POST['bank_name'])) : '';
    $account_name = isset($_POST['account_name']) ? sanitize_text_field(wp_unslash($_POST['account_name'])) : '';
    $card_number  = isset($_POST['card_number']) ? sanitize_text_field(wp_unslash($_POST['card_number'])) : '';
    $account_number = isset($_POST['account_number']) ? sanitize_text_field(wp_unslash($_POST['account_number'])) : '';
    $iban         = isset($_POST['iban']) ? sanitize_text_field(wp_unslash($_POST['iban'])) : '';

    // Normalize Persian digits in numeric fields
    $fa_to_en = array('۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9');
    $card_number   = strtr($card_number, $fa_to_en);
    $account_number = strtr($account_number, $fa_to_en);
    $iban          = strtr(strtoupper($iban), $fa_to_en);

    if (!$bank_name || !$account_name) {
        wp_send_json_error(array('message' => __('نام بانک و نام صاحب حساب الزامی است.', 'piazhen')));
    }
    if ($card_number === '' && $account_number === '' && $iban === '') {
        wp_send_json_error(array('message' => __('شماره کارت، شماره حساب یا شبا را وارد کنید.', 'piazhen')));
    }

    // Hold the amount
    update_user_meta($uid, 'pzh_wallet_balance', $balance - $amount);

    $tx = get_user_meta($uid, 'pzh_wallet_transactions', true);
    if (!is_array($tx)) $tx = array();
    $tx[] = array(
        'title'  => __('برداشت از کیف پول', 'piazhen'),
        'desc'   => __('انتقال به کارت — در انتظار بررسی', 'piazhen'),
        'amount' => $amount,
        'status' => 'pending',
        'date'   => current_time('mysql'),
    );
    update_user_meta($uid, 'pzh_wallet_transactions', $tx);

    // Queue the request for the admin
    $requests = get_option('pzh_wallet_withdrawals', array());
    if (!is_array($requests)) $requests = array();
    $requests[] = array(
        'id'             => uniqid('wd_'),
        'user_id'        => $uid,
        'user_login'     => wp_get_current_user()->user_login,
        'amount'         => $amount,
        'bank_name'      => $bank_name,
        'account_name'   => $account_name,
        'card_number'    => $card_number,
        'account_number' => $account_number,
        'iban'           => $iban,
        'status'         => 'pending',
        'date'           => current_time('mysql'),
    );
    update_option('pzh_wallet_withdrawals', $requests);

    wp_send_json_success(array(
        'message' => __('درخواست برداشت ثبت شد و پس از بررسی مدیریت پرداخت می‌شود.', 'piazhen'),
        'html'    => pzh_render_account_section('wallet', array()),
    ));
}
add_action('wp_ajax_pzh_wallet_withdraw', 'pzh_wallet_withdraw');

/**
 * Admin page: wallet withdrawal requests (approve / reject + refund)
 */
function pzh_admin_withdrawals_menu() {
    add_menu_page(
        __('برداشت‌های کیف پول', 'piazhen'),
        __('برداشت کیف پول', 'piazhen'),
        'manage_options',
        'pzh-wallet-withdrawals',
        'pzh_admin_withdrawals_render',
        'dashicons-money-alt',
        58
    );
}
add_action('admin_menu', 'pzh_admin_withdrawals_menu');

// ============================================================================
// Free Delivery Settings (threshold + suggested products) — admin panel
// ============================================================================

function pzh_admin_free_delivery_menu() {
    add_submenu_page(
        'pzh-wallet-withdrawals',
        __('ارسال رایگان', 'piazhen'),
        __('ارسال رایگان', 'piazhen'),
        'manage_options',
        'pzh-free-delivery',
        'pzh_admin_free_delivery_render'
    );
}
add_action('admin_menu', 'pzh_admin_free_delivery_menu');

function pzh_admin_free_delivery_render() {
    // Save settings
    if (isset($_POST['pzh_fd_save']) && check_admin_referer('pzh_free_delivery_settings')) {
        $threshold = isset($_POST['pzh_fd_threshold']) ? absint($_POST['pzh_fd_threshold']) : 0;
        update_option('pzh_free_delivery_threshold', $threshold);

        $products = isset($_POST['pzh_fd_products']) ? array_map('absint', (array) $_POST['pzh_fd_products']) : array();
        $products = array_values(array_filter(array_unique($products)));
        update_option('pzh_free_delivery_products', $products);

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('تنظیمات ذخیره شد.', 'piazhen') . '</p></div>';
    }

    $threshold    = absint(get_option('pzh_free_delivery_threshold', 5000000));
    $selected_ids = array_map('absint', (array) get_option('pzh_free_delivery_products', array()));
    $products     = get_posts(array(
        'post_type'      => array('product', 'product_variation'),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));
    ?>
    <div class="wrap">
        <h1><?php _e('تنظیمات ارسال رایگان', 'piazhen'); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field('pzh_free_delivery_settings'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="pzh_fd_threshold"><?php _e('حداقل مبلغ برای ارسال رایگان (تومان)', 'piazhen'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="pzh_fd_threshold" name="pzh_fd_threshold" class="regular-text"
                               value="<?php echo esc_attr($threshold); ?>" min="0" step="1000" style="direction: ltr;">
                        <p class="description"><?php _e('مثلاً ۵۰۰۰۰۰۰ — اگر مجموع سبد کمتر از این مبلغ باشد، نوار پیشرفت و محصولات پیشنهادی نمایش داده می‌شود.', 'piazhen'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="pzh_fd_products"><?php _e('محصولات پیشنهادی', 'piazhen'); ?></label>
                    </th>
                    <td>
                        <select id="pzh_fd_products" name="pzh_fd_products[]" multiple size="12" style="min-width: 420px; max-width: 100%;">
                            <?php foreach ($products as $product_post): ?>
                                <?php $product = wc_get_product($product_post); ?>
                                <?php if (!$product || !$product->is_purchasable()) continue; ?>
                                <option value="<?php echo esc_attr($product_post->ID); ?>" <?php selected(in_array($product_post->ID, $selected_ids, true)); ?>>
                                    <?php echo esc_html($product->get_name()); ?> (#<?php echo $product_post->ID; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('برای انتخاب چند محصول، کلید Ctrl (یا Cmd) را نگه دارید. این محصولات وقتی سبد به حد ارسال رایگان نرسیده پیشنهاد می‌شوند.', 'piazhen'); ?></p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="pzh_fd_save" class="button button-primary"><?php _e('ذخیره تنظیمات', 'piazhen'); ?></button>
            </p>
        </form>
    </div>
    <?php
}

/**
 * Free delivery settings helper
 */
function pzh_free_delivery_data() {
    return array(
        'threshold'   => absint(get_option('pzh_free_delivery_threshold', 5000000)),
        'product_ids' => array_values(array_filter(array_map('absint', (array) get_option('pzh_free_delivery_products', array())))),
    );
}

/**
 * Render the free-delivery progress bar + suggested products (cart page top)
 * Per cart-free-delivery.png: yellow-bordered cream box, suggested product
 * cards with orange "+" badges on one side, delivery text + truck icon +
 * right-filling yellow progress bar on the other.
 */
function pzh_free_delivery_html() {
    $data      = pzh_free_delivery_data();
    $threshold = max(1, $data['threshold']);
    $total     = (float) WC()->cart->get_subtotal();
    $pct       = min(100, round($total / $threshold * 100, 1));
    $remaining = $total < $threshold ? $threshold - $total : 0;

    // Suggested products: admin-selected, purchasable, not already in cart — max 4
    $in_cart = array();
    foreach (WC()->cart->get_cart() as $item) {
        $in_cart[$item['product_id']] = true;
        if ($item['variation_id']) {
            $in_cart[$item['variation_id']] = true;
        }
    }
    $suggestions = array();
    foreach ($data['product_ids'] as $pid) {
        if (count($suggestions) >= 4) break;
        if (isset($in_cart[$pid])) continue;
        $sugg_product = wc_get_product($pid);
        if ($sugg_product && $sugg_product->is_purchasable() && $sugg_product->is_in_stock()) {
            $suggestions[] = $sugg_product;
        }
    }

    ob_start();
    ?>
    <div class="pz-free-delivery">
        <!-- Status column first in DOM → renders on the RIGHT (RTL), per the mockup -->
        <div class="pz-free-delivery__status">
            <div class="pz-free-delivery__text-row">
                <svg class="pz-free-delivery__truck" width="38" height="32" viewBox="0 0 39 33" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="1" width="25" height="26" rx="1"/>
                    <path d="M25 4h8l5 9v10"/>
                    <path d="M25 23h13"/>
                    <line x1="25" y1="14" x2="38" y2="14"/>
                    <circle cx="10.5" cy="26.5" r="4"/>
                    <circle cx="28.5" cy="26.5" r="4"/>
                </svg>
                <div class="pz-free-delivery__text">
                    <?php if ($remaining > 0): ?>
                        <?php /* translators: %s = formatted remaining amount for free delivery */ ?>
                        <?php printf(__('%s تا ارسال رایگان محصول', 'piazhen'), wc_price($remaining)); ?>
                    <?php else: ?>
                        <?php _e('این سفارش شامل ارسال رایگان است', 'piazhen'); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="pz-free-delivery__bar">
                <span class="pz-free-delivery__fill" style="width: <?php echo esc_attr($pct); ?>%"></span>
            </div>
        </div>

        <?php if (!empty($suggestions)): ?>
            <div class="pz-free-delivery__suggestions">
                <div class="pz-free-delivery__title">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="10" width="28" height="28" rx="4"/>
                        <path d="M15 17v14M8 24h14"/>
                        <path d="M10 1h29M38 1v29"/>
                    </svg>
                    <span><?php _e('محصولات پیشنهادی', 'piazhen'); ?></span>
                </div>
                <div class="pz-free-delivery__cards">
                    <?php foreach ($suggestions as $sugg): ?>
                        <div class="pz-fd-card">
                            <a class="pz-fd-card__image" href="<?php echo esc_url($sugg->get_permalink()); ?>">
                                <?php echo $sugg->get_image('pzh_product_thumb'); ?>
                            </a>
                            <button type="button"
                                    class="pz-fd-card__add <?php echo $sugg->is_type('variable') ? 'product-card__add-to-cart--variable' : 'product-card__add-to-cart'; ?>"
                                    data-product-id="<?php echo $sugg->get_id(); ?>"
                                    aria-label="<?php esc_attr_e('افزودن به سبد', 'piazhen'); ?>">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 1v10M1 6h10"/></svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function pzh_admin_withdrawals_render() {
    // Handle approve/reject actions
    if (isset($_GET['pzh_action'], $_GET['req_id']) && check_admin_referer('pzh_withdrawal_action')) {
        $action = sanitize_key($_GET['pzh_action']);
        $req_id = sanitize_text_field(wp_unslash($_GET['req_id']));

        $requests = get_option('pzh_wallet_withdrawals', array());
        foreach ($requests as &$req) {
            if ($req['id'] !== $req_id || $req['status'] !== 'pending') continue;

            if ($action === 'done') {
                $req['status'] = 'done';
            } elseif ($action === 'reject') {
                $req['status'] = 'rejected';
                // Refund the user
                $uid  = intval($req['user_id']);
                $balance = pzh_wallet_balance($uid);
                update_user_meta($uid, 'pzh_wallet_balance', $balance + intval($req['amount']));
                $tx = get_user_meta($uid, 'pzh_wallet_transactions', true);
                if (!is_array($tx)) $tx = array();
                $tx[] = array(
                    'title'  => __('برگشت برداشت', 'piazhen'),
                    'desc'   => __('رد درخواست برداشت', 'piazhen'),
                    'amount' => intval($req['amount']),
                    'status' => 'success',
                    'date'   => current_time('mysql'),
                );
                update_user_meta($uid, 'pzh_wallet_transactions', $tx);
            }
            unset($req);
            break;
        }
        update_option('pzh_wallet_withdrawals', $requests);
    }

    $requests = get_option('pzh_wallet_withdrawals', array());
    $labels = array('pending' => __('در انتظار', 'piazhen'), 'done' => __('پرداخت شده', 'piazhen'), 'rejected' => __('رد شده', 'piazhen'));
    ?>
    <div class="wrap">
        <h1><?php _e('درخواست‌های برداشت از کیف پول', 'piazhen'); ?></h1>
        <?php if (empty($requests)): ?>
            <p><?php _e('درخواستی ثبت نشده است.', 'piazhen'); ?></p>
        <?php else: ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('کاربر', 'piazhen'); ?></th>
                        <th><?php _e('مبلغ', 'piazhen'); ?></th>
                        <th><?php _e('بانک', 'piazhen'); ?></th>
                        <th><?php _e('صاحب حساب', 'piazhen'); ?></th>
                        <th><?php _e('کارت / حساب / شبا', 'piazhen'); ?></th>
                        <th><?php _e('تاریخ', 'piazhen'); ?></th>
                        <th><?php _e('وضعیت', 'piazhen'); ?></th>
                        <th><?php _e('عملیات', 'piazhen'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($requests) as $req): ?>
                        <tr>
                            <td><?php echo esc_html($req['user_login']); ?> (<?php echo intval($req['user_id']); ?>)</td>
                            <td><?php echo esc_html(number_format(intval($req['amount']))); ?></td>
                            <td><?php echo esc_html($req['bank_name']); ?></td>
                            <td><?php echo esc_html($req['account_name']); ?></td>
                            <td dir="ltr">
                                <?php echo esc_html(implode(' / ', array_filter(array($req['card_number'] ?? '', $req['account_number'] ?? '', $req['iban'] ?? '')))); ?>
                            </td>
                            <td><?php echo esc_html($req['date']); ?></td>
                            <td><?php echo esc_html($labels[$req['status']] ?? $req['status']); ?></td>
                            <td>
                                <?php if ($req['status'] === 'pending'): ?>
                                    <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('pzh_action' => 'done', 'req_id' => $req['id'])), 'pzh_withdrawal_action')); ?>">
                                        <?php _e('پرداخت شد', 'piazhen'); ?>
                                    </a>
                                    <a class="button" href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('pzh_action' => 'reject', 'req_id' => $req['id'])), 'pzh_withdrawal_action')); ?>">
                                        <?php _e('رد و بازگشت وجه', 'piazhen'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * AJAX: render a dashboard section
 */
function pzh_account_page() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('لطفاً وارد حساب کاربری شوید.', 'piazhen')));
    }

    $section = isset($_POST['section']) ? sanitize_key($_POST['section']) : 'dashboard';
    $allowed = array_keys(pzh_dashboard_menu());
    if (!in_array($section, $allowed, true)) $section = 'dashboard';

    wp_send_json_success(array(
        'html'    => pzh_render_account_section($section, $_POST),
        'section' => $section,
    ));
}
add_action('wp_ajax_pzh_account_page', 'pzh_account_page');

/**
 * AJAX: save account info (اطلاعات کاربری)
 */
function pzh_account_save_info() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('لطفاً وارد حساب کاربری شوید.', 'piazhen')));
    }

    $uid        = get_current_user_id();
    $first_name = sanitize_text_field(isset($_POST['first_name']) ? $_POST['first_name'] : '');
    $last_name  = sanitize_text_field(isset($_POST['last_name']) ? $_POST['last_name'] : '');
    $email      = sanitize_email(isset($_POST['email']) ? $_POST['email'] : '');

    if (!$first_name || !$last_name) {
        wp_send_json_error(array('message' => __('نام و نام خانوادگی الزامی است.', 'piazhen')));
    }
    if ($email && !is_email($email)) {
        wp_send_json_error(array('message' => __('ایمیل وارد شده معتبر نیست.', 'piazhen')));
    }

    $user = wp_get_current_user();
    wp_update_user(array(
        'ID'           => $uid,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'display_name' => trim($first_name . ' ' . $last_name),
    ));
    if ($email && $email !== $user->user_email) {
        $result = wp_update_user(array('ID' => $uid, 'user_email' => $email));
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
    }

    $fields = array(
        'company'   => 'billing_company',
        'phone'     => 'billing_phone',
        'postcode'  => 'billing_postcode',
        'state'     => 'billing_state',
        'city'      => 'billing_city',
        'address_1' => 'billing_address_1',
    );
    foreach ($fields as $input => $meta) {
        $value = isset($_POST[$input]) ? sanitize_text_field(wp_unslash($_POST[$input])) : '';
        update_user_meta($uid, $meta, $value);
        update_user_meta($uid, str_replace('billing_', 'shipping_', $meta), $value);
    }
    update_user_meta($uid, 'billing_first_name', $first_name);
    update_user_meta($uid, 'billing_last_name', $last_name);
    update_user_meta($uid, 'shipping_first_name', $first_name);
    update_user_meta($uid, 'shipping_last_name', $last_name);

    wp_send_json_success(array('message' => __('اطلاعات با موفقیت ذخیره شد.', 'piazhen')));
}
add_action('wp_ajax_pzh_account_save_info', 'pzh_account_save_info');

/**
 * AJAX: save an address (billing or shipping)
 */
function pzh_account_save_address() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('لطفاً وارد حساب کاربری شوید.', 'piazhen')));
    }

    $type = isset($_POST['address_type']) ? sanitize_key($_POST['address_type']) : '';
    if (!in_array($type, array('billing', 'shipping'), true)) {
        wp_send_json_error(array('message' => __('نوع آدرس نامعتبر است.', 'piazhen')));
    }

    $uid = get_current_user_id();

    // پلاک و واحد → combined address_2 ("پلاک ۱۲، واحد ۳")
    $plaque = isset($_POST['plaque']) ? sanitize_text_field(wp_unslash($_POST['plaque'])) : '';
    $unit   = isset($_POST['unit']) ? sanitize_text_field(wp_unslash($_POST['unit'])) : '';
    if ($plaque !== '' || $unit !== '') {
        $address_2 = trim('پلاک ' . $plaque);
        if ($unit !== '') $address_2 .= '، واحد ' . $unit;
        $_POST['address_2'] = $address_2;
    }

    $fields = array(
        'state'      => $type . '_state',
        'city'       => $type . '_city',
        'address_1'  => $type . '_address_1',
        'address_2'  => $type . '_address_2',
        'postcode'   => $type . '_postcode',
        'phone'      => $type . '_phone',
        'latitude'   => $type . '_latitude',
        'longitude'  => $type . '_longitude',
    );
    foreach ($fields as $input => $meta) {
        $value = isset($_POST[$input]) ? sanitize_text_field(wp_unslash($_POST[$input])) : '';
        update_user_meta($uid, $meta, $value);
    }

    // Keep plaque/unit as separate meta too (for the edit form prefills)
    update_user_meta($uid, $type . '_plaque', $plaque);
    update_user_meta($uid, $type . '_unit', $unit);

    wp_send_json_success(array(
        'message' => __('آدرس با موفقیت ذخیره شد.', 'piazhen'),
        'html'    => pzh_render_account_section('addresses', array()),
    ));
}
add_action('wp_ajax_pzh_account_save_address', 'pzh_account_save_address');

/**
 * AJAX: submit a product review from the dashboard
 */
function pzh_account_add_review() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('لطفاً وارد حساب کاربری شوید.', 'piazhen')));
    }

    $uid        = get_current_user_id();
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $comment    = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

    if (!$product_id || trim($comment) === '') {
        wp_send_json_error(array('message' => __('متن نظر را وارد کنید.', 'piazhen')));
    }
    if (!wc_customer_bought_product('', $uid, $product_id)) {
        wp_send_json_error(array('message' => __('برای ثبت نظر باید این محصول را خریداری کرده باشید.', 'piazhen')));
    }

    $user = wp_get_current_user();
    $approved = (get_option('comment_moderation') === '1') ? 0 : 1;

    $comment_id = wp_insert_comment(array(
        'comment_post_ID'      => $product_id,
        'comment_author'       => $user->display_name,
        'comment_author_email' => $user->user_email,
        'user_id'              => $uid,
        'comment_content'      => $comment,
        'comment_type'         => 'review',
        'comment_approved'     => $approved,
    ));

    if (!$comment_id) {
        wp_send_json_error(array('message' => __('خطا در ثبت نظر.', 'piazhen')));
    }

    if (wc_review_ratings_enabled()) {
        add_comment_meta($comment_id, 'rating', 5);
    }

    wp_send_json_success(array(
        'message' => __('نظر شما با موفقیت ثبت شد.', 'piazhen'),
        'html'    => pzh_render_account_section('reviews', array()),
    ));
}
add_action('wp_ajax_pzh_account_add_review', 'pzh_account_add_review');

// ============================================================================
// Magazine (Blog) — carousel + AJAX filters/search, no plugins
// ============================================================================

/**
 * Estimated reading time in minutes (Persian text ≈ 150 words/min)
 */
function pzh_post_reading_time($post_id = 0) {
    $content = get_post_field('post_content', $post_id ?: get_the_ID());
    $words   = preg_match_all('/\S+/u', wp_strip_all_tags($content));
    $minutes = max(1, intval(ceil($words / 150)));
    return $minutes;
}

/**
 * Blog categories with post counts
 */
function pzh_get_blog_categories() {
    return get_categories(array('hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC'));
}

/**
 * A single magazine post card
 */
function pzh_render_post_card($post_id) {
    $post_id = intval($post_id);
    $categories = get_the_category($post_id);
    $cat_name   = !empty($categories) ? $categories[0]->name : '';
    $thumb      = get_the_post_thumbnail_url($post_id, 'medium_large') ?: wc_placeholder_img_src('medium_large');
    ?>
    <article class="mag-card">
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="mag-card__image">
            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy">
        </a>
        <div class="mag-card__body">
            <?php if ($cat_name): ?>
                <span class="mag-card__category"><?php echo esc_html($cat_name); ?></span>
            <?php endif; ?>
            <h3 class="mag-card__title">
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
            </h3>
            <p class="mag-card__excerpt">
                <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 18, '…')); ?>
            </p>
            <div class="mag-card__meta">
                <span class="mag-card__date">
                    <i class="fa-regular fa-calendar"></i>
                    <?php echo get_the_date('Y/m/d', $post_id); ?>
                </span>
                <span class="mag-card__read-time">
                    <i class="fa-regular fa-clock"></i>
                    <?php printf(__('%s دقیقه مطالعه', 'piazhen'), pzh_fa_num(pzh_post_reading_time($post_id))); ?>
                </span>
            </div>
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="mag-card__more">
                <?php _e('ادامه مطلب', 'piazhen'); ?>
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    </article>
    <?php
}

/**
 * Render the magazine grid + pagination for a query
 */
function pzh_render_magazine_grid($query, $page, $per_page) {
    $total       = intval($query->found_posts);
    $total_pages = $per_page ? intval(ceil($total / $per_page)) : 0;

    ob_start();

    if ($query->have_posts()) {
        echo '<div class="mag-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            pzh_render_post_card(get_the_ID());
        }
        echo '</div>';
        pzh_render_pagination($page, $total_pages);
    } else {
        echo '<div class="mag-empty">';
        echo '<p>' . __('مقاله‌ای یافت نشد.', 'piazhen') . '</p>';
        echo '</div>';
    }

    return ob_get_clean();
}

/**
 * AJAX: newsletter subscription (stored in a site option — no plugin)
 */
function pzh_newsletter_subscribe() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    if (!$email || !is_email($email)) {
        wp_send_json_error(array('message' => __('ایمیل وارد شده معتبر نیست.', 'piazhen')));
    }

    $emails = get_option('pzh_newsletter_emails', array());
    if (!is_array($emails)) $emails = array();

    if (in_array($email, $emails, true)) {
        wp_send_json_success(array('message' => __('شما قبلاً عضو خبرنامه شده‌اید.', 'piazhen')));
    }

    $emails[] = $email;
    update_option('pzh_newsletter_emails', $emails);

    wp_send_json_success(array('message' => __('عضویت شما در خبرنامه با موفقیت ثبت شد.', 'piazhen')));
}
add_action('wp_ajax_pzh_newsletter_subscribe', 'pzh_newsletter_subscribe');
add_action('wp_ajax_nopriv_pzh_newsletter_subscribe', 'pzh_newsletter_subscribe');

/**
 * AJAX: magazine filters / search / pagination
 */
function pzh_blog_ajax() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $page     = max(1, isset($_POST['page']) ? intval($_POST['page']) : 1);
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 9;
    $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $search   = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ($category > 0) {
        $args['cat'] = $category;
    }
    if ($search !== '') {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);
    $html  = pzh_render_magazine_grid($query, $page, $per_page);
    wp_reset_postdata();

    wp_send_json_success(array(
        'html'        => $html,
        'total'       => intval($query->found_posts),
        'page'        => $page,
        'total_pages' => $per_page ? intval(ceil($query->found_posts / $per_page)) : 0,
    ));
}
add_action('wp_ajax_pzh_blog_ajax', 'pzh_blog_ajax');
add_action('wp_ajax_nopriv_pzh_blog_ajax', 'pzh_blog_ajax');

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

    public $menu_id = 0;
    public $skip_children = false;
    public $skip_depth = 0;

    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        // Items inside a mega panel are already rendered there — skip them
        if ($this->skip_children) {
            return;
        }

        $classes   = empty($item->classes) ? array() : (array) $item->classes;
        $has_children = in_array('menu-item-has-children', $classes);

        if (!$this->menu_id) {
            $this->menu_id = pzh_get_nav_menu_id_from_args($args);
        }

        // Mega trigger: a top-level item whose children are WooCommerce categories
        $is_mega = ($depth === 0 && $has_children && $this->menu_id && pzh_menu_item_has_product_cat_children($item, $this->menu_id));
        if ($is_mega) {
            // Note: the li gets its OWN class — the .mega-menu class belongs
            // to the panel only (its opacity/visibility rules must not hit the li)
            $classes[] = 'has-mega-menu';
        }

        $output .= '<li class="' . esc_attr(implode(' ', $classes)) . '">';

        $attributes  = '';
        $attributes .= !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        // Arrow only for regular dropdowns — the design shows no chevron on the mega trigger
        if ($has_children && !$is_mega) {
            $item_output .= ' <svg class="menu-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
        }
        $item_output .= '</a>';

        // Render the mega panel from the menu's own category hierarchy
        if ($is_mega && $depth === 0) {
            $item_output .= pzh_build_mega_menu_panel($item, $this->menu_id);
            $this->skip_children = true;
            $this->skip_depth    = $depth;
        }

        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    function start_lvl(&$output, $depth = 0, $args = array()) {
        if ($this->skip_children) {
            return;
        }
        $output .= '<ul class="sub-menu">';
    }

    function end_lvl(&$output, $depth = 0, $args = array()) {
        if ($this->skip_children) {
            if ($depth === $this->skip_depth) {
                $this->skip_children = false;
            }
            return;
        }
        $output .= '</ul>';
    }

    function end_el(&$output, $item, $depth = 0, $args = array()) {
        // Suppressed children (rendered inside the mega panel) must not emit
        // their closing tags — orphan </li> tags break the browser's DOM.
        if ($this->skip_children) {
            return;
        }
        $output .= '</li>';
    }
}

/**
 * Resolve the nav menu term id from walker args (object / id / slug / location)
 */
function pzh_get_nav_menu_id_from_args($args) {
    if (!empty($args->menu)) {
        if (is_object($args->menu) && isset($args->menu->term_id)) {
            return intval($args->menu->term_id);
        }
        if (is_numeric($args->menu)) {
            return intval($args->menu);
        }
        if (is_string($args->menu)) {
            $term = get_term_by('slug', $args->menu, 'nav_menu');
            if (!$term) $term = get_term_by('name', $args->menu, 'nav_menu');
            if ($term) return intval($term->term_id);
        }
    }
    if (!empty($args->theme_location)) {
        $locations = get_nav_menu_locations();
        if (isset($locations[$args->theme_location])) {
            return intval($locations[$args->theme_location]);
        }
    }
    return 0;
}

/**
 * Menu items grouped by parent (cached per menu)
 */
function pzh_get_menu_item_children_map($menu_id) {
    static $cache = array();
    if (isset($cache[$menu_id])) return $cache[$menu_id];

    $items = wp_get_nav_menu_items($menu_id);
    $map   = array();
    if (!empty($items) && !is_wp_error($items)) {
        foreach ($items as $it) {
            $map[intval($it->menu_item_parent)][] = $it;
        }
    }
    $cache[$menu_id] = $map;
    return $map;
}

/**
 * Does this menu item have WooCommerce category children?
 */
function pzh_menu_item_has_product_cat_children($item, $menu_id) {
    $map = pzh_get_menu_item_children_map($menu_id);
    $children = isset($map[intval($item->ID)]) ? $map[intval($item->ID)] : array();
    foreach ($children as $child) {
        if ($child->type === 'taxonomy' && $child->object === 'product_cat') {
            return true;
        }
    }
    return false;
}

/**
 * Build the mega menu panel from the nav menu hierarchy:
 * 4 balanced, right-aligned text columns (bold headers + gray links).
 */
function pzh_build_mega_menu_panel($item, $menu_id) {
    $map = pzh_get_menu_item_children_map($menu_id);
    $children = isset($map[intval($item->ID)]) ? $map[intval($item->ID)] : array();

    $categories = array();
    foreach ($children as $child) {
        $subs = isset($map[intval($child->ID)]) ? $map[intval($child->ID)] : array();
        $categories[] = array('item' => $child, 'subs' => $subs);
    }

    if (empty($categories)) {
        return '';
    }

    // Distribute into 4 columns, balancing the row count per column
    $columns = array(array(), array(), array(), array());
    $weights = array(0, 0, 0, 0);
    foreach ($categories as $cat) {
        $weight = 1 + count($cat['subs']); // header + its links
        $target = array_search(min($weights), $weights, true);
        $columns[$target][] = $cat;
        $weights[$target] += $weight;
    }

    ob_start();
    ?>
    <div class="mega-menu">
        <div class="mega-menu__cols">
            <?php foreach ($columns as $column):
                if (empty($column)) continue;
                ?>
                <div class="mega-menu__col">
                    <?php foreach ($column as $cat): ?>
                        <a class="mega-menu__header" href="<?php echo esc_url($cat['item']->url); ?>">
                            <?php echo esc_html($cat['item']->title); ?>
                        </a>
                        <?php if (!empty($cat['subs'])): ?>
                            <ul class="mega-menu__links">
                                <?php foreach ($cat['subs'] as $sub): ?>
                                    <li>
                                        <a href="<?php echo esc_url($sub->url); ?>"><?php echo esc_html($sub->title); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
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
