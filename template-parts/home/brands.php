<?php
/**
 * Homepage: Brands Logos Section
 * Grid of 4 cols × 2 rows
 */
$brands = pzh_get_brands();

// If no brands from taxonomy, show placeholder brands
if (empty($brands)) {
    $brands = array();
    for ($i = 1; $i <= 8; $i++) {
        $brands[] = array(
            'name'  => sprintf(__('برند %s', 'piazhen'), pzh_fa_num($i)),
            'image' => '',
            'link'  => '#',
        );
    }
}
?>
<section class="home_brands py-5">
    <div class="container">

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
