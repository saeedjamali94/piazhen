<?php
/**
 * Homepage: Features Section
 * 4-column grid, each with icon & title
 */
$features = array(
    array(
        'icon'  => PZH_THEME_URI.'/assets/images/guarantee.svg',
        'title' => __('ضمانت اصل بودن', 'piazhen'),
    ),
    array(
        'icon'  => PZH_THEME_URI.'/assets/images/delivery.svg',
        'title' => __('امکان تحویل اکسپرس', 'piazhen'),
    ),
    array(
        'icon'  => PZH_THEME_URI.'/assets/images/support.svg',
        'title' => __('پشتیبانی آنلاین', 'piazhen'),
    ),
    array(
        'icon'  => PZH_THEME_URI.'/assets/images/pay.svg',
        'title' => __('امکان پرداخت امن', 'piazhen'),
    ),
);
?>
<section class="home_features py-5">
    <div class="container">
        <div class="features-grid">
            <?php foreach ($features as $feature): ?>
                <div class="feature-item text-center">
                    <div class="feature-item__icon">
                        <img height="50" src="<?= $feature['icon']; ?>" alt="<?= esc_html($feature['title']); ?>">
                    </div>
                    <h4 class="feature-item__title"><?= esc_html($feature['title']); ?></h4>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
