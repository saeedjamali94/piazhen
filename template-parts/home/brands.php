<?php
/**
 * Homepage: Brands Logos Section
 * Grid of 4 cols × 2 rows
 */
$brands = pzh_get_brands();

// If no brands from taxonomy, show placeholder brands
if (empty($brands)) {
    $brands = array(
        array('name' => 'برند ۱', 'image' => '', 'link' => '#'),
        array('name' => 'برند ۲', 'image' => '', 'link' => '#'),
        array('name' => 'برند ۳', 'image' => '', 'link' => '#'),
        array('name' => 'برند ۴', 'image' => '', 'link' => '#'),
        array('name' => 'برند ۵', 'image' => '', 'link' => '#'),
        array('name' => 'برند ۶', 'image' => '', 'link' => '#'),
        array('name' => 'برند ۷', 'image' => '', 'link' => '#'),
        array('name' => 'برند ۸', 'image' => '', 'link' => '#'),
    );
}
?>
<section class="home_brands py-5">
    <div class="container">
        <?php
        set_query_var('texts', array(
            'topBtnText' => __('برندها', 'piazhen'),
            'heading'    => __('برندهای محبوب', 'piazhen'),
            'text'       => __('محصولات را بر اساس برند مورد علاقه خود انتخاب کنید', 'piazhen'),
        ));
        get_template_part('template-parts/global/section', 'title');
        ?>

        <div class="brands-grid">
            <?php foreach ($brands as $brand): ?>
                <a href="<?= esc_url($brand['link']); ?>" class="brand-item">
                    <?php if ($brand['image']): ?>
                        <img src="<?= esc_url($brand['image']); ?>" alt="<?= esc_attr($brand['name']); ?>" class="brand-item__logo">
                    <?php else: ?>
                        <div class="brand-item__placeholder">
                            <span><?= esc_html($brand['name']); ?></span>
                        </div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
