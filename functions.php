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
    wp_enqueue_style('piazhen-main-style', PZH_THEME_URI . '/assets/css/styles.css', array('swiper-css'), $version);

    // Scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.1.0', true);
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.8', true);
    wp_enqueue_script('piazhen-js', PZH_THEME_URI . '/assets/js/app.js', array('jquery', 'swiper-js'), $version, true);

    wp_localize_script('piazhen-js', 'pzh_options', array(
        'theme_url'  => PZH_THEME_URI,
        'ajax_url'   => admin_url('admin-ajax.php'),
        'sprite_url' => SPRITE_URL,
        'site_url'   => SITE_URL,
        'nonce'      => wp_create_nonce('pzh_ajax_nonce'),
        'is_rtl'     => is_rtl(),
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
                <span class="product-card__badge product-card__badge--sale"><?php _e('حراج', 'piazhen'); ?></span>
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
            <button class="product-card__add-to-cart mainBtn small"
                    data-product-id="<?php echo esc_attr($product_id); ?>">
                <?php _e('افزودن به سبد خرید', 'piazhen'); ?>
            </button>
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
 * Get site brand logos
 */
function pzh_get_brands() {
    // Try to get brands from a product_brand taxonomy (common WooCommerce brands plugin)
    $brands = array();
    if (taxonomy_exists('product_brand')) {
        $terms = get_terms(array('taxonomy' => 'product_brand', 'hide_empty' => false, 'number' => 8));
        foreach ($terms as $term) {
            $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
            $brands[] = array(
                'name' => $term->name,
                'image' => $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '',
                'link'  => get_term_link($term),
            );
        }
    }
    return $brands;
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

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $variation  = isset($_POST['variation']) ? $_POST['variation'] : array();

    if (!$product_id) {
        wp_send_json_error(array('message' => __('محصول نامعتبر است.', 'piazhen')));
    }

    $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, $variation);

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
 * AJAX Filter Products
 */
function pzh_filter_products() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    $page     = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
    $orderby  = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
    $order    = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';
    $brands   = isset($_POST['brands']) ? array_map('intval', $_POST['brands']) : array();
    $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
    $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 0;
    $category  = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    // Build query args
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'post_status'    => 'publish',
    );

    // Category filter
    if ($category) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $category,
        );
    }

    // Brand filter
    if (!empty($brands)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_brand',
            'field'    => 'term_id',
            'terms'    => $brands,
        );
    }

    // Price filter
    if ($min_price > 0 || $max_price > 0) {
        $args['meta_query'][] = array(
            'key'     => '_price',
            'value'   => array($min_price, $max_price),
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        );
    }

    // Sort
    switch ($orderby) {
        case 'price':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = $order;
            break;
        case 'popularity':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $args['order']    = 'DESC';
            break;
        case 'date':
            $args['orderby'] = 'date';
            $args['order']   = $order;
            break;
        case 'discount':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_sale_price';
            $args['order']    = 'DESC';
            $args['meta_query'][] = array(
                'key'     => '_sale_price',
                'value'   => '',
                'compare' => '!=',
            );
            break;
        default:
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
    }

    $query = new WP_Query($args);
    $total = $query->found_posts;

    ob_start();
    if ($query->have_posts()) {
        echo '<div class="products-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            echo pzh_get_product_card_html(get_the_ID());
        }
        echo '</div>';

        // Pagination
        $total_pages = ceil($total / $per_page);
        if ($total_pages > 1) {
            echo '<div class="products-pagination">';
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i === $page) ? ' active' : '';
                echo '<button class="products-pagination__btn' . $active . '" data-page="' . $i . '">' . $i . '</button>';
            }
            echo '</div>';
        }
    } else {
        echo '<p class="products-grid__empty">' . __('محصولی یافت نشد.', 'piazhen') . '</p>';
    }

    wp_reset_postdata();
    $html = ob_get_clean();

    wp_send_json_success(array(
        'html'       => $html,
        'total'      => $total,
        'page'       => $page,
        'total_pages' => ceil($total / $per_page),
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
