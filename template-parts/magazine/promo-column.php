<?php
/**
 * Magazine promo column (the blog page's left sidebar): 3 yellow quick-links
 * to the blog categories + the article search. Shared by the magazine page
 * and the single-post sidebar.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

$magazine_url = pzh_magazine_page_url();
$promo_cats   = array_slice(pzh_get_blog_categories(), 0, 3);
$search       = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
?>
<div class="mag-promo">
    <?php
    $promo_icons = array(
        // magic wand + sparkles
        '<svg viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 34l10-10M12 12l2 4 4 2-4 2-2 4-2-4-4-2 4-2z"/><path d="M30 6l1.5 3.5L35 11l-3.5 1.5L30 16l-1.5-3.5L25 11l3.5-1.5z"/><path d="M33 24l1 2.5L36.5 27.5 34 28.5 33 31l-1-2.5-2.5-1 2.5-1z"/></svg>',
        // 2x2 grid
        '<svg viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><rect x="5" y="5" width="13" height="13" rx="3"/><rect x="22" y="5" width="13" height="13" rx="3"/><rect x="5" y="22" width="13" height="13" rx="3"/><rect x="22" y="22" width="13" height="13" rx="3"/></svg>',
        // light bulb
        '<svg viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 5a11 11 0 0 1 8 18.7c-1.6 1.5-2.5 3-2.5 4.8h-11c0-1.8-.9-3.3-2.5-4.8A11 11 0 0 1 20 5z"/><path d="M17 32h6M17.5 36h5"/></svg>',
    );
    $promo_i = 0;
    foreach ($promo_cats as $pcat):
        $icon = $promo_icons[$promo_i % 3];
        $promo_i++;
        ?>
        <a href="<?php echo esc_url(add_query_arg('category', $pcat->term_id, $magazine_url)); ?>" class="mag-promo__link">
            <span class="mag-promo__icon"><?php echo $icon; ?></span>
            <span class="mag-promo__label"><?php echo esc_html($pcat->name); ?></span>
            <span class="mag-promo__arrow">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
            </span>
        </a>
    <?php endforeach; ?>

    <form class="mag-promo__search" action="<?php echo esc_url($magazine_url); ?>" method="get" role="search">
        <svg class="mag-promo__search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        <input type="search" id="mag-search" name="search"
               placeholder="<?php esc_attr_e('جست‌وجو در مقالات', 'piazhen'); ?>"
               value="<?php echo esc_attr($search); ?>">
        <button type="submit" id="mag-search-btn" aria-label="<?php esc_attr_e('جستجو', 'piazhen'); ?>"></button>
    </form>
</div>
