<?php
/**
 * Template Name: مقایسه محصول
 *
 * Product comparison page (per comparison.png): two product card slots +
 * a مشخصات فنی specs grid + closing copy. Products arrive via ?ids=1,2
 * (the floating compare bar links here).
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

// The content depends on ?ids — never let LiteSpeed/browsers serve a stale copy
if (!headers_sent()) {
    header('X-LiteSpeed-Cache-Control: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
}

get_header();

// Products to compare (max 2 slots, per the design)
$ids = array();
if (!empty($_GET['ids'])) {
    $raw = array_filter(array_map('absint', explode(',', sanitize_text_field(wp_unslash($_GET['ids'])))));
    $ids = array_slice(array_unique($raw), 0, 2);
}
$products = array();
foreach ($ids as $id) {
    $p = wc_get_product($id);
    if ($p) {
        $products[] = $p;
    }
}

$ids = array_map(function ($p) { return $p->get_id(); }, $products);
$count = count($products);
$compare_url = pzh_compare_page_url();
$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

/** One brand attribute value for the برند row (empty when the shop has none). */
$get_brand = function ($product) {
    foreach (array('برند', 'pa_برند', 'brand', 'pa_brand') as $name) {
        $value = trim($product->get_attribute($name));
        if ($value !== '') {
            return $value;
        }
    }
    return '';
};

/** Top-level category name for the نوع محصول row. */
$get_type_label = function ($product) {
    $terms = get_the_terms($product->get_id(), 'product_cat');
    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            if (empty($term->parent)) {
                return $term->name;
            }
        }
        return $terms[0]->name;
    }
    return '';
};
?>

<div class="container">
    <div class="pzh-cmp">

        <!-- H1: مقایسه محصول · ۲ محصول -->
        <h1 class="pzh-cmp__title">
            <span><?php _e('مقایسه محصول', 'piazhen'); ?></span>
            <span class="pzh-cmp__dot">.</span>
            <span class="pzh-cmp__count"><?php echo pzh_fa_num($count) . ' ' . __('محصول', 'piazhen'); ?></span>
        </h1>

        <!-- Product card slots -->
        <div class="pzh-cmp__cards">
            <?php foreach ($products as $product): ?>
            <div class="pzh-cmp-card">

                <!-- Image area -->
                <div class="pzh-cmp-card__image-area">
                    <button type="button" class="pzh-cmp-card__remove pzh-cmp-remove"
                            data-product-id="<?php echo $product->get_id(); ?>"
                            aria-label="<?php esc_attr_e('حذف از مقایسه', 'piazhen'); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M9 9l6 6M15 9l-6 6"/>
                        </svg>
                    </button>
                    <div class="pzh-cmp-card__image">
                        <?php echo $product->get_image('pzh_product_card'); ?>
                    </div>
                </div>

                <div class="pzh-cmp-card__divider"></div>

                <!-- Info area -->
                <div class="pzh-cmp-card__info">
                    <h3 class="pzh-cmp-card__name"><?php echo esc_html($product->get_name()); ?></h3>
                    <div class="pzh-cmp-card__price"><?php echo $product->get_price_html(); ?></div>
                    <a href="<?php echo esc_url($product->get_permalink()); ?>" class="pzh-cmp-card__buy"><?php _e('مشاهده و خرید', 'piazhen'); ?></a>
                </div>

            </div>
            <?php endforeach; ?>

            <?php for ($i = $count; $i < 2; $i++): ?>
            <!-- Empty slot: add-product affordance -->
            <div class="pzh-cmp-card pzh-cmp-card--empty">

                <div class="pzh-cmp-card__image-area">
                    <div class="pzh-cmp-card__focus">
                        <svg viewBox="0 0 210 210" fill="none" stroke="#FBCA38" stroke-width="2" stroke-linecap="butt" aria-hidden="true">
                            <path d="M190.2 45.35A104 104 0 0 1 164.65 19.8"/>
                            <path d="M45.35 19.8A104 104 0 0 1 19.8 45.35"/>
                            <path d="M19.8 164.65A104 104 0 0 1 45.35 190.2"/>
                            <path d="M164.65 190.2A104 104 0 0 1 190.2 164.65"/>
                            <path d="M91 19h28M91 191h28M19 91v28M191 91v28"/>
                            <path d="M65 105h80M105 65v80"/>
                        </svg>
                    </div>
                </div>

                <div class="pzh-cmp-card__divider"></div>

                <div class="pzh-cmp-card__info">
                    <div class="pzh-cmp-card__bar"></div>
                    <div class="pzh-cmp-card__bar"></div>
                    <div class="pzh-cmp-card__bar"></div>
                    <a href="<?php echo esc_url($shop_url); ?>" class="pzh-cmp-card__add"><?php _e('افزودن محصول', 'piazhen'); ?></a>
                </div>

            </div>
            <?php endfor; ?>
        </div>

        <?php if ($count > 0): ?>
        <!-- Specs -->
        <h2 class="pzh-cmp__specs-title"><?php _e('مشخصات فنی', 'piazhen'); ?></h2>

        <div class="pzh-cmp__specs" style="grid-template-columns: 342px repeat(<?php echo $count; ?>, 475px);">
            <?php
            $rows = array(
                array('label' => __('نام محصول', 'piazhen'), 'values' => array_map(function ($p) { return $p->get_name(); }, $products), 'first' => true),
                array('label' => __('برند', 'piazhen'), 'values' => array_map(function ($p) use ($get_brand) { $v = $get_brand($p); return $v !== '' ? $v : '—'; }, $products), 'first' => false),
                array('label' => __('نوع محصول', 'piazhen'), 'values' => array_map(function ($p) use ($get_type_label) { $v = $get_type_label($p); return $v !== '' ? $v : '—'; }, $products), 'first' => false),
            );
            foreach ($rows as $row):
                $first = !empty($row['first']);
                ?>
                <div class="pzh-cmp-spec pzh-cmp-spec--label <?php echo $first ? 'is-first' : ''; ?>"><?php echo esc_html($row['label']); ?></div>
                <?php foreach ($row['values'] as $value): ?>
                <div class="pzh-cmp-spec <?php echo $first ? 'is-first' : ''; ?>"><?php echo esc_html($value); ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Closing copy -->
        <div class="pzh-cmp__copy">
            <h2><?php _e('مقایسه محصول', 'piazhen'); ?></h2>
            <h3><?php _e('انتخاب بهترین کالا', 'piazhen'); ?></h3>
            <p><?php _e('شما می‌توانید با استفاده از این قسمت ویژگی های کالاهای مدنظرتون رو در کنار هم مشاهده ده در نهایت بهترین رو انتخاب کنید.', 'piazhen'); ?></p>
        </div>

    </div>
</div>

<script>
(function () {
    // Hydrate from localStorage when the page is opened without ?ids, and
    // keep localStorage in sync with the URL ids afterwards
    var params = new URLSearchParams(window.location.search);
    var urlIds = (params.get('ids') || '').split(',').filter(Boolean).slice(0, 2);
    var localIds = [];
    try {
        localIds = JSON.parse(localStorage.getItem('pzh_compare') || '[]');
    } catch (ex) {}

    if (!urlIds.length && localIds.length) {
        window.location.replace(<?php echo wp_json_encode($compare_url); ?> + '?ids=' + localIds.slice(0, 2).join(','));
        return;
    }
    if (localIds.join(',') !== urlIds.join(',')) {
        try {
            localStorage.setItem('pzh_compare', JSON.stringify(urlIds));
        } catch (ex) {}
    }

    // Remove a product from the comparison
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.pzh-cmp-remove');
        if (!btn) return;
        e.preventDefault();
        var id = btn.getAttribute('data-product-id');
        var list = [];
        try {
            list = JSON.parse(localStorage.getItem('pzh_compare') || '[]');
        } catch (ex) {}
        list = list.filter(function (i) { return String(i) !== String(id); });
        try {
            localStorage.setItem('pzh_compare', JSON.stringify(list));
        } catch (ex) {}
        window.location.href = <?php echo wp_json_encode($compare_url); ?> + (list.length ? '?ids=' + list.join(',') : '');
    });
})();
</script>

<?php get_footer(); ?>
